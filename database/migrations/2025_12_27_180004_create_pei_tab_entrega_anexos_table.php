<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration para criar tabela de anexos em entregas.
 * 
 * Permite upload de arquivos e imagens nas entregas,
 * similar ao sistema de anexos do Notion.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pei.tab_entrega_anexos', function (Blueprint $table) {
            // Chave primária UUID
            $table->uuid('cod_anexo')
                  ->primary()
                  ->default(DB::raw('gen_random_uuid()'));
            
            // FK para entrega
            $table->foreignUuid('cod_entrega')
                  ->references('cod_entrega')
                  ->on('pei.tab_entregas')
                  ->cascadeOnDelete();
            
            // FK para usuário que fez upload
            $table->uuid('cod_usuario')
                  ->references('id')
                  ->on('users');
            
            // Nome original do arquivo
            $table->string('dsc_nome_arquivo', 255);
            
            // Caminho no storage
            $table->string('dsc_caminho', 500);
            
            // Tipo MIME
            $table->string('dsc_mime_type', 100);
            
            // Tamanho em bytes
            $table->bigInteger('num_tamanho_bytes');
            
            // Descrição/alt text opcional
            $table->string('dsc_descricao', 500)->nullable();
            
            // Miniatura para imagens (base64 ou caminho)
            $table->text('dsc_thumbnail')->nullable();
            
            // Timestamps e SoftDeletes
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('cod_entrega', 'idx_anexos_entrega');
            $table->index('cod_usuario', 'idx_anexos_usuario');
            $table->index('dsc_mime_type', 'idx_anexos_mime');
        });
        
        // Comentário na tabela
        DB::statement("
            COMMENT ON TABLE pei.tab_entrega_anexos IS 
            'Tabela de anexos/arquivos em entregas. Suporta imagens, documentos e outros arquivos.';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pei.tab_entrega_anexos');
    }
};
