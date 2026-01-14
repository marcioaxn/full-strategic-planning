<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\Valor;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetalharValor extends Component
{
    public $valor;
    public $estatisticas = [];

    public function mount($id)
    {
        $this->valor = Valor::with(['pei', 'organizacao'])->findOrFail($id);
        
        // Futuramente carregar estatísticas reais de uso
        $this->estatisticas = [
            'referencias' => 0, // Ex: Objetivos que citam este valor
            'acoes' => 0, // Ex: Ações alinhadas
        ];
    }

    public function render()
    {
        return view('livewire.p-e-i.detalhar-valor');
    }
}
