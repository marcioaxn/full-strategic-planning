<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * O tipo "Novo Plano" do encaminhamento da RAE foi renomeado no código para
 * "Nova Iniciativa" em 05/09/2026 (RaeEncaminhamento::TIPOS), mas a regra
 * CHECK da tabela continuou exigindo "Novo Plano": escolher a primeira opção
 * da tela dava erro 500 (achado no teste pelo navegador de 04/10/2026).
 *
 * Converte as linhas antigas e troca a regra. Compatível com PostgreSQL 9.3
 * (UPDATE, DROP CONSTRAINT IF EXISTS, ADD CONSTRAINT CHECK).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE strategic_planning.tab_rae_encaminhamento SET dsc_tipo = 'Nova Iniciativa' WHERE dsc_tipo = 'Novo Plano'");
        DB::statement('ALTER TABLE strategic_planning.tab_rae_encaminhamento DROP CONSTRAINT IF EXISTS tab_rae_encaminhamento_dsc_tipo_check');
        DB::statement("ALTER TABLE strategic_planning.tab_rae_encaminhamento ADD CONSTRAINT tab_rae_encaminhamento_dsc_tipo_check
            CHECK (dsc_tipo IN ('Nova Iniciativa', 'Revisão de Meta', 'Revisão de Objetivo', 'Revisão de Risco', 'Outro'))");
    }

    public function down(): void
    {
        DB::statement("UPDATE strategic_planning.tab_rae_encaminhamento SET dsc_tipo = 'Novo Plano' WHERE dsc_tipo = 'Nova Iniciativa'");
        DB::statement('ALTER TABLE strategic_planning.tab_rae_encaminhamento DROP CONSTRAINT IF EXISTS tab_rae_encaminhamento_dsc_tipo_check');
        DB::statement("ALTER TABLE strategic_planning.tab_rae_encaminhamento ADD CONSTRAINT tab_rae_encaminhamento_dsc_tipo_check
            CHECK (dsc_tipo IN ('Novo Plano', 'Revisão de Meta', 'Revisão de Objetivo', 'Revisão de Risco', 'Outro'))");
    }
};
