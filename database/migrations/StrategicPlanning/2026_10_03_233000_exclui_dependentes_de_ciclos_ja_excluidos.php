<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ciclo PEI excluído deixava vivos a identidade, as perspectivas, os objetivos,
 * as iniciativas, os indicadores, os riscos e as análises dele (achado no
 * teste pelo navegador de 03/10/2026). O modelo PEI passou a excluir tudo junto;
 * esta migration aplica a mesma regra ao que já estava assim na base.
 *
 * Só exclusão LÓGICA (deleted_at): nada é apagado de fato. Mesma lista de
 * tabelas de PEI::TABELAS_DO_CICLO, escrita aqui por extenso para a migration
 * não depender do código do modelo. UPDATE ... FROM/IN: compatível com PG 9.3.
 */
return new class extends Migration
{
    public function up(): void
    {
        $ciclos = 'SELECT cod_pei FROM strategic_planning.tab_pei WHERE deleted_at IS NOT NULL';
        $objetivos = "SELECT o.cod_objetivo FROM strategic_planning.tab_objetivo o
                       JOIN strategic_planning.tab_perspectiva p ON p.cod_perspectiva = o.cod_perspectiva
                      WHERE p.cod_pei IN ({$ciclos})";
        $planos = "SELECT cod_plano_de_acao FROM action_plan.tab_plano_de_acao WHERE cod_objetivo IN ({$objetivos})";

        $marcar = fn (string $tabela, string $condicao) => DB::statement(
            "UPDATE {$tabela} SET deleted_at = NOW(), updated_at = NOW() WHERE deleted_at IS NULL AND {$condicao}"
        );

        // Iniciativas dos objetivos do ciclo, e o que é delas (mesma regra da exclusão de iniciativa).
        $marcar('action_plan.tab_entregas', "cod_plano_de_acao IN ({$planos})");
        $marcar('performance_indicators.tab_indicador', "cod_plano_de_acao IN ({$planos})");
        $marcar('organization.rel_users_tab_organizacoes_tab_perfil_acesso', "cod_plano_de_acao IN ({$planos})");
        $marcar('action_plan.tab_plano_de_acao', "cod_objetivo IN ({$objetivos})");

        // Objetivos e o que pende deles.
        $marcar('performance_indicators.tab_indicador', "cod_objetivo IN ({$objetivos})");
        $marcar('strategic_planning.tab_futuro_almejado_objetivo', "cod_objetivo IN ({$objetivos})");
        $marcar('strategic_planning.tab_objetivo_comentarios', "cod_objetivo IN ({$objetivos})");
        $marcar('strategic_planning.tab_objetivo', "cod_objetivo IN ({$objetivos})");

        // Filhos de tabelas do ciclo.
        $marcar('strategic_planning.tab_processos_atividade_cadeia_valor',
            "cod_atividade_cadeia_valor IN (SELECT cod_atividade_cadeia_valor FROM strategic_planning.tab_atividade_cadeia_valor WHERE cod_pei IN ({$ciclos}))");
        $marcar('strategic_planning.tab_rae_encaminhamento',
            "cod_rae IN (SELECT cod_rae FROM strategic_planning.tab_rae WHERE cod_pei IN ({$ciclos}))");
        $marcar('risk_management.tab_risco_mitigacao',
            "cod_risco IN (SELECT cod_risco FROM risk_management.tab_risco WHERE cod_pei IN ({$ciclos}))");
        $marcar('risk_management.tab_risco_ocorrencia',
            "cod_risco IN (SELECT cod_risco FROM risk_management.tab_risco WHERE cod_pei IN ({$ciclos}))");

        foreach ([
            'strategic_planning.tab_perspectiva',
            'strategic_planning.tab_missao_visao_valores',
            'strategic_planning.tab_valores',
            'strategic_planning.tab_tema_norteador',
            'strategic_planning.tab_grau_satisfacao',
            'strategic_planning.tab_analise_ambiental',
            'strategic_planning.tab_estrategia_tows',
            'strategic_planning.tab_partes_interessadas',
            'strategic_planning.tab_cenarios_prospectivos',
            'strategic_planning.tab_atividade_cadeia_valor',
            'strategic_planning.tab_inaugurar_pei',
            'strategic_planning.tab_integracao_instrumentos',
            'strategic_planning.tab_calendario_eventos_pei',
            'strategic_planning.tab_rae',
            'risk_management.tab_risco',
        ] as $tabela) {
            $marcar($tabela, "cod_pei IN ({$ciclos})");
        }
    }

    public function down(): void
    {
        // Irreversível de propósito: não há como separar o que já estava excluído.
    }
};
