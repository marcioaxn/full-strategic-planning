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
        Schema::create('rel_organizacao', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_organizacao')->references('cod_organizacao')->on('tab_organizacoes')->cascadeOnDelete();
            $table->foreignUuid('rel_cod_organizacao')->references('cod_organizacao')->on('tab_organizacoes')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Índice composto para evitar duplicatas
            $table->unique(['cod_organizacao', 'rel_cod_organizacao']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rel_organizacao');
    }
};
