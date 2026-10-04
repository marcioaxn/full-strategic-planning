<?php

/*
 * Revisão de 04/10/2026, achado 18: o 403 redirecionava ao dashboard com
 * ->with('error') e o início da impersonação com ->with('status'), mas o
 * layout só lê flash.banner. O usuário caía no dashboard sem explicação.
 */

use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/** O banner recebe a mensagem no x-data, em JSON (acentos viram \uXXXX). */
function textoDoBanner(string $mensagem): string
{
    return trim(json_encode($mensagem), '"');
}

test('quem é barrado por falta de permissão vê o motivo no dashboard', function () {
    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);
    $consulta = User::factory()->create(['trocarsenha' => 0]);
    $consulta->perfisAcesso()->attach(PerfilAcesso::CONSULTA, ['cod_organizacao' => $org->cod_organizacao]);
    $consulta->organizacoes()->sync([$org->cod_organizacao]);

    $this->actingAs($consulta)->get(route('admin.perfis'))->assertRedirect(route('dashboard'));

    $this->actingAs($consulta)->get(route('dashboard'))
        ->assertOk()
        ->assertSee(textoDoBanner('Você não tem permissão para realizar esta ação.'), false);
});

test('ao assumir uma identidade, o dashboard diz como quem se está navegando', function () {
    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);
    $admin = User::factory()->create(['password' => bcrypt('senha-de-teste-1'), 'trocarsenha' => 0]);
    $admin->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
    $alvo = User::factory()->create(['name' => 'Fulana de Teste', 'trocarsenha' => 0]);
    $alvo->perfisAcesso()->attach(PerfilAcesso::CONSULTA, ['cod_organizacao' => $org->cod_organizacao]);

    $this->post(route('login'), ['email' => $admin->email, 'password' => 'senha-de-teste-1']);
    Auth::forgetGuards();
    $this->post(route('impersonate.start', $alvo->id));
    Auth::forgetGuards();

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee(textoDoBanner('Você está agora visualizando o sistema como Fulana de Teste.'), false);
});
