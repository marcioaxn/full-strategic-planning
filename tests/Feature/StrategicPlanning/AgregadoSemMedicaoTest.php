<?php

/*
 * "Sem medição" não é 0%.
 *
 * Achado no navegador em 04/10/2026: no Mapa Estratégico, uma perspectiva sem
 * nenhum indicador aparecia com 0% e a cor de "Crítico". O indicador já sabia
 * dizer "sem medição" (atingimentoMedido() === null, farol cinza); os
 * agregados — objetivo, perspectiva, IQG, portal público — tratavam "nenhum
 * dado" como zero e pintavam de vermelho um desempenho que ninguém mediu.
 *
 * Regra: indicador sem medição ou informativo não entra em média nenhuma; o
 * componente (indicadores / iniciativas) sem dado sai do cálculo híbrido e os
 * pesos se renormalizam; nada medido → NULL, cinza, "Sem medição".
 */

use App\Livewire\Dashboard\Index as Dashboard;
use App\Livewire\StrategicPlanning\DetalharObjetivo;
use App\Livewire\StrategicPlanning\MapaEstrategico;
use App\Models\ActionPlan\Entrega;
use App\Models\PerformanceIndicators\EvolucaoIndicador;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\Perspectiva;
use App\Services\IndicadorCalculoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\Feature\PerformanceIndicators\CenarioIndicador;

function medirSemMedicao(Indicador $indicador, ?float $previsto, ?float $realizado, int $mes = 1): void
{
    EvolucaoIndicador::create([
        'cod_indicador' => $indicador->cod_indicador,
        'num_ano' => (int) date('Y'),
        'num_mes' => $mes,
        'vlr_previsto' => $previsto,
        'vlr_realizado' => $realizado,
        'bln_atualizado' => 'Sim',
    ]);
}

/** Um segundo indicador no objetivo do cenário, ligado à mesma unidade. */
function outroIndicadorSemMedicao(array $c, array $atributos = []): Indicador
{
    $ind = Indicador::create(array_merge([
        'cod_objetivo' => $c['objetivo']->cod_objetivo,
        'nom_indicador' => 'Outro indicador '.uniqid(),
        'dsc_indicador' => 'Indicador adicional do teste.',
        'dsc_tipo' => 'Objetivo',
        'dsc_unidade_medida' => 'Percentual (%)',
        'bln_acumulado' => 'Não',
        'dsc_periodo_medicao' => 'Mensal',
        'dsc_polaridade' => 'Positiva',
        'dsc_calculation_type' => 'manual',
    ], $atributos));
    $ind->organizacoes()->attach($c['org']->cod_organizacao);

    return $ind;
}

/** @return array<string, mixed> a perspectiva do cenário como o Mapa a entrega à tela */
function perspectivaNoMapa($tela, string $codPerspectiva): array
{
    return collect($tela->get('perspectivas'))->firstWhere('cod_perspectiva', $codPerspectiva);
}

// ---------------------------------------------------------------- MAPA

test('mapa: perspectiva cujo indicador não tem medição fica cinza e "Sem medição", não 0% vermelho', function () {
    $c = CenarioIndicador::criar();
    CenarioIndicador::regua($c['pei']);
    // Lançou o previsto, não o realizado: há evolução, não há medição.
    medirSemMedicao($c['indicador'], 100, null);

    $tela = Livewire::actingAs($c['user'])->test(MapaEstrategico::class)->assertOk();
    $p = perspectivaNoMapa($tela, $c['objetivo']->cod_perspectiva);

    expect($p['atingimento_medio'])->toBeNull()
        ->and($p['cor_satisfacao'])->toBe(GrauSatisfacao::COR_SEM_REGUA)
        ->and($p['objetivos'][0]['resumo_indicadores']['percentual'])->toBeNull()
        ->and($p['objetivos'][0]['resumo_indicadores']['cor'])->toBe(GrauSatisfacao::COR_SEM_REGUA);

    // O selo da perspectiva sai cinza; a cor de "Crítico" só aparece na legenda.
    $tela->assertSee('Sem medição')
        ->assertDontSee('0,0%')
        ->assertSeeHtml('background-color: '.GrauSatisfacao::COR_SEM_REGUA.'; color')
        ->assertDontSeeHtml('background-color: #a11111; color');

    // A memória de cálculo diz o mesmo, por indicador.
    $tela->call('abrirMemoriaCalculo', 0)
        ->assertSet('detalhesCalculo.media', null)
        ->assertSet('detalhesCalculo.indicadores.0.atingimento', null)
        ->assertDontSee('0,0%');
});

test('mapa: perspectiva sem nenhum indicador nem iniciativa também é "Sem medição"', function () {
    $c = CenarioIndicador::criar();
    CenarioIndicador::regua($c['pei']);
    $c['indicador']->delete();

    $tela = Livewire::actingAs($c['user'])->test(MapaEstrategico::class)->assertOk();
    $p = perspectivaNoMapa($tela, $c['objetivo']->cod_perspectiva);

    expect($p['atingimento_medio'])->toBeNull()
        ->and($p['cor_satisfacao'])->toBe(GrauSatisfacao::COR_SEM_REGUA);
    $tela->assertDontSee('0,0%');
});

test('mapa: indicador sem medição e informativo ficam fora da média — vale só o medido', function () {
    $c = CenarioIndicador::criar();
    CenarioIndicador::regua($c['pei']);
    medirSemMedicao($c['indicador'], 100, 80);                       // 80%
    $semMedicao = outroIndicadorSemMedicao($c);                      // nada lançado
    $informativo = outroIndicadorSemMedicao($c, ['dsc_polaridade' => 'Não Aplicável']);
    medirSemMedicao($informativo, 100, 10);                          // 10%, mas informativo

    $tela = Livewire::actingAs($c['user'])->test(MapaEstrategico::class)->assertOk();
    $p = perspectivaNoMapa($tela, $c['objetivo']->cod_perspectiva);

    expect($p['atingimento_medio'])->toBe(80.0)
        ->and($p['objetivos'][0]['resumo_indicadores']['percentual'])->toBe(80.0)
        // E o serviço que Dashboard, portal e relatórios usam dá o mesmo número.
        ->and(app(IndicadorCalculoService::class)->calcularAtingimentoPerspectiva(
            Perspectiva::find($c['objetivo']->cod_perspectiva), (int) date('Y')
        ))->toBe(80.0)
        ->and(app(IndicadorCalculoService::class)->calcularAtingimentoObjetivo(
            $c['objetivo']->fresh(), (int) date('Y')
        ))->toBe(80.0)
        ->and(round($c['objetivo']->fresh()->calcularAtingimentoConsolidado((int) date('Y')), 1))->toBe(80.0);

    $memoria = collect($p['memoria_indicadores'])->keyBy('indicador');
    expect($memoria[$semMedicao->nom_indicador]['atingimento'])->toBeNull();
});

test('mapa: no cálculo híbrido, o componente sem dado sai e os pesos se renormalizam', function () {
    $c = CenarioIndicador::criar();
    CenarioIndicador::regua($c['pei']);
    Perspectiva::whereKey($c['objetivo']->cod_perspectiva)
        ->update(['num_peso_indicadores' => 50, 'num_peso_planos' => 50]);
    // Indicador sem medição; iniciativa com a única entrega do ano concluída.
    $plano = CenarioIndicador::plano($c['objetivo'], $c['org']);
    Entrega::create([
        'cod_plano_de_acao' => $plano->cod_plano_de_acao,
        'dsc_entrega' => 'Entrega concluída',
        'bln_status' => 'Concluído',
        'dte_prazo' => now()->toDateString(),
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);

    $tela = Livewire::actingAs($c['user'])->test(MapaEstrategico::class)->assertOk();
    $p = perspectivaNoMapa($tela, $c['objetivo']->cod_perspectiva);

    // Era 50% (o indicador entrava como 0 com metade do peso).
    expect($p['atingimento_medio'])->toBe(100.0)
        ->and($p['detalhes_calculo']['nota_indicadores'])->toBeNull()
        ->and($p['detalhes_calculo']['nota_planos'])->toBe(100.0)
        ->and(app(IndicadorCalculoService::class)->calcularAtingimentoPerspectiva(
            Perspectiva::find($c['objetivo']->cod_perspectiva), (int) date('Y')
        ))->toBe(100.0);

    $tela->call('abrirMemoriaCalculo', 0)->assertSee('Sem medição');
});

// ---------------------------------------------------------------- DASHBOARD

test('dashboard: perspectiva sem medição não entra no IQG nem vira barra de 0%', function () {
    $c = CenarioIndicador::criar();
    CenarioIndicador::regua($c['pei']);
    // Há evolução no ano (o IQG antigo a incluía), mas sem realizado.
    medirSemMedicao($c['indicador'], 100, null);

    $tela = Livewire::actingAs($c['user'])->test(Dashboard::class)->assertOk();
    $barra = collect($tela->get('chartData')['bsc'])->first();

    expect($barra['count'])->toBeNull()
        ->and($barra['color'])->toBe(GrauSatisfacao::COR_SEM_REGUA)
        ->and($tela->viewData('iqg')['tem_dados'])->toBeFalse()
        ->and($tela->viewData('iqg')['perspectivas'])->toBe([]);
});

// ---------------------------------------------------------------- OBJETIVO

test('detalhe do objetivo: indicador sem medição mostra "Sem medição", não 0,0%', function () {
    $c = CenarioIndicador::criar();
    CenarioIndicador::regua($c['pei']);
    medirSemMedicao($c['indicador'], 100, null);

    $tela = Livewire::actingAs($c['user'])
        ->test(DetalharObjetivo::class, ['id' => $c['objetivo']->cod_objetivo])
        ->assertOk();

    expect($tela->get('estatisticas')['atingimento'])->toBeNull()
        ->and($tela->get('estatisticas')['cor_farol'])->toBe(GrauSatisfacao::COR_SEM_REGUA);
    $tela->assertSee('Sem medição')->assertDontSee('0,0%');
});

// ---------------------------------------------------------------- PORTAL PÚBLICO

test('portal público: perspectiva sem medição não puxa a média global para baixo', function () {
    Cache::forget('lp_dados_publicos');
    $c = CenarioIndicador::criar();
    CenarioIndicador::regua($c['pei']);
    medirSemMedicao($c['indicador'], 100, 80); // perspectiva 1: 80%

    $objetivo2 = CenarioIndicador::objetivo($c['pei']);
    $semMedicao = outroIndicadorSemMedicao(['objetivo' => $objetivo2, 'org' => $c['org']]);
    medirSemMedicao($semMedicao, 100, null);    // perspectiva 2: sem medição

    Auth::logout();
    $resposta = $this->get('/')->assertOk();

    // Média das perspectivas MEDIDAS: 80, não (80 + 0) / 2 = 40. ("0,0%" não
    // se testa por ausência aqui: é substring de "80,0%".)
    $resposta->assertSee('80,0%')
        ->assertDontSee('40,0%')
        ->assertSee('Sem medição');
});
