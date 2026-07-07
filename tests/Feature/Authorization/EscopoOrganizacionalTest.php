<?php

use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\User;

function criarOrganizacao(string $nome): Organization
{
    return Organization::create([
        'nom_organizacao' => $nome,
        'sgl_organizacao' => strtoupper(substr($nome, 0, 3)),
        'cod_organizacao_pai' => null,
    ]);
}

test('super admin enxerga todas as organizações', function () {
    $orgA = criarOrganizacao('Org A');
    $orgB = criarOrganizacao('Org B');

    $user = User::factory()->create();
    $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $orgA->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');

    $ids = $user->organizacaoIdsPermitidas();

    expect($ids)->toContain($orgA->cod_organizacao, $orgB->cod_organizacao);
});

test('usuário comum só enxerga organizações vinculadas', function () {
    $orgA = criarOrganizacao('Org A');
    $orgB = criarOrganizacao('Org B');

    $user = User::factory()->create();
    $user->organizacoes()->attach($orgA->cod_organizacao);

    expect($user->podeAcessarOrganizacao($orgA->cod_organizacao))->toBeTrue()
        ->and($user->podeAcessarOrganizacao($orgB->cod_organizacao))->toBeFalse();
});

test('organizacaoSelecionadaId retorna null quando não há seleção na sessão', function () {
    $user = User::factory()->create();

    expect($user->organizacaoSelecionadaId())->toBeNull();
});

test('organizacaoSelecionadaId retorna null quando a seleção está fora do escopo do usuário', function () {
    $orgA = criarOrganizacao('Org A');
    $orgB = criarOrganizacao('Org B');

    $user = User::factory()->create();
    $user->organizacoes()->attach($orgA->cod_organizacao);

    session(['organizacao_selecionada_id' => $orgB->cod_organizacao]);

    expect($user->organizacaoSelecionadaId())->toBeNull();
});

test('organizacaoSelecionadaId retorna o id quando dentro do escopo', function () {
    $orgA = criarOrganizacao('Org A');

    $user = User::factory()->create();
    $user->organizacoes()->attach($orgA->cod_organizacao);

    session(['organizacao_selecionada_id' => $orgA->cod_organizacao]);

    expect($user->organizacaoSelecionadaId())->toBe($orgA->cod_organizacao);
});
