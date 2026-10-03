<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Aplica às iniciativas JÁ excluídas a regra que passou a valer em 03/10/2026:
 * excluir a iniciativa leva junto as entregas, os indicadores e os vínculos de
 * Gestor dela (ver PlanoDeAcao::booted).
 *
 * Antes, a exclusão marcava só a iniciativa. O que dependia dela ficava "vivo":
 * entrega e indicador órfãos e, pior, vínculo de Gestor ativo — a pessoa seguia
 * com o perfil na unidade sem iniciativa nenhuma. A base de desenvolvimento
 * tinha um caso assim.
 *
 * Exclusão LÓGICA (deleted_at), com a mesma data da exclusão da iniciativa:
 * nada é apagado de fato. UPDATE ... FROM, compatível com PostgreSQL 9.3.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE action_plan.tab_entregas e
               SET deleted_at = p.deleted_at, updated_at = NOW()
              FROM action_plan.tab_plano_de_acao p
             WHERE p.cod_plano_de_acao = e.cod_plano_de_acao
               AND p.deleted_at IS NOT NULL
               AND e.deleted_at IS NULL
        ');

        DB::statement('
            UPDATE performance_indicators.tab_indicador i
               SET deleted_at = p.deleted_at, updated_at = NOW()
              FROM action_plan.tab_plano_de_acao p
             WHERE p.cod_plano_de_acao = i.cod_plano_de_acao
               AND p.deleted_at IS NOT NULL
               AND i.deleted_at IS NULL
        ');

        DB::statement('
            UPDATE organization.rel_users_tab_organizacoes_tab_perfil_acesso r
               SET deleted_at = p.deleted_at, updated_at = NOW()
              FROM action_plan.tab_plano_de_acao p
             WHERE p.cod_plano_de_acao = r.cod_plano_de_acao
               AND p.deleted_at IS NOT NULL
               AND r.deleted_at IS NULL
        ');
    }

    public function down(): void
    {
        // Sem reversão automática: não há como distinguir, depois, o que esta
        // migration marcou do que já estava excluído antes dela.
    }
};
