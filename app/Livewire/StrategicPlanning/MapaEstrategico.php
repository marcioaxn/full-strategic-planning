<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\MissaoVisaoValores;
use App\Models\StrategicPlanning\Valor;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\TemaNorteador;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MapaEstrategico extends Component
{
    public $perspectivas = [];
    public $peiAtivo;
    public $organizacaoId;
    public $organizacaoNome;
    public $missaoVisao;
    public $valores = [];
    public $temasNorteadores = [];
    public $grausSatisfacao = [];
    
    // Estado simples sem #[Url] para garantir reatividade interna pura
    public string $viewMode = 'grouped'; 

    public bool $showCalcModal = false;
    public $detalhesCalculo = null;

    public $coresPerspectivas = [
        1 => ['bg' => 'bg-slate', 'border' => 'border-secondary', 'text' => 'text-white', 'bg_light' => 'bg-secondary-subtle'],
        2 => ['bg' => 'bg-success', 'border' => 'border-success', 'text' => 'text-white', 'bg_light' => 'bg-success-subtle'],
        3 => ['bg' => 'bg-info', 'border' => 'border-info', 'text' => 'text-white', 'bg_light' => 'bg-info-subtle'],
        4 => ['bg' => 'bg-warning', 'border' => 'border-warning', 'text' => 'text-dark', 'bg_light' => 'bg-warning-subtle'],
        5 => ['bg' => 'bg-primary', 'border' => 'border-primary', 'text' => 'text-white', 'bg_light' => 'bg-primary-subtle'],
    ];

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI',
        'anoSelecionado' => '$refresh'
    ];

    public function mount()
    {
        $this->organizacaoId = Session::get('organizacao_selecionada_id');
        if (!$this->organizacaoId) {
            $orgRaiz = Organization::whereColumn('cod_organizacao', 'rel_cod_organizacao')->first() 
                       ?? Organization::orderBy('sgl_organizacao')->first();
            $this->organizacaoId = $orgRaiz?->cod_organizacao;
        }
        $this->carregarPEI();
    }

    public function atualizarOrganizacao($id) { $this->organizacaoId = $id; }
    public function atualizarPEI($id) { $this->peiAtivo = PEI::find($id); }

    private function carregarPEI()
    {
        $peiId = Session::get('pei_selecionado_id');
        $this->peiAtivo = $peiId ? PEI::find($peiId) : PEI::ativos()->first();
    }

    public function carregarMapa()
    {
        if (!$this->peiAtivo || !$this->organizacaoId) return;

        // Determinar IDs de organizações conforme o modo
        $orgIds = [$this->organizacaoId];
        if ($this->viewMode === 'grouped') {
            $org = Organization::find($this->organizacaoId);
            if ($org) {
                $orgIds = $org->getDescendantsAndSelfIds();
            }
        }

        $this->grausSatisfacao = GrauSatisfacao::orderBy('vlr_minimo')->get();

        $this->perspectivas = Perspectiva::where('cod_pei', $this->peiAtivo->cod_pei)
            ->with(['objetivos' => function($query) use ($orgIds) {
                $query->with(['indicadores' => function($qInd) use ($orgIds) {
                    $qInd->whereHas('organizacoes', function($q) use ($orgIds) {
                        $q->whereIn('tab_organizacoes.cod_organizacao', $orgIds);
                    });
                }, 'planosAcao' => function($qPlan) use ($orgIds) {
                    $qPlan->whereHas('organizacoes', function($q) use ($orgIds) {
                        $q->whereIn('tab_organizacoes.cod_organizacao', $orgIds);
                    });
                }])->ordenadoPorNivel();
            }])
            ->orderBy('num_nivel_hierarquico_apresentacao', 'desc')
            ->get()
            ->map(function($p) {
                $somaPersp = 0;
                $contPersp = 0;
                $listaIndicadoresMemoria = [];

                foreach ($p->objetivos as $obj) {
                    $indicadores = $obj->indicadores;
                    $totalInd = $indicadores->count();
                    $somaAtingObj = 0;
                    
                    foreach ($indicadores as $ind) {
                        $ating = $ind->calcularAtingimento();
                        $somaAtingObj += $ating;
                        $somaPersp += $ating;
                        $contPersp++;
                        
                        $listaIndicadoresMemoria[] = [
                            'objetivo' => $obj->nom_objetivo,
                            'indicador' => $ind->nom_indicador,
                            'atingimento' => round($ating, 1),
                            'cor' => $this->getCorPorPercentual($ating),
                            'polaridade' => $ind->dsc_polaridade ?? 'Positiva'
                        ];
                    }
                    
                    $mediaAtingObj = $totalInd > 0 ? round($somaAtingObj / $totalInd, 1) : 0;
                    $obj->resumo_indicadores = [
                        'quantidade' => $totalInd,
                        'percentual' => $mediaAtingObj,
                        'cor' => $this->getCorPorPercentual($mediaAtingObj)
                    ];

                    $planos = $obj->planosAcao;
                    $totalPlanos = $planos->count();
                    $concluidos = $planos->where('bln_status', 'Concluído')->count();
                    
                    $corPlano = '#475569';
                    if ($totalPlanos > 0) {
                        if ($concluidos == $totalPlanos) $corPlano = '#429B22';
                        else if ($planos->whereIn('bln_status', ['Em Andamento', 'Atrasado'])->count() > 0) $corPlano = '#F3C72B';
                    }

                    $obj->resumo_planos = [
                        'quantidade' => $totalPlanos,
                        'concluidos' => $concluidos,
                        'percentual' => $totalPlanos > 0 ? round(($concluidos / $totalPlanos) * 100, 1) : 0,
                        'cor' => $corPlano
                    ];
                }
                
                $atingimentoPersp = $contPersp > 0 ? round($somaPersp / $contPersp, 1) : 0;
                $p->atingimento_medio = $atingimentoPersp;
                $p->cor_satisfacao = $this->getCorPorPercentual($atingimentoPersp);
                $p->memoria_indicadores = $listaIndicadoresMemoria;
                
                return $p;
            })->toArray();
    }

    public function carregarIdentidadeEstrategica()
    {
        if (!$this->peiAtivo) return;
        $this->missaoVisao = MissaoVisaoValores::where('cod_pei', $this->peiAtivo->cod_pei)->where('cod_organizacao', $this->organizacaoId)->first();
        $this->valores = Valor::where('cod_pei', $this->peiAtivo->cod_pei)->where('cod_organizacao', $this->organizacaoId)->orderBy('nom_valor')->get();
        $this->temasNorteadores = TemaNorteador::where('cod_pei', $this->peiAtivo->cod_pei)->where('cod_organizacao', $this->organizacaoId)->orderBy('created_at', 'asc')->get();
    }

    public function getCorPorPercentual($percentual): string
    {
        foreach ($this->grausSatisfacao as $grau) {
            if ($percentual >= $grau->vlr_minimo && $percentual <= $grau->vlr_maximo) return $grau->cor;
        }
        return '#dc3545';
    }

    public function getCoresPerspectiva($nivel): array { return $this->coresPerspectivas[$nivel] ?? $this->coresPerspectivas[1]; }

    public function render()
    {
        $this->carregarMapa();
        $this->carregarIdentidadeEstrategica();

        if ($this->organizacaoId) {
            $org = Organization::find($this->organizacaoId);
            $this->organizacaoNome = $org ? $org->nom_organizacao : 'SPS';
        }

        $layout = Auth::check() ? 'layouts.app' : 'layouts.public';
        return view('livewire.p-e-i.mapa-estrategico')->layout($layout);
    }
}
