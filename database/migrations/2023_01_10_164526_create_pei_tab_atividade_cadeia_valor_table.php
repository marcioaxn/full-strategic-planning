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
        Schema::create('pei.tab_atividade_cadeia_valor', function (Blueprint $table) {
            $table->uuid('cod_atividade_cadeia_valor')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_pei')->references('cod_pei')->on('pei.tab_pei')->cascadeOnDelete();
            $table->foreignUuid('cod_perspectiva')->references('cod_perspectiva')->on('pei.tab_perspectiva')->cascadeOnDelete();
            $table->text('dsc_atividade')->nullable(false);
            $table->timestamps();
            $table->softDeletes();

            // Índices para performance
            $table->index('cod_pei');
            $table->index('cod_perspectiva');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pei.tab_atividade_cadeia_valor');
    }
};
