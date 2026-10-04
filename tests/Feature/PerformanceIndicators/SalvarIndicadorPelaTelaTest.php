<?php

/*
 * Criar e editar indicador pelo caminho da TELA (ListarIndicadores::save).
 * Origem: revisão adversária do módulo de indicadores, 04/10/2026.
 */

use App\Livewire\PerformanceIndicators\ListarIndicadores;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\PEI;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\PerformanceIndicators\CenarioIndicador;

// M7 ------------------------------------------------------------------------

test('M7: indicador com unidade legada abre o formulário com a unidade dele no select', function () {
    $c = CenarioIndicador::criar(['dsc_unidade_medida' => 'Índice']);

    Livewire::actingAs($c['user'])
        ->test(ListarIndicadores::class)
        ->call('edit', $c['indicador']->cod_indicador)
        ->assertSet('form.dsc_unidade_medida', 'Índice')
        ->assertSeeHtml('<option value="Índice">');
});

test('M7: editar indicador legado mantendo a unidade grava sem erro', function () {
    $c = CenarioIndicador::criar(['dsc_unidade_medida' => 'Índice']);

    Livewire::actingAs($c['user'])
        ->test(ListarIndicadores::class)
        ->call('edit', $c['indicador']->cod_indicador)
        ->set('form.nom_indicador', 'Nome alterado')
        ->call('save')
        ->assertHasNoErrors();

    expect($c['indicador']->fresh()->nom_indicador)->toBe('Nome alterado');
});

test('M7: unidade fora da lista (e diferente da atual) é recusada', function () {
    $c = CenarioIndicador::criar();

    Livewire::actingAs($c['user'])
        ->test(ListarIndicadores::class)
        ->call('edit', $c['indicador']->cod_indicador)
        ->set('form.dsc_unidade_medida', 'Unidade inventada')
        ->call('save')
        ->assertHasErrors(['form.dsc_unidade_medida']);
});

// M8 ------------------------------------------------------------------------

test('M8: o Gestor da iniciativa não transforma o indicador em indicador de Objetivo', function () {
    $c = CenarioIndicador::criar();
    $plano = CenarioIndicador::plano($c['objetivo'], $c['org']);
    $c['indicador']->update(['cod_objetivo' => null, 'cod_plano_de_acao' => $plano->cod_plano_de_acao, 'dsc_tipo' => 'Iniciativa']);

    $gestor = User::factory()->create(['ativo' => true]);
    $gestor->perfisAcesso()->attach(PerfilAcesso::GESTOR_RESPONSAVEL, [
        'cod_organizacao' => $c['org']->cod_organizacao,
        'cod_plano_de_acao' => $plano->cod_plano_de_acao,
    ]);
    $gestor->unsetRelation('perfisAcesso');

    Livewire::actingAs($gestor)
        ->test(ListarIndicadores::class)
        ->call('edit', $c['indicador']->cod_indicador)
        ->set('form.dsc_tipo', 'Objetivo')
        ->set('form.cod_objetivo', $c['objetivo']->cod_objetivo)
        ->call('save')
        ->assertHasErrors(['form.dsc_tipo']);

    $indicador = $c['indicador']->fresh();
    expect($indicador->cod_plano_de_acao)->toBe($plano->cod_plano_de_acao)
        ->and($indicador->cod_objetivo)->toBeNull();
});

test('M8: objetivo de outro ciclo é recusado, mesmo para o Super Administrador', function () {
    $c = CenarioIndicador::criar();
    $outroPei = PEI::create(['dsc_pei' => 'Outro ciclo', 'num_ano_inicio_pei' => 2040, 'num_ano_fim_pei' => 2043]);
    $objetivoDeFora = CenarioIndicador::objetivo($outroPei);

    Livewire::actingAs($c['user'])
        ->test(ListarIndicadores::class)
        ->call('edit', $c['indicador']->cod_indicador)
        ->set('form.cod_objetivo', $objetivoDeFora->cod_objetivo)
        ->call('save')
        ->assertHasErrors(['form.cod_objetivo']);

    expect($c['indicador']->fresh()->cod_objetivo)->toBe($c['objetivo']->cod_objetivo);
});

// M9 ------------------------------------------------------------------------

function administradorDaUnidade(Organization $org): User
{
    $admin = User::factory()->create(['ativo' => true]);
    $admin->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $org->cod_organizacao]);
    $admin->organizacoes()->sync([$org->cod_organizacao]);
    $admin->unsetRelation('perfisAcesso');

    return $admin;
}

test('M9: a árvore de unidades do formulário só mostra as do escopo do usuário', function () {
    $c = CenarioIndicador::criar();
    $outra = CenarioIndicador::raiz(Organization::create(['nom_organizacao' => 'Unidade Fora Do Escopo', 'sgl_organizacao' => 'UFE', 'cod_organizacao_pai' => null]));
    $admin = administradorDaUnidade($c['org']);

    $ids = collect(Livewire::actingAs($admin)->test(ListarIndicadores::class)->get('organizacoesOptions'))->pluck('id');

    expect($ids)->toContain($c['org']->cod_organizacao)
        ->and($ids)->not->toContain($outra->cod_organizacao);
});

test('M9: marcar unidade fora do escopo vira mensagem de validação, não página 403', function () {
    $c = CenarioIndicador::criar();
    $outra = CenarioIndicador::raiz(Organization::create(['nom_organizacao' => 'Unidade Fora Do Escopo', 'sgl_organizacao' => 'UFE', 'cod_organizacao_pai' => null]));
    $admin = administradorDaUnidade($c['org']);

    Livewire::actingAs($admin)
        ->test(ListarIndicadores::class)
        ->set('form.nom_indicador', 'Novo indicador')
        ->set('form.dsc_tipo', 'Objetivo')
        ->set('form.cod_objetivo', $c['objetivo']->cod_objetivo)
        ->set('form.organizacoes_ids', [$outra->cod_organizacao])
        ->call('save')
        ->assertHasErrors(['form.organizacoes_ids.0']);

    expect(Indicador::where('nom_indicador', 'Novo indicador')->exists())->toBeFalse();
});

// M1 ------------------------------------------------------------------------

test('M1: o menu da lista não oferece Lançar Evolução para indicador automático', function () {
    $c = CenarioIndicador::criar();
    $plano = CenarioIndicador::plano($c['objetivo'], $c['org']);
    $c['indicador']->update([
        'cod_objetivo' => null, 'cod_plano_de_acao' => $plano->cod_plano_de_acao,
        'dsc_tipo' => 'Iniciativa', 'dsc_calculation_type' => 'action_plan',
    ]);

    Livewire::actingAs($c['user'])
        ->test(ListarIndicadores::class)
        ->assertSee($c['indicador']->nom_indicador)
        ->assertDontSeeHtml(route('indicadores.evolucao', $c['indicador']->cod_indicador));
});
