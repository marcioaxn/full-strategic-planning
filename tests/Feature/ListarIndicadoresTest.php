<?php

namespace Tests\Feature;

use App\Models\PEI\PEI;
use App\Models\PEI\Perspectiva;
use App\Models\PEI\Objetivo;
use App\Models\PEI\Indicador;
use App\Models\User;
use App\Models\Organization;
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

        $this->user = User::factory()->create();
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

        Livewire::test(\App\Livewire\Indicador\ListarIndicadores::class)
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
            'num_nivel_hierarquico_apresentacao' => 1,
        ]);

        Indicador::create([
            'cod_objetivo' => $objetivo->cod_objetivo,
            'nom_indicador' => 'Indicador Alpha',
            'dsc_unidade_medida' => 'Unidade',
        ]);

        Indicador::create([
            'cod_objetivo' => $objetivo->cod_objetivo,
            'nom_indicador' => 'Indicador Beta',
            'dsc_unidade_medida' => 'Unidade',
        ]);

        Livewire::test(\App\Livewire\Indicador\ListarIndicadores::class)
            ->set('search', 'Alpha')
            ->assertSee('Indicador Alpha')
            ->assertDontSee('Indicador Beta');
    }
}