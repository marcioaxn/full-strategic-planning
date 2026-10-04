<?php

/*
 * Iniciativas (ListarPlanos), Gestores e Responsáveis (AtribuirResponsaveis) e
 * Minhas Entregas, pelo caminho da tela.
 *
 * Revisão adversária de 04/10/2026: a iniciativa plurianual sumia no filtro de
 * ano (2026–2028 não aparecia em 2027), texto longo dava 500, o modal de
 * exclusão prometia o que não fazia, a unidade principal dependia da ordem dos
 * cliques, exclusões sem confirmação e "Concluído" em Minhas Entregas vinha
 * sempre vazio.
 */

use App\Livewire\ActionPlan\AtribuirResponsaveis;
use App\Livewire\ActionPlan\ListarPlanos;
use App\Livewire\Deliverables\MinhasEntregas;
use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\PlanoComunicacao;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\Raci;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

/** @return array{org: Organization, pei: PEI, objetivo: Objetivo, ano: int} */
function ipCenario(): array
{
    $ano = (int) date('Y');
    $org = Organization::create(['nom_organizacao' => 'Unidade Iniciativas', 'sgl_organizacao' => 'UI']);
    $pei = PEI::create(['dsc_pei' => 'Ciclo Iniciativas', 'num_ano_inicio_pei' => $ano - 2, 'num_ano_fim_pei' => $ano + 4]);
    $perspectiva = Perspectiva::create(['cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'Processos', 'num_nivel_hierarquico_apresentacao' => 1]);
    $objetivo = Objetivo::create(['cod_perspectiva' => $perspectiva->cod_perspectiva, 'nom_objetivo' => 'Objetivo Iniciativas', 'dsc_objetivo' => 'x', 'num_nivel_hierarquico_apresentacao' => 1]);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    return compact('org', 'pei', 'objetivo', 'ano');
}

function ipPlano(Objetivo $objetivo, Organization $org, string $nome, string $inicio, string $fim, array $orgs = []): PlanoDeAcao
{
    $plano = PlanoDeAcao::create([
        'cod_objetivo' => $objetivo->cod_objetivo, 'cod_organizacao' => $org->cod_organizacao,
        'cod_tipo_execucao' => TipoExecucao::ACAO, 'dsc_plano_de_acao' => $nome,
        'num_nivel_hierarquico_apresentacao' => 1, 'bln_status' => 'Em Andamento',
        'dte_inicio' => $inicio, 'dte_fim' => $fim,
    ]);
    $plano->organizacoes()->sync($orgs ?: [$org->cod_organizacao]);

    return $plano;
}

function ipUsuario(string $perfil, Organization ...$orgs): User
{
    $user = User::factory()->create(['ativo' => true]);
    foreach ($orgs as $org) {
        $user->perfisAcesso()->attach($perfil, ['cod_organizacao' => $org->cod_organizacao]);
        $user->organizacoes()->syncWithoutDetaching([$org->cod_organizacao]);
    }
    $user->unsetRelation('perfisAcesso');
    Session::put('organizacao_selecionada_id', $orgs[0]->cod_organizacao);

    return $user;
}

// ── 5. Filtro de ano = iniciativa vigente no ano ─────────────────────────────

test('o filtro de ano mostra a iniciativa vigente no ano, inclusive a plurianual', function () {
    $c = ipCenario();
    $admin = ipUsuario(PerfilAcesso::ADMIN_UNIDADE, $c['org']);
    ipPlano($c['objetivo'], $c['org'], 'Plurianual atravessa o ano', ($c['ano'] - 1).'-03-01', ($c['ano'] + 1).'-06-30');
    ipPlano($c['objetivo'], $c['org'], 'Encerrada antes do ano', ($c['ano'] - 2).'-01-01', ($c['ano'] - 1).'-12-31');

    Livewire::actingAs($admin)->test(ListarPlanos::class)
        ->call('atualizarAno', $c['ano'])
        ->assertSee('Plurianual atravessa o ano')
        ->assertDontSee('Encerrada antes do ano');
});

// ── 7. Texto longo → mensagem, não 500 ───────────────────────────────────────

test('PPA e LOA longos demais voltam como mensagem, não como erro 500', function () {
    $c = ipCenario();
    $admin = ipUsuario(PerfilAcesso::ADMIN_UNIDADE, $c['org']);

    Livewire::actingAs($admin)->test(ListarPlanos::class)
        ->call('create')
        ->set('dsc_plano_de_acao', 'Iniciativa com PPA longo')
        ->set('cod_objetivo', $c['objetivo']->cod_objetivo)
        ->set('cod_tipo_execucao', TipoExecucao::ACAO)
        ->set('dte_inicio', $c['ano'].'-01-01')
        ->set('dte_fim', $c['ano'].'-12-31')
        ->set('cod_ppa', str_repeat('P', 192))
        ->set('cod_loa', str_repeat('L', 192))
        ->set('organizacoes_ids', [$c['org']->cod_organizacao])
        ->call('save')
        ->assertHasErrors(['cod_ppa', 'cod_loa']);

    expect(PlanoDeAcao::where('dsc_plano_de_acao', 'Iniciativa com PPA longo')->exists())->toBeFalse();
});

test('responsável do plano de comunicação longo demais volta como mensagem', function () {
    $c = ipCenario();
    $admin = ipUsuario(PerfilAcesso::ADMIN_UNIDADE, $c['org']);
    $plano = ipPlano($c['objetivo'], $c['org'], 'Iniciativa comunicação', $c['ano'].'-01-01', $c['ano'].'-12-31');

    Livewire::actingAs($admin)->test(AtribuirResponsaveis::class, ['planoId' => $plano->cod_plano_de_acao])
        ->call('novaComunicacao')
        ->set('formComun.nom_publico_alvo', 'Equipe')
        ->set('formComun.dsc_mensagem_chave', 'Mensagem')
        ->set('formComun.nom_responsavel', str_repeat('R', 120))
        ->call('salvarComunicacao')
        ->assertHasErrors(['formComun.nom_responsavel']);

    expect(PlanoComunicacao::count())->toBe(0);
});

// ── 10. Modal de exclusão honesto ────────────────────────────────────────────

test('o modal de excluir iniciativa diz o que vai junto e não promete exclusão permanente', function () {
    $c = ipCenario();
    $admin = ipUsuario(PerfilAcesso::ADMIN_UNIDADE, $c['org']);
    $plano = ipPlano($c['objetivo'], $c['org'], 'Iniciativa a excluir', $c['ano'].'-01-01', $c['ano'].'-12-31');
    Entrega::create(['cod_plano_de_acao' => $plano->cod_plano_de_acao, 'dsc_entrega' => 'E', 'bln_status' => 'Não Iniciado', 'num_nivel_hierarquico_apresentacao' => 1]);
    Indicador::create([
        'cod_plano_de_acao' => $plano->cod_plano_de_acao, 'nom_indicador' => 'I', 'dsc_indicador' => 'I',
        'dsc_unidade_medida' => 'Unidade', 'dsc_tipo' => 'Efetividade', 'bln_acumulado' => false, 'dsc_periodo_medicao' => 'mensal',
    ]);
    $gestor = User::factory()->create();
    $gestor->perfisAcesso()->attach(PerfilAcesso::GESTOR_RESPONSAVEL, ['cod_organizacao' => $c['org']->cod_organizacao, 'cod_plano_de_acao' => $plano->cod_plano_de_acao]);

    Livewire::actingAs($admin)->test(ListarPlanos::class)
        ->call('confirmDelete', $plano->cod_plano_de_acao)
        ->assertSee('1 entrega(s)')
        ->assertSee('1 indicador(es)')
        ->assertSee('1 vínculo(s) de Gestor')
        ->assertDontSee('irreversível')
        ->assertDontSee('removidos permanentemente');
});

// ── 20. Unidade principal não depende da ordem dos cliques ──────────────────

test('editar a lista de unidades não troca a unidade principal já gravada', function () {
    $c = ipCenario();
    $outra = Organization::create(['nom_organizacao' => 'Unidade B', 'sgl_organizacao' => 'UB']);
    $admin = ipUsuario(PerfilAcesso::ADMIN_UNIDADE, $c['org'], $outra);
    $plano = ipPlano($c['objetivo'], $c['org'], 'Iniciativa em duas unidades', $c['ano'].'-01-01', $c['ano'].'-12-31', [$c['org']->cod_organizacao, $outra->cod_organizacao]);

    Livewire::actingAs($admin)->test(ListarPlanos::class)
        ->call('edit', $plano->cod_plano_de_acao)
        ->set('organizacoes_ids', [$outra->cod_organizacao, $c['org']->cod_organizacao])
        ->call('save')
        ->assertHasNoErrors();

    expect($plano->fresh()->cod_organizacao)->toBe($c['org']->cod_organizacao);
});

test('na iniciativa nova, a unidade principal é a selecionada no topo quando marcada', function () {
    $c = ipCenario();
    $outra = Organization::create(['nom_organizacao' => 'Unidade B', 'sgl_organizacao' => 'UB']);
    $admin = ipUsuario(PerfilAcesso::ADMIN_UNIDADE, $outra, $c['org']);

    Livewire::actingAs($admin)->test(ListarPlanos::class)
        ->call('create')
        ->set('dsc_plano_de_acao', 'Iniciativa nova em duas unidades')
        ->set('cod_objetivo', $c['objetivo']->cod_objetivo)
        ->set('cod_tipo_execucao', TipoExecucao::ACAO)
        ->set('dte_inicio', $c['ano'].'-01-01')
        ->set('dte_fim', $c['ano'].'-12-31')
        ->set('organizacoes_ids', [$c['org']->cod_organizacao, $outra->cod_organizacao])
        ->call('save')
        ->assertHasNoErrors();

    expect(PlanoDeAcao::where('dsc_plano_de_acao', 'Iniciativa nova em duas unidades')->value('cod_organizacao'))
        ->toBe($outra->cod_organizacao);
});

// ── 14. Exclusões pedem confirmação ──────────────────────────────────────────

test('remover gestor, excluir RACI e excluir item de comunicação pedem confirmação', function () {
    $c = ipCenario();
    $admin = ipUsuario(PerfilAcesso::ADMIN_UNIDADE, $c['org']);
    $plano = ipPlano($c['objetivo'], $c['org'], 'Iniciativa com equipe', $c['ano'].'-01-01', $c['ano'].'-12-31');
    $pessoa = User::factory()->create(['ativo' => true]);
    $pessoa->organizacoes()->attach($c['org']->cod_organizacao);
    $pessoa->perfisAcesso()->attach(PerfilAcesso::GESTOR_RESPONSAVEL, ['cod_organizacao' => $c['org']->cod_organizacao, 'cod_plano_de_acao' => $plano->cod_plano_de_acao]);
    Raci::create(['cod_plano_de_acao' => $plano->cod_plano_de_acao, 'user_id' => $pessoa->id, 'dsc_papel' => 'R']);
    PlanoComunicacao::create(['cod_plano_de_acao' => $plano->cod_plano_de_acao, 'nom_publico_alvo' => 'Equipe', 'dsc_mensagem_chave' => 'M', 'dsc_canal' => 'E-mail', 'dsc_frequencia' => 'Mensal']);

    $html = Livewire::actingAs($admin)->test(AtribuirResponsaveis::class, ['planoId' => $plano->cod_plano_de_acao])->html();

    expect($html)->toMatch('/wire:click="remover\(\'[^\']+\'\)"\s+wire:confirm=/')
        ->toMatch('/wire:click="excluirRaci\(\'[^\']+\'\)"\s+wire:confirm=/')
        ->toMatch('/wire:click="excluirComunicacao\(\'[^\']+\'\)"\s+wire:confirm=/');
});

// ── 22 e 12. Minhas Entregas ─────────────────────────────────────────────────

test('Minhas Entregas: o filtro Concluído mostra as concluídas e atrasada segue o critério único', function () {
    $c = ipCenario();
    $eu = ipUsuario(PerfilAcesso::GESTOR_RESPONSAVEL, $c['org']);
    $plano = ipPlano($c['objetivo'], $c['org'], 'Iniciativa minhas', $c['ano'].'-01-01', ($c['ano'] + 1).'-12-31');

    $criar = function (string $titulo, string $status, string $prazo) use ($plano, $eu) {
        $e = Entrega::create(['cod_plano_de_acao' => $plano->cod_plano_de_acao, 'dsc_entrega' => $titulo, 'bln_status' => $status, 'dte_prazo' => $prazo, 'num_nivel_hierarquico_apresentacao' => 1]);
        $e->responsaveis()->sync([$eu->id]);
    };
    $criar('Minha concluída', 'Concluído', now()->subDays(5)->toDateString());
    $criar('Minha de hoje', 'Não Iniciado', now()->toDateString());
    $criar('Minha cancelada', 'Cancelado', now()->subDay()->toDateString());
    $criar('Minha suspensa', 'Suspenso', now()->subDay()->toDateString());
    $criar('Minha atrasada', 'Em Andamento', now()->subDay()->toDateString());

    $tela = Livewire::actingAs($eu)->test(MinhasEntregas::class)
        ->assertDontSee('Minha concluída')
        ->assertViewHas('totalAtrasadas', 1);

    $tela->set('filtroStatus', 'Concluído')
        ->assertSee('Minha concluída')
        ->assertDontSee('Minha de hoje');
});
