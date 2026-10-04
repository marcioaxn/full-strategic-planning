<?php

/*
 * Correções da revisão adversária de 04/10/2026:
 * - excluir perspectiva leva (logicamente) os objetivos e o que pende deles,
 *   como o modal sempre prometeu (antes, só a perspectiva era apagada);
 * - Cadeia de Valor: atividade com perspectiva excluída volta a salvar, e a
 *   "Ordem" gigante vira mensagem de validação, não 500.
 */

use App\Livewire\StrategicPlanning\CadeiaDeValor;
use App\Livewire\StrategicPlanning\ListarPerspectivas;
use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\AtividadeCadeiaValor;
use App\Models\StrategicPlanning\FuturoAlmejado;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Livewire;

/** @return array{super: User, org: Organization, pei: PEI, perspectiva: Perspectiva, objetivo: Objetivo} */
function cenarioPerspectivaRevisao(): array
{
    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG']);
    $super = User::factory()->create(['ativo' => true]);
    $super->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
    $super->unsetRelation('perfisAcesso');

    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => (int) date('Y'), 'num_ano_fim_pei' => (int) date('Y') + 3]);
    $perspectiva = Perspectiva::create(['cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'Resultados', 'num_nivel_hierarquico_apresentacao' => 1]);
    $objetivo = Objetivo::create(['cod_perspectiva' => $perspectiva->cod_perspectiva, 'nom_objetivo' => 'O', 'dsc_objetivo' => 'D', 'num_nivel_hierarquico_apresentacao' => 1]);

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    return compact('super', 'org', 'pei', 'perspectiva', 'objetivo');
}

test('excluir a perspectiva exclui (logicamente) objetivos, iniciativas, entregas, indicadores, futuro almejado e comentários', function () {
    $c = cenarioPerspectivaRevisao();
    $outra = Perspectiva::create(['cod_pei' => $c['pei']->cod_pei, 'dsc_perspectiva' => 'Processos', 'num_nivel_hierarquico_apresentacao' => 2]);
    $objetivoQueFica = Objetivo::create(['cod_perspectiva' => $outra->cod_perspectiva, 'nom_objetivo' => 'Fica', 'dsc_objetivo' => 'D', 'num_nivel_hierarquico_apresentacao' => 1]);

    $plano = PlanoDeAcao::create([
        'cod_objetivo' => $c['objetivo']->cod_objetivo, 'cod_organizacao' => $c['org']->cod_organizacao,
        'cod_tipo_execucao' => TipoExecucao::ACAO, 'dsc_plano_de_acao' => 'Iniciativa',
        'num_nivel_hierarquico_apresentacao' => 1, 'dte_inicio' => now()->toDateString(),
        'dte_fim' => now()->addMonth()->toDateString(), 'bln_status' => 'Não Iniciado',
    ]);
    $entrega = Entrega::create(['cod_plano_de_acao' => $plano->cod_plano_de_acao, 'dsc_entrega' => 'E', 'bln_status' => 'Não Iniciado', 'num_nivel_hierarquico_apresentacao' => 1]);
    $indicador = Indicador::create([
        'cod_objetivo' => $c['objetivo']->cod_objetivo, 'nom_indicador' => 'I', 'dsc_indicador' => 'I',
        'dsc_unidade_medida' => 'Unidade', 'dsc_tipo' => 'Objetivo', 'bln_acumulado' => 'Não', 'dsc_periodo_medicao' => 'Mensal',
    ]);
    $futuro = FuturoAlmejado::create(['cod_objetivo' => $c['objetivo']->cod_objetivo, 'dsc_futuro_almejado' => 'Futuro']);

    Livewire::actingAs($c['super'])->test(ListarPerspectivas::class)
        ->call('confirmDelete', $c['perspectiva']->cod_perspectiva)
        ->call('delete')
        ->assertHasNoErrors();

    expect(Perspectiva::find($c['perspectiva']->cod_perspectiva))->toBeNull()
        ->and(Objetivo::find($c['objetivo']->cod_objetivo))->toBeNull()
        ->and(Objetivo::withTrashed()->find($c['objetivo']->cod_objetivo))->not->toBeNull()
        ->and(PlanoDeAcao::find($plano->cod_plano_de_acao))->toBeNull()
        ->and(Entrega::find($entrega->cod_entrega))->toBeNull()
        ->and(Indicador::find($indicador->cod_indicador))->toBeNull()
        ->and(FuturoAlmejado::find($futuro->getKey()))->toBeNull()
        // A outra perspectiva e o objetivo dela não são tocados.
        ->and(Perspectiva::find($outra->cod_perspectiva))->not->toBeNull()
        ->and(Objetivo::find($objetivoQueFica->cod_objetivo))->not->toBeNull();
});

test('excluir a perspectiva marca os comentários dos objetivos dela', function () {
    $c = cenarioPerspectivaRevisao();

    DB::table('strategic_planning.tab_objetivo_comentarios')->insert([
        'cod_comentario' => (string) Str::uuid(),
        'cod_objetivo' => $c['objetivo']->cod_objetivo,
        'user_id' => $c['super']->id,
        'dsc_comentario' => 'Comentário',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    Livewire::actingAs($c['super'])->test(ListarPerspectivas::class)
        ->call('confirmDelete', $c['perspectiva']->cod_perspectiva)
        ->call('delete');

    expect(DB::table('strategic_planning.tab_objetivo_comentarios')
        ->where('cod_objetivo', $c['objetivo']->cod_objetivo)->whereNull('deleted_at')->count())->toBe(0);
});

test('o modal de exclusão da perspectiva diz o que de fato será excluído', function () {
    $c = cenarioPerspectivaRevisao();

    Livewire::actingAs($c['super'])->test(ListarPerspectivas::class)
        ->call('confirmDelete', $c['perspectiva']->cod_perspectiva)
        ->assertSee('Os objetivos desta perspectiva também serão excluídos');
});

test('atividade cuja perspectiva foi excluída abre sem perspectiva e volta a salvar', function () {
    $c = cenarioPerspectivaRevisao();
    $atividade = AtividadeCadeiaValor::create(['cod_pei' => $c['pei']->cod_pei, 'dsc_atividade' => 'Atender', 'dsc_tipo' => 'Finalística', 'cod_perspectiva' => $c['perspectiva']->cod_perspectiva, 'num_ordem' => 1]);
    $c['perspectiva']->delete();

    Livewire::actingAs($c['super'])->test(CadeiaDeValor::class)
        ->call('editarAtividade', $atividade->cod_atividade_cadeia_valor)
        ->assertSet('formAtividade.cod_perspectiva', '')
        ->set('formAtividade.dsc_atividade', 'Atender bem')
        ->call('salvarAtividade')
        ->assertHasNoErrors();

    expect($atividade->fresh()->dsc_atividade)->toBe('Atender bem')
        ->and($atividade->fresh()->cod_perspectiva)->toBeNull();
});

test('perspectiva inválida na atividade mostra a mensagem na tela', function () {
    $c = cenarioPerspectivaRevisao();

    Livewire::actingAs($c['super'])->test(CadeiaDeValor::class)
        ->call('novaAtividade')
        ->set('formAtividade.dsc_atividade', 'Atender')
        ->set('formAtividade.cod_perspectiva', '01a00000-0000-7000-8000-000000000000')
        ->call('salvarAtividade')
        ->assertHasErrors(['formAtividade.cod_perspectiva'])
        ->assertSee('Escolha uma perspectiva deste ciclo.');
});

test('ordem gigante na atividade vira mensagem de validação, não erro 500', function () {
    $c = cenarioPerspectivaRevisao();

    Livewire::actingAs($c['super'])->test(CadeiaDeValor::class)
        ->call('novaAtividade')
        ->set('formAtividade.dsc_atividade', 'Atender')
        ->set('formAtividade.num_ordem', '99999999999')
        ->call('salvarAtividade')
        ->assertHasErrors(['formAtividade.num_ordem'])
        ->assertSee('Informe a ordem como um número de 0 a 9999.');

    expect(AtividadeCadeiaValor::count())->toBe(0);
});
