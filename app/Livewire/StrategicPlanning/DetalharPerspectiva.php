<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\Perspectiva;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetalharPerspectiva extends Component
{
    public $perspectiva;
    public $estatisticas = [];

    public function mount($id)
    {
        $this->perspectiva = Perspectiva::with(['pei', 'objetivos.indicadores'])->findOrFail($id);
        
        $qtdObjetivos = $this->perspectiva->objetivos->count();
        $qtdIndicadores = $this->perspectiva->objetivos->sum(fn($obj) => $obj->indicadores->count());

        $this->estatisticas = [
            'qtd_objetivos' => $qtdObjetivos,
            'qtd_indicadores' => $qtdIndicadores,
            'progresso_medio' => 0, // Implementar cálculo real depois
        ];
    }

    public function render()
    {
        return view('livewire.p-e-i.detalhar-perspectiva');
    }
}
