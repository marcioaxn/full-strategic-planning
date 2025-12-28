<?php

namespace App\Exports;

use App\Models\PEI\PlanoDeAcao;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PlanosExport implements FromCollection, WithHeadings, WithMapping
{
    protected $organizacaoId;
    protected $ano;

    public function __construct($organizacaoId, $ano = null)
    {
        $this->organizacaoId = $organizacaoId;
        $this->ano = $ano ?? date('Y');
    }

    public function collection()
    {
        $query = PlanoDeAcao::query()->with(['objetivo', 'entregas', 'responsaveis']);

        if ($this->organizacaoId) {
            $query->where('cod_organizacao', $this->organizacaoId);
        }

        $query->where(function($q) {
            $q->whereYear('dte_inicio', $this->ano)
              ->orWhereYear('dte_fim', $this->ano);
        });

        return $query->orderBy('dte_fim')->get();
    }

    public function headings(): array
    {
        return [
            'Plano de Ação',
            'Objetivo Estratégico',
            'Data Início',
            'Data Fim',
            'Status',
            'Responsáveis',
            'Entregas',
            'Progresso (%)',
        ];
    }

    public function map($plano): array
    {
        $responsaveis = $plano->responsaveis->pluck('name')->implode(', ');
        $entregas = $plano->entregas->count();
        $entregasConcluidas = $plano->entregas->where('bln_concluida', true)->count();
        $progresso = $entregas > 0 ? round(($entregasConcluidas / $entregas) * 100, 1) : 0;

        return [
            $plano->dsc_plano_de_acao,
            $plano->objetivo?->nom_objetivo_estrategico ?? '-',
            $plano->dte_inicio?->format('d/m/Y') ?? '-',
            $plano->dte_fim?->format('d/m/Y') ?? '-',
            $plano->bln_status ?? 'Não Definido',
            $responsaveis ?: '-',
            "{$entregasConcluidas}/{$entregas}",
            $progresso,
        ];
    }
}
