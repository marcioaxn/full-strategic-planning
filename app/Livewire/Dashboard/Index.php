<?php

namespace App\Livewire\Dashboard;

use App\Concerns\RevalidaUnidadeNaRequisicao;
use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\EntregaComentario;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\Agenda2030\ODS;
use App\Models\Organization;
use App\Models\PerformanceIndicators\EvolucaoIndicador;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\RiskManagement\Risco;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\MissaoVisaoValores;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Services\AI\AiServiceFactory;
use App\Services\IndicadorCalculoService;
use App\Support\CalculoPolaridade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    use RevalidaUnidadeNaRequisicao;

    // Locked: o painel recalcula a cada poll com este id; vindo do navegador,
    // bastaria trocá-lo para ler o painel de qualquer unidade.
    #[Locked]
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
        'evolucao' => ['labels' => [], 'data' => []],
    ];

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI',
        'anoSelecionado' => 'atualizarAno',
    ];

    public function mount()
    {
        $this->anoSelecionado = Session::get('ano_selecionado', date('Y'));

        // A organização vem SEMPRE validada contra o escopo do usuário.
        //
        // 🔴 Com a sessão vazia (primeira tela após o login), o painel gravava a
        // unidade RAIZ na sessão sem checar se o usuário tinha acesso a ela: um
        // Gestor de uma unidade folha via o consolidado da instituição inteira,
        // e as telas seguintes (RAE, Lições, Entregas) liam essa raiz.
        // Quem não é Super Admin cai na primeira unidade do próprio escopo.
        $this->organizacaoId = Auth::user()->organizacaoSelecionadaId();

        // O Dashboard monta antes do SeletorOrganizacao (slot renderiza antes do layout).
        // Super Admin sem seleção: começa pela raiz, alinhado ao seletor.
        if (! $this->organizacaoId && Auth::user()->isSuperAdmin()) {
            $org = Organization::raiz()->orderBy('nom_organizacao')->first();
            if ($org) {
                $this->organizacaoId = $org->cod_organizacao;
                Session::put('organizacao_selecionada_id', $org->cod_organizacao);
                Session::put('organizacao_selecionada_nom', $org->nom_organizacao);
                Session::put('organizacao_selecionada_sgl', $org->sgl_organizacao);
            }
        }

        if ($this->organizacaoId && ! Session::has('organizacao_selecionada_nom')) {
            $org = Organization::find($this->organizacaoId);
            Session::put('organizacao_selecionada_nom', $org?->nom_organizacao);
            Session::put('organizacao_selecionada_sgl', $org?->sgl_organizacao);
        }

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
        // Método público (e ouvinte de evento): o ID vem do cliente. Logado, só
        // dentro do próprio escopo; o visitante da área pública só consulta.
        // Organização vazia ("todas as unidades") é só do Super Admin: para os
        // demais, vazio passava sem filtro e o painel mostrava a instituição inteira.
        $user = Auth::user();
        abort_unless($id ? $user->podeAcessarOrganizacao($id) : $user->isSuperAdmin(), 403);

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
        if (! $this->peiAtivo) {
            $this->peiAtivo = PEI::ativos()->first();
        }
    }

    public function generateAiSummary()
    {
        // Chamada paga ao provedor de IA com os números da unidade: autoriza e
        // revalida a unidade aqui, porque o método é chamável direto do navegador.
        $this->authorize('modulo.acessar', 'planejamento-estrategico');
        abort_unless(! $this->organizacaoId || auth()->user()->podeAcessarOrganizacao($this->organizacaoId), 403);
        $this->carregarNomeOrganizacao();

        $aiService = AiServiceFactory::make();
        if (! $aiService) {
            return;
        }

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
        $this->atualizarDadosGraficos();
        $this->dispatch('graficosAtualizados', chartData: $this->chartData);

        return view('livewire.dashboard.index', [
            'stats' => $this->getStats(),
            'iqg' => $this->getIQG(),
            'minhasEntregas' => $this->getMinhasEntregas(),
            'entregasAgrupadas' => $this->getMinhasEntregasAgrupadas(),
            'comentariosRecentes' => $this->getComentariosRecentes(),
            'alertasPrazos' => $this->getAlertasPrazos(),
            'odsCobertura' => $this->getOdsCobertura(),
        ]);
    }

    /**
     * Alertas de entregas com prazo vencido ou próximo do vencimento (próximos 7 dias).
     */
    private function getAlertasPrazos()
    {
        if (! $this->peiAtivo) {
            return collect();
        }

        $query = Entrega::whereNotNull('dte_prazo')
            ->where('bln_status', '!=', 'Concluído')
            ->where('bln_arquivado', false)
            ->whereNull('deleted_at')
            ->where('dte_prazo', '<=', now()->addDays(7))
            ->whereHas('planoDeAcao.objetivo.perspectiva', fn ($q) => $q->where('cod_pei', $this->peiAtivo->cod_pei))
            ->with('planoDeAcao');

        if ($this->organizacaoId) {
            $query->whereHas('planoDeAcao', fn ($q) => $q->where('cod_organizacao', $this->organizacaoId));
        }

        return $query->orderBy('dte_prazo')->take(6)->get()->map(function ($e) {
            $venceu = $e->dte_prazo->isPast();

            return [
                'titulo' => $e->dsc_entrega,
                'plano' => $e->planoDeAcao?->dsc_plano_de_acao,
                'prazo' => $e->dte_prazo,
                'vencido' => $venceu,
                'plano_id' => $e->cod_plano_de_acao,
            ];
        });
    }

    private function getStats()
    {
        $codPei = $this->peiAtivo?->cod_pei;
        $service = app(IndicadorCalculoService::class);

        // Buscar Planos
        $planosQuery = PlanoDeAcao::query();
        if ($codPei) {
            $planosQuery->whereHas('objetivo.perspectiva', fn ($q) => $q->where('cod_pei', $codPei));
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
            $calculo = $service->calcularProgressoPlanoNoAno($plano, (int) $this->anoSelecionado);
            $totalProgresso += $calculo['progresso'];

            if ($calculo['status_calculado'] === 'Concluído') {
                $planosConcluidosAno++;
            }
        }

        return [
            'totalObjetivos' => $this->peiAtivo ? Objetivo::whereHas('perspectiva', fn ($q) => $q->where('cod_pei', $codPei))->count() : 0,
            'totalPerspectivas' => $this->peiAtivo ? Perspectiva::where('cod_pei', $codPei)->count() : 0,
            'totalIndicadores' => Indicador::whereHas('objetivo.perspectiva', fn ($q) => $q->where('cod_pei', $codPei))->count(),
            'progressoPlanos' => $planos->count() > 0 ? $totalProgresso / $planos->count() : 0,
            'totalPlanos' => $planos->count(),
            'planosConcluidos' => $planosConcluidosAno,
            'riscosCriticos' => Risco::where('cod_organizacao', $this->organizacaoId)->where('cod_pei', $codPei)->criticos()->count(),
            'totalRiscos' => Risco::where('cod_organizacao', $this->organizacaoId)->where('cod_pei', $codPei)->count(),
        ];
    }

    private function getMinhasEntregas()
    {
        $query = Entrega::whereHas('responsaveis', fn ($q) => $q->where('users.id', Auth::id()))
            ->where('bln_status', '!=', 'Concluído')
            ->raiz()->ativas()->with(['planoDeAcao.objetivo']);

        if ($this->peiAtivo) {
            $query->whereHas('planoDeAcao.objetivo.perspectiva', fn ($q) => $q->where('cod_pei', $this->peiAtivo->cod_pei));
        }
        if ($this->organizacaoId) {
            $query->whereHas('planoDeAcao', fn ($q) => $q->where('cod_organizacao', $this->organizacaoId));
        }

        return $query->orderBy('dte_prazo')->get();
    }

    private function getMinhasEntregasAgrupadas()
    {
        return $this->getMinhasEntregas()->groupBy(fn ($e) => $e->planoDeAcao->cod_plano_de_acao)->map(fn ($g) => [
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
            $query->whereHas('entrega.planoDeAcao.objetivo.perspectiva', fn ($q) => $q->where('cod_pei', $this->peiAtivo->cod_pei));
        }
        if ($this->organizacaoId) {
            $query->whereHas('entrega.planoDeAcao', fn ($q) => $q->where('cod_organizacao', $this->organizacaoId));
        }

        return $query->latest()->take(5)->get();
    }

    private function getChartBSC()
    {
        if (! $this->peiAtivo) {
            return [];
        }
        $service = app(IndicadorCalculoService::class);
        $ano = (int) $this->anoSelecionado;

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
        if (! empty($orgIds)) {
            $query->with(['objetivos' => function ($qObj) use ($orgIds) {
                $qObj->with(['indicadores' => function ($qInd) use ($orgIds) {
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
                }])->ordenadoPorNivel();
            }]);
        } else {
            // Carregamento padrão sem filtro de org (Visão Global)
            $query->with(['objetivos' => function ($qObj) {
                $qObj->with(['indicadores', 'planosAcao.entregas']);
            }]);
        }

        return $query->get()->map(function ($p) use ($service, $ano) {
            // CÁLCULO CENTRALIZADO
            $atingimento = $service->calcularAtingimentoPerspectiva($p, $ano);

            // Sem medição: barra nenhuma (null, não 0) e o rótulo diz por quê.
            return [
                'label' => $p->dsc_perspectiva.($atingimento === null ? ' (sem medição)' : ''),
                'count' => $atingimento,
                'color' => $this->getCorAtingimento($atingimento),
            ];
        })->toArray();
    }

    private function getChartRiscosNivel()
    {
        $riscos = Risco::where('cod_organizacao', $this->organizacaoId)->where('cod_pei', $this->peiAtivo?->cod_pei)->get();
        $niveis = ['Crítico' => ['c' => 0, 'col' => '#dc3545'], 'Alto' => ['c' => 0, 'col' => '#fd7e14'], 'Médio' => ['c' => 0, 'col' => '#ffc107'], 'Baixo' => ['c' => 0, 'col' => '#198754']];
        foreach ($riscos as $r) {
            $l = $r->getNivelRiscoLabel();
            if (isset($niveis[$l])) {
                $niveis[$l]['c']++;
            }
        }

        return ['labels' => array_keys($niveis), 'data' => array_column($niveis, 'c'), 'colors' => array_column($niveis, 'col')];
    }

    private function getChartPlanos()
    {
        $service = app(IndicadorCalculoService::class);
        $planosQuery = PlanoDeAcao::query();

        if ($this->peiAtivo) {
            $planosQuery->whereHas('objetivo.perspectiva', fn ($q) => $q->where('cod_pei', $this->peiAtivo->cod_pei));
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
            'Sem Entregas' => ['c' => 0, 'col' => '#6c757d'],
        ];

        foreach ($planos as $plano) {
            $calculo = $service->calcularProgressoPlanoNoAno($plano, (int) $this->anoSelecionado);
            $st = $calculo['status_calculado'];

            if (isset($statusCounts[$st])) {
                $statusCounts[$st]['c']++;
            } else {
                // Fallback para status desconhecidos
                $statusCounts['Em Andamento']['c']++;
            }
        }

        return collect($statusCounts)
            ->filter(fn ($v) => $v['c'] > 0) // Remove categorias vazias para limpar o gráfico
            ->map(fn ($v, $k) => ['label' => $k, 'count' => $v['c'], 'color' => $v['col']])
            ->values()
            ->toArray();
    }

    private function getChartEvolucao()
    {
        if (! $this->peiAtivo) {
            return ['labels' => [], 'data' => []];
        }

        // Buscar evoluções do ano selecionado vinculadas ao PEI
        // Só conta o que foi de fato lançado (realizado preenchido). Indicador
        // informativo (polaridade "Não Aplicável") não entra em média.
        // bln_acumulado vai junto: com Previsto em branco, o atingimento usa a
        // meta do mês (acumulado) ou a anual (não acumulado), decidido por ele.
        $evolucoes = EvolucaoIndicador::with('indicador:cod_indicador,dsc_polaridade,bln_acumulado')
            ->where('num_ano', $this->anoSelecionado)
            ->whereNotNull('vlr_realizado')
            ->whereHas('indicador.objetivo.perspectiva', fn ($q) => $q->where('cod_pei', $this->peiAtivo->cod_pei))
            ->get()
            ->reject(fn ($ev) => CalculoPolaridade::ehInformativo($ev->indicador?->dsc_polaridade));

        $dadosPorMes = [];
        for ($i = 1; $i <= 12; $i++) {
            // Se o ano for o atual, parar no mês atual
            if ($this->anoSelecionado == date('Y') && $i > date('n')) {
                break;
            }

            $evolucoesMes = $evolucoes->where('num_mes', $i);

            // Mês sem lançamento é lacuna (null), não 0%: zero desenharia uma
            // queda de desempenho que não aconteceu.
            if ($evolucoesMes->isEmpty()) {
                $dadosPorMes[] = null;

                continue;
            }

            // Atingimento segundo a polaridade de cada indicador, limitado a
            // 100% para um desvio isolado não distorcer a média.
            $dadosPorMes[] = round(
                $evolucoesMes->avg(fn ($ev) => min($ev->calcularAtingimento(), 100)),
                1
            );
        }

        $meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

        return [
            'labels' => array_slice($meses, 0, count($dadosPorMes)),
            'data' => $dadosPorMes,
        ];
    }

    private function getIQG(): array
    {
        if (! $this->peiAtivo) {
            return ['valor' => 0, 'tem_dados' => false, 'grau' => null, 'perspectivas' => []];
        }

        $service = app(IndicadorCalculoService::class);

        $iqg = $service->calcularIQG($this->peiAtivo->cod_pei, (int) $this->anoSelecionado);

        // Cada perspectiva com a cor da SUA faixa: a tela pintava todas com a
        // cor do índice geral, e uma perspectiva a 10% aparecia como "atenção".
        $iqg['perspectivas'] = collect($iqg['perspectivas'] ?? [])
            ->map(fn ($p) => $p + ['cor' => $this->getCorAtingimento($p['atingimento'] ?? null)])
            ->all();

        return $iqg;
    }

    private function getCorAtingimento($percentual)
    {
        // A cor sai da régua DESTE ciclo. Sem o filtro, o dashboard podia
        // acender com a faixa de outro PEI — e o número parecia certo.
        // Sem medição: cinza neutro, nunca a cor da pior faixa.
        if ($percentual === null) {
            return GrauSatisfacao::COR_SEM_REGUA;
        }

        return GrauSatisfacao::corDe(
            (float) $percentual,
            $this->peiAtivo?->cod_pei,
            (int) $this->anoSelecionado
        );
    }

    /**
     * Cobertura da Agenda 2030: quais dos 17 ODS têm objetivos estratégicos
     * vinculados neste ciclo PEI. Degrada graciosamente se as tabelas ODS
     * ainda não existirem.
     */
    private function getOdsCobertura(): array
    {
        $codPei = $this->peiAtivo?->cod_pei;
        // O total é o do cadastro de ODS, nunca um número escrito no código.
        $total = 0;

        if (! $codPei) {
            return ['cobertos' => [], 'total' => $total];
        }

        try {
            $total = ODS::count();
            $cobertos = ODS::whereHas('objetivos', function ($q) use ($codPei) {
                $q->whereHas('perspectiva', fn ($qp) => $qp->where('cod_pei', $codPei));
            })->pluck('num_ods')->map(fn ($n) => (int) $n)->toArray();
        } catch (\Throwable $e) {
            // Sem isto, a causa real desaparece: o cliente recebe uma
            // orientação genérica e não sobra rastro nenhum para investigar.
            report($e);

            $cobertos = [];
        }

        return ['cobertos' => $cobertos, 'total' => $total];
    }

    public function getMentorStatus()
    {
        if (! $this->organizacaoId || ! $this->peiAtivo) {
            return [
                'steps' => ['identidade' => false, 'mapa' => false, 'objetivos' => false, 'indicadores' => false, 'planos' => false],
                'percent' => 0,
            ];
        }

        $codPei = $this->peiAtivo->cod_pei;
        $orgId = $this->organizacaoId;

        // Verificar preenchimento das etapas
        $steps = [
            'identidade' => MissaoVisaoValores::where('cod_organizacao', $orgId)->exists(),
            'mapa' => Perspectiva::where('cod_pei', $codPei)->exists(),
            'objetivos' => Objetivo::whereHas('perspectiva', fn ($q) => $q->where('cod_pei', $codPei))->exists(),
            'indicadores' => Indicador::whereHas('objetivo.perspectiva', fn ($q) => $q->where('cod_pei', $codPei))->exists(),
            'planos' => PlanoDeAcao::whereHas('objetivo.perspectiva', fn ($q) => $q->where('cod_pei', $codPei))
                ->where('cod_organizacao', $orgId)->exists(),
        ];

        $filled = count(array_filter($steps));
        $total = count($steps);
        $percent = $total > 0 ? round(($filled / $total) * 100) : 0;

        return ['steps' => $steps, 'percent' => $percent];
    }
}
