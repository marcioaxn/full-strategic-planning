<?php

namespace App\Livewire\Dashboard;

use App\Models\PEI\PEI;
use App\Models\PEI\Objetivo;
use App\Models\PEI\Indicador;
use App\Models\PEI\PlanoDeAcao;
use App\Models\PEI\Entrega;
use App\Models\PEI\EntregaComentario;
use App\Models\PEI\Perspectiva;
use App\Models\Organization;
use App\Models\Risco;
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

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao'
    ];

    public function mount()
    {
        $this->organizacaoId = Session::get('organizacao_selecionada_id');
        $this->peiAtivo = PEI::ativos()->first();
        $this->carregarNomeOrganizacao();
    }

    public function atualizarOrganizacao($id)
    {
        $this->organizacaoId = $id;
        $this->carregarNomeOrganizacao();
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

    public function render()
    {
        $stats = $this->getStats();
        
        return view('livewire.dashboard.index', [
            'stats' => $stats,
            'minhasEntregas' => $this->getMinhasEntregas(),
            'comentariosRecentes' => $this->getComentariosRecentes(),
            'chartBSC' => $this->getChartBSC(),
            'chartRiscosNivel' => $this->getChartRiscosNivel(),
            'chartPlanos' => $this->getChartPlanos(),
        ]);
    }

    private function getStats()
    {
        $codPei = $this->peiAtivo?->cod_pei;

        // Cálculo de Progresso dos Planos
        $planosQuery = PlanoDeAcao::query();
        if ($this->organizacaoId) {
            $planosQuery->where('cod_organizacao', $this->organizacaoId);
        }
        $planos = $planosQuery->get();
        
        $totalProgresso = 0;
        foreach ($planos as $plano) {
            $totalProgresso += $plano->calcularProgressoEntregas();
        }
        $mediaProgresso = $planos->count() > 0 ? $totalProgresso / $planos->count() : 0;

        return [
            'totalObjetivos' => $this->peiAtivo ? Objetivo::whereHas('perspectiva', function($q) use ($codPei) {
                $q->where('cod_pei', $codPei);
            })->count() : 0,

            'progressoPlanos' => $mediaProgresso,

            'riscosCriticos' => $this->organizacaoId ? Risco::where('cod_organizacao', $this->organizacaoId)
                ->where('cod_pei', $codPei)
                ->criticos()
                ->count() : 0,
        ];
    }

    private function getMinhasEntregas()
    {
        return Entrega::whereHas('responsaveis', function($q) {
            $q->where('users.id', Auth::id());
        })
        ->where('bln_status', '!=', 'Concluído')
        ->with('planoDeAcao')
        ->orderBy('dte_prazo')
        ->get();
    }

    private function getComentariosRecentes()
    {
        return EntregaComentario::with(['usuario', 'entrega'])
            ->whereHas('entrega.planoDeAcao', function($q) {
                if ($this->organizacaoId) {
                    $q->where('cod_organizacao', $this->organizacaoId);
                }
            })
            ->latest()
            ->take(5)
            ->get();
    }

    private function getChartBSC()
    {
        if (!$this->peiAtivo) return [];

        return Perspectiva::where('cod_pei', $this->peiAtivo->cod_pei)
            ->orderBy('num_nivel_hierarquico_apresentacao')
            ->get()
            ->map(function($p) {
                // Cálculo simplificado de média por perspectiva para o gráfico
                $objetivos = $p->objetivos;
                $atingimentos = $objetivos->map(fn($o) => $o->calcularAtingimentoConsolidado());
                $media = $atingimentos->count() > 0 ? $atingimentos->avg() : 0;

                return [
                    'label' => $p->dsc_perspectiva,
                    'count' => round($media, 1),
                    'color' => $this->getCorAtingimento($media)
                ];
            });
    }

    private function getChartRiscosNivel()
    {
        if (!$this->organizacaoId) return ['labels' => [], 'data' => [], 'colors' => []];

        $riscos = Risco::where('cod_organizacao', $this->organizacaoId)
            ->where('cod_pei', $this->peiAtivo?->cod_pei)
            ->get();

        $niveis = [
            'Crítico' => ['count' => 0, 'color' => '#dc3545'],
            'Alto' => ['count' => 0, 'color' => '#fd7e14'],
            'Médio' => ['count' => 0, 'color' => '#ffc107'],
            'Baixo' => ['count' => 0, 'color' => '#198754'],
        ];

        foreach ($riscos as $r) {
            $label = $r->getNivelRiscoLabel();
            if (isset($niveis[$label])) {
                $niveis[$label]['count']++;
            }
        }

        return [
            'labels' => array_keys($niveis),
            'data' => array_column($niveis, 'count'),
            'colors' => array_column($niveis, 'color'),
        ];
    }

    private function getChartPlanos()
    {
        $planosQuery = PlanoDeAcao::query();
        if ($this->organizacaoId) {
            $planosQuery->where('cod_organizacao', $this->organizacaoId);
        }
        
        $planos = $planosQuery->get();
        
        $status = [
            'Concluído' => ['count' => 0, 'color' => '#198754'],
            'Em Andamento' => ['count' => 0, 'color' => '#0d6efd'],
            'Não Iniciado' => ['count' => 0, 'color' => '#6c757d'],
            'Atrasado' => ['count' => 0, 'color' => '#dc3545'],
        ];

        foreach ($planos as $p) {
            $st = $p->bln_status;
            if (isset($status[$st])) {
                $status[$st]['count']++;
            }
        }

        return collect($status)->map(fn($v, $k) => [
            'label' => $k,
            'count' => $v['count'],
            'color' => $v['color']
        ])->values()->toArray();
    }

    private function getCorAtingimento($percentual)
    {
        if ($percentual >= 100) return '#198754';
        if ($percentual >= 80) return '#0d6efd';
        if ($percentual >= 60) return '#0dcaf0';
        if ($percentual >= 40) return '#ffc107';
        return '#dc3545';
    }
}