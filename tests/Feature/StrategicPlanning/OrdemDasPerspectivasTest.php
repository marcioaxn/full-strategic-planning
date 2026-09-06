<?php

/*
 * O BSC empilha as perspectivas de baixo para cima: nível 1 é a BASE, o maior
 * nível é o TOPO. Três lugares representavam essa ordem, e dois discordavam:
 *
 *   - Mapa Estratégico:      DESC  (correto)
 *   - Tabela de Perspectivas: ASC  (inverso — nível 1 na primeira linha)
 *   - Tela de Objetivos:      ordenava por UUID da perspectiva
 *
 * O cliente comparava as três e concluía que o sistema estava errado — ou,
 * pior, entendia o método ao contrário.
 *
 * A asserção é sobre a SEQUÊNCIA RENDERIZADA, não sobre a query.
 */

use App\Livewire\StrategicPlanning\ListarObjetivos;
use App\Livewire\StrategicPlanning\ListarPerspectivas;
use App\Livewire\StrategicPlanning\MapaEstrategico;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

function cenarioBsc(): array
{
    $org = Organization::create([
        'nom_organizacao' => 'Org BSC',
        'sgl_organizacao' => 'ORGBSC',
        'cod_organizacao_pai' => null,
    ]);

    $pei = PEI::create([
        'dsc_pei' => 'Ciclo BSC',
        'num_ano_inicio_pei' => (int) date('Y'),
        'num_ano_fim_pei' => (int) date('Y') + 3,
    ]);

    // Criadas fora de ordem de propósito: o que vale é o nível, não a inserção.
    foreach ([
        2 => 'Processos Internos',
        4 => 'Resultados para a Sociedade',
        1 => 'Aprendizado e Crescimento',
        3 => 'Destinatários',
    ] as $nivel => $nome) {
        Perspectiva::create([
            'cod_pei' => $pei->cod_pei,
            'dsc_perspectiva' => $nome,
            'num_nivel_hierarquico_apresentacao' => $nivel,
        ]);
    }

    $user = User::factory()->create();
    $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    return [$user, $pei];
}

/** A ordem canônica do BSC: topo (maior nível) primeiro. */
function ordemEsperada(PEI $pei): array
{
    return Perspectiva::where('cod_pei', $pei->cod_pei)
        ->orderBy('num_nivel_hierarquico_apresentacao', 'desc')
        ->pluck('dsc_perspectiva')
        ->all();
}

test('a tabela de Perspectivas lista o topo do mapa na primeira linha', function () {
    [$user, $pei] = cenarioBsc();

    $componente = Livewire::actingAs($user)->test(ListarPerspectivas::class)->assertOk();

    $ordemNaTela = collect($componente->get('perspectivas'))->pluck('dsc_perspectiva')->all();

    expect($ordemNaTela)->toBe(ordemEsperada($pei));
});

test('a tela de Objetivos agrupa na mesma ordem do mapa', function () {
    [$user, $pei] = cenarioBsc();

    $componente = Livewire::actingAs($user)->test(ListarObjetivos::class)->assertOk();

    $ordemNaTela = collect($componente->get('perspectivas'))->pluck('dsc_perspectiva')->all();

    expect($ordemNaTela)->toBe(ordemEsperada($pei));
});

test('COERÊNCIA: mapa, tabela e objetivos devolvem a MESMA sequência', function () {
    // A trava que responde à queixa original ("confunde o cliente"): se
    // qualquer uma das três telas mudar de ordem sozinha, este teste falha.
    [$user, $pei] = cenarioBsc();

    $esperada = ordemEsperada($pei);

    $mapa = Livewire::actingAs($user)->test(MapaEstrategico::class)->assertOk();
    $ordemMapa = collect($mapa->get('perspectivas'))
        ->map(fn ($p) => is_array($p) ? ($p['dsc_perspectiva'] ?? null) : $p->dsc_perspectiva)
        ->filter()
        ->values()
        ->all();

    $objetivos = Livewire::actingAs($user)->test(ListarObjetivos::class)->assertOk();
    $ordemObjetivos = collect($objetivos->get('perspectivas'))->pluck('dsc_perspectiva')->all();

    expect($ordemMapa)->toBe($esperada)
        ->and($ordemObjetivos)->toBe($esperada);
});

test('o nível 1 é a base e o maior nível é o topo', function () {
    [, $pei] = cenarioBsc();

    $ordem = ordemEsperada($pei);

    expect($ordem[0])->toBe('Resultados para a Sociedade')      // nível 4 — topo
        ->and(end($ordem))->toBe('Aprendizado e Crescimento');  // nível 1 — base
});
