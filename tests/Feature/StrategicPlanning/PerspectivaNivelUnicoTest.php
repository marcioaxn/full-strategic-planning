<?php

/*
 * Duas perspectivas no mesmo nível deixam a ordem do mapa indefinida.
 *
 * 🔴 Achado no teste pelo navegador de 04/10/2026: "Aplicar" uma sugestão da
 * IA (ordem 1) gravou uma segunda perspectiva no nível 1 do ciclo. O cadastro
 * manual também aceitava. Caminho da tela: ListarPerspectivas::save e
 * ListarPerspectivas::aplicarSugestao.
 */

use App\Livewire\StrategicPlanning\ListarPerspectivas;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

function cenarioPerspectivaNivel(): array
{
    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);
    $super = User::factory()->create(['ativo' => true]);
    $super->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
    $super->unsetRelation('perfisAcesso');

    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => 2026, 'num_ano_fim_pei' => 2029]);
    Perspectiva::create(['cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'Base', 'num_nivel_hierarquico_apresentacao' => 1]);

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    return [$super, $pei];
}

test('cadastro manual recusa nível já ocupado no ciclo', function () {
    [$super, $pei] = cenarioPerspectivaNivel();

    Livewire::actingAs($super)->test(ListarPerspectivas::class)
        ->set('dsc_perspectiva', 'Outra')
        ->set('num_nivel_hierarquico_apresentacao', 1)
        ->call('save')
        ->assertHasErrors('num_nivel_hierarquico_apresentacao');

    expect(Perspectiva::where('cod_pei', $pei->cod_pei)->count())->toBe(1);
});

test('editar a própria perspectiva mantendo o nível continua permitido', function () {
    [$super, $pei] = cenarioPerspectivaNivel();
    $base = Perspectiva::where('cod_pei', $pei->cod_pei)->first();

    Livewire::actingAs($super)->test(ListarPerspectivas::class)
        ->call('edit', $base->cod_perspectiva)
        ->set('dsc_perspectiva', 'Base renomeada')
        ->call('save')
        ->assertHasNoErrors();

    expect($base->fresh()->dsc_perspectiva)->toBe('Base renomeada');
});

test('aplicar sugestão da IA com nível ocupado grava no próximo nível livre', function () {
    [$super, $pei] = cenarioPerspectivaNivel();

    Livewire::actingAs($super)->test(ListarPerspectivas::class)
        ->call('aplicarSugestao', 'Aprendizado e Crescimento', 1);

    expect(Perspectiva::where('cod_pei', $pei->cod_pei)->orderBy('num_nivel_hierarquico_apresentacao')
        ->pluck('num_nivel_hierarquico_apresentacao')->all())->toBe([1, 2]);
});
