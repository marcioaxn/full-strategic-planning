<?php

namespace App\Livewire\PlanoAcao;

use App\Models\PEI\PlanoDeAcao;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetalharPlano extends Component
{
    public $plano;

    public function mount($id)
    {
        $this->plano = PlanoDeAcao::with(['objetivo.perspectiva', 'tipoExecucao', 'organizacao', 'entregas', 'indicadores'])
            ->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.plano-acao.detalhar-plano');
    }
}
