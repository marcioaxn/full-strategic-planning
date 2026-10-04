<?php

/*
 * Revisão de 04/10/2026 dos Relatórios (achados 4 a 10 e 14 a 17).
 *
 * Decisão do gestor: o ciclo de TODO relatório é o selecionado no topo
 * (PEI::doContexto()); o agendamento grava o ciclo e a unidade escolhidos e o
 * processamento usa os gravados.
 *
 * Cenário com dois ciclos sobrepostos em 2026 — o estado real do banco de dev
 * ("PEI MIDR 2023-2027" e "[TESTE] Ciclo 2026-2030"). As asserções são sobre
 * os DADOS ENTREGUES à view do PDF (capturados no render), não sobre o service
 * isolado: a requisição entra pela rota que a tela usa.
 */

use App\Exports\PlanosExport;
use App\Livewire\Reports\AgendarRelatorio;
use App\Livewire\Reports\ListarRelatorios;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\Reports\RelatorioAgendado;
use App\Models\Reports\RelatorioGerado;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;

/** Guarda os dados com que a view do PDF foi renderizada. */
function capturarView(string $nome): ArrayObject
{
    $dados = new ArrayObject;
    View::composer($nome, function ($view) use ($dados) {
        $dados->exchangeArray($view->getData());
    });

    return $dados;
}

function cenarioDoisCiclos(): array
{
    Storage::fake('relatorios');

    $org = Organization::create(['nom_organizacao' => 'Órgão dos Relatórios', 'sgl_organizacao' => 'ORL', 'rel_cod_organizacao' => null]);
    $outra = Organization::create(['nom_organizacao' => 'Outra Unidade', 'sgl_organizacao' => 'OUT', 'rel_cod_organizacao' => null]);

    $cicloA = PEI::create(['dsc_pei' => 'Ciclo A 2024-2027', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027]);
    $cicloB = PEI::create(['dsc_pei' => 'Ciclo B 2026-2030', 'num_ano_inicio_pei' => 2026, 'num_ano_fim_pei' => 2030]);

    $perspectiva = fn (PEI $pei, string $nome, int $nivel) => Perspectiva::create([
        'cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => $nome, 'num_nivel_hierarquico_apresentacao' => $nivel,
    ]);
    $objetivo = fn (Perspectiva $p, string $nome) => Objetivo::create([
        'cod_perspectiva' => $p->cod_perspectiva, 'nom_objetivo' => $nome, 'dsc_objetivo' => $nome,
        'num_nivel_hierarquico_apresentacao' => 1, 'num_nivel_desdobramento' => 1,
    ]);

    $pA = $perspectiva($cicloA, 'Perspectiva do A', 1);
    $pB = $perspectiva($cicloB, 'Sociedade do B', 2);
    $pB2 = $perspectiva($cicloB, 'Processos do B', 1);
    $oA = $objetivo($pA, 'Objetivo do A');
    $oB = $objetivo($pB, 'Objetivo do B');
    $objetivo($pB2, 'Objetivo de Processos do B');

    $plano = fn (Objetivo $o, string $nome, string $inicio, string $fim) => PlanoDeAcao::create([
        'cod_objetivo' => $o->cod_objetivo, 'cod_organizacao' => $org->cod_organizacao,
        'cod_tipo_execucao' => TipoExecucao::ACAO, 'dsc_plano_de_acao' => $nome,
        'num_nivel_hierarquico_apresentacao' => 1, 'bln_status' => 'Em Andamento',
        'dte_inicio' => $inicio, 'dte_fim' => $fim,
    ]);
    // Vigente em 2026 sem começar nem terminar em 2026: o Excel a perdia.
    $plano($oB, 'Iniciativa longa do B', '2024-03-01', '2028-06-30');
    // Mesmo ano, mas do ciclo A: não pode aparecer com o B selecionado.
    $plano($oA, 'Iniciativa do A', '2026-01-01', '2026-12-31');

    $indicador = function (Objetivo $o, string $nome, Organization $unidade) {
        $ind = Indicador::create([
            'cod_objetivo' => $o->cod_objetivo, 'nom_indicador' => $nome, 'dsc_indicador' => $nome,
            'dsc_unidade_medida' => 'Unidade', 'dsc_tipo' => 'Efetividade',
            'bln_acumulado' => false, 'dsc_periodo_medicao' => 'mensal',
        ]);
        $ind->organizacoes()->attach($unidade->cod_organizacao);

        return $ind;
    };
    $indicador($oB, 'Indicador da unidade', $org);
    $indicador($oB, 'Indicador de outra unidade', $outra);

    // Missão e visão só no ciclo A.
    DB::table('strategic_planning.tab_missao_visao_valores')->insert([
        'cod_missao_visao_valores' => (string) Str::uuid(),
        'dsc_missao' => 'Missão do ciclo A', 'dsc_visao' => 'Visão do ciclo A',
        'cod_pei' => $cicloA->cod_pei, 'cod_organizacao' => $org->cod_organizacao,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $user = User::factory()->create(['trocarsenha' => 0]);
    $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
    $user->organizacoes()->sync([$org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);
    Session::put('pei_selecionado_id', $cicloB->cod_pei);
    Session::put('ano_selecionado', 2026);

    return compact('org', 'outra', 'cicloA', 'cicloB', 'pB', 'pB2', 'user');
}

// ------------------------------------------------------- 4 · Relatório de Gestão

test('o Relatório de Gestão sai do ciclo selecionado no topo, não de um ciclo qualquer do ano', function () {
    $c = cenarioDoisCiclos();
    $dados = capturarView('relatorios.gestao.relatorio');

    $this->actingAs($c['user'])->get(route('relatorios.gestao.pdf', [
        'organizacao_id' => $c['org']->cod_organizacao, 'ano' => 2026, 'variante' => 'autoral',
    ]))->assertOk()->streamedContent();

    expect($dados['dados']['capa']['ciclo'])->toBe('Ciclo B 2026-2030');
});

test('Relatório de Gestão de exercício fora do ciclo selecionado é recusado com explicação, sem gerar arquivo', function () {
    $c = cenarioDoisCiclos();

    $this->actingAs($c['user'])
        ->from(route('relatorios.index'))
        ->get(route('relatorios.gestao.pdf', ['organizacao_id' => $c['org']->cod_organizacao, 'ano' => 2032]))
        ->assertRedirect(route('relatorios.index'))
        ->assertSessionHas('flash.banner');

    expect(RelatorioGerado::count())->toBe(0);
});

// --------------------------------------------- 6 · 7 · 8 · 9 · Executivo

test('o Executivo respeita perspectiva, ciclo e unidade, e não chama a IA com "Incluir IA" desligado', function () {
    $c = cenarioDoisCiclos();
    SystemSetting::setValue('ai_enabled', true);
    SystemSetting::setValue('ai_provider', 'gemini-studio');
    SystemSetting::setValue('ai_api_key', 'chave-de-teste');
    Http::fake();
    $dados = capturarView('relatorios.executivo');

    $this->actingAs($c['user'])->get(route('relatorios.executivo', [
        'organizacaoId' => $c['org']->cod_organizacao, 'ano' => 2026, 'periodo' => 'anual',
        'perspectiva' => $c['pB']->cod_perspectiva, 'include_ai' => '0',
    ]))->assertOk()->streamedContent();

    Http::assertNothingSent();

    $perspectivas = collect($dados['perspectivas']);
    $indicadores = $perspectivas->flatMap(fn ($p) => $p->objetivos)->flatMap(fn ($o) => $o->indicadores)->pluck('nom_indicador');

    expect($perspectivas->pluck('dsc_perspectiva')->all())->toBe(['Sociedade do B'])
        ->and($indicadores->all())->toBe(['Indicador da unidade'])
        ->and(collect($dados['planos'])->pluck('dsc_plano_de_acao')->all())->toBe(['Iniciativa longa do B'])
        // A missão do ciclo A não aparece sob o ciclo B.
        ->and($dados['identidade']->dsc_missao)->toBeNull();
});

test('com "Incluir IA" ligado, o Executivo consulta a IA', function () {
    $c = cenarioDoisCiclos();
    SystemSetting::setValue('ai_enabled', true);
    SystemSetting::setValue('ai_provider', 'gemini-studio');
    SystemSetting::setValue('ai_api_key', 'chave-de-teste');
    Http::fake();

    $this->actingAs($c['user'])->get(route('relatorios.executivo', [
        'organizacaoId' => $c['org']->cod_organizacao, 'ano' => 2026, 'include_ai' => '1',
    ]))->assertOk()->streamedContent();

    Http::assertSentCount(2);
});

test('a tela de Relatórios leva perspectiva e "Incluir IA" aos links que os anunciam', function () {
    $c = cenarioDoisCiclos();

    $html = Livewire::actingAs($c['user'])
        ->test(ListarRelatorios::class)
        ->set('perspectivaSelecionada', $c['pB']->cod_perspectiva)
        ->html();

    preg_match('/href="([^"]*relatorios\/executivo[^"]*)"/', $html, $executivo);
    preg_match('/href="([^"]*relatorios\/objetivos\/pdf[^"]*)"/', $html, $objetivos);

    expect(html_entity_decode($executivo[1] ?? ''))->toContain('perspectiva='.$c['pB']->cod_perspectiva)
        ->and(html_entity_decode($executivo[1] ?? ''))->toContain('include_ai=0')
        ->and(html_entity_decode($objetivos[1] ?? ''))->toContain('perspectiva='.$c['pB']->cod_perspectiva);
});

// ----------------------------------------------------- 9 · Mapa Estratégico

test('o Mapa Estratégico não imprime a missão de outro ciclo', function () {
    $c = cenarioDoisCiclos();
    $dados = capturarView('relatorios.identidade');

    $this->actingAs($c['user'])
        ->get(route('relatorios.identidade', ['organizacaoId' => $c['org']->cod_organizacao]).'?ano=2026')
        ->assertOk()->streamedContent();

    expect($dados['identidade']->dsc_missao)->toBeNull();

    Session::put('pei_selecionado_id', $c['cicloA']->cod_pei);
    $this->actingAs($c['user'])
        ->get(route('relatorios.identidade', ['organizacaoId' => $c['org']->cod_organizacao]).'?ano=2026')
        ->assertOk()->streamedContent();

    expect($dados['identidade']->dsc_missao)->toBe('Missão do ciclo A');
});

// ------------------------------------------------------ 10 · Iniciativas

test('PDF e Excel de Iniciativas trazem as mesmas iniciativas: as vigentes no ano, do ciclo selecionado', function () {
    $c = cenarioDoisCiclos();
    Excel::fake();
    $dados = capturarView('relatorios.planos');

    $this->actingAs($c['user'])
        ->get(route('relatorios.planos.pdf').'?organizacao_id='.$c['org']->cod_organizacao.'&ano=2026')
        ->assertOk()->streamedContent();

    $this->actingAs($c['user'])
        ->get(route('relatorios.planos.excel').'?organizacao_id='.$c['org']->cod_organizacao.'&ano=2026')
        ->assertOk();

    $noPdf = collect($dados['planos'])->pluck('dsc_plano_de_acao')->all();

    Excel::assertDownloaded('Planos_Acao_ORL_2026.xlsx', function (PlanosExport $export) use ($noPdf) {
        return $export->collection()->pluck('dsc_plano_de_acao')->all() === $noPdf;
    });

    expect($noPdf)->toBe(['Iniciativa longa do B']);
});

// --------------------------------------------------------- 14 · anos

test('a lista de anos da tela sai dos ciclos cadastrados e inclui o ano global', function () {
    $c = cenarioDoisCiclos();
    Session::put('ano_selecionado', 2024);

    $tela = Livewire::actingAs($c['user'])->test(ListarRelatorios::class);

    expect($tela->get('anos'))->toBe([2024, 2025, 2026, 2027, 2028, 2029, 2030])
        ->and((int) $tela->get('anoSelecionado'))->toBe(2024);
});

// ------------------------------------------------------- 15 · 16 · Objetivos / Excel

test('sem nenhum ciclo, o PDF de Objetivos devolve mensagem, não erro 500', function () {
    $org = Organization::create(['nom_organizacao' => 'Sem ciclo', 'sgl_organizacao' => 'SC', 'rel_cod_organizacao' => null]);
    $user = User::factory()->create(['trocarsenha' => 0]);
    $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);

    $this->actingAs($user)
        ->from(route('relatorios.index'))
        ->get(route('relatorios.objetivos.pdf').'?organizacao_id='.$org->cod_organizacao)
        ->assertRedirect(route('relatorios.index'))
        ->assertSessionHas('flash.banner');
});

test('a planilha também entra no histórico de relatórios gerados', function () {
    $c = cenarioDoisCiclos();

    $this->actingAs($c['user'])
        ->get(route('relatorios.planos.excel').'?organizacao_id='.$c['org']->cod_organizacao.'&ano=2026')
        ->assertOk();

    $registro = RelatorioGerado::where('user_id', $c['user']->id)->latest()->first();

    expect($registro)->not->toBeNull()
        ->and($registro->dsc_formato)->toBe('xlsx');
});

// ------------------------------------------------------ 5 · 17 · agendamento

test('o agendamento grava o ciclo selecionado e o cron gera daquele ciclo, com o ano escolhido', function () {
    $c = cenarioDoisCiclos();
    Session::put('pei_selecionado_id', $c['cicloA']->cod_pei);

    $filtros = ['ano' => 2027, 'periodo' => 'anual', 'perspectiva' => '', 'organizacao_id' => $c['org']->cod_organizacao, 'include_ai' => false];

    foreach (['objetivos', 'identidade'] as $tipo) {
        Livewire::actingAs($c['user'])
            ->test(AgendarRelatorio::class)
            ->call('carregar', $tipo, $filtros)
            ->call('salvar')
            ->assertHasNoErrors();
    }

    expect(RelatorioAgendado::count())->toBe(2)
        ->and(RelatorioAgendado::first()->txt_filtros['cod_pei'])->toBe($c['cicloA']->cod_pei);

    // O cron não tem sessão: sem o ciclo gravado, sairia o "ativo" (o B).
    Session::forget('pei_selecionado_id');
    RelatorioAgendado::query()->update(['dte_proxima_execucao' => now()->subMinute()]);
    $objetivos = capturarView('relatorios.objetivos');
    $mapa = capturarView('relatorios.identidade');

    $this->artisan('reports:process-scheduled')->assertSuccessful();

    expect($objetivos['pei']->cod_pei)->toBe($c['cicloA']->cod_pei)
        ->and((int) $mapa['filtros']['ano'])->toBe(2027)
        ->and(RelatorioGerado::count())->toBe(2);
});

test('tipo de relatório inventado não é agendado', function () {
    $c = cenarioDoisCiclos();

    Livewire::actingAs($c['user'])
        ->test(AgendarRelatorio::class)
        ->call('carregar', 'inventado', ['organizacao_id' => $c['org']->cod_organizacao])
        ->call('salvar')
        ->assertHasErrors('tipoRelatorio');

    expect(RelatorioAgendado::count())->toBe(0);
});

test('agendamento que falha não é reprocessado de hora em hora: tipo desconhecido é desativado, erro empurra a próxima execução', function () {
    $c = cenarioDoisCiclos();

    $desconhecido = RelatorioAgendado::create([
        'user_id' => $c['user']->id, 'dsc_tipo_relatorio' => 'inventado', 'dsc_frequencia' => 'mensal',
        'txt_filtros' => ['organizacao_id' => $c['org']->cod_organizacao], 'dte_proxima_execucao' => now()->subMinute(), 'bln_ativo' => true,
    ]);
    // Mapa Estratégico sem unidade lança exceção na geração.
    $comErro = RelatorioAgendado::create([
        'user_id' => $c['user']->id, 'dsc_tipo_relatorio' => 'identidade', 'dsc_frequencia' => 'mensal',
        'txt_filtros' => ['cod_pei' => $c['cicloB']->cod_pei], 'dte_proxima_execucao' => now()->subMinute(), 'bln_ativo' => true,
    ]);

    $this->artisan('reports:process-scheduled')->assertSuccessful();

    expect($desconhecido->fresh()->bln_ativo)->toBeFalse()
        ->and($comErro->fresh()->dte_proxima_execucao->isFuture())->toBeTrue();
});
