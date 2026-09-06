<?php

/*
 * O caso do CEO da Presidência, exercitado pelo caminho da TELA.
 *
 * Ele escolheu a unidade "Monetário (R$)" e foi digitar R$ 20.000.000.000,00.
 * O campo era <input type="number">, que em navegador pt-BR recusa ponto de
 * milhar e vírgula decimal sem qualquer mensagem — o valor não entrava.
 *
 * O teste de unidade de UnidadeMedida prova a conversão. Este prova o que
 * importa: que o valor CHEGA AO BANCO certo quando digitado pela tela.
 */

use App\Livewire\PerformanceIndicators\LancarEvolucao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\PerformanceIndicators\EvolucaoIndicador;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

function indicadorDeUnidade(string $unidade): array
{
    $org = Organization::firstOrCreate(
        ['sgl_organizacao' => 'ORGVAL'],
        ['nom_organizacao' => 'Org Valores', 'cod_organizacao_pai' => null]
    );

    $pei = PEI::create([
        'dsc_pei' => 'Ciclo Valores '.uniqid(),
        'num_ano_inicio_pei' => (int) date('Y'),
        'num_ano_fim_pei' => (int) date('Y') + 3,
    ]);

    $perspectiva = Perspectiva::create([
        'cod_pei' => $pei->cod_pei,
        'dsc_perspectiva' => 'Orçamento',
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);

    $objetivo = Objetivo::create([
        'cod_perspectiva' => $perspectiva->cod_perspectiva,
        'nom_objetivo' => 'Executar o orçamento',
        'dsc_objetivo' => 'Objetivo para o teste de valores.',
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);

    $indicador = Indicador::create([
        'cod_objetivo' => $objetivo->cod_objetivo,
        'nom_indicador' => 'Investimento executado',
        'dsc_indicador' => 'Indicador de investimento para o teste de valores.',
        'dsc_tipo' => 'Objetivo',
        'dsc_unidade_medida' => $unidade,
        'bln_acumulado' => 'Não',
        'dsc_periodo_medicao' => 'Mensal',
        'dsc_polaridade' => 'Positiva (Quanto maior, melhor)',
    ]);

    $user = User::factory()->create();
    $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    return [$user, $indicador];
}

test('R$ 20.000.000.000,00 digitado na tela chega inteiro ao banco', function () {
    [$user, $indicador] = indicadorDeUnidade('Monetário (R$)');

    Livewire::actingAs($user)
        ->test(LancarEvolucao::class, ['indicadorId' => $indicador->cod_indicador])
        ->set('ano', (int) date('Y'))
        ->set('mes', 1)
        ->set('vlr_previsto', '20.000.000.000,00')
        ->set('vlr_realizado', '18.500.000.000,00')
        ->call('salvar')
        ->assertHasNoErrors();

    $evolucao = EvolucaoIndicador::where('cod_indicador', $indicador->cod_indicador)->first();

    expect($evolucao)->not->toBeNull()
        ->and((float) $evolucao->vlr_previsto)->toBe(20000000000.00)
        ->and((float) $evolucao->vlr_realizado)->toBe(18500000000.00);
});

test('o campo NÃO é type=number — é ele que recusa o formato brasileiro', function () {
    [$user, $indicador] = indicadorDeUnidade('Monetário (R$)');

    $html = Livewire::actingAs($user)
        ->test(LancarEvolucao::class, ['indicadorId' => $indicador->cod_indicador])
        ->html();

    // Se alguém devolver type="number", o CEO trava de novo.
    expect($html)->not->toContain('type="number" step="0.01" wire:model="vlr_previsto"')
        ->and($html)->toContain('inputmode="decimal"');
});

test('a tela mostra o símbolo e a ajuda da unidade escolhida', function () {
    [$user, $indicador] = indicadorDeUnidade('Monetário (R$)');

    $html = Livewire::actingAs($user)
        ->test(LancarEvolucao::class, ['indicadorId' => $indicador->cod_indicador])
        ->html();

    expect($html)->toContain('R$')
        ->and($html)->toContain('20.000.000.000,00');
});

test('valor gravado volta formatado ao reabrir a tela', function () {
    // Sem isto o usuário reabre e vê "20000000000.00", que não é como se lê.
    [$user, $indicador] = indicadorDeUnidade('Monetário (R$)');

    EvolucaoIndicador::create([
        'cod_indicador' => $indicador->cod_indicador,
        'num_ano' => (int) date('Y'),
        'num_mes' => 3,
        'vlr_previsto' => 20000000000.00,
        'vlr_realizado' => 18500000000.00,
    ]);

    $componente = Livewire::actingAs($user)
        ->test(LancarEvolucao::class, ['indicadorId' => $indicador->cod_indicador])
        ->set('ano', (int) date('Y'))
        ->set('mes', 3);

    expect($componente->get('vlr_previsto'))->toBe('20.000.000.000,00');
});

test('cada unidade grava com a precisão que lhe cabe', function () {
    // "Nº de Ocorrências" não admite fração; "Índice (0-1)" precisa de 4 casas.
    [$user, $indicador] = indicadorDeUnidade('Índice (0-1)');

    Livewire::actingAs($user)
        ->test(LancarEvolucao::class, ['indicadorId' => $indicador->cod_indicador])
        ->set('ano', (int) date('Y'))
        ->set('mes', 1)
        ->set('vlr_realizado', '0,8750')
        ->call('salvar')
        ->assertHasNoErrors();

    $evolucao = EvolucaoIndicador::where('cod_indicador', $indicador->cod_indicador)->first();

    expect((float) $evolucao->vlr_realizado)->toBe(0.88); // numeric(15,2) no banco
});
