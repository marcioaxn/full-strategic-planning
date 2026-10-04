<?php

/*
 * Ficha Técnica (DetalharIndicador). Origem: revisão adversária, 04/10/2026.
 */

use App\Livewire\PerformanceIndicators\DetalharIndicador;
use App\Models\PerformanceIndicators\EvolucaoIndicador;
use App\Models\PerformanceIndicators\MetaPorAno;
use Livewire\Livewire;
use Tests\Feature\PerformanceIndicators\CenarioIndicador;

test('B1: a ficha mostra os valores com o símbolo e as casas da unidade', function () {
    $c = CenarioIndicador::criar(['dsc_unidade_medida' => 'Monetário (R$)']);
    MetaPorAno::create(['cod_indicador' => $c['indicador']->cod_indicador, 'num_ano' => (int) date('Y'), 'meta' => 20000000000]);
    EvolucaoIndicador::create([
        'cod_indicador' => $c['indicador']->cod_indicador, 'num_ano' => (int) date('Y'), 'num_mes' => 1,
        'vlr_previsto' => 1000, 'vlr_realizado' => 900, 'bln_atualizado' => 'Sim',
    ]);

    Livewire::actingAs($c['user'])
        ->test(DetalharIndicador::class, ['id' => $c['indicador']->cod_indicador])
        ->assertSee('R$ 20.000.000.000,00')
        ->assertSee('R$ 900,00');
});

test('B1: quantidade aparece sem casas decimais', function () {
    $c = CenarioIndicador::criar(['dsc_unidade_medida' => 'Quantidade (un)']);
    EvolucaoIndicador::create([
        'cod_indicador' => $c['indicador']->cod_indicador, 'num_ano' => (int) date('Y'), 'num_mes' => 1,
        'vlr_previsto' => 12, 'vlr_realizado' => 12, 'bln_atualizado' => 'Sim',
    ]);

    Livewire::actingAs($c['user'])
        ->test(DetalharIndicador::class, ['id' => $c['indicador']->cod_indicador])
        ->assertSee('12 un')
        ->assertDontSee('12,00');
});

test('B3: ano da sessão fora dos ciclos não desencontra o seletor do gráfico', function () {
    $c = CenarioIndicador::criar();
    session(['ano_selecionado' => 1999]);

    $componente = Livewire::actingAs($c['user'])
        ->test(DetalharIndicador::class, ['id' => $c['indicador']->cod_indicador]);

    expect($componente->get('anosDisponiveis'))->toContain($componente->get('anoFiltro'));
});

test('B3: ano inventado vindo do navegador é recusado', function () {
    $c = CenarioIndicador::criar();

    $componente = Livewire::actingAs($c['user'])
        ->test(DetalharIndicador::class, ['id' => $c['indicador']->cod_indicador])
        ->set('anoFiltro', 1800);

    expect($componente->get('anosDisponiveis'))->toContain($componente->get('anoFiltro'));
});

test('B2: indicador informativo não aparece em vermelho no detalhamento mensal', function () {
    $c = CenarioIndicador::criar(['dsc_polaridade' => 'Não Aplicável']);
    EvolucaoIndicador::create([
        'cod_indicador' => $c['indicador']->cod_indicador, 'num_ano' => (int) date('Y'), 'num_mes' => 1,
        'vlr_previsto' => 10, 'vlr_realizado' => 3, 'bln_atualizado' => 'Sim',
    ]);

    Livewire::actingAs($c['user'])
        ->test(DetalharIndicador::class, ['id' => $c['indicador']->cod_indicador])
        ->assertDontSeeHtml('progress-bar bg-danger')
        ->assertDontSeeHtml('0,0%');
});
