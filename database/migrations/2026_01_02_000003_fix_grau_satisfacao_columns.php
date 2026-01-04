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
        // Renomear a coluna da Chave Primária
        DB::statement("ALTER TABLE strategic_planning.tab_grau_satisfacao RENAME COLUMN cod_grau_satisfcao TO cod_grau_satisfacao");

        // Renomear a coluna de Descrição
        DB::statement("ALTER TABLE strategic_planning.tab_grau_satisfacao RENAME COLUMN dsc_grau_satisfcao TO dsc_grau_satisfacao");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE strategic_planning.tab_grau_satisfacao RENAME COLUMN cod_grau_satisfacao TO cod_grau_satisfcao");
        DB::statement("ALTER TABLE strategic_planning.tab_grau_satisfacao RENAME COLUMN dsc_grau_satisfacao TO dsc_grau_satisfcao");
    }
};
