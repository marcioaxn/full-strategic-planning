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
        // Criar schema PEI para tabelas de Planejamento Estratégico Institucional
        DB::statement('CREATE SCHEMA IF NOT EXISTS pei;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover schema PEI (CASCADE para remover todas as tabelas dentro dele)
        DB::statement('DROP SCHEMA IF EXISTS pei CASCADE;');
    }
};
