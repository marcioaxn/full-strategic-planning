<?php

namespace App\Exports;

use App\Models\PEI\Indicador;
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
            $query->whereHas('organizacoes', function($q) {
                $q->where('tab_organizacoes.cod_organizacao', $this->organizacaoId);
            })->orWhereHas('planoDeAcao', function($q) {
                $q->where('cod_organizacao', $this->organizacaoId);
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
            ? 'Objetivo: ' . $indicador->objetivo->nom_objetivo 
            : 'Plano: ' . $indicador->planoDeAcao->dsc_plano_de_acao;

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
