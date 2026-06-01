<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\MissaoVisaoValores;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\Organization;

#[Layout('layouts.public')]
class LandingPage extends Component
{
    public bool $temDados    = false;
    public $pei              = null;
    public $organizacao      = null;
    public $identidade       = null;
    public $perspectivas     = null;
    public array $stats      = [];
    public $grausSatisfacao  = null;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirect(route('dashboard'), navigate: true);
            return;
        }

        $this->carregarDados();
    }

    private function carregarDados(): void
    {
        try {
            // Cache leve de 5 min para não onerar a DB em cada visita
            $cache = Cache::remember('lp_dados_publicos', 300, function () {
                $pei = PEI::ativos()->first();

                if (!$pei) {
                    return ['temDados' => false];
                }

                $graus = GrauSatisfacao::orderBy('vlr_minimo')->get();

                $calcularCor = function (float $pct) use ($graus): string {
                    foreach ($graus as $g) {
                        if ($pct >= $g->vlr_minimo && $pct <= $g->vlr_maximo) return $g->cor;
                    }
                    return '#dee2e6';
                };

                $org        = Organization::whereColumn('cod_organizacao', 'rel_cod_organizacao')->first()
                           ?? Organization::first();
                $identidade = MissaoVisaoValores::where('cod_pei', $pei->cod_pei)->first();
                $anoAtual   = (int) date('Y');

                // Perspectivas com objetivos (desc: nível mais alto no topo)
                $perspectivas = Perspectiva::where('cod_pei', $pei->cod_pei)
                    ->with(['objetivos'])
                    ->orderBy('num_nivel_hierarquico_apresentacao', 'desc')
                    ->get()
                    ->map(function ($p) use ($anoAtual, $calcularCor) {
                        // Pré-calcula o atingimento de cada objetivo UMA vez e anexa ao objeto
                        // (evita N+1 na renderização do Mapa Estratégico e do Panorama).
                        $p->objetivos->each(function ($o) use ($anoAtual, $calcularCor) {
                            $o->lp_atingimento = round($o->calcularAtingimentoConsolidado($anoAtual), 1);
                            $o->lp_cor         = $calcularCor($o->lp_atingimento);
                        });

                        $ats = $p->objetivos->pluck('lp_atingimento')->filter()->values();

                        $p->atingimento_medio = $ats->count() > 0 ? round($ats->avg(), 1) : 0;
                        $p->cor_atingimento   = $calcularCor($p->atingimento_medio);
                        $p->objetivos_abaixo  = $p->objetivos->filter(fn ($o) => $o->lp_atingimento < 50)->count();

                        return $p;
                    });

                // Atingimento global
                $medias = $perspectivas->pluck('atingimento_medio')->filter(fn ($v) => $v > 0);
                $globalAt = $medias->count() > 0 ? round($medias->avg(), 1) : 0;

                // Contagens (lightweight)
                $totalObjs = $perspectivas->sum(fn ($p) => $p->objetivos->count());

                $totalInds = \App\Models\PerformanceIndicators\Indicador::whereHas(
                    'objetivo.perspectiva', fn ($q) => $q->where('cod_pei', $pei->cod_pei)
                )->count();

                $totalPlanos = \App\Models\ActionPlan\PlanoDeAcao::whereHas(
                    'objetivo.perspectiva', fn ($q) => $q->where('cod_pei', $pei->cod_pei)
                )->count();

                $riscosCrit = \App\Models\RiskManagement\Risco::where('cod_pei', $pei->cod_pei)
                    ->where('num_nivel_risco', '>=', 16)->count();

                return [
                    'temDados'     => true,
                    'pei'          => $pei,
                    'organizacao'  => $org,
                    'identidade'   => $identidade,
                    'perspectivas' => $perspectivas,
                    'graus'        => $graus,
                    'stats'        => [
                        'atingimentoGlobal' => $globalAt,
                        'corGlobal'         => $calcularCor($globalAt),
                        'perspectivas'      => $perspectivas->count(),
                        'objetivos'         => $totalObjs,
                        'indicadores'       => $totalInds,
                        'planos'            => $totalPlanos,
                        'riscosCriticos'    => $riscosCrit,
                    ],
                ];
            });

            if ($cache['temDados']) {
                $this->temDados      = true;
                $this->pei           = $cache['pei'];
                $this->organizacao   = $cache['organizacao'];
                $this->identidade    = $cache['identidade'];
                $this->perspectivas  = $cache['perspectivas'];
                $this->grausSatisfacao = $cache['graus'];
                $this->stats         = $cache['stats'];
            }

        } catch (\Throwable $e) {
            $this->temDados = false;
        }
    }

    public function render()
    {
        return view('livewire.landing-page');
    }
}
