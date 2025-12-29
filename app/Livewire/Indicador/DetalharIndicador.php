<?php

namespace App\Livewire\Indicador;

use App\Models\PEI\Indicador;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetalharIndicador extends Component
{
    public $indicador;

    public function mount($id)
    {
        $this->indicador = Indicador::with(['objetivo', 'planoDeAcao', 'evolucoes', 'metasPorAno', 'linhaBase', 'organizacoes'])
            ->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.indicador.detalhar-indicador');
    }
}
