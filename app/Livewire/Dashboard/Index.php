<?php

namespace App\Livewire\Dashboard;

use App\Models\PEI\PEI;
use App\Models\PEI\Objetivo;
use App\Models\PEI\Indicador;
use App\Models\PEI\PlanoDeAcao;
use App\Models\PEI\Entrega;
use App\Models\PEI\EntregaComentario;
use App\Models\PEI\Perspectiva;
use App\Models\PEI\GrauSatisfacao;
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
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI',
        'anoSelecionado' => 'atualizarAno'
    ];

    public function mount()
    {
        $this->organizacaoId = Session::get('organizacao_selecionada_id');
        $this->carregarPEI();
        $this->carregarNomeOrganizacao();
    }

    public function atualizarAno($ano)
    {
        // O ano já está na sessão, basta recarregar os dados do componente
        $this->dispararAtualizacaoGraficos();
    }

    public function atualizarOrganizacao($id)
    {
        $this->organizacaoId = $id;
        $this->carregarNomeOrganizacao();
        $this->dispararAtualizacaoGraficos();
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->dispararAtualizacaoGraficos();
    }

    private function dispararAtualizacaoGraficos()
    {
        $this->dispatch('charts-updated', [
            'bsc' => $this->getChartBSC(),
            'riscos' => $this->getChartRiscosNivel(),
            'planos' => $this->getChartPlanos(),
        ]);
    }

    private function carregarPEI()
    {
        $peiId = Session::get('pei_selecionado_id');

        if ($peiId) {
            $this->peiAtivo = PEI::find($peiId);
        }

        // Fallback: se não há PEI na sessão, pega o primeiro ativo
        if (!$this->peiAtivo) {
            $this->peiAtivo = PEI::ativos()->first();
        }
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
            'entregasAgrupadas' => $this->getMinhasEntregasAgrupadas(),
            'comentariosRecentes' => $this->getComentariosRecentes(),
            'chartBSC' => $this->getChartBSC(),
            'chartRiscosNivel' => $this->getChartRiscosNivel(),
            'chartPlanos' => $this->getChartPlanos(),
        ]);
    }

    private function getStats()
    {
        $codPei = $this->peiAtivo?->cod_pei;

        // Dados dos Planos - FILTRA POR PEI via Objetivo → Perspectiva
        $planosQuery = PlanoDeAcao::query();
        if ($codPei) {
            $planosQuery->whereHas('objetivo.perspectiva', function($q) use ($codPei) {
                $q->where('cod_pei', $codPei);
            });
        }
        if ($this->organizacaoId) {
            $planosQuery->where('cod_organizacao', $this->organizacaoId);
        }
        $planos = $planosQuery->get();
        $totalPlanos = $planos->count();
        $planosConcluidos = $planos->where('bln_status', 'Concluído')->count();

        $totalProgresso = 0;
        foreach ($planos as $plano) {
            $totalProgresso += $plano->calcularProgressoEntregas();
        }
        $mediaProgresso = $totalPlanos > 0 ? $totalProgresso / $totalPlanos : 0;

        // Dados dos Objetivos
        $totalObjetivos = $this->peiAtivo ? Objetivo::whereHas('perspectiva', function($q) use ($codPei) {
            $q->where('cod_pei', $codPei);
        })->count() : 0;

        // Dados dos Indicadores
        $indicadoresQuery = Indicador::whereHas('objetivo.perspectiva', function($q) use ($codPei) {
            $q->where('cod_pei', $codPei);
        });
        $totalIndicadores = $indicadoresQuery->count();

        // Dados dos Riscos
        $riscosQuery = Risco::query();
        if ($this->organizacaoId) {
            $riscosQuery->where('cod_organizacao', $this->organizacaoId);
        }
        if ($codPei) {
            $riscosQuery->where('cod_pei', $codPei);
        }
        $totalRiscos = $riscosQuery->count();
        $riscosCriticos = (clone $riscosQuery)->criticos()->count();

        // Dados das Perspectivas
        $totalPerspectivas = $this->peiAtivo ? Perspectiva::where('cod_pei', $codPei)->count() : 0;

        return [
            // Objetivos
            'totalObjetivos' => $totalObjetivos,
            'totalPerspectivas' => $totalPerspectivas,
            'totalIndicadores' => $totalIndicadores,

            // Planos
            'progressoPlanos' => $mediaProgresso,
            'totalPlanos' => $totalPlanos,
            'planosConcluidos' => $planosConcluidos,

            // Riscos
            'riscosCriticos' => $riscosCriticos,
            'totalRiscos' => $totalRiscos,
        ];
    }

    private function getMinhasEntregas()
    {
        $codPei = $this->peiAtivo?->cod_pei;

        $query = Entrega::whereHas('responsaveis', function($q) {
            $q->where('users.id', Auth::id());
        })
        ->where('bln_status', '!=', 'Concluído')
        ->raiz()
        ->ativas()
        ->with(['planoDeAcao.objetivo']);

        // Filtra por PEI via PlanoDeAcao → Objetivo → Perspectiva
        if ($codPei) {
            $query->whereHas('planoDeAcao.objetivo.perspectiva', function($q) use ($codPei) {
                $q->where('cod_pei', $codPei);
            });
        }

        if ($this->organizacaoId) {
            $query->whereHas('planoDeAcao', function($q) {
                $q->where('cod_organizacao', $this->organizacaoId);
            });
        }

        return $query->orderBy('dte_prazo')->get();
    }

    private function getMinhasEntregasAgrupadas()
    {
        $entregas = $this->getMinhasEntregas();

        // Agrupa por Plano de Ação
        return $entregas->groupBy(function($entrega) {
            return $entrega->planoDeAcao->cod_plano_de_acao;
        })->map(function($grupo) {
            $plano = $grupo->first()->planoDeAcao;
            return [
                'plano' => $plano,
                'objetivo' => $plano->objetivo,
                'entregas' => $grupo,
                'total' => $grupo->count(),
            ];
        });
    }

    private function getComentariosRecentes()
    {
        $codPei = $this->peiAtivo?->cod_pei;

        $query = EntregaComentario::with(['usuario', 'entrega']);

        // Filtra por PEI via Entrega → PlanoDeAcao → Objetivo → Perspectiva
        if ($codPei) {
            $query->whereHas('entrega.planoDeAcao.objetivo.perspectiva', function($q) use ($codPei) {
                $q->where('cod_pei', $codPei);
            });
        }

        if ($this->organizacaoId) {
            $query->whereHas('entrega.planoDeAcao', function($q) {
                $q->where('cod_organizacao', $this->organizacaoId);
            });
        }

        return $query->latest()->take(5)->get();
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
        $codPei = $this->peiAtivo?->cod_pei;

        $planosQuery = PlanoDeAcao::query();

        // Filtra por PEI via Objetivo → Perspectiva
        if ($codPei) {
            $planosQuery->whereHas('objetivo.perspectiva', function($q) use ($codPei) {
                $q->where('cod_pei', $codPei);
            });
        }

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
        $grau = GrauSatisfacao::where('vlr_minimo', '<=', $percentual)
            ->where('vlr_maximo', '>=', $percentual)
            ->first();

        return $grau?->cor ?? '#6c757d';
    }
}