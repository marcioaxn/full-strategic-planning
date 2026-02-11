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
    public $anoSelecionado;
    
    // Dados para os gráficos observados pelo AlpineJS
    public $chartData = [
        'bsc' => [],
        'riscos' => ['labels' => [], 'data' => [], 'colors' => []],
        'planos' => [],
        'evolucao' => ['labels' => [], 'data' => []]
    ];

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI',
        'anoSelecionado' => 'atualizarAno'
    ];

    public function mount()
    {
        $this->anoSelecionado = Session::get('ano_selecionado', date('Y'));
        $this->organizacaoId = Session::get('organizacao_selecionada_id');
        $this->carregarPEI();
        $this->carregarNomeOrganizacao();
        $this->atualizarDadosGraficos();
    }

    public function atualizarAno($ano)
    {
        $this->anoSelecionado = $ano;
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
            'evolucao' => $this->getChartEvolucao(),
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
        $service = app(\App\Services\IndicadorCalculoService::class);
        
        // Buscar Planos
        $planosQuery = PlanoDeAcao::query();
        if ($codPei) {
            $planosQuery->whereHas('objetivo.perspectiva', fn($q) => $q->where('cod_pei', $codPei));
        }
        if ($this->organizacaoId) {
            $planosQuery->where('cod_organizacao', $this->organizacaoId);
        }
        
        // Filtrar planos que tenham vigência no ano selecionado
        $planosQuery->whereYear('dte_inicio', '<=', $this->anoSelecionado)
                    ->whereYear('dte_fim', '>=', $this->anoSelecionado);
                    
        $planos = $planosQuery->get();
        
        $totalProgresso = 0;
        $planosConcluidosAno = 0;
        
        foreach ($planos as $plano) {
            $calculo = $service->calcularProgressoPlanoNoAno($plano, (int)$this->anoSelecionado);
            $totalProgresso += $calculo['progresso'];
            
            if ($calculo['status_calculado'] === 'Concluído') {
                $planosConcluidosAno++;
            }
        }
        
        return [
            'totalObjetivos' => $this->peiAtivo ? Objetivo::whereHas('perspectiva', fn($q) => $q->where('cod_pei', $codPei))->count() : 0,
            'totalPerspectivas' => $this->peiAtivo ? Perspectiva::where('cod_pei', $codPei)->count() : 0,
            'totalIndicadores' => Indicador::whereHas('objetivo.perspectiva', fn($q) => $q->where('cod_pei', $codPei))->count(),
            'progressoPlanos' => $planos->count() > 0 ? $totalProgresso / $planos->count() : 0,
            'totalPlanos' => $planos->count(),
            'planosConcluidos' => $planosConcluidosAno,
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
        $service = app(\App\Services\IndicadorCalculoService::class);
        $ano = (int)$this->anoSelecionado;

        // Determinar IDs de Organização para Roll-up (igual ao Mapa Estratégico)
        $orgIds = [];
        if ($this->organizacaoId) {
            $org = Organization::find($this->organizacaoId);
            if ($org) {
                // Tenta pegar descendentes se o model tiver trait de árvore, senão apenas ele mesmo
                if (method_exists($org, 'getDescendantsAndSelfIds')) {
                    $orgIds = $org->getDescendantsAndSelfIds();
                } else {
                    $orgIds = [$this->organizacaoId];
                }
            }
        } else {
            // Se nenhuma organização selecionada, pegar todas vinculadas ao PEI ou do usuário?
            // Dashboard sem Org selecionada mostra visão global.
            // Para visão global, talvez não devamos filtrar por rel_..._organizacao.
            // Mas o Mapa FORÇA uma organização. O Dashboard permite "Todas".
            // Se "Todas", $orgIds vazio.
        }

        $query = Perspectiva::where('cod_pei', $this->peiAtivo->cod_pei)
            ->orderBy('num_nivel_hierarquico_apresentacao');

        // Aplicar Eager Loading com Filtros APENAS sc houver $orgIds
        if (!empty($orgIds)) {
            $query->with(['objetivos' => function($qObj) use ($orgIds) {
                $qObj->with(['indicadores' => function($qInd) use ($orgIds) {
                    $qInd->whereIn('tab_indicador.cod_indicador', function($sub) use ($orgIds) {
                        $sub->select('cod_indicador')
                            ->from('performance_indicators.rel_indicador_objetivo_organizacao')
                            ->whereIn('cod_organizacao', $orgIds);
                    });
                }, 'planosAcao' => function($qPlan) use ($orgIds) {
                    $qPlan->whereIn('tab_plano_de_acao.cod_plano_de_acao', function($sub) use ($orgIds) {
                        $sub->select('cod_plano_de_acao')
                            ->from('action_plan.rel_plano_organizacao')
                            ->whereIn('cod_organizacao', $orgIds);
                    })->with(['entregas' => function($qEntrega) {
                        $qEntrega->where('bln_arquivado', false)->orderBy('dte_prazo');
                    }]);
                }])->ordenadoPorNivel();
            }]);
        } else {
            // Carregamento padrão sem filtro de org (Visão Global)
            $query->with(['objetivos' => function($qObj) {
                $qObj->with(['indicadores', 'planosAcao.entregas']);
            }]);
        }

        return $query->get()->map(function($p) use ($service, $ano) {
                // CÁLCULO CENTRALIZADO
                $atingimento = $service->calcularAtingimentoPerspectiva($p, $ano);
                
                return [
                    'label' => $p->dsc_perspectiva,
                    'count' => $atingimento,
                    'color' => $this->getCorAtingimento($atingimento)
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
        $service = app(\App\Services\IndicadorCalculoService::class);
        $planosQuery = PlanoDeAcao::query();
        
        if ($this->peiAtivo) {
            $planosQuery->whereHas('objetivo.perspectiva', fn($q) => $q->where('cod_pei', $this->peiAtivo->cod_pei));
        }
        if ($this->organizacaoId) {
            $planosQuery->where('cod_organizacao', $this->organizacaoId);
        }
        
        // Filtro de vigência
        $planosQuery->whereYear('dte_inicio', '<=', $this->anoSelecionado)
                    ->whereYear('dte_fim', '>=', $this->anoSelecionado);
                    
        $planos = $planosQuery->get();
        
        $statusCounts = [
            'Concluído' => ['c' => 0, 'col' => '#429B22'], 
            'Em Andamento' => ['c' => 0, 'col' => '#F3C72B'], 
            'Não Iniciado' => ['c' => 0, 'col' => '#475569'], 
            'Atrasado' => ['c' => 0, 'col' => '#dc3545'],
            'Sem Entregas' => ['c' => 0, 'col' => '#6c757d']
        ];
        
        foreach ($planos as $plano) {
            $calculo = $service->calcularProgressoPlanoNoAno($plano, (int)$this->anoSelecionado);
            $st = $calculo['status_calculado'];
            
            if (isset($statusCounts[$st])) {
                $statusCounts[$st]['c']++;
            } else {
                // Fallback para status desconhecidos
                $statusCounts['Em Andamento']['c']++;
            }
        }
        
        return collect($statusCounts)
            ->filter(fn($v) => $v['c'] > 0) // Remove categorias vazias para limpar o gráfico
            ->map(fn($v, $k) => ['label' => $k, 'count' => $v['c'], 'color' => $v['col']])
            ->values()
            ->toArray();
    }

    private function getChartEvolucao()
    {
        if (!$this->peiAtivo) return ['labels' => [], 'data' => []];

        // Buscar evoluções do ano selecionado vinculadas ao PEI
        $evolucoes = \App\Models\PerformanceIndicators\EvolucaoIndicador::where('num_ano', $this->anoSelecionado)
            ->whereHas('indicador.objetivo.perspectiva', fn($q) => $q->where('cod_pei', $this->peiAtivo->cod_pei))
            ->get();

        $dadosPorMes = [];
        for ($i = 1; $i <= 12; $i++) {
            // Se o ano for o atual, parar no mês atual
            if ($this->anoSelecionado == date('Y') && $i > date('n')) break;
            
            $evolucoesMes = $evolucoes->where('num_mes', $i);
            
            if ($evolucoesMes->count() > 0) {
                // Média simples das porcentagens de atingimento (realizado vs 100% ou meta)
                // Considerando polaridade simplificada (assumindo realizado é bom)
                $somaAtingimento = 0;
                $count = 0;
                
                foreach ($evolucoesMes as $ev) {
                   $meta = $ev->vlr_previsto != 0 ? $ev->vlr_previsto : 1;
                   $real = $ev->vlr_realizado;
                   
                   // Limitar a 100% para não distorcer o gráfico com outliers
                   $perc = ($real / $meta) * 100;
                   $somaAtingimento += min($perc, 100); 
                   $count++;
                }
                
                $dadosPorMes[] = round($somaAtingimento / $count, 1);
            } else {
                $dadosPorMes[] = 0;
            }
        }
        
        $meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        
        return [
            'labels' => array_slice($meses, 0, count($dadosPorMes)),
            'data' => $dadosPorMes
        ];
    }

    private function getCorAtingimento($percentual)
    {
        $grau = GrauSatisfacao::where('vlr_minimo', '<=', $percentual)->where('vlr_maximo', '>=', $percentual)->first();
        return $grau?->cor ?? '#6c757d';
    }

    public function getMentorStatus()
    {
        if (!$this->organizacaoId || !$this->peiAtivo) {
            return [
                'steps' => ['identidade' => false, 'mapa' => false, 'objetivos' => false, 'indicadores' => false, 'planos' => false],
                'percent' => 0
            ];
        }

        $codPei = $this->peiAtivo->cod_pei;
        $orgId = $this->organizacaoId;

        // Verificar preenchimento das etapas
        $steps = [
            'identidade' => \App\Models\StrategicPlanning\MissaoVisaoValores::where('cod_organizacao', $orgId)->exists(),
            'mapa' => Perspectiva::where('cod_pei', $codPei)->exists(),
            'objetivos' => Objetivo::whereHas('perspectiva', fn($q) => $q->where('cod_pei', $codPei))->exists(),
            'indicadores' => Indicador::whereHas('objetivo.perspectiva', fn($q) => $q->where('cod_pei', $codPei))->exists(),
            'planos' => PlanoDeAcao::whereHas('objetivo.perspectiva', fn($q) => $q->where('cod_pei', $codPei))
                        ->where('cod_organizacao', $orgId)->exists()
        ];

        $filled = count(array_filter($steps));
        $total = count($steps);
        $percent = $total > 0 ? round(($filled / $total) * 100) : 0;

        return ['steps' => $steps, 'percent' => $percent];
    }
}