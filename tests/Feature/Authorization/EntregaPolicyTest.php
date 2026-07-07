<?php

use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;

function montarPlanoDeAcaoComEntrega(Organization $org): Entrega
{
    $pei = PEI::create([
        'dsc_pei' => 'Ciclo Teste',
        'num_ano_inicio_pei' => 2024,
        'num_ano_fim_pei' => 2027,
        'bln_ativo' => true,
    ]);

    $perspectiva = Perspectiva::create([
        'cod_pei' => $pei->cod_pei,
        'dsc_perspectiva' => 'Perspectiva Teste',
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);

    $objetivo = Objetivo::create([
        'cod_perspectiva' => $perspectiva->cod_perspectiva,
        'nom_objetivo' => 'Objetivo Teste',
        'dsc_objetivo' => 'Descrição do objetivo de teste',
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);

    $plano = PlanoDeAcao::create([
        'cod_objetivo' => $objetivo->cod_objetivo,
        'cod_organizacao' => $org->cod_organizacao,
        'cod_tipo_execucao' => TipoExecucao::ACAO,
        'nom_plano_de_acao' => 'Plano Teste',
        'dsc_plano_de_acao' => 'Descrição do plano de teste',
        'num_nivel_hierarquico_apresentacao' => 1,
        'dte_inicio' => now()->toDateString(),
        'dte_fim' => now()->addYear()->toDateString(),
        'bln_status' => true,
    ]);

    return Entrega::create([
        'cod_plano_de_acao' => $plano->cod_plano_de_acao,
        'dsc_entrega' => 'Entrega Teste',
        'bln_status' => 'Pendente',
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);
}

test('super admin pode ver e editar entrega de qualquer organização', function () {
    $org = Organization::create(['nom_organizacao' => 'Org A', 'sgl_organizacao' => 'OA', 'cod_organizacao_pai' => null]);
    $entrega = montarPlanoDeAcaoComEntrega($org);

    $user = User::factory()->create(['ativo' => true]);
    $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);

    expect($user->can('view', $entrega))->toBeTrue()
        ->and($user->can('update', $entrega))->toBeTrue();
});

test('gestor responsável vinculado à organização pode editar a entrega', function () {
    $org = Organization::create(['nom_organizacao' => 'Org A', 'sgl_organizacao' => 'OA', 'cod_organizacao_pai' => null]);
    $entrega = montarPlanoDeAcaoComEntrega($org);

    $user = User::factory()->create(['ativo' => true]);
    $user->organizacoes()->attach($org->cod_organizacao);
    $user->perfisAcesso()->attach(PerfilAcesso::GESTOR_RESPONSAVEL, ['cod_organizacao' => $org->cod_organizacao]);

    expect($user->can('update', $entrega))->toBeTrue();
});

test('usuário sem vínculo com a organização não pode ver a entrega', function () {
    $org = Organization::create(['nom_organizacao' => 'Org A', 'sgl_organizacao' => 'OA', 'cod_organizacao_pai' => null]);
    $outraOrg = Organization::create(['nom_organizacao' => 'Org B', 'sgl_organizacao' => 'OB', 'cod_organizacao_pai' => null]);
    $entrega = montarPlanoDeAcaoComEntrega($org);

    $user = User::factory()->create(['ativo' => true]);
    $user->organizacoes()->attach($outraOrg->cod_organizacao);
    $user->perfisAcesso()->attach(PerfilAcesso::GESTOR_RESPONSAVEL, ['cod_organizacao' => $outraOrg->cod_organizacao]);

    expect($user->can('view', $entrega))->toBeFalse();
});

test('usuário inativo é vetado mesmo com perfil válido (Gate::before)', function () {
    $org = Organization::create(['nom_organizacao' => 'Org A', 'sgl_organizacao' => 'OA', 'cod_organizacao_pai' => null]);
    $entrega = montarPlanoDeAcaoComEntrega($org);

    $user = User::factory()->create(['ativo' => false]);
    $user->organizacoes()->attach($org->cod_organizacao);
    $user->perfisAcesso()->attach(PerfilAcesso::GESTOR_RESPONSAVEL, ['cod_organizacao' => $org->cod_organizacao]);

    expect($user->can('view', $entrega))->toBeFalse();
});
