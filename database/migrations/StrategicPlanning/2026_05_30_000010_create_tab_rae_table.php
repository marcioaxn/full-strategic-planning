<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strategic_planning.tab_rae', function (Blueprint $table) {
            $table->uuid('cod_rae')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_pei')
                  ->references('cod_pei')
                  ->on('strategic_planning.tab_pei')
                  ->cascadeOnDelete();
            $table->foreignUuid('cod_organizacao')
                  ->references('cod_organizacao')
                  ->on('organization.tab_organizacoes')
                  ->cascadeOnDelete();
            $table->date('dte_referencia');
            $table->date('dte_reuniao')->nullable();
            $table->text('txt_destaques_positivos')->nullable();
            $table->text('txt_problemas_identificados')->nullable();
            $table->text('txt_encaminhamentos')->nullable();
            $table->jsonb('json_participantes')->nullable();
            $table->decimal('num_progresso_geral', 5, 2)->nullable();
            $table->string('dsc_tipo_reuniao', 30)->default('RAE');
            $table->timestamps();
            $table->softDeletes();

            $table->index('cod_pei');
            $table->index('cod_organizacao');
            $table->index('dte_referencia');
        });
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS "strategic_planning"."tab_rae" CASCADE');
    }
};
