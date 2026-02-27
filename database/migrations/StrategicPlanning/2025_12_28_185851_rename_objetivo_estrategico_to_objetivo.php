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
        DB::statement('ALTER TABLE strategic_planning.tab_objetivo_estrategico RENAME TO tab_objetivo');
        DB::statement('ALTER TABLE strategic_planning.tab_futuro_almejado_objetivo_estrategico RENAME TO tab_futuro_almejado_objetivo');
        DB::statement('ALTER TABLE performance_indicators.rel_indicador_objetivo_estrategico_organizacao RENAME TO rel_indicador_objetivo_organizacao');

        // 2. Renomear Colunas na tabela principal strategic_planning.tab_objetivo
        // Removido pois já estão com os nomes corretos nas migrations iniciais ajustadas
        
        // 3. Renomear Colunas de FK em outras tabelas
        // Se já estão com os nomes corretos, não precisa renomear. 
        // Vamos apenas garantir que os schemas estão corretos se as colunas forem realmente diferentes.
        // Como ajustamos as migrations iniciais para usar 'cod_objetivo', estas renomeações são redundantes.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Reverter Tabelas
        DB::statement('ALTER TABLE performance_indicators.rel_indicador_objetivo_organizacao RENAME TO rel_indicador_objetivo_estrategico_organizacao');
        DB::statement('ALTER TABLE strategic_planning.tab_futuro_almejado_objetivo RENAME TO tab_futuro_almejado_objetivo_estrategico');
        DB::statement('ALTER TABLE strategic_planning.tab_objetivo RENAME TO tab_objetivo_estrategico');
    }
};
