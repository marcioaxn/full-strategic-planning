<?php

/*
 * Correções da revisão adversária de 04/10/2026 em Graus de Satisfação, pelo
 * caminho da tela (ListarGrausSatisfacao e DetalharGrauSatisfacao).
 */

use App\Livewire\StrategicPlanning\DetalharGrauSatisfacao;
use App\Livewire\StrategicPlanning\ListarGrausSatisfacao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\PerformanceIndicators\EvolucaoIndicador;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

/** @return array{user: User, pei: PEI, outro: PEI} */
function cenarioGrausRevisao(): array
{
    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'cod_organizacao_pai' => null]);
    $pei = PEI::create(['dsc_pei' => 'Ciclo atual', 'num_ano_inicio_pei' => (int) date('Y') - 1, 'num_ano_fim_pei' => (int) date('Y') + 2]);
    $outro = PEI::create(['dsc_pei' => 'Outro ciclo', 'num_ano_inicio_pei' => 2020, 'num_ano_fim_pei' => 2023]);

    $user = User::factory()->create(['ativo' => true]);
    $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    return compact('user', 'pei', 'outro');
}

test('a busca não traz faixas de outros ciclos', function () {
    $c = cenarioGrausRevisao();
    GrauSatisfacao::create(['cod_pei' => $c['pei']->cod_pei, 'dsc_grau_satisfacao' => 'Crítico', 'cor' => '#aa0000', 'vlr_minimo' => 0, 'vlr_maximo' => 50]);
    GrauSatisfacao::create(['cod_pei' => $c['outro']->cod_pei, 'dsc_grau_satisfacao' => 'Faixa do outro ciclo', 'cor' => '#ffaa00', 'vlr_minimo' => 0, 'vlr_maximo' => 50]);

    $graus = Livewire::actingAs($c['user'])->test(ListarGrausSatisfacao::class)
        ->set('search', 'a')
        ->viewData('graus');

    expect(collect($graus->items())->pluck('cod_pei')->unique()->values()->all())->toBe([$c['pei']->cod_pei]);
});

test('"Todo o Ciclo" (ano vazio) grava faixa geral, sem erro técnico', function () {
    $c = cenarioGrausRevisao();

    Livewire::actingAs($c['user'])->test(ListarGrausSatisfacao::class)
        ->call('openModal')
        ->set('dsc_grau_satisfacao', 'Geral')
        ->set('cor', '#00aa00')
        ->set('vlr_minimo', '0,00')
        ->set('vlr_maximo', '100,00')
        ->set('num_ano', (int) date('Y'))
        ->set('num_ano', '')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showErrorModal', false)
        ->assertSet('showSuccessModal', true);

    expect(GrauSatisfacao::where('cod_pei', $c['pei']->cod_pei)->sole()->num_ano)->toBeNull();
});

test('editar faixa de um ano para "Todo o Ciclo" torna-a geral', function () {
    $c = cenarioGrausRevisao();
    $grau = GrauSatisfacao::create(['cod_pei' => $c['pei']->cod_pei, 'num_ano' => (int) date('Y'), 'dsc_grau_satisfacao' => 'Do ano', 'cor' => '#00aa00', 'vlr_minimo' => 0, 'vlr_maximo' => 100]);

    Livewire::actingAs($c['user'])->test(ListarGrausSatisfacao::class)
        ->call('edit', $grau->cod_grau_satisfacao)
        ->set('num_ano', '')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showErrorModal', false);

    expect($grau->fresh()->num_ano)->toBeNull();
});

test('ano fora do formato é recusado com mensagem, não com erro técnico', function () {
    $c = cenarioGrausRevisao();

    Livewire::actingAs($c['user'])->test(ListarGrausSatisfacao::class)
        ->call('openModal')
        ->set('dsc_grau_satisfacao', 'Geral')
        ->set('cor', '#00aa00')
        ->set('vlr_minimo', '0,00')
        ->set('vlr_maximo', '100,00')
        ->set('num_ano', 'abc')
        ->call('save')
        ->assertHasErrors(['num_ano'])
        ->assertSet('showErrorModal', false);
});

test('o detalhe da faixa classifica pelo atingimento sem arredondar, como o farol do mapa', function () {
    $c = cenarioGrausRevisao();
    $ano = (int) date('Y') - 1;

    $critico = GrauSatisfacao::create(['cod_pei' => $c['pei']->cod_pei, 'dsc_grau_satisfacao' => 'Crítico', 'cor' => '#aa0000', 'vlr_minimo' => 0, 'vlr_maximo' => 50]);
    $atencao = GrauSatisfacao::create(['cod_pei' => $c['pei']->cod_pei, 'dsc_grau_satisfacao' => 'Atenção', 'cor' => '#ccaa00', 'vlr_minimo' => 50.01, 'vlr_maximo' => 100]);

    $perspectiva = Perspectiva::create(['cod_pei' => $c['pei']->cod_pei, 'dsc_perspectiva' => 'P', 'num_nivel_hierarquico_apresentacao' => 1]);
    $objetivo = Objetivo::create(['cod_perspectiva' => $perspectiva->cod_perspectiva, 'nom_objetivo' => 'O', 'dsc_objetivo' => 'D', 'num_nivel_hierarquico_apresentacao' => 1]);
    $indicador = Indicador::create([
        'cod_objetivo' => $objetivo->cod_objetivo, 'nom_indicador' => 'Indicador limítrofe', 'dsc_indicador' => 'I',
        'dsc_tipo' => 'Objetivo', 'dsc_unidade_medida' => 'Unidade', 'bln_acumulado' => 'Não',
        'dsc_periodo_medicao' => 'Mensal', 'dsc_polaridade' => 'Positiva', 'dsc_calculation_type' => 'manual',
    ]);
    EvolucaoIndicador::create([
        'cod_indicador' => $indicador->cod_indicador, 'num_ano' => $ano, 'num_mes' => 1,
        'vlr_previsto' => 10000, 'vlr_realizado' => 5004, 'bln_atualizado' => 'Sim',
    ]);

    $bruto = $indicador->fresh()->calcularAtingimento($ano);
    // Premissa do cenário: um valor que o arredondamento a 1 casa leva para a faixa de baixo.
    expect(round($bruto, 1))->toBeLessThanOrEqual(50.0)
        ->and($bruto)->toBeGreaterThanOrEqual(50.01);

    Session::put('ano_selecionado', $ano);

    $noCritico = Livewire::actingAs($c['user'])->test(DetalharGrauSatisfacao::class, ['id' => $critico->cod_grau_satisfacao])->get('indicadoresNaFaixa');
    $naAtencao = Livewire::actingAs($c['user'])->test(DetalharGrauSatisfacao::class, ['id' => $atencao->cod_grau_satisfacao])->get('indicadoresNaFaixa');

    expect(collect($noCritico)->pluck('cod')->all())->not->toContain($indicador->cod_indicador)
        ->and(collect($naAtencao)->pluck('cod')->all())->toContain($indicador->cod_indicador);
});
