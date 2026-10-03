<?php

/*
 * Regressão do defeito mostrado ao cliente na reunião de 02/10/2026: ao
 * cadastrar um grau de satisfação, o percentual máximo ficava "travado" em
 * 100,00 e não aceitava 29,99.
 *
 * Causa: a máscara do campo aceitava no máximo 5 dígitos e reescrevia o texto a
 * cada tecla, levando o cursor para o fim. Com "100,00" já preenchido, todo
 * dígito novo era cortado.
 *
 * O campo agora é texto no formato brasileiro, igual ao do Lançar Evolução, e
 * o servidor lê "29,99". Os testes vão pelo caminho da tela: as propriedades
 * do componente recebem o texto como o navegador envia e o save() é chamado.
 */

use App\Livewire\StrategicPlanning\ListarGrausSatisfacao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\PEI;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

function cenarioGrauSatisfacao(): array
{
    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'cod_organizacao_pai' => null]);
    $pei = PEI::create([
        'dsc_pei' => 'Ciclo Graus',
        'num_ano_inicio_pei' => (int) date('Y'),
        'num_ano_fim_pei' => (int) date('Y') + 3,
    ]);

    $user = User::factory()->create();
    $user->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');
    $user->organizacoes()->syncWithoutDetaching([$org->cod_organizacao]);

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    return [$user, $pei];
}

test('grava o percentual máximo 29,99 digitado no formato brasileiro', function () {
    [$user, $pei] = cenarioGrauSatisfacao();

    Livewire::actingAs($user)
        ->test(ListarGrausSatisfacao::class)
        ->call('openModal')
        ->set('dsc_grau_satisfacao', 'Crítico')
        ->set('cor', '#dc3545')
        ->set('vlr_minimo', '0,00')
        ->set('vlr_maximo', '29,99')
        ->call('save')
        ->assertHasNoErrors();

    $grau = GrauSatisfacao::where('cod_pei', $pei->cod_pei)->sole();

    expect((float) $grau->vlr_minimo)->toBe(0.0)
        ->and((float) $grau->vlr_maximo)->toBe(29.99);
});

test('ao editar, a faixa abre no formato do campo e aceita trocar 100,00 por 29,99', function () {
    [$user, $pei] = cenarioGrauSatisfacao();
    $grau = GrauSatisfacao::create([
        'cod_pei' => $pei->cod_pei, 'dsc_grau_satisfacao' => 'Crítico', 'cor' => '#dc3545',
        'vlr_minimo' => 0, 'vlr_maximo' => 100,
    ]);

    Livewire::actingAs($user)
        ->test(ListarGrausSatisfacao::class)
        ->call('edit', $grau->cod_grau_satisfacao)
        ->assertSet('vlr_maximo', '100,00')
        ->set('vlr_maximo', '29,99')
        ->call('save')
        ->assertHasNoErrors();

    expect((float) $grau->fresh()->vlr_maximo)->toBe(29.99);
});

test('recusa faixa que cruza outra do mesmo ciclo e aceita a que só encosta no limite', function () {
    [$user, $pei] = cenarioGrauSatisfacao();
    GrauSatisfacao::create([
        'cod_pei' => $pei->cod_pei, 'dsc_grau_satisfacao' => 'Crítico', 'cor' => '#dc3545',
        'vlr_minimo' => 0, 'vlr_maximo' => 29.99,
    ]);

    $tela = Livewire::actingAs($user)->test(ListarGrausSatisfacao::class)
        ->call('openModal')
        ->set('dsc_grau_satisfacao', 'Atenção')
        ->set('cor', '#ffc107')
        ->set('vlr_minimo', '20,00')
        ->set('vlr_maximo', '60,00')
        ->call('save')
        ->assertHasErrors(['vlr_maximo']);

    $tela->set('vlr_minimo', '29,99')->call('save')->assertHasNoErrors();

    expect(GrauSatisfacao::where('cod_pei', $pei->cod_pei)->count())->toBe(2);
});

test('recusa texto que não é percentual', function () {
    [$user] = cenarioGrauSatisfacao();

    Livewire::actingAs($user)
        ->test(ListarGrausSatisfacao::class)
        ->call('openModal')
        ->set('dsc_grau_satisfacao', 'Crítico')
        ->set('cor', '#dc3545')
        ->set('vlr_minimo', '0,00')
        ->set('vlr_maximo', '1.000,00')
        ->call('save')
        ->assertHasErrors(['vlr_maximo']);
});
