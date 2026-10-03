<?php

/*
 * Erro 500 em /pei relatado pelo gestor em 03/10/2026: a sessão do Super Admin
 * guardava uma unidade que depois foi excluída. Para o Super Admin,
 * podeAcessarOrganizacao() respondia "sim" sem conferir se a unidade existe, e
 * a Identidade Estratégica lia o nome de uma organização nula.
 */

use App\Livewire\Shared\SeletorPei;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\PEI;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

test('unidade excluída guardada na sessão não derruba as telas do Super Admin', function () {
    $raiz = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);
    $excluida = Organization::create(['nom_organizacao' => 'Unidade excluída', 'sgl_organizacao' => 'EXC', 'rel_cod_organizacao' => $raiz->cod_organizacao]);

    $admin = User::factory()->create();
    $admin->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $raiz->cod_organizacao]);

    $excluida->delete();

    $this->actingAs($admin)
        ->withSession(['organizacao_selecionada_id' => $excluida->cod_organizacao])
        ->get(route('pei.index'))
        ->assertOk();

    expect($admin->fresh()->podeAcessarOrganizacao($excluida->cod_organizacao))->toBeFalse();
});

test('ciclo excluído guardado na sessão é trocado pelo vigente no seletor do topo', function () {
    $raiz = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);
    $ano = (int) date('Y');
    $vigente = PEI::create(['dsc_pei' => 'Vigente', 'num_ano_inicio_pei' => $ano - 1, 'num_ano_fim_pei' => $ano + 2]);
    $excluido = PEI::create(['dsc_pei' => 'Excluído', 'num_ano_inicio_pei' => $ano, 'num_ano_fim_pei' => $ano + 3]);
    $excluido->delete();

    $admin = User::factory()->create();
    $admin->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $raiz->cod_organizacao]);

    Session::put('pei_selecionado_id', $excluido->cod_pei);
    Session::put('pei_selecionado_periodo', ($ano).'-'.($ano + 3));

    Livewire::actingAs($admin)->test(SeletorPei::class)
        ->assertSet('selecionadoId', $vigente->cod_pei);

    expect(session('pei_selecionado_periodo'))->toBe(($ano - 1).'-'.($ano + 2));
});
