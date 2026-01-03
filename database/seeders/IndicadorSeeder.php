<?php

namespace Database\Seeders;

use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\ActionPlan\PlanoDeAcao;
use Illuminate\Database\Seeder;

class IndicadorSeeder extends Seeder
{
    public function run(): void
    {
        $peiAtivo = PEI::where('bln_ativo', true)->first();
        if (!$peiAtivo) return;

        // 1. Criar Indicadores para Objetivos
        $objetivos = Objetivo::whereHas('perspectiva', function($q) use ($peiAtivo) {
            $q->where('cod_pei', $peiAtivo->cod_pei);
        })->get();

        foreach ($objetivos as $objetivo) {
            // Criar 1 a 2 indicadores por objetivo
            $qtd = rand(1, 2);
            for ($i = 1; $i <= $qtd; $i++) {
                Indicador::create($this->criarIndicadorObjetivo($objetivo, $i));
            }
        }

        // 2. Criar Indicadores para Planos de Ação
        $planos = PlanoDeAcao::all();
        foreach ($planos as $plano) {
            Indicador::create([
                'cod_plano_de_acao' => $plano->cod_plano_de_acao,
                'nom_indicador' => "Taxa de Execução: " . $plano->dsc_plano_de_acao,
                'dsc_indicador' => "Mede o percentual de conclusão das entregas do plano.",
                'dsc_tipo' => 'Plano',
                'dsc_meta' => '100%',
                'dsc_unidade_medida' => 'Percentual (%)',
                'num_peso' => 1,
                'bln_acumulado' => 'Não',
                'dsc_formula' => '(Entregas Concluídas / Total de Entregas) * 100',
                'dsc_fonte' => 'Sistema SEAE - Módulo de Entregas',
                'dsc_periodo_medicao' => 'Mensal',
            ]);
        }
    }

    private function criarIndicadorObjetivo(Objetivo $objetivo, int $numero): array
    {
        $nomes = [
            1 => "Índice de Atingimento: " . $objetivo->nom_objetivo,
            2 => "Eficiência Operacional: " . $objetivo->nom_objetivo,
        ];

        return [
            'cod_objetivo' => $objetivo->cod_objetivo,
            'nom_indicador' => $nomes[$numero] ?? "Indicador $numero de " . $objetivo->nom_objetivo,
            'dsc_indicador' => "Indicador estratégico vinculado ao objetivo " . $objetivo->nom_objetivo,
            'dsc_tipo' => 'Objetivo',
            'dsc_meta' => '90%',
            'dsc_unidade_medida' => 'Índice',
            'num_peso' => rand(1, 3),
            'bln_acumulado' => rand(0, 1) ? 'Sim' : 'Não',
            'dsc_formula' => 'Cálculo definido pela área técnica',
            'dsc_fonte' => 'Relatórios Gerenciais',
            'dsc_periodo_medicao' => 'Mensal',
        ];
    }
}