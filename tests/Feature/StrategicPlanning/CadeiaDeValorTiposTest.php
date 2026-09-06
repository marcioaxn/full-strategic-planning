<?php

/*
 * O teste que importa aqui NÃO é "a atividade foi salva" — esse passaria verde
 * com o defeito de pé. É "a atividade APARECE na tela".
 *
 * Antes, o combo e a validação liam a constante, mas o agrupamento nomeava dois
 * grupos fixos ('Finalística' e 'Suporte'). Acrescentar um tipo fazia a opção
 * aparecer, o cliente salvar, e o registro sumir — erro silencioso, do tipo que
 * aparece semanas depois.
 */

use App\Livewire\StrategicPlanning\CadeiaDeValor;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\AtividadeCadeiaValor;
use App\Models\StrategicPlanning\PEI;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

function cenarioDaCadeia(): array
{
    $org = Organization::create([
        'nom_organizacao' => 'Org Cadeia',
        'sgl_organizacao' => 'ORGC',
        'cod_organizacao_pai' => null,
    ]);

    $pei = PEI::create([
        'dsc_pei' => 'Ciclo Cadeia',
        'num_ano_inicio_pei' => (int) date('Y'),
        'num_ano_fim_pei' => (int) date('Y') + 3,
    ]);

    $user = User::factory()->create();
    $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    return [$user, $pei];
}

test('"Valores públicos" é uma opção do campo Tipo', function () {
    expect(AtividadeCadeiaValor::TIPOS)->toContain('Valores públicos');
});

test('cada tipo declarado tem um grupo exibido na tela', function () {
    [$user] = cenarioDaCadeia();

    $componente = Livewire::actingAs($user)->test(CadeiaDeValor::class)->assertOk();

    $tiposExibidos = collect($componente->viewData('grupos'))->pluck('tipo')->all();

    expect($tiposExibidos)->toBe(AtividadeCadeiaValor::TIPOS);
});

test('atividade do tipo novo é salva E aparece na listagem', function () {
    [$user, $pei] = cenarioDaCadeia();

    $componente = Livewire::actingAs($user)
        ->test(CadeiaDeValor::class)
        ->call('novaAtividade')
        ->set('formAtividade.dsc_atividade', 'Confiança da sociedade nas instituições')
        ->set('formAtividade.dsc_tipo', 'Valores públicos')
        ->call('salvarAtividade')
        ->assertHasNoErrors();

    // 1) Gravou.
    expect(AtividadeCadeiaValor::where('dsc_tipo', 'Valores públicos')->count())->toBe(1);

    // 2) E aparece — que é o que o teste antigo não teria checado.
    $grupo = collect($componente->viewData('grupos'))->firstWhere('tipo', 'Valores públicos');

    expect($grupo)->not->toBeNull()
        ->and($grupo['itens'])->toHaveCount(1)
        ->and($grupo['itens']->first()->dsc_atividade)
        ->toBe('Confiança da sociedade nas instituições');
});

test('cada um dos tipos declarados é salvo e exibido', function () {
    [$user] = cenarioDaCadeia();

    foreach (AtividadeCadeiaValor::TIPOS as $i => $tipo) {
        Livewire::actingAs($user)
            ->test(CadeiaDeValor::class)
            ->call('novaAtividade')
            ->set('formAtividade.dsc_atividade', "Atividade {$i}")
            ->set('formAtividade.dsc_tipo', $tipo)
            ->call('salvarAtividade')
            ->assertHasNoErrors();
    }

    $grupos = collect(
        Livewire::actingAs($user)->test(CadeiaDeValor::class)->viewData('grupos')
    );

    foreach (AtividadeCadeiaValor::TIPOS as $tipo) {
        expect($grupos->firstWhere('tipo', $tipo)['itens'])->toHaveCount(1);
    }
});

test('tipo fora da constante é recusado pela validação', function () {
    [$user] = cenarioDaCadeia();

    Livewire::actingAs($user)
        ->test(CadeiaDeValor::class)
        ->call('novaAtividade')
        ->set('formAtividade.dsc_atividade', 'Tipo inventado')
        ->set('formAtividade.dsc_tipo', 'Categoria Inexistente')
        ->call('salvarAtividade')
        ->assertHasErrors(['formAtividade.dsc_tipo']);
});

test('tipo antigo, já fora da constante, continua visível na tela', function () {
    // Dado de cliente não pode desaparecer em silêncio quando o vocabulário
    // muda — o grupo é montado, mesmo sem apresentação declarada.
    [$user, $pei] = cenarioDaCadeia();

    AtividadeCadeiaValor::create([
        'cod_pei' => $pei->cod_pei,
        'dsc_atividade' => 'Atividade de um tipo legado',
        'dsc_tipo' => 'Tipo Descontinuado',
        'num_ordem' => 0,
    ]);

    $grupos = collect(
        Livewire::actingAs($user)->test(CadeiaDeValor::class)->viewData('grupos')
    );

    expect($grupos->firstWhere('tipo', 'Tipo Descontinuado')['itens'])->toHaveCount(1);
});
