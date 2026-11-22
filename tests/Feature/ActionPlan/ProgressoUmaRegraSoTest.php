<?php

/*
 * Uma regra só para o progresso das entregas (22/11/2026).
 *
 * Havia cinco contas diferentes para a mesma coisa:
 * - o progresso da iniciativa ignorava a entrega de peso 0 quando outras tinham peso;
 * - o atingimento do objetivo e da perspectiva contava essa entrega com peso 1 e não
 *   olhava as sub-entregas;
 * - o Mapa Estratégico contava as sub-entregas como entregas soltas e incluía as
 *   arquivadas;
 * - o indicador calculado pela iniciativa gravava 0% quando a iniciativa não tinha
 *   entrega nenhuma (sem medição virava farol vermelho).
 * A mesma iniciativa saía com números diferentes em telas diferentes.
 *
 * A regra única (IndicadorCalculoService::entregasQueContam e progressoDasEntregas):
 * - contam as entregas raiz, não arquivadas, não excluídas e não canceladas;
 * - a sub-entrega conta dentro da entrega pai, nunca solta;
 * - peso efetivo = o peso informado; sem peso, 1;
 * - nada a medir = sem medição (null), nunca 0%.
 */

use App\Livewire\ActionPlan\DetalharPlano;
use App\Livewire\ActionPlan\GerenciarEntregas;
use App\Livewire\Deliverables\DeliverablesBoard;
use App\Livewire\StrategicPlanning\MapaEstrategico;
use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\PerformanceIndicators\EvolucaoIndicador;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\Perspectiva;
use App\Services\IndicadorCalculoService;
use Livewire\Livewire;
use Tests\Feature\PerformanceIndicators\CenarioIndicador;

function purEntrega(PlanoDeAcao $plano, string $titulo, string $status, float $peso, array $extra = []): Entrega
{
    return Entrega::create(array_merge([
        'cod_plano_de_acao' => $plano->cod_plano_de_acao,
        'dsc_entrega' => $titulo,
        'bln_status' => $status,
        'dsc_tipo' => 'task',
        'num_nivel_hierarquico_apresentacao' => 1,
        'num_peso' => $peso,
        'dte_prazo' => now()->startOfYear()->addMonths(5)->toDateString(),
    ], $extra));
}

/**
 * A iniciativa que mistura tudo o que divergia.
 *
 * @return array{c: array<string, mixed>, plano: PlanoDeAcao}
 */
function purCenario(): array
{
    $c = CenarioIndicador::criar();
    CenarioIndicador::regua($c['pei']);

    // Só as iniciativas pesam na perspectiva: o indicador do cenário não tem medição.
    Perspectiva::find($c['objetivo']->cod_perspectiva)->update(['num_peso_indicadores' => 0, 'num_peso_planos' => 100]);

    $plano = CenarioIndicador::plano($c['objetivo'], $c['org']);

    purEntrega($plano, 'A', 'Concluído', 50);                          // 50 × 1,0
    purEntrega($plano, 'B', 'Em Andamento', 50);                       // 50 × 0,5
    purEntrega($plano, 'C sem peso', 'Concluído', 0);                  //  1 × 1,0 (peso efetivo 1)
    purEntrega($plano, 'D arquivada', 'Concluído', 30, ['bln_arquivado' => true]); // fora
    purEntrega($plano, 'F cancelada', 'Cancelado', 40);                // fora
    $e = purEntrega($plano, 'E com sub-entregas', 'Não Iniciado', 20); // 20 × progresso das subs
    purEntrega($plano, 'E1', 'Concluído', 0, ['cod_entrega_pai' => $e->cod_entrega]);
    purEntrega($plano, 'E2', 'Concluído', 0, ['cod_entrega_pai' => $e->cod_entrega]);

    return ['c' => $c, 'plano' => $plano->fresh()];
}

// (50 + 25 + 1 + 20) ÷ (50 + 50 + 1 + 20) = 96 ÷ 121
const PUR_ESPERADO = 96 / 121 * 100;

test('o progresso da iniciativa usa a regra única: peso 0 vale 1, sub-entrega conta no pai, arquivada e cancelada ficam fora', function () {
    ['plano' => $plano] = purCenario();

    expect(app(IndicadorCalculoService::class)->calcularProgressoPlano($plano))->toBe(round(PUR_ESPERADO, 2));
});

test('o progresso do ano dá o mesmo número que o progresso da iniciativa', function () {
    ['plano' => $plano] = purCenario();

    $noAno = app(IndicadorCalculoService::class)->calcularProgressoPlanoNoAno($plano, (int) date('Y'));

    expect($noAno['progresso'])->toBe(round(PUR_ESPERADO, 2))
        ->and($noAno['total_entregas'])->toBe(4);
});

test('o objetivo e a perspectiva dão o mesmo número que a iniciativa', function () {
    ['c' => $c] = purCenario();
    $servico = app(IndicadorCalculoService::class);
    $ano = (int) date('Y');

    expect($servico->calcularAtingimentoObjetivo($c['objetivo']->fresh(), $ano))->toBe(round(PUR_ESPERADO, 1))
        ->and($servico->calcularAtingimentoPerspectiva(Perspectiva::find($c['objetivo']->cod_perspectiva), $ano))->toBe(round(PUR_ESPERADO, 1));
});

test('o Mapa Estratégico dá o mesmo número que o serviço, sem contar sub-entrega solta nem arquivada', function () {
    ['c' => $c] = purCenario();

    $tela = Livewire::actingAs($c['user'])->test(MapaEstrategico::class)->assertOk();
    $p = collect($tela->get('perspectivas'))->firstWhere('cod_perspectiva', $c['objetivo']->cod_perspectiva);

    expect($p['atingimento_medio'])->toBe(round(PUR_ESPERADO, 1))
        ->and($p['objetivos'][0]['resumo_planos']['media_progresso'])->toBe(round(PUR_ESPERADO, 1));

    // A memória de cálculo lista só as entregas que contam, com o peso efetivo.
    $memoria = collect($p['memoria_planos'][0]['entregas']);
    expect($memoria->pluck('entrega')->all())->toEqualCanonicalizing(['A', 'B', 'C sem peso', 'E com sub-entregas'])
        ->and($memoria->firstWhere('entrega', 'C sem peso')['peso'])->toEqual(1);
});

test('o indicador calculado pela iniciativa sem entrega não grava 0%: fica sem medição', function () {
    $c = CenarioIndicador::criar();
    $plano = CenarioIndicador::plano($c['objetivo'], $c['org']);
    $indicador = Indicador::create([
        'cod_objetivo' => $c['objetivo']->cod_objetivo,
        'cod_plano_de_acao' => $plano->cod_plano_de_acao,
        'nom_indicador' => 'Execução da iniciativa',
        'dsc_indicador' => 'Calculado pelas entregas.',
        'dsc_tipo' => 'Objetivo',
        'dsc_unidade_medida' => 'Percentual (%)',
        'bln_acumulado' => 'Não',
        'dsc_periodo_medicao' => 'Mensal',
        'dsc_polaridade' => 'Positiva',
        'dsc_calculation_type' => 'action_plan',
    ]);

    expect(app(IndicadorCalculoService::class)->atualizarIndicadorAutomatico($indicador))->toBeNull()
        ->and(EvolucaoIndicador::where('cod_indicador', $indicador->cod_indicador)->count())->toBe(0);
});

test('as três telas da iniciativa dizem "Sem entregas a medir", nunca 0%, quando não há entrega', function () {
    $c = CenarioIndicador::criar();
    $vazia = CenarioIndicador::plano($c['objetivo'], $c['org']);
    ['plano' => $comEntregas] = purCenario();

    foreach ([
        [DetalharPlano::class, 'id'],
        [GerenciarEntregas::class, 'planoId'],
        [DeliverablesBoard::class, 'planoId'],
    ] as [$tela, $parametro]) {
        Livewire::actingAs($c['user'])->test($tela, [$parametro => $vazia->cod_plano_de_acao])
            ->assertOk()
            ->assertSee('Sem entregas a medir');

        Livewire::actingAs($c['user'])->test($tela, [$parametro => $comEntregas->cod_plano_de_acao])
            ->assertOk()
            ->assertDontSee('Sem entregas a medir')
            ->assertSee('79,3');
    }
});

test('a conferência dos pesos avisa a entrega sem peso, mesmo com a soma em 100', function () {
    $c = CenarioIndicador::criar();
    $plano = CenarioIndicador::plano($c['objetivo'], $c['org']);
    purEntrega($plano, 'A', 'Não Iniciado', 50);
    purEntrega($plano, 'B', 'Não Iniciado', 50);
    purEntrega($plano, 'C sem peso', 'Não Iniciado', 0);

    $conferencia = app(IndicadorCalculoService::class)->validarPesosPlano($plano);

    expect($conferencia['valid'])->toBeFalse()
        ->and($conferencia['message'])->toBe('A soma dos pesos é 100%, mas 1 entrega está sem peso e conta com peso 1. Dê um peso a ela ou redistribua os pesos.');
});
