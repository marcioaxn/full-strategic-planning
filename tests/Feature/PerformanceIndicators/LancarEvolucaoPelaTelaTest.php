<?php

/*
 * Lançar Evolução exercitado pelo caminho da TELA (métodos do componente).
 * Origem: revisão adversária do módulo de indicadores, 04/10/2026.
 */

use App\Livewire\PerformanceIndicators\LancarEvolucao;
use App\Models\PerformanceIndicators\EvolucaoIndicador;
use App\Models\StrategicPlanning\Arquivo;
use Livewire\Livewire;
use Tests\Feature\PerformanceIndicators\CenarioIndicador;

function lancarEvolucaoTela(array $cenario)
{
    return Livewire::actingAs($cenario['user'])
        ->test(LancarEvolucao::class, ['indicadorId' => $cenario['indicador']->cod_indicador])
        ->set('ano', (int) date('Y'))
        ->set('mes', 1);
}

// A1 ------------------------------------------------------------------------

test('A1: desligar o switch "Marcar como Atualizado" grava "Não"', function () {
    $c = CenarioIndicador::criar();

    // O wire:model de checkbox do Livewire 4 envia booleano, nunca "Sim"/"Não".
    lancarEvolucaoTela($c)
        ->set('vlr_realizado', '50,00')
        ->set('bln_atualizado', false)
        ->call('salvar')
        ->assertHasNoErrors();

    expect(EvolucaoIndicador::where('cod_indicador', $c['indicador']->cod_indicador)->value('bln_atualizado'))->toBe('Não');
});

test('A1: ligar o switch grava "Sim"', function () {
    $c = CenarioIndicador::criar();

    lancarEvolucaoTela($c)
        ->set('vlr_realizado', '50,00')
        ->set('bln_atualizado', true)
        ->call('salvar')
        ->assertHasNoErrors();

    expect(EvolucaoIndicador::where('cod_indicador', $c['indicador']->cod_indicador)->value('bln_atualizado'))->toBe('Sim');
});

test('A1: mês gravado como "Não" abre com o switch desligado', function () {
    $c = CenarioIndicador::criar();
    EvolucaoIndicador::create([
        'cod_indicador' => $c['indicador']->cod_indicador,
        'num_ano' => (int) date('Y'), 'num_mes' => 1,
        'vlr_previsto' => 10, 'vlr_realizado' => 5, 'bln_atualizado' => 'Não',
    ]);

    expect(lancarEvolucaoTela($c)->get('bln_atualizado'))->toBeFalse();
});

test('A1: o switch não usa true-value/false-value e tem mensagem de erro visível', function () {
    $c = CenarioIndicador::criar();
    $html = lancarEvolucaoTela($c)->html();

    expect($html)->not->toContain('true-value=')
        ->and(file_get_contents(resource_path('views/livewire/indicador/lancar-evolucao.blade.php')))
        ->toContain("@error('bln_atualizado')");
});

// A3 ------------------------------------------------------------------------

test('A3: Realizado em branco é gravado como NULL, não como zero', function () {
    $c = CenarioIndicador::criar();

    lancarEvolucaoTela($c)
        ->set('vlr_previsto', '80,00')
        ->set('vlr_realizado', '')
        ->call('salvar')
        ->assertHasNoErrors();

    $ev = EvolucaoIndicador::where('cod_indicador', $c['indicador']->cod_indicador)->first();

    expect($ev->vlr_realizado)->toBeNull()
        ->and((float) $ev->vlr_previsto)->toBe(80.0);
});

test('A3: Previsto em branco é gravado como NULL', function () {
    $c = CenarioIndicador::criar();

    lancarEvolucaoTela($c)
        ->set('vlr_previsto', '')
        ->set('vlr_realizado', '85,00')
        ->call('salvar')
        ->assertHasNoErrors();

    expect(EvolucaoIndicador::where('cod_indicador', $c['indicador']->cod_indicador)->first()->vlr_previsto)->toBeNull();
});

test('A3: zero digitado continua sendo zero', function () {
    $c = CenarioIndicador::criar();

    lancarEvolucaoTela($c)->set('vlr_realizado', '0,00')->call('salvar')->assertHasNoErrors();

    expect(EvolucaoIndicador::where('cod_indicador', $c['indicador']->cod_indicador)->first()->vlr_realizado)->not->toBeNull();
});

// B4 ------------------------------------------------------------------------

test('B4: valor acima do que a coluna comporta vira mensagem de validação, não erro 500', function () {
    $c = CenarioIndicador::criar(['dsc_unidade_medida' => 'Monetário (R$)']);

    lancarEvolucaoTela($c)
        ->set('vlr_realizado', '9.999.999.999.999.999,00')
        ->call('salvar')
        ->assertHasErrors(['vlr_realizado']);

    expect(EvolucaoIndicador::where('cod_indicador', $c['indicador']->cod_indicador)->count())->toBe(0);
});

// M1 ------------------------------------------------------------------------

test('M1: indicador de cálculo automático não abre o Lançar Evolução', function () {
    $c = CenarioIndicador::criar();
    $plano = CenarioIndicador::plano($c['objetivo'], $c['org']);
    $c['indicador']->update([
        'cod_objetivo' => null,
        'cod_plano_de_acao' => $plano->cod_plano_de_acao,
        'dsc_tipo' => 'Iniciativa',
        'dsc_calculation_type' => 'action_plan',
    ]);

    $this->actingAs($c['user'])
        ->get(route('indicadores.evolucao', $c['indicador']->cod_indicador))
        ->assertForbidden();
});

// M2 ------------------------------------------------------------------------

test('M2: a evidência tem link para abrir e o X de excluir pede confirmação', function () {
    $c = CenarioIndicador::criar();
    $ev = EvolucaoIndicador::create([
        'cod_indicador' => $c['indicador']->cod_indicador,
        'num_ano' => (int) date('Y'), 'num_mes' => 1, 'vlr_realizado' => 5, 'bln_atualizado' => 'Sim',
    ]);
    $arquivo = Arquivo::create([
        'cod_evolucao_indicador' => $ev->cod_evolucao_indicador,
        'txt_assunto' => 'relatorio.pdf',
        'data' => now()->format('Y-m-d'),
        'dsc_nome_arquivo' => 'pei/evidencias/relatorio.pdf',
        'dsc_tipo' => 'pdf',
    ]);

    $html = lancarEvolucaoTela($c)->html();

    expect($html)->toContain(route('indicadores.evidencia', $arquivo->cod_arquivo))
        ->and($html)->toMatch('/wire:click="excluirArquivo\(\''.$arquivo->cod_arquivo.'\'\)"[^>]*wire:confirm=/');
});
