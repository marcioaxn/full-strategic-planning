<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\Objetivo;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetalharObjetivo extends Component
{
    public $objetivo;
    public $estatisticas = [];
    public $novoComentario = '';

    public function mount($id)
    {
        $this->carregarObjetivo($id);
    }

    public function carregarObjetivo($id)
    {
        $this->objetivo = Objetivo::with([
            'perspectiva.pei', 
            'indicadores', 
            'planosAcao',
            'futuroAlmejado',
            'comentarios.user'
        ])->findOrFail($id);
        
        $this->estatisticas = [
            'atingimento' => $this->objetivo->calcularAtingimentoConsolidado(),
            'cor_farol' => $this->objetivo->getCorFarolConsolidado(),
            'qtd_indicadores' => $this->objetivo->indicadores->count(),
            'qtd_planos' => $this->objetivo->planosAcao->count(),
        ];
    }

    public function postarComentario()
    {
        $this->validate(['novoComentario' => 'required|string|min:3']);

        ObjetivoComentario::create([
            'cod_objetivo' => $this->objetivo->cod_objetivo,
            'user_id' => auth()->id(),
            'dsc_comentario' => $this->novoComentario,
        ]);

        $this->novoComentario = '';
        $this->carregarObjetivo($this->objetivo->cod_objetivo);
        
        $this->dispatch('notify', message: 'Comentário postado!');
    }

    public function removerComentario($id)
    {
        $comentario = ObjetivoComentario::findOrFail($id);
        if ($comentario->user_id === auth()->id() || auth()->user()->isSuperAdmin()) {
            $comentario->delete();
            $this->carregarObjetivo($this->objetivo->cod_objetivo);
        }
    }

    public function render()
    {
        return view('livewire.p-e-i.detalhar-objetivo');
    }
}
