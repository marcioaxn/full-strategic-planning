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
        Schema::create('tab_processos_atividade_cadeia_valor', function (Blueprint $table) {
            $table->uuid('cod_processo_atividade_cadeia_valor')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_atividade_cadeia_valor')->references('cod_atividade_cadeia_valor')->on('tab_atividade_cadeia_valor')->cascadeOnDelete();
            $table->text('dsc_entrada')->nullable(false);
            $table->text('dsc_transformacao')->nullable(false);
            $table->text('dsc_saida')->nullable(false);
            $table->timestamps();
            $table->softDeletes();

            // Índice para performance
            $table->index('cod_atividade_cadeia_valor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tab_processos_atividade_cadeia_valor');
    }
};
