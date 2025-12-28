<?php

namespace App\Livewire\Dashboard;

use App\Models\Organization;
use App\Models\PEI\ObjetivoEstrategico;
use App\Models\PEI\PlanoDeAcao;
use App\Models\PEI\Indicador;
use App\Models\PEI\Perspectiva;
use App\Models\PEI\PEI;
use App\Models\Risco;
use App\Models\PEI\EvolucaoIndicador;
use App\Models\PEI\MetaPorAno;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Session;

use App\Models\PEI\GrauSatisfacao;

#[Layout('layouts.app')]
class Index extends Component
{
    public $organizacaoId;
    public $peiAtivo;

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarDashboard'
    ];

    public function mount()
    {
        $this->organizacaoId = Session::get('organizacao_selecionada_id');
        $this->peiAtivo = PEI::ativos()->first();
    }

    public function atualizarDashboard($id)
    {
        $this->organizacaoId = $id;
    }

    public function render()
    {
        $orgId = $this->organizacaoId;
        $userId = auth()->id();
        
        // Base queries filtradas por Organização
        $queryPlanos = PlanoDeAcao::query();
        $queryIndicadores = Indicador::query();
        
        if ($orgId) {
            $queryPlanos->where('cod_organizacao', $orgId);
            $queryIndicadores->where(function($q) use ($orgId) {
                $q->whereHas('organizacoes', function($sq) use ($orgId) {
                    $sq->where('tab_organizacoes.cod_organizacao', $orgId);
                })->orWhereHas('planoDeAcao', function($sq) use ($orgId) {
                    $sq->where('cod_organizacao', $orgId);
                });
            });
        }

        // --- 1. Minhas Entregas (Foco no Usuário) ---
        $minhasEntregas = \App\Models\PEI\Entrega::with(['planoDeAcao', 'labels'])
            ->whereHas('responsaveis', function($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->where('bln_status', '!=', 'Concluído')
            ->orderBy('dte_prazo', 'asc')
            ->get();

        $comentariosRecentes = \App\Models\PEI\EntregaComentario::with(['usuario', 'entrega'])
            ->whereHas('entrega.responsaveis', function($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->where('cod_usuario', '!=', $userId)
            ->latest()
            ->take(5)
            ->get();

        // --- 2. Estatísticas Estratégicas (KPIs) ---
        $stats = [
            'totalObjetivos' => $this->peiAtivo ? ObjetivoEstrategico::whereHas('perspectiva', function($q) {
                $q->where('cod_pei', $this->peiAtivo->cod_pei);
            })->count() : 0,
            'totalPlanos' => (clone $queryPlanos)->count(),
            'progressoPlanos' => (clone $queryPlanos)->get()->avg(fn($p) => $p->calcularProgressoEntregas()) ?? 0,
            'riscosCriticos' => Risco::where('cod_organizacao', $orgId)
                                    ->whereRaw('(num_probabilidade * num_impacto) >= 15')->count(),
            'indicadoresAtrasados' => (clone $queryIndicadores)->whereDoesntHave('evolucoes', function($q) {
                $q->where('num_ano', now()->year)->where('num_mes', now()->month);
            })->count(),
        ];

        // --- 3. Desempenho por Perspectiva (BSC) ---
        $chartBSC = [];
        $graus = GrauSatisfacao::orderBy('vlr_minimo')->get();

        if ($this->peiAtivo) {
            $perspectivas = Perspectiva::where('cod_pei', $this->peiAtivo->cod_pei)
                ->with(['objetivos.indicadores'])
                ->ordenadoPorNivel()
                ->get();

            foreach ($perspectivas as $p) {
                $somaPersp = 0;
                $contPersp = 0;
                foreach($p->objetivos as $obj) {
                    foreach($obj->indicadores as $ind) {
                        $somaPersp += $ind->calcularAtingimento();
                        $contPersp++;
                    }
                }
                $atingimento = $contPersp > 0 ? round($somaPersp / $contPersp, 1) : 0;
                
                // Determinar cor baseada no grau de satisfação
                $cor = '#1B408E'; // Default Deep Blue
                foreach ($graus as $grau) {
                    if ($atingimento >= $grau->vlr_minimo && $atingimento <= $grau->vlr_maximo) {
                        $cor = $grau->cor; // CORRIGIDO: de dsc_cor para cor
                        break;
                    }
                }

                $chartBSC[] = [
                    'label' => $p->dsc_perspectiva,
                    'count' => $atingimento,
                    'color' => $cor,
                    'id' => $p->cod_perspectiva
                ];
            }
        }

        // --- 4. Riscos por Nível (Substituindo Evolução) ---
        $riscosNiveis = Risco::where('cod_organizacao', $orgId)
            ->selectRaw('
                CASE
                    WHEN (num_probabilidade * num_impacto) >= 15 THEN \'Crítico\'
                    WHEN (num_probabilidade * num_impacto) >= 10 THEN \'Alto\'
                    WHEN (num_probabilidade * num_impacto) >= 5 THEN \'Médio\'
                    ELSE \'Baixo\'
                END as nivel,
                count(*) as total
            ')
            ->groupByRaw('nivel')
            ->pluck('total', 'nivel')->toArray();

        $chartRiscosNivel = [
            'labels' => ['Crítico', 'Alto', 'Médio', 'Baixo'],
            'data' => [
                $riscosNiveis['Crítico'] ?? 0,
                $riscosNiveis['Alto'] ?? 0,
                $riscosNiveis['Médio'] ?? 0,
                $riscosNiveis['Baixo'] ?? 0,
            ],
            'colors' => ['#dc3545', '#fd7e14', '#ffc107', '#198754']
        ];

        // --- 5. Status Planos ---
        $planosStatus = (clone $queryPlanos)->selectRaw('bln_status as status, count(*) as total')->groupBy('bln_status')->pluck('total', 'status')->toArray();
        $chartPlanos = [
            ['label' => 'Concluído', 'count' => $planosStatus['Concluído'] ?? 0, 'color' => '#198754'],
            ['label' => 'Em Andamento', 'count' => $planosStatus['Em Andamento'] ?? 0, 'color' => '#0d6efd'],
            ['label' => 'Atrasado', 'count' => $planosStatus['Atrasado'] ?? 0, 'color' => '#dc3545'],
            ['label' => 'Não Iniciado', 'count' => $planosStatus['Não Iniciado'] ?? 0, 'color' => '#6c757d'],
        ];

        return view('livewire.dashboard.index', [
            'stats' => $stats,
            'minhasEntregas' => $minhasEntregas,
            'comentariosRecentes' => $comentariosRecentes,
            'chartBSC' => $chartBSC,
            'chartRiscosNivel' => $chartRiscosNivel,
            'chartPlanos' => $chartPlanos,
            'organizacaoNome' => Session::get('organizacao_selecionada_sgl', 'Global')
        ]);
    }
}