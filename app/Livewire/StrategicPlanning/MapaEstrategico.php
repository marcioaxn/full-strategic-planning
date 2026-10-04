<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\Organization;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\MissaoVisaoValores;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\TemaNorteador;
use App\Models\StrategicPlanning\Valor;
use App\Services\IndicadorCalculoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Locked;
use Livewire\Component;

class MapaEstrategico extends Component
{
    public $perspectivas = [];

    public $peiAtivo;

    // Trocada só por atualizarOrganizacao(), que confere o escopo do usuário logado.
    #[Locked]
    public $organizacaoId;

    public $organizacaoNome;

    public $missaoVisao;

    public $valores = [];

    public $temasNorteadores = [];

    public $grausSatisfacao = [];

    public $qtdUnidadesConsolidadas = 1;

    public $organizacoesConsolidadas = [];

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
        'anoSelecionado' => '$refresh',
    ];

    public function mount()
    {
        // Logado: a seleção do topo, validada contra o escopo (nunca a sessão
        // crua). Visitante da Transparência: a seleção que houver, ou a raiz.
        if (Auth::check()) {
            $this->authorize('modulo.acessar', 'planejamento-estrategico');
            $this->organizacaoId = Auth::user()->organizacaoSelecionadaId();
        } else {
            $this->organizacaoId = Session::get('organizacao_selecionada_id');
        }
        $this->viewMode = Session::get('mapa_view_mode', 'grouped');

        if (! $this->organizacaoId) {
            // Super Admin sem seleção, ou visitante: a unidade raiz.
            $orgRaiz = Organization::whereColumn('cod_organizacao', 'rel_cod_organizacao')->first()
                       ?? Organization::orderBy('sgl_organizacao')->first();
            $this->organizacaoId = $orgRaiz?->cod_organizacao;
        }
        $this->carregarPEI();
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
        Session::put('mapa_view_mode', $mode);
        // O carregarMapa() dentro do render garantirá o resto
    }

    public function atualizarOrganizacao($id)
    {
        // Método público (e ouvinte de evento): o ID vem do cliente. Logado, só
        // dentro do próprio escopo; o visitante da área pública só consulta.
        abort_unless(! $id || ! Auth::check() || Auth::user()->podeAcessarOrganizacao($id), 403);

        $this->organizacaoId = $id;
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
    }

    private function carregarPEI()
    {
        $peiId = Session::get('pei_selecionado_id');
        $this->peiAtivo = $peiId ? PEI::find($peiId) : PEI::ativos()->first();
    }

    public function carregarMapa()
    {
        if (! $this->peiAtivo || ! $this->organizacaoId) {
            return;
        }

        // Recupera do estado atual
        $this->viewMode = Session::get('mapa_view_mode', $this->viewMode);

        // IDs para o Roll-up
        $orgIds = [$this->organizacaoId];
        $this->organizacoesConsolidadas = [];

        if ($this->viewMode === 'grouped') {
            $org = Organization::find($this->organizacaoId);
            if ($org) {
                $orgIds = $org->getDescendantsAndSelfIds();
                $this->organizacoesConsolidadas = Organization::whereIn('cod_organizacao', $orgIds)
                    ->orderBy('nom_organizacao')
                    ->get(['sgl_organizacao', 'nom_organizacao'])
                    ->toArray();
            }
        }
        $this->qtdUnidadesConsolidadas = count($orgIds);

        // A régua é a do ciclo exibido no mapa, nunca a de outro.
        $this->grausSatisfacao = GrauSatisfacao::doPei($this->peiAtivo?->cod_pei)->get();

        // Query direta via JOIN/Exists para máxima precisão e performance
        $this->perspectivas = Perspectiva::where('cod_pei', $this->peiAtivo->cod_pei)
            ->with(['objetivos' => function ($query) use ($orgIds) {
                $query->with(['indicadores' => function ($qInd) use ($orgIds) {
                    $qInd->whereIn('tab_indicador.cod_indicador', function ($sub) use ($orgIds) {
                        $sub->select('cod_indicador')
                            ->from('performance_indicators.rel_indicador_objetivo_organizacao')
                            ->whereIn('cod_organizacao', $orgIds);
                    });
                }, 'planosAcao' => function ($qPlan) use ($orgIds) {
                    $qPlan->whereIn('tab_plano_de_acao.cod_plano_de_acao', function ($sub) use ($orgIds) {
                        $sub->select('cod_plano_de_acao')
                            ->from('action_plan.rel_plano_organizacao')
                            ->whereIn('cod_organizacao', $orgIds);
                    })->with(['entregas' => function ($qEntrega) {
                        $qEntrega->where('bln_arquivado', false)->orderBy('dte_prazo');
                    }]);
                }, 'ods'])->ordenadoPorNivel();
            }])
            ->orderBy('num_nivel_hierarquico_apresentacao', 'desc')
            ->get()
            ->map(function ($p) {
                // Pesos configurados
                $pesoInd = $p->num_peso_indicadores ?? 100;
                $pesoPlan = $p->num_peso_planos ?? 0;
                $anoSelecionado = session('ano_selecionado', date('Y'));

                // Coletar métricas dos Indicadores
                $somaAtingInd = 0;
                $totalInd = 0;
                $listaIndicadoresMemoria = [];

                // Coletar métricas dos Planos (Entregas do Ano)
                $somaProgressoPlan = 0;
                $somaPesoPlan = 0;
                $listaPlanosMemoria = [];

                foreach ($p->objetivos as $obj) {
                    $objSomaInd = 0;
                    $objTotalInd = 0;
                    $objMedidos = 0;

                    // --- INDICADORES ---
                    // Sem medição e informativo ficam FORA da média (não entram
                    // como 0%); aparecem na memória como "Sem medição".
                    foreach ($obj->indicadores as $ind) {
                        $objTotalInd++;
                        $ating = $ind->atingimentoParaMedia((int) $anoSelecionado);

                        if ($ating !== null) {
                            $somaAtingInd += $ating;
                            $totalInd++;
                            $objSomaInd += $ating;
                            $objMedidos++;
                        }

                        $listaIndicadoresMemoria[] = [
                            'objetivo' => $obj->nom_objetivo,
                            'indicador' => $ind->nom_indicador,
                            'atingimento' => $ating === null ? null : round($ating, 1),
                            'cor' => $this->getCorPorPercentual($ating),
                            'polaridade' => $ind->dsc_polaridade ?? 'Positiva',
                            'tipo' => 'Indicador',
                        ];
                    }

                    // Resumo Indicadores (Objetivo): NULL quando nenhum foi medido
                    $objMediaInd = $objMedidos > 0 ? ($objSomaInd / $objMedidos) : null;
                    $obj->resumo_indicadores = [
                        'quantidade' => $objTotalInd,
                        'medidos' => $objMedidos,
                        'percentual' => $objMediaInd === null ? null : round($objMediaInd, 1),
                        'cor' => $this->getCorPorPercentual($objMediaInd),
                    ];

                    // --- INICIATIVAS (ENTREGAS DO ANO) ---
                    // Inicializar contadores para o card (apenas planos do ano)
                    $objTotalPlanosAno = 0;
                    $objConcluidosAno = 0;

                    // Acumuladores locais para média de progresso DO OBJETIVO
                    $objSomaProgressoPlan = 0;
                    $objSomaPesoPlan = 0;

                    foreach ($obj->planosAcao as $plano) {
                        // Cálculo Granular para Perspectiva (Usando Collection Filter agora)
                        $entregasAno = $plano->entregas->filter(function ($entrega) use ($anoSelecionado) {
                            return $entrega->dte_prazo && $entrega->dte_prazo->year == $anoSelecionado;
                        });

                        // Se não houver entregas no ano, ignorar este plano para o cálculo e contagem
                        if ($entregasAno->count() === 0) {
                            continue;
                        }

                        $objTotalPlanosAno++;
                        if ($plano->bln_status === 'Concluído') {
                            $objConcluidosAno++;
                        }

                        // Acumuladores Locais do Plano
                        $planoSomaProgresso = 0;
                        $planoSomaPeso = 0;
                        $entregasMemoria = [];

                        foreach ($entregasAno as $entrega) {
                            if ($entrega->bln_status === 'Cancelado') {
                                continue;
                            }

                            $statusDecimal = match ($entrega->bln_status) {
                                'Concluído' => 1.0, 'Em Andamento' => 0.5, 'Suspenso' => 0.25, default => 0.0
                            };
                            $peso = $entrega->num_peso > 0 ? $entrega->num_peso : 1;

                            $planoSomaProgresso += ($peso * $statusDecimal);
                            $planoSomaPeso += $peso;

                            // Coletar para memória
                            $entregasMemoria[] = [
                                'entrega' => $entrega->dsc_entrega,
                                'prazo' => $entrega->dte_prazo->format('d/m/Y'),
                                'status' => $entrega->bln_status,
                                'peso' => $peso,
                            ];
                        }

                        // Calcular atingimento deste plano específico no ano
                        // Só entregas canceladas no ano: não há o que medir (não é 0%).
                        $planoAtingimento = $planoSomaPeso > 0 ? ($planoSomaProgresso / $planoSomaPeso) * 100 : null;

                        // Adicionar ao acumulador global da perspectiva
                        $somaProgressoPlan += $planoSomaProgresso;
                        $somaPesoPlan += $planoSomaPeso;

                        // Adicionar ao acumulador local do objetivo (para média de progresso do card)
                        $objSomaProgressoPlan += $planoSomaProgresso;
                        $objSomaPesoPlan += $planoSomaPeso;

                        // Adicionar à lista de memória
                        $listaPlanosMemoria[] = [
                            'objetivo' => $obj->nom_objetivo,
                            'plano' => $plano->dsc_plano_de_acao,
                            'entregas' => $entregasMemoria,
                            'atingimento' => $planoAtingimento === null ? null : round($planoAtingimento, 1),
                            'cor' => $this->getCorPorPercentual($planoAtingimento),
                            'tipo' => 'Iniciativa',
                        ];
                    }

                    // Calcular média ponderada de progresso do OBJETIVO (para exibição no card)
                    $objMediaProgresso = $objSomaPesoPlan > 0 ? ($objSomaProgressoPlan / $objSomaPesoPlan) * 100 : null;

                    // Resumo Planos (Calculado com base filterada)
                    $corPlano = '#475569';
                    if ($objTotalPlanosAno > 0) {
                        if ($objConcluidosAno == $objTotalPlanosAno) {
                            $corPlano = '#429B22';
                        } elseif ($objTotalPlanosAno > $objConcluidosAno) {
                            $corPlano = '#F3C72B';
                        } // Se tem pendente, warning
                        // Nota: A lógica original usava query status 'Em Andamento'/'Atrasado'.
                        // Aqui simplificamos: Se não todos concluídos, é Warning (laranja), a menos que seja 0.
                    }

                    $obj->resumo_planos = [
                        'quantidade' => $objTotalPlanosAno,
                        'concluidos' => $objConcluidosAno,
                        'percentual' => $objTotalPlanosAno > 0 ? round(($objConcluidosAno / $objTotalPlanosAno) * 100, 1) : 0,
                        'media_progresso' => $objMediaProgresso === null ? null : round($objMediaProgresso, 1), // Progresso ponderado; NULL = nada a medir
                        'cor' => $corPlano,
                    ];
                }

                // Média dos indicadores MEDIDOS; NULL = nenhum medido
                $mediaIndicadores = $totalInd > 0 ? ($somaAtingInd / $totalInd) : null;

                // Média Planos (Ponderada); NULL = nenhuma entrega do ano a medir
                $mediaPlanos = $somaPesoPlan > 0 ? ($somaProgressoPlan / $somaPesoPlan) * 100 : null;

                // CÁLCULO FINAL HÍBRIDO — a mesma regra do IndicadorCalculoService:
                // componente sem dado sai e os pesos se renormalizam; nada medido
                // é NULL ("Sem medição", cinza), nunca 0% pintado de crítico.
                $atingimentoFinal = IndicadorCalculoService::combinarComponentes($mediaIndicadores, $pesoInd, $mediaPlanos, $pesoPlan);

                $p->atingimento_medio = $atingimentoFinal === null ? null : round($atingimentoFinal, 1);
                $p->cor_satisfacao = $this->getCorPorPercentual($atingimentoFinal);
                $p->memoria_indicadores = $listaIndicadoresMemoria;
                $p->memoria_planos = $listaPlanosMemoria;

                // Detalhes extras para tooltip
                $p->detalhes_calculo = [
                    'nota_indicadores' => $mediaIndicadores === null ? null : round($mediaIndicadores, 1),
                    'peso_indicadores' => $pesoInd,
                    'nota_planos' => $mediaPlanos === null ? null : round($mediaPlanos, 1),
                    'peso_planos' => $pesoPlan,
                ];

                return $p;
            })->toArray();
    }

    public function carregarIdentidadeEstrategica()
    {
        if (! $this->peiAtivo) {
            return;
        }
        $this->missaoVisao = MissaoVisaoValores::where('cod_pei', $this->peiAtivo->cod_pei)->where('cod_organizacao', $this->organizacaoId)->first();
        $this->valores = Valor::where('cod_pei', $this->peiAtivo->cod_pei)->where('cod_organizacao', $this->organizacaoId)->orderBy('nom_valor')->get();
        $this->temasNorteadores = TemaNorteador::where('cod_pei', $this->peiAtivo->cod_pei)->where('cod_organizacao', $this->organizacaoId)->orderBy('created_at', 'asc')->get();
    }

    /**
     * A cor do farol da perspectiva.
     *
     * 🔴 Devolvia '#dc3545' — VERMELHO — para tudo que não casasse com uma
     * faixa. Duas consequências, as duas vistas na tela: organização sem
     * graus de satisfação configurados ficava com o mapa inteiro em crítico,
     * e perspectiva a 125% da meta era pintada de crítico por estar ACIMA da
     * última faixa. A regra honesta mora no Model, com o resto do farol.
     */
    public function getCorPorPercentual($percentual): string
    {
        // Sem medição: cinza neutro, nunca a cor da pior faixa.
        if ($percentual === null) {
            return GrauSatisfacao::COR_SEM_REGUA;
        }

        return GrauSatisfacao::corDe(
            (float) $percentual,
            $this->peiAtivo?->cod_pei,
            (int) session('ano_selecionado', date('Y'))
        );
    }

    public function getCoresPerspectiva($nivel): array
    {
        return $this->coresPerspectivas[$nivel] ?? $this->coresPerspectivas[1];
    }

    public function abrirMemoriaCalculo($index)
    {
        // O índice vem do navegador: fora da lista, não há o que mostrar (era 500).
        if (! is_numeric($index) || ! isset($this->perspectivas[(int) $index])) {
            return;
        }

        $p = $this->perspectivas[(int) $index];
        $this->detalhesCalculo = [
            'titulo' => $p['dsc_perspectiva'], 'media' => $p['atingimento_medio'],
            'cor' => $p['cor_satisfacao'], 'indicadores' => $p['memoria_indicadores'],
            'planos' => $p['memoria_planos'] ?? [],
            'detalhes_calculo' => $p['detalhes_calculo'] ?? null,
        ];
        $this->showCalcModal = true;
    }

    public function fecharMemoriaCalculo()
    {
        $this->showCalcModal = false;
    }

    public function render()
    {
        $this->carregarMapa();
        $this->carregarIdentidadeEstrategica();
        if ($this->organizacaoId) {
            $org = Organization::find($this->organizacaoId);
            $this->organizacaoNome = $org ? $org->nom_organizacao : 'SPS';
        }

        return view('livewire.p-e-i.mapa-estrategico')->layout(Auth::check() ? 'layouts.app' : 'layouts.public');
    }
}
