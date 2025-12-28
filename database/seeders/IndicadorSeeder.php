<?php

namespace Database\Seeders;

use App\Models\PEI\Indicador;
use App\Models\PEI\ObjetivoEstrategico;
use App\Models\PEI\PlanoDeAcao;
use App\Models\PEI\PEI;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndicadorSeeder extends Seeder
{
    public function run(): void
    {
        $peiAtivo = PEI::ativos()->first();
        if (!$peiAtivo) {
            $this->command->warn('Nenhum PEI ativo encontrado.');
            return;
        }

        $objetivos = ObjetivoEstrategico::whereHas('perspectiva', function($q) use ($peiAtivo) {
            $q->where('cod_pei', $peiAtivo->cod_pei);
        })->get();
        $planos = PlanoDeAcao::whereIn('cod_objetivo_estrategico', $objetivos->pluck('cod_objetivo_estrategico'))->get();

        if ($objetivos->isEmpty()) {
            $this->command->warn('Nenhum objetivo encontrado.');
            return;
        }

        // Limpar indicadores existentes
        DB::table('tab_indicador')
            ->where(function($q) use ($objetivos, $planos) {
                $q->whereIn('cod_objetivo_estrategico', $objetivos->pluck('cod_objetivo_estrategico'))
                  ->orWhereIn('cod_plano_de_acao', $planos->pluck('cod_plano_de_acao'));
            })
            ->delete();

        $this->command->info('Criando Indicadores...');

        $indicadores = [];

        // Indicadores de Objetivo Estratégico (2-3 por objetivo)
        foreach ($objetivos as $objetivo) {
            $numIndicadores = rand(2, 3);

            for ($i = 1; $i <= $numIndicadores; $i++) {
                $indicadores[] = $this->criarIndicadorObjetivo($objetivo, $i);
            }
        }

        // Indicadores de Plano de Ação (1-2 por plano, 30% dos planos)
        $planosComIndicador = $planos->random(min($planos->count(), (int)($planos->count() * 0.3)));
        foreach ($planosComIndicador as $plano) {
            $numIndicadores = rand(1, 2);

            for ($i = 1; $i <= $numIndicadores; $i++) {
                $indicadores[] = $this->criarIndicadorPlano($plano, $i);
            }
        }

        // Inserir em lotes
        foreach (array_chunk($indicadores, 50) as $chunk) {
            Indicador::insert($chunk);
        }

        $this->command->info('✓ ' . count($indicadores) . ' Indicadores criados com sucesso!');
    }

    private function criarIndicadorObjetivo(ObjetivoEstrategico $objetivo, int $numero): array
    {
        $templates = [
            [
                'tipo' => 'Resultado',
                'nome' => 'Taxa de Implementacao',
                'descricao' => 'Percentual de implementacao das acoes previstas',
                'unidade' => '%',
                'formula' => '(Acoes Implementadas / Total de Acoes) * 100',
                'fonte' => 'Sistema de Gestao Estrategica',
                'periodo' => 'Trimestral',
                'acumulado' => 'Nao'
            ],
            [
                'tipo' => 'Eficiencia',
                'nome' => 'Indice de Eficiencia Operacional',
                'descricao' => 'Mede a eficiencia na execucao das atividades',
                'unidade' => 'Indice',
                'formula' => 'Resultado Realizado / Recurso Utilizado',
                'fonte' => 'Relatorios Gerenciais',
                'periodo' => 'Mensal',
                'acumulado' => 'Nao'
            ],
            [
                'tipo' => 'Efetividade',
                'nome' => 'Grau de Satisfacao',
                'descricao' => 'Nivel de satisfacao das partes interessadas',
                'unidade' => 'Pontos',
                'formula' => 'Media das Avaliacoes * 20',
                'fonte' => 'Pesquisa de Satisfacao',
                'periodo' => 'Semestral',
                'acumulado' => 'Nao'
            ],
            [
                'tipo' => 'Resultado',
                'nome' => 'Percentual de Conclusao',
                'descricao' => 'Taxa de conclusao das entregas do objetivo',
                'unidade' => '%',
                'formula' => '(Entregas Concluidas / Total Entregas) * 100',
                'fonte' => 'Modulo de Monitoramento',
                'periodo' => 'Mensal',
                'acumulado' => 'Sim'
            ],
        ];

        $template = $templates[($numero - 1) % count($templates)];

        return [
            'cod_objetivo_estrategico' => $objetivo->cod_objetivo_estrategico,
            'cod_plano_de_acao' => null,
            'dsc_tipo' => $template['tipo'],
            'nom_indicador' => $template['nome'] . ' - ' . mb_substr($objetivo->dsc_objetivo_estrategico, 0, 30),
            'dsc_indicador' => $template['descricao'],
            'txt_observacao' => 'Indicador vinculado ao objetivo estrategico para mensuracao de resultados',
            'dsc_meta' => 'Atingir ' . rand(70, 95) . '% ao final do ciclo',
            'dsc_atributos' => 'Especifico, Mensuravel, Atingivel, Relevante, Temporal',
            'dsc_referencial_comparativo' => 'Benchmark de mercado e historico interno',
            'dsc_unidade_medida' => $template['unidade'],
            'num_peso' => rand(1, 5),
            'bln_acumulado' => $template['acumulado'],
            'dsc_formula' => $template['formula'],
            'dsc_fonte' => $template['fonte'],
            'dsc_periodo_medicao' => $template['periodo'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function criarIndicadorPlano(PlanoDeAcao $plano, int $numero): array
    {
        $templates = [
            [
                'tipo' => 'Processo',
                'nome' => 'Taxa de Execucao Orcamentaria',
                'descricao' => 'Percentual do orcamento executado',
                'unidade' => '%',
                'formula' => '(Valor Executado / Orcamento Previsto) * 100',
                'fonte' => 'Sistema Financeiro',
                'periodo' => 'Mensal',
                'acumulado' => 'Sim'
            ],
            [
                'tipo' => 'Processo',
                'nome' => 'Prazo de Entrega',
                'descricao' => 'Percentual de entregas no prazo',
                'unidade' => '%',
                'formula' => '(Entregas no Prazo / Total de Entregas) * 100',
                'fonte' => 'Cronograma do Projeto',
                'periodo' => 'Mensal',
                'acumulado' => 'Nao'
            ],
        ];

        $template = $templates[($numero - 1) % count($templates)];

        return [
            'cod_objetivo_estrategico' => null,
            'cod_plano_de_acao' => $plano->cod_plano_de_acao,
            'dsc_tipo' => $template['tipo'],
            'nom_indicador' => $template['nome'] . ' - ' . mb_substr($plano->dsc_plano_de_acao, 0, 30),
            'dsc_indicador' => $template['descricao'],
            'txt_observacao' => 'Indicador de acompanhamento do plano de acao',
            'dsc_meta' => 'Atingir ' . rand(80, 100) . '%',
            'dsc_atributos' => 'Especifico, Mensuravel, Atingivel',
            'dsc_referencial_comparativo' => 'Historico de projetos similares',
            'dsc_unidade_medida' => $template['unidade'],
            'num_peso' => rand(1, 3),
            'bln_acumulado' => $template['acumulado'],
            'dsc_formula' => $template['formula'],
            'dsc_fonte' => $template['fonte'],
            'dsc_periodo_medicao' => $template['periodo'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
