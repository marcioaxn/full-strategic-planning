<?php

/*
 * 22/11/2026: User::acoes() apontava para App\Models\Acao, classe que não existe —
 * faltava o `use` do model App\Models\ActionPlan\Acao. Ninguém chamava a relação;
 * quem chamasse tomava erro 500 ("Class not found").
 */

use App\Models\ActionPlan\Acao;
use App\Models\User;
use Illuminate\Support\Str;

test('a relação acoes() do usuário resolve o model de ações e lê só os registros dele', function () {
    $usuario = User::factory()->create();
    $outro = User::factory()->create();

    Acao::create(['user_id' => $usuario->id, 'table' => 'tab_teste', 'table_id' => (string) Str::uuid(), 'acao' => 'criou']);
    Acao::create(['user_id' => $outro->id, 'table' => 'tab_teste', 'table_id' => (string) Str::uuid(), 'acao' => 'editou']);

    expect($usuario->acoes()->getRelated())->toBeInstanceOf(Acao::class)
        ->and($usuario->acoes()->pluck('acao')->all())->toBe(['criou']);
});
