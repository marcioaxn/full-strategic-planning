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
        Schema::create('performance_indicators.tab_indicador', function (Blueprint $table) {
            $table->uuid('cod_indicador')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_plano_de_acao')->nullable()->references('cod_plano_de_acao')->on('pei.tab_plano_de_acao')->cascadeOnDelete();
            $table->foreignUuid('cod_objetivo')->nullable()->references('cod_objetivo')->on('pei.tab_objetivo_estrategico')->cascadeOnDelete();
            $table->text('dsc_tipo')->nullable(false);
            $table->text('nom_indicador')->nullable(false);
            $table->text('dsc_indicador')->nullable(false);
            $table->text('txt_observacao')->nullable(true);
            $table->text('dsc_meta')->nullable(true);
            $table->text('dsc_atributos')->nullable(true);
            $table->text('dsc_referencial_comparativo')->nullable(true);
            $table->text('dsc_unidade_medida')->nullable(false);
            $table->smallInteger('num_peso')->nullable(true);
            $table->string('bln_acumulado')->nullable(false);
            $table->text('dsc_formula')->nullable(true);
            $table->string('dsc_fonte')->nullable(true);
            $table->string('dsc_periodo_medicao')->nullable(false);
            $table->timestamps();
            $table->softDeletes();

            // Índices para performance
            $table->index('cod_plano_de_acao');
            $table->index('cod_objetivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pei.tab_indicador');
    }
};
