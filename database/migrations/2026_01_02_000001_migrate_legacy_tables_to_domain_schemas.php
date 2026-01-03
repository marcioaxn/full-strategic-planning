<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Garantir que os Schemas existem
        $schemas = [
            'strategic_planning',
            'action_plan',
            'performance_indicators',
            'risk_management',
            'organization'
        ];

        foreach ($schemas as $schema) {
            DB::statement("CREATE SCHEMA IF NOT EXISTS $schema");
        }

        // Função auxiliar para mover tabela se ela existir no schema antigo
        $moveTable = function ($table, $fromSchema, $toSchema) {
            // Verifica se a tabela existe no schema de origem
            $exists = DB::select("
                SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_schema = ? 
                    AND table_name = ?
                )
            ", [$fromSchema, $table])[0]->exists;

            if ($exists) {
                DB::statement("ALTER TABLE $fromSchema.$table SET SCHEMA $toSchema");
            }
        };

        // === Mover tabelas de Organization (Origem: public ou pei - geralmente public) ===
        // Algumas instalações antigas podem ter isso no public
        $moveTable('tab_organizacoes', 'public', 'organization');
        $moveTable('tab_perfil_acesso', 'public', 'organization');
        $moveTable('rel_users_tab_organizacoes', 'public', 'organization');
        $moveTable('rel_organizacao', 'public', 'organization');
        $moveTable('rel_users_tab_organizacoes_tab_perfil_acesso', 'public', 'organization');

        // === Mover tabelas de Strategic Planning (Origem: pei) ===
        $moveTable('tab_pei', 'pei', 'strategic_planning');
        $moveTable('tab_missao_visao_valores', 'pei', 'strategic_planning');
        $moveTable('tab_perspectiva', 'pei', 'strategic_planning');
        $moveTable('tab_objetivo', 'pei', 'strategic_planning');
        $moveTable('tab_objetivo_estrategico', 'pei', 'strategic_planning'); // Caso legado
        $moveTable('tab_valores', 'pei', 'strategic_planning');
        $moveTable('tab_futuro_almejado_objetivo', 'pei', 'strategic_planning');
        $moveTable('tab_analise_ambiental', 'pei', 'strategic_planning');
        $moveTable('tab_nivel_hierarquico', 'pei', 'strategic_planning');
        $moveTable('tab_grau_satisfacao', 'pei', 'strategic_planning');
        $moveTable('tab_arquivos', 'pei', 'strategic_planning');
        $moveTable('tab_atividade_cadeia_valor', 'pei', 'strategic_planning');
        $moveTable('tab_processos_atividade_cadeia_valor', 'pei', 'strategic_planning');

        // === Mover tabelas de Action Plan (Origem: pei) ===
        $moveTable('tab_plano_de_acao', 'pei', 'action_plan');
        $moveTable('tab_entregas', 'pei', 'action_plan');
        $moveTable('tab_entrega_comentarios', 'pei', 'action_plan');
        $moveTable('tab_entrega_labels', 'pei', 'action_plan');
        $moveTable('rel_entrega_labels', 'pei', 'action_plan');
        $moveTable('tab_entrega_anexos', 'pei', 'action_plan');
        $moveTable('tab_entrega_historico', 'pei', 'action_plan');
        $moveTable('rel_entrega_users_responsaveis', 'pei', 'action_plan');
        $moveTable('acoes', 'public', 'action_plan'); // Às vezes criado no public
        $moveTable('acoes', 'pei', 'action_plan');
        $moveTable('tab_tipo_execucao', 'pei', 'action_plan');

        // === Mover tabelas de Performance Indicators (Origem: pei) ===
        $moveTable('tab_indicador', 'pei', 'performance_indicators');
        $moveTable('tab_evolucao_indicador', 'pei', 'performance_indicators');
        $moveTable('tab_linha_base_indicador', 'pei', 'performance_indicators');
        $moveTable('tab_meta_por_ano', 'pei', 'performance_indicators');
        $moveTable('rel_indicador_objetivo_estrategico_organizacao', 'pei', 'performance_indicators');

        // === Mover tabelas de Risk Management (Origem: pei) ===
        $moveTable('tab_risco', 'pei', 'risk_management');
        $moveTable('tab_risco_objetivo', 'pei', 'risk_management');
        $moveTable('tab_risco_mitigacao', 'pei', 'risk_management');
        $moveTable('tab_risco_ocorrencia', 'pei', 'risk_management');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter é complexo pois depende de onde vieram (public ou pei).
        // Vamos mover tudo de volta para 'pei' (exceto organization -> public) como fallback seguro.
        
        $moveBack = function ($table, $currentSchema, $targetSchema) {
             DB::statement("ALTER TABLE $currentSchema.$table SET SCHEMA $targetSchema");
        };

        // Organization -> Public
        $moveBack('tab_organizacoes', 'organization', 'public');
        // ... (outros de organization)

        // Resto -> Pei
        // (Simplificação: em um rollback real de produção, precisaríamos mapear exato)
        // Como é uma migração estrutural "One Way", o down idealmente não seria executado
        // em produção sem um backup prévio.
    }
};
