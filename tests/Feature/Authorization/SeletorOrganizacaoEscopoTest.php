<?php

use App\Livewire\Shared\SeletorOrganizacao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\User;
use Livewire\Livewire;

test('usuário comum não consegue selecionar uma organização fora do seu escopo', function () {
    $orgPermitida = Organization::create(['nom_organizacao' => 'Org Permitida', 'sgl_organizacao' => 'OP', 'cod_organizacao_pai' => null]);
    $orgProibida = Organization::create(['nom_organizacao' => 'Org Proibida', 'sgl_organizacao' => 'OX', 'cod_organizacao_pai' => null]);

    $user = User::factory()->create(['ativo' => true]);
    $user->organizacoes()->attach($orgPermitida->cod_organizacao);
    $user->perfisAcesso()->attach(PerfilAcesso::GESTOR_RESPONSAVEL, ['cod_organizacao' => $orgPermitida->cod_organizacao]);

    Livewire::actingAs($user)
        ->test(SeletorOrganizacao::class)
        ->call('selecionar', $orgProibida->cod_organizacao);

    expect(session('organizacao_selecionada_id'))->not->toBe($orgProibida->cod_organizacao);
});

test('usuário comum consegue selecionar uma organização do seu próprio escopo', function () {
    $org = Organization::create(['nom_organizacao' => 'Org A', 'sgl_organizacao' => 'OA', 'cod_organizacao_pai' => null]);

    $user = User::factory()->create(['ativo' => true]);
    $user->organizacoes()->attach($org->cod_organizacao);
    $user->perfisAcesso()->attach(PerfilAcesso::GESTOR_RESPONSAVEL, ['cod_organizacao' => $org->cod_organizacao]);

    Livewire::actingAs($user)
        ->test(SeletorOrganizacao::class)
        ->call('selecionar', $org->cod_organizacao);

    expect(session('organizacao_selecionada_id'))->toBe($org->cod_organizacao);
});

test('super admin consegue selecionar qualquer organização', function () {
    $org = Organization::create(['nom_organizacao' => 'Org Qualquer', 'sgl_organizacao' => 'OQ', 'cod_organizacao_pai' => null]);
    $orgVinculo = Organization::create(['nom_organizacao' => 'Org Vinculo', 'sgl_organizacao' => 'OV', 'cod_organizacao_pai' => null]);

    $user = User::factory()->create(['ativo' => true]);
    $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $orgVinculo->cod_organizacao]);

    Livewire::actingAs($user)
        ->test(SeletorOrganizacao::class)
        ->call('selecionar', $org->cod_organizacao);

    expect(session('organizacao_selecionada_id'))->toBe($org->cod_organizacao);
});
