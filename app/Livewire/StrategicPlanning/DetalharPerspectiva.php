<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\Perspectiva;
use App\Services\IndicadorCalculoService;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetalharPerspectiva extends Component
{
    public $perspectiva;

    public $estatisticas = [];

    /** @var array<string, array{atingimento: float|null, faixa: string|null, cor: string|null}> */
    public array $desempenhoObjetivos = [];

    public int $ano;

    public function mount($id)
    {
        $this->authorize('modulo.acessar', 'planejamento-estrategico');

        $this->perspectiva = Perspectiva::with(['pei', 'objetivos.indicadores', 'objetivos.planosAcao.entregas'])->findOrFail($id);
        $this->ano = (int) Session::get('ano_selecionado', now()->year);

        $qtdObjetivos = $this->perspectiva->objetivos->count();
        $qtdIndicadores = $this->perspectiva->objetivos->sum(fn ($obj) => $obj->indicadores->count());

        // O status de cada objetivo era o texto fixo "Não iniciado" e o
        // desempenho da perspectiva, "--% em breve". Agora os dois vêm do
        // mesmo cálculo do Mapa Estratégico; sem indicador, a tela diz isso.
        $codPei = $this->perspectiva->cod_pei;
        foreach ($this->perspectiva->objetivos as $objetivo) {
            $temIndicador = $objetivo->indicadores->isNotEmpty()
                || Indicador::whereHas('planoDeAcao', fn ($q) => $q->where('cod_objetivo', $objetivo->cod_objetivo))->exists();

            if (! $temIndicador) {
                $this->desempenhoObjetivos[$objetivo->cod_objetivo] = ['atingimento' => null, 'faixa' => null, 'cor' => null];

                continue;
            }

            $atingimento = round($objetivo->calcularAtingimentoConsolidado($this->ano), 1);
            $faixa = GrauSatisfacao::faixaDe($atingimento, $codPei, $this->ano);
            $this->desempenhoObjetivos[$objetivo->cod_objetivo] = [
                'atingimento' => $atingimento,
                'faixa' => $faixa?->dsc_grau_satisfacao,
                'cor' => GrauSatisfacao::corDe($atingimento, $codPei, $this->ano),
            ];
        }

        $this->estatisticas = [
            'qtd_objetivos' => $qtdObjetivos,
            'qtd_indicadores' => $qtdIndicadores,
            'progresso_medio' => $qtdIndicadores > 0
                ? round(app(IndicadorCalculoService::class)->calcularAtingimentoPerspectiva($this->perspectiva, $this->ano), 1)
                : null,
        ];
    }

    public function render()
    {
        return view('livewire.p-e-i.detalhar-perspectiva');
    }
}
