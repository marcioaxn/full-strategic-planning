<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\TemaNorteador;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetalharPei extends Component
{
    public $pei;
    public $estatisticas = [];

    public function mount($id)
    {
        $this->pei = PEI::with(['identidadeEstrategica', 'valores'])->findOrFail($id);
        $this->carregarEstatisticas();
    }

    public function carregarEstatisticas()
    {
        // IDs das perspectivas deste PEI
        $perspectivasIds = Perspectiva::where('cod_pei', $this->pei->cod_pei)->pluck('cod_perspectiva');

        // Contagem de Objetivos BSC (vinculados a perspectivas)
        $objetivosBscCount = Objetivo::whereIn('cod_perspectiva', $perspectivasIds)->count();

        // Contagem de Temas Norteadores (vinculados diretamente ao PEI)
        $temasNorteadoresCount = TemaNorteador::where('cod_pei', $this->pei->cod_pei)->count();
        
        $this->estatisticas = [
            'qtd_perspectivas' => $perspectivasIds->count(),
            'qtd_objetivos_bsc' => $objetivosBscCount,
            'qtd_temas_norteadores' => $temasNorteadoresCount,
            'qtd_valores' => $this->pei->valores->count(),
        ];
    }

    public function render()
    {
        return view('livewire.p-e-i.detalhar-pei');
    }
}