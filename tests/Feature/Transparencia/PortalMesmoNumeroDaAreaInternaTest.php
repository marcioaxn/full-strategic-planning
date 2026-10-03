<?php

/*
 * O portal da transparência calculava o atingimento da perspectiva por conta
 * própria, só com indicadores. Uma perspectiva sem indicador e com iniciativa em
 * execução aparecia 0% na home pública e 10% no Mapa Estratégico e no Dashboard.
 * O número publicado ao cidadão tem de ser o mesmo da área interna.
 */

use App\Livewire\LandingPage;
use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Services\IndicadorCalculoService;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

test('a home pública publica o atingimento da perspectiva calculado pelo mesmo serviço da área interna', function () {
    Cache::forget('lp_dados_publicos');

    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'cod_organizacao_pai' => null]);
    $pei = PEI::create([
        'dsc_pei' => 'Ciclo Portal',
        'num_ano_inicio_pei' => (int) date('Y'),
        'num_ano_fim_pei' => (int) date('Y') + 3,
    ]);
    $perspectiva = Perspectiva::create([
        'cod_pei' => $pei->cod_pei,
        'dsc_perspectiva' => 'Resultado Integrado',
        'num_nivel_hierarquico_apresentacao' => 4,
        'num_peso_indicadores' => 50,
        'num_peso_planos' => 50,
    ]);
    $objetivo = Objetivo::create([
        'cod_perspectiva' => $perspectiva->cod_perspectiva,
        'nom_objetivo' => 'Objetivo só com iniciativa',
        'dsc_objetivo' => 'Sem indicador, com entrega concluída.',
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);
    $plano = PlanoDeAcao::create([
        'cod_objetivo' => $objetivo->cod_objetivo,
        'cod_organizacao' => $org->cod_organizacao,
        'cod_tipo_execucao' => TipoExecucao::ACAO,
        'dsc_plano_de_acao' => 'Iniciativa em execução',
        'num_nivel_hierarquico_apresentacao' => 1,
        'dte_inicio' => now()->startOfYear()->toDateString(),
        'dte_fim' => now()->endOfYear()->toDateString(),
        'bln_status' => 'Em Andamento',
    ]);
    Entrega::create([
        'cod_plano_de_acao' => $plano->cod_plano_de_acao,
        'dsc_entrega' => 'Entrega concluída',
        'bln_status' => 'Concluído',
        'dte_prazo' => now()->toDateString(),
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);

    $esperado = round(app(IndicadorCalculoService::class)
        ->calcularAtingimentoPerspectiva($perspectiva->fresh(), (int) date('Y')), 1);

    $publicado = Livewire::test(LandingPage::class)->get('perspectivas')
        ->firstWhere('cod_perspectiva', $perspectiva->cod_perspectiva)
        ->atingimento_medio;

    expect($esperado)->toBeGreaterThan(0.0)
        ->and($publicado)->toBe($esperado);
});
