<?php

namespace Tests\Feature\PerformanceIndicators;

use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Illuminate\Support\Facades\Session;

/**
 * Cenário comum aos testes do módulo de indicadores: unidade, ciclo PEI do ano
 * corrente, perspectiva, objetivo e um indicador de objetivo, com um Super
 * Administrador na sessão. Cada teste ajusta o que precisa.
 */
class CenarioIndicador
{
    /**
     * @param  array<string, mixed>  $atributos
     * @return array{user: User, org: Organization, pei: PEI, objetivo: Objetivo, indicador: Indicador}
     */
    public static function criar(array $atributos = []): array
    {
        $org = Organization::create([
            'nom_organizacao' => 'Unidade A '.uniqid(),
            'sgl_organizacao' => 'UA'.substr(uniqid(), -4),
            'cod_organizacao_pai' => null,
        ]);
        self::raiz($org);

        $pei = PEI::create([
            'dsc_pei' => 'Ciclo '.uniqid(),
            'num_ano_inicio_pei' => (int) date('Y') - 1,
            'num_ano_fim_pei' => (int) date('Y') + 2,
        ]);

        $objetivo = self::objetivo($pei);

        $indicador = Indicador::create(array_merge([
            'cod_objetivo' => $objetivo->cod_objetivo,
            'nom_indicador' => 'Indicador do cenário',
            'dsc_indicador' => 'Indicador para os testes do módulo.',
            'dsc_tipo' => 'Objetivo',
            'dsc_unidade_medida' => 'Percentual (%)',
            'bln_acumulado' => 'Não',
            'dsc_periodo_medicao' => 'Mensal',
            'dsc_polaridade' => 'Positiva',
            'dsc_calculation_type' => 'manual',
        ], $atributos));
        $indicador->organizacoes()->attach($org->cod_organizacao);

        $user = User::factory()->create(['ativo' => true]);
        $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
        $user->unsetRelation('perfisAcesso');

        Session::put('organizacao_selecionada_id', $org->cod_organizacao);
        Session::put('pei_selecionado_id', $pei->cod_pei);
        Session::put('ano_selecionado', (int) date('Y'));

        return compact('user', 'org', 'pei', 'objetivo', 'indicador');
    }

    /** Raiz da árvore = aponta para si mesma (é como getTreeForSelector a encontra). */
    public static function raiz(Organization $org): Organization
    {
        $org->update(['rel_cod_organizacao' => $org->cod_organizacao]);

        return $org;
    }

    public static function objetivo(PEI $pei): Objetivo
    {
        $perspectiva = Perspectiva::create([
            'cod_pei' => $pei->cod_pei,
            'dsc_perspectiva' => 'Perspectiva '.uniqid(),
            'num_nivel_hierarquico_apresentacao' => 1,
        ]);

        return Objetivo::create([
            'cod_perspectiva' => $perspectiva->cod_perspectiva,
            'nom_objetivo' => 'Objetivo '.uniqid(),
            'dsc_objetivo' => 'Objetivo do cenário de teste.',
            'num_nivel_hierarquico_apresentacao' => 1,
        ]);
    }

    public static function plano(Objetivo $objetivo, Organization $org): PlanoDeAcao
    {
        $plano = PlanoDeAcao::create([
            'cod_objetivo' => $objetivo->cod_objetivo,
            'cod_organizacao' => $org->cod_organizacao,
            'cod_tipo_execucao' => TipoExecucao::ACAO,
            'dsc_plano_de_acao' => 'Iniciativa '.uniqid(),
            'num_nivel_hierarquico_apresentacao' => 1,
            'dte_inicio' => now()->startOfYear()->toDateString(),
            'dte_fim' => now()->endOfYear()->toDateString(),
            'bln_status' => 'Em Andamento',
        ]);
        $plano->organizacoes()->sync([$org->cod_organizacao]);

        return $plano;
    }

    /** Régua de três faixas, com cores que não se confundem com as do Bootstrap. */
    public static function regua(PEI $pei, ?int $ano = null): void
    {
        foreach ([
            ['Crítico', '#a11111', 0, 59.99],
            ['Atenção', '#b2b222', 60, 89.99],
            ['No alvo', '#13a313', 90, 100],
        ] as [$nome, $cor, $min, $max]) {
            GrauSatisfacao::create([
                'cod_pei' => $pei->cod_pei,
                'num_ano' => $ano,
                'dsc_grau_satisfacao' => $nome,
                'cor' => $cor,
                'vlr_minimo' => $min,
                'vlr_maximo' => $max,
            ]);
        }
    }
}
