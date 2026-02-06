<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration para adicionar peso às entregas.
 * 
 * Permite que cada entrega tenha um peso diferenciado no cálculo
 * do progresso do plano de ação e do indicador vinculado.
 * 
 * Fórmula de cálculo:
 * Progresso = Σ (Status da Entrega × Peso da Entrega)
 * 
 * Exemplo:
 * - Entrega A: Peso 40, Status Concluído (100%) → 40 × 1.0 = 40
 * - Entrega B: Peso 60, Status Pendente (0%) → 60 × 0.0 = 0
 * - Progresso Total: 40%
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('action_plan.tab_entregas', function (Blueprint $table) {
            // Peso da entrega para cálculo ponderado (0-100 ou valor absoluto)
            // Usar decimal para permitir pesos fracionários (ex: 33.33)
            $table->decimal('num_peso', 8, 2)
                  ->default(0)
                  ->after('num_ordem')
                  ->comment('Peso da entrega no cálculo do progresso (0-100)');
            
            // Índice para ordenação por peso
            $table->index('num_peso', 'idx_entregas_peso');
        });

        // Definir peso padrão para entregas existentes
        // Distribuir peso igualitário entre entregas de cada plano
        DB::statement("
            WITH peso_calculado AS (
                SELECT 
                    cod_entrega,
                    cod_plano_de_acao,
                    CASE 
                        WHEN COUNT(*) OVER (PARTITION BY cod_plano_de_acao) > 0 
                        THEN ROUND(100.0 / COUNT(*) OVER (PARTITION BY cod_plano_de_acao), 2)
                        ELSE 0
                    END as peso_sugerido
                FROM action_plan.tab_entregas
                WHERE cod_entrega_pai IS NULL  -- Apenas entregas raiz
                  AND deleted_at IS NULL
            )
            UPDATE action_plan.tab_entregas e
            SET num_peso = pc.peso_sugerido
            FROM peso_calculado pc
            WHERE e.cod_entrega = pc.cod_entrega
              AND e.num_peso = 0
        ");

        // Comentário explicativo
        DB::statement("
            COMMENT ON COLUMN action_plan.tab_entregas.num_peso IS 
            'Peso da entrega no cálculo do progresso ponderado. Idealmente a soma dos pesos de um plano deve totalizar 100. Pesos de sub-entregas são proporcionais ao peso da entrega pai.';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('action_plan.tab_entregas', function (Blueprint $table) {
            $table->dropIndex('idx_entregas_peso');
            $table->dropColumn('num_peso');
        });
    }
};
