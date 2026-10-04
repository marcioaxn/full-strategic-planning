<?php

/*
 * Ressalvas anotadas no teste pelo navegador de 04/10/2026 (controle em
 * documentacao/testes/TESTE-FUNCIONAL-NAVEGADOR.md, linhas 100 e 101).
 */

use App\Livewire\ActionPlan\ListarPlanos;
use App\Livewire\PerformanceIndicators\LancarEvolucao;
use App\Models\PerformanceIndicators\EvolucaoIndicador;
use Livewire\Livewire;
use Tests\Feature\PerformanceIndicators\CenarioIndicador;

test('o ano que vem no endereço de /planos vale, em vez de ser trocado pelo do topo', function () {
    $c = CenarioIndicador::criar();
    $outroAno = (int) date('Y') + 1;

    Livewire::withQueryParams(['filtroAno' => (string) $outroAno])
        ->actingAs($c['user'])
        ->test(ListarPlanos::class)
        ->assertSet('filtroAno', (string) $outroAno);
});

test('sem ano no endereço, /planos usa o ano de referência do topo', function () {
    $c = CenarioIndicador::criar();

    Livewire::actingAs($c['user'])->test(ListarPlanos::class)
        ->assertSet('filtroAno', (int) date('Y'));
});

test('lançar evolução sem nenhum valor, análise ou evidência não grava um mês vazio', function () {
    $c = CenarioIndicador::criar();

    Livewire::actingAs($c['user'])
        ->test(LancarEvolucao::class, ['indicadorId' => $c['indicador']->cod_indicador])
        ->set('vlr_previsto', '')
        ->set('vlr_realizado', '')
        ->set('txt_avaliacao', '')
        ->call('salvar')
        ->assertHasErrors('vlr_realizado');

    expect(EvolucaoIndicador::where('cod_indicador', $c['indicador']->cod_indicador)->count())->toBe(0);

    // Com só a análise preenchida, grava.
    Livewire::actingAs($c['user'])
        ->test(LancarEvolucao::class, ['indicadorId' => $c['indicador']->cod_indicador])
        ->set('txt_avaliacao', 'Mês sem coleta por greve.')
        ->call('salvar')
        ->assertHasNoErrors();

    expect(EvolucaoIndicador::where('cod_indicador', $c['indicador']->cod_indicador)->count())->toBe(1);
});
