<?php

/*
 * Regressão encontrada no teste pelo navegador em 03/10/2026: cadastrar uma
 * unidade SEM superior (a raiz — primeiro passo de todo cliente novo, como a
 * Presidência montando a própria árvore) quebrava com "invalid input syntax for
 * type uuid" e exibia o SQL na tela. O seletor envia texto vazio, que ia direto
 * para a coluna UUID.
 */

use App\Livewire\Organization\ListarOrganizacoes;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\User;
use Livewire\Livewire;

function superAdminParaOrganizacoes(): User
{
    $base = Organization::create(['nom_organizacao' => 'Base', 'sgl_organizacao' => 'BASE']);
    $user = User::factory()->create(['ativo' => true]);
    $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $base->cod_organizacao]);

    return $user->fresh();
}

test('cadastra unidade raiz (sem superior) pela tela, apontando para si mesma', function () {
    Livewire::actingAs(superAdminParaOrganizacoes())
        ->test(ListarOrganizacoes::class)
        ->call('create')
        ->set('form.nom_organizacao', 'Órgão Central')
        ->set('form.sgl_organizacao', 'OC')
        ->set('form.rel_cod_organizacao', '')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showErrorModal', false);

    $raiz = Organization::where('sgl_organizacao', 'OC')->sole();

    expect($raiz->rel_cod_organizacao)->toBe($raiz->cod_organizacao)
        ->and($raiz->isRaiz())->toBeTrue();
});

test('cadastra unidade subordinada pela tela', function () {
    $user = superAdminParaOrganizacoes();
    $pai = Organization::create(['nom_organizacao' => 'Órgão Central', 'sgl_organizacao' => 'OC']);

    Livewire::actingAs($user)
        ->test(ListarOrganizacoes::class)
        ->call('create')
        ->set('form.nom_organizacao', 'Secretaria A')
        ->set('form.sgl_organizacao', 'SA')
        ->set('form.rel_cod_organizacao', $pai->cod_organizacao)
        ->call('save')
        ->assertHasNoErrors();

    expect(Organization::where('sgl_organizacao', 'SA')->sole()->rel_cod_organizacao)->toBe($pai->cod_organizacao);
});
