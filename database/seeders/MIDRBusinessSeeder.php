<?php

namespace Database\Seeders;

use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\PerformanceIndicators\LinhaBaseIndicador;
use App\Models\PerformanceIndicators\MetaPorAno;
use App\Models\PerformanceIndicators\EvolucaoIndicador;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MIDRBusinessSeeder extends Seeder
{
    public function run(): void
    {
        $objetivos = Objetivo::all();
        $organizacoes = Organization::all(); 
        $tipos = TipoExecucao::all();
        $usuarios = User::all();
        $statuses = ['Não Iniciado', 'Em Andamento', 'Concluído', 'Atrasado', 'Suspenso', 'Cancelado'];

        if ($objetivos->isEmpty() || $organizacoes->isEmpty()) return;

        foreach ($objetivos as $objetivo) {
            $tema = $this->matchTema($objetivo->nom_objetivo);
            
            // 1. KPIs (Indicadores)
            $kpis = $this->getKPIsForTema($tema);
            foreach ($kpis as $kpiData) {
                $indicador = Indicador::create([
                    'cod_objetivo' => $objetivo->cod_objetivo,
                    'dsc_tipo' => 'Estratégico',
                    'nom_indicador' => $kpiData['nom'],
                    'dsc_indicador' => $kpiData['dsc'],
                    'dsc_unidade_medida' => $kpiData['und'],
                    'dsc_periodo_medicao' => 'Mensal',
                    'bln_acumulado' => 'Sim',
                    'dsc_fonte' => 'SISMIDR',
                    'num_peso' => 1,
                    'dsc_formula' => 'Metodologia MIDR',
                ]);

                LinhaBaseIndicador::create([
                    'cod_indicador' => $indicador->cod_indicador,
                    'num_ano' => 2025,
                    'num_linha_base' => $kpiData['base'],
                ]);

                MetaPorAno::create([
                    'cod_indicador' => $indicador->cod_indicador,
                    'num_ano' => 2026,
                    'meta' => $kpiData['meta'],
                ]);

                // DISTRIBUIÇÃO ALEATÓRIA DE INDICADORES
                $targetOrg = $organizacoes->random();
                DB::table('performance_indicators.rel_indicador_objetivo_organizacao')->insert([
                    'cod_indicador' => $indicador->cod_indicador,
                    'cod_organizacao' => $targetOrg->cod_organizacao,
                ]);

                for ($mes = 1; $mes <= 3; $mes++) {
                    $previsto = $kpiData['base'] + (($kpiData['meta'] - $kpiData['base']) * ($mes / 12));
                    $percentualPerformance = rand(30, 110) / 100;
                    $realizado = $previsto * $percentualPerformance;

                    EvolucaoIndicador::create([
                        'cod_indicador' => $indicador->cod_indicador,
                        'num_ano' => 2026,
                        'num_mes' => $mes,
                        'vlr_previsto' => round($previsto, 2),
                        'vlr_realizado' => round($realizado, 2),
                        'txt_avaliacao' => 'Análise de desempenho mensal conforme oscilação operacional.',
                        'bln_atualizado' => 'Sim',
                    ]);
                }
            }

            // 2. Planos de Ação
            $planosNomes = $this->getPlanosForTema($tema);
            foreach ($planosNomes as $nomePlano) {
                // DISTRIBUIÇÃO ALEATÓRIA DE PLANOS
                $targetOrg = $organizacoes->random();

                $plano = PlanoDeAcao::create([
                    'cod_objetivo' => $objetivo->cod_objetivo,
                    'cod_tipo_execucao' => $tipos->random()->cod_tipo_execucao,
                    'cod_organizacao' => $targetOrg->cod_organizacao,
                    'dsc_plano_de_acao' => $nomePlano,
                    'dte_inicio' => '2026-01-01',
                    'dte_fim' => '2026-12-31',
                    'vlr_orcamento_previsto' => rand(1000000, 50000000),
                    'bln_status' => $statuses[rand(0, 5)],
                    'num_nivel_hierarquico_apresentacao' => 3
                ]);

                DB::table('action_plan.rel_plano_organizacao')->insert([
                    'cod_plano_de_acao' => $plano->cod_plano_de_acao,
                    'cod_organizacao' => $targetOrg->cod_organizacao,
                ]);

                // 3. Tarefas
                $tarefasNomes = $this->getTarefasForPlano($tema);
                foreach ($tarefasNomes as $idx => $nomeTarefa) {
                    $entrega = Entrega::create([
                        'cod_plano_de_acao' => $plano->cod_plano_de_acao,
                        'dsc_entrega' => $nomeTarefa,
                        'dte_prazo' => "2026-0" . ($idx + 3) . "-15",
                        'bln_status' => $statuses[rand(0, 5)],
                        'dsc_periodo_medicao' => 'Mensal',
                        'num_nivel_hierarquico_apresentacao' => $idx + 1,
                        'num_peso' => 25,
                    ]);
                    
                    if ($usuarios->isNotEmpty()) {
                        $entrega->responsaveis()->attach($usuarios->random()->id);
                    }
                }
            }
        }
    }

    private function matchTema($nome): string
    {
        $n = mb_strtolower($nome);
        if (str_contains($n, 'hídric') || str_contains($n, 'água')) return 'Hídrica';
        if (str_contains($n, 'desastre') || str_contains($n, 'defesa civil')) return 'Defesa Civil';
        return 'Regional';
    }

    private function getKPIsForTema($tema): array
    {
        return match($tema) {
            'Hídrica' => [['nom' => 'Segurança Hídrica', 'dsc' => 'ISH', 'und' => 'Índice', 'base' => 0.4, 'meta' => 0.7]],
            'Defesa Civil' => [['nom' => 'Tempo de Resposta', 'dsc' => 'Horas', 'und' => 'Horas', 'base' => 48, 'meta' => 12]],
            default => [['nom' => 'Eficiência Regional', 'dsc' => 'Índice', 'und' => 'Índice', 'base' => 0.3, 'meta' => 0.6]],
        };
    }

    private function getPlanosForTema($tema): array
    {
        return match($tema) {
            'Hídrica' => ['Projeto Rio São Francisco 2026', 'Barragens Setoriais 2026'],
            'Defesa Civil' => ['Modernização CENAD 2026', 'Planos de Resiliência 2026'],
            default => ['Rotas de Integração 2026'],
        };
    }

    private function getTarefasForPlano($tema): array
    {
        return ['Planejamento 2026', 'Execução Técnica', 'Homologação', 'Entrega Final'];
    }
}
