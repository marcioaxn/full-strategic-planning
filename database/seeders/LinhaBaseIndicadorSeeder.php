<?php

namespace Database\Seeders;

use App\Models\StrategicPlanning\PEI;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\PerformanceIndicators\LinhaBaseIndicador;
use Illuminate\Database\Seeder;

class LinhaBaseIndicadorSeeder extends Seeder
{
    public function run(): void
    {
        $peiAtivo = PEI::where('bln_ativo', true)->first();
        if (!$peiAtivo) return;

        $indicadores = Indicador::whereHas('objetivo.perspectiva', function($q) use ($peiAtivo) {
            $q->where('cod_pei', $peiAtivo->cod_pei);
        })->orWhereHas('planoDeAcao.objetivo.perspectiva', function($q) use ($peiAtivo) {
            $q->where('cod_pei', $peiAtivo->cod_pei);
        })->get();

        foreach ($indicadores as $indicador) {
            LinhaBaseIndicador::create([
                'cod_indicador' => $indicador->cod_indicador,
                'num_ano' => $peiAtivo->num_ano_inicio_pei - 1,
                'num_linha_base' => rand(50, 100),
            ]);
        }
    }
}