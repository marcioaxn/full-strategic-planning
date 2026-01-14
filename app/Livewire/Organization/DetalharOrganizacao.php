<?php

namespace App\Livewire\Organization;

use App\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetalharOrganizacao extends Component
{
    public $organizacao;
    public $estatisticas = [];

    public function mount($id)
    {
        $this->organizacao = Organization::with([
            'pai', 
            'filhas', 
            'usuarios', 
            'planosAcao',
            'valores',
            'identidadeEstrategica.pei' // Carregar PEI da identidade
        ])->findOrFail($id);
        
        // Contar indicadores via tabela pivô (se existir relacionamento definido no model)
        // O model Organization não tinha o método 'indicadores' explícito na minha leitura anterior, 
        // mas Indicador tem 'organizacoes'. Vou tentar acessar via query inversa se necessário.
        
        $qtdIndicadores = \App\Models\PerformanceIndicators\Indicador::whereHas('organizacoes', function($q) use ($id) {
            $q->where('tab_organizacoes.cod_organizacao', $id);
        })->count();

        $this->estatisticas = [
            'qtd_usuarios' => $this->organizacao->usuarios->count(),
            'qtd_filhas' => $this->organizacao->filhas->count(),
            'qtd_planos' => $this->organizacao->planosAcao->count(),
            'qtd_indicadores' => $qtdIndicadores,
        ];
    }

    public function render()
    {
        return view('livewire.organization.detalhar-organizacao');
    }
}
