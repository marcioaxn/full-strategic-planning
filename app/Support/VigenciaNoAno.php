<?php

namespace App\Support;

use Illuminate\Contracts\Database\Query\Builder as ContratoBuilder;

/**
 * "Vigente no ano" — o critério ÚNICO de ano para iniciativas nos relatórios:
 * começa até 31/12 do ano e termina a partir de 01/01 do ano.
 *
 * 🔴 O PDF de Iniciativas usava este critério e o Excel usava "começa OU
 * termina no ano": uma iniciativa de 2024 a 2028 saía no PDF de 2026 e sumia
 * do Excel de 2026. Teste: RelatoriosCicloEFiltrosTest.
 *
 * Comparação por data (não whereYear): usa índice e roda no PostgreSQL 9.3.
 */
class VigenciaNoAno
{
    /**
     * @template T of ContratoBuilder
     *
     * @param  T  $query
     * @return T
     */
    public static function aplicar($query, int $ano, string $colunaInicio = 'dte_inicio', string $colunaFim = 'dte_fim')
    {
        return $query->where($colunaInicio, '<=', sprintf('%04d-12-31', $ano))
            ->where($colunaFim, '>=', sprintf('%04d-01-01', $ano));
    }
}
