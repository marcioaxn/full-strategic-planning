<?php

namespace Tests\Feature;

use App\Livewire\PerformanceIndicators\ListarIndicadores;
use App\Models\Organization;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ListarIndicadoresTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $organizacao;

    protected $pei;

    protected $perspectiva;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['ativo' => true]);
        $this->organizacao = Organization::create([
            'nom_organizacao' => 'Org Teste',
            'sgl_organizacao' => 'OT',
            'cod_organizacao_pai' => null,
        ]);

        $this->user->organizacoes()->attach($this->organizacao->cod_organizacao);

        $this->pei = PEI::create([
            'dsc_pei' => 'Ciclo Teste',
            'num_ano_inicio_pei' => 2024,
            'num_ano_fim_pei' => 2027,
            'bln_ativo' => true,
        ]);

        $this->perspectiva = Perspectiva::create([
            'cod_pei' => $this->pei->cod_pei,
            'dsc_perspectiva' => 'Perspectiva Teste',
            'num_nivel_hierarquico_apresentacao' => 1,
        ]);
    }

    public function test_pode_renderizar_lista_de_indicadores()
    {
        $this->actingAs($this->user);
        session(['organizacao_selecionada_id' => $this->organizacao->cod_organizacao]);

        Livewire::test(ListarIndicadores::class)
            ->assertStatus(200)
            ->assertSee('Indicadores de Desempenho');
    }

    public function test_pode_filtrar_indicadores_por_nome()
    {
        $this->actingAs($this->user);
        session(['organizacao_selecionada_id' => $this->organizacao->cod_organizacao]);

        $objetivo = Objetivo::create([
            'cod_perspectiva' => $this->perspectiva->cod_perspectiva,
            'nom_objetivo' => 'Objetivo Teste',
            'dsc_objetivo' => 'Descrição do objetivo de teste',
            'num_nivel_hierarquico_apresentacao' => 1,
        ]);

        $alpha = Indicador::create([
            'cod_objetivo' => $objetivo->cod_objetivo,
            'nom_indicador' => 'Indicador Alpha',
            'dsc_indicador' => 'Descrição do indicador Alpha',
            'dsc_unidade_medida' => 'Unidade',
            'dsc_tipo' => 'Efetividade',
            'bln_acumulado' => false,
            'dsc_periodo_medicao' => 'mensal',
        ]);
        $alpha->organizacoes()->attach($this->organizacao->cod_organizacao);

        $beta = Indicador::create([
            'cod_objetivo' => $objetivo->cod_objetivo,
            'nom_indicador' => 'Indicador Beta',
            'dsc_indicador' => 'Descrição do indicador Beta',
            'dsc_unidade_medida' => 'Unidade',
            'dsc_tipo' => 'Efetividade',
            'bln_acumulado' => false,
            'dsc_periodo_medicao' => 'mensal',
        ]);
        $beta->organizacoes()->attach($this->organizacao->cod_organizacao);

        Livewire::test(ListarIndicadores::class)
            ->set('search', 'Alpha')
            ->assertSee('Indicador Alpha')
            ->assertDontSee('Indicador Beta');
    }
}
