<?php

/*
 * Quadro de entregas (DeliverablesBoard) pelo caminho da tela.
 *
 * Revisão adversária de 04/10/2026, confirmada no navegador: o filtro
 * "Responsável" dava 404 (uuid convertido em inteiro), o seletor da Lista
 * chamava um método inexistente, arrastar no kanban não entrava no histórico
 * (Query Builder, sem eventos do Model), o progresso do quadro divergia do
 * detalhe da iniciativa (33,3% × 75,0%) e outros 13 defeitos menores. Cada
 * teste chama o método público como o navegador chamaria e confere o banco
 * ou o HTML que a tela recebe.
 */

use App\Livewire\Deliverables\DeliverablesBoard;
use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\EntregaAnexo;
use App\Models\ActionPlan\EntregaComentario;
use App\Models\ActionPlan\EntregaHistorico;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use App\Services\IndicadorCalculoService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mockery\MockInterface;

/**
 * Unidade, ciclo do ano corrente + 3 e uma iniciativa de 01/01 do ano a 31/12 do ano seguinte.
 *
 * @return array{org: Organization, pei: PEI, objetivo: Objetivo, plano: PlanoDeAcao, admin: User, ano: int}
 */
function qeCenario(): array
{
    $ano = (int) date('Y');
    $org = Organization::create(['nom_organizacao' => 'Unidade Quadro', 'sgl_organizacao' => 'UQ']);
    $pei = PEI::create(['dsc_pei' => 'Ciclo Quadro', 'num_ano_inicio_pei' => $ano, 'num_ano_fim_pei' => $ano + 3]);
    $perspectiva = Perspectiva::create(['cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'Processos', 'num_nivel_hierarquico_apresentacao' => 1]);
    $objetivo = Objetivo::create(['cod_perspectiva' => $perspectiva->cod_perspectiva, 'nom_objetivo' => 'Objetivo Quadro', 'dsc_objetivo' => 'x', 'num_nivel_hierarquico_apresentacao' => 1]);
    $plano = qePlano($objetivo, $org, 'Iniciativa do Quadro', "{$ano}-01-01", ($ano + 1).'-12-31');

    $admin = qeUsuario(PerfilAcesso::ADMIN_UNIDADE, $org);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    return compact('org', 'pei', 'objetivo', 'plano', 'admin', 'ano');
}

function qePlano(Objetivo $objetivo, Organization $org, string $nome, string $inicio, string $fim): PlanoDeAcao
{
    $plano = PlanoDeAcao::create([
        'cod_objetivo' => $objetivo->cod_objetivo, 'cod_organizacao' => $org->cod_organizacao,
        'cod_tipo_execucao' => TipoExecucao::ACAO, 'dsc_plano_de_acao' => $nome,
        'num_nivel_hierarquico_apresentacao' => 1, 'bln_status' => 'Em Andamento',
        'dte_inicio' => $inicio, 'dte_fim' => $fim,
    ]);
    $plano->organizacoes()->sync([$org->cod_organizacao]);

    return $plano;
}

function qeUsuario(string $perfil, Organization $org): User
{
    $user = User::factory()->create(['ativo' => true]);
    $user->perfisAcesso()->attach($perfil, ['cod_organizacao' => $org->cod_organizacao]);
    $user->organizacoes()->syncWithoutDetaching([$org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');
    Session::put('organizacao_selecionada_id', $org->cod_organizacao);

    return $user;
}

function qeEntrega(PlanoDeAcao $plano, string $titulo, string $status = 'Não Iniciado', array $extra = []): Entrega
{
    return Entrega::create(array_merge([
        'cod_plano_de_acao' => $plano->cod_plano_de_acao,
        'dsc_entrega' => $titulo,
        'bln_status' => $status,
        'dsc_tipo' => 'task',
        'num_nivel_hierarquico_apresentacao' => 1,
    ], $extra));
}

function qeQuadro(User $user, PlanoDeAcao $plano)
{
    return Livewire::actingAs($user)->test(DeliverablesBoard::class, ['planoId' => $plano->cod_plano_de_acao]);
}

// ── 1. Filtro "Responsável" ──────────────────────────────────────────────────

test('o filtro Responsável usa o uuid e a relação de responsáveis (não a coluna legada)', function () {
    $c = qeCenario();
    $pessoa = qeUsuario(PerfilAcesso::GESTOR_RESPONSAVEL, $c['org']);

    $daPessoa = qeEntrega($c['plano'], 'Entrega atribuída pela pivot');
    $daPessoa->responsaveis()->sync([$pessoa->id]);
    qeEntrega($c['plano'], 'Entrega só com a coluna legada', 'Não Iniciado', ['cod_responsavel' => $pessoa->id]);

    qeQuadro($c['admin'], $c['plano'])
        ->set('filtroResponsavel', $pessoa->id)
        ->assertOk()
        ->assertSee('Entrega atribuída pela pivot')
        ->assertDontSee('Entrega só com a coluna legada');
});

test('filtro Responsável inválido (vindo da URL) não derruba a página', function () {
    $c = qeCenario();
    qeEntrega($c['plano'], 'Entrega visível');

    Livewire::withQueryParams(['filtroResponsavel' => '12345'])
        ->actingAs($c['admin'])
        ->test(DeliverablesBoard::class, ['planoId' => $c['plano']->cod_plano_de_acao])
        ->assertOk()
        ->assertSee('Entrega visível')
        ->assertSet('filtroResponsavel', '');
});

// ── 2. Seletor "Responsável" da Lista ───────────────────────────────────────

test('o seletor Responsável da Lista chama o método que existe e grava na relação', function () {
    $c = qeCenario();
    $pessoa = qeUsuario(PerfilAcesso::GESTOR_RESPONSAVEL, $c['org']);
    $entrega = qeEntrega($c['plano'], 'Entrega da lista');

    qeQuadro($c['admin'], $c['plano'])
        ->call('setView', 'lista')
        ->assertSeeHtml("atualizarResponsaveis('{$entrega->cod_entrega}'")
        ->assertDontSeeHtml('atualizarResponsavel(')
        ->call('atualizarResponsaveis', $entrega->cod_entrega, [$pessoa->id])
        ->assertOk();

    expect($entrega->responsaveis()->pluck('users.id')->all())->toBe([$pessoa->id]);
});

// ── 3. Escritas do quadro passam pelo Model ──────────────────────────────────

test('mover no kanban e as edições inline entram no histórico da entrega', function () {
    $c = qeCenario();
    $entrega = qeEntrega($c['plano'], 'Entrega com histórico');
    $id = $entrega->cod_entrega;

    qeQuadro($c['admin'], $c['plano'])
        ->call('moverParaStatus', $id, 'Concluído', [$id])
        ->call('atualizarPrioridade', $id, 'alta')
        ->call('atualizarPrazo', $id, $c['ano'].'-11-30')
        ->call('atualizarTitulo', $id, 'Título novo')
        ->call('arquivar', $id)
        ->call('desarquivar', $id)
        ->assertOk();

    $campos = EntregaHistorico::where('cod_entrega', $id)->where('dsc_acao', 'updated')->pluck('dsc_campo')->all();

    expect($campos)->toContain('bln_status', 'cod_prioridade', 'dte_prazo', 'dsc_entrega', 'bln_arquivado');
});

test('excluir e restaurar pelo quadro entram no histórico', function () {
    $c = qeCenario();
    $entrega = qeEntrega($c['plano'], 'Entrega da lixeira');

    qeQuadro($c['admin'], $c['plano'])
        ->call('confirmDeleteEntrega', $entrega->cod_entrega)
        ->call('excluir')
        ->call('restaurar', $entrega->cod_entrega)
        ->assertOk();

    $acoes = EntregaHistorico::where('cod_entrega', $entrega->cod_entrega)->pluck('dsc_acao')->all();

    expect($acoes)->toContain('deleted', 'restored')
        ->and($entrega->fresh()->trashed())->toBeFalse();
});

test('mudar o status pelo quadro recalcula os indicadores da iniciativa (EntregaObserver)', function () {
    $c = qeCenario();
    $entrega = qeEntrega($c['plano'], 'Entrega que move indicador');
    $quadro = qeQuadro($c['admin'], $c['plano']);

    $this->partialMock(IndicadorCalculoService::class, function (MockInterface $mock) {
        $mock->shouldReceive('atualizarIndicadoresDoPlano')->atLeast()->once()->andReturn(0);
    });

    $quadro->call('atualizarStatus', $entrega->cod_entrega, 'Concluído')->assertOk();
});

// ── 4. Uma só regra de progresso ─────────────────────────────────────────────

test('o progresso do quadro é o mesmo do detalhe da iniciativa', function () {
    $c = qeCenario();
    qeEntrega($c['plano'], 'A', 'Concluído');
    qeEntrega($c['plano'], 'B', 'Em Andamento');
    qeEntrega($c['plano'], 'C', 'Cancelado');
    qeEntrega($c['plano'], 'D', 'Não Iniciado');

    $doDetalhe = app(IndicadorCalculoService::class)->calcularProgressoPlano($c['plano']);

    // Cancelado fora do denominador, Em Andamento vale 0,5: (1 + 0,5 + 0) / 3.
    expect($doDetalhe)->toBe(50.0)
        ->and(qeQuadro($c['admin'], $c['plano'])->get('progresso'))->toBe(50.0);
});

// ── 8. Excluir definitivo apaga o arquivo do anexo ───────────────────────────

test('excluir em definitivo apaga também o arquivo dos anexos', function () {
    Storage::fake(EntregaAnexo::DISCO);
    $c = qeCenario();
    $entrega = qeEntrega($c['plano'], 'Entrega com anexo');
    Storage::disk(EntregaAnexo::DISCO)->put('entregas/anexos/contrato.pdf', 'conteudo');
    EntregaAnexo::create([
        'cod_entrega' => $entrega->cod_entrega, 'cod_usuario' => $c['admin']->id,
        'dsc_nome_arquivo' => 'contrato.pdf', 'dsc_caminho' => 'entregas/anexos/contrato.pdf',
        'dsc_mime_type' => 'application/pdf', 'num_tamanho_bytes' => 8,
    ]);
    $entrega->delete();

    qeQuadro($c['admin'], $c['plano'])
        ->call('confirmDeleteEntrega', $entrega->cod_entrega, true)
        ->call('excluir')
        ->assertOk();

    expect(Entrega::withTrashed()->find($entrega->cod_entrega))->toBeNull();
    Storage::disk(EntregaAnexo::DISCO)->assertMissing('entregas/anexos/contrato.pdf');
});

// ── 9. Lixeira não promete prazo que não existe ──────────────────────────────

test('item excluído há mais de 24h continua na lixeira e restaurável; a tela não promete prazo', function () {
    $c = qeCenario();
    $entrega = qeEntrega($c['plano'], 'Excluída há três dias');
    $entrega->delete();
    Entrega::withTrashed()->whereKey($entrega->cod_entrega)->update(['deleted_at' => now()->subDays(3)]);

    qeQuadro($c['admin'], $c['plano'])
        ->call('toggleLixeira')
        ->assertSee('Excluída há três dias')
        ->assertDontSee('24 horas')
        ->call('restaurar', $entrega->cod_entrega)
        ->assertOk();

    expect($entrega->fresh()->trashed())->toBeFalse();
});

// ── 11. Prazo dentro do período da iniciativa ────────────────────────────────

test('prazo fora do período da iniciativa é recusado em todas as vias', function () {
    $c = qeCenario();
    $entrega = qeEntrega($c['plano'], 'Entrega com prazo', 'Não Iniciado', ['dte_prazo' => $c['ano'].'-06-30']);

    qeQuadro($c['admin'], $c['plano'])
        ->set('editTitulo', 'Entrega de 2035')
        ->set('editPrazo', '2035-01-01')
        ->call('salvarEntrega')
        ->assertHasErrors(['editPrazo'])
        ->call('atualizarPrazo', $entrega->cod_entrega, '2035-01-01')
        ->call('openQuickAdd', 'Não Iniciado', '2035-01-01')
        ->set('quickAddTitulo', 'Rápida de 2035')
        ->call('criarRapido')
        ->assertHasErrors(['quickAddPrazo']);

    expect($entrega->fresh()->dte_prazo->format('Y-m-d'))->toBe($c['ano'].'-06-30')
        ->and(Entrega::where('dsc_entrega', 'Entrega de 2035')->exists())->toBeFalse()
        ->and(Entrega::where('dsc_entrega', 'Rápida de 2035')->exists())->toBeFalse();

    qeQuadro($c['admin'], $c['plano'])
        ->call('atualizarPrazo', $entrega->cod_entrega, ($c['ano'] + 1).'-03-15')
        ->assertOk();

    expect($entrega->fresh()->dte_prazo->format('Y-m-d'))->toBe(($c['ano'] + 1).'-03-15');
});

// ── 12. Atrasada: um só critério ─────────────────────────────────────────────

test('atrasada só com prazo antes de hoje e status em aberto', function () {
    $c = qeCenario();
    $hoje = qeEntrega($c['plano'], 'Prazo hoje', 'Não Iniciado', ['dte_prazo' => now()->toDateString()]);
    $cancelada = qeEntrega($c['plano'], 'Cancelada ontem', 'Cancelado', ['dte_prazo' => now()->subDay()->toDateString()]);
    $suspensa = qeEntrega($c['plano'], 'Suspensa ontem', 'Suspenso', ['dte_prazo' => now()->subDay()->toDateString()]);
    $atrasada = qeEntrega($c['plano'], 'Em andamento ontem', 'Em Andamento', ['dte_prazo' => now()->subDay()->toDateString()]);

    expect($hoje->fresh()->isAtrasada())->toBeFalse()
        ->and($cancelada->fresh()->isAtrasada())->toBeFalse()
        ->and($suspensa->fresh()->isAtrasada())->toBeFalse()
        ->and($atrasada->fresh()->isAtrasada())->toBeTrue()
        ->and(Entrega::atrasadas()->pluck('dsc_entrega')->all())->toBe(['Em andamento ontem']);
});

// ── 13. Comentário com respostas ─────────────────────────────────────────────

test('excluir comentário com respostas não some com as respostas; sem respostas, exclui', function () {
    $c = qeCenario();
    $outro = qeUsuario(PerfilAcesso::GESTOR_RESPONSAVEL, $c['org']);
    $entrega = qeEntrega($c['plano'], 'Entrega comentada');

    $pai = EntregaComentario::create(['cod_entrega' => $entrega->cod_entrega, 'cod_usuario' => $c['admin']->id, 'dsc_comentario' => 'Pai']);
    $resposta = EntregaComentario::create(['cod_entrega' => $entrega->cod_entrega, 'cod_usuario' => $outro->id, 'dsc_comentario' => 'Resposta', 'cod_comentario_pai' => $pai->cod_comentario]);
    $sozinho = EntregaComentario::create(['cod_entrega' => $entrega->cod_entrega, 'cod_usuario' => $c['admin']->id, 'dsc_comentario' => 'Sozinho']);

    qeQuadro($c['admin'], $c['plano'])
        ->call('excluirComentario', $pai->cod_comentario)
        ->call('excluirComentario', $sozinho->cod_comentario)
        ->assertOk();

    expect(EntregaComentario::find($pai->cod_comentario))->not->toBeNull()
        ->and(EntregaComentario::find($resposta->cod_comentario))->not->toBeNull()
        ->and(EntregaComentario::find($sozinho->cod_comentario))->toBeNull();
});

test('o botão de excluir comentário pede confirmação', function () {
    $c = qeCenario();
    $entrega = qeEntrega($c['plano'], 'Entrega comentada');
    $comentario = EntregaComentario::create(['cod_entrega' => $entrega->cod_entrega, 'cod_usuario' => $c['admin']->id, 'dsc_comentario' => 'Meu comentário']);

    $html = qeQuadro($c['admin'], $c['plano'])->call('openDetails', $entrega->cod_entrega)->html();

    expect($html)->toMatch('/wire:click="excluirComentario\(\''.$comentario->cod_comentario.'\'\)"\s+wire:confirm=/');
});

// ── 15. Limpar o 5W2H apaga ──────────────────────────────────────────────────

test('limpar os campos do 5W2H e salvar apaga o 5W2H', function () {
    $c = qeCenario();
    $entrega = qeEntrega($c['plano'], 'Entrega com 5W2H', 'Não Iniciado', ['json_propriedades' => ['5w2h' => ['what' => 'Algo', 'why' => 'Motivo']]]);

    qeQuadro($c['admin'], $c['plano'])
        ->call('openEditModal', $entrega->cod_entrega)
        ->set('edit5w2h', ['what' => '', 'why' => '', 'who' => '', 'where' => '', 'when' => '', 'how' => '', 'howmuch' => ''])
        ->call('salvarEntrega')
        ->assertHasNoErrors();

    expect($entrega->fresh()->json_propriedades['5w2h'] ?? null)->toBeNull();
});

// ── 16. Detalhe aberto pela lixeira ──────────────────────────────────────────

test('o detalhe aberto pela lixeira só oferece Restaurar e Excluir definitivo', function () {
    $c = qeCenario();
    $entrega = qeEntrega($c['plano'], 'Entrega na lixeira');
    $entrega->delete();
    $id = $entrega->cod_entrega;

    qeQuadro($c['admin'], $c['plano'])
        ->call('toggleLixeira')
        ->call('openDetails', $id)
        ->assertDontSeeHtml("openEditModal('{$id}')")
        ->assertDontSeeHtml("arquivar('{$id}')")
        ->assertDontSeeHtml("atualizarStatus('{$id}'")
        ->assertDontSeeHtml("atualizarPrazo('{$id}'")
        ->assertDontSeeHtml("confirmDeleteEntrega('{$id}')")
        ->assertSeeHtml("restaurar('{$id}')")
        ->assertSeeHtml("confirmDeleteEntrega('{$id}', true)");
});

// ── 17. Calendário ───────────────────────────────────────────────────────────

test('clicar no dia do calendário cria a entrega com aquele prazo; "+N mais" não dispara evento sem ouvinte', function () {
    $c = qeCenario();
    $dia = $c['ano'].'-10-20';

    qeQuadro($c['admin'], $c['plano'])
        ->call('setView', 'calendario')
        ->assertSeeHtml("openQuickAdd('Não Iniciado', '")
        ->assertDontSeeHtml('show-day-entregas')
        ->call('openQuickAdd', 'Não Iniciado', $dia)
        ->set('quickAddTitulo', 'Entrega do dia 20')
        ->call('criarRapido')
        ->assertHasNoErrors();

    expect(Entrega::where('dsc_entrega', 'Entrega do dia 20')->first()->dte_prazo->format('Y-m-d'))->toBe($dia);
});

// ── 18. Posição no kanban ────────────────────────────────────────────────────

test('a posição em que a entrega é solta no kanban se mantém', function () {
    $c = qeCenario();
    $c1 = qeEntrega($c['plano'], 'Card C', 'Não Iniciado', ['num_ordem' => 1]);
    $a = qeEntrega($c['plano'], 'Card A', 'Em Andamento', ['num_ordem' => 4]);
    $b = qeEntrega($c['plano'], 'Card B', 'Em Andamento', ['num_ordem' => 5]);

    $quadro = qeQuadro($c['admin'], $c['plano'])
        ->call('moverParaStatus', $c1->cod_entrega, 'Em Andamento', [$a->cod_entrega, $c1->cod_entrega, $b->cod_entrega])
        ->assertOk();

    $coluna = $quadro->viewData('entregasPorStatus')['Em Andamento']->pluck('dsc_entrega')->all();

    expect($coluna)->toBe(['Card A', 'Card C', 'Card B']);
});

// ── 19. Perfil Consulta não vê controles de escrita ──────────────────────────

test('o perfil Consulta não recebe controles de escrita na Lista nem no kanban', function () {
    $c = qeCenario();
    $consulta = qeUsuario(PerfilAcesso::CONSULTA, $c['org']);
    $entrega = qeEntrega($c['plano'], 'Entrega somente leitura');
    $id = $entrega->cod_entrega;

    qeQuadro($consulta, $c['plano'])
        ->assertSee('Entrega somente leitura')
        ->assertSeeHtml('data-pode-editar="0"')
        ->call('setView', 'lista')
        ->assertSee('Entrega somente leitura')
        ->assertDontSeeHtml("atualizarStatus('{$id}'")
        ->assertDontSeeHtml("atualizarPrioridade('{$id}'")
        ->assertDontSeeHtml("atualizarPrazo('{$id}'")
        ->assertDontSeeHtml("atualizarResponsaveis('{$id}'")
        ->assertDontSeeHtml("arquivar('{$id}')")
        ->assertDontSeeHtml("openEditModal('{$id}')")
        ->assertDontSeeHtml('notion-drag-handle')
        ->call('setView', 'timeline')
        ->assertOk()
        ->assertSee('Entrega somente leitura');
});

// ── 21. Seletor de iniciativa só do ciclo selecionado ────────────────────────

test('o seletor de iniciativa do quadro lista só as iniciativas do ciclo selecionado', function () {
    $c = qeCenario();
    $outroPei = PEI::create(['dsc_pei' => 'Outro ciclo', 'num_ano_inicio_pei' => $c['ano'], 'num_ano_fim_pei' => $c['ano'] + 3]);
    $perspectiva = Perspectiva::create(['cod_pei' => $outroPei->cod_pei, 'dsc_perspectiva' => 'P2', 'num_nivel_hierarquico_apresentacao' => 1]);
    $objetivo = Objetivo::create(['cod_perspectiva' => $perspectiva->cod_perspectiva, 'nom_objetivo' => 'O2', 'dsc_objetivo' => 'x', 'num_nivel_hierarquico_apresentacao' => 1]);
    qePlano($objetivo, $c['org'], 'Iniciativa de outro ciclo', $c['ano'].'-01-01', $c['ano'].'-12-31');

    // "Todas Perspectivas": sem objetivo escolhido, o seletor lista a unidade inteira.
    $nomes = collect(qeQuadro($c['admin'], $c['plano'])->set('perspectivaId', '')->get('planosDisponiveis'))
        ->pluck('dsc_plano_de_acao')->all();

    expect($nomes)->toContain('Iniciativa do Quadro')
        ->not->toContain('Iniciativa de outro ciclo');
});
