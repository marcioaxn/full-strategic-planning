<?php

namespace App\Exports;

use App\Models\RiskManagement\Risco;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RiscosExport implements FromCollection, WithHeadings, WithMapping
{
    protected $organizacaoId;

    public function __construct($organizacaoId)
    {
        $this->organizacaoId = $organizacaoId;
    }

    public function collection()
    {
        $query = Risco::query()->with(['mitigacoes', 'ocorrencias']);

        if ($this->organizacaoId) {
            $query->where('cod_organizacao', $this->organizacaoId);
        }

        return $query->orderByRaw('(num_probabilidade * num_impacto) DESC')->get();
    }

    public function headings(): array
    {
        return [
            'Risco',
            'Descrição',
            'Probabilidade',
            'Impacto',
            'Nível (P x I)',
            'Classificação',
            'Mitigações',
            'Ocorrências',
        ];
    }

    public function map($risco): array
    {
        $nivel = $risco->num_probabilidade * $risco->num_impacto;
        $classificacao = $nivel >= 15 ? 'Crítico' : ($nivel >= 10 ? 'Alto' : ($nivel >= 5 ? 'Médio' : 'Baixo'));

        return [
            $risco->nom_risco,
            $risco->dsc_risco,
            $risco->num_probabilidade,
            $risco->num_impacto,
            $nivel,
            $classificacao,
            $risco->mitigacoes->count(),
            $risco->ocorrencias->count(),
        ];
    }
}
