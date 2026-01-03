<?php

namespace Database\Seeders;

use App\Models\StrategicPlanning\PEI;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\PerformanceIndicators\MetaPorAno;
use Illuminate\Database\Seeder;

class MetaPorAnoSeeder extends Seeder
{
    public function run(): void
    {
        $peiAtivo = PEI::where('bln_ativo', true)->first();
        if (!$peiAtivo) return;

        $indicadores = Indicador::with('linhaBase')->whereHas('objetivo.perspectiva', function($q) use ($peiAtivo) {
            $q->where('cod_pei', $peiAtivo->cod_pei);
        })->orWhereHas('planoDeAcao.objetivo.perspectiva', function($q) use ($peiAtivo) {
            $q->where('cod_pei', $peiAtivo->cod_pei);
        })->get();

        foreach ($indicadores as $indicador) {
            // Criar metas para cada ano do ciclo PEI
            for ($ano = $peiAtivo->num_ano_inicio_pei; $ano <= $peiAtivo->num_ano_fim_pei; $ano++) {
                MetaPorAno::create([
                    'cod_indicador' => $indicador->cod_indicador,
                    'num_ano' => $ano,
                    'meta' => rand(100, 200),
                ]);
            }
        }
    }
}