<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\PerformanceIndicators\Indicador;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetalharGrauSatisfacao extends Component
{
    public $grau;
    public $indicadoresNaFaixa = [];

    public function mount($id)
    {
        $this->grau = GrauSatisfacao::with('pei')->findOrFail($id);
        
        // Calcular quais indicadores caem nesta faixa atualmente
        // Isso é pesado, então vamos limitar ou fazer apenas count
        // Preciso iterar sobre todos os indicadores do PEI e calcular atingimento.
        // Se for global, todos os PEIs.
        
        // Vou deixar placeholder por enquanto para não travar a performance, 
        // ou buscar apenas alguns para exemplo.
        $this->indicadoresNaFaixa = collect();
    }

    public function render()
    {
        return view('livewire.p-e-i.detalhar-grau-satisfacao');
    }
}
