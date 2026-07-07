<?php

use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\User;
use App\Services\Authorization\CapacidadeResolver;

function organizacaoDeTeste(): Organization
{
    return Organization::create([
        'nom_organizacao' => 'Org Teste',
        'sgl_organizacao' => 'OT',
        'cod_organizacao_pai' => null,
    ]);
}

function vincularPerfil(User $user, Organization $org, string $codPerfil): void
{
    $user->perfisAcesso()->attach($codPerfil, ['cod_organizacao' => $org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');
}

test('super admin pode tudo em qualquer módulo', function () {
    $user = User::factory()->create();
    $org = organizacaoDeTeste();
    vincularPerfil($user, $org, PerfilAcesso::SUPER_ADMIN);

    expect(CapacidadeResolver::podeNoModulo($user, 'auditoria', 'acessar'))->toBeTrue()
        ->and(CapacidadeResolver::podeNoModulo($user, 'admin.configuracoes', 'editar'))->toBeTrue();
});

test('usuário sem perfil não pode nada', function () {
    $user = User::factory()->create();

    expect(CapacidadeResolver::podeNoModulo($user, 'indicadores', 'acessar'))->toBeFalse();
});

test('admin de unidade pode excluir indicadores mas gestor substituto não', function () {
    $user = User::factory()->create();
    $org = organizacaoDeTeste();
    vincularPerfil($user, $org, PerfilAcesso::ADMIN_UNIDADE);

    expect(CapacidadeResolver::podeNoModulo($user, 'indicadores', 'excluir'))->toBeTrue();

    $gestorSubstituto = User::factory()->create();
    vincularPerfil($gestorSubstituto, $org, PerfilAcesso::GESTOR_SUBSTITUTO);

    expect(CapacidadeResolver::podeNoModulo($gestorSubstituto, 'indicadores', 'excluir'))->toBeFalse()
        ->and(CapacidadeResolver::podeNoModulo($gestorSubstituto, 'indicadores', 'editar'))->toBeTrue();
});

test('módulos restritos a super admin negam todos os demais perfis', function () {
    $user = User::factory()->create();
    $org = organizacaoDeTeste();
    vincularPerfil($user, $org, PerfilAcesso::ADMIN_UNIDADE);

    expect(CapacidadeResolver::podeNoModulo($user, 'auditoria', 'acessar'))->toBeFalse()
        ->and(CapacidadeResolver::podeNoModulo($user, 'admin.perfis', 'acessar'))->toBeFalse()
        ->and(CapacidadeResolver::podeNoModulo($user, 'admin.configuracoes', 'acessar'))->toBeFalse();
});
