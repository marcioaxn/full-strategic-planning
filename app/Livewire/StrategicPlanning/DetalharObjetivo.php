<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\ObjetivoComentario;
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
            'planosAcao.entregas', // Preciso carregar entregas para o calculo funcionar se o service nao carregar
            'futuroAlmejado',
            'comentarios.user' // Assumindo que relacionamento existe, mas o código original tinha 'comentarios.user'
        ])->findOrFail($id);
        
        $service = app(\App\Services\IndicadorCalculoService::class);
        $ano = session('ano_selecionado', date('Y'));

        $atingimento = $service->calcularAtingimentoObjetivo($this->objetivo, $ano);

        $this->estatisticas = [
            'atingimento' => $atingimento,
            'cor_farol' => $this->getCorFarolManual($atingimento), // O método original getCorFarolConsolidado usava o valor interno. Tenho que ver se consigo usar o valor calculado.
            'qtd_indicadores' => $this->objetivo->indicadores->count(),
            'qtd_planos' => $this->objetivo->planosAcao->count(),
        ];
    }

    private function getCorFarolManual($val) {
        // Logica simplificada ou extraida do Model/Service. 
        // O Mapa usa getCorPorPercentual do Service/Component
        // Vou usar estático aqui para simplificar ou replicar a lógica do Mapa
        if ($val >= 100) return '#28a745'; // Success
        if ($val >= 70) return '#17a2b8'; // Info
        if ($val >= 40) return '#ffc107'; // Warning
        return '#dc3545'; // Danger
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
