<?php

/*
 * A lista de Iniciativas filtrava só pela unidade, ignorando o ciclo (PEI)
 * selecionado no topo — a única entre as listas irmãs (Indicadores, Entregas,
 * Riscos já filtravam). Com o "Salvar como" do PEI, a cópia e o original
 * apareciam misturados na mesma lista. Achado no teste pelo navegador em
 * 03/10/2026.
 */

use App\Livewire\ActionPlan\ListarPlanos;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

function iniciativaNoCiclo(PEI $pei, Organization $org, string $nome): PlanoDeAcao
{
    $perspectiva = Perspectiva::create([
        'cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'Perspectiva', 'num_nivel_hierarquico_apresentacao' => 1, 'num_peso_indicadores' => 50, 'num_peso_planos' => 50,
    ]);
    $objetivo = Objetivo::create([
        'cod_perspectiva' => $perspectiva->cod_perspectiva, 'nom_objetivo' => 'Objetivo', 'dsc_objetivo' => 'x', 'num_nivel_hierarquico_apresentacao' => 1,
    ]);
    $plano = PlanoDeAcao::create([
        'cod_objetivo' => $objetivo->cod_objetivo, 'cod_organizacao' => $org->cod_organizacao,
        'cod_tipo_execucao' => TipoExecucao::ACAO, 'dsc_plano_de_acao' => $nome,
        'num_nivel_hierarquico_apresentacao' => 1, 'dte_inicio' => now()->startOfYear()->toDateString(),
        'dte_fim' => now()->endOfYear()->toDateString(), 'bln_status' => 'Em Andamento',
    ]);
    $plano->organizacoes()->attach($org->cod_organizacao);

    return $plano;
}

test('a lista de Iniciativas mostra só as do ciclo selecionado no topo', function () {
    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);
    $ano = (int) date('Y');
    $original = PEI::create(['dsc_pei' => 'Original', 'num_ano_inicio_pei' => $ano, 'num_ano_fim_pei' => $ano + 3]);
    $copia = PEI::create(['dsc_pei' => 'Cópia', 'num_ano_inicio_pei' => $ano, 'num_ano_fim_pei' => $ano + 3]);
    iniciativaNoCiclo($original, $org, 'Iniciativa do original');
    iniciativaNoCiclo($copia, $org, 'Iniciativa da cópia');

    $admin = User::factory()->create();
    $admin->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);
    Session::put('pei_selecionado_id', $copia->cod_pei);

    Livewire::actingAs($admin)->test(ListarPlanos::class)
        ->assertSee('Iniciativa da cópia')
        ->assertDontSee('Iniciativa do original');
});
