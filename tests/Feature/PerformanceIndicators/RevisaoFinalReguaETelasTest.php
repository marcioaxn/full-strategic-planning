<?php

/*
 * Achados da revisão hostil do conjunto de correções de 04/10/2026: regras que
 * divergiam de uma tela para outra depois que quatro agentes corrigiram em
 * paralelo. Cada teste vai pelo caminho que a tela usa.
 */

use App\Exports\RiscosExport;
use App\Livewire\Dashboard\Index as Dashboard;
use App\Livewire\PerformanceIndicators\LancarEvolucao;
use App\Livewire\PerformanceIndicators\ListarIndicadores;
use App\Livewire\StrategicPlanning\DetalharGrauSatisfacao;
use App\Models\Organization;
use App\Models\PerformanceIndicators\EvolucaoIndicador;
use App\Models\PerformanceIndicators\MetaPorAno;
use App\Models\RiskManagement\Risco;
use App\Models\StrategicPlanning\GrauSatisfacao;
use Livewire\Livewire;
use Tests\Feature\PerformanceIndicators\CenarioIndicador;

test('a legenda da lista de indicadores mostra só a régua que pinta o farol', function () {
    $ano = (int) date('Y');
    $c = CenarioIndicador::criar();
    GrauSatisfacao::create(['cod_pei' => $c['pei']->cod_pei, 'num_ano' => null, 'dsc_grau_satisfacao' => 'Neutro geral', 'cor' => '#999999', 'vlr_minimo' => 0, 'vlr_maximo' => 100]);
    CenarioIndicador::regua($c['pei'], $ano);

    Livewire::actingAs($c['user'])->test(ListarIndicadores::class)
        ->assertSee('No alvo')
        ->assertDontSee('Neutro geral');
});

test('o gráfico do dashboard usa a meta do mês para indicador acumulado com previsto em branco', function () {
    $ano = (int) date('Y');
    $c = CenarioIndicador::criar(['bln_acumulado' => 'Sim']);
    MetaPorAno::create(['cod_indicador' => $c['indicador']->cod_indicador, 'num_ano' => $ano, 'meta' => 120]);
    EvolucaoIndicador::create(['cod_indicador' => $c['indicador']->cod_indicador, 'num_ano' => $ano, 'num_mes' => 1, 'vlr_previsto' => null, 'vlr_realizado' => 10, 'bln_atualizado' => 'Sim']);

    $grafico = (fn () => $this->getChartEvolucao())->call(
        Livewire::actingAs($c['user'])->test(Dashboard::class)->instance()
    );

    // 10 ÷ (120/12) = 100%. Sem bln_acumulado no eager load dava 10 ÷ 120 = 8,3%.
    expect($grafico['data'][0])->toBe(100.0);
});

test('o detalhe da faixa não lista indicador sem medição na pior faixa', function () {
    $ano = (int) date('Y');
    $c = CenarioIndicador::criar();
    CenarioIndicador::regua($c['pei'], $ano);
    // Realizado lançado, sem previsto e sem meta: o farol fica cinza ("Sem medição").
    EvolucaoIndicador::create(['cod_indicador' => $c['indicador']->cod_indicador, 'num_ano' => $ano, 'num_mes' => 1, 'vlr_previsto' => null, 'vlr_realizado' => 10, 'bln_atualizado' => 'Sim']);
    $critico = GrauSatisfacao::where('cod_pei', $c['pei']->cod_pei)->where('dsc_grau_satisfacao', 'Crítico')->first();

    $tela = Livewire::actingAs($c['user'])->test(DetalharGrauSatisfacao::class, ['id' => $critico->cod_grau_satisfacao]);

    expect(collect($tela->get('indicadoresNaFaixa'))->pluck('nome'))->not->toContain('Indicador do cenário');
});

test('indicador marcado como automático mas sem iniciativa recebe lançamento manual', function () {
    $c = CenarioIndicador::criar(['dsc_calculation_type' => 'action_plan']);

    Livewire::actingAs($c['user'])
        ->test(LancarEvolucao::class, ['indicadorId' => $c['indicador']->cod_indicador])
        ->assertOk();
});

test('o Excel de riscos traz os riscos das unidades subordinadas, como a lista e a matriz', function () {
    $c = CenarioIndicador::criar();
    $filha = Organization::create(['nom_organizacao' => 'Filha', 'sgl_organizacao' => 'FIL', 'rel_cod_organizacao' => $c['org']->cod_organizacao]);

    foreach ([[$c['org'], 'Risco da mãe'], [$filha, 'Risco da filha']] as [$org, $titulo]) {
        Risco::create([
            'cod_pei' => $c['pei']->cod_pei, 'cod_organizacao' => $org->cod_organizacao, 'dsc_titulo' => $titulo,
            'txt_descricao' => 'D', 'dsc_categoria' => 'Operacional', 'num_probabilidade' => 2, 'num_impacto' => 2,
            'dsc_status' => 'Identificado', 'cod_responsavel_monitoramento' => $c['user']->id,
        ]);
    }

    $titulos = (new RiscosExport($c['org']->cod_organizacao))->collection()->pluck('dsc_titulo');

    expect($titulos)->toContain('Risco da mãe')->toContain('Risco da filha');
});
