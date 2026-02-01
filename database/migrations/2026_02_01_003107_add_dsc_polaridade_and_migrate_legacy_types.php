<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Adicionar a nova coluna
        Schema::table('performance_indicators.tab_indicador', function (Blueprint $table) {
            $table->string('dsc_polaridade')->nullable()->after('dsc_unidade_medida');
        });

        // 2. Migrar dados do legado (dsc_tipo) para dsc_polaridade
        // Mapeamento:
        // + -> Positiva (Quanto maior, melhor)
        // - -> Negativa (Quanto menor, melhor)
        // = -> Estabilidade (Quanto mais próximo do alvo, melhor)
        
        DB::table('performance_indicators.tab_indicador')
            ->where('dsc_tipo', '+')
            ->update(['dsc_polaridade' => 'Positiva']);

        DB::table('performance_indicators.tab_indicador')
            ->where('dsc_tipo', '-')
            ->update(['dsc_polaridade' => 'Negativa']);

        DB::table('performance_indicators.tab_indicador')
            ->where('dsc_tipo', '=')
            ->update(['dsc_polaridade' => 'Estabilidade']);

        // 3. Corrigir dsc_tipo para o padrão atual (Objetivo / Plano)
        // Se dsc_tipo ainda contiver os símbolos legados, vamos inferir o tipo correto
        DB::table('performance_indicators.tab_indicador')
            ->whereIn('dsc_tipo', ['+', '-', '='])
            ->whereNotNull('cod_objetivo')
            ->update(['dsc_tipo' => 'Objetivo']);

        DB::table('performance_indicators.tab_indicador')
            ->whereIn('dsc_tipo', ['+', '-', '='])
            ->whereNotNull('cod_plano_de_acao')
            ->update(['dsc_tipo' => 'Plano']);
            
        // Se não tiver vínculo, definimos como Objetivo por padrão (ou mantemos nulo se permitido)
        DB::table('performance_indicators.tab_indicador')
            ->whereIn('dsc_tipo', ['+', '-', '='])
            ->update(['dsc_tipo' => 'Objetivo']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Para reverter, poderíamos tentar devolver os símbolos para dsc_tipo, 
        // mas como dsc_tipo agora tem outra semântica, o mais seguro é apenas remover dsc_polaridade.
        Schema::table('performance_indicators.tab_indicador', function (Blueprint $table) {
            $table->dropColumn('dsc_polaridade');
        });
    }
};