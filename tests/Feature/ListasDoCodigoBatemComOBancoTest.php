<?php

/*
 * Toda lista de opções do código cabe na regra CHECK do banco.
 *
 * 🔴 Em 05/09/2026 o tipo de encaminhamento da RAE foi renomeado no código
 * ("Novo Plano" → "Nova Iniciativa") sem migration: a primeira opção da tela
 * dava erro 500 e ninguém viu por um mês (achado no teste pelo navegador de
 * 04/10/2026). Este teste lê os CHECK do banco migrado e trava a CLASSE do
 * defeito: lista nova no código sem a regra do banco acompanhar.
 */

use App\Models\RiskManagement\Risco;
use App\Models\StrategicPlanning\RaeCausaRaiz;
use App\Models\StrategicPlanning\RaeEncaminhamento;
use Illuminate\Support\Facades\DB;

/** Valores aceitos pelo CHECK ... = ANY (ARRAY[...]) de uma coluna. */
function valoresDoCheck(string $tabela, string $coluna): array
{
    $definicoes = DB::select(
        "SELECT pg_get_constraintdef(k.oid) AS def FROM pg_constraint k
          WHERE k.contype = 'c' AND k.conrelid = ?::regclass",
        [$tabela]
    );

    foreach ($definicoes as $d) {
        if (str_contains($d->def, "({$coluna})::text") || str_contains($d->def, "({$coluna} ")) {
            preg_match_all("/'([^']*)'::character varying/", $d->def, $m);

            return $m[1];
        }
    }

    return [];
}

dataset('listas', [
    'tipo do encaminhamento da RAE' => ['strategic_planning.tab_rae_encaminhamento', 'dsc_tipo', fn () => RaeEncaminhamento::TIPOS],
    'status do encaminhamento da RAE' => ['strategic_planning.tab_rae_encaminhamento', 'dsc_status', fn () => RaeEncaminhamento::STATUS],
    'categoria de Ishikawa' => ['strategic_planning.tab_rae_causa_raiz', 'dsc_categoria_ishikawa', fn () => RaeCausaRaiz::CATEGORIAS_ISHIKAWA],
    'estratégia de resposta ao risco' => ['risk_management.tab_risco', 'dsc_estrategia_resposta', fn () => Risco::ESTRATEGIAS_RESPOSTA],
]);

test('a lista do código cabe na regra do banco', function (string $tabela, string $coluna, Closure $lista) {
    $aceitos = valoresDoCheck($tabela, $coluna);

    expect($aceitos)->not->toBeEmpty("Não achei o CHECK de {$tabela}.{$coluna}")
        ->and(array_values(array_diff($lista(), $aceitos)))->toBe([]);
})->with('listas');

test('os tipos TOWS da tela cabem na regra do banco', function () {
    expect(array_values(array_diff(['SO', 'ST', 'WO', 'WT'], valoresDoCheck('strategic_planning.tab_estrategia_tows', 'dsc_tipo'))))->toBe([]);
});
