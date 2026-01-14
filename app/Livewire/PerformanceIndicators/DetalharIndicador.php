<?php

namespace App\Livewire\PerformanceIndicators;

use App\Models\PerformanceIndicators\Indicador;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetalharIndicador extends Component
{
    public Indicador $indicador;
    public int $anoFiltro;
    public array $chartData = [];
    public array $anosDisponiveis = [];

    protected $listeners = [
        'anoSelecionado' => 'atualizarAno'
    ];

    public function atualizarAno($ano)
    {
        $this->anoFiltro = (int) $ano;
        $this->prepareChartData();
        $this->dispatch('updateChart', data: $this->chartData);
    }

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

        // Usa o ano selecionado no navbar (Ano de referência) ou ano atual como fallback
        $this->anoFiltro = (int) session('ano_selecionado', now()->year);
        
        $this->carregarAnosDisponiveis();
        $this->prepareChartData();
    }

    protected function carregarAnosDisponiveis()
    {
        // Busca anos dos PEIs para consistência com o seletor global
        $this->anosDisponiveis = \App\Models\StrategicPlanning\PEI::orderBy('num_ano_inicio_pei', 'desc')
            ->get()
            ->flatMap(fn($pei) => range($pei->num_ano_fim_pei, $pei->num_ano_inicio_pei))
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        // Fallback se não houver PEIs
        if (empty($this->anosDisponiveis)) {
            $anoAtual = now()->year;
            $this->anosDisponiveis = [$anoAtual + 1, $anoAtual, $anoAtual - 1, $anoAtual - 2];
        }
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
            ->where('num_ano', (int)$this->anoFiltro)
            ->keyBy('num_mes');

        for ($i = 1; $i <= 12; $i++) {
            $ev = $evolucoes->get($i);
            $vlrPrevisto = $ev?->vlr_previsto;
            $vlrRealizado = $ev?->vlr_realizado;

            // Se não houver evolução lançada mas houver meta anual, poderíamos sugerir o previsto proporcional?
            // Por enquanto mantemos fiel ao que está no banco, enviando null para o Chart.js
            $previsto[] = $vlrPrevisto !== null ? (float)$vlrPrevisto : null;
            $realizado[] = $vlrRealizado !== null ? (float)$vlrRealizado : null;
        }

        $this->chartData = [
            'labels' => $meses,
            'previsto' => $previsto,
            'realizado' => $realizado,
            'ano' => (int)$this->anoFiltro
        ];
    }

    public function render()
    {
        return view('livewire.indicador.detalhar-indicador');
    }
}
