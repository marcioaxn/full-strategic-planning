<?php

namespace App\Livewire\PerformanceIndicators;

use App\Models\PEI\Indicador;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetalharIndicador extends Component
{
    public Indicador $indicador;
    public int $anoFiltro;
    public array $chartData = [];

    public function mount($id)
    {
        $this->indicador = Indicador::with([
            'objetivo',
            'planoDeAcao',
            'evolucoes',
            'metasPorAno',
            'linhaBase',
            'organizacoes'
        ])->findOrFail($id);

        $this->anoFiltro = now()->year;
        $this->prepareChartData();
    }

    public function updatedAnoFiltro()
    {
        $this->prepareChartData();
        $this->dispatch('updateChart', data: $this->chartData);
    }

    protected function prepareChartData()
    {
        $meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $previsto = [];
        $realizado = [];

        $evolucoes = $this->indicador->evolucoes
            ->where('num_ano', $this->anoFiltro)
            ->keyBy('num_mes');

        for ($i = 1; $i <= 12; $i++) {
            $ev = $evolucoes->get($i);
            $previsto[] = $ev?->vlr_previsto ?? null;
            $realizado[] = $ev?->vlr_realizado ?? null;
        }

        $this->chartData = [
            'labels' => $meses,
            'previsto' => $previsto,
            'realizado' => $realizado,
        ];
    }

    public function render()
    {
        return view('livewire.indicador.detalhar-indicador');
    }
}
