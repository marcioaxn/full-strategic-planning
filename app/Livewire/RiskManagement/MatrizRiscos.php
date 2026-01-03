<?php

namespace App\Livewire\RiskManagement;

use App\Models\Risco;
use App\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class MatrizRiscos extends Component
{
    public $organizacaoId;
    public $matriz = [];

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarMatriz'
    ];

    public function mount()
    {
        $this->organizacaoId = Session::get('organizacao_selecionada_id');
        $this->carregarMatriz();
    }

    public function atualizarMatriz($id)
    {
        $this->organizacaoId = $id;
        $this->carregarMatriz();
    }

    public function carregarMatriz()
    {
        $this->matriz = [];
        
        // Inicializar matriz vazia 5x5
        for ($i=5; $i>=1; $i--) {
            for ($j=1; $j<=5; $j++) {
                $this->matriz[$i][$j] = [];
            }
        }

        $query = Risco::query();
        if ($this->organizacaoId) {
            $query->where('cod_organizacao', $this->organizacaoId);
        }

        $riscos = $query->get();

        foreach ($riscos as $risco) {
            $this->matriz[$risco->num_impacto][$risco->num_probabilidade][] = $risco;
        }
    }

    public function render()
    {
        return view('livewire.risco.matriz-riscos', [
            'organizacaoNome' => Session::get('organizacao_selecionada_sgl', 'Global')
        ]);
    }
}
