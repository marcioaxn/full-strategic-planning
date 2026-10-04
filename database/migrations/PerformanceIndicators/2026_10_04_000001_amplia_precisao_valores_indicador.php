<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Valores de indicador com 4 casas decimais: numeric(15,2) → numeric(19,4).
 *
 * A tela aceita 4 casas para "Índice (0-1)", "Proporção" e "Taxa" e 3 para
 * "Toneladas (t)" (App\Support\UnidadeMedida), mas as colunas guardavam 2:
 * 0,8750 virava 0,88 e 0,0049 virava 0,00. A parte inteira continua com 15
 * dígitos (o mesmo teto de antes).
 *
 * ALTER COLUMN ... TYPE preserva NULL/NOT NULL e o default — compatível com
 * PostgreSQL 9.3. Nenhuma view depende destas colunas (conferido em
 * information_schema.view_column_usage em 04/10/2026).
 */
return new class extends Migration
{
    /** @var array<string, list<string>> tabela => colunas */
    private const COLUNAS = [
        'performance_indicators.tab_evolucao_indicador' => ['vlr_previsto', 'vlr_realizado'],
        'performance_indicators.tab_meta_por_ano' => ['meta'],
        'performance_indicators.tab_linha_base_indicador' => ['num_linha_base'],
    ];

    public function up(): void
    {
        foreach (self::COLUNAS as $tabela => $colunas) {
            foreach ($colunas as $coluna) {
                DB::statement("ALTER TABLE {$tabela} ALTER COLUMN {$coluna} TYPE numeric(19,4)");
            }
        }
    }

    public function down(): void
    {
        // Reverter arredonda para 2 casas o que foi gravado com 3 ou 4.
        foreach (self::COLUNAS as $tabela => $colunas) {
            foreach ($colunas as $coluna) {
                DB::statement("ALTER TABLE {$tabela} ALTER COLUMN {$coluna} TYPE numeric(15,2)");
            }
        }
    }
};
