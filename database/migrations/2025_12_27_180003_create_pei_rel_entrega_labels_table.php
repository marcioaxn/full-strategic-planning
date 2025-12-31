<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration para criar tabela de relacionamento N:N entre entregas e labels.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rel_entrega_labels', function (Blueprint $table) {
            // Chaves compostas
            $table->uuid('cod_entrega');
            $table->uuid('cod_label');
            
            // Chave primária composta
            $table->primary(['cod_entrega', 'cod_label']);
            
            // Foreign keys
            $table->foreign('cod_entrega')
                  ->references('cod_entrega')
                  ->on('tab_entregas')
                  ->cascadeOnDelete();
            
            $table->foreign('cod_label')
                  ->references('cod_label')
                  ->on('tab_entrega_labels')
                  ->cascadeOnDelete();
            
            // Timestamp de quando a label foi atribuída
            $table->timestamp('created_at')->useCurrent();
        });
        
        // Comentário na tabela
        DB::statement("
            COMMENT ON TABLE rel_entrega_labels IS 
            'Tabela de relacionamento N:N entre entregas e labels.';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rel_entrega_labels');
    }
};
