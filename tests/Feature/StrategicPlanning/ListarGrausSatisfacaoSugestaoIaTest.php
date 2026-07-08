<?php

use App\Livewire\StrategicPlanning\ListarGrausSatisfacao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\PEI;
use App\Models\User;
use Livewire\Livewire;

function criarPeiAtivo(string $descricao): PEI
{
    return PEI::create([
        'dsc_pei' => $descricao,
        'num_ano_inicio_pei' => 2024,
        'num_ano_fim_pei' => 2027,
        'bln_ativo' => true,
    ]);
}

test('aplicarSugestao grava o grau de satisfação vinculado ao PEI atualmente selecionado, não a um PEI antigo em cache', function () {
    $peiAntigo = criarPeiAtivo('Ciclo Antigo');
    $peiAtual = criarPeiAtivo('Ciclo Atual');

    $org = Organization::create(['nom_organizacao' => 'Org Teste', 'sgl_organizacao' => 'OT', 'cod_organizacao_pai' => null]);
    $user = User::factory()->create(['ativo' => true]);
    $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);

    session(['pei_selecionado_id' => $peiAntigo->cod_pei]);

    $component = Livewire::actingAs($user)->test(ListarGrausSatisfacao::class);

    // Primeira sugestão aplicada ainda com o PEI antigo selecionado — é o que
    // faz o componente "aprender" (cachear) esse cod_pei, reproduzindo o
    // estado em que o bug se manifestava.
    $component->call('aplicarSugestao', 'Crítico', '#dc3545', 0, 40);

    // Simula a troca do Ciclo PEI no menu superior, sem recarregar a página
    // (é exatamente isso que o componente precisa refletir na próxima gravação).
    session(['pei_selecionado_id' => $peiAtual->cod_pei]);

    $component->call('aplicarSugestao', 'Excelente', '#28a745', 80, 100);

    $grau = GrauSatisfacao::where('dsc_grau_satisfacao', 'Excelente')->first();

    expect($grau)->not->toBeNull()
        ->and($grau->cod_pei)->toBe($peiAtual->cod_pei)
        ->and($grau->cod_pei)->not->toBe($peiAntigo->cod_pei);
});

test('duas sugestões aplicadas em sequência após a troca de PEI são gravadas no PEI novo', function () {
    $peiAntigo = criarPeiAtivo('Ciclo Antigo');
    $peiAtual = criarPeiAtivo('Ciclo Atual');

    $org = Organization::create(['nom_organizacao' => 'Org Teste', 'sgl_organizacao' => 'OT', 'cod_organizacao_pai' => null]);
    $user = User::factory()->create(['ativo' => true]);
    $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);

    session(['pei_selecionado_id' => $peiAntigo->cod_pei]);

    $component = Livewire::actingAs($user)->test(ListarGrausSatisfacao::class);

    // "Aquece" o cache interno do componente ainda no PEI antigo.
    $component->call('aplicarSugestao', 'Regular', '#6c757d', 41, 60);

    session(['pei_selecionado_id' => $peiAtual->cod_pei]);

    $component->call('aplicarSugestao', 'Crítico', '#dc3545', 0, 40);
    $component->call('aplicarSugestao', 'Bom', '#ffc107', 61, 100);

    $graus = GrauSatisfacao::whereIn('dsc_grau_satisfacao', ['Crítico', 'Bom'])->get();

    expect($graus)->toHaveCount(2);
    foreach ($graus as $grau) {
        expect($grau->cod_pei)->toBe($peiAtual->cod_pei);
    }
});
