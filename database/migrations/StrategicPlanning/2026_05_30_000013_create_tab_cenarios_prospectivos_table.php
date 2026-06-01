<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strategic_planning.tab_cenarios_prospectivos', function (Blueprint $table) {
            $table->uuid('cod_cenario')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_pei')
                  ->references('cod_pei')
                  ->on('strategic_planning.tab_pei')
                  ->cascadeOnDelete();
            $table->foreignUuid('cod_organizacao')->nullable()
                  ->references('cod_organizacao')
                  ->on('organization.tab_organizacoes')
                  ->cascadeOnDelete();
            $table->string('nom_cenario', 150);
            $table->string('dsc_tipo', 20)->default('Tendencial'); // Otimista, Tendencial, Pessimista
            $table->text('dsc_descricao')->nullable();
            $table->text('txt_implicacoes')->nullable();
            $table->text('txt_resposta_estrategica')->nullable();
            $table->smallInteger('num_probabilidade')->default(3); // 1-5
            $table->smallInteger('num_impacto')->default(3);       // 1-5
            $table->smallInteger('num_ordem')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('cod_pei');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strategic_planning.tab_cenarios_prospectivos');
    }
};
