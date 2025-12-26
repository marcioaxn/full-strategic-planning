<?php

namespace App\Livewire\Indicador;

use App\Models\PEI\Indicador;
use App\Models\PEI\EvolucaoIndicador;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('layouts.app')]
class DetalharIndicador extends Component
{
    use AuthorizesRequests;

    public $indicador;
    public $chartData = [];
    public $anoFiltro;

    public function mount($indicadorId)
    {
        $this->indicador = Indicador::with(['objetivoEstrategico', 'planoDeAcao', 'evolucoes', 'metasPorAno', 'linhaBase', 'organizacoes'])
            ->findOrFail($indicadorId);
        
        $this->authorize('view', $this->indicador);
        
        $this->anoFiltro = now()->year;
        $this->prepararDadosGrafico();
    }

    public function updatedAnoFiltro()
    {
        $this->prepararDadosGrafico();
    }

    public function prepararDadosGrafico()
    {
        $evolucoes = EvolucaoIndicador::where('cod_indicador', $this->indicador->cod_indicador)
            ->where('num_ano', $this->anoFiltro)
            ->orderBy('num_mes')
            ->get();

        $labels = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $previsto = array_fill(0, 12, null);
        $realizado = array_fill(0, 12, null);

        foreach ($evolucoes as $ev) {
            $previsto[$ev->num_mes - 1] = $ev->vlr_previsto;
            $realizado[$ev->num_mes - 1] = $ev->vlr_realizado;
        }

        $this->chartData = [
            'labels' => $labels,
            'previsto' => $previsto,
            'realizado' => $realizado,
        ];
        
        // Emitir evento para o Chart.js atualizar
        $this->dispatch('updateChart', data: $this->chartData);
    }

    public function render()
    {
        return view('livewire.indicador.detalhar-indicador');
    }
}