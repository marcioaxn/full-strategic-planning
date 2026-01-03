<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verifica se a tabela existe no schema pei
        $exists = DB::select("
            SELECT EXISTS (
                SELECT FROM information_schema.tables
                WHERE table_schema = 'pei'
                AND table_name = 'rel_indicador_objetivo_organizacao'
            )
        ")[0]->exists;

        if ($exists) {
            // Move a tabela do schema pei para performance_indicators
            DB::statement("ALTER TABLE pei.rel_indicador_objetivo_organizacao SET SCHEMA performance_indicators");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Verifica se a tabela existe no schema performance_indicators
        $exists = DB::select("
            SELECT EXISTS (
                SELECT FROM information_schema.tables
                WHERE table_schema = 'performance_indicators'
                AND table_name = 'rel_indicador_objetivo_organizacao'
            )
        ")[0]->exists;

        if ($exists) {
            // Move a tabela de volta para o schema pei
            DB::statement("ALTER TABLE performance_indicators.rel_indicador_objetivo_organizacao SET SCHEMA pei");
        }
    }
};
