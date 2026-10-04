<?php

/*
 * Auditoria de segurança (PRM-01): o middleware TransparenciaPublica recusa
 * POST no carregamento da página, mas as ações dos componentes vão para
 * POST /livewire/update, que só passa pelo grupo 'web'. A barreira real é a
 * autorização dentro de cada método público.
 *
 * Este teste chama, como VISITANTE, todo método público declarado nos sete
 * componentes da área pública e prova que nenhum grava nada no banco. Método
 * novo entra sozinho na varredura (reflexão), sem depender de alguém lembrar.
 */

use App\Livewire\ActionPlan\DetalharPlano;
use App\Livewire\ActionPlan\ListarPlanos;
use App\Livewire\PerformanceIndicators\DetalharIndicador;
use App\Livewire\PerformanceIndicators\ListarIndicadores;
use App\Livewire\StrategicPlanning\DetalharObjetivo;
use App\Livewire\StrategicPlanning\ListarObjetivos;
use App\Livewire\StrategicPlanning\MapaEstrategico;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

/** Impressão digital do banco: linhas e última alteração de cada tabela de domínio. */
function impressaoDigitalDoBanco(): array
{
    $tabelas = DB::select("
        SELECT t.table_schema || '.' || t.table_name AS nome,
               EXISTS (SELECT 1 FROM information_schema.columns c
                        WHERE c.table_schema = t.table_schema AND c.table_name = t.table_name
                          AND c.column_name = 'updated_at') AS tem_updated
          FROM information_schema.tables t
         WHERE t.table_type = 'BASE TABLE'
           AND t.table_schema IN ('pei', 'strategic_planning', 'action_plan', 'performance_indicators', 'risk_management', 'organization')
           AND t.table_name NOT IN ('sessions', 'cache', 'cache_locks', 'jobs')
    ");

    $digital = [];
    foreach ($tabelas as $t) {
        $linha = DB::selectOne('SELECT COUNT(*) AS n'.($t->tem_updated ? ', MAX(updated_at) AS u' : '').' FROM '.$t->nome);
        $digital[$t->nome] = $linha->n.'|'.($linha->u ?? '');
    }

    return $digital;
}

/** Métodos públicos declarados no próprio componente (não os herdados do Livewire). */
function metodosPublicosDoComponente(string $classe): array
{
    $ignorar = '/^(mount|render|boot|hydrate|dehydrate|updated|updating|rendering|rendered|exception|get\w+Property)/';

    return collect((new ReflectionClass($classe))->getMethods(ReflectionMethod::IS_PUBLIC))
        ->filter(fn (ReflectionMethod $m) => $m->getDeclaringClass()->getName() === $classe
            && ! $m->isStatic() && ! preg_match($ignorar, $m->getName()))
        ->values()
        ->all();
}

test('visitante não grava nada em nenhum método dos componentes da Transparência', function () {
    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);
    $pei = PEI::create(['dsc_pei' => 'PEI 2024-2027', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027]);
    $perspectiva = Perspectiva::create(['cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'Sociedade', 'num_nivel_hierarquico_apresentacao' => 1]);
    $objetivo = Objetivo::create(['cod_perspectiva' => $perspectiva->cod_perspectiva, 'nom_objetivo' => 'Objetivo', 'dsc_objetivo' => 'Descrição', 'num_nivel_hierarquico_apresentacao' => 1, 'num_nivel_desdobramento' => 1]);
    $plano = PlanoDeAcao::create([
        'cod_objetivo' => $objetivo->cod_objetivo, 'cod_organizacao' => $org->cod_organizacao,
        'cod_tipo_execucao' => TipoExecucao::ACAO, 'num_nivel_hierarquico_apresentacao' => 1,
        'dsc_plano_de_acao' => 'Iniciativa', 'dte_inicio' => '2024-01-01', 'dte_fim' => '2027-12-31',
        'bln_status' => 'Em Andamento',
    ]);
    $plano->organizacoes()->sync([$org->cod_organizacao]);
    $indicador = Indicador::create(['cod_objetivo' => $objetivo->cod_objetivo, 'nom_indicador' => 'Indicador', 'dsc_indicador' => 'Descrição', 'dsc_tipo' => 'Objetivo', 'dsc_unidade_medida' => 'Percentual (%)', 'bln_acumulado' => false, 'dsc_periodo_medicao' => 'mensal']);
    $indicador->organizacoes()->attach($org->cod_organizacao);

    session(['organizacao_selecionada_id' => $org->cod_organizacao, 'pei_selecionado_id' => $pei->cod_pei]);

    $componentes = [
        MapaEstrategico::class => [[], $objetivo->cod_objetivo],
        ListarObjetivos::class => [[], $objetivo->cod_objetivo],
        DetalharObjetivo::class => [['id' => $objetivo->cod_objetivo], $objetivo->cod_objetivo],
        ListarIndicadores::class => [[], $indicador->cod_indicador],
        DetalharIndicador::class => [['id' => $indicador->cod_indicador], $indicador->cod_indicador],
        ListarPlanos::class => [[], $plano->cod_plano_de_acao],
        DetalharPlano::class => [['id' => $plano->cod_plano_de_acao], $plano->cod_plano_de_acao],
    ];

    $antes = impressaoDigitalDoBanco();
    $chamados = 0;
    $gravaram = [];

    foreach ($componentes as $classe => [$parametros, $id]) {
        foreach (metodosPublicosDoComponente($classe) as $metodo) {
            $args = array_map(fn (ReflectionParameter $p) => $p->getPosition() === 0 ? $id : 'x', $metodo->getParameters());

            // Cada chamada num savepoint: um erro de SQL (argumento inválido) aborta
            // a transação no PostgreSQL. A comparação é feita ANTES de desfazer —
            // o rollback só limpa para a próxima chamada, não esconde gravação.
            DB::beginTransaction();
            try {
                Livewire::test($classe, $parametros)->call($metodo->getName(), ...$args);
            } catch (Throwable) {
                // Recusa (403/405/422) ou argumento inválido: o que importa é o banco.
            }
            try {
                if (impressaoDigitalDoBanco() !== $antes) {
                    $gravaram[] = class_basename($classe).'::'.$metodo->getName();
                }
            } catch (QueryException) {
                // Transação abortada por erro de SQL: nada foi gravado com sucesso.
            }
            DB::rollBack();
            $chamados++;
        }
    }

    expect($chamados)->toBeGreaterThan(30)
        ->and($gravaram)->toBe([]);
});
