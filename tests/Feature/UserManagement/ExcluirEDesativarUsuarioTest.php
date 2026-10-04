<?php

/*
 * Revisão de 04/10/2026, achados 2, 12 e 13 — pelo caminho da tela
 * (ListarUsuarios), não pelo model.
 *
 *  2. "Excluir" apagava fisicamente o usuário e, em cascata, a auditoria, o
 *     RACI e os comentários dele; o modal só dizia "perderá o acesso". Agora
 *     quem tem histórico não é excluído: a tela recusa e oferece Desativar.
 * 12. O último Super Admin ativo podia tirar o próprio perfil (ou se desativar).
 * 13. Conta de autocadastro com e-mail não confirmado não podia ser desativada.
 */

use App\Livewire\UserManagement\ListarUsuarios;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\User;
use Livewire\Livewire;
use OwenIt\Auditing\Models\Audit;

function cenarioUsuarios(): array
{
    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);

    $admin = User::factory()->create(['ativo' => true, 'trocarsenha' => 0]);
    $admin->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
    $admin->organizacoes()->sync([$org->cod_organizacao]);
    $admin->unsetRelation('perfisAcesso');

    return [$org, $admin];
}

function usuarioComum(Organization $org, array $atributos = []): User
{
    $u = User::factory()->create(array_merge(['ativo' => true, 'trocarsenha' => 0], $atributos));
    $u->perfisAcesso()->attach(PerfilAcesso::CONSULTA, ['cod_organizacao' => $org->cod_organizacao]);
    $u->organizacoes()->sync([$org->cod_organizacao]);

    return $u;
}

test('usuário com registro na auditoria não é excluído: a tela recusa e oferece desativar', function () {
    [$org, $admin] = cenarioUsuarios();
    $ex = usuarioComum($org);

    Audit::create([
        'user_type' => User::class, 'user_id' => $ex->id, 'event' => 'updated',
        'auditable_type' => 'App\\Models\\StrategicPlanning\\Valor', 'auditable_id' => $org->cod_organizacao,
        'old_values' => [], 'new_values' => [],
    ]);

    $tela = Livewire::actingAs($admin)
        ->test(ListarUsuarios::class)
        ->call('confirmDelete', $ex->id)
        ->assertSet('showDeleteModal', true)
        ->assertSee('não pode ser excluído')
        ->assertSee('Desativar');

    expect($tela->get('historicoDoExcluido'))->not->toBeEmpty();

    // Mesmo chamando delete direto (é um endpoint), nada é apagado.
    $tela->call('delete')->assertSet('transactionStyle', 'warning');

    expect(User::find($ex->id))->not->toBeNull()
        ->and(Audit::where('user_id', $ex->id)->count())->toBe(1);
});

test('o usuário com histórico é desativado pelo mesmo modal, sem perder nada', function () {
    [$org, $admin] = cenarioUsuarios();
    $ex = usuarioComum($org);
    Audit::create([
        'user_type' => User::class, 'user_id' => $ex->id, 'event' => 'created',
        'auditable_type' => 'App\\Models\\StrategicPlanning\\Valor', 'auditable_id' => $org->cod_organizacao,
        'old_values' => [], 'new_values' => [],
    ]);

    Livewire::actingAs($admin)
        ->test(ListarUsuarios::class)
        ->call('confirmDelete', $ex->id)
        ->call('desativar')
        ->assertSet('transactionStyle', 'success');

    $ex->refresh();
    expect($ex->ativo)->toBeFalse()
        ->and($ex->perfisAcesso()->count())->toBe(1)
        ->and(Audit::where('user_id', $ex->id)->count())->toBe(1);
});

test('conta sem nenhum histórico é excluída de fato, e o modal diz que é definitivo', function () {
    [$org, $admin] = cenarioUsuarios();
    $nunca = User::factory()->unverified()->create(['ativo' => true, 'trocarsenha' => 1]);

    Livewire::actingAs($admin)
        ->test(ListarUsuarios::class)
        ->call('confirmDelete', $nunca->id)
        ->assertSee('apagados definitivamente')
        ->call('delete');

    expect(User::find($nunca->id))->toBeNull();
});

test('o último Super Admin ativo não tira o próprio perfil de Super Admin', function () {
    [$org, $admin] = cenarioUsuarios();

    Livewire::actingAs($admin)
        ->test(ListarUsuarios::class)
        ->call('edit', $admin->id)
        ->set('form.vinculos', [[
            'org_id' => $org->cod_organizacao, 'perfil_id' => PerfilAcesso::ADMIN_UNIDADE,
            'org_label' => 'ORG', 'perfil_label' => 'Administrador',
        ]])
        ->call('save')
        ->assertHasErrors('form.vinculos');

    $admin->unsetRelation('perfisAcesso');
    expect($admin->fresh()->isSuperAdmin())->toBeTrue();
});

test('o último Super Admin ativo não se desativa', function () {
    [$org, $admin] = cenarioUsuarios();

    Livewire::actingAs($admin)
        ->test(ListarUsuarios::class)
        ->call('edit', $admin->id)
        ->set('form.ativo', false)
        ->call('save')
        ->assertHasErrors('form.ativo');

    expect($admin->fresh()->ativo)->toBeTrue();
});

test('com outro Super Admin ativo, o Super Admin pode deixar o perfil', function () {
    [$org, $admin] = cenarioUsuarios();
    $outro = User::factory()->create(['ativo' => true]);
    $outro->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);

    Livewire::actingAs($admin)
        ->test(ListarUsuarios::class)
        ->call('edit', $admin->id)
        ->set('form.vinculos', [[
            'org_id' => $org->cod_organizacao, 'perfil_id' => PerfilAcesso::ADMIN_UNIDADE,
            'org_label' => 'ORG', 'perfil_label' => 'Administrador',
        ]])
        ->call('save')
        ->assertHasNoErrors();

    expect(User::find($admin->id)->isSuperAdmin())->toBeFalse();
});

test('conta de autocadastro com e-mail não confirmado pode ser desativada', function () {
    [, $admin] = cenarioUsuarios();
    $suspeita = User::factory()->unverified()->create(['ativo' => true, 'trocarsenha' => 1]);

    Livewire::actingAs($admin)
        ->test(ListarUsuarios::class)
        ->call('edit', $suspeita->id)
        ->set('form.ativo', false)
        ->call('save')
        ->assertHasNoErrors();

    expect($suspeita->fresh()->ativo)->toBeFalse();
});

test('conta ATIVA continua exigindo ao menos um vínculo', function () {
    [, $admin] = cenarioUsuarios();
    $suspeita = User::factory()->unverified()->create(['ativo' => true, 'trocarsenha' => 1]);

    Livewire::actingAs($admin)
        ->test(ListarUsuarios::class)
        ->call('edit', $suspeita->id)
        ->call('save')
        ->assertHasErrors('form.vinculos');
});
