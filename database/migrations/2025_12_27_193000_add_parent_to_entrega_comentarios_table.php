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
        Schema::table('tab_entrega_comentarios', function (Blueprint $table) {
            // Adiciona auto-referência para comentários (respostas)
            $table->uuid('cod_comentario_pai')->nullable()->after('cod_usuario');
            
            $table->foreign('cod_comentario_pai')
                  ->references('cod_comentario')
                  ->on('tab_entrega_comentarios')
                  ->cascadeOnDelete();
            
            $table->index('cod_comentario_pai', 'idx_comentarios_pai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tab_entrega_comentarios', function (Blueprint $table) {
            $table->dropForeign('idx_comentarios_pai');
            $table->dropColumn('cod_comentario_pai');
        });
    }
};
