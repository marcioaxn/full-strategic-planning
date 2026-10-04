<?php

/*
 * Entrega só existe dentro de uma iniciativa (regra do BSC).
 *
 * 🔴 A entrada /entregas (busca do menu) abria o quadro da iniciativa mais
 * recente da unidade, escolhida pelo sistema: a pessoa cadastrava entrega numa
 * iniciativa que não escolheu. Os testes antigos sempre abriam o quadro já com
 * a iniciativa na URL — este cobre a entrada sem ela.
 */

use App\Livewire\Deliverables\DeliverablesBoard;
use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

function cenarioEntregaSemIniciativa(): array
{
    $org = Organization::create(['nom_organizacao' => 'Unidade', 'sgl_organizacao' => 'UN']);
    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => (int) date('Y'), 'num_ano_fim_pei' => (int) date('Y') + 3]);
    $perspectiva = Perspectiva::create(['cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'Processos', 'num_nivel_hierarquico_apresentacao' => 1]);
    $objetivo = Objetivo::create(['cod_perspectiva' => $perspectiva->cod_perspectiva, 'nom_objetivo' => 'Objetivo X', 'dsc_objetivo' => 'X', 'num_nivel_hierarquico_apresentacao' => 1]);
    $plano = PlanoDeAcao::create([
        'cod_objetivo' => $objetivo->cod_objetivo, 'cod_organizacao' => $org->cod_organizacao,
        'cod_tipo_execucao' => TipoExecucao::ACAO, 'dsc_plano_de_acao' => 'Iniciativa Única',
        'num_nivel_hierarquico_apresentacao' => 1, 'bln_status' => 'Em Andamento',
        'dte_inicio' => now()->startOfYear()->toDateString(), 'dte_fim' => now()->endOfYear()->toDateString(),
    ]);
    $plano->organizacoes()->sync([$org->cod_organizacao]);

    $admin = User::factory()->create(['ativo' => true]);
    $admin->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $org->cod_organizacao]);
    $admin->organizacoes()->sync([$org->cod_organizacao]);
    $admin->unsetRelation('perfisAcesso');

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    return [$admin, $plano];
}

test('/entregas sem iniciativa pede a escolha e lista as iniciativas, sem abrir quadro de nenhuma', function () {
    [$admin, $plano] = cenarioEntregaSemIniciativa();

    $this->actingAs($admin)->get(route('entregas.index'))
        ->assertOk()
        ->assertSee('Entregas — escolha a iniciativa')
        ->assertSee('Iniciativa Única')
        ->assertSee(route('planos.entregas', $plano->cod_plano_de_acao), false)
        ->assertDontSee('Nova Entrega nesta iniciativa');
});

test('sem iniciativa escolhida, nenhuma entrega é gravada mesmo chamando o método direto', function () {
    [$admin] = cenarioEntregaSemIniciativa();

    $quadro = Livewire::actingAs($admin)->test(DeliverablesBoard::class);

    $quadro->set('quickAddTitulo', 'Entrega sem iniciativa')->call('criarRapido')->assertStatus(422);

    expect(Entrega::count())->toBe(0);
});

test('o banco recusa entrega sem iniciativa', function () {
    expect(fn () => Entrega::create(['dsc_entrega' => 'Órfã', 'bln_status' => 'Não Iniciado', 'num_nivel_hierarquico_apresentacao' => 1]))
        ->toThrow(QueryException::class);
});

test('com a iniciativa escolhida, a entrega entra nela e o quadro diz qual é', function () {
    [$admin, $plano] = cenarioEntregaSemIniciativa();

    Livewire::actingAs($admin)->test(DeliverablesBoard::class, ['planoId' => $plano->cod_plano_de_acao])
        ->assertSee('Nova Entrega nesta iniciativa')
        ->set('quickAddTitulo', 'Entrega correta')
        ->call('criarRapido');

    expect(Entrega::where('dsc_entrega', 'Entrega correta')->value('cod_plano_de_acao'))->toBe($plano->cod_plano_de_acao);
});
