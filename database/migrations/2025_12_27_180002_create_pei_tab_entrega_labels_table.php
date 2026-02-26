<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration para criar tabela de labels/tags para entregas.
 * 
 * Labels são tags coloridas que podem ser atribuídas às entregas
 * para categorização visual, similar ao sistema do Notion.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('action_plan.tab_entrega_labels', function (Blueprint $table) {
            // Chave primária UUID
            $table->uuid('cod_label')
                  ->primary()
                  ->default(DB::raw('gen_random_uuid()'));
            
            // FK para plano de ação (labels são específicas por plano)
            $table->foreignUuid('cod_plano_de_acao')
                  ->references('cod_plano_de_acao')
                  ->on('action_plan.tab_plano_de_acao')
                  ->cascadeOnDelete();
            
            // Nome da label
            $table->string('dsc_label', 100);
            
            // Cor em formato HEX
            $table->string('dsc_cor', 7)->default('#6366f1');
            
            // Ícone opcional (nome do ícone Bootstrap Icons)
            $table->string('dsc_icone', 50)->nullable();
            
            // Ordem de exibição
            $table->integer('num_ordem')->default(0);
            
            // Timestamps
            $table->timestamps();
            
            // Índices
            $table->index('cod_plano_de_acao', 'idx_labels_plano');
            $table->index('num_ordem', 'idx_labels_ordem');
        });
        
        // Comentário na tabela
        DB::statement("
            COMMENT ON TABLE action_plan.tab_entrega_labels IS 
            'Tabela de labels/tags coloridas para categorização de entregas.';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_plan.tab_entrega_labels');
    }
};
