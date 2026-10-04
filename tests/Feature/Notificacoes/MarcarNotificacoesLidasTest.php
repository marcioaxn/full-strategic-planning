<?php

/*
 * Revisão de 04/10/2026, achado 11: "Marcar todas como lidas" usava só o
 * user_id e dava como lidos os alertas de OUTRAS unidades, que o sino (filtrado
 * pela unidade selecionada) nunca mostrou. Agora marca só o que o sino lista
 * no escopo atual: os da unidade selecionada e os sem unidade.
 */

use App\Livewire\Shared\StrategicAlertsBell;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicAlert;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

test('marcar todas como lidas só alcança os alertas da unidade selecionada e os gerais', function () {
    $raiz = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);
    $a = Organization::create(['nom_organizacao' => 'Unidade A', 'sgl_organizacao' => 'UA', 'rel_cod_organizacao' => $raiz->cod_organizacao]);
    $b = Organization::create(['nom_organizacao' => 'Unidade B', 'sgl_organizacao' => 'UB', 'rel_cod_organizacao' => $raiz->cod_organizacao]);

    $user = User::factory()->create(['trocarsenha' => 0]);
    $user->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $a->cod_organizacao]);
    $user->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $b->cod_organizacao]);
    $user->organizacoes()->sync([$a->cod_organizacao, $b->cod_organizacao]);

    $alerta = fn (?string $org) => StrategicAlert::create([
        'user_id' => $user->id, 'cod_organizacao' => $org,
        'title' => 'Alerta', 'message' => 'Mensagem', 'type' => 'warning',
    ]);

    $alerta($a->cod_organizacao);
    $alerta($a->cod_organizacao);
    $geral = $alerta(null);
    $alerta($b->cod_organizacao);
    $alerta($b->cod_organizacao);

    Session::put('organizacao_selecionada_id', $a->cod_organizacao);

    Livewire::actingAs($user)
        ->test(StrategicAlertsBell::class)
        ->assertSet('unreadCount', 3)
        ->call('markAllAsRead')
        ->assertSet('unreadCount', 0);

    expect(StrategicAlert::where('cod_organizacao', $b->cod_organizacao)->whereNull('read_at')->count())->toBe(2)
        ->and(StrategicAlert::where('cod_organizacao', $a->cod_organizacao)->whereNull('read_at')->count())->toBe(0)
        ->and($geral->fresh()->read_at)->not->toBeNull();
});
