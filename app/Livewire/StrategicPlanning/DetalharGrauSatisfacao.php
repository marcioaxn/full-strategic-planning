<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Support\CalculoPolaridade;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetalharGrauSatisfacao extends Component
{
    public $grau;

    public $indicadoresNaFaixa = [];

    public function mount($id)
    {
        $this->authorize('modulo.acessar', 'graus-satisfacao');

        $this->grau = GrauSatisfacao::with('pei')->findOrFail($id);
        $this->ano = (int) ($this->grau->num_ano ?? session('ano_selecionado', now()->year));

        // Indicadores do ciclo da faixa cujo atingimento no ano cai NESTA faixa.
        // A tela dizia "disponível em breve". A conta é a mesma do farol
        // (faixaDe), para a lista nunca discordar da cor que o mapa mostra.
        $codPei = $this->grau->cod_pei;
        if (! $codPei) {
            return;
        }

        $this->indicadoresNaFaixa = Indicador::with(['objetivo', 'evolucoes', 'metasPorAno'])
            ->where(function ($q) use ($codPei) {
                $q->whereHas('objetivo.perspectiva', fn ($p) => $p->where('cod_pei', $codPei))
                    ->orWhereHas('planoDeAcao.objetivo.perspectiva', fn ($p) => $p->where('cod_pei', $codPei));
            })
            ->orderBy('nom_indicador')
            ->get()
            ->reject(fn ($ind) => CalculoPolaridade::ehInformativo($ind->dsc_polaridade)
                || $ind->evolucoes->where('num_ano', $this->ano)->whereNotNull('vlr_realizado')->isEmpty())
            ->map(fn ($ind) => [
                'cod' => $ind->cod_indicador,
                'nome' => $ind->nom_indicador,
                'objetivo' => $ind->objetivo?->nom_objetivo,
                'atingimento' => round($ind->calcularAtingimento($this->ano), 1),
            ])
            ->filter(fn ($i) => GrauSatisfacao::faixaDe($i['atingimento'], $codPei, $this->ano)?->cod_grau_satisfacao === $this->grau->cod_grau_satisfacao)
            ->values()
            ->all();
    }

    public int $ano;

    public function render()
    {
        return view('livewire.p-e-i.detalhar-grau-satisfacao');
    }
}
