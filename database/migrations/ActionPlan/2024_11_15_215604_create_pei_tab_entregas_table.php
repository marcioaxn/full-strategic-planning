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
        Schema::create('action_plan.tab_entregas', function (Blueprint $table) {
            $table->uuid('cod_entrega')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_plano_de_acao')
                  ->nullable()
                  ->references('cod_plano_de_acao')
                  ->on('action_plan.tab_plano_de_acao')
                  ->cascadeOnDelete();
            $table->text('dsc_entrega');
            $table->string('bln_status')->nullable(false);
            $table->string('dsc_periodo_medicao')->nullable(false);
            $table->smallInteger('num_nivel_hierarquico_apresentacao')->nullable(false);
            $table->timestamps();
            $table->softDeletes();

            // Índice para performance
            $table->index('cod_plano_de_acao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_plan.tab_entregas');
    }
};
