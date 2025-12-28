<?php

namespace App\Livewire\PEI;

use App\Models\PEI\PEI;
use App\Models\PEI\Perspectiva;
use App\Models\PEI\MissaoVisaoValores;
use App\Models\PEI\Valor;
use App\Models\PEI\GrauSatisfacao;
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
        'organizacaoSelecionada' => 'atualizarOrganizacao'
    ];

    public function mount()
    {
        // Se estiver logado, usa a organização da sessão.
        // Se não, tenta pegar a Unidade Central ou a primeira disponível para visualização pública.
        if (Auth::check()) {
            $this->organizacaoId = Session::get('organizacao_selecionada_id');
        } else {
            // Unidade Central padrão para o público (UUID fixo da migration)
            $this->organizacaoId = '3834910f-66f7-46d8-9104-2904d59e1241';
        }

        $this->peiAtivo = PEI::ativos()->first();
        $this->atualizarOrganizacao($this->organizacaoId);
    }

    public function atualizarOrganizacao($id)
    {
        $this->organizacaoId = $id;

        // Carregar graus de satisfacao PRIMEIRO
        $this->carregarGrausSatisfacao();

        if ($id) {
            $org = Organization::find($id);
            $this->organizacaoNome = $org ? $org->nom_organizacao : 'Sistema SEAE';
        }

        if ($this->peiAtivo) {
            $this->carregarMapa();
            $this->carregarIdentidadeEstrategica();
        }
    }

    public function carregarGrausSatisfacao()
    {
        $this->grausSatisfacao = GrauSatisfacao::orderBy('vlr_minimo')->get();
    }

    public function carregarMapa()
    {
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

                // Processar cada objetivo da perspectiva
                $p->objetivos->map(function($obj) use (&$somaPersp, &$contPersp, &$listaIndicadoresMemoria) {
                    // 1. Cálculo de Indicadores do Objetivo
                    $indicadores = $obj->indicadores;
                    $totalInd = $indicadores->count();
                    $somaAtingObj = 0;
                    
                    foreach ($indicadores as $ind) {
                        $ating = $ind->calcularAtingimento();
                        $somaAtingObj += $ating;
                        $somaPersp += $ating;
                        $contPersp++;
                        
                        $listaIndicadoresMemoria[] = [
                            'objetivo' => $obj->nom_objetivo_estrategico,
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

                    // 2. Cálculo de Planos de Ação do Objetivo
                    $planos = $obj->planosAcao;
                    $totalPlanos = $planos->count();
                    $concluidos = $planos->where('bln_status', 'Concluído')->count();
                    $percentualPlanos = $totalPlanos > 0 ? ($concluidos / $totalPlanos) * 100 : 0;
                    
                    $obj->resumo_planos = [
                        'quantidade' => $totalPlanos,
                        'concluidos' => $concluidos,
                        'percentual' => round($percentualPlanos, 1),
                        'cor' => $this->getCorPorPercentual($percentualPlanos)
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
        // Carregar Missão e Visão
        $this->missaoVisao = MissaoVisaoValores::where('cod_pei', $this->peiAtivo->cod_pei)
            ->where('cod_organizacao', $this->organizacaoId)
            ->first();

        // Carregar Valores
        $this->valores = Valor::where('cod_pei', $this->peiAtivo->cod_pei)
            ->where('cod_organizacao', $this->organizacaoId)
            ->orderBy('nom_valor')
            ->get();
    }

    /**
     * Calcula o percentual de atingimento dos indicadores de um objetivo
     */
    public function calcularAtingimentoIndicadores($objetivo): array
    {
        $indicadores = $objetivo->indicadores;
        $total = $indicadores->count();

        if ($total === 0) {
            return [
                'quantidade' => 0,
                'percentual' => 0,
                'cor' => '#6c757d'
            ];
        }

        $soma = 0;
        foreach ($indicadores as $ind) {
            $soma += $ind->calcularAtingimento();
        }

        $media = $soma / $total;

        // Determinar cor baseado no percentual
        $cor = $this->getCorPorPercentual($media);

        return [
            'quantidade' => $total,
            'percentual' => round($media, 1),
            'cor' => $cor
        ];
    }

    /**
     * Calcula o status dos planos de ação de um objetivo
     */
    public function calcularStatusPlanos($objetivo): array
    {
        $planos = $objetivo->planosAcao;
        $total = $planos->count();

        if ($total === 0) {
            return [
                'quantidade' => 0,
                'concluidos' => 0,
                'percentual' => 0,
                'cor' => '#6c757d'
            ];
        }

        $concluidos = $planos->where('bln_status', 'Concluído')->count();
        $percentual = ($concluidos / $total) * 100;

        // Determinar cor baseado no percentual
        $cor = $this->getCorPorPercentual($percentual);

        return [
            'quantidade' => $total,
            'concluidos' => $concluidos,
            'percentual' => round($percentual, 1),
            'cor' => $cor
        ];
    }

    /**
     * Retorna a cor baseada no percentual (usando graus de satisfacao dinamicos)
     */
    public function getCorPorPercentual($percentual): string
    {
        // Buscar nos graus de satisfacao cadastrados
        foreach ($this->grausSatisfacao as $grau) {
            if ($percentual >= $grau->vlr_minimo && $percentual <= $grau->vlr_maximo) {
                return $grau->cor;
            }
        }

        // Fallback caso nao encontre (Usando Hexadecimais)
        if ($percentual >= 100) return '#198754';
        if ($percentual >= 80) return '#0d6efd';
        if ($percentual >= 60) return '#0dcaf0';
        if ($percentual >= 40) return '#ffc107';
        return '#dc3545';
    }

    /**
     * Retorna as cores para uma perspectiva baseado no nível
     */
    public function getCoresPerspectiva($nivel): array
    {
        return $this->coresPerspectivas[$nivel] ?? $this->coresPerspectivas[1];
    }

    public function render()
    {
        // Define o layout dinamicamente: 'app' para logados, 'guest' para público
        $layout = Auth::check() ? 'layouts.app' : 'layouts.guest';

        return view('livewire.pei.mapa-estrategico')
            ->layout($layout);
    }
}
