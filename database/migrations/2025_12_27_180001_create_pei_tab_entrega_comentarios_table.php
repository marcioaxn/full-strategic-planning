<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration para criar tabela de comentários em entregas.
 * 
 * Permite que usuários adicionem comentários/notas em entregas,
 * similar ao sistema de comentários do Notion.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('action_plan.tab_entrega_comentarios', function (Blueprint $table) {
            // Chave primária UUID
            $table->uuid('cod_comentario')
                  ->primary()
                  ->default(DB::raw('gen_random_uuid()'));
            
            // FK para entrega
            $table->foreignUuid('cod_entrega')
                  ->references('cod_entrega')
                  ->on('action_plan.tab_entregas')
                  ->cascadeOnDelete();
            
            // FK para usuário que comentou
            $table->uuid('cod_usuario')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
            
            // Conteúdo do comentário (suporta markdown/rich text)
            $table->text('dsc_comentario');
            
            // Indica se é uma menção a outro usuário (@usuario)
            $table->jsonb('json_mencoes')->nullable();
            
            // Timestamps e SoftDeletes
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para performance
            $table->index('cod_entrega', 'idx_comentarios_entrega');
            $table->index('cod_usuario', 'idx_comentarios_usuario');
            $table->index('created_at', 'idx_comentarios_data');
        });
        
        // Comentário na tabela
        DB::statement("
            COMMENT ON TABLE action_plan.tab_entrega_comentarios IS 
            'Tabela de comentários em entregas. Suporta menções a usuários e rich text.';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_plan.tab_entrega_comentarios');
    }
};
