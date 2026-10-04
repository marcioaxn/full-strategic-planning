<?php

/*
 * Atingimento, farol e tendência do indicador, como a lista de indicadores os
 * mostra. Origem: revisão adversária do módulo de indicadores, 04/10/2026.
 */

use App\Livewire\PerformanceIndicators\ListarIndicadores;
use App\Models\PerformanceIndicators\EvolucaoIndicador;
use App\Models\PerformanceIndicators\MetaPorAno;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Services\IndicadorCalculoService;
use Livewire\Livewire;
use Tests\Feature\PerformanceIndicators\CenarioIndicador;

function evolucaoDoCenario(array $c, int $mes, $previsto, $realizado, ?int $ano = null): EvolucaoIndicador
{
    return EvolucaoIndicador::create([
        'cod_indicador' => $c['indicador']->cod_indicador,
        'num_ano' => $ano ?? (int) date('Y'),
        'num_mes' => $mes,
        'vlr_previsto' => $previsto,
        'vlr_realizado' => $realizado,
        'bln_atualizado' => 'Sim',
    ]);
}

// A3 ------------------------------------------------------------------------

test('A3: mês só com Previsto (Realizado NULL) não pinta 100% em polaridade negativa', function () {
    $c = CenarioIndicador::criar(['dsc_polaridade' => 'Negativa']);
    CenarioIndicador::regua($c['pei']);
    evolucaoDoCenario($c, 1, 10, null);

    $indicador = $c['indicador']->fresh();

    expect($indicador->atingimentoMedido((int) date('Y'), 12))->toBeNull()
        ->and($indicador->getCorFarol((int) date('Y')))->toBe(GrauSatisfacao::COR_SEM_REGUA);
});

test('A3: mês sem Realizado é ignorado — vale o último mês medido', function () {
    $c = CenarioIndicador::criar();
    evolucaoDoCenario($c, 1, 100, 80);
    evolucaoDoCenario($c, 2, 100, null);

    expect($c['indicador']->fresh()->calcularAtingimento((int) date('Y'), 12))->toBe(80.0);
});

test('A3: a lista mostra "sem medição", não 0%, para indicador sem Realizado', function () {
    $c = CenarioIndicador::criar(['nom_indicador' => 'Indicador Sem Medicao']);
    evolucaoDoCenario($c, 1, 10, null);

    Livewire::actingAs($c['user'])
        ->test(ListarIndicadores::class)
        ->assertSee('Indicador Sem Medicao')
        ->assertSee('Sem medição');
});

// A5 ------------------------------------------------------------------------

test('A5: não acumulado sem Previsto compara com a meta anual inteira, não com meta/12', function () {
    $c = CenarioIndicador::criar(['bln_acumulado' => 'Não']);
    MetaPorAno::create(['cod_indicador' => $c['indicador']->cod_indicador, 'num_ano' => (int) date('Y'), 'meta' => 90]);
    evolucaoDoCenario($c, 1, null, 85);

    $ating = $c['indicador']->fresh()->calcularAtingimento((int) date('Y'), 12);

    expect(round($ating, 2))->toBe(round(85 / 90 * 100, 2));
});

test('A5: acumulado mantém a meta proporcional aos meses (meta/12 × meses)', function () {
    $c = CenarioIndicador::criar(['bln_acumulado' => 'Sim']);
    MetaPorAno::create(['cod_indicador' => $c['indicador']->cod_indicador, 'num_ano' => (int) date('Y'), 'meta' => 120]);
    evolucaoDoCenario($c, 1, null, 10);
    evolucaoDoCenario($c, 2, null, 10);

    // previsto = 120/12 × 2 = 20; realizado = 20 → 100%
    expect($c['indicador']->fresh()->calcularAtingimento((int) date('Y'), 12))->toBe(100.0);
});

// M5 ------------------------------------------------------------------------

test('M5: indicador de iniciativa usa a régua do ciclo da iniciativa', function () {
    $c = CenarioIndicador::criar(['nom_indicador' => 'Indicador De Iniciativa']);
    CenarioIndicador::regua($c['pei']);
    $plano = CenarioIndicador::plano($c['objetivo'], $c['org']);
    $c['indicador']->update([
        'cod_objetivo' => null,
        'cod_plano_de_acao' => $plano->cod_plano_de_acao,
        'dsc_tipo' => 'Iniciativa',
    ]);
    evolucaoDoCenario($c, 1, 100, 95);

    expect($c['indicador']->fresh()->getCorFarol((int) date('Y')))->toBe('#13a313');

    Livewire::actingAs($c['user'])
        ->test(ListarIndicadores::class)
        ->assertSee('Indicador De Iniciativa')
        ->assertSeeHtml('background-color: #13a313');
});

// M6 ------------------------------------------------------------------------

test('M6: com o ano de referência sem régua, a lista não usa a régua de outro ano', function () {
    $ano = (int) date('Y');
    $c = CenarioIndicador::criar();
    CenarioIndicador::regua($c['pei'], $ano); // só faixas do ano corrente
    evolucaoDoCenario($c, 1, 100, 95, $ano - 1);
    session(['ano_selecionado' => $ano - 1]);

    Livewire::actingAs($c['user'])
        ->test(ListarIndicadores::class)
        ->assertDontSeeHtml('background-color: #13a313')
        ->assertDontSee('No alvo (90-100%)');
});

test('M6: com o ano de referência que tem régua, a lista pinta a cor da faixa', function () {
    $ano = (int) date('Y');
    $c = CenarioIndicador::criar();
    CenarioIndicador::regua($c['pei'], $ano);
    evolucaoDoCenario($c, 1, 100, 95, $ano);

    Livewire::actingAs($c['user'])
        ->test(ListarIndicadores::class)
        ->assertSeeHtml('background-color: #13a313')
        ->assertSee('No alvo (90-100%)');
});

// B8 ------------------------------------------------------------------------

test('B8: a tendência reconhece a polaridade gravada com o rótulo longo', function () {
    $c = CenarioIndicador::criar(['dsc_polaridade' => 'Negativa (Quanto menor, melhor)']);
    evolucaoDoCenario($c, 1, 10, 30);
    evolucaoDoCenario($c, 2, 10, 20);
    evolucaoDoCenario($c, 3, 10, 10);

    $t = app(IndicadorCalculoService::class)->calcularTendencia($c['indicador']->cod_indicador, 3);

    expect($t['direcao'])->toBe('Decrescente')
        ->and($t['favoravel'])->toBeTrue();
});
