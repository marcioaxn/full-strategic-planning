<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strategic_planning.tab_integracao_instrumentos', function (Blueprint $table) {
            $table->uuid('cod_integracao')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_pei')
                  ->references('cod_pei')
                  ->on('strategic_planning.tab_pei')
                  ->cascadeOnDelete();
            $table->string('dsc_instrumento', 100);
            $table->string('dsc_tipo_instrumento', 50)->default('PPA');
            $table->text('txt_pontos_atencao')->nullable();
            $table->text('txt_tarefas')->nullable();
            $table->string('dsc_intensidade', 10)->default('Media');
            $table->smallInteger('num_ordem')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('cod_pei');
            $table->index('dsc_tipo_instrumento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strategic_planning.tab_integracao_instrumentos');
    }
};
