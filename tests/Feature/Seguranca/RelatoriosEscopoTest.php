<?php

/*
 * Achado de 03/10/2026: as rotas de relatório exigiam só login. Qualquer
 * usuário autenticado — inclusive sem perfil nenhum — baixava o PDF/Excel de
 * qualquer organização trocando o ID na URL.
 */

use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\User;

function cenarioEscopoRelatorio(?string $perfil): array
{
    $org = Organization::create(['nom_organizacao' => 'Órgão A', 'sgl_organizacao' => 'RELA']);
    $outra = Organization::create(['nom_organizacao' => 'Órgão B', 'sgl_organizacao' => 'RELB']);

    $user = User::factory()->create();
    if ($perfil) {
        $user->perfisAcesso()->attach($perfil, ['cod_organizacao' => $org->cod_organizacao]);
        $user->organizacoes()->syncWithoutDetaching([$org->cod_organizacao]);
    }

    return [$user, $org, $outra];
}

test('usuário sem perfil não baixa relatório', function () {
    [$user, $org] = cenarioEscopoRelatorio(null);

    // Um middleware anterior pode redirecioná-lo (302) ou o controlador negar
    // (403): o que não pode é receber o arquivo.
    $resposta = $this->actingAs($user)
        ->get(route('relatorios.riscos.excel', ['organizacao_id' => $org->cod_organizacao]));

    expect($resposta->status())->toBeIn([302, 403])
        ->and($resposta->headers->get('content-disposition'))->toBeNull();
});

test('admin de uma unidade não baixa relatório de outra unidade', function () {
    [$user, , $outra] = cenarioEscopoRelatorio(PerfilAcesso::ADMIN_UNIDADE);

    $this->actingAs($user)
        ->get(route('relatorios.planos.excel', ['organizacao_id' => $outra->cod_organizacao]))
        ->assertForbidden();
});

test('admin da unidade baixa o relatório da própria unidade', function () {
    [$user, $org] = cenarioEscopoRelatorio(PerfilAcesso::ADMIN_UNIDADE);

    $this->actingAs($user)
        ->get(route('relatorios.riscos.excel', ['organizacao_id' => $org->cod_organizacao]))
        ->assertOk();
});
