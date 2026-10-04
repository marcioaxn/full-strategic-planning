<?php

namespace App\Exports;

use App\Exports\Concerns\CelulasSemFormula;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\StrategicPlanning\PEI;
use App\Support\VigenciaNoAno;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PlanosExport implements FromCollection, WithHeadings, WithMapping
{
    use CelulasSemFormula;

    protected $organizacaoId;

    protected $ano;

    protected ?string $codPei;

    public function __construct($organizacaoId, $ano = null, ?string $codPei = null)
    {
        $this->organizacaoId = $organizacaoId;
        $this->ano = $ano ?? date('Y');
        $this->codPei = $codPei;
    }

    public function collection()
    {
        // 'responsaveis' é um accessor derivado de entregas->responsaveis, não uma relação.
        // Eager-load aninhado evita N+1 e alimenta o accessor getResponsaveisAttribute().
        $query = PlanoDeAcao::query()->with(['objetivo', 'entregas.responsaveis']);

        if ($this->organizacaoId) {
            $query->where('action_plan.tab_plano_de_acao.cod_organizacao', $this->organizacaoId);
        }

        // Mesmo recorte do PDF: do ciclo selecionado e VIGENTES no ano.
        // 🔴 Usava "começa OU termina no ano": a iniciativa de 2024 a 2028
        // saía no PDF de 2026 e sumia desta planilha.
        $codPei = $this->codPei ?? PEI::doContexto()?->cod_pei;
        if ($codPei) {
            $query->whereHas('objetivo.perspectiva', fn ($q) => $q->where('cod_pei', $codPei));
        }

        VigenciaNoAno::aplicar(
            $query,
            (int) $this->ano,
            'action_plan.tab_plano_de_acao.dte_inicio',
            'action_plan.tab_plano_de_acao.dte_fim'
        );

        return $query->orderBy('dte_fim')->get();
    }

    public function headings(): array
    {
        return [
            'Iniciativa',
            'Objetivo',
            'Data Início',
            'Data Fim',
            'Status',
            'Responsáveis',
            'Entregas',
            'Progresso (%)',
        ];
    }

    protected function linha($plano): array
    {
        $responsaveis = $plano->responsaveis->pluck('name')->implode(', ');
        $entregas = $plano->entregas->count();
        $entregasConcluidas = $plano->entregas->where('bln_concluida', true)->count();
        $progresso = $entregas > 0 ? round(($entregasConcluidas / $entregas) * 100, 1) : 0;

        return [
            $plano->dsc_plano_de_acao,
            $plano->objetivo?->nom_objetivo ?? '-',
            $plano->dte_inicio?->format('d/m/Y') ?? '-',
            $plano->dte_fim?->format('d/m/Y') ?? '-',
            $plano->bln_status ?? 'Não Definido',
            $responsaveis ?: '-',
            "{$entregasConcluidas}/{$entregas}",
            $progresso,
        ];
    }
}
