<?php

/*
 * Permissões por PERFIL nas telas de execução (iniciativas, entregas,
 * indicadores, riscos), pelo caminho da tela.
 *
 * O gestor da Presidência suspeitou de "vazamentos dentro de determinados
 * perfis". A revisão confirmou: o Gestor Substituto se promovia a Responsável,
 * o Administrador de uma unidade vinculava indicador a outra, a lista de
 * riscos sem organização mostrava todas as unidades, o Substituto excluía
 * entrega em definitivo e o anexo aceitava .html no disco público. Cada teste
 * abaixo chama o método público como o navegador chamaria e confere no banco
 * que nada foi gravado — com controles positivos para provar que o uso
 * legítimo segue funcionando.
 */

use App\Livewire\ActionPlan\AtribuirResponsaveis;
use App\Livewire\ActionPlan\ListarPlanos;
use App\Livewire\Deliverables\DeliverablesBoard;
use App\Livewire\PerformanceIndicators\LancarEvolucao;
use App\Livewire\PerformanceIndicators\ListarIndicadores;
use App\Livewire\RiskManagement\ListarRiscos;
use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\RiskManagement\Risco;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/**
 * Unidades A e B no mesmo ciclo; a iniciativa P1 é da A.
 *
 * @return array{orgA: Organization, orgB: Organization, pei: PEI, objetivo: Objetivo, plano: PlanoDeAcao}
 */
function execCenario(): array
{
    $orgA = Organization::create(['nom_organizacao' => 'Unidade A', 'sgl_organizacao' => 'UA']);
    $orgB = Organization::create(['nom_organizacao' => 'Unidade B', 'sgl_organizacao' => 'UB']);

    $pei = PEI::create([
        'dsc_pei' => 'Ciclo Perfis',
        'num_ano_inicio_pei' => (int) date('Y'),
        'num_ano_fim_pei' => (int) date('Y') + 3,
    ]);
    $perspectiva = Perspectiva::create([
        'cod_pei' => $pei->cod_pei,
        'dsc_perspectiva' => 'Processos',
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);
    $objetivo = Objetivo::create([
        'cod_perspectiva' => $perspectiva->cod_perspectiva,
        'nom_objetivo' => 'Objetivo Perfis',
        'dsc_objetivo' => 'Objetivo do teste de perfis.',
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);

    $plano = execPlano($objetivo, $orgA, 'Iniciativa P1');

    Session::put('pei_selecionado_id', $pei->cod_pei);

    return compact('orgA', 'orgB', 'pei', 'objetivo', 'plano');
}

function execPlano(Objetivo $objetivo, Organization $org, string $nome): PlanoDeAcao
{
    $plano = PlanoDeAcao::create([
        'cod_objetivo' => $objetivo->cod_objetivo,
        'cod_organizacao' => $org->cod_organizacao,
        'cod_tipo_execucao' => TipoExecucao::ACAO,
        'dsc_plano_de_acao' => $nome,
        'num_nivel_hierarquico_apresentacao' => 1,
        'dte_inicio' => now()->startOfYear()->toDateString(),
        'dte_fim' => now()->endOfYear()->toDateString(),
        'bln_status' => 'Em Andamento',
    ]);
    $plano->organizacoes()->sync([$org->cod_organizacao]);

    return $plano;
}

/** Usuário com UM vínculo de perfil; a unidade do vínculo fica selecionada. */
function execUsuario(string $perfil, Organization $org, ?PlanoDeAcao $plano = null): User
{
    $user = User::factory()->create(['ativo' => true]);
    $user->perfisAcesso()->attach($perfil, [
        'cod_organizacao' => $org->cod_organizacao,
        'cod_plano_de_acao' => $plano?->cod_plano_de_acao,
    ]);
    $user->organizacoes()->syncWithoutDetaching([$org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);

    return $user;
}

function execEntrega(PlanoDeAcao $plano): Entrega
{
    return Entrega::create([
        'cod_plano_de_acao' => $plano->cod_plano_de_acao,
        'dsc_entrega' => 'Entrega do teste',
        'bln_status' => 'Não Iniciado',
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);
}

function execGestoresDoPlano(PlanoDeAcao $plano): int
{
    return DB::table('organization.rel_users_tab_organizacoes_tab_perfil_acesso')
        ->where('cod_plano_de_acao', $plano->cod_plano_de_acao)
        ->count();
}

// ── C2: designar gestor é do Administrador ─────────────────────────────────

test('o Gestor Substituto não se promove a Gestor Responsável', function () {
    $c = execCenario();
    $substituto = execUsuario(PerfilAcesso::GESTOR_SUBSTITUTO, $c['orgA'], $c['plano']);
    $antes = execGestoresDoPlano($c['plano']);

    Livewire::actingAs($substituto)
        ->test(AtribuirResponsaveis::class, ['planoId' => $c['plano']->cod_plano_de_acao])
        ->set('novo_usuario_id', $substituto->id)
        ->set('novo_perfil_id', PerfilAcesso::GESTOR_RESPONSAVEL)
        ->call('adicionar')
        ->assertForbidden();

    expect(execGestoresDoPlano($c['plano']))->toBe($antes);
});

test('o Gestor Substituto não dá o papel de gestor a outra pessoa', function () {
    $c = execCenario();
    $substituto = execUsuario(PerfilAcesso::GESTOR_SUBSTITUTO, $c['orgA'], $c['plano']);
    $colega = User::factory()->create(['ativo' => true]);
    $colega->organizacoes()->attach($c['orgA']->cod_organizacao);
    $antes = execGestoresDoPlano($c['plano']);

    Livewire::actingAs($substituto)
        ->test(AtribuirResponsaveis::class, ['planoId' => $c['plano']->cod_plano_de_acao])
        ->set('novo_usuario_id', $colega->id)
        ->set('novo_perfil_id', PerfilAcesso::GESTOR_RESPONSAVEL)
        ->call('adicionar')
        ->assertForbidden();

    expect(execGestoresDoPlano($c['plano']))->toBe($antes);
});

test('o Administrador da unidade designa gestor da unidade, mas não pessoa de fora nem a si mesmo', function () {
    $c = execCenario();
    $admin = execUsuario(PerfilAcesso::ADMIN_UNIDADE, $c['orgA']);
    $daUnidade = User::factory()->create(['ativo' => true]);
    $daUnidade->organizacoes()->attach($c['orgA']->cod_organizacao);
    $deFora = User::factory()->create(['ativo' => true]);
    $deFora->organizacoes()->attach($c['orgB']->cod_organizacao);

    $tela = Livewire::actingAs($admin)
        ->test(AtribuirResponsaveis::class, ['planoId' => $c['plano']->cod_plano_de_acao]);

    $tela->set('novo_usuario_id', $deFora->id)->set('novo_perfil_id', PerfilAcesso::GESTOR_RESPONSAVEL)
        ->call('adicionar')->assertHasErrors(['novo_usuario_id']);
    $tela->set('novo_usuario_id', $admin->id)->set('novo_perfil_id', PerfilAcesso::GESTOR_RESPONSAVEL)
        ->call('adicionar')->assertHasErrors(['novo_usuario_id']);
    expect(execGestoresDoPlano($c['plano']))->toBe(0);

    $tela->set('novo_usuario_id', $daUnidade->id)->set('novo_perfil_id', PerfilAcesso::GESTOR_RESPONSAVEL)
        ->call('adicionar')->assertHasNoErrors();
    expect(execGestoresDoPlano($c['plano']))->toBe(1);
});

// ── Titularidade e criação de iniciativa ───────────────────────────────────

test('o Gestor de outra iniciativa não edita esta', function () {
    $c = execCenario();
    $outra = execPlano($c['objetivo'], $c['orgA'], 'Iniciativa P2');
    $gestorDaOutra = execUsuario(PerfilAcesso::GESTOR_RESPONSAVEL, $c['orgA'], $outra);

    Livewire::actingAs($gestorDaOutra)
        ->test(ListarPlanos::class)
        ->call('edit', $c['plano']->cod_plano_de_acao)
        ->assertForbidden();
});

test('o Gestor Responsável não cria iniciativa (é ato do Administrador)', function () {
    $c = execCenario();
    $gestor = execUsuario(PerfilAcesso::GESTOR_RESPONSAVEL, $c['orgA'], $c['plano']);
    $antes = PlanoDeAcao::count();

    Livewire::actingAs($gestor)
        ->test(ListarPlanos::class)
        ->set('dsc_plano_de_acao', 'Iniciativa indevida')
        ->set('cod_objetivo', $c['objetivo']->cod_objetivo)
        ->set('cod_tipo_execucao', TipoExecucao::ACAO)
        ->set('dte_inicio', now()->startOfYear()->toDateString())
        ->set('dte_fim', now()->endOfYear()->toDateString())
        ->set('organizacoes_ids', [$c['orgA']->cod_organizacao])
        ->call('save')
        ->assertForbidden();

    expect(PlanoDeAcao::count())->toBe($antes);
});

// ── Consulta: lê, não grava ─────────────────────────────────────────────────

test('o perfil Consulta não cria nem edita iniciativa', function () {
    $c = execCenario();
    $consulta = execUsuario(PerfilAcesso::CONSULTA, $c['orgA']);

    Livewire::actingAs($consulta)->test(ListarPlanos::class)
        ->call('edit', $c['plano']->cod_plano_de_acao)
        ->assertForbidden();

    Livewire::actingAs($consulta)->test(ListarPlanos::class)
        ->call('create')
        ->assertForbidden();
});

test('o perfil Consulta não cria entrega, não lança evolução, não cria indicador nem risco', function () {
    $c = execCenario();
    $consulta = execUsuario(PerfilAcesso::CONSULTA, $c['orgA']);
    $indicador = Indicador::create([
        'nom_indicador' => 'Indicador P1',
        'dsc_indicador' => 'Indicador da iniciativa P1',
        'bln_acumulado' => 'Não',
        'dsc_periodo_medicao' => 'Mensal',
        'dsc_tipo' => 'Iniciativa',
        'cod_plano_de_acao' => $c['plano']->cod_plano_de_acao,
        'dsc_unidade_medida' => 'Percentual (%)',
    ]);

    Livewire::actingAs($consulta)
        ->test(DeliverablesBoard::class, ['planoId' => $c['plano']->cod_plano_de_acao])
        ->set('quickAddTitulo', 'Entrega indevida')
        ->call('criarRapido')
        ->assertForbidden();

    // Negação na abertura da tela vira redirecionamento com aviso (handler
    // de AuthorizationException em bootstrap/app.php): a tela não abre.
    Livewire::actingAs($consulta)
        ->test(LancarEvolucao::class, ['indicadorId' => $indicador->cod_indicador])
        ->assertRedirect();

    Livewire::actingAs($consulta)->test(ListarIndicadores::class)
        ->set('form.nom_indicador', 'Indicador indevido')
        ->set('form.dsc_tipo', 'Iniciativa')
        ->set('form.cod_plano_de_acao', $c['plano']->cod_plano_de_acao)
        ->set('form.organizacoes_ids', [$c['orgA']->cod_organizacao])
        ->call('save')
        ->assertForbidden();

    Livewire::actingAs($consulta)->test(ListarRiscos::class)
        ->call('create')
        ->assertForbidden();

    expect(Entrega::count())->toBe(0)
        ->and(Indicador::count())->toBe(1)
        ->and(Risco::count())->toBe(0);
});

// ── C11: excluir entrega é capacidade própria ───────────────────────────────

test('o Gestor Substituto edita a entrega, mas não a exclui (nem em definitivo)', function () {
    $c = execCenario();
    $substituto = execUsuario(PerfilAcesso::GESTOR_SUBSTITUTO, $c['orgA'], $c['plano']);
    $entrega = execEntrega($c['plano']);

    $quadro = Livewire::actingAs($substituto)
        ->test(DeliverablesBoard::class, ['planoId' => $c['plano']->cod_plano_de_acao]);

    $quadro->call('atualizarStatus', $entrega->cod_entrega, 'Em Andamento')->assertOk();
    expect($entrega->fresh()->bln_status)->toBe('Em Andamento');

    $quadro->call('confirmDeleteEntrega', $entrega->cod_entrega)->assertForbidden();

    Livewire::actingAs($substituto)
        ->test(DeliverablesBoard::class, ['planoId' => $c['plano']->cod_plano_de_acao])
        ->call('excluirPermanente', $entrega->cod_entrega)
        ->assertForbidden();

    expect(Entrega::withTrashed()->find($entrega->cod_entrega))->not->toBeNull()
        ->and($entrega->fresh()->trashed())->toBeFalse();
});

test('o Gestor Responsável da iniciativa exclui a entrega', function () {
    $c = execCenario();
    $responsavel = execUsuario(PerfilAcesso::GESTOR_RESPONSAVEL, $c['orgA'], $c['plano']);
    $entrega = execEntrega($c['plano']);

    Livewire::actingAs($responsavel)
        ->test(DeliverablesBoard::class, ['planoId' => $c['plano']->cod_plano_de_acao])
        ->call('confirmDeleteEntrega', $entrega->cod_entrega)
        ->call('excluir')
        ->assertOk();

    expect($entrega->fresh()->trashed())->toBeTrue();
});

// ── C15: anexo só de documento/imagem ───────────────────────────────────────

test('anexo .html é recusado antes de chegar ao disco público', function () {
    Storage::fake('public');
    $c = execCenario();
    $responsavel = execUsuario(PerfilAcesso::GESTOR_RESPONSAVEL, $c['orgA'], $c['plano']);
    $entrega = execEntrega($c['plano']);

    Livewire::actingAs($responsavel)
        ->test(DeliverablesBoard::class, ['planoId' => $c['plano']->cod_plano_de_acao])
        ->call('openDetails', $entrega->cod_entrega)
        ->set('anexosUpload', [UploadedFile::fake()->createWithContent('pagina.html', '<script>alert(1)</script>')])
        ->assertHasErrors(['anexosUpload.0']);

    expect(Storage::disk('public')->allFiles('entregas/anexos'))->toBeEmpty();
});

// ── C6: "sem organização" não é "todas as unidades" ─────────────────────────

test('a lista de riscos com organização nula não mostra riscos de outra unidade', function () {
    $c = execCenario();
    $admin = execUsuario(PerfilAcesso::ADMIN_UNIDADE, $c['orgA']);

    Risco::create([
        'cod_pei' => $c['pei']->cod_pei, 'cod_organizacao' => $c['orgA']->cod_organizacao,
        'dsc_titulo' => 'Risco visível da unidade A', 'txt_descricao' => 'Descrição A', 'dsc_categoria' => 'Operacional', 'dsc_status' => 'Identificado',
        'num_probabilidade' => 3, 'num_impacto' => 3,
    ]);
    Risco::create([
        'cod_pei' => $c['pei']->cod_pei, 'cod_organizacao' => $c['orgB']->cod_organizacao,
        'dsc_titulo' => 'Risco sigiloso da unidade B', 'txt_descricao' => 'Descrição B', 'dsc_categoria' => 'Operacional', 'dsc_status' => 'Identificado',
        'num_probabilidade' => 3, 'num_impacto' => 3,
    ]);

    Livewire::actingAs($admin)
        ->test(ListarRiscos::class)
        ->call('atualizarOrganizacao', null)
        ->assertSee('Risco visível da unidade A')
        ->assertDontSee('Risco sigiloso da unidade B');
});

// ── C10: indicador não se vincula a unidade alheia ──────────────────────────

test('o Administrador da unidade A não cria indicador vinculado à unidade B', function () {
    $c = execCenario();
    $admin = execUsuario(PerfilAcesso::ADMIN_UNIDADE, $c['orgA']);

    Livewire::actingAs($admin)->test(ListarIndicadores::class)
        ->set('form.nom_indicador', 'Indicador na unidade alheia')
        ->set('form.dsc_tipo', 'Objetivo')
        ->set('form.cod_objetivo', $c['objetivo']->cod_objetivo)
        ->set('form.organizacoes_ids', [$c['orgB']->cod_organizacao])
        ->call('save')
        ->assertForbidden();

    expect(Indicador::count())->toBe(0);

    // Controle positivo: na própria unidade, grava.
    Livewire::actingAs($admin)->test(ListarIndicadores::class)
        ->set('form.nom_indicador', 'Indicador da unidade A')
        ->set('form.dsc_tipo', 'Objetivo')
        ->set('form.cod_objetivo', $c['objetivo']->cod_objetivo)
        ->set('form.organizacoes_ids', [$c['orgA']->cod_organizacao])
        ->call('save')
        ->assertHasNoErrors();

    expect(Indicador::count())->toBe(1);
});
