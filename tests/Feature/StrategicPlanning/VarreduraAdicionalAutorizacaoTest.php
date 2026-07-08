<?php

use App\Livewire\StrategicPlanning\CadeiaDeValor;
use App\Livewire\StrategicPlanning\InaugurarIntegrar;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\AtividadeCadeiaValor;
use App\Models\StrategicPlanning\InauguraPei;
use App\Models\StrategicPlanning\PEI;
use App\Models\User;
use Livewire\Livewire;

/**
 * Segunda rodada da varredura de vazamento de responsabilidade: Cadeia de
 * Valor e Inaugurar/Integrar não tinham NENHUMA autorização em seus métodos
 * de escrita (mesma classe de falha já corrigida em GerenciarRae,
 * GerenciarFuturoAlmejado, MissaoVisao e LicoesAprendidas).
 */
test('usuário sem nenhum perfil NÃO consegue salvar uma atividade da Cadeia de Valor', function () {
    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027, 'bln_ativo' => true]);
    session(['pei_selecionado_id' => $pei->cod_pei]);

    $user = User::factory()->create(['ativo' => true]);

    Livewire::actingAs($user)
        ->test(CadeiaDeValor::class)
        ->set('formAtividade.dsc_atividade', 'Atividade Teste')
        ->call('salvarAtividade')
        ->assertForbidden();

    expect(AtividadeCadeiaValor::count())->toBe(0);
});

test('admin de unidade consegue salvar uma atividade da Cadeia de Valor', function () {
    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027, 'bln_ativo' => true]);
    session(['pei_selecionado_id' => $pei->cod_pei]);

    $user = User::factory()->create(['ativo' => true]);
    $org = Organization::create(['nom_organizacao' => 'Org', 'sgl_organizacao' => 'O', 'cod_organizacao_pai' => null]);
    $user->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $org->cod_organizacao]);

    Livewire::actingAs($user)
        ->test(CadeiaDeValor::class)
        ->set('formAtividade.dsc_atividade', 'Atividade Teste')
        ->call('salvarAtividade');

    expect(AtividadeCadeiaValor::where('dsc_atividade', 'Atividade Teste')->count())->toBe(1);
});

test('usuário sem nenhum perfil NÃO consegue salvar o registro de Inaugurar o Ciclo PEI', function () {
    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027, 'bln_ativo' => true]);
    session(['pei_selecionado_id' => $pei->cod_pei]);

    $user = User::factory()->create(['ativo' => true]);

    Livewire::actingAs($user)
        ->test(InaugurarIntegrar::class)
        ->set('formInaugurar.txt_equipe', 'Equipe Teste')
        ->call('salvarInaugurar')
        ->assertForbidden();

    expect(InauguraPei::count())->toBe(0);
});

test('admin de unidade consegue salvar o registro de Inaugurar o Ciclo PEI', function () {
    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027, 'bln_ativo' => true]);
    session(['pei_selecionado_id' => $pei->cod_pei]);

    $user = User::factory()->create(['ativo' => true]);
    $org = Organization::create(['nom_organizacao' => 'Org', 'sgl_organizacao' => 'O', 'cod_organizacao_pai' => null]);
    $user->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $org->cod_organizacao]);

    Livewire::actingAs($user)
        ->test(InaugurarIntegrar::class)
        ->set('formInaugurar.txt_equipe', 'Equipe Teste')
        ->set('formInaugurar.dte_inicio_processo', now()->format('Y-m-d'))
        ->set('formInaugurar.dte_fim_previsto', now()->addMonths(6)->format('Y-m-d'))
        ->call('salvarInaugurar');

    expect(InauguraPei::where('cod_pei', $pei->cod_pei)->count())->toBe(1);
});
