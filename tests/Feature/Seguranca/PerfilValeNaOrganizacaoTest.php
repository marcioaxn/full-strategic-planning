<?php

/*
 * 🔴 O vazamento que o gestor da Presidência suspeitou, confirmado em 03/10/2026:
 * a permissão SOMAVA todos os perfis do usuário, em qualquer unidade. Quem era
 * Administrador na unidade A e Gestor Substituto na B tinha poderes de
 * Administrador também na B.
 *
 * Regra agora: cada vínculo vale onde foi dado. Administrador e Consulta valem
 * na unidade do vínculo e nas subordinadas; Gestores, só na unidade do vínculo.
 * O que é da instituição inteira (perspectivas, objetivos, faixas do farol) só
 * o Super Admin ou o Administrador da unidade raiz alteram.
 */

use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\User;
use App\Services\Authorization\CapacidadeResolver;
use Illuminate\Support\Facades\Gate;

function arvoreDeUnidades(): array
{
    $raiz = Organization::create(['nom_organizacao' => 'Ministério', 'sgl_organizacao' => 'MIN', 'cod_organizacao_pai' => null]);
    $secretariaA = Organization::create(['nom_organizacao' => 'Secretaria A', 'sgl_organizacao' => 'SA', 'rel_cod_organizacao' => $raiz->cod_organizacao]);
    $diretoriaA1 = Organization::create(['nom_organizacao' => 'Diretoria A1', 'sgl_organizacao' => 'DA1', 'rel_cod_organizacao' => $secretariaA->cod_organizacao]);
    $secretariaB = Organization::create(['nom_organizacao' => 'Secretaria B', 'sgl_organizacao' => 'SB', 'rel_cod_organizacao' => $raiz->cod_organizacao]);

    return [$raiz, $secretariaA, $diretoriaA1, $secretariaB];
}

function usuarioCom(array $vinculos): User
{
    $user = User::factory()->create(['ativo' => true]);

    foreach ($vinculos as [$perfil, $org]) {
        $user->perfisAcesso()->attach($perfil, ['cod_organizacao' => $org->cod_organizacao]);
        $user->organizacoes()->syncWithoutDetaching([$org->cod_organizacao]);
    }

    return $user->fresh();
}

test('Administrador em A e Gestor Substituto em B NÃO tem poder de Administrador em B', function () {
    [, $a, , $b] = arvoreDeUnidades();
    $user = usuarioCom([[PerfilAcesso::ADMIN_UNIDADE, $a], [PerfilAcesso::GESTOR_SUBSTITUTO, $b]]);

    expect(CapacidadeResolver::podeNoModulo($user, 'planejamento-estrategico', 'excluir', $a->cod_organizacao))->toBeTrue()
        ->and(CapacidadeResolver::podeNoModulo($user, 'planejamento-estrategico', 'excluir', $b->cod_organizacao))->toBeFalse()
        ->and(CapacidadeResolver::podeNoModulo($user, 'planejamento-estrategico', 'editar', $b->cod_organizacao))->toBeFalse()
        ->and(CapacidadeResolver::podeNoModulo($user, 'planejamento-estrategico', 'acessar', $b->cod_organizacao))->toBeTrue();
});

test('o Gate sem organização usa a selecionada no topo — e o perfil que vale nela', function () {
    [, $a, , $b] = arvoreDeUnidades();
    $user = usuarioCom([[PerfilAcesso::ADMIN_UNIDADE, $a], [PerfilAcesso::GESTOR_SUBSTITUTO, $b]]);

    session(['organizacao_selecionada_id' => $b->cod_organizacao]);
    expect(Gate::forUser($user)->allows('modulo.excluir', 'riscos'))->toBeFalse();

    session(['organizacao_selecionada_id' => $a->cod_organizacao]);
    expect(Gate::forUser($user)->allows('modulo.excluir', 'riscos'))->toBeTrue();
});

test('Administrador da Secretaria A alcança a Diretoria subordinada, mas não a Secretaria B', function () {
    [, $a, $a1, $b] = arvoreDeUnidades();
    $user = usuarioCom([[PerfilAcesso::ADMIN_UNIDADE, $a]]);

    expect($user->organizacaoIdsPermitidas()->all())->toContain($a->cod_organizacao, $a1->cod_organizacao)
        ->and($user->organizacaoIdsPermitidas()->all())->not->toContain($b->cod_organizacao)
        ->and($user->ehAdministradorEm($a1->cod_organizacao))->toBeTrue()
        ->and($user->ehAdministradorEm($b->cod_organizacao))->toBeFalse();
});

test('Gestor da Secretaria A não ganha nada na Diretoria subordinada', function () {
    [, $a, $a1] = arvoreDeUnidades();
    $user = usuarioCom([[PerfilAcesso::GESTOR_RESPONSAVEL, $a]]);

    expect($user->perfisEfetivosNaOrganizacao($a1->cod_organizacao))->toBe([])
        ->and($user->podeAcessarOrganizacao($a1->cod_organizacao))->toBeFalse();
});

test('Consulta lê e exporta, e não grava em módulo nenhum', function () {
    [, $a] = arvoreDeUnidades();
    $user = usuarioCom([[PerfilAcesso::CONSULTA, $a]]);
    $org = $a->cod_organizacao;

    foreach (array_keys(CapacidadeResolver::matriz()) as $modulo) {
        foreach (['criar', 'editar', 'excluir'] as $ability) {
            expect(CapacidadeResolver::podeNoModulo($user, $modulo, $ability, $org))
                ->toBeFalse("Consulta pôde {$ability} em {$modulo}");
        }
    }

    expect(CapacidadeResolver::podeNoModulo($user, 'indicadores', 'acessar', $org))->toBeTrue()
        ->and(CapacidadeResolver::podeNoModulo($user, 'relatorios', 'exportar', $org))->toBeTrue()
        ->and(CapacidadeResolver::podeNoModulo($user, 'usuarios', 'acessar', $org))->toBeFalse();
});

test('Gestor Responsável lê o planejamento da unidade mas não o reescreve', function () {
    [, $a] = arvoreDeUnidades();
    $user = usuarioCom([[PerfilAcesso::GESTOR_RESPONSAVEL, $a]]);
    $org = $a->cod_organizacao;

    expect(CapacidadeResolver::podeNoModulo($user, 'planejamento-estrategico', 'acessar', $org))->toBeTrue()
        ->and(CapacidadeResolver::podeNoModulo($user, 'planejamento-estrategico', 'criar', $org))->toBeFalse()
        ->and(CapacidadeResolver::podeNoModulo($user, 'planejamento-estrategico', 'editar', $org))->toBeFalse()
        ->and(CapacidadeResolver::podeNoModulo($user, 'planos-de-acao', 'criar', $org))->toBeFalse();
});

test('só o Super Admin ou o Administrador da raiz altera o que é da instituição inteira', function () {
    [$raiz, $a] = arvoreDeUnidades();

    $adminRaiz = usuarioCom([[PerfilAcesso::ADMIN_UNIDADE, $raiz]]);
    $adminSecretaria = usuarioCom([[PerfilAcesso::ADMIN_UNIDADE, $a]]);
    $superAdmin = usuarioCom([[PerfilAcesso::SUPER_ADMIN, $raiz]]);

    expect(Gate::forUser($adminRaiz)->allows('editar-institucional'))->toBeTrue()
        ->and(Gate::forUser($superAdmin)->allows('editar-institucional'))->toBeTrue()
        ->and(Gate::forUser($adminSecretaria)->allows('editar-institucional'))->toBeFalse();
});

test('conta sem perfil (autocadastro) não tem capacidade e cai na página de acesso pendente', function () {
    $user = User::factory()->create(['ativo' => true]);

    expect($user->temPerfilDeAcesso())->toBeFalse();

    $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('acesso.pendente'));
    $this->actingAs($user)->get(route('acesso.pendente'))->assertOk()->assertSee('Acesso aguardando liberação');
});

test('conta do autocadastro (sem perfil e com troca de senha pendente) não entra em laço de redirecionamento', function () {
    // Encontrado no teste pelo navegador: a troca de senha obrigatória estava
    // dentro do grupo que exige perfil — troca de senha → acesso pendente →
    // troca de senha → … ERR_TOO_MANY_REDIRECTS.
    $user = User::factory()->create(['ativo' => true, 'trocarsenha' => 1]);

    $this->actingAs($user)->get(route('acesso.pendente'))->assertRedirect(route('auth.trocar-senha'));
    $this->actingAs($user)->get(route('auth.trocar-senha'))->assertOk();
    $this->actingAs($user)->get(route('dashboard'))->assertRedirect();
});

test('árvore corrompida (ciclo) não derruba o cálculo de escopo', function () {
    [, $a, $a1] = arvoreDeUnidades();
    // A vira filha da própria filha: o percurso antigo recursava sem fim.
    $a->update(['rel_cod_organizacao' => $a1->cod_organizacao]);

    expect(Organization::descendentesEProprio($a->cod_organizacao))->toContain($a->cod_organizacao, $a1->cod_organizacao)
        ->and(Organization::ascendentesEProprio($a1->cod_organizacao))->toContain($a1->cod_organizacao, $a->cod_organizacao);
});
