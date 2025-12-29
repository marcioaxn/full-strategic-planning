<?php

namespace Database\Seeders;

use App\Models\PEI\PEI;
use App\Models\PEI\Indicador;
use App\Models\PEI\EvolucaoIndicador;
use Illuminate\Database\Seeder;

class EvolucaoIndicadorSeeder extends Seeder
{
    public function run(): void
    {
        $peiAtivo = PEI::where('bln_ativo', true)->first();
        if (!$peiAtivo) return;

        // Buscar indicadores vinculados a este PEI (diretos ou via plano)
        $indicadores = Indicador::with('metasPorAno')->whereHas('objetivo.perspectiva', function($q) use ($peiAtivo) {
            $q->where('cod_pei', $peiAtivo->cod_pei);
        })->orWhereHas('planoDeAcao.objetivo.perspectiva', function($q) use ($peiAtivo) {
            $q->where('cod_pei', $peiAtivo->cod_pei);
        })->get();

        foreach ($indicadores as $indicador) {
            $ano = now()->year;
            $metaAnual = $indicador->metasPorAno->where('num_ano', $ano)->first()?->meta ?? 100;
            $metaMensal = $metaAnual / 12;

            // Criar evoluções para os meses passados do ano atual
            for ($mes = 1; $mes <= now()->month; $mes++) {
                $previsto = $metaMensal;
                $realizado = $previsto * (rand(70, 110) / 100); // Oscilação entre 70% e 110% da meta

                EvolucaoIndicador::create([
                    'cod_indicador' => $indicador->cod_indicador,
                    'num_ano' => $ano,
                    'num_mes' => $mes,
                    'vlr_previsto' => $previsto,
                    'vlr_realizado' => $realizado,
                    'txt_observacao' => "Lançamento automático via Seeder para o mês $mes.",
                ]);
            }
        }
    }
}