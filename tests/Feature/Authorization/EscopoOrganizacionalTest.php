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
    $user->perfisAcesso()->attach(PerfilAcesso::CONSULTA, ['cod_organizacao' => $orgA->cod_organizacao]);

    expect($user->podeAcessarOrganizacao($orgA->cod_organizacao))->toBeTrue()
        ->and($user->podeAcessarOrganizacao($orgB->cod_organizacao))->toBeFalse();
});

test('organizacaoSelecionadaId retorna null quando não há seleção na sessão', function () {
    $user = User::factory()->create();

    expect($user->organizacaoSelecionadaId())->toBeNull();
});

test('seleção fora do escopo nunca é devolvida: cai na primeira unidade do próprio usuário', function () {
    // Antes devolvia null — e várias telas liam null como "todas as unidades".
    $orgA = criarOrganizacao('Org A');
    $orgB = criarOrganizacao('Org B');

    $user = User::factory()->create();
    $user->perfisAcesso()->attach(PerfilAcesso::CONSULTA, ['cod_organizacao' => $orgA->cod_organizacao]);

    session(['organizacao_selecionada_id' => $orgB->cod_organizacao]);

    expect($user->organizacaoSelecionadaId())->toBe($orgA->cod_organizacao)
        ->and(session('organizacao_selecionada_id'))->toBe($orgA->cod_organizacao);
});

test('vínculo genérico com uma unidade, sem perfil nela, NÃO dá escopo', function () {
    // Caso encontrado no teste pelo navegador em 03/10/2026: o Gestor retirado
    // de uma iniciativa de outra unidade ficava com o vínculo genérico com ela;
    // o sistema o colocava lá por padrão e, sem perfil, tudo lhe era negado.
    $orgA = criarOrganizacao('Org A');
    $orgAntiga = criarOrganizacao('Org Antiga');

    $user = User::factory()->create();
    $user->organizacoes()->attach($orgAntiga->cod_organizacao);
    $user->perfisAcesso()->attach(PerfilAcesso::GESTOR_RESPONSAVEL, ['cod_organizacao' => $orgA->cod_organizacao]);

    expect($user->organizacaoIdsPermitidas()->all())->toBe([$orgA->cod_organizacao])
        ->and($user->organizacaoSelecionadaId())->toBe($orgA->cod_organizacao);
});

test('organizacaoSelecionadaId retorna o id quando dentro do escopo', function () {
    $orgA = criarOrganizacao('Org A');

    $user = User::factory()->create();
    $user->perfisAcesso()->attach(PerfilAcesso::CONSULTA, ['cod_organizacao' => $orgA->cod_organizacao]);

    session(['organizacao_selecionada_id' => $orgA->cod_organizacao]);

    expect($user->organizacaoSelecionadaId())->toBe($orgA->cod_organizacao);
});
