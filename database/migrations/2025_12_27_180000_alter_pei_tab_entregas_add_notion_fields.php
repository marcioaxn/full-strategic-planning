<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration para adicionar campos estilo Notion à tabela de entregas.
 * 
 * Esta migration preserva todos os dados existentes e adiciona novos campos
 * para suportar funcionalidades como hierarquia, drag-and-drop, prioridades,
 * responsáveis e propriedades customizáveis.
 * 
 * @see https://www.notion.com - Inspiração do design
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tab_entregas', function (Blueprint $table) {
            // ========================================
            // HIERARQUIA E SUBTAREFAS
            // ========================================
            
            // Auto-referência para criar hierarquia de entregas (sub-entregas)
            $table->uuid('cod_entrega_pai')
                  ->nullable()
                  ->after('cod_plano_de_acao');
            
            // ========================================
            // TIPO DE BLOCO (estilo Notion)
            // ========================================
            
            // Tipo de bloco: task, heading, text, divider, checklist
            $table->string('dsc_tipo', 50)
                  ->default('task')
                  ->after('dsc_entrega');
            
            // ========================================
            // PROPRIEDADES EXTRAS (JSON)
            // ========================================
            
            // Propriedades customizáveis em JSON (cor, ícone, metadata, etc.)
            $table->jsonb('json_propriedades')
                  ->nullable()
                  ->after('dsc_tipo');
            
            // ========================================
            // DATA DE PRAZO
            // ========================================
            
            // Data de prazo específica para a entrega
            $table->date('dte_prazo')
                  ->nullable()
                  ->after('dsc_periodo_medicao');
            
            // ========================================
            // RESPONSÁVEL
            // ========================================
            
            // Usuário responsável pela entrega
            $table->uuid('cod_responsavel')
                  ->nullable()
                  ->after('dte_prazo');
            
            // ========================================
            // PRIORIDADE
            // ========================================
            
            // Nível de prioridade: baixa, media, alta, urgente
            $table->string('cod_prioridade', 20)
                  ->default('media')
                  ->after('cod_responsavel');
            
            // ========================================
            // ORDENAÇÃO (substituindo num_nivel_hierarquico_apresentacao)
            // ========================================
            
            // Ordem para drag-and-drop (mais flexível que o campo antigo)
            $table->integer('num_ordem')
                  ->default(0)
                  ->after('cod_prioridade');
            
            // ========================================
            // ARQUIVAMENTO VISUAL
            // ========================================
            
            // Arquivado visualmente (diferente de soft delete)
            // Itens arquivados não aparecem na view padrão mas podem ser restaurados
            $table->boolean('bln_arquivado')
                  ->default(false)
                  ->after('num_ordem');
            
            // ========================================
            // ÍNDICES PARA PERFORMANCE
            // ========================================
            
            $table->index('cod_entrega_pai', 'idx_entregas_entrega_pai');
            $table->index('dsc_tipo', 'idx_entregas_tipo');
            $table->index('bln_arquivado', 'idx_entregas_arquivado');
            $table->index('num_ordem', 'idx_entregas_ordem');
            $table->index('cod_responsavel', 'idx_entregas_responsavel');
            $table->index('cod_prioridade', 'idx_entregas_prioridade');
            $table->index('dte_prazo', 'idx_entregas_prazo');
        });
        
        // ========================================
        // ADICIONAR FOREIGN KEY SEPARADAMENTE
        // ========================================
        
        // FK para hierarquia (auto-referência)
        Schema::table('tab_entregas', function (Blueprint $table) {
            $table->foreign('cod_entrega_pai', 'fk_entregas_entrega_pai')
                  ->references('cod_entrega')
                  ->on('tab_entregas')
                  ->nullOnDelete();
        });
        
        // FK para responsável
        Schema::table('tab_entregas', function (Blueprint $table) {
            $table->foreign('cod_responsavel', 'fk_entregas_responsavel')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
        
        // ========================================
        // MIGRAR DADOS EXISTENTES
        // ========================================
        
        // Copiar num_nivel_hierarquico_apresentacao para num_ordem
        // e definir valores padrão para novos campos
        DB::statement("
            UPDATE tab_entregas 
            SET 
                num_ordem = COALESCE(num_nivel_hierarquico_apresentacao, 0),
                dsc_tipo = 'task',
                cod_prioridade = 'media',
                bln_arquivado = false,
                json_propriedades = '{}'::jsonb
            WHERE num_ordem = 0 OR num_ordem IS NULL
        ");
        
        // ========================================
        // COMENTÁRIO NA TABELA
        // ========================================
        
        DB::statement("
            COMMENT ON TABLE tab_entregas IS 
            'Tabela de entregas com suporte a interface estilo Notion. 
            Migrada para incluir campos de hierarquia, propriedades e ordenação.
            Campos novos: cod_entrega_pai, dsc_tipo, json_propriedades, dte_prazo, 
            cod_responsavel, cod_prioridade, num_ordem, bln_arquivado.
            Campo num_nivel_hierarquico_apresentacao mantido para compatibilidade.';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tab_entregas', function (Blueprint $table) {
            // Remover foreign keys primeiro
            $table->dropForeign('fk_entregas_entrega_pai');
            $table->dropForeign('fk_entregas_responsavel');
            
            // Remover índices
            $table->dropIndex('idx_entregas_entrega_pai');
            $table->dropIndex('idx_entregas_tipo');
            $table->dropIndex('idx_entregas_arquivado');
            $table->dropIndex('idx_entregas_ordem');
            $table->dropIndex('idx_entregas_responsavel');
            $table->dropIndex('idx_entregas_prioridade');
            $table->dropIndex('idx_entregas_prazo');
            
            // Remover colunas
            $table->dropColumn([
                'cod_entrega_pai',
                'dsc_tipo',
                'json_propriedades',
                'dte_prazo',
                'cod_responsavel',
                'cod_prioridade',
                'num_ordem',
                'bln_arquivado',
            ]);
        });
    }
};
