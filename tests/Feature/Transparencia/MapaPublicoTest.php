<?php

/*
 * Item 7 — o cidadão navega o Mapa Estratégico e mergulha nos dados sem login,
 * mas NÃO escreve.
 *
 * A primeira metade é fácil de ver na tela. A segunda é a que importa: método
 * público de componente Livewire é invocável direto pelo navegador, sem passar
 * por botão nenhum. Esconder o botão não protege — a asserção tem de ser sobre
 * a CHAMADA.
 */

use App\Livewire\ActionPlan\ListarPlanos;
use App\Livewire\PerformanceIndicators\ListarIndicadores;
use App\Livewire\StrategicPlanning\ListarObjetivos;
use App\Models\Organization;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

function cenarioPublico(): array
{
    $org = Organization::create([
        'nom_organizacao' => 'Órgão Público',
        'sgl_organizacao' => 'ORGPUB',
        'cod_organizacao_pai' => null,
    ]);

    $pei = PEI::create([
        'dsc_pei' => 'Ciclo Público 2026-2029',
        'num_ano_inicio_pei' => (int) date('Y'),
        'num_ano_fim_pei' => (int) date('Y') + 3,
    ]);

    $perspectiva = Perspectiva::create([
        'cod_pei' => $pei->cod_pei,
        'dsc_perspectiva' => 'Resultados para a Sociedade',
        'num_nivel_hierarquico_apresentacao' => 4,
    ]);

    $objetivo = Objetivo::create([
        'cod_perspectiva' => $perspectiva->cod_perspectiva,
        'nom_objetivo' => 'Ampliar o acesso da população aos serviços',
        'dsc_objetivo' => 'Objetivo publicado no portal da transparência.',
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    return [$pei, $org, $objetivo];
}

// ------------------------------------------------------- O QUE O CIDADÃO VÊ

test('a home abre sem login', function () {
    cenarioPublico();

    $this->get('/')->assertOk();
});

test('o Mapa Estratégico abre sem login', function () {
    cenarioPublico();

    $this->get(route('pei.mapa'))->assertOk();
});

test('a home mostra o MESMO componente de mapa da rota /pei/mapa', function () {
    // O pedido é "da mesma forma como é mostrado em /pei/mapa". A landing
    // desenhava uma releitura própria, com barras e cores inventadas ali.
    cenarioPublico();

    $home = $this->get('/')->getContent();
    $mapa = $this->get(route('pei.mapa'))->getContent();

    // A raiz do componente real.
    expect($home)->toContain('mapa-canvas')
        ->and($mapa)->toContain('mapa-canvas');
});

test('o mapa público mostra os objetivos — o dado, não só a moldura', function () {
    [, , $objetivo] = cenarioPublico();

    $this->get(route('pei.mapa'))
        ->assertOk()
        ->assertSee($objetivo->nom_objetivo);
});

test('o cidadão mergulha nos dados pelos cliques, sem login', function () {
    // São as telas para onde o mapa leva.
    cenarioPublico();

    $this->get(route('objetivos.index'))->assertOk();
    $this->get(route('indicadores.index'))->assertOk();
    $this->get(route('planos.index'))->assertOk();
});

// ------------------------------------------------- O QUE ELE NÃO PODE FAZER

test('visitante NÃO cria objetivo', function () {
    cenarioPublico();

    Livewire::test(ListarObjetivos::class)
        ->call('create')
        ->assertForbidden();
});

test('visitante NÃO salva objetivo', function () {
    cenarioPublico();

    Livewire::test(ListarObjetivos::class)
        ->set('nom_objetivo', 'Objetivo inserido por visitante')
        ->set('num_nivel_hierarquico_apresentacao', 1)
        ->call('save')
        ->assertForbidden();

    expect(Objetivo::where('nom_objetivo', 'Objetivo inserido por visitante')->exists())
        ->toBeFalse();
});

test('visitante NÃO exclui objetivo', function () {
    [, , $objetivo] = cenarioPublico();

    Livewire::test(ListarObjetivos::class)
        ->call('confirmDelete', $objetivo->cod_objetivo)
        ->assertForbidden();

    expect(Objetivo::find($objetivo->cod_objetivo))->not->toBeNull();
});

test('visitante NÃO cria indicador nem iniciativa', function () {
    cenarioPublico();

    Livewire::test(ListarIndicadores::class)->call('create')->assertForbidden();
    Livewire::test(ListarPlanos::class)->call('create')->assertForbidden();
});

test('a área de Transparência recusa qualquer verbo que não seja GET', function () {
    cenarioPublico();

    $this->post(route('pei.mapa'))->assertStatus(405);
    $this->put(route('objetivos.index'))->assertStatus(405);
    $this->delete(route('indicadores.index'))->assertStatus(405);
});

test('o mapa público NÃO expõe nome nem e-mail de pessoa', function () {
    // Dado pessoal em tela pública é LGPD, não preferência.
    //
    // A asserção é sobre dado REAL de pessoa, não sobre um padrão de e-mail:
    // regex genérica casa com string de asset e versão de pacote no HTML, e
    // reportaria erro do MEDIDOR como falha do sistema.
    cenarioPublico();

    $pessoa = User::factory()->create([
        'name' => 'Fulano de Tal Servidor',
        'email' => 'fulano.servidor@orgao.gov.br',
    ]);

    $html = $this->get(route('pei.mapa'))->getContent();

    expect($html)->not->toContain($pessoa->email)
        ->and($html)->not->toContain($pessoa->name);
});

test('as telas administrativas continuam FECHADAS ao visitante', function () {
    cenarioPublico();

    foreach (['usuarios.index', 'admin.perfis', 'admin.configuracoes', 'audit.index', 'dashboard'] as $rota) {
        $this->get(route($rota))->assertRedirect();
    }
});
