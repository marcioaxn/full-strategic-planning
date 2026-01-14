<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\MissaoVisaoValores;
use App\Models\StrategicPlanning\Valor;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetalharIdentidade extends Component
{
    public $identidade;
    public $valores;

    public function mount($id)
    {
        $this->identidade = MissaoVisaoValores::with(['pei', 'organizacao', 'audits.user'])->findOrFail($id);
        
        // Carregar valores associados ao mesmo PEI e Organização
        $this->valores = Valor::where('cod_organizacao', $this->identidade->cod_organizacao)
            ->where('cod_pei', $this->identidade->cod_pei)
            ->get();
    }

    public function render()
    {
        return view('livewire.p-e-i.detalhar-identidade');
    }
}
