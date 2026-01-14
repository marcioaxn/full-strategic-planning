<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\MissaoVisaoValores;
use App\Models\StrategicPlanning\Valor;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\ObjetivoEstrategico;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class MapaEstrategico extends Component
{
    public $perspectivas = [];
    public $peiAtivo;
    public $organizacaoId;
    public $organizacaoNome;
    public $missaoVisao;
    public $valores = [];
    public $objetivosEstrategicos = [];
    public $grausSatisfacao = [];

    // Propriedades para Modal de Memória de Cálculo
    public bool $showCalcModal = false;
    public $detalhesCalculo = null;

    // Cores das perspectivas por nível hierárquico
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
        'anoSelecionado' => 'atualizarAno'
    ];

    public function mount()
    {
        // Tenta obter da sessão primeiro (funciona para logados e visitantes)
        $this->organizacaoId = Session::get('organizacao_selecionada_id');

        // Se não houver na sessão, busca a primeira organização do banco como padrão
        if (!$this->organizacaoId) {
            $primeiraOrg = Organization::orderBy('sgl_organizacao')->first();
            $this->organizacaoId = $primeiraOrg?->cod_organizacao;
            
            if ($this->organizacaoId) {
                Session::put('organizacao_selecionada_id', $this->organizacaoId);
                Session::put('organizacao_selecionada_sgl', $primeiraOrg->sgl_organizacao);
            }
        }

        $this->carregarPEI();
    }

    public function atualizarAno($ano)
    {
        // Recarrega os dados do mapa para o novo ano
    }

    public function atualizarOrganizacao($id)
    {
        $this->organizacaoId = $id;
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
    }

    private function carregarPEI()
    {
        $peiId = Session::get('pei_selecionado_id');

        if ($peiId) {
            $this->peiAtivo = PEI::find($peiId);
        }

        if (!$this->peiAtivo) {
            $this->peiAtivo = PEI::ativos()->first();
        }
    }

    public function carregarGrausSatisfacao()
    {
        $this->grausSatisfacao = GrauSatisfacao::orderBy('vlr_minimo')->get();
    }

    public function carregarMapa()
    {
        if (!$this->peiAtivo) return;

        $this->perspectivas = Perspectiva::where('cod_pei', $this->peiAtivo->cod_pei)
            ->with(['objetivos' => function($query) {
                $query->with(['indicadores', 'planosAcao'])->ordenadoPorNivel();
            }])
            ->orderBy('num_nivel_hierarquico_apresentacao', 'desc')
            ->get()
            ->map(function($p) {
                $somaPersp = 0;
                $contPersp = 0;
                $listaIndicadoresMemoria = [];

                $p->objetivos->map(function($obj) use (&$somaPersp, &$contPersp, &$listaIndicadoresMemoria) {
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
                            'cor' => $this->getCorPorPercentual($ating)
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
                    $percentualPlanos = $totalPlanos > 0 ? ($concluidos / $totalPlanos) * 100 : 0;
                    
                    // Determinar cor do plano baseado na nova regra
                    $corPlano = '#475569'; // secondary
                    if ($totalPlanos > 0) {
                        if ($concluidos == $totalPlanos) {
                            $corPlano = '#429B22'; // success
                        } else if ($planos->whereIn('bln_status', ['Em Andamento', 'Atrasado'])->count() > 0) {
                            $corPlano = '#F3C72B'; // warning
                        }
                    }

                    $obj->resumo_planos = [
                        'quantidade' => $totalPlanos,
                        'concluidos' => $concluidos,
                        'percentual' => round($percentualPlanos, 1),
                        'cor' => $corPlano
                    ];

                    return $obj;
                });
                
                $atingimentoPersp = $contPersp > 0 ? round($somaPersp / $contPersp, 1) : 0;
                
                $p->atingimento_medio = $atingimentoPersp;
                $p->cor_satisfacao = $this->getCorPorPercentual($atingimentoPersp);
                $p->memoria_indicadores = $listaIndicadoresMemoria;
                
                return $p;
            })->toArray();
    }

    public function abrirMemoriaCalculo($index)
    {
        $p = $this->perspectivas[$index];
        $this->detalhesCalculo = [
            'titulo' => $p['dsc_perspectiva'],
            'media' => $p['atingimento_medio'],
            'cor' => $p['cor_satisfacao'],
            'indicadores' => $p['memoria_indicadores']
        ];
        $this->showCalcModal = true;
    }

    public function fecharMemoriaCalculo()
    {
        $this->showCalcModal = false;
        $this->detalhesCalculo = null;
    }

    public function carregarIdentidadeEstrategica()
    {
        if (!$this->peiAtivo) return;

        $this->missaoVisao = MissaoVisaoValores::where('cod_pei', $this->peiAtivo->cod_pei)
            ->where('cod_organizacao', $this->organizacaoId)
            ->first();

                $this->valores = Valor::where('cod_pei', $this->peiAtivo->cod_pei)

                    ->where('cod_organizacao', $this->organizacaoId)

                    ->orderBy('nom_valor')

                    ->get();

        

                $this->objetivosEstrategicos = ObjetivoEstrategico::where('cod_pei', $this->peiAtivo->cod_pei)

                    ->where('cod_organizacao', $this->organizacaoId)

                    ->orderBy('created_at', 'asc')

                    ->get();

            }

    public function getCorPorPercentual($percentual): string
    {
        foreach ($this->grausSatisfacao as $grau) {
            if ($percentual >= $grau->vlr_minimo && $percentual <= $grau->vlr_maximo) {
                return $grau->cor;
            }
        }

        if ($percentual >= 100) return '#198754';
        if ($percentual >= 80) return '#0d6efd';
        if ($percentual >= 60) return '#0dcaf0';
        if ($percentual >= 40) return '#ffc107';
        return '#dc3545';
    }

    public function getCoresPerspectiva($nivel): array
    {
        return $this->coresPerspectivas[$nivel] ?? $this->coresPerspectivas[1];
    }

    public function render()
    {
        // Re-executa o carregamento a cada requisição (incluindo poll)
        $this->carregarGrausSatisfacao();
        
        if ($this->organizacaoId) {
            $org = Organization::find($this->organizacaoId);
            $this->organizacaoNome = $org ? $org->nom_organizacao : 'Strategic Planning System';
        }

        if ($this->peiAtivo) {
            $this->carregarMapa();
            $this->carregarIdentidadeEstrategica();
        }

        $layout = Auth::check() ? 'layouts.app' : 'layouts.public';

        return view('livewire.p-e-i.mapa-estrategico')
            ->layout($layout);
    }
}
