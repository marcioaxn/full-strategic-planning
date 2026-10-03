<?php

/*
 * Encontrado no teste pelo navegador em 03/10/2026: excluir uma iniciativa
 * deixava as entregas, os indicadores e o vínculo de Gestor dela "vivos". O
 * Gestor seguia com o perfil na unidade sem iniciativa nenhuma — e a base já
 * tinha um vínculo assim, de uma iniciativa excluída antes.
 */

use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('excluir a iniciativa exclui (logicamente) entregas, indicadores e vínculos de gestor dela', function () {
    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG']);
    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => (int) date('Y'), 'num_ano_fim_pei' => (int) date('Y') + 3]);
    $perspectiva = Perspectiva::create(['cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'P', 'num_nivel_hierarquico_apresentacao' => 1]);
    $objetivo = Objetivo::create(['cod_perspectiva' => $perspectiva->cod_perspectiva, 'nom_objetivo' => 'O', 'dsc_objetivo' => 'D', 'num_nivel_hierarquico_apresentacao' => 1]);

    $plano = PlanoDeAcao::create([
        'cod_objetivo' => $objetivo->cod_objetivo,
        'cod_organizacao' => $org->cod_organizacao,
        'cod_tipo_execucao' => TipoExecucao::ACAO,
        'dsc_plano_de_acao' => 'Iniciativa',
        'num_nivel_hierarquico_apresentacao' => 1,
        'dte_inicio' => now()->toDateString(),
        'dte_fim' => now()->addMonth()->toDateString(),
        'bln_status' => 'Não Iniciado',
    ]);
    $entrega = Entrega::create(['cod_plano_de_acao' => $plano->cod_plano_de_acao, 'dsc_entrega' => 'E', 'bln_status' => 'Não Iniciado', 'num_nivel_hierarquico_apresentacao' => 1]);
    $indicador = Indicador::create([
        'cod_plano_de_acao' => $plano->cod_plano_de_acao,
        'nom_indicador' => 'I',
        'dsc_indicador' => 'I',
        'dsc_unidade_medida' => 'Unidade',
        'dsc_tipo' => 'Efetividade',
        'bln_acumulado' => false,
        'dsc_periodo_medicao' => 'mensal',
    ]);

    $gestor = User::factory()->create();
    $gestor->perfisAcesso()->attach(PerfilAcesso::GESTOR_RESPONSAVEL, [
        'cod_organizacao' => $org->cod_organizacao,
        'cod_plano_de_acao' => $plano->cod_plano_de_acao,
    ]);

    $plano->delete();

    expect(Entrega::find($entrega->cod_entrega))->toBeNull()
        ->and(Entrega::withTrashed()->find($entrega->cod_entrega))->not->toBeNull()
        ->and(Indicador::find($indicador->cod_indicador))->toBeNull()
        ->and(DB::table('organization.rel_users_tab_organizacoes_tab_perfil_acesso')
            ->where('cod_plano_de_acao', $plano->cod_plano_de_acao)->whereNull('deleted_at')->count())->toBe(0)
        ->and($gestor->fresh()->temPerfilDeAcesso())->toBeFalse();
});
