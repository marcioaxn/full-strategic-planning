<?php

namespace App\Exports;

use App\Exports\Concerns\CelulasSemFormula;
use App\Models\Organization;
use App\Models\RiskManagement\Risco;
use App\Models\StrategicPlanning\PEI;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RiscosExport implements FromCollection, WithHeadings, WithMapping
{
    use CelulasSemFormula;

    protected $organizacaoId;

    public function __construct($organizacaoId)
    {
        $this->organizacaoId = $organizacaoId;
    }

    public function collection()
    {
        $query = Risco::query()->with(['mitigacoes', 'ocorrencias']);

        // Mesmo recorte da lista, da matriz e do PDF: a unidade e as subordinadas.
        if ($this->organizacaoId) {
            $query->whereIn('risk_management.tab_risco.cod_organizacao', Organization::descendentesEProprio($this->organizacaoId));
        }

        // Só o ciclo em contexto, como a tela de riscos e o PDF.
        if ($pei = PEI::doContexto()) {
            $query->where('cod_pei', $pei->cod_pei);
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

    protected function linha($risco): array
    {
        $nivel = $risco->num_probabilidade * $risco->num_impacto;

        // Colunas reais de tab_risco (nom_risco/dsc_risco não existem: saíam em
        // branco) e a mesma régua das telas, da matriz e do PDF (Crítico ≥ 16).
        return [
            $risco->dsc_titulo,
            $risco->txt_descricao,
            $risco->num_probabilidade,
            $risco->num_impacto,
            $nivel,
            Risco::rotuloDoNivel($nivel),
            $risco->mitigacoes->count(),
            $risco->ocorrencias->count(),
        ];
    }
}
