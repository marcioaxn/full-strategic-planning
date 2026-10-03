<?php

/*
 * "Salvar como" do PEI (pedido do gestor em 03/10/2026): cria um ciclo novo
 * levando tudo o que está preso ao de origem — inclusive para o mesmo período,
 * desde que com outra descrição.
 *
 * Os testes vão pelo caminho da tela (ListarPeis::abrirSalvarComo e
 * salvarComo) e conferem no banco que a cópia é independente: cada registro
 * copiado aponta para a cópia do pai, nunca para o original.
 */

use App\Livewire\StrategicPlanning\ListarPeis;
use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

function cenarioPeiParaCopiar(): array
{
    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'cod_organizacao_pai' => null]);
    $pei = PEI::create(['dsc_pei' => 'PEI 2026-2029', 'num_ano_inicio_pei' => 2026, 'num_ano_fim_pei' => 2029]);

    DB::table('strategic_planning.tab_missao_visao_valores')->insert([
        'cod_missao_visao_valores' => (string) Str::uuid(), 'cod_pei' => $pei->cod_pei,
        'cod_organizacao' => $org->cod_organizacao, 'dsc_missao' => 'Missão', 'dsc_visao' => 'Visão',
    ]);
    DB::table('strategic_planning.tab_grau_satisfacao')->insert([
        'cod_grau_satisfacao' => (string) Str::uuid(), 'cod_pei' => $pei->cod_pei,
        'dsc_grau_satisfacao' => 'Crítico', 'cor' => '#dc3545', 'vlr_minimo' => 0, 'vlr_maximo' => 29.99,
    ]);

    $perspectiva = Perspectiva::create([
        'cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'Sociedade',
        'num_nivel_hierarquico_apresentacao' => 1, 'num_peso_indicadores' => 50, 'num_peso_planos' => 50,
    ]);
    $objetivo = Objetivo::create([
        'cod_perspectiva' => $perspectiva->cod_perspectiva, 'nom_objetivo' => 'Objetivo pai',
        'dsc_objetivo' => 'x', 'num_nivel_hierarquico_apresentacao' => 1,
    ]);
    Objetivo::create([
        'cod_perspectiva' => $perspectiva->cod_perspectiva, 'nom_objetivo' => 'Objetivo filho',
        'dsc_objetivo' => 'x', 'num_nivel_hierarquico_apresentacao' => 2, 'cod_objetivo_pai' => $objetivo->cod_objetivo,
    ]);

    $plano = PlanoDeAcao::create([
        'cod_objetivo' => $objetivo->cod_objetivo, 'cod_organizacao' => $org->cod_organizacao,
        'cod_tipo_execucao' => TipoExecucao::ACAO, 'dsc_plano_de_acao' => 'Iniciativa',
        'num_nivel_hierarquico_apresentacao' => 1, 'dte_inicio' => '2026-01-01', 'dte_fim' => '2026-12-31',
        'bln_status' => 'Em Andamento',
    ]);
    $entrega = Entrega::create([
        'cod_plano_de_acao' => $plano->cod_plano_de_acao, 'dsc_entrega' => 'Entrega mãe',
        'bln_status' => 'Concluído', 'num_nivel_hierarquico_apresentacao' => 1,
    ]);
    Entrega::create([
        'cod_plano_de_acao' => $plano->cod_plano_de_acao, 'dsc_entrega' => 'Entrega filha', 'cod_entrega_pai' => $entrega->cod_entrega,
        'bln_status' => 'Não Iniciado', 'num_nivel_hierarquico_apresentacao' => 2,
    ]);

    $gestor = User::factory()->create();
    DB::table('organization.rel_users_tab_organizacoes_tab_perfil_acesso')->insert([
        'id' => (string) Str::uuid(), 'user_id' => $gestor->id, 'cod_organizacao' => $org->cod_organizacao,
        'cod_plano_de_acao' => $plano->cod_plano_de_acao, 'cod_perfil' => PerfilAcesso::GESTOR_RESPONSAVEL,
    ]);

    $indicador = (string) Str::uuid();
    DB::table('performance_indicators.tab_indicador')->insert([
        'cod_indicador' => $indicador, 'cod_objetivo' => $objetivo->cod_objetivo,
        'nom_indicador' => 'Indicador', 'dsc_tipo' => 'Objetivo', 'dsc_indicador' => 'x',
        'dsc_unidade_medida' => '%', 'bln_acumulado' => 'Não', 'dsc_periodo_medicao' => 'Mensal',
    ]);
    DB::table('performance_indicators.tab_meta_por_ano')->insert([
        'cod_meta_por_ano' => (string) Str::uuid(), 'cod_indicador' => $indicador, 'num_ano' => 2026, 'meta' => 80,
    ]);
    DB::table('performance_indicators.tab_evolucao_indicador')->insert([
        'cod_evolucao_indicador' => (string) Str::uuid(), 'cod_indicador' => $indicador,
        'num_ano' => 2026, 'num_mes' => 1, 'vlr_previsto' => 10, 'vlr_realizado' => 8,
    ]);

    $risco = (string) Str::uuid();
    DB::table('risk_management.tab_risco')->insert([
        'cod_risco' => $risco, 'cod_pei' => $pei->cod_pei, 'cod_organizacao' => $org->cod_organizacao,
        'num_codigo_risco' => 1, 'dsc_titulo' => 'Risco', 'txt_descricao' => 'x', 'dsc_categoria' => 'Operacional', 'dsc_status' => 'Identificado', 'num_probabilidade' => 3, 'num_impacto' => 3, 'num_nivel_risco' => 9,
    ]);
    DB::table('risk_management.tab_risco_objetivo')->insert([
        'cod_risco' => $risco, 'cod_objetivo' => $objetivo->cod_objetivo,
    ]);

    $admin = User::factory()->create();
    $admin->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
    $admin->unsetRelation('perfisAcesso');

    return [$admin, $pei, $org];
}

function peisDeObjetivos(string $codPei): array
{
    return DB::table('strategic_planning.tab_objetivo as o')
        ->join('strategic_planning.tab_perspectiva as p', 'p.cod_perspectiva', '=', 'o.cod_perspectiva')
        ->where('p.cod_pei', $codPei)
        ->pluck('o.cod_objetivo')
        ->all();
}

test('salvar como, no mesmo período, leva o PEI inteiro e a cópia não aponta para o original', function () {
    [$admin, $origem] = cenarioPeiParaCopiar();

    Livewire::actingAs($admin)
        ->test(ListarPeis::class)
        ->call('abrirSalvarComo', $origem->cod_pei)
        ->assertSet('copia_num_ano_inicio_pei', 2026)
        ->assertSet('copia_num_ano_fim_pei', 2029)
        ->set('copia_dsc_pei', 'PEI 2026-2029 — revisão')
        ->call('salvarComo')
        ->assertHasNoErrors()
        ->assertSet('showSuccessModal', true);

    $copia = PEI::where('dsc_pei', 'PEI 2026-2029 — revisão')->sole();
    expect([$copia->num_ano_inicio_pei, $copia->num_ano_fim_pei])->toBe([2026, 2029]);

    $objetivosOrigem = peisDeObjetivos($origem->cod_pei);
    $objetivosCopia = peisDeObjetivos($copia->cod_pei);
    expect($objetivosCopia)->toHaveCount(2)
        ->and(array_intersect($objetivosOrigem, $objetivosCopia))->toBe([]);

    // Hierarquia: o filho copiado aponta para o pai copiado.
    $filho = DB::table('strategic_planning.tab_objetivo')->whereIn('cod_objetivo', $objetivosCopia)->where('nom_objetivo', 'Objetivo filho')->sole();
    expect($objetivosCopia)->toContain($filho->cod_objetivo_pai);

    // Iniciativa, entregas (com hierarquia) e o gestor designado
    $planoCopia = DB::table('action_plan.tab_plano_de_acao')->whereIn('cod_objetivo', $objetivosCopia)->sole();
    $entregas = DB::table('action_plan.tab_entregas')->where('cod_plano_de_acao', $planoCopia->cod_plano_de_acao)->get();
    expect($entregas)->toHaveCount(2)
        ->and($entregas->pluck('cod_entrega'))->toContain($entregas->firstWhere('dsc_entrega', 'Entrega filha')->cod_entrega_pai);
    expect(DB::table('organization.rel_users_tab_organizacoes_tab_perfil_acesso')
        ->where('cod_plano_de_acao', $planoCopia->cod_plano_de_acao)
        ->where('cod_perfil', PerfilAcesso::GESTOR_RESPONSAVEL)->count())->toBe(1);

    // Indicador com meta e evolução
    $indicador = DB::table('performance_indicators.tab_indicador')->whereIn('cod_objetivo', $objetivosCopia)->sole();
    expect(DB::table('performance_indicators.tab_meta_por_ano')->where('cod_indicador', $indicador->cod_indicador)->value('meta'))->toEqual(80)
        ->and((float) DB::table('performance_indicators.tab_evolucao_indicador')->where('cod_indicador', $indicador->cod_indicador)->value('vlr_realizado'))->toBe(8.0);

    // Risco ligado ao objetivo copiado; identidade e régua do ciclo
    $risco = DB::table('risk_management.tab_risco')->where('cod_pei', $copia->cod_pei)->sole();
    expect(DB::table('risk_management.tab_risco_objetivo')->where('cod_risco', $risco->cod_risco)->value('cod_objetivo'))->toBeIn($objetivosCopia)
        ->and(DB::table('strategic_planning.tab_missao_visao_valores')->where('cod_pei', $copia->cod_pei)->value('dsc_missao'))->toBe('Missão')
        ->and((float) DB::table('strategic_planning.tab_grau_satisfacao')->where('cod_pei', $copia->cod_pei)->value('vlr_maximo'))->toBe(29.99);

    // O original segue intacto.
    expect($objetivosOrigem)->toHaveCount(2)
        ->and(DB::table('risk_management.tab_risco')->where('cod_pei', $origem->cod_pei)->count())->toBe(1);
});

test('salvar como recusa a mesma descrição de um PEI existente', function () {
    [$admin, $origem] = cenarioPeiParaCopiar();

    Livewire::actingAs($admin)
        ->test(ListarPeis::class)
        ->call('abrirSalvarComo', $origem->cod_pei)
        ->set('copia_dsc_pei', ' pei 2026-2029 ')
        ->call('salvarComo')
        ->assertHasErrors(['copia_dsc_pei']);

    expect(PEI::count())->toBe(1);
});

test('salvar como é só do Super Administrador', function () {
    [, $origem, $org] = cenarioPeiParaCopiar();
    $adminUnidade = User::factory()->create();
    $adminUnidade->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $org->cod_organizacao]);
    $adminUnidade->unsetRelation('perfisAcesso');

    $this->actingAs($adminUnidade);
    $tela = new ListarPeis;
    $tela->copiaOrigemId = $origem->cod_pei;
    $tela->copia_dsc_pei = 'Cópia indevida';
    expect(fn () => app()->call([$tela, 'salvarComo']))->toThrow(HttpException::class);
    expect(PEI::count())->toBe(1);
});

test('com original e cópia vigentes no mesmo período, o ciclo padrão continua sendo o original', function () {
    [$admin, $origem] = cenarioPeiParaCopiar();
    $this->travel(1)->minutes();

    Livewire::actingAs($admin)->test(ListarPeis::class)
        ->call('abrirSalvarComo', $origem->cod_pei)
        ->set('copia_dsc_pei', 'Cópia vigente')
        ->call('salvarComo');

    // Atualizar o original o move para o fim da tabela no PostgreSQL: sem
    // ORDER BY, o banco o devolveria depois da cópia.
    $origem->refresh()->touch();
    $this->travelTo(Carbon::create(2027, 6, 1));
    expect(PEI::ativos()->first()->cod_pei)->toBe($origem->cod_pei);
});
