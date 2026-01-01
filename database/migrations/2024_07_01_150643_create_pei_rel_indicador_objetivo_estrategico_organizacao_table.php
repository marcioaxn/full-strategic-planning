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
        Schema::create('pei.rel_indicador_objetivo_estrategico_organizacao', function (Blueprint $table) {
            $table->foreignUuid('cod_indicador')->references('cod_indicador')->on('pei.tab_indicador')->cascadeOnDelete();
            $table->foreignUuid('cod_organizacao')->references('cod_organizacao')->on('tab_organizacoes')->cascadeOnDelete();
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
        Schema::dropIfExists('pei.rel_indicador_objetivo_estrategico_organizacao');
    }
};
