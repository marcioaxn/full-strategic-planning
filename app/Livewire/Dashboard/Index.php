<?php

namespace App\Livewire\Dashboard;

use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\EntregaComentario;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\Organization;
use App\Models\RiskManagement\Risco;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
class Index extends Component
{
    public $organizacaoId;
    public $organizacaoNome;
    public $peiAtivo;
    public $aiSummary = '';
    
    // Dados para os gráficos observados pelo AlpineJS
    public $chartData = [
        'bsc' => [],
        'riscos' => ['labels' => [], 'data' => [], 'colors' => []],
        'planos' => []
    ];

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI',
        'anoSelecionado' => 'atualizarAno'
    ];

    public function mount()
    {
        $this->organizacaoId = Session::get('organizacao_selecionada_id');
        $this->carregarPEI();
        $this->carregarNomeOrganizacao();
        $this->atualizarDadosGraficos();
    }

    public function atualizarAno($ano)
    {
        $this->atualizarDadosGraficos();
    }

    public function atualizarOrganizacao($id)
    {
        $this->organizacaoId = $id;
        $this->carregarNomeOrganizacao();
        $this->atualizarDadosGraficos();
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->atualizarDadosGraficos();
    }

    public function carregarPEI()
    {
        $peiId = Session::get('pei_selecionado_id');
        if ($peiId) {
            $this->peiAtivo = PEI::find($peiId);
        }
        if (!$this->peiAtivo) {
            $this->peiAtivo = PEI::ativos()->first();
        }
    }

    public function generateAiSummary()
    {
        $aiService = \App\Services\AI\AiServiceFactory::make();
        if (!$aiService) return;

        $this->aiSummary = 'Analisando dados estratégicos...';
        
        $stats = $this->getStats();
        $this->aiSummary = $aiService->summarizeStrategy($stats, $this->organizacaoNome);
    }

    private function carregarNomeOrganizacao()
    {
        if ($this->organizacaoId) {
            $org = Organization::find($this->organizacaoId);
            $this->organizacaoNome = $org ? $org->nom_organizacao : 'Unidade Não Encontrada';
        } else {
            $this->organizacaoNome = 'Todas as Unidades';
        }
    }

    public function atualizarDadosGraficos()
    {
        $this->chartData = [
            'bsc' => $this->getChartBSC(),
            'riscos' => $this->getChartRiscosNivel(),
            'planos' => $this->getChartPlanos(),
        ];
    }

    public function render()
    {
        // Atualiza os dados a cada renderização (incluindo poll)
        $this->atualizarDadosGraficos();

        return view('livewire.dashboard.index', [
            'stats' => $this->getStats(),
            'minhasEntregas' => $this->getMinhasEntregas(),
            'entregasAgrupadas' => $this->getMinhasEntregasAgrupadas(),
            'comentariosRecentes' => $this->getComentariosRecentes(),
        ]);
    }

    private function getStats()
    {
        $codPei = $this->peiAtivo?->cod_pei;
        $planosQuery = PlanoDeAcao::query();
        if ($codPei) {
            $planosQuery->whereHas('objetivo.perspectiva', fn($q) => $q->where('cod_pei', $codPei));
        }
        if ($this->organizacaoId) {
            $planosQuery->where('cod_organizacao', $this->organizacaoId);
        }
        $planos = $planosQuery->get();
        $totalProgresso = 0;
        foreach ($planos as $plano) {
            $totalProgresso += $plano->calcularProgressoEntregas();
        }
        
        return [
            'totalObjetivos' => $this->peiAtivo ? Objetivo::whereHas('perspectiva', fn($q) => $q->where('cod_pei', $codPei))->count() : 0,
            'totalPerspectivas' => $this->peiAtivo ? Perspectiva::where('cod_pei', $codPei)->count() : 0,
            'totalIndicadores' => Indicador::whereHas('objetivo.perspectiva', fn($q) => $q->where('cod_pei', $codPei))->count(),
            'progressoPlanos' => $planos->count() > 0 ? $totalProgresso / $planos->count() : 0,
            'totalPlanos' => $planos->count(),
            'planosConcluidos' => $planos->where('bln_status', 'Concluído')->count(),
            'riscosCriticos' => Risco::where('cod_organizacao', $this->organizacaoId)->where('cod_pei', $codPei)->criticos()->count(),
            'totalRiscos' => Risco::where('cod_organizacao', $this->organizacaoId)->where('cod_pei', $codPei)->count(),
        ];
    }

    private function getMinhasEntregas()
    {
        $query = Entrega::whereHas('responsaveis', fn($q) => $q->where('users.id', Auth::id()))
            ->where('bln_status', '!=', 'Concluído')
            ->raiz()->ativas()->with(['planoDeAcao.objetivo']);

        if ($this->peiAtivo) {
            $query->whereHas('planoDeAcao.objetivo.perspectiva', fn($q) => $q->where('cod_pei', $this->peiAtivo->cod_pei));
        }
        if ($this->organizacaoId) {
            $query->whereHas('planoDeAcao', fn($q) => $q->where('cod_organizacao', $this->organizacaoId));
        }
        return $query->orderBy('dte_prazo')->get();
    }

    private function getMinhasEntregasAgrupadas()
    {
        return $this->getMinhasEntregas()->groupBy(fn($e) => $e->planoDeAcao->cod_plano_de_acao)->map(fn($g) => [
            'plano' => $g->first()->planoDeAcao,
            'objetivo' => $g->first()->planoDeAcao->objetivo,
            'entregas' => $g,
            'total' => $g->count(),
        ]);
    }

    private function getComentariosRecentes()
    {
        $query = EntregaComentario::with(['usuario', 'entrega']);
        if ($this->peiAtivo) {
            $query->whereHas('entrega.planoDeAcao.objetivo.perspectiva', fn($q) => $q->where('cod_pei', $this->peiAtivo->cod_pei));
        }
        if ($this->organizacaoId) {
            $query->whereHas('entrega.planoDeAcao', fn($q) => $q->where('cod_organizacao', $this->organizacaoId));
        }
        return $query->latest()->take(5)->get();
    }

    private function getChartBSC()
    {
        if (!$this->peiAtivo) return [];
        return Perspectiva::where('cod_pei', $this->peiAtivo->cod_pei)
            ->orderBy('num_nivel_hierarquico_apresentacao')
            ->get()->map(function($p) {
                $atingimentos = $p->objetivos->map(fn($o) => $o->calcularAtingimentoConsolidado())->filter(fn($v) => $v > 0);
                $media = $atingimentos->count() > 0 ? $atingimentos->avg() : 0;
                return [
                    'label' => $p->dsc_perspectiva,
                    'count' => round($media, 1),
                    'color' => $this->getCorAtingimento($media)
                ];
            })->toArray();
    }

    private function getChartRiscosNivel()
    {
        $riscos = Risco::where('cod_organizacao', $this->organizacaoId)->where('cod_pei', $this->peiAtivo?->cod_pei)->get();
        $niveis = ['Crítico' => ['c' => 0, 'col' => '#dc3545'], 'Alto' => ['c' => 0, 'col' => '#fd7e14'], 'Médio' => ['c' => 0, 'col' => '#ffc107'], 'Baixo' => ['c' => 0, 'col' => '#198754']];
        foreach ($riscos as $r) { $l = $r->getNivelRiscoLabel(); if (isset($niveis[$l])) $niveis[$l]['c']++; }
        return ['labels' => array_keys($niveis), 'data' => array_column($niveis, 'c'), 'colors' => array_column($niveis, 'col')];
    }

    private function getChartPlanos()
    {
        $planosQuery = PlanoDeAcao::query();
        if ($this->peiAtivo) $planosQuery->whereHas('objetivo.perspectiva', fn($q) => $q->where('cod_pei', $this->peiAtivo->cod_pei));
        if ($this->organizacaoId) $planosQuery->where('cod_organizacao', $this->organizacaoId);
        $planos = $planosQuery->get();
        $status = ['Concluído' => ['c' => 0, 'col' => '#429B22'], 'Em Andamento' => ['c' => 0, 'col' => '#F3C72B'], 'Não Iniciado' => ['c' => 0, 'col' => '#475569'], 'Atrasado' => ['c' => 0, 'col' => '#dc3545']];
        foreach ($planos as $p) { $st = $p->bln_status; if (isset($status[$st])) $status[$st]['c']++; }
        return collect($status)->map(fn($v, $k) => ['label' => $k, 'count' => $v['c'], 'color' => $v['col']])->values()->toArray();
    }

    private function getCorAtingimento($percentual)
    {
        $grau = GrauSatisfacao::where('vlr_minimo', '<=', $percentual)->where('vlr_maximo', '>=', $percentual)->first();
        return $grau?->cor ?? '#6c757d';
    }
}