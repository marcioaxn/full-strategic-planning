<?php

namespace App\Livewire\Shared;

use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\InauguraPei;
use App\Models\StrategicPlanning\MissaoVisaoValores;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\ActionPlan\PlanoDeAcao;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

class PeiProgressBar extends Component
{
    public $progresso = 0;
    public $steps = [];
    public $peiLabel = '';

    protected $listeners = [
        'peiSelecionado'         => '$refresh',
        'organizacaoSelecionada' => '$refresh',
    ];

    public function mount(): void
    {
        $this->calcular();
    }

    public function render()
    {
        $this->calcular();
        return view('livewire.shared.pei-progress-bar');
    }

    private function calcular(): void
    {
        $orgId  = Session::get('organizacao_selecionada_id');
        $peiId  = Session::get('pei_selecionado_id');
        $pei    = $peiId ? PEI::find($peiId) : PEI::ativos()->first();

        if (!$pei || !$orgId) {
            $this->progresso = 0;
            $this->steps = [];
            $this->peiLabel = $pei?->dsc_pei ?? '';
            return;
        }

        $codPei = $pei->cod_pei;
        $this->peiLabel = $pei->dsc_pei ?? '';

        try {
            $inaugurou = InauguraPei::where('cod_pei', $codPei)->whereNotNull('txt_equipe')->where('txt_equipe', '!=', '')->exists();
        } catch (\Exception) {
            $inaugurou = false;
        }

        $this->steps = [
            'inaugurar'   => $inaugurou,
            'identidade'  => MissaoVisaoValores::where('cod_organizacao', $orgId)->where('cod_pei', $codPei)->exists(),
            'perspectivas'=> Perspectiva::where('cod_pei', $codPei)->exists(),
            'objetivos'   => Objetivo::whereHas('perspectiva', fn($q) => $q->where('cod_pei', $codPei))->exists(),
            'graus'       => GrauSatisfacao::where('cod_pei', $codPei)->exists(),
            'indicadores' => Indicador::whereHas('objetivo.perspectiva', fn($q) => $q->where('cod_pei', $codPei))->exists(),
            'planos'      => PlanoDeAcao::whereHas('objetivo.perspectiva', fn($q) => $q->where('cod_pei', $codPei))->where('cod_organizacao', $orgId)->exists(),
        ];

        $concluidos = count(array_filter($this->steps));
        $total = count($this->steps);
        $this->progresso = $total > 0 ? (int) round(($concluidos / $total) * 100) : 0;
    }
}
