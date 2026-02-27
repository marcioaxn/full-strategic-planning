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
        Schema::create('action_plan.tab_plano_de_acao', function (Blueprint $table) {
            $table->uuid('cod_plano_de_acao')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_objetivo')->references('cod_objetivo')->on('strategic_planning.tab_objetivo_estrategico')->cascadeOnDelete();
            $table->foreignUuid('cod_tipo_execucao')->references('cod_tipo_execucao')->on('action_plan.tab_tipo_execucao')->cascadeOnDelete();
            $table->foreignUuid('cod_organizacao')->references('cod_organizacao')->on('organization.tab_organizacoes')->cascadeOnDelete();
            $table->smallInteger('num_nivel_hierarquico_apresentacao')->nullable(false);
            $table->text('dsc_plano_de_acao')->nullable(false);
            $table->date('dte_inicio')->nullable(false);
            $table->date('dte_fim')->nullable(false);
            $table->decimal('vlr_orcamento_previsto', 15, 2)->nullable(true);
            $table->string('bln_status')->nullable(false);
            $table->string('cod_ppa')->nullable(true);
            $table->string('cod_loa')->nullable(true);
            $table->timestamps();
            $table->softDeletes();

            // Índices para melhorar performance
            $table->index('cod_objetivo');
            $table->index('cod_organizacao');
            $table->index('bln_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_plan.tab_plano_de_acao');
    }
};
