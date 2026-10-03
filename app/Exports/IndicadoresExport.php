<?php

namespace App\Exports;

use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\PEI;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class IndicadoresExport implements FromCollection, WithHeadings, WithMapping
{
    protected $organizacaoId;

    public function __construct($organizacaoId)
    {
        $this->organizacaoId = $organizacaoId;
    }

    public function collection()
    {
        $query = Indicador::query()->with(['objetivo', 'planoDeAcao']);

        if ($this->organizacaoId) {
            // Agrupado: o orWhereHas solto anulava o filtro de ciclo abaixo.
            $query->where(function ($q) {
                $q->whereHas('organizacoes', fn ($o) => $o->where('tab_organizacoes.cod_organizacao', $this->organizacaoId))
                    ->orWhereHas('planoDeAcao', fn ($p) => $p->where('cod_organizacao', $this->organizacaoId));
            });
        }

        // Só o ciclo em contexto (a planilha misturava todos os ciclos).
        if ($pei = PEI::doContexto()) {
            $query->where(function ($q) use ($pei) {
                $q->whereHas('objetivo.perspectiva', fn ($p) => $p->where('cod_pei', $pei->cod_pei))
                    ->orWhereHas('planoDeAcao.objetivo.perspectiva', fn ($p) => $p->where('cod_pei', $pei->cod_pei));
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Indicador',
            'Unidade',
            'Periodicidade',
            'Vínculo',
            'Meta',
            'Atingimento (%)',
        ];
    }

    public function map($indicador): array
    {
        $vinculo = $indicador->cod_objetivo
            ? 'Objetivo: '.$indicador->objetivo->nom_objetivo
            : 'Iniciativa: '.$indicador->planoDeAcao->dsc_plano_de_acao;

        return [
            $indicador->nom_indicador,
            $indicador->dsc_unidade_medida,
            $indicador->dsc_periodo_medicao,
            $vinculo,
            $indicador->dsc_meta,
            number_format($indicador->calcularAtingimento(), 1),
        ];
    }
}
