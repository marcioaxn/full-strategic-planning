<?php

use App\Livewire\StrategicPlanning\GerenciarRae;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Rae;
use App\Models\User;
use Livewire\Livewire;

function montarPeiAtivoParaRae(): PEI
{
    return PEI::create([
        'dsc_pei' => 'Ciclo Teste RAE',
        'num_ano_inicio_pei' => 2024,
        'num_ano_fim_pei' => 2027,
        'bln_ativo' => true,
    ]);
}

test('usuário sem nenhum vínculo com a organização não consegue criar RAE nela mesmo selecionando-a', function () {
    $pei = montarPeiAtivoParaRae();
    $org = Organization::create(['nom_organizacao' => 'Org Alvo', 'sgl_organizacao' => 'OA', 'cod_organizacao_pai' => null]);

    // Usuário autenticado, mas sem NENHUM perfil/vínculo em nenhuma organização.
    $user = User::factory()->create(['ativo' => true]);

    session(['pei_selecionado_id' => $pei->cod_pei, 'organizacao_selecionada_id' => $org->cod_organizacao]);

    Livewire::actingAs($user)
        ->test(GerenciarRae::class)
        ->call('novaRae')
        ->assertForbidden();

    expect(Rae::count())->toBe(0);
});

test('admin unidade da organização consegue criar e salvar um RAE nela', function () {
    $pei = montarPeiAtivoParaRae();
    $org = Organization::create(['nom_organizacao' => 'Org Alvo', 'sgl_organizacao' => 'OA', 'cod_organizacao_pai' => null]);

    $user = User::factory()->create(['ativo' => true]);
    $user->organizacoes()->attach($org->cod_organizacao);
    $user->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $org->cod_organizacao]);

    session(['pei_selecionado_id' => $pei->cod_pei, 'organizacao_selecionada_id' => $org->cod_organizacao]);

    Livewire::actingAs($user)
        ->test(GerenciarRae::class)
        ->set('form.dte_referencia', now()->format('Y-m-d'))
        ->set('form.dsc_tipo_reuniao', 'RAE')
        ->call('salvarRae');

    expect(Rae::where('cod_organizacao', $org->cod_organizacao)->count())->toBe(1);
});

test('admin unidade de uma organização não consegue editar RAE de outra organização, mesmo manipulando o id diretamente', function () {
    $pei = montarPeiAtivoParaRae();
    $orgDoRae = Organization::create(['nom_organizacao' => 'Org do RAE', 'sgl_organizacao' => 'OR', 'cod_organizacao_pai' => null]);
    $orgDoUsuario = Organization::create(['nom_organizacao' => 'Org do Usuario', 'sgl_organizacao' => 'OU', 'cod_organizacao_pai' => null]);

    $rae = Rae::create([
        'cod_pei' => $pei->cod_pei,
        'cod_organizacao' => $orgDoRae->cod_organizacao,
        'dte_referencia' => now()->format('Y-m-d'),
        'dsc_tipo_reuniao' => 'RAE',
    ]);

    $user = User::factory()->create(['ativo' => true]);
    $user->organizacoes()->attach($orgDoUsuario->cod_organizacao);
    $user->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $orgDoUsuario->cod_organizacao]);

    session(['pei_selecionado_id' => $pei->cod_pei, 'organizacao_selecionada_id' => $orgDoUsuario->cod_organizacao]);

    Livewire::actingAs($user)
        ->test(GerenciarRae::class)
        ->call('editarRae', $rae->cod_rae)
        ->assertForbidden();
});
