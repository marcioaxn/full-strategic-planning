<?php

use App\Livewire\ActionPlan\LicoesAprendidas;
use App\Livewire\StrategicPlanning\GerenciarFuturoAlmejado;
use App\Livewire\StrategicPlanning\GerenciarRae;
use App\Livewire\StrategicPlanning\MissaoVisao;
use App\Models\ActionPlan\LicaoAprendida;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\FuturoAlmejado;
use App\Models\StrategicPlanning\IdentidadeEstrategica;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\Rae;
use App\Models\User;
use Livewire\Livewire;

/**
 * Confirma que o Super Admin tem acesso de ESCRITA irrestrito nos módulos
 * corrigidos nas duas últimas sessões (vazamento de responsabilidade),
 * mesmo sem qualquer vínculo direto com a organização/registro em questão.
 */
function superAdminSemVinculo(): User
{
    $orgQualquer = Organization::create(['nom_organizacao' => 'Org Vínculo do Admin', 'sgl_organizacao' => 'OVA', 'cod_organizacao_pai' => null]);
    $user = User::factory()->create(['ativo' => true]);
    $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $orgQualquer->cod_organizacao]);

    return $user;
}

test('super admin cria e edita RAE em organização à qual não tem nenhum vínculo direto', function () {
    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027, 'bln_ativo' => true]);
    $orgAlvo = Organization::create(['nom_organizacao' => 'Org Alvo', 'sgl_organizacao' => 'OA', 'cod_organizacao_pai' => null]);

    $admin = superAdminSemVinculo();

    session(['pei_selecionado_id' => $pei->cod_pei, 'organizacao_selecionada_id' => $orgAlvo->cod_organizacao]);

    Livewire::actingAs($admin)
        ->test(GerenciarRae::class)
        ->set('form.dte_referencia', now()->format('Y-m-d'))
        ->set('form.dsc_tipo_reuniao', 'RAE')
        ->call('salvarRae')
        ->assertHasNoErrors();

    expect(Rae::where('cod_organizacao', $orgAlvo->cod_organizacao)->count())->toBe(1);
});

test('super admin habilita edição e salva Missão/Visão de qualquer organização', function () {
    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027, 'bln_ativo' => true]);
    $orgAlvo = Organization::create(['nom_organizacao' => 'Org Alvo', 'sgl_organizacao' => 'OA', 'cod_organizacao_pai' => null]);

    $admin = superAdminSemVinculo();

    session(['pei_selecionado_id' => $pei->cod_pei, 'organizacao_selecionada_id' => $orgAlvo->cod_organizacao]);

    Livewire::actingAs($admin)
        ->test(MissaoVisao::class)
        ->call('habilitarEdicao')
        ->assertSet('isEditing', true)
        ->set('missao', 'Missão de teste')
        ->call('salvar');

    expect(IdentidadeEstrategica::where('cod_organizacao', $orgAlvo->cod_organizacao)->count())->toBe(1);
});

test('super admin cria Futuro Almejado em objetivo de qualquer organização', function () {
    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027, 'bln_ativo' => true]);
    $perspectiva = Perspectiva::create(['cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'P', 'num_nivel_hierarquico_apresentacao' => 1]);
    $objetivo = Objetivo::create(['cod_perspectiva' => $perspectiva->cod_perspectiva, 'nom_objetivo' => 'O', 'dsc_objetivo' => 'D', 'num_nivel_hierarquico_apresentacao' => 1]);

    $admin = superAdminSemVinculo();

    Livewire::actingAs($admin)
        ->test(GerenciarFuturoAlmejado::class, ['objetivoId' => $objetivo->cod_objetivo])
        ->call('create')
        ->set('form.dsc_futuro_almejado', 'Futuro de teste')
        ->call('save')
        ->assertHasNoErrors();

    expect(FuturoAlmejado::where('cod_objetivo', $objetivo->cod_objetivo)->count())->toBe(1);
});

test('super admin cria lição aprendida em plano de ação de qualquer organização', function () {
    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027, 'bln_ativo' => true]);
    $perspectiva = Perspectiva::create(['cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'P', 'num_nivel_hierarquico_apresentacao' => 1]);
    $objetivo = Objetivo::create(['cod_perspectiva' => $perspectiva->cod_perspectiva, 'nom_objetivo' => 'O', 'dsc_objetivo' => 'D', 'num_nivel_hierarquico_apresentacao' => 1]);
    $orgAlvo = Organization::create(['nom_organizacao' => 'Org Alvo', 'sgl_organizacao' => 'OA', 'cod_organizacao_pai' => null]);
    $plano = PlanoDeAcao::create([
        'cod_objetivo' => $objetivo->cod_objetivo,
        'cod_organizacao' => $orgAlvo->cod_organizacao,
        'cod_tipo_execucao' => TipoExecucao::ACAO,
        'nom_plano_de_acao' => 'Plano Teste',
        'dsc_plano_de_acao' => 'Descrição',
        'num_nivel_hierarquico_apresentacao' => 1,
        'dte_inicio' => now()->toDateString(),
        'dte_fim' => now()->addYear()->toDateString(),
        'bln_status' => true,
    ]);

    $admin = superAdminSemVinculo();

    Livewire::actingAs($admin)
        ->test(LicoesAprendidas::class)
        ->set('form.cod_plano_de_acao', $plano->cod_plano_de_acao)
        ->set('form.dsc_tipo', 'Aprendizado')
        ->set('form.txt_descricao', 'Lição de teste')
        ->call('salvar');

    expect(LicaoAprendida::where('cod_plano_de_acao', $plano->cod_plano_de_acao)->count())->toBe(1);
});
