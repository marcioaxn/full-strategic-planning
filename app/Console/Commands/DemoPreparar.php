<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DemoPreparar extends Command
{
    protected $signature = 'demo:preparar
                            {--nivel=3 : Nível da demo (2=semi-preenchida, 3=completa)}
                            {--force : Não pede confirmação}';

    protected $description = 'Prepara o banco para a demo de apresentação. Zera domínio estratégico e popula dados de demo.';

    // Tabelas de domínio a zerar, em ordem que respeita FKs (filhos antes dos pais)
    private array $tabelasDominio = [
        // Risk Management
        'risk_management.tab_risco_ocorrencia',
        'risk_management.tab_risco_mitigacao',
        'risk_management.tab_risco_objetivo',
        'risk_management.tab_risco',
        // Action Plan
        'action_plan.tab_entrega_historico',
        'action_plan.tab_entrega_comentarios',
        'action_plan.tab_entrega_anexos',
        'action_plan.rel_entrega_labels',
        'action_plan.rel_entrega_users_responsaveis',
        'action_plan.tab_entregas',
        'action_plan.tab_entrega_labels',
        'action_plan.tab_raci',
        'action_plan.tab_licoes_aprendidas',
        'action_plan.tab_plano_comunicacao',
        'action_plan.rel_plano_organizacao',
        'action_plan.tab_plano_de_acao',
        // Performance Indicators
        'performance_indicators.rel_indicador_objetivo_organizacao',
        'performance_indicators.tab_evolucao_indicador',
        'performance_indicators.tab_meta_por_ano',
        'performance_indicators.tab_linha_base_indicador',
        'performance_indicators.tab_indicador',
        // Strategic Planning (filhos antes dos pais)
        'strategic_planning.rel_objetivo_ods',
        'strategic_planning.rel_pei_ods',
        'strategic_planning.tab_objetivo_comentarios',
        'strategic_planning.tab_objetivo',
        'strategic_planning.tab_perspectiva',
        'strategic_planning.tab_analise_ambiental',
        'strategic_planning.tab_partes_interessadas',
        'strategic_planning.tab_cenarios_prospectivos',
        'strategic_planning.tab_rae',
        'strategic_planning.tab_raci',
        'strategic_planning.tab_calendario_eventos_pei',
        'strategic_planning.tab_integracao_instrumentos',
        'strategic_planning.tab_inaugurar_pei',
        'strategic_planning.tab_missao_visao_valores',
        'strategic_planning.tab_valores',
        'strategic_planning.tab_grau_satisfacao',
        'strategic_planning.tab_tema_norteador',
        'strategic_planning.tab_futuro_almejado_objetivo',
        'strategic_planning.tab_pei',
    ];

    public function handle(): int
    {
        $nivel = (int) $this->option('nivel');

        if (! in_array($nivel, [2, 3])) {
            $this->error('Nível inválido. Use --nivel=2 ou --nivel=3.');
            return 1;
        }

        $descricaoNivel = $nivel === 2
            ? 'Nível 2 — Semi-preenchida (50% automático, 50% ao vivo)'
            : 'Nível 3 — Completa (95% automático, passeio guiado)';

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════╗');
        $this->info('║       PREPARAÇÃO DE DEMO — SISTEMA PEI               ║');
        $this->info('╚══════════════════════════════════════════════════════╝');
        $this->info('');
        $this->line("  Nível selecionado: <comment>{$descricaoNivel}</comment>");
        $this->line('  Ação: zerará tabelas de domínio estratégico e populará dados de demo.');
        $this->line('  Tabelas preservadas: users, tab_organizacoes, tab_perfil_acesso, pivots de usuário.');
        $this->info('');

        if (! $this->option('force')) {
            if (! $this->confirm('Confirma a preparação da demo? Esta ação apaga dados de planejamento existentes.')) {
                $this->info('Operação cancelada.');
                return 0;
            }
        }

        $this->info('');
        $this->line('  <info>1/3</info> Zerando tabelas de domínio...');
        $this->zerarDominio();

        $this->line('  <info>2/3</info> Populando dados de demo...');
        $this->popularDemo($nivel);

        $this->line('  <info>3/3</info> Garantindo usuário de demo...');
        $this->call('db:seed', ['--class' => \Database\Seeders\Demo\DemoUsuarioSeeder::class]);

        $this->line('  <info>4/4</info> Verificando resultado...');
        $this->verificar();

        $this->info('');
        $this->info('✅ Demo preparada com sucesso!');
        $this->info('');
        $this->table(['Credencial', 'Valor'], [
            ['E-mail', \Database\Seeders\Demo\DemoUsuarioSeeder::EMAIL],
            ['Senha',  \Database\Seeders\Demo\DemoUsuarioSeeder::SENHA],
        ]);
        $this->info('');

        if ($nivel === 2) {
            $this->table(
                ['O que está pronto', 'O que fazer ao vivo'],
                [
                    ['PEI (ciclo 2025–2028)', '—'],
                    ['Identidade (missão, visão, valores)', '—'],
                    ['Perspectivas BSC (4)', '—'],
                    ['Graus de satisfação (farois)', '—'],
                    ['—', 'Objetivos estratégicos (8)'],
                    ['—', 'PESTEL (6 fatores)'],
                    ['—', 'Riscos (4) + Matriz'],
                ]
            );
            $this->info('  Acesse /objetivos para iniciar a parte ao vivo.');
        } else {
            $this->table(
                ['Item', 'Qtd'],
                [
                    ['PEI', '1'],
                    ['Identidade (missão, visão, valores)', '1 + 3'],
                    ['Perspectivas BSC', '4'],
                    ['Graus de satisfação', '3'],
                    ['Objetivos estratégicos', '8'],
                    ['Fatores PESTEL', '6'],
                    ['Riscos', '4'],
                ]
            );
            $this->info('  Acesse /pei/mapa para iniciar o passeio guiado.');
        }

        $this->info('');
        return 0;
    }

    private function zerarDominio(): bool
    {
        DB::statement('SET session_replication_role = replica;');

        foreach ($this->tabelasDominio as $tabela) {
            try {
                DB::table($tabela)->delete();
            } catch (\Throwable) {
                // Tabela pode não existir ainda (migrations novas) — ignorar
            }
        }

        DB::statement('SET session_replication_role = DEFAULT;');
        return true;
    }

    private function popularDemo(int $nivel): bool
    {
        $seeder = $nivel === 2
            ? \Database\Seeders\Demo\DemoNivel2Seeder::class
            : \Database\Seeders\Demo\DemoNivel3Seeder::class;

        $this->callSilently('db:seed', ['--class' => $seeder]);
        return true;
    }

    private function verificar(): bool
    {
        $peiCount = DB::table('strategic_planning.tab_pei')->count();
        return $peiCount > 0;
    }
}
