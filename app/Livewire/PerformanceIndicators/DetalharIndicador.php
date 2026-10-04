<?php

namespace App\Livewire\PerformanceIndicators;

use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\PEI;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

// Sem #[Layout] fixo: o layout é escolhido no render(), porque esta tela
// também é servida ao visitante pelo Mapa Estratégico público.
class DetalharIndicador extends Component
{
    public Indicador $indicador;

    public int $anoFiltro;

    public array $chartData = [];

    public array $anosDisponiveis = [];

    protected $listeners = [
        'anoSelecionado' => 'atualizarAno',
    ];

    public function atualizarAno($ano)
    {
        $this->anoFiltro = (int) $ano;
        $this->ajustarAnoAoSeletor();
        $this->prepareChartData();
        $this->dispatch('updateChart', data: $this->chartData);
    }

    public function mount($id)
    {
        $this->indicador = Indicador::with([
            'objetivo.perspectiva',
            'planoDeAcao.objetivo.perspectiva',
            'evolucoes',
            'metasPorAno',
            'linhaBase',
            'organizacoes',
        ])->findOrFail($id);

        // Usa o ano selecionado no navbar (Ano de referência) ou ano atual como fallback
        $this->anoFiltro = (int) session('ano_selecionado', now()->year);

        $this->carregarAnosDisponiveis();
        $this->ajustarAnoAoSeletor();
        $this->prepareChartData();
    }

    /**
     * O ano tem de estar entre as opções do seletor. Senão o select MOSTRA um
     * ano (o primeiro da lista) e o gráfico desenha outro (o da sessão) — mesmo
     * defeito que LancarEvolucao já corrigia.
     */
    private function ajustarAnoAoSeletor(): void
    {
        if (! in_array($this->anoFiltro, $this->anosDisponiveis, true)) {
            $anoAtual = (int) now()->year;
            $this->anoFiltro = in_array($anoAtual, $this->anosDisponiveis, true) ? $anoAtual : (int) $this->anosDisponiveis[0];
        }
    }

    protected function carregarAnosDisponiveis()
    {
        // Busca anos dos PEIs para consistência com o seletor global
        $this->anosDisponiveis = PEI::orderBy('num_ano_inicio_pei', 'desc')
            ->get()
            ->flatMap(fn ($pei) => range((int) $pei->num_ano_fim_pei, (int) $pei->num_ano_inicio_pei))
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
        $this->ajustarAnoAoSeletor();
        $this->prepareChartData();
        $this->dispatch('updateChart', data: $this->chartData);
    }

    protected function prepareChartData()
    {
        $meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $previsto = [];
        $realizado = [];

        $evolucoes = $this->indicador->evolucoes
            ->where('num_ano', (int) $this->anoFiltro)
            ->keyBy('num_mes');

        for ($i = 1; $i <= 12; $i++) {
            $ev = $evolucoes->get($i);
            $vlrPrevisto = $ev?->vlr_previsto;
            $vlrRealizado = $ev?->vlr_realizado;

            // Se não houver evolução lançada mas houver meta anual, poderíamos sugerir o previsto proporcional?
            // Por enquanto mantemos fiel ao que está no banco, enviando null para o Chart.js
            $previsto[] = $vlrPrevisto !== null ? (float) $vlrPrevisto : null;
            $realizado[] = $vlrRealizado !== null ? (float) $vlrRealizado : null;
        }

        $this->chartData = [
            'labels' => $meses,
            'previsto' => $previsto,
            'realizado' => $realizado,
            'ano' => (int) $this->anoFiltro,
        ];
    }

    public function render()
    {
        // Layout dinâmico: o visitante chega aqui pelo Mapa Estratégico público
        // e não tem menu autenticado. Mesmo critério do MapaEstrategico.
        return view('livewire.indicador.detalhar-indicador')
            ->layout(Auth::check() ? 'layouts.app' : 'layouts.public');
    }
}
