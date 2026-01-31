<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Renomear tabela
        if (Schema::hasTable('strategic_planning.tab_objetivo_estrategico')) {
            Schema::rename('strategic_planning.tab_objetivo_estrategico', 'tab_tema_norteador');
        }

        // Renomear colunas
        Schema::table('strategic_planning.tab_tema_norteador', function (Blueprint $table) {
            $table->renameColumn('cod_objetivo_estrategico', 'cod_tema_norteador');
            $table->renameColumn('nom_objetivo_estrategico', 'nom_tema_norteador');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('strategic_planning.tab_tema_norteador', function (Blueprint $table) {
            $table->renameColumn('cod_tema_norteador', 'cod_objetivo_estrategico');
            $table->renameColumn('nom_tema_norteador', 'nom_objetivo_estrategico');
        });

        if (Schema::hasTable('strategic_planning.tab_tema_norteador')) {
            Schema::rename('strategic_planning.tab_tema_norteador', 'tab_objetivo_estrategico');
        }
    }
};
