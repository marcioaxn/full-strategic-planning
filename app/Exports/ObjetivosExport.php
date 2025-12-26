<?php

namespace App\Exports;

use App\Models\PEI\ObjetivoEstrategico;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ObjetivosExport implements FromCollection, WithHeadings, WithMapping
{
    protected $codPei;

    public function __construct($codPei)
    {
        $this->codPei = $codPei;
    }

    public function collection()
    {
        return ObjetivoEstrategico::whereHas('perspectiva', function($q) {
            $q->where('cod_pei', $this->codPei);
        })->with('perspectiva')->get();
    }

    public function headings(): array
    {
        return [
            'Nível',
            'Perspectiva',
            'Objetivo Estratégico',
            'Descrição',
        ];
    }

    public function map($objetivo): array
    {
        return [
            $objetivo->num_nivel_hierarquico_apresentacao,
            $objetivo->perspectiva->dsc_perspectiva,
            $objetivo->nom_objetivo_estrategico,
            $objetivo->dsc_objetivo_estrategico,
        ];
    }
}
