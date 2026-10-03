<?php

/*
 * Regressão da auditoria de autorização de 03/10/2026 nas telas de
 * Planejamento (PESTEL, SWOT, Valores, Perspectivas, Temas Norteadores).
 *
 * Dois furos, ambos provados na leitura do código:
 *  1. Os métodos de escrita não chamavam authorize: o Gestor Substituto, que
 *     na MATRIZ só pode EDITAR o planejamento, criava e excluía itens.
 *  2. atualizarOrganizacao($id) é público (é ouvinte de evento) e trocava o
 *     escopo #[Locked] por qualquer organização. Os abort_unless dos métodos
 *     comparavam o registro com um escopo escolhido pelo próprio cliente.
 */

use App\Livewire\PerformanceIndicators\ListarIndicadores;
use App\Livewire\RiskManagement\ListarRiscos;
use App\Livewire\RiskManagement\MatrizRiscos;
use App\Livewire\StrategicPlanning\AnalisePESTEL;
use App\Livewire\StrategicPlanning\AnaliseSWOT;
use App\Livewire\StrategicPlanning\ListarValores;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\AnaliseAmbiental;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Valor;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

function cenarioPlanejamento(string $perfil): array
{
    $org = Organization::create(['nom_organizacao' => 'Órgão A', 'sgl_organizacao' => 'ORGA']);
    $outra = Organization::create(['nom_organizacao' => 'Órgão B', 'sgl_organizacao' => 'ORGB']);

    $pei = PEI::create([
        'dsc_pei' => 'Ciclo de Teste',
        'num_ano_inicio_pei' => (int) date('Y'),
        'num_ano_fim_pei' => (int) date('Y') + 3,
    ]);

    $user = User::factory()->create();
    $user->perfisAcesso()->attach($perfil, ['cod_organizacao' => $org->cod_organizacao]);
    $user->organizacoes()->syncWithoutDetaching([$org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    return [$user, $org, $outra, $pei];
}

test('gestor substituto não exclui item da PESTEL (só pode editar)', function () {
    [$user, $org, , $pei] = cenarioPlanejamento(PerfilAcesso::GESTOR_SUBSTITUTO);

    $item = AnaliseAmbiental::create([
        'cod_pei' => $pei->cod_pei,
        'cod_organizacao' => $org->cod_organizacao,
        'dsc_tipo_analise' => AnaliseAmbiental::TIPO_PESTEL,
        'dsc_categoria' => AnaliseAmbiental::PESTEL_POLITICO,
        'dsc_item' => 'Mudança de governo',
        'num_impacto' => 3,
    ]);

    Livewire::actingAs($user)
        ->test(AnalisePESTEL::class)
        ->call('delete', $item->cod_analise)
        ->assertForbidden();

    expect(AnaliseAmbiental::whereKey($item->getKey())->exists())->toBeTrue();
});

test('gestor substituto não cria item na SWOT', function () {
    [$user] = cenarioPlanejamento(PerfilAcesso::GESTOR_SUBSTITUTO);

    // A recusa vem já ao abrir o formulário de criação...
    Livewire::actingAs($user)
        ->test(AnaliseSWOT::class)
        ->call('create', AnaliseAmbiental::SWOT_FORCA)
        ->assertForbidden();

    // ...e também a quem pula o formulário e chama save() direto do navegador.
    Livewire::actingAs($user)
        ->test(AnaliseSWOT::class)
        ->set('dsc_categoria', AnaliseAmbiental::SWOT_FORCA)
        ->set('dsc_item', 'Força criada sem capacidade')
        ->call('save')
        ->assertForbidden();

    expect(AnaliseAmbiental::count())->toBe(0);
});

test('não é possível trocar o escopo para uma organização fora do alcance', function () {
    [$user, , $outra] = cenarioPlanejamento(PerfilAcesso::ADMIN_UNIDADE);

    Livewire::actingAs($user)
        ->test(ListarValores::class)
        ->call('atualizarOrganizacao', $outra->cod_organizacao)
        ->assertForbidden();
});

test('telas de monitoramento também não trocam o escopo para outra organização', function () {
    [$user, , $outra] = cenarioPlanejamento(PerfilAcesso::ADMIN_UNIDADE);

    foreach ([ListarIndicadores::class, MatrizRiscos::class, ListarRiscos::class] as $componente) {
        Livewire::actingAs($user)
            ->test($componente)
            ->call(in_array($componente, [MatrizRiscos::class]) ? 'atualizarMatriz' : 'atualizarOrganizacao', $outra->cod_organizacao)
            ->assertForbidden();
    }
});

test('admin da unidade continua criando valor na própria organização', function () {
    [$user, $org] = cenarioPlanejamento(PerfilAcesso::ADMIN_UNIDADE);

    Livewire::actingAs($user)
        ->test(ListarValores::class)
        ->call('create')
        ->set('nom_valor', 'Transparência')
        ->call('save')
        ->assertHasNoErrors();

    expect(Valor::where('cod_organizacao', $org->cod_organizacao)->where('nom_valor', 'Transparência')->exists())->toBeTrue();
});
