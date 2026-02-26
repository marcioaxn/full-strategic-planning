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
        Schema::create('strategic_planning.tab_objetivo_comentarios', function (Blueprint $table) {
            $table->uuid('cod_comentario')->primary();
            $table->foreignUuid('cod_objetivo')->constrained('tab_objetivo', 'cod_objetivo')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->text('dsc_comentario');
            $table->uuid('cod_comentario_pai')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strategic_planning.tab_objetivo_comentarios');
    }
};