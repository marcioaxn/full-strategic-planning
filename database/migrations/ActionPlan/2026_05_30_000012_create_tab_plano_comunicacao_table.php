<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_plan.tab_plano_comunicacao', function (Blueprint $table) {
            $table->uuid('cod_comunicacao')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_plano_de_acao')
                  ->references('cod_plano_de_acao')
                  ->on('action_plan.tab_plano_de_acao')
                  ->cascadeOnDelete();
            $table->string('nom_publico_alvo', 150);
            $table->string('dsc_mensagem_chave', 500);
            $table->string('dsc_canal', 100)->default('E-mail');
            $table->string('dsc_frequencia', 50)->default('Mensal');
            $table->string('nom_responsavel', 100)->nullable();
            $table->smallInteger('num_ordem')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('cod_plano_de_acao');
        });
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS "action_plan"."tab_plano_comunicacao" CASCADE');
    }
};
