<?php

use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;

function montarIndicadorDeObjetivo(Organization $org): Indicador
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

    return Indicador::create([
        'cod_objetivo' => $objetivo->cod_objetivo,
        'nom_indicador' => 'Indicador Teste',
        'dsc_indicador' => 'Descrição do indicador de teste',
        'dsc_unidade_medida' => 'Unidade',
        'dsc_tipo' => 'Efetividade',
        'bln_acumulado' => false,
        'dsc_periodo_medicao' => 'mensal',
    ]);
}

test('admin unidade só pode editar indicador quando a organização selecionada bate com o vínculo do perfil', function () {
    $org = Organization::create(['nom_organizacao' => 'Org A', 'sgl_organizacao' => 'OA', 'cod_organizacao_pai' => null]);
    $indicador = montarIndicadorDeObjetivo($org);

    $user = User::factory()->create(['ativo' => true]);
    $user->organizacoes()->attach($org->cod_organizacao);
    $user->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $org->cod_organizacao]);

    session(['organizacao_selecionada_id' => $org->cod_organizacao]);

    expect($user->can('update', $indicador))->toBeTrue()
        ->and($user->can('delete', $indicador))->toBeTrue();
});

test('admin unidade não pode editar indicador se a organização selecionada não é a vinculada ao seu perfil', function () {
    $org = Organization::create(['nom_organizacao' => 'Org A', 'sgl_organizacao' => 'OA', 'cod_organizacao_pai' => null]);
    $outraOrg = Organization::create(['nom_organizacao' => 'Org B', 'sgl_organizacao' => 'OB', 'cod_organizacao_pai' => null]);
    $indicador = montarIndicadorDeObjetivo($org);

    // Usuário é Admin Unidade de Org A (a organização do indicador), mas está
    // com Org B selecionada na sessão — sem nenhum vínculo de perfil em Org B.
    $user = User::factory()->create(['ativo' => true]);
    $user->organizacoes()->attach([$org->cod_organizacao, $outraOrg->cod_organizacao]);
    $user->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $org->cod_organizacao]);

    session(['organizacao_selecionada_id' => $outraOrg->cod_organizacao]);

    expect($user->can('update', $indicador))->toBeFalse();
});

test('gestor substituto não pode editar indicador só por ter a capacidade genérica do módulo', function () {
    $org = Organization::create(['nom_organizacao' => 'Org A', 'sgl_organizacao' => 'OA', 'cod_organizacao_pai' => null]);
    $indicador = montarIndicadorDeObjetivo($org);

    $user = User::factory()->create(['ativo' => true]);
    $user->organizacoes()->attach($org->cod_organizacao);
    $user->perfisAcesso()->attach(PerfilAcesso::GESTOR_SUBSTITUTO, ['cod_organizacao' => $org->cod_organizacao]);

    session(['organizacao_selecionada_id' => $org->cod_organizacao]);

    expect($user->can('update', $indicador))->toBeFalse();
});
