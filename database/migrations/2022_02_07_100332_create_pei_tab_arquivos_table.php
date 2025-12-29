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
        Schema::create('tab_arquivos', function (Blueprint $table) {
            $table->uuid('cod_arquivo')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_evolucao_indicador')->references('cod_evolucao_indicador')->on('tab_evolucao_indicador')->cascadeOnDelete();
            $table->text('txt_assunto')->nullable(false);
            $table->text('data')->nullable(false);
            $table->text('dsc_nome_arquivo')->nullable(false);
            $table->string('dsc_tipo')->nullable(false);
            $table->timestamps();
            $table->softDeletes();

            // Índice para performance
            $table->index('cod_evolucao_indicador');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tab_arquivos');
    }
};
