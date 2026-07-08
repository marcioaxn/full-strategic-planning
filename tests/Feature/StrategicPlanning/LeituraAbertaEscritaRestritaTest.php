<?php

use App\Livewire\StrategicPlanning\GerenciarFuturoAlmejado;
use App\Livewire\StrategicPlanning\MissaoVisao;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Livewire\Livewire;

/**
 * Princípio de segurança: navegar/visualizar informação (Mapa Estratégico,
 * Missão/Visão, Futuro Almejado etc.) é livre para qualquer usuário
 * autenticado, independentemente de perfil ou organização. Só a ESCRITA
 * (criar/editar/salvar/excluir) exige capacidade RBAC e/ou vínculo com a
 * organização responsável.
 */
test('usuário sem nenhum perfil consegue visualizar a tela de Missão/Visão (leitura livre)', function () {
    $user = User::factory()->create(['ativo' => true]);

    Livewire::actingAs($user)
        ->test(MissaoVisao::class)
        ->assertStatus(200);
});

test('usuário sem nenhum perfil NÃO consegue habilitar edição da Missão/Visão (escrita restrita)', function () {
    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027, 'bln_ativo' => true]);
    session(['pei_selecionado_id' => $pei->cod_pei]);

    $user = User::factory()->create(['ativo' => true]);

    Livewire::actingAs($user)
        ->test(MissaoVisao::class)
        ->call('habilitarEdicao')
        ->assertForbidden();
});

test('usuário sem nenhum perfil consegue visualizar o Futuro Almejado de um objetivo (leitura livre)', function () {
    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027, 'bln_ativo' => true]);
    $perspectiva = Perspectiva::create(['cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'P', 'num_nivel_hierarquico_apresentacao' => 1]);
    $objetivo = Objetivo::create(['cod_perspectiva' => $perspectiva->cod_perspectiva, 'nom_objetivo' => 'O', 'dsc_objetivo' => 'D', 'num_nivel_hierarquico_apresentacao' => 1]);

    $user = User::factory()->create(['ativo' => true]);

    Livewire::actingAs($user)
        ->test(GerenciarFuturoAlmejado::class, ['objetivoId' => $objetivo->cod_objetivo])
        ->assertStatus(200);
});

test('usuário sem nenhum perfil NÃO consegue criar um Futuro Almejado (escrita restrita)', function () {
    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027, 'bln_ativo' => true]);
    $perspectiva = Perspectiva::create(['cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'P', 'num_nivel_hierarquico_apresentacao' => 1]);
    $objetivo = Objetivo::create(['cod_perspectiva' => $perspectiva->cod_perspectiva, 'nom_objetivo' => 'O', 'dsc_objetivo' => 'D', 'num_nivel_hierarquico_apresentacao' => 1]);

    $user = User::factory()->create(['ativo' => true]);

    Livewire::actingAs($user)
        ->test(GerenciarFuturoAlmejado::class, ['objetivoId' => $objetivo->cod_objetivo])
        ->call('create')
        ->assertForbidden();
});
