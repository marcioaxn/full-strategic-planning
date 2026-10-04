<?php

/*
 * Gerenciar Metas e Linha de Base pelo caminho da TELA. O campo era
 * type="number" step="0.01": recusava "20.000.000.000,00", bloqueava 0,875 e
 * aceitava 3,75 unidades. Origem: revisão adversária, 04/10/2026.
 */

use App\Livewire\PerformanceIndicators\ListarIndicadores;
use App\Models\PerformanceIndicators\LinhaBaseIndicador;
use App\Models\PerformanceIndicators\MetaPorAno;
use Livewire\Livewire;
use Tests\Feature\PerformanceIndicators\CenarioIndicador;

function telaDeMetas(array $c)
{
    return Livewire::actingAs($c['user'])
        ->test(ListarIndicadores::class)
        ->call('abrirMetas', $c['indicador']->cod_indicador);
}

// A2 ------------------------------------------------------------------------

test('A2: meta de R$ 20.000.000.000,00 digitada no formato brasileiro chega ao banco', function () {
    $c = CenarioIndicador::criar(['dsc_unidade_medida' => 'Monetário (R$)']);

    telaDeMetas($c)
        ->set('metaAno', (int) date('Y'))
        ->set('metaValor', '20.000.000.000,00')
        ->call('salvarMeta')
        ->assertHasNoErrors();

    expect((float) MetaPorAno::where('cod_indicador', $c['indicador']->cod_indicador)->value('meta'))->toBe(20000000000.0);
});

test('A2/A4: meta de Índice (0-1) guarda as quatro casas', function () {
    $c = CenarioIndicador::criar(['dsc_unidade_medida' => 'Índice (0-1)']);

    telaDeMetas($c)
        ->set('metaAno', (int) date('Y'))
        ->set('metaValor', '0,8750')
        ->call('salvarMeta')
        ->assertHasNoErrors();

    expect((float) MetaPorAno::where('cod_indicador', $c['indicador']->cod_indicador)->value('meta'))->toBe(0.875);
});

test('A2: unidade inteira recusa meta fracionária', function () {
    $c = CenarioIndicador::criar(['dsc_unidade_medida' => 'Quantidade (un)']);

    telaDeMetas($c)
        ->set('metaAno', (int) date('Y'))
        ->set('metaValor', '3,75')
        ->call('salvarMeta')
        ->assertHasErrors(['metaValor']);

    expect(MetaPorAno::where('cod_indicador', $c['indicador']->cod_indicador)->count())->toBe(0);
});

test('A2: linha de base no formato brasileiro chega ao banco', function () {
    $c = CenarioIndicador::criar(['dsc_unidade_medida' => 'Monetário (R$)']);

    Livewire::actingAs($c['user'])
        ->test(ListarIndicadores::class)
        ->call('abrirLinhaBase', $c['indicador']->cod_indicador)
        ->set('linhaBaseAno', (int) date('Y') - 1)
        ->set('linhaBaseValor', '1.234.567,89')
        ->call('salvarLinhaBase')
        ->assertHasNoErrors();

    expect((float) LinhaBaseIndicador::where('cod_indicador', $c['indicador']->cod_indicador)->value('num_linha_base'))->toBe(1234567.89);
});

test('A2: os campos de meta e linha de base não são type=number e usam a máscara da unidade', function () {
    $blade = file_get_contents(resource_path('views/livewire/indicador/listar-indicadores.blade.php'));

    expect($blade)->not->toMatch('/type="number"[^>]*wire:model="(metaValor|linhaBaseValor)"/')
        ->and($blade)->toMatch('/wire:model="metaValor"[^>]*x-mask:dynamic/s')
        ->and($blade)->toMatch('/wire:model="linhaBaseValor"[^>]*x-mask:dynamic/s');
});

test('A2: valor gravado volta formatado pela unidade na tabela do modal', function () {
    $c = CenarioIndicador::criar(['dsc_unidade_medida' => 'Monetário (R$)']);
    MetaPorAno::create(['cod_indicador' => $c['indicador']->cod_indicador, 'num_ano' => (int) date('Y'), 'meta' => 20000000000]);

    telaDeMetas($c)->assertSee('R$ 20.000.000.000,00');
});

test('B4: meta acima do que a coluna comporta vira mensagem de validação', function () {
    $c = CenarioIndicador::criar(['dsc_unidade_medida' => 'Monetário (R$)']);

    telaDeMetas($c)
        ->set('metaAno', (int) date('Y'))
        ->set('metaValor', '9.999.999.999.999.999,00')
        ->call('salvarMeta')
        ->assertHasErrors(['metaValor']);
});

// M3 ------------------------------------------------------------------------

test('M3: excluir meta e excluir linha de base pedem confirmação', function () {
    $c = CenarioIndicador::criar();
    $meta = MetaPorAno::create(['cod_indicador' => $c['indicador']->cod_indicador, 'num_ano' => (int) date('Y'), 'meta' => 90]);
    $lb = LinhaBaseIndicador::create(['cod_indicador' => $c['indicador']->cod_indicador, 'num_ano' => (int) date('Y') - 1, 'num_linha_base' => 50]);

    $htmlMetas = telaDeMetas($c)->html();
    $htmlLb = Livewire::actingAs($c['user'])->test(ListarIndicadores::class)
        ->call('abrirLinhaBase', $c['indicador']->cod_indicador)->html();

    expect($htmlMetas)->toMatch('/wire:click="excluirMeta\(\''.$meta->cod_meta_por_ano.'\'\)"[^>]*wire:confirm=/')
        ->and($htmlLb)->toMatch('/wire:click="excluirLinhaBase\(\''.$lb->cod_linha_base.'\'\)"[^>]*wire:confirm=/');
});
