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
        Schema::create('performance_indicators.rel_indicador_objetivo_estrategico_organizacao', function (Blueprint $table) {
            $table->uuid('cod_indicador');
            $table->uuid('cod_organizacao');
            
            $table->foreign('cod_indicador', 'fk_rioo_indicador')->references('cod_indicador')->on('performance_indicators.tab_indicador')->cascadeOnDelete();
            $table->foreign('cod_organizacao', 'fk_rioo_org')->references('cod_organizacao')->on('organization.tab_organizacoes')->cascadeOnDelete();
            
            $table->timestamps();
            $table->softDeletes();

            // Chave primária composta
            $table->primary(['cod_indicador', 'cod_organizacao'], 'rel_ind_obj_org_pk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_indicators.rel_indicador_objetivo_estrategico_organizacao');
    }
};
