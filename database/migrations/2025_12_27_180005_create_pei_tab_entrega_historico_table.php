<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration para criar tabela de histórico de atividades em entregas.
 * 
 * Registra todas as alterações feitas nas entregas para funcionalidade
 * de "histórico de versões" estilo Notion.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('action_plan.tab_entrega_historico', function (Blueprint $table) {
            // Chave primária UUID
            $table->uuid('cod_historico')
                  ->primary()
                  ->default(DB::raw('gen_random_uuid()'));
            
            // FK para entrega
            $table->foreignUuid('cod_entrega')
                  ->references('cod_entrega')
                  ->on('action_plan.tab_entregas')
                  ->cascadeOnDelete();
            
            // FK para usuário que fez a alteração
            $table->uuid('cod_usuario')
                  ->nullable()
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
            
            // Tipo de ação: created, updated, deleted, restored, status_changed, etc.
            $table->string('dsc_acao', 50);
            
            // Campo que foi alterado (se aplicável)
            $table->string('dsc_campo', 100)->nullable();
            
            // Valor antigo (JSON para suportar qualquer tipo)
            $table->jsonb('json_valor_antigo')->nullable();
            
            // Valor novo (JSON para suportar qualquer tipo)
            $table->jsonb('json_valor_novo')->nullable();
            
            // Descrição legível da alteração
            $table->text('dsc_descricao')->nullable();
            
            // Timestamp da ação
            $table->timestamp('created_at')->useCurrent();
            
            // Índices
            $table->index('cod_entrega', 'idx_historico_entrega');
            $table->index('cod_usuario', 'idx_historico_usuario');
            $table->index('dsc_acao', 'idx_historico_acao');
            $table->index('created_at', 'idx_historico_data');
        });
        
        // Comentário na tabela
        DB::statement("
            COMMENT ON TABLE action_plan.tab_entrega_historico IS 
            'Tabela de histórico de atividades em entregas. Registra todas as alterações para auditoria e versões.';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_plan.tab_entrega_historico');
    }
};
