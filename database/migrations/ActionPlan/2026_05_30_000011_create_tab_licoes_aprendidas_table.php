<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_plan.tab_licoes_aprendidas', function (Blueprint $table) {
            $table->uuid('cod_licao')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_plano_de_acao')
                  ->references('cod_plano_de_acao')
                  ->on('action_plan.tab_plano_de_acao')
                  ->cascadeOnDelete();
            $table->string('dsc_categoria', 50)->default('Geral');
            $table->string('dsc_tipo', 20)->default('Aprendizado');
            $table->text('txt_descricao');
            $table->text('txt_recomendacao')->nullable();
            $table->smallInteger('num_ordem')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('cod_plano_de_acao');
            $table->index('dsc_tipo');
        });
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS "action_plan"."tab_licoes_aprendidas" CASCADE');
    }
};
