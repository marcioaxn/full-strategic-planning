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
        // Valor é da unidade: só abre para quem tem o módulo e a unidade no escopo.
        $this->authorize('modulo.acessar', 'planejamento-estrategico');

        $this->valor = Valor::with(['pei', 'organizacao'])->findOrFail($id);
        abort_unless(auth()->user()->podeAcessarOrganizacao($this->valor->cod_organizacao), 403);

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
