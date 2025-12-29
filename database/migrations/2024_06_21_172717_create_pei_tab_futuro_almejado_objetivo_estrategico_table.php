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
        Schema::create('tab_futuro_almejado_objetivo_estrategico', function (Blueprint $table) {
            $table->uuid('cod_futuro_almejado')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->text('dsc_futuro_almejado')->nullable(false);
            $table->foreignUuid('cod_objetivo')->references('cod_objetivo')->on('tab_objetivo_estrategico')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Índice para performance
            $table->index('cod_objetivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tab_futuro_almejado_objetivo_estrategico');
    }
};
