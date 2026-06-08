<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('action_plan.tab_entrega_comentarios', function (Blueprint $table) {
            $table->uuid('cod_comentario_pai')->nullable()->after('cod_usuario');

            $table->foreign('cod_comentario_pai')
                  ->references('cod_comentario')
                  ->on('action_plan.tab_entrega_comentarios')
                  ->cascadeOnDelete();

            $table->index('cod_comentario_pai', 'idx_comentarios_pai');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Usa DROP COLUMN CASCADE para remover a coluna junto com o índice e a FK
     * automaticamente, evitando falha por nome de constraint desconhecido.
     * O bloco DO/IF EXISTS torna o rollback idempotente.
     */
    public function down(): void
    {
        DB::statement("
            DO \$\$
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM information_schema.columns
                    WHERE table_schema = 'action_plan'
                      AND table_name   = 'tab_entrega_comentarios'
                      AND column_name  = 'cod_comentario_pai'
                ) THEN
                    ALTER TABLE action_plan.tab_entrega_comentarios
                    DROP COLUMN cod_comentario_pai CASCADE;
                END IF;
            END \$\$;
        ");
    }
};
