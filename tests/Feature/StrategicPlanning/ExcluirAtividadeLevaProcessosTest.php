<?php

/*
 * Excluir uma atividade da Cadeia de Valor leva junto (exclusão lógica) os
 * processos dela. Achado no teste pelo navegador de 04/10/2026: os processos
 * continuavam vivos. Caminho da tela: CadeiaDeValor::confirmarExcluirAtividade
 * + executarExclusao.
 */

use App\Livewire\StrategicPlanning\CadeiaDeValor;
use App\Models\StrategicPlanning\AtividadeCadeiaValor;
use App\Models\StrategicPlanning\ProcessoAtividadeCadeiaValor;
use Livewire\Livewire;
use Tests\Feature\PerformanceIndicators\CenarioIndicador;

test('excluir a atividade exclui os processos dela, e só os dela', function () {
    $c = CenarioIndicador::criar();

    $criar = fn (string $nome) => AtividadeCadeiaValor::create([
        'cod_pei' => $c['pei']->cod_pei, 'dsc_atividade' => $nome, 'dsc_tipo' => 'Finalística', 'num_ordem' => 1,
    ]);
    $excluir = $criar('Atividade a excluir');
    $manter = $criar('Atividade a manter');

    $processo = fn (AtividadeCadeiaValor $a) => ProcessoAtividadeCadeiaValor::create([
        'cod_atividade_cadeia_valor' => $a->cod_atividade_cadeia_valor,
        'dsc_entrada' => 'E', 'dsc_transformacao' => 'T', 'dsc_saida' => 'S',
    ]);
    $doExcluido = $processo($excluir);
    $doMantido = $processo($manter);

    Livewire::actingAs($c['user'])->test(CadeiaDeValor::class)
        ->call('confirmarExcluirAtividade', $excluir->cod_atividade_cadeia_valor)
        ->call('executarExclusao');

    expect($excluir->fresh()->trashed())->toBeTrue()
        ->and($doExcluido->fresh()->trashed())->toBeTrue()
        ->and($doMantido->fresh()->trashed())->toBeFalse();
});
