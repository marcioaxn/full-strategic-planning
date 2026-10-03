<?php

use App\Livewire\StrategicPlanning\GerenciarFuturoAlmejado;
use App\Livewire\StrategicPlanning\MissaoVisao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Livewire\Livewire;

/**
 * Leitura aberta a quem tem perfil; escrita restrita à capacidade.
 *
 * 🔴 Até 03/10/2026 a leitura era livre para QUALQUER conta autenticada, com
 * ou sem perfil — e o autocadastro está ativo: quem se cadastrasse lia o
 * planejamento de todas as unidades. Agora conta sem perfil não abre a tela;
 * o perfil Consulta (só leitura) abre e não grava nada.
 */
function usuarioDeConsulta(): User
{
    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'cod_organizacao_pai' => null]);
    $user = User::factory()->create(['ativo' => true]);
    $user->perfisAcesso()->attach(PerfilAcesso::CONSULTA, ['cod_organizacao' => $org->cod_organizacao]);
    $user->organizacoes()->syncWithoutDetaching([$org->cod_organizacao]);
    session(['organizacao_selecionada_id' => $org->cod_organizacao]);

    return $user->fresh();
}

function objetivoParaFuturoAlmejado(): Objetivo
{
    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027, 'bln_ativo' => true]);
    session(['pei_selecionado_id' => $pei->cod_pei]);
    $perspectiva = Perspectiva::create(['cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'P', 'num_nivel_hierarquico_apresentacao' => 1]);

    return Objetivo::create(['cod_perspectiva' => $perspectiva->cod_perspectiva, 'nom_objetivo' => 'O', 'dsc_objetivo' => 'D', 'num_nivel_hierarquico_apresentacao' => 1]);
}

test('conta sem perfil NÃO abre a tela de Missão/Visão', function () {
    $user = User::factory()->create(['ativo' => true]);

    $tela = Livewire::actingAs($user)->test(MissaoVisao::class);

    expect($tela->status())->not->toBe(200);
});

test('perfil Consulta abre a Missão/Visão (leitura)', function () {
    Livewire::actingAs(usuarioDeConsulta())
        ->test(MissaoVisao::class)
        ->assertStatus(200);
});

test('perfil Consulta NÃO habilita edição da Missão/Visão (escrita restrita)', function () {
    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027, 'bln_ativo' => true]);
    session(['pei_selecionado_id' => $pei->cod_pei]);

    Livewire::actingAs(usuarioDeConsulta())
        ->test(MissaoVisao::class)
        ->call('habilitarEdicao')
        ->assertForbidden();
});

test('perfil Consulta abre o Futuro Almejado de um objetivo (leitura)', function () {
    $user = usuarioDeConsulta();
    $objetivo = objetivoParaFuturoAlmejado();

    Livewire::actingAs($user)
        ->test(GerenciarFuturoAlmejado::class, ['objetivoId' => $objetivo->cod_objetivo])
        ->assertStatus(200);
});

test('perfil Consulta NÃO cria um Futuro Almejado (escrita restrita)', function () {
    $user = usuarioDeConsulta();
    $objetivo = objetivoParaFuturoAlmejado();

    Livewire::actingAs($user)
        ->test(GerenciarFuturoAlmejado::class, ['objetivoId' => $objetivo->cod_objetivo])
        ->call('create')
        ->assertForbidden();
});
