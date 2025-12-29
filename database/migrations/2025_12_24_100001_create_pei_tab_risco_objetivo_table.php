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
        Schema::create('tab_risco_objetivo', function (Blueprint $table) {
            $table->foreignUuid('cod_risco')->references('cod_risco')->on('tab_risco')->cascadeOnDelete();
            $table->foreignUuid('cod_objetivo')->references('cod_objetivo')->on('tab_objetivo_estrategico')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Chave primária composta
            $table->primary(['cod_risco', 'cod_objetivo'], 'pk_risco_objetivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tab_risco_objetivo');
    }
};
