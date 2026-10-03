<?php

/*
 * Permissões do Planejamento por PERFIL e por UNIDADE, pelo caminho da tela.
 *
 * Origem: o gestor da Presidência suspeitou de "vazamentos de condições dentro
 * de determinados perfis" e a revisão confirmou:
 *  - a permissão somava os perfis do usuário em todas as unidades
 *    (Administrador na A e Gestor Substituto na B apagava itens da B);
 *  - Gestores reescreviam missão, análises e objetivos, embora a ajuda diga que
 *    o Gestor só responde pelas iniciativas a que está vinculado;
 *  - o Administrador de qualquer unidade alterava perspectivas e faixas do
 *    farol, que valem para a instituição inteira;
 *  - na SWOT, o cenário de outra unidade era excluído pelo id.
 *
 * Cada teste chama o método que o botão da tela chama (Livewire::test()->call),
 * e há controles positivos — um teste que só espera 403 passaria com tudo
 * bloqueado.
 */

use App\Livewire\StrategicPlanning\AnalisePESTEL;
use App\Livewire\StrategicPlanning\AnaliseSWOT;
use App\Livewire\StrategicPlanning\ListarGrausSatisfacao;
use App\Livewire\StrategicPlanning\ListarObjetivos;
use App\Livewire\StrategicPlanning\ListarPerspectivas;
use App\Livewire\StrategicPlanning\ListarValores;
use App\Livewire\StrategicPlanning\MissaoVisao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\AnaliseAmbiental;
use App\Models\StrategicPlanning\CenarioProspectivo;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

/** Raiz da instituição: aponta para si mesma, como na base real. */
function ppRaiz(): Organization
{
    $org = Organization::create(['nom_organizacao' => 'Ministério', 'sgl_organizacao' => 'MIN']);
    $org->update(['rel_cod_organizacao' => $org->cod_organizacao]);

    return $org;
}

function ppUnidade(Organization $pai, string $sigla): Organization
{
    return Organization::create([
        'nom_organizacao' => "Unidade {$sigla}",
        'sgl_organizacao' => $sigla,
        'rel_cod_organizacao' => $pai->cod_organizacao,
    ]);
}

/** @param array<int, array{0: string, 1: Organization}> $vinculos [perfil, organização] */
function ppUsuario(array $vinculos): User
{
    $user = User::factory()->create();

    foreach ($vinculos as [$perfil, $org]) {
        $user->perfisAcesso()->attach($perfil, ['cod_organizacao' => $org->cod_organizacao]);
        $user->organizacoes()->syncWithoutDetaching([$org->cod_organizacao]);
    }

    $user->unsetRelation('perfisAcesso');

    return $user;
}

function ppCiclo(Organization $selecionada): PEI
{
    $pei = PEI::create([
        'dsc_pei' => 'Ciclo de Teste',
        'num_ano_inicio_pei' => (int) date('Y'),
        'num_ano_fim_pei' => (int) date('Y') + 3,
    ]);

    Session::put('organizacao_selecionada_id', $selecionada->cod_organizacao);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    return $pei;
}

function ppItemPestel(PEI $pei, Organization $org): AnaliseAmbiental
{
    return AnaliseAmbiental::create([
        'cod_pei' => $pei->cod_pei,
        'cod_organizacao' => $org->cod_organizacao,
        'dsc_tipo_analise' => AnaliseAmbiental::TIPO_PESTEL,
        'dsc_categoria' => AnaliseAmbiental::PESTEL_POLITICO,
        'dsc_item' => 'Mudança regulatória',
        'num_impacto' => 3,
    ]);
}

// ── Gestor Responsável: lê o planejamento, não o reescreve ──────────────────

test('Gestor Responsável não grava item da PESTEL, missão nem objetivo', function () {
    $raiz = ppRaiz();
    $a = ppUnidade($raiz, 'A');
    $gestor = ppUsuario([[PerfilAcesso::GESTOR_RESPONSAVEL, $a]]);
    $pei = ppCiclo($a);
    $perspectiva = Perspectiva::create(['cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'Processos', 'num_nivel_hierarquico_apresentacao' => 1]);

    Livewire::actingAs($gestor)->test(AnalisePESTEL::class)
        ->set('dsc_categoria', AnaliseAmbiental::PESTEL_POLITICO)
        ->set('dsc_item', 'Item novo')
        ->call('save')
        ->assertForbidden();

    Livewire::actingAs($gestor)->test(MissaoVisao::class)
        ->set('missao', 'Missão reescrita pelo gestor')
        ->call('salvar')
        ->assertForbidden();

    Livewire::actingAs($gestor)->test(ListarObjetivos::class)
        ->set('nom_objetivo', 'Objetivo do gestor')
        ->set('cod_perspectiva', $perspectiva->cod_perspectiva)
        ->set('num_nivel_hierarquico_apresentacao', 1)
        ->call('save')
        ->assertForbidden();

    expect(AnaliseAmbiental::count())->toBe(0)
        ->and(Objetivo::count())->toBe(0);
});

// ── Consulta: só leitura ────────────────────────────────────────────────────

test('Consulta abre as telas e não grava nada', function () {
    $raiz = ppRaiz();
    $a = ppUnidade($raiz, 'A');
    $consulta = ppUsuario([[PerfilAcesso::CONSULTA, $a]]);
    ppCiclo($a);

    Livewire::actingAs($consulta)->test(AnalisePESTEL::class)
        ->assertOk()
        ->call('create', AnaliseAmbiental::PESTEL_POLITICO)
        ->assertForbidden();

    Livewire::actingAs($consulta)->test(ListarValores::class)
        ->assertOk()
        ->set('nom_valor', 'Transparência')
        ->call('save')
        ->assertForbidden();

    Livewire::actingAs($consulta)->test(ListarPerspectivas::class)
        ->assertOk()
        ->set('dsc_perspectiva', 'Nova')
        ->call('save')
        ->assertForbidden();
});

// ── Dado institucional: só a raiz ───────────────────────────────────────────

test('Administrador de unidade folha não altera perspectiva nem faixa do farol; o da raiz altera', function () {
    $raiz = ppRaiz();
    $folha = ppUnidade($raiz, 'FOLHA');
    $adminFolha = ppUsuario([[PerfilAcesso::ADMIN_UNIDADE, $folha]]);
    $adminRaiz = ppUsuario([[PerfilAcesso::ADMIN_UNIDADE, $raiz]]);
    $pei = ppCiclo($folha);

    Livewire::actingAs($adminFolha)->test(ListarPerspectivas::class)
        ->set('dsc_perspectiva', 'Perspectiva da folha')
        ->set('num_nivel_hierarquico_apresentacao', 1)
        ->call('save')
        ->assertForbidden();

    Livewire::actingAs($adminFolha)->test(ListarGrausSatisfacao::class)
        ->call('openModal')
        ->assertForbidden();

    Session::put('organizacao_selecionada_id', $raiz->cod_organizacao);

    Livewire::actingAs($adminRaiz)->test(ListarPerspectivas::class)
        ->set('dsc_perspectiva', 'Perspectiva da raiz')
        ->set('num_nivel_hierarquico_apresentacao', 1)
        ->call('save')
        ->assertHasNoErrors();

    expect(Perspectiva::where('cod_pei', $pei->cod_pei)->pluck('dsc_perspectiva')->all())
        ->toBe(['Perspectiva da raiz']);
});

// ── C1: o perfil vale na unidade do vínculo ─────────────────────────────────

test('Administrador na unidade A e Gestor Substituto na B não exclui item da PESTEL da B', function () {
    $raiz = ppRaiz();
    $a = ppUnidade($raiz, 'A');
    $b = ppUnidade($raiz, 'B');
    $user = ppUsuario([
        [PerfilAcesso::ADMIN_UNIDADE, $a],
        [PerfilAcesso::GESTOR_SUBSTITUTO, $b],
    ]);
    $pei = ppCiclo($b);
    $itemDaB = ppItemPestel($pei, $b);

    Livewire::actingAs($user)->test(AnalisePESTEL::class)
        ->call('delete', $itemDaB->cod_analise)
        ->assertForbidden();

    expect(AnaliseAmbiental::whereKey($itemDaB->cod_analise)->exists())->toBeTrue();

    // Controle positivo: na A, onde é Administrador, exclui.
    Session::put('organizacao_selecionada_id', $a->cod_organizacao);
    $itemDaA = ppItemPestel($pei, $a);

    Livewire::actingAs($user)->test(AnalisePESTEL::class)
        ->call('delete', $itemDaA->cod_analise)
        ->assertOk();

    expect(AnaliseAmbiental::whereKey($itemDaA->cod_analise)->exists())->toBeFalse();
});

// ── C9: SWOT, registro de outra unidade ─────────────────────────────────────

test('na SWOT, o Administrador da unidade A não exclui cenário da unidade B pelo id', function () {
    $raiz = ppRaiz();
    $a = ppUnidade($raiz, 'A');
    $b = ppUnidade($raiz, 'B');
    $adminA = ppUsuario([[PerfilAcesso::ADMIN_UNIDADE, $a]]);
    $pei = ppCiclo($a);

    $cenarioDaB = CenarioProspectivo::create([
        'cod_pei' => $pei->cod_pei,
        'cod_organizacao' => $b->cod_organizacao,
        'nom_cenario' => 'Cenário da B',
        'dsc_tipo' => 'Tendencial',
        'num_probabilidade' => 3,
        'num_impacto' => 3,
    ]);

    Livewire::actingAs($adminA)->test(AnaliseSWOT::class)
        ->call('excluirCenario', $cenarioDaB->getKey())
        ->assertForbidden();

    expect(CenarioProspectivo::whereKey($cenarioDaB->getKey())->exists())->toBeTrue();
});

// ── C5: save() com id vazio CRIA ────────────────────────────────────────────

test('Gestor Substituto não cria objetivo chamando save() direto, nem como Administrador de unidade folha', function () {
    $raiz = ppRaiz();
    $a = ppUnidade($raiz, 'A');
    $gs = ppUsuario([[PerfilAcesso::GESTOR_SUBSTITUTO, $a]]);
    $adminFolha = ppUsuario([[PerfilAcesso::ADMIN_UNIDADE, $a]]);
    $pei = ppCiclo($a);
    $perspectiva = Perspectiva::create(['cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'Resultados', 'num_nivel_hierarquico_apresentacao' => 1]);

    foreach ([$gs, $adminFolha] as $quem) {
        Livewire::actingAs($quem)->test(ListarObjetivos::class)
            ->set('nom_objetivo', 'Objetivo indevido')
            ->set('cod_perspectiva', $perspectiva->cod_perspectiva)
            ->set('num_nivel_hierarquico_apresentacao', 1)
            ->call('save')
            ->assertForbidden();
    }

    expect(Objetivo::count())->toBe(0);
});

test('o id do objetivo em edição não pode ser trocado pelo navegador', function () {
    $raiz = ppRaiz();
    $admin = ppUsuario([[PerfilAcesso::ADMIN_UNIDADE, $raiz]]);
    ppCiclo($raiz);

    Livewire::actingAs($admin)->test(ListarObjetivos::class)
        ->set('objetivoId', 'qualquer-id');
})->throws(CannotUpdateLockedPropertyException::class);
