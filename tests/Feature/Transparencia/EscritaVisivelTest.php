<?php

/*
 * As telas do mergulho são públicas para LEITURA. Este teste guarda as duas
 * pontas ao mesmo tempo:
 *
 *   - o visitante não vê nenhum controle de escrita (honestidade de interface);
 *   - quem tem permissão continua vendo (não quebrei a tela de quem trabalha).
 *
 * A segunda metade é a que costuma faltar: esconder botão é fácil, esconder
 * demais é o acidente.
 */

use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Illuminate\Support\Facades\Session;

function cenarioEscrita(): array
{
    $org = Organization::create([
        'nom_organizacao' => 'Org Escrita',
        'sgl_organizacao' => 'ORGESC',
        'cod_organizacao_pai' => null,
    ]);

    $pei = PEI::create([
        'dsc_pei' => 'Ciclo Escrita',
        'num_ano_inicio_pei' => (int) date('Y'),
        'num_ano_fim_pei' => (int) date('Y') + 3,
    ]);

    Perspectiva::create([
        'cod_pei' => $pei->cod_pei,
        'dsc_perspectiva' => 'Processos',
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    $admin = User::factory()->create();
    $admin->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
    $admin->unsetRelation('perfisAcesso');

    return [$admin, $org, $pei];
}

/** Ações que ESCREVEM. Filtro, busca e paginação não entram. */
function acoesDeEscritaNoHtml(string $html): array
{
    preg_match_all('/wire:click(?:\.prevent)?="(create|edit|confirmDelete|delete|save|pedirAjudaIA|aplicarSugestao)\b/', $html, $m);

    return array_values(array_unique($m[1]));
}

test('o VISITANTE não vê nenhum controle de escrita', function () {
    cenarioEscrita();

    foreach (['objetivos.index', 'indicadores.index', 'planos.index'] as $rota) {
        $html = $this->get(route($rota))->assertOk()->getContent();

        expect(acoesDeEscritaNoHtml($html))->toBe([], "rota [{$rota}] expõe escrita ao visitante");
    }
});

test('quem TEM permissão continua vendo os controles', function () {
    // O acidente oposto: esconder demais e quebrar a tela de quem trabalha.
    [$admin] = cenarioEscrita();

    foreach (['objetivos.index', 'indicadores.index', 'planos.index'] as $rota) {
        $html = $this->actingAs($admin)->get(route($rota))->assertOk()->getContent();

        expect(acoesDeEscritaNoHtml($html))->not->toBe([], "rota [{$rota}] perdeu os controles do usuário autorizado");
    }
});

test('o visitante vê o conteúdo — não é uma casca vazia', function () {
    cenarioEscrita();

    $this->get(route('objetivos.index'))->assertOk()->assertSee('Objetivos');
    $this->get(route('indicadores.index'))->assertOk();
    $this->get(route('planos.index'))->assertOk()->assertSee('Iniciativas', false);
});
