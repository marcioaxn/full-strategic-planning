<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strategic_planning.tab_inaugurar_pei', function (Blueprint $table) {
            $table->uuid('cod_inaugurar')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_pei')
                  ->references('cod_pei')
                  ->on('strategic_planning.tab_pei')
                  ->cascadeOnDelete();
            $table->text('txt_equipe')->nullable();
            $table->text('txt_diretrizes')->nullable();
            $table->text('txt_metodologia')->nullable();
            $table->text('txt_observacoes')->nullable();
            $table->date('dte_inicio_processo')->nullable();
            $table->date('dte_fim_previsto')->nullable();
            $table->boolean('bln_aprovado')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('cod_pei');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strategic_planning.tab_inaugurar_pei');
    }
};
