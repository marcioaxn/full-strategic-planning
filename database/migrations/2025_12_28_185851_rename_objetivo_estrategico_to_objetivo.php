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
        DB::statement('ALTER TABLE tab_objetivo_estrategico RENAME TO tab_objetivo');
        DB::statement('ALTER TABLE tab_futuro_almejado_objetivo_estrategico RENAME TO tab_futuro_almejado_objetivo');
        DB::statement('ALTER TABLE rel_indicador_objetivo_estrategico_organizacao RENAME TO rel_indicador_objetivo_organizacao');

        // As colunas já possuem os nomes corretos, não é necessário renomeá-las
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter Tabelas
        DB::statement('ALTER TABLE rel_indicador_objetivo_organizacao RENAME TO rel_indicador_objetivo_estrategico_organizacao');
        DB::statement('ALTER TABLE tab_futuro_almejado_objetivo RENAME TO tab_futuro_almejado_objetivo_estrategico');
        DB::statement('ALTER TABLE tab_objetivo RENAME TO tab_objetivo_estrategico');
    }
};
