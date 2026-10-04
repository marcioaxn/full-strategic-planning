<?php

/*
 * Excluir um ciclo PEI exclui (logicamente) tudo o que é dele.
 *
 * 🔴 Achado no teste pelo navegador de 03/10/2026: três ciclos de teste
 * excluídos mais cedo ainda tinham identidade, perspectivas e objetivos vivos.
 * O teste vai pelo caminho da tela (ListarPeis::delete).
 */

use App\Livewire\StrategicPlanning\ListarPeis;
use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\MissaoVisaoValores;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Livewire\Livewire;

test('excluir o ciclo pela tela leva identidade, perspectivas, objetivos, iniciativas e entregas; outro ciclo fica intacto', function () {
    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);
    $super = User::factory()->create(['ativo' => true]);
    $super->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
    $super->unsetRelation('perfisAcesso');

    $montar = function (string $nome) use ($org) {
        $pei = PEI::create(['dsc_pei' => $nome, 'num_ano_inicio_pei' => 2026, 'num_ano_fim_pei' => 2029]);
        $perspectiva = Perspectiva::create(['cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'P', 'num_nivel_hierarquico_apresentacao' => 1]);
        $objetivo = Objetivo::create(['cod_perspectiva' => $perspectiva->cod_perspectiva, 'nom_objetivo' => 'O', 'dsc_objetivo' => 'O', 'num_nivel_hierarquico_apresentacao' => 1]);
        $plano = PlanoDeAcao::create([
            'cod_objetivo' => $objetivo->cod_objetivo, 'cod_organizacao' => $org->cod_organizacao,
            'cod_tipo_execucao' => TipoExecucao::ACAO, 'dsc_plano_de_acao' => 'I', 'num_nivel_hierarquico_apresentacao' => 1,
            'bln_status' => 'Em Andamento', 'dte_inicio' => '2026-01-01', 'dte_fim' => '2026-12-31',
        ]);
        $entrega = Entrega::create(['cod_plano_de_acao' => $plano->cod_plano_de_acao, 'dsc_entrega' => 'E', 'bln_status' => 'Não Iniciado', 'num_nivel_hierarquico_apresentacao' => 1]);
        $identidade = MissaoVisaoValores::create(['cod_pei' => $pei->cod_pei, 'cod_organizacao' => $org->cod_organizacao, 'dsc_missao' => 'M', 'dsc_visao' => 'V']);

        return compact('pei', 'perspectiva', 'objetivo', 'plano', 'entrega', 'identidade');
    };

    $excluir = $montar('Ciclo a excluir');
    $manter = $montar('Ciclo a manter');

    Livewire::actingAs($super)->test(ListarPeis::class)
        ->call('confirmDelete', $excluir['pei']->cod_pei)
        ->call('delete');

    foreach (['pei', 'perspectiva', 'objetivo', 'plano', 'entrega', 'identidade'] as $parte) {
        expect($excluir[$parte]->fresh()?->trashed() ?? true)->toBeTrue("{$parte} do ciclo excluído continuou vivo")
            ->and($manter[$parte]->fresh()->trashed())->toBeFalse("{$parte} do outro ciclo foi excluído junto");
    }
});
