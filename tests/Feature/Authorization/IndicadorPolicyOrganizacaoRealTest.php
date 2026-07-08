<?php

use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;

function montarIndicadorNaOrganizacao(Organization $org): Indicador
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

    $indicador = Indicador::create([
        'cod_objetivo' => $objetivo->cod_objetivo,
        'nom_indicador' => 'Indicador Teste',
        'dsc_indicador' => 'Descrição do indicador de teste',
        'dsc_unidade_medida' => 'Unidade',
        'dsc_tipo' => 'Efetividade',
        'bln_acumulado' => false,
        'dsc_periodo_medicao' => 'mensal',
    ]);
    $indicador->organizacoes()->attach($org->cod_organizacao);

    return $indicador;
}

test('admin unidade NÃO pode editar indicador de uma organização diferente da sua, mesmo com essa organização selecionada na sessão', function () {
    $orgDoIndicador = Organization::create(['nom_organizacao' => 'Org do Indicador', 'sgl_organizacao' => 'OI', 'cod_organizacao_pai' => null]);
    $orgDoAdmin = Organization::create(['nom_organizacao' => 'Org do Admin', 'sgl_organizacao' => 'OA', 'cod_organizacao_pai' => null]);

    $indicador = montarIndicadorNaOrganizacao($orgDoIndicador);

    // Usuário é Admin Unidade de "Org do Admin" apenas — sem nenhum vínculo
    // com "Org do Indicador".
    $user = User::factory()->create(['ativo' => true]);
    $user->organizacoes()->attach($orgDoAdmin->cod_organizacao);
    $user->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $orgDoAdmin->cod_organizacao]);

    // Mas está com "Org do Admin" selecionada no menu superior — o bug fazia
    // a Policy autorizar com base apenas nisso, ignorando a organização real
    // do indicador.
    session(['organizacao_selecionada_id' => $orgDoAdmin->cod_organizacao]);

    expect($user->can('update', $indicador))->toBeFalse()
        ->and($user->can('delete', $indicador))->toBeFalse();
});

test('admin unidade pode editar indicador da sua própria organização independentemente da organização selecionada na sessão', function () {
    $orgDoIndicador = Organization::create(['nom_organizacao' => 'Org do Indicador', 'sgl_organizacao' => 'OI', 'cod_organizacao_pai' => null]);
    $outraOrg = Organization::create(['nom_organizacao' => 'Outra Org', 'sgl_organizacao' => 'OO', 'cod_organizacao_pai' => null]);

    $indicador = montarIndicadorNaOrganizacao($orgDoIndicador);

    $user = User::factory()->create(['ativo' => true]);
    $user->organizacoes()->attach([$orgDoIndicador->cod_organizacao, $outraOrg->cod_organizacao]);
    $user->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $orgDoIndicador->cod_organizacao]);

    // Mesmo com outra organização selecionada na sessão, o admin ainda deve
    // poder editar o indicador porque ele É admin da organização REAL do
    // indicador (checagem não depende da sessão).
    session(['organizacao_selecionada_id' => $outraOrg->cod_organizacao]);

    expect($user->can('update', $indicador))->toBeTrue();
});
