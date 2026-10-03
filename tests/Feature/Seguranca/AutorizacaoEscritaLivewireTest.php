<?php

/*
 * IDOR em métodos públicos de componentes Livewire.
 *
 * Todo método público de um componente é um endpoint: o navegador manda o id
 * que quiser. Autorizar o "pai" da tela (o plano, o risco, o indicador) não
 * basta — se o método busca o filho só pelo id (Model::findOrFail($id)), quem
 * pode editar UM plano apaga entrega, anexo ou vínculo de QUALQUER outro.
 *
 * Estes testes chamam os métodos pelo caminho da tela, com ids de registros
 * que pertencem a OUTRO plano/risco/indicador/organização, e conferem no banco
 * que nada foi alterado. Há também controles positivos, para provar que o
 * recorte não bloqueou o uso legítimo.
 */

use App\Livewire\ActionPlan\AtribuirResponsaveis;
use App\Livewire\ActionPlan\GerenciarEntregas;
use App\Livewire\Deliverables\DeliverablesBoard;
use App\Livewire\PerformanceIndicators\LancarEvolucao;
use App\Livewire\Reports\AgendarRelatorio;
use App\Livewire\RiskManagement\GerenciarMitigacoes;
use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\EntregaAnexo;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\PerformanceIndicators\EvolucaoIndicador;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\Reports\RelatorioAgendado;
use App\Models\RiskManagement\Risco;
use App\Models\StrategicPlanning\Arquivo;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Livewire;

const PIVOT_PERFIS = 'organization.rel_users_tab_organizacoes_tab_perfil_acesso';

/**
 * Duas organizações no mesmo PEI, cada uma com o próprio objetivo.
 *
 * @return array{0: PEI, 1: Organization, 2: Organization, 3: Objetivo}
 */
function cenarioDuasOrganizacoes(): array
{
    $orgA = Organization::create(['nom_organizacao' => 'Órgão A', 'sgl_organizacao' => 'OGA', 'cod_organizacao_pai' => null]);
    $orgB = Organization::create(['nom_organizacao' => 'Órgão B', 'sgl_organizacao' => 'OGB', 'cod_organizacao_pai' => null]);

    $pei = PEI::create([
        'dsc_pei' => 'Ciclo IDOR',
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
        'nom_objetivo' => 'Objetivo IDOR',
        'dsc_objetivo' => 'Objetivo do teste de autorização.',
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);

    Session::put('pei_selecionado_id', $pei->cod_pei);

    return [$pei, $orgA, $orgB, $objetivo];
}

function adminDaUnidade(Organization $org): User
{
    $user = User::factory()->create(['ativo' => true]);
    $user->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');
    $user->organizacoes()->syncWithoutDetaching([$org->cod_organizacao]);

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);

    return $user;
}

function superAdminIdor(Organization $org): User
{
    $user = User::factory()->create(['ativo' => true]);
    $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');

    return $user;
}

function planoIdor(Organization $org, Objetivo $objetivo, string $nome): PlanoDeAcao
{
    return PlanoDeAcao::create([
        'cod_objetivo' => $objetivo->cod_objetivo,
        'cod_organizacao' => $org->cod_organizacao,
        'cod_tipo_execucao' => TipoExecucao::ACAO,
        'nom_plano_de_acao' => $nome,
        'dsc_plano_de_acao' => $nome,
        'num_nivel_hierarquico_apresentacao' => 1,
        'dte_inicio' => now()->toDateString(),
        'dte_fim' => now()->addYear()->toDateString(),
        'bln_status' => true,
    ]);
}

function entregaIdor(PlanoDeAcao $plano, string $nome): Entrega
{
    return Entrega::create([
        'cod_plano_de_acao' => $plano->cod_plano_de_acao,
        'dsc_entrega' => $nome,
        'bln_status' => 'Não Iniciado',
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);
}

/**
 * O recorte faz o findOrFail falhar (404) — ou, nos métodos que usam
 * where()->delete(), simplesmente não casa nenhuma linha. As duas formas são
 * aceitáveis; a asserção que importa é a do banco, feita depois.
 */
function chamarIgnorandoNaoEncontrado(callable $chamada): void
{
    try {
        $chamada();
    } catch (ModelNotFoundException) {
        // esperado
    }
}

// ── Indicadores ──────────────────────────────────────────────────────────────

test('LancarEvolucao não apaga evidência de evolução de outro indicador', function () {
    [, $orgA, , $objetivo] = cenarioDuasOrganizacoes();

    $criarIndicador = fn (string $nome) => Indicador::create([
        'cod_objetivo' => $objetivo->cod_objetivo,
        'nom_indicador' => $nome,
        'dsc_indicador' => 'Indicador do teste de IDOR.',
        'dsc_tipo' => 'Objetivo',
        'dsc_unidade_medida' => 'Percentual (%)',
        'bln_acumulado' => 'Não',
        'dsc_periodo_medicao' => 'Mensal',
        'dsc_polaridade' => 'Positiva (Quanto maior, melhor)',
    ]);

    $indicadorDaTela = $criarIndicador('Indicador da tela');
    $outroIndicador = $criarIndicador('Outro indicador');

    $evolucaoAlheia = EvolucaoIndicador::create([
        'cod_indicador' => $outroIndicador->cod_indicador,
        'num_ano' => (int) date('Y'),
        'num_mes' => 1,
        'vlr_previsto' => 10,
        'vlr_realizado' => 5,
        'bln_atualizado' => 'Sim',
    ]);

    $arquivoAlheio = Arquivo::create([
        'cod_evolucao_indicador' => $evolucaoAlheia->cod_evolucao_indicador,
        'txt_assunto' => 'evidencia.pdf',
        'data' => now()->format('Y-m-d'),
        'dsc_nome_arquivo' => 'pei/evidencias/evidencia.pdf',
        'dsc_tipo' => 'pdf',
    ]);

    $componente = Livewire::actingAs(superAdminIdor($orgA))
        ->test(LancarEvolucao::class, ['indicadorId' => $indicadorDaTela->cod_indicador]);

    chamarIgnorandoNaoEncontrado(fn () => $componente->call('excluirArquivo', $arquivoAlheio->cod_arquivo));

    expect(Arquivo::whereKey($arquivoAlheio->cod_arquivo)->exists())->toBeTrue();
});

// ── Entregas: quadro (DeliverablesBoard) ────────────────────────────────────

test('o quadro de entregas não apaga nem altera entrega de outro plano', function () {
    [, $orgA, $orgB, $objetivo] = cenarioDuasOrganizacoes();

    $planoA = planoIdor($orgA, $objetivo, 'Plano A');
    $planoB = planoIdor($orgB, $objetivo, 'Plano B');
    $entregaB = entregaIdor($planoB, 'Entrega do plano B');

    $componente = Livewire::actingAs(adminDaUnidade($orgA))
        ->test(DeliverablesBoard::class, ['planoId' => $planoA->cod_plano_de_acao]);

    $componente->call('excluirPermanente', $entregaB->cod_entrega);
    $componente->call('atualizarTitulo', $entregaB->cod_entrega, 'título trocado por outro órgão');
    chamarIgnorandoNaoEncontrado(fn () => $componente->call('confirmDeleteEntrega', $entregaB->cod_entrega));

    $entregaB->refresh();
    expect($entregaB->exists)->toBeTrue()
        ->and($entregaB->dsc_entrega)->toBe('Entrega do plano B')
        ->and($componente->get('entregaParaExcluirId'))->toBeNull();
});

test('o quadro de entregas não apaga anexo de entrega de outro plano', function () {
    [, $orgA, $orgB, $objetivo] = cenarioDuasOrganizacoes();

    $planoA = planoIdor($orgA, $objetivo, 'Plano A');
    $planoB = planoIdor($orgB, $objetivo, 'Plano B');
    $entregaB = entregaIdor($planoB, 'Entrega do plano B');
    $usuario = adminDaUnidade($orgA);

    $anexoB = EntregaAnexo::create([
        'cod_entrega' => $entregaB->cod_entrega,
        'cod_usuario' => $usuario->id,
        'dsc_nome_arquivo' => 'contrato.pdf',
        'dsc_caminho' => 'entregas/anexos/contrato.pdf',
        'dsc_mime_type' => 'application/pdf',
        'num_tamanho_bytes' => 10,
    ]);

    $componente = Livewire::actingAs($usuario)
        ->test(DeliverablesBoard::class, ['planoId' => $planoA->cod_plano_de_acao]);

    chamarIgnorandoNaoEncontrado(fn () => $componente->call('excluirAnexo', $anexoB->cod_anexo));

    expect(EntregaAnexo::whereKey($anexoB->cod_anexo)->exists())->toBeTrue();
});

test('o quadro de entregas continua excluindo entrega do próprio plano', function () {
    [, $orgA, , $objetivo] = cenarioDuasOrganizacoes();

    $planoA = planoIdor($orgA, $objetivo, 'Plano A');
    $entregaA = entregaIdor($planoA, 'Entrega do plano A');

    Livewire::actingAs(adminDaUnidade($orgA))
        ->test(DeliverablesBoard::class, ['planoId' => $planoA->cod_plano_de_acao])
        ->call('excluirPermanente', $entregaA->cod_entrega);

    expect(Entrega::withTrashed()->whereKey($entregaA->cod_entrega)->exists())->toBeFalse();
});

// ── Entregas: GerenciarEntregas ─────────────────────────────────────────────

test('GerenciarEntregas não apaga entrega de outro plano', function () {
    [, $orgA, $orgB, $objetivo] = cenarioDuasOrganizacoes();

    $planoA = planoIdor($orgA, $objetivo, 'Plano A');
    $planoB = planoIdor($orgB, $objetivo, 'Plano B');
    $entregaB = entregaIdor($planoB, 'Entrega do plano B');

    $componente = Livewire::actingAs(adminDaUnidade($orgA))
        ->test(GerenciarEntregas::class, ['planoId' => $planoA->cod_plano_de_acao]);

    chamarIgnorandoNaoEncontrado(fn () => $componente->call('delete', $entregaB->cod_entrega));

    expect(Entrega::whereKey($entregaB->cod_entrega)->exists())->toBeTrue();
});

// ── Responsáveis: remover() na pivot de perfis ──────────────────────────────

test('remover() não apaga vínculo de perfil de outra organização', function () {
    [, $orgA, $orgB, $objetivo] = cenarioDuasOrganizacoes();

    $planoA = planoIdor($orgA, $objetivo, 'Plano A');

    // O Admin da Unidade do órgão B — a linha mais valiosa da pivot.
    $adminB = User::factory()->create(['ativo' => true]);
    $vinculoAlheio = (string) Str::uuid();
    DB::table(PIVOT_PERFIS)->insert([
        'id' => $vinculoAlheio,
        'user_id' => $adminB->id,
        'cod_organizacao' => $orgB->cod_organizacao,
        'cod_perfil' => PerfilAcesso::ADMIN_UNIDADE,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Controle positivo: gestor deste plano, que a tela gerencia.
    $gestor = User::factory()->create(['ativo' => true]);
    $vinculoDoPlano = (string) Str::uuid();
    DB::table(PIVOT_PERFIS)->insert([
        'id' => $vinculoDoPlano,
        'user_id' => $gestor->id,
        'cod_organizacao' => $orgA->cod_organizacao,
        'cod_perfil' => PerfilAcesso::GESTOR_RESPONSAVEL,
        'cod_plano_de_acao' => $planoA->cod_plano_de_acao,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $usuario = adminDaUnidade($orgA);
    $vinculoAdminProprio = DB::table(PIVOT_PERFIS)->where('user_id', $usuario->id)->value('id');

    $componente = Livewire::actingAs($usuario)
        ->test(AtribuirResponsaveis::class, ['planoId' => $planoA->cod_plano_de_acao]);

    $componente->call('remover', $vinculoAlheio);
    $componente->call('remover', $vinculoAdminProprio);
    $componente->call('remover', $vinculoDoPlano);

    expect(DB::table(PIVOT_PERFIS)->where('id', $vinculoAlheio)->exists())->toBeTrue()
        ->and(DB::table(PIVOT_PERFIS)->where('id', $vinculoAdminProprio)->exists())->toBeTrue()
        ->and(DB::table(PIVOT_PERFIS)->where('id', $vinculoDoPlano)->exists())->toBeFalse();
});

// ── Riscos ──────────────────────────────────────────────────────────────────

test('GerenciarMitigacoes não apaga mitigação de outro risco', function () {
    [$pei, $orgA, $orgB] = cenarioDuasOrganizacoes();

    $criarRisco = fn (Organization $org, string $titulo) => Risco::create([
        'cod_pei' => $pei->cod_pei,
        'cod_organizacao' => $org->cod_organizacao,
        'dsc_titulo' => $titulo,
        'txt_descricao' => 'Risco do teste de IDOR.',
        'dsc_categoria' => 'Operacional',
        'dsc_status' => 'Identificado',
        'num_probabilidade' => 2,
        'num_impacto' => 2,
    ]);

    $riscoA = $criarRisco($orgA, 'Risco A');
    $riscoB = $criarRisco($orgB, 'Risco B');

    // As colunas foram alinhadas ao Model pela migration de 03/10/2026.
    $mitigacaoB = (string) Str::uuid();
    DB::table('risk_management.tab_risco_mitigacao')->insert([
        'cod_mitigacao' => $mitigacaoB,
        'cod_risco' => $riscoB->cod_risco,
        'dsc_tipo' => 'Prevenção',
        'txt_descricao' => 'Ação do órgão B',
        'dsc_status' => 'A Fazer',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $componente = Livewire::actingAs(adminDaUnidade($orgA))
        ->test(GerenciarMitigacoes::class, ['riscoId' => $riscoA->cod_risco]);

    chamarIgnorandoNaoEncontrado(fn () => $componente->call('delete', $mitigacaoB));

    expect(DB::table('risk_management.tab_risco_mitigacao')->where('cod_mitigacao', $mitigacaoB)->exists())->toBeTrue();
});

// ── Relatórios agendados ────────────────────────────────────────────────────

test('agendamento de relatório recusa organização fora do escopo do usuário', function () {
    [, $orgA, $orgB] = cenarioDuasOrganizacoes();

    $usuario = adminDaUnidade($orgA);

    Livewire::actingAs($usuario)->test(AgendarRelatorio::class)
        ->call('carregar', 'objetivos', ['organizacao_id' => $orgB->cod_organizacao])
        ->assertForbidden();

    // Mesmo pulando o carregar(), o salvar() revalida os filtros.
    Livewire::actingAs($usuario)->test(AgendarRelatorio::class)
        ->set('tipoRelatorio', 'objetivos')
        ->set('filtros', ['organizacao_id' => $orgB->cod_organizacao])
        ->set('dataInicio', now()->addDay()->format('Y-m-d H:i'))
        ->call('salvar')
        ->assertForbidden();

    expect(RelatorioAgendado::count())->toBe(0);

    // Controle positivo: a própria organização continua agendável.
    Livewire::actingAs($usuario)->test(AgendarRelatorio::class)
        ->call('carregar', 'objetivos', ['organizacao_id' => $orgA->cod_organizacao])
        ->call('salvar')
        ->assertHasNoErrors();

    expect(RelatorioAgendado::count())->toBe(1);
});
