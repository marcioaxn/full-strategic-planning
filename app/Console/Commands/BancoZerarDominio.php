<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BancoZerarDominio extends Command
{
    protected $signature = 'banco:zerar-dominio
                            {--force : Não pede confirmação}';

    protected $description = 'Limpeza profunda: apaga todo o conteúdo de planejamento estratégico e operacional,
                             preservando apenas usuários, organizações, vínculos usuário-organização e configuração do agente de IA.';

    // Preservadas (NÃO são tocadas):
    //   pei.users
    //   pei.password_reset_tokens
    //   pei.sessions
    //   pei.cache / pei.cache_locks
    //   pei.jobs / pei.job_batches / pei.failed_jobs
    //   pei.personal_access_tokens
    //   pei.tab_audit / pei.audits
    //   pei.tab_status
    //   pei.system_settings          ← configuração do agente de IA
    //   pei.strategic_alerts
    //   pei.tab_relatorios_agendados / pei.tab_relatorios_gerados
    //   organization.tab_organizacoes
    //   organization.tab_perfil_acesso
    //   organization.rel_users_tab_organizacoes
    //   organization.rel_users_tab_organizacoes_tab_perfil_acesso
    //   organization.rel_organizacao
    //   strategic_planning.tab_nivel_hierarquico  ← tabela de lookup
    //   strategic_planning.tab_ods                ← tabela de lookup
    //   action_plan.tab_tipo_execucao             ← tabela de lookup

    private array $tabelasDominio = [
        // ── Risk Management ──────────────────────────────────────────────
        'risk_management.tab_risco_ocorrencia',
        'risk_management.tab_risco_mitigacao',
        'risk_management.tab_risco_objetivo',
        'risk_management.tab_risco',

        // ── Action Plan ───────────────────────────────────────────────────
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
        'action_plan.acoes',

        // ── Performance Indicators ────────────────────────────────────────
        'performance_indicators.rel_indicador_plano_de_acao',
        'performance_indicators.rel_indicador_objetivo_organizacao',
        'performance_indicators.tab_evolucao_indicador',
        'performance_indicators.tab_meta_por_ano',
        'performance_indicators.tab_linha_base_indicador',
        'performance_indicators.tab_indicador',

        // ── Strategic Planning — filhos antes dos pais ────────────────────
        'strategic_planning.rel_objetivo_ods',
        'strategic_planning.rel_pei_ods',
        'strategic_planning.tab_objetivo_comentarios',
        'strategic_planning.tab_futuro_almejado_objetivo_estrategico',
        'strategic_planning.tab_objetivo',
        'strategic_planning.tab_rae_encaminhamento',
        'strategic_planning.tab_rae_causa_raiz',
        'strategic_planning.tab_rae',
        'strategic_planning.tab_estrategia_tows',
        'strategic_planning.tab_analise_ambiental',
        'strategic_planning.tab_partes_interessadas',
        'strategic_planning.tab_cenarios_prospectivos',
        'strategic_planning.tab_calendario_eventos_pei',
        'strategic_planning.tab_integracao_instrumentos',
        'strategic_planning.tab_inaugurar_pei',
        'strategic_planning.tab_atividade_cadeia_valor',
        'strategic_planning.tab_processos_atividade_cadeia_valor',
        'strategic_planning.tab_arquivos',
        'strategic_planning.tab_perspectiva',
        'strategic_planning.tab_grau_satisfcao',
        'strategic_planning.tab_tema_norteador',
        'strategic_planning.tab_missao_visao_valores',
        'strategic_planning.tab_valores',
        'strategic_planning.tab_pei',
    ];

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════╗');
        $this->info('║       LIMPEZA PROFUNDA DE DOMÍNIO — SISTEMA PEI          ║');
        $this->info('╚══════════════════════════════════════════════════════════╝');
        $this->info('');
        $this->line('  <comment>O que será apagado:</comment>');
        $this->line('    • Todo o planejamento estratégico (PEI, ciclos, perspectivas, objetivos)');
        $this->line('    • Identidade estratégica (missão, visão, valores)');
        $this->line('    • Análise ambiental (SWOT/PESTEL), graus de satisfação');
        $this->line('    • Indicadores, metas, evoluções');
        $this->line('    • Planos de ação, entregas, RACI, lições aprendidas');
        $this->line('    • Riscos, mitigações, ocorrências');
        $this->line('    • RAE, estratégias TOWS, partes interessadas, cenários');
        $this->info('');
        $this->line('  <info>O que será preservado:</info>');
        $this->line('    • Usuários e senhas');
        $this->line('    • Organizações e hierarquia');
        $this->line('    • Vínculos usuário–organização–perfil');
        $this->line('    • Configuração do agente de IA (system_settings)');
        $this->line('    • Alertas estratégicos e relatórios agendados');
        $this->line('    • Tabelas de lookup (níveis hierárquicos, ODS, tipos de execução)');
        $this->info('');

        if (! $this->option('force')) {
            if (! $this->confirm('⚠️  Esta ação é IRREVERSÍVEL. Confirma a limpeza profunda do banco?', false)) {
                $this->info('Operação cancelada. Nenhum dado foi alterado.');

                return 0;
            }
        }

        $this->info('');

        $total = count($this->tabelasDominio);
        $apagadas = 0;
        $ignoradas = 0;

        $this->line('  Desabilitando restrições de FK...');
        DB::statement('SET session_replication_role = replica;');

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat('  %current%/%max% [%bar%] %percent:3s%% — <comment>%message%</comment>');
        $bar->start();

        foreach ($this->tabelasDominio as $tabela) {
            $bar->setMessage($tabela);
            try {
                $linhas = DB::table($tabela)->delete();
                $apagadas++;
            } catch (\Throwable) {
                // Tabela pode não existir (ambiente com migrations parciais)
                $ignoradas++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->info('');

        DB::statement('SET session_replication_role = DEFAULT;');
        $this->line('  Restrições de FK reativadas.');

        $this->info('');
        $this->info('✅ Limpeza concluída!');
        $this->info('');
        $this->table(
            ['Resultado', 'Quantidade'],
            [
                ['Tabelas limpas',   $apagadas],
                ['Tabelas ausentes (ignoradas)', $ignoradas],
                ['Total processado', $total],
            ]
        );
        $this->info('');
        $this->line('  O banco está pronto para um novo ciclo de planejamento.');
        $this->line('  Acesse <comment>/pei/ciclos</comment> para criar o primeiro PEI.');
        $this->info('');

        return 0;
    }
}
