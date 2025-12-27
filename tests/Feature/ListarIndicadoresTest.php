<?php

namespace Tests\Feature;

use App\Livewire\Indicador\ListarIndicadores;
use App\Models\PEI\Indicador;
use App\Models\PEI\ObjetivoEstrategico;
use App\Models\PEI\Perspectiva;
use App\Models\PEI\PEI;
use App\Models\PEI\PlanoDeAcao;
use App\Models\PEI\TipoExecucao;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ListarIndicadoresTest extends TestCase
{
    protected static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! in_array('pgsql', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('PostgreSQL driver not available.');
        }

        if (! static::$migrated) {
            Artisan::call('migrate:fresh', [
                '--database' => 'pgsql',
                '--force' => true,
            ]);
            static::$migrated = true;
        }

        DB::connection('pgsql')->beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::connection('pgsql')->rollBack();
        parent::tearDown();
    }

    /**
     * Cria dados de teste necessarios para os testes de indicadores
     */
    protected function criarDadosTeste(): array
    {
        // Criar organizacao
        $organizacao = Organization::create([
            'cod_organizacao' => (string) \Illuminate\Support\Str::uuid(),
            'nom_organizacao' => 'Organizacao Teste',
            'sgl_organizacao' => 'OT',
            'bln_ativo' => 'Sim',
        ]);

        // Criar PEI ativo (ano atual dentro do intervalo)
        $pei = PEI::create([
            'cod_pei' => (string) \Illuminate\Support\Str::uuid(),
            'dsc_pei' => 'PEI Teste 2024-2027',
            'num_ano_inicio_pei' => 2024,
            'num_ano_fim_pei' => 2027,
        ]);

        // Criar perspectiva
        $perspectiva = Perspectiva::create([
            'cod_perspectiva' => (string) \Illuminate\Support\Str::uuid(),
            'cod_pei' => $pei->cod_pei,
            'dsc_perspectiva' => 'Perspectiva Teste',
            'num_nivel_hierarquico_apresentacao' => 1,
        ]);

        // Criar dois objetivos estrategicos
        $objetivo1 = ObjetivoEstrategico::create([
            'cod_objetivo_estrategico' => (string) \Illuminate\Support\Str::uuid(),
            'cod_perspectiva' => $perspectiva->cod_perspectiva,
            'nom_objetivo_estrategico' => 'Objetivo Estrategico 1',
            'dsc_objetivo_estrategico' => 'Descricao do objetivo 1',
            'num_nivel_hierarquico_apresentacao' => 1,
        ]);

        $objetivo2 = ObjetivoEstrategico::create([
            'cod_objetivo_estrategico' => (string) \Illuminate\Support\Str::uuid(),
            'cod_perspectiva' => $perspectiva->cod_perspectiva,
            'nom_objetivo_estrategico' => 'Objetivo Estrategico 2',
            'dsc_objetivo_estrategico' => 'Descricao do objetivo 2',
            'num_nivel_hierarquico_apresentacao' => 2,
        ]);

        // Criar tipo de execucao
        $tipoExecucao = TipoExecucao::create([
            'cod_tipo_execucao' => (string) \Illuminate\Support\Str::uuid(),
            'dsc_tipo_execucao' => 'Projeto',
        ]);

        // Criar plano de acao vinculado ao objetivo 1
        $planoAcao = PlanoDeAcao::create([
            'cod_plano_de_acao' => (string) \Illuminate\Support\Str::uuid(),
            'cod_objetivo_estrategico' => $objetivo1->cod_objetivo_estrategico,
            'cod_tipo_execucao' => $tipoExecucao->cod_tipo_execucao,
            'cod_organizacao' => $organizacao->cod_organizacao,
            'dsc_plano_de_acao' => 'Plano de Acao do Objetivo 1',
            'dte_inicio' => now(),
            'dte_fim' => now()->addYear(),
            'bln_status' => 'Em Andamento',
            'num_nivel_hierarquico_apresentacao' => 1,
        ]);

        // Criar indicadores para objetivo 1 (2 indicadores diretos)
        $indicador1Obj1 = Indicador::create([
            'cod_indicador' => (string) \Illuminate\Support\Str::uuid(),
            'cod_objetivo_estrategico' => $objetivo1->cod_objetivo_estrategico,
            'dsc_tipo' => 'Resultado',
            'nom_indicador' => 'Indicador Direto 1 do Objetivo 1',
            'dsc_indicador' => 'Descricao indicador 1',
            'dsc_unidade_medida' => '%',
            'dsc_periodo_medicao' => 'Mensal',
            'bln_acumulado' => 'Nao',
        ]);

        $indicador2Obj1 = Indicador::create([
            'cod_indicador' => (string) \Illuminate\Support\Str::uuid(),
            'cod_objetivo_estrategico' => $objetivo1->cod_objetivo_estrategico,
            'dsc_tipo' => 'Eficiencia',
            'nom_indicador' => 'Indicador Direto 2 do Objetivo 1',
            'dsc_indicador' => 'Descricao indicador 2',
            'dsc_unidade_medida' => 'Indice',
            'dsc_periodo_medicao' => 'Trimestral',
            'bln_acumulado' => 'Nao',
        ]);

        // Criar indicador vinculado ao plano de acao (objetivo 1 via plano)
        $indicadorPlano = Indicador::create([
            'cod_indicador' => (string) \Illuminate\Support\Str::uuid(),
            'cod_plano_de_acao' => $planoAcao->cod_plano_de_acao,
            'dsc_tipo' => 'Processo',
            'nom_indicador' => 'Indicador do Plano de Acao (Objetivo 1)',
            'dsc_indicador' => 'Descricao indicador do plano',
            'dsc_unidade_medida' => '%',
            'dsc_periodo_medicao' => 'Mensal',
            'bln_acumulado' => 'Sim',
        ]);

        // Criar indicadores para objetivo 2 (1 indicador)
        $indicadorObj2 = Indicador::create([
            'cod_indicador' => (string) \Illuminate\Support\Str::uuid(),
            'cod_objetivo_estrategico' => $objetivo2->cod_objetivo_estrategico,
            'dsc_tipo' => 'Resultado',
            'nom_indicador' => 'Indicador do Objetivo 2',
            'dsc_indicador' => 'Descricao indicador objetivo 2',
            'dsc_unidade_medida' => 'Quantidade',
            'dsc_periodo_medicao' => 'Anual',
            'bln_acumulado' => 'Nao',
        ]);

        // Criar usuario para autenticacao
        $user = User::factory()->create();

        return [
            'organizacao' => $organizacao,
            'pei' => $pei,
            'perspectiva' => $perspectiva,
            'objetivo1' => $objetivo1,
            'objetivo2' => $objetivo2,
            'planoAcao' => $planoAcao,
            'indicador1Obj1' => $indicador1Obj1,
            'indicador2Obj1' => $indicador2Obj1,
            'indicadorPlano' => $indicadorPlano,
            'indicadorObj2' => $indicadorObj2,
            'user' => $user,
        ];
    }

    /**
     * Testa se o filtro por objetivo retorna apenas indicadores daquele objetivo
     */
    public function test_filtro_objetivo_retorna_indicadores_do_objetivo(): void
    {
        $dados = $this->criarDadosTeste();

        $this->actingAs($dados['user']);

        // Testar filtro pelo objetivo 1 - deve retornar 2 indicadores diretos
        $component = Livewire::test(ListarIndicadores::class)
            ->set('filtroObjetivo', $dados['objetivo1']->cod_objetivo_estrategico);

        // Deve encontrar os 2 indicadores diretos do objetivo 1
        $indicadores = $component->viewData('indicadores');

        $this->assertGreaterThanOrEqual(2, $indicadores->count(),
            'Deve retornar ao menos 2 indicadores para o objetivo 1');

        // Verifica que os indicadores retornados sao do objetivo correto
        foreach ($indicadores as $indicador) {
            $pertenceAoObjetivo =
                $indicador->cod_objetivo_estrategico === $dados['objetivo1']->cod_objetivo_estrategico ||
                ($indicador->planoDeAcao &&
                 $indicador->planoDeAcao->cod_objetivo_estrategico === $dados['objetivo1']->cod_objetivo_estrategico);

            $this->assertTrue($pertenceAoObjetivo,
                "Indicador {$indicador->nom_indicador} nao pertence ao objetivo filtrado");
        }
    }

    /**
     * Testa se o filtro por objetivo inclui indicadores de planos de acao do objetivo
     */
    public function test_filtro_objetivo_inclui_indicadores_de_planos(): void
    {
        $dados = $this->criarDadosTeste();

        $this->actingAs($dados['user']);

        $component = Livewire::test(ListarIndicadores::class)
            ->set('filtroObjetivo', $dados['objetivo1']->cod_objetivo_estrategico);

        $indicadores = $component->viewData('indicadores');
        $nomes = $indicadores->pluck('nom_indicador')->toArray();

        // Deve incluir o indicador vinculado ao plano de acao
        $this->assertContains('Indicador do Plano de Acao (Objetivo 1)', $nomes,
            'Deve incluir indicadores vinculados a planos de acao do objetivo');
    }

    /**
     * Testa se o filtro por objetivo 2 nao retorna indicadores do objetivo 1
     */
    public function test_filtro_objetivo_nao_retorna_indicadores_de_outro_objetivo(): void
    {
        $dados = $this->criarDadosTeste();

        $this->actingAs($dados['user']);

        $component = Livewire::test(ListarIndicadores::class)
            ->set('filtroObjetivo', $dados['objetivo2']->cod_objetivo_estrategico);

        $indicadores = $component->viewData('indicadores');

        // Deve retornar apenas 1 indicador (do objetivo 2)
        $this->assertEquals(1, $indicadores->count(),
            'Deve retornar apenas 1 indicador para o objetivo 2');

        // Verifica que o indicador retornado e do objetivo 2
        $indicador = $indicadores->first();
        $this->assertEquals($dados['objetivo2']->cod_objetivo_estrategico,
            $indicador->cod_objetivo_estrategico,
            'O indicador retornado deve ser do objetivo 2');
    }

    /**
     * Testa se sem filtro retorna todos os indicadores
     */
    public function test_sem_filtro_retorna_todos_indicadores(): void
    {
        $dados = $this->criarDadosTeste();

        $this->actingAs($dados['user']);

        $component = Livewire::test(ListarIndicadores::class);

        $indicadores = $component->viewData('indicadores');

        // Deve retornar todos os 4 indicadores
        $this->assertGreaterThanOrEqual(4, $indicadores->count(),
            'Sem filtro deve retornar todos os indicadores');
    }

    /**
     * Testa combinacao de filtro por objetivo e busca textual
     */
    public function test_filtro_objetivo_com_busca_textual(): void
    {
        $dados = $this->criarDadosTeste();

        $this->actingAs($dados['user']);

        $component = Livewire::test(ListarIndicadores::class)
            ->set('filtroObjetivo', $dados['objetivo1']->cod_objetivo_estrategico)
            ->set('search', 'Direto 1');

        $indicadores = $component->viewData('indicadores');

        // Deve retornar apenas 1 indicador que atende aos dois criterios
        $this->assertEquals(1, $indicadores->count(),
            'Deve retornar apenas indicadores que atendem a ambos os filtros');

        $this->assertEquals('Indicador Direto 1 do Objetivo 1',
            $indicadores->first()->nom_indicador);
    }
}
