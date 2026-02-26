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
        Schema::create('tab_analise_ambiental', function (Blueprint $table) {
            $table->uuid('cod_analise')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_pei')->references('cod_pei')->on('tab_pei')->cascadeOnDelete();
            $table->foreignUuid('cod_organizacao')->nullable()->references('cod_organizacao')->on('organization.tab_organizacoes')->cascadeOnDelete();

            // Tipo de análise: SWOT ou PESTEL
            $table->string('dsc_tipo_analise', 10); // SWOT, PESTEL

            // Categoria dentro do tipo
            // SWOT: Força, Fraqueza, Oportunidade, Ameaça
            // PESTEL: Político, Econômico, Social, Tecnológico, Ambiental, Legal
            $table->string('dsc_categoria', 20);

            // Descrição do item
            $table->string('dsc_item', 500);

            // Impacto/Relevância (1-5)
            $table->integer('num_impacto')->default(3);

            // Observações adicionais
            $table->text('txt_observacao')->nullable();

            // Ordenação
            $table->integer('num_ordem')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('cod_pei');
            $table->index('cod_organizacao');
            $table->index(['dsc_tipo_analise', 'dsc_categoria']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tab_analise_ambiental');
    }
};
