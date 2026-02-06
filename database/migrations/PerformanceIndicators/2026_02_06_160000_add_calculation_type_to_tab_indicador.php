<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration para adicionar tipo de cálculo ao indicador.
 * 
 * Permite que indicadores sejam calculados de duas formas:
 * - 'manual': Medição tradicional com lançamento de evoluções
 * - 'action_plan': Cálculo automático baseado no progresso das entregas ponderadas
 * 
 * @see https://www.bsc.gov - Balanced Scorecard best practices
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('performance_indicators.tab_indicador', function (Blueprint $table) {
            // Tipo de cálculo: manual (padrão) ou baseado em plano de ação
            $table->string('dsc_calculation_type', 20)
                  ->default('manual')
                  ->after('dsc_periodo_medicao')
                  ->comment('Tipo de cálculo: manual ou action_plan');
            
            // Índice para filtros rápidos
            $table->index('dsc_calculation_type', 'idx_indicador_calculation_type');
        });

        // Definir todos os indicadores existentes como 'manual' (preserva comportamento atual)
        DB::statement("
            UPDATE performance_indicators.tab_indicador 
            SET dsc_calculation_type = 'manual' 
            WHERE dsc_calculation_type IS NULL OR dsc_calculation_type = ''
        ");

        // Comentário explicativo
        DB::statement("
            COMMENT ON COLUMN performance_indicators.tab_indicador.dsc_calculation_type IS 
            'Tipo de cálculo do indicador: manual (lançamento de evoluções) ou action_plan (cálculo automático baseado em entregas ponderadas do plano vinculado)';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_indicators.tab_indicador', function (Blueprint $table) {
            $table->dropIndex('idx_indicador_calculation_type');
            $table->dropColumn('dsc_calculation_type');
        });
    }
};
