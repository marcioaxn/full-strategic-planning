<?php

namespace App\Livewire\Dashboard;

use App\Models\Organization;
use App\Models\PEI\ObjetivoEstrategico;
use App\Models\PEI\PlanoDeAcao;
use App\Models\PEI\Indicador;
use App\Models\PEI\Perspectiva;
use App\Models\PEI\PEI;
use App\Models\Risco;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Session;

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
        
        // Base queries filtradas por Organização
        $queryPlanos = PlanoDeAcao::query();
        $queryIndicadores = Indicador::query();
        
        if ($orgId) {
            $queryPlanos->where('cod_organizacao', $orgId);
            
            // Indicadores vinculados à org via pivot ou via plano da org
            $queryIndicadores->where(function($q) use ($orgId) {
                $q->whereHas('organizacoes', function($sq) use ($orgId) {
                    $sq->where('public.tab_organizacoes.cod_organizacao', $orgId);
                })->orWhereHas('planoDeAcao', function($sq) use ($orgId) {
                    $sq->where('cod_organizacao', $orgId);
                });
            });
        }

        // Estatísticas
        $stats = [
            'totalObjetivos' => $this->peiAtivo ? ObjetivoEstrategico::where('cod_perspectiva', function($q) {
                $q->select('cod_perspectiva')->from('pei.tab_perspectiva')->where('cod_pei', $this->peiAtivo->cod_pei)->limit(1);
            })->count() : 0, // Simplificado: objetivos do PEI
            'totalPlanos' => (clone $queryPlanos)->count(),
            'totalIndicadores' => (clone $queryIndicadores)->count(),
            'planosAtrasados' => (clone $queryPlanos)->where('bln_status', '!=', 'Concluído')
                                    ->where('dte_fim', '<', now())
                                    ->count(),
            'indicadoresSemLancamento' => (clone $queryIndicadores)->whereDoesntHave('evolucoes', function($q) {
                $q->where('num_ano', now()->year)->where('num_mes', now()->month);
            })->count(),
            'riscosCriticos' => Risco::whereRaw('(num_probabilidade * num_impacto) >= 15')->count(),
        ];

        // Se houver PEI, pegar dados para gráfico por perspectiva
        $chartBSC = [];
        if ($this->peiAtivo) {
            $perspectivas = Perspectiva::where('cod_pei', $this->peiAtivo->cod_pei)
                ->withCount('objetivos')
                ->ordenadoPorNivel()
                ->get();
            
            foreach ($perspectivas as $p) {
                $chartBSC[] = [
                    'label' => $p->dsc_perspectiva,
                    'count' => $p->objetivos_count
                ];
            }
        }

        return view('livewire.dashboard.index', [
            'stats' => $stats,
            'chartBSC' => $chartBSC,
            'organizacaoNome' => Session::get('organizacao_selecionada_sgl', 'Global')
        ]);
    }
}