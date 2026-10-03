<?php

/*
 * "Assumir identidade" derrubava a sessão (achado no teste pelo navegador em
 * 03/10/2026): a tela voltava para o portal público em vez de mostrar o
 * sistema como o usuário escolhido.
 *
 * Causa: o AuthenticateSession (jetstream.auth_session) guarda na sessão o
 * hash da senha de quem está logado e, ao fim da requisição da troca, grava o
 * do usuário que o guard sanctum tinha em cache — o Super Admin. Na requisição
 * seguinte o usuário da sessão é o alvo, o hash não bate e o middleware desloga.
 *
 * Para reproduzir como no navegador, o teste entra pelo login de verdade e
 * esquece os guards entre as requisições: o usuário passa a vir só da sessão.
 */

use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('assumir a identidade de um usuário mantém a sessão, e encerrar volta ao Super Admin', function () {
    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);

    $admin = User::factory()->create(['password' => bcrypt('senha-de-teste-1')]);
    $admin->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
    $alvo = User::factory()->create();
    $alvo->perfisAcesso()->attach(PerfilAcesso::CONSULTA, ['cod_organizacao' => $org->cod_organizacao]);

    $this->post(route('login'), ['email' => $admin->email, 'password' => 'senha-de-teste-1']);
    Auth::forgetGuards();
    $this->get(route('dashboard'))->assertOk();

    Auth::forgetGuards();
    $this->post(route('impersonate.start', $alvo->id))->assertRedirect(route('dashboard'));

    Auth::forgetGuards();
    $this->get(route('dashboard'))->assertOk()->assertSee('Modo Impersonação Ativo');
    expect(Auth::guard('web')->id())->toBe($alvo->id);

    Auth::forgetGuards();
    $this->post(route('impersonate.stop'))->assertRedirect(route('admin.perfis'));

    Auth::forgetGuards();
    $this->get(route('dashboard'))->assertOk()->assertDontSee('Modo Impersonação Ativo');
    expect(Auth::guard('web')->id())->toBe($admin->id);
});
