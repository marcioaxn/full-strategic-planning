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
        Schema::create('performance_indicators.tab_evolucao_indicador', function (Blueprint $table) {
            $table->uuid('cod_evolucao_indicador')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_indicador')->references('cod_indicador')->on('performance_indicators.tab_indicador')->cascadeOnDelete();
            $table->smallInteger('num_ano')->nullable(false);
            $table->smallInteger('num_mes')->nullable(false);
            $table->decimal('vlr_previsto', 15, 2)->nullable(true);
            $table->decimal('vlr_realizado', 15, 2)->nullable(true);
            $table->text('txt_avaliacao')->nullable(true);
            $table->string('bln_atualizado')->nullable(true);
            $table->timestamps();
            $table->softDeletes();

            // Índice composto para evitar duplicatas e melhorar performance
            $table->index(['cod_indicador', 'num_ano', 'num_mes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_indicators.tab_evolucao_indicador');
    }
};
