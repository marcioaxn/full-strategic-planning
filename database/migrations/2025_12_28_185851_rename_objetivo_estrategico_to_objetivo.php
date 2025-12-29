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
        // 1. Renomear Tabelas (Usando DB::statement para garantir compatibilidade Postgres com schemas)
        DB::statement('ALTER TABLE pei.tab_objetivo_estrategico RENAME TO tab_objetivo');
        DB::statement('ALTER TABLE pei.tab_futuro_almejado_objetivo_estrategico RENAME TO tab_futuro_almejado_objetivo');
        DB::statement('ALTER TABLE pei.rel_indicador_objetivo_estrategico_organizacao RENAME TO rel_indicador_objetivo_organizacao');

        // 2. Renomear Colunas na tabela principal pei.tab_objetivo
        Schema::table('pei.tab_objetivo', function (Blueprint $table) {
            $table->renameColumn('cod_objetivo', 'cod_objetivo');
            $table->renameColumn('nom_objetivo', 'nom_objetivo');
            $table->renameColumn('dsc_objetivo', 'dsc_objetivo');
        });

        // 3. Renomear Colunas de FK em outras tabelas
        Schema::table('pei.tab_plano_de_acao', function (Blueprint $table) {
            $table->renameColumn('cod_objetivo', 'cod_objetivo');
        });

        Schema::table('pei.tab_indicador', function (Blueprint $table) {
            $table->renameColumn('cod_objetivo', 'cod_objetivo');
        });

        Schema::table('pei.tab_futuro_almejado_objetivo', function (Blueprint $table) {
            $table->renameColumn('cod_objetivo', 'cod_objetivo');
        });

        Schema::table('pei.tab_risco_objetivo', function (Blueprint $table) {
            $table->renameColumn('cod_objetivo', 'cod_objetivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Reverter Colunas de FK
        Schema::table('pei.tab_risco_objetivo', function (Blueprint $table) {
            $table->renameColumn('cod_objetivo', 'cod_objetivo');
        });

        Schema::table('pei.tab_futuro_almejado_objetivo', function (Blueprint $table) {
            $table->renameColumn('cod_objetivo', 'cod_objetivo');
        });

        Schema::table('pei.tab_indicador', function (Blueprint $table) {
            $table->renameColumn('cod_objetivo', 'cod_objetivo');
        });

        Schema::table('pei.tab_plano_de_acao', function (Blueprint $table) {
            $table->renameColumn('cod_objetivo', 'cod_objetivo');
        });

        // 2. Reverter Colunas na tabela pei.tab_objetivo
        Schema::table('pei.tab_objetivo', function (Blueprint $table) {
            $table->renameColumn('dsc_objetivo', 'dsc_objetivo');
            $table->renameColumn('nom_objetivo', 'nom_objetivo');
            $table->renameColumn('cod_objetivo', 'cod_objetivo');
        });

        // 3. Reverter Tabelas
        DB::statement('ALTER TABLE pei.rel_indicador_objetivo_organizacao RENAME TO rel_indicador_objetivo_estrategico_organizacao');
        DB::statement('ALTER TABLE pei.tab_futuro_almejado_objetivo RENAME TO tab_futuro_almejado_objetivo_estrategico');
        DB::statement('ALTER TABLE pei.tab_objetivo RENAME TO tab_objetivo_estrategico');
    }
};
