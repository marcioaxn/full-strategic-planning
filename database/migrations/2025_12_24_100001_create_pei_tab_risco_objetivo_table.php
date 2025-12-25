<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pei.tab_risco_objetivo', function (Blueprint $table) {
            $table->foreignUuid('cod_risco')->references('cod_risco')->on('pei.tab_risco')->cascadeOnDelete();
            $table->foreignUuid('cod_objetivo_estrategico')->references('cod_objetivo_estrategico')->on('pei.tab_objetivo_estrategico')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Chave primária composta
            $table->primary(['cod_risco', 'cod_objetivo_estrategico'], 'pk_risco_objetivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pei.tab_risco_objetivo');
    }
};
