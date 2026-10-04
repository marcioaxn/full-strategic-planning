<?php

/*
 * Uma pessoa tem um único papel de gestão na iniciativa.
 *
 * 🔴 Achado no teste pelo navegador de 04/10/2026: a mesma pessoa foi aceita
 * como Gestora Responsável e Gestora Substituta da mesma iniciativa — o
 * substituto, que existe para cobrir a ausência do titular, ficava anulado.
 * Caminho da tela: AtribuirResponsaveis::adicionar.
 */

use App\Livewire\ActionPlan\AtribuirResponsaveis;
use App\Models\PerfilAcesso;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Feature\PerformanceIndicators\CenarioIndicador;

test('a mesma pessoa não é Responsável e Substituta da mesma iniciativa', function () {
    $c = CenarioIndicador::criar();
    $plano = CenarioIndicador::plano($c['objetivo'], $c['org']);
    $pessoa = User::factory()->create(['ativo' => true]);
    // A tela oferece quem tem vínculo com a unidade da iniciativa.
    $pessoa->organizacoes()->attach($c['org']->cod_organizacao);

    $tela = Livewire::actingAs($c['user'])->test(AtribuirResponsaveis::class, ['planoId' => $plano->cod_plano_de_acao])
        ->set('novo_usuario_id', $pessoa->id)
        ->set('novo_perfil_id', PerfilAcesso::GESTOR_RESPONSAVEL)
        ->call('adicionar')
        ->assertHasNoErrors();

    $tela->set('novo_usuario_id', $pessoa->id)
        ->set('novo_perfil_id', PerfilAcesso::GESTOR_SUBSTITUTO)
        ->call('adicionar')
        ->assertHasErrors('novo_usuario_id');

    $papeis = DB::table('organization.rel_users_tab_organizacoes_tab_perfil_acesso')
        ->where('cod_plano_de_acao', $plano->cod_plano_de_acao)
        ->where('user_id', $pessoa->id)
        ->pluck('cod_perfil')->all();

    expect($papeis)->toBe([PerfilAcesso::GESTOR_RESPONSAVEL]);
});
