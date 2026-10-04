<?php

namespace App\Services\Reports;

use App\Models\ActionPlan\LicaoAprendida;
use App\Models\ActionPlan\PlanoComunicacao;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\Raci;
use App\Models\Organization;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\RiskManagement\Risco;
use App\Models\StrategicPlanning\AnaliseAmbiental;
use App\Models\StrategicPlanning\AtividadeCadeiaValor;
use App\Models\StrategicPlanning\CalendarioEventoPei;
use App\Models\StrategicPlanning\CenarioProspectivo;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\InauguraPei;
use App\Models\StrategicPlanning\IntegracaoInstrumento;
use App\Models\StrategicPlanning\MissaoVisaoValores;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\ParteInteressada;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\Rae;
use App\Models\StrategicPlanning\TemaNorteador;
use App\Models\StrategicPlanning\Valor;
use App\Models\SystemSetting;
use App\Services\AI\AiServiceFactory;
use App\Services\IndicadorCalculoService;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportGenerationService
{
    /**
     * Fecha o PDF de QUALQUER relatório: aplica o cabeçalho, o rodapé e a
     * margem simétrica, e devolve os bytes.
     *
     * 🔴 Este é o ponto único que padroniza os onze relatórios. Antes, cada
     * um trazia o próprio cabeçalho em Blade — e "padronização" mantida por
     * onze arquivos parecidos dura até alguém mexer em um deles. Aqui, ou
     * todos mudam juntos, ou nenhum muda.
     *
     * A ORIENTAÇÃO é escolha de cada relatório e precisa bater com a
     * declarada no `@include` dos estilos: é o `@page` que reserva a margem
     * onde o cabeçalho é desenhado.
     */
    private function finalizar(
        $pdf,
        string $titulo,
        $organizacao = null,
        string $orientacao = 'portrait',
        ?string $direita = null
    ): string {
        (new AcabamentoPdf($orientacao))->aplicar($pdf, [
            'esquerda' => $organizacao?->nom_organizacao ?? 'Todas as unidades',
            'centro' => $titulo,
            'direita' => $direita,
            'site' => (string) SystemSetting::getValue('orgao_site', ''),
            'emitido_em' => now()->format('d/m/Y'),
        ]);

        return $pdf->output();
    }

    protected $calculoService;

    public function __construct(IndicadorCalculoService $calculoService)
    {
        $this->calculoService = $calculoService;
    }

    public function generateExecutivo($organizacaoId, $ano, $periodo, $perspectivaId = null)
    {
        $mesLimite = 12;
        switch ($periodo) {
            case '1_semestre': $mesLimite = 6;
                break;
            case '2_semestre': $mesLimite = 12;
                break;
            case '1_trimestre': $mesLimite = 3;
                break;
            case '2_trimestre': $mesLimite = 6;
                break;
            case '3_trimestre': $mesLimite = 9;
                break;
            case '4_trimestre': $mesLimite = 12;
                break;
            default: $mesLimite = ($ano == date('Y') ? date('n') : 12);
        }

        $organizacao = Organization::findOrFail($organizacaoId);
        $identidade = MissaoVisaoValores::where('cod_organizacao', $organizacaoId)->first() ?? new MissaoVisaoValores;

        $pei = PEI::doContexto();

        // 1. Valores (Identidade Cultural)
        $valores = Valor::where('cod_pei', $pei?->cod_pei)
            ->where('cod_organizacao', $organizacaoId)
            ->orderBy('nom_valor')
            ->get();

        // 2. Perspectivas e Objetivos (BSC)
        $queryPerspectivas = Perspectiva::where('cod_pei', $pei?->cod_pei);
        if ($perspectivaId) {
            $queryPerspectivas->where('cod_perspectiva', $perspectivaId);
        }
        $perspectivas = $queryPerspectivas->with(['objetivos.indicadores', 'objetivos.ods'])->ordenadoPorNivel()->get();

        // 3. Iniciativas (Ordenados por Perspectiva > Objetivo > Plano)
        $planos = PlanoDeAcao::where('cod_organizacao', $organizacaoId)
            ->with(['entregas.responsaveis', 'objetivo.perspectiva'])
            ->where(function ($q) use ($ano) {
                $q->whereYear('dte_inicio', '<=', $ano)
                    ->whereYear('dte_fim', '>=', $ano);
            })
            ->get()
            ->map(function ($plano) use ($ano) {
                // Calcular progresso real do ano usando o Service
                $calculo = $this->calculoService->calcularProgressoPlanoNoAno($plano, $ano);

                // Sobrescrever propriedades para exibição no relatório
                $plano->progresso_anual = $calculo['progresso'];
                $plano->status_anual = $calculo['status_calculado'];
                $plano->entregas_ano_count = $calculo['total_entregas'];
                $plano->detalhes_calculo = $calculo['detalhes'];

                return $plano;
            })
            ->sortBy([
                ['objetivo.perspectiva.num_nivel_hierarquico_apresentacao', 'asc'],
                ['objetivo.num_nivel_hierarquico_apresentacao', 'asc'],
                ['dsc_plano_de_acao', 'asc'],
            ]);

        // 4. Análise SWOT
        $swot = AnaliseAmbiental::swot()
            ->where('cod_pei', $pei?->cod_pei)
            ->where('cod_organizacao', $organizacaoId)
            ->get()
            ->groupBy('dsc_categoria');

        // 5. Gestão de Riscos (Sumário e Lista Detalhada)
        $riscosDetalhado = Risco::where('cod_organizacao', $organizacaoId)
            ->where('cod_pei', $pei?->cod_pei)
            ->orderByRaw('(num_probabilidade * num_impacto) DESC')
            ->get();

        $riscosSummary = Risco::where('cod_organizacao', $organizacaoId)
            ->selectRaw("
                CASE
                    WHEN (num_probabilidade * num_impacto) >= 16 THEN 'Crítico'
                    WHEN (num_probabilidade * num_impacto) >= 10 THEN 'Alto'
                    WHEN (num_probabilidade * num_impacto) >= 5 THEN 'Médio'
                    ELSE 'Baixo'
                END as nivel,
                count(*) as total
            ")
            ->groupByRaw('nivel')
            ->pluck('total', 'nivel')
            ->toArray();

        // 6. Graus de Satisfação (Para coerência de cores)
        // A régua do relatório é a do ciclo relatado. Sem o filtro, o PDF
        // podia pintar o farol com a faixa de outro PEI — e divergir da tela.
        $grausSatisfacao = GrauSatisfacao::doPei($pei?->cod_pei)->get();

        // Mapeamento de nomes de períodos
        $periodosMap = [
            'anual' => 'Anual (Completo)',
            '1_semestre' => '1º Semestre',
            '2_semestre' => '2º Semestre',
            '1_trimestre' => '1º Trimestre',
            '2_trimestre' => '2º Trimestre',
            '3_trimestre' => '3º Trimestre',
            '4_trimestre' => '4º Trimestre',
        ];
        $periodoNome = $periodosMap[$periodo] ?? $periodo;

        $filtros = [
            'ano' => $ano,
            'mesLimite' => $mesLimite,
            'periodo' => $periodoNome,
            'perspectiva' => $perspectivaId ? Perspectiva::find($perspectivaId)?->dsc_perspectiva : 'Todas',
        ];

        // --- INTEGRAÇÃO COM IA: Resumo e Análise Preditiva ---
        $aiSummary = null;
        $aiTrends = null;
        $aiEnabled = SystemSetting::getValue('ai_enabled', false);

        if ($aiEnabled) {
            $aiService = AiServiceFactory::make();
            if ($aiService) {
                // Preparar Estatísticas para o Resumo
                $stats = [
                    'totalObjetivos' => Objetivo::whereHas('perspectiva', fn ($q) => $q->where('cod_pei', $pei?->cod_pei))->count(),
                    'totalIndicadores' => Indicador::whereHas('objetivo.perspectiva', fn ($q) => $q->where('cod_pei', $pei?->cod_pei))->count(),
                    'totalPlanos' => PlanoDeAcao::where('cod_organizacao', $organizacaoId)->count(),
                    'riscosCriticos' => Risco::where('cod_organizacao', $organizacaoId)->where('cod_pei', $pei?->cod_pei)->criticos()->count(),
                ];
                $aiSummary = $aiService->summarizeStrategy($stats, $organizacao->nom_organizacao);

                // Preparar Dados de Tendência (Histórico recente de indicadores)
                $indicatorData = [];
                $topIndicadores = Indicador::whereHas('organizacoes', function ($q) use ($organizacaoId) {
                    $q->where('tab_organizacoes.cod_organizacao', $organizacaoId);
                })->with(['evolucoes' => fn ($q) => $q->where('num_ano', $ano)->orderBy('num_mes')])
                    ->take(5)->get();

                foreach ($topIndicadores as $ind) {
                    $evolucoes = $ind->evolucoes->map(fn ($e) => ['mes' => $e->num_mes, 'valor' => $e->vlr_realizado, 'previsto' => $e->vlr_previsto]);
                    $indicatorData[] = [
                        'nome' => $ind->nom_indicador,
                        'historico' => $evolucoes->toArray(),
                    ];
                }
                $aiTrends = $aiService->analyzeTrends($indicatorData, $organizacao->nom_organizacao);
            }
        }

        // Temas Norteadores (Antigos Objetivos Estratégicos)
        // No executivo original, estava buscando Objetivo::whereHas... o que parece ser Objetivos BSC.
        // Mas o nome da variável era $objetivosEstrategicos e no blade estava como "Objetivos Estratégicos".
        // Se a intenção era listar os Temas Norteadores, a query estava errada (buscava Objetivo).
        // Se a intenção era listar Objetivos BSC, o nome estava confuso.
        // Dado que "Temas Norteadores" são o nível estratégico, vou assumir que aqui devia ser TemaNorteador.
        // E no blade vou corrigir para "Temas Norteadores".
        // CORREÇÃO: No código original estava: $objetivosEstrategicos = Objetivo::whereHas...
        // Isso retorna Objetivos do BSC. Não vou mudar a lógica de qual dado é retornado se o relatório executivo mostra objetivos do BSC como destaque.
        // Mas espera, o blade mostrava: "Nenhum objetivo estratégico cadastrado".
        // Se eu mudar para TemaNorteador aqui, vou mudar o que é exibido.
        // "Objetivo Estratégico" -> "Tema Norteador".
        // Vou assumir que o usuário quer ver os TEMAS NORTEADORES (nível estratégico) aqui, pois é um relatório executivo.
        // Se antes mostrava Objetivos BSC, talvez fosse um erro ou decisão de design.
        // Mas como a tarefa é RENOMEAR a entidade, eu vou buscar a entidade renomeada.

        $temasNorteadores = TemaNorteador::where('cod_pei', $pei?->cod_pei)
            ->where('cod_organizacao', $organizacaoId)
            ->get();

        // Se eu quiser manter a lista de objetivos BSC, devo usar outra variável.
        // Vou manter apenas $temasNorteadores para substituir $objetivosEstrategicos.

        $pdf = Pdf::loadView('relatorios.executivo', compact(
            'organizacao', 'identidade', 'valores',
            'perspectivas', 'planos', 'filtros', 'swot', 'riscosSummary', 'riscosDetalhado', 'grausSatisfacao',
            'aiSummary', 'aiTrends', 'temasNorteadores'
        ));

        return [
            'content' => $this->finalizar($pdf, 'Relatório Executivo', $organizacao, 'portrait', 'Exercício '.$ano),
            'filename' => "Relatorio_Executivo_{$organizacao->sgl_organizacao}_{$ano}.pdf",
        ];
    }

    public function generateIdentidade($organizacaoId, $ano = null)
    {
        $ano = $ano ?? date('Y');
        $organizacao = Organization::findOrFail($organizacaoId);
        $identidade = MissaoVisaoValores::where('cod_organizacao', $organizacaoId)->first() ?? new MissaoVisaoValores;

        $pei = PEI::doContexto();

        // Carregar Valores
        $valores = Valor::where('cod_pei', $pei?->cod_pei)
            ->where('cod_organizacao', $organizacaoId)
            ->orderBy('nom_valor')
            ->get();

        // Carregar Temas Norteadores
        $temasNorteadores = TemaNorteador::where('cod_pei', $pei?->cod_pei)
            ->where('cod_organizacao', $organizacaoId)
            ->get();

        // Carregar Perspectivas e Objetivos para o Mapa (Com filtro de organização e cálculo unificado)
        // IDs para o Roll-up (mesma lógica do Mapa Livewire)
        $orgIds = [];
        if ($organizacaoId) {
            $org = Organization::find($organizacaoId);
            if ($org) {
                if (method_exists($org, 'getDescendantsAndSelfIds')) {
                    $orgIds = $org->getDescendantsAndSelfIds();
                } else {
                    $orgIds = [$organizacaoId];
                }
            }
        }

        $queryPerspectivas = Perspectiva::where('cod_pei', $pei?->cod_pei)->ordenadoPorNivel();

        if (! empty($orgIds)) {
            $queryPerspectivas->with(['objetivos' => function ($qObj) use ($orgIds) {
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
            $queryPerspectivas->with(['objetivos.indicadores', 'objetivos.planosAcao.entregas']);
        }

        $perspectivas = $queryPerspectivas->get()->map(function ($p) use ($ano) {
            $p->atingimento_calculado = $this->calculoService->calcularAtingimentoPerspectiva($p, $ano);

            // Injetar cálculo nos objetivos filhos
            foreach ($p->objetivos as $obj) {
                $obj->atingimento_calculado = $this->calculoService->calcularAtingimentoObjetivo($obj, $ano);
            }

            return $p;
        });

        // A régua do relatório é a do ciclo relatado. Sem o filtro, o PDF
        // podia pintar o farol com a faixa de outro PEI — e divergir da tela.
        $grausSatisfacao = GrauSatisfacao::doPei($pei?->cod_pei)->get();

        $getCorSatisfacao = fn ($percentual) => GrauSatisfacao::corDe(
            (float) $percentual,
            $pei?->cod_pei,
            (int) $ano
        );

        $filtros = ['ano' => $ano, 'mesLimite' => 12];

        $pdf = Pdf::loadView('relatorios.identidade', compact('organizacao', 'identidade', 'valores', 'temasNorteadores', 'perspectivas', 'grausSatisfacao', 'filtros', 'getCorSatisfacao'))
            ->setPaper('a4', 'landscape');

        return [
            'content' => $this->finalizar($pdf, 'Mapa Estratégico', $organizacao, 'landscape', 'Exercício '.$ano),
            'filename' => "Mapa_Estrategico_{$organizacao->sgl_organizacao}_{$ano}.pdf",
        ];
    }

    public function generateObjetivos($organizacaoId = null, $ano = null, $perspectivaId = null)
    {
        $ano = $ano ?? date('Y');
        $pei = PEI::doContexto();

        if (! $pei) {
            throw new \Exception('Nenhum ciclo PEI ativo encontrado.');
        }

        $organizacao = $organizacaoId ? Organization::find($organizacaoId) : null;

        $query = Perspectiva::where('cod_pei', $pei->cod_pei);
        if ($perspectivaId) {
            $query->where('cod_perspectiva', $perspectivaId);
        }

        $perspectivas = $query->with('objetivos.ods')->ordenadoPorNivel()->get();

        $filtros = [
            'ano' => $ano,
            'organizacao' => $organizacao ? $organizacao->nom_organizacao : 'Todas',
            'perspectiva' => $perspectivaId ? Perspectiva::find($perspectivaId)?->dsc_perspectiva : 'Todas',
        ];

        $pdf = Pdf::loadView('relatorios.objetivos', compact('pei', 'perspectivas', 'filtros', 'organizacao'));

        return [
            'content' => $this->finalizar($pdf, 'Objetivos Estratégicos', $organizacao, 'portrait', $pei?->dsc_pei),
            'filename' => "Objetivos_Estrategicos_{$ano}.pdf",
        ];
    }

    public function generateIndicadores($organizacaoId = null, $ano = null, $periodo = null)
    {
        $ano = $ano ?? date('Y');
        $periodo = $periodo ?? 'anual';

        $organizacao = $organizacaoId ? Organization::find($organizacaoId) : null;
        $query = Indicador::query();
        if ($organizacaoId) {
            // Mesmo critério da tela de Indicadores: o Super Admin vê a unidade e
            // as subordinadas. O PDF olhava só a unidade exata — para o órgão
            // raiz saíam 2 indicadores enquanto a tela mostrava 16.
            $orgIds = auth()->user()?->isSuperAdmin()
                ? (Organization::find($organizacaoId)?->getDescendantsAndSelfIds() ?? [$organizacaoId])
                : [$organizacaoId];

            // Agrupado: o orWhereHas solto anulava qualquer filtro somado depois.
            $query->where(function ($q) use ($orgIds) {
                $q->whereHas('organizacoes', fn ($o) => $o->whereIn('tab_organizacoes.cod_organizacao', $orgIds))
                    ->orWhereHas('planoDeAcao', fn ($p) => $p->whereIn('cod_organizacao', $orgIds));
            });
        }

        // Só o ciclo em contexto: o relatório misturava indicadores de todos os ciclos.
        if ($pei = PEI::doContexto()) {
            $query->where(function ($q) use ($pei) {
                $q->whereHas('objetivo.perspectiva', fn ($p) => $p->where('cod_pei', $pei->cod_pei))
                    ->orWhereHas('planoDeAcao.objetivo.perspectiva', fn ($p) => $p->where('cod_pei', $pei->cod_pei));
            });
        }
        $indicadores = $query->with(['objetivo', 'planoDeAcao'])->get();

        $periodosMap = [
            'anual' => 'Anual (Completo)',
            '1_semestre' => '1º Semestre',
            '2_semestre' => '2º Semestre',
            '1_trimestre' => '1º Trimestre',
            '2_trimestre' => '2º Trimestre',
            '3_trimestre' => '3º Trimestre',
            '4_trimestre' => '4º Trimestre',
        ];

        $filtros = [
            'ano' => $ano,
            'periodo' => $periodosMap[$periodo] ?? $periodo,
            'organizacao' => $organizacao ? $organizacao->nom_organizacao : 'Todas',
        ];

        // 🔴 A régua vai JUNTO. Sem ela, a view caía nos cortes 80/50 escritos
        // no próprio Blade — cortes que a organização nunca definiu, e que
        // divergem do farol do Mapa Estratégico para o mesmo indicador.
        $pei = PEI::doContexto();
        $grausSatisfacao = GrauSatisfacao::doPei($pei?->cod_pei, (int) $ano)->get();

        $pdf = Pdf::loadView('relatorios.indicadores', compact(
            'indicadores', 'organizacao', 'filtros', 'pei', 'ano', 'grausSatisfacao'
        ));

        return [
            'content' => $this->finalizar($pdf, 'Indicadores de Desempenho', $organizacao, 'landscape', 'Exercício '.$ano),
            'filename' => "Indicadores_Desempenho_{$ano}.pdf",
        ];
    }

    public function generatePlanos($organizacaoId = null, $ano = null)
    {
        $ano = $ano ?? date('Y');
        $organizacao = $organizacaoId ? Organization::find($organizacaoId) : null;

        $query = PlanoDeAcao::query()->with(['objetivo.perspectiva', 'entregas', 'tipoExecucao']);

        if ($organizacaoId) {
            $query->where('cod_organizacao', $organizacaoId);
        }

        // Filtrar por ano (vigência no ano selecionado)
        $query->where(function ($q) use ($ano) {
            $q->whereYear('dte_inicio', '<=', $ano)
                ->whereYear('dte_fim', '>=', $ano);
        });

        // Calcular progresso e status reais via Service unificado
        $planos = $query->orderBy('dte_fim')->get()->map(function ($plano) use ($ano) {
            $calculo = $this->calculoService->calcularProgressoPlanoNoAno($plano, (int) $ano);
            $plano->progresso_anual = $calculo['progresso'];
            $plano->status_anual = $calculo['status_calculado'];
            $plano->entregas_ano_count = $calculo['total_entregas'];

            return $plano;
        });

        // Sumário por status (calculado)
        $resumo = [
            'total' => $planos->count(),
            'concluidos' => $planos->where('status_anual', 'Concluído')->count(),
            'andamento' => $planos->where('status_anual', 'Em Andamento')->count(),
            'atrasados' => $planos->where('status_anual', 'Atrasado')->count(),
            'nao_iniciado' => $planos->whereIn('status_anual', ['Não Iniciado', 'Sem Entregas'])->count(),
            'progresso_medio' => $planos->count() > 0 ? round($planos->avg('progresso_anual'), 1) : 0,
            'orcamento_total' => $planos->sum('vlr_orcamento_previsto'),
        ];

        $pdf = Pdf::loadView('relatorios.planos', compact('planos', 'organizacao', 'ano', 'resumo'));
        $nomeArquivo = $organizacao ? "Planos_Acao_{$organizacao->sgl_organizacao}_{$ano}.pdf" : "Planos_Acao_{$ano}.pdf";

        return [
            'content' => $this->finalizar($pdf, 'Iniciativas', $organizacao, 'landscape', 'Exercício '.$ano),
            'filename' => $nomeArquivo,
        ];
    }

    public function generateRiscos($organizacaoId = null)
    {
        $organizacao = $organizacaoId ? Organization::find($organizacaoId) : null;

        $query = Risco::query()->with(['mitigacoes', 'ocorrencias']);

        if ($organizacaoId) {
            $query->where('cod_organizacao', $organizacaoId);
        }

        // Só o ciclo em contexto, como a tela de riscos.
        if ($pei = PEI::doContexto()) {
            $query->where('cod_pei', $pei->cod_pei);
        }

        $riscos = $query->orderByRaw('(num_probabilidade * num_impacto) DESC')->get();

        $pdf = Pdf::loadView('relatorios.riscos', compact('riscos', 'organizacao'));
        $nomeArquivo = $organizacao ? "Riscos_{$organizacao->sgl_organizacao}.pdf" : 'Riscos_Geral.pdf';

        return [
            'content' => $this->finalizar($pdf, 'Gestão de Riscos', $organizacao, 'landscape'),
            'filename' => $nomeArquivo,
        ];
    }

    public function generateIntegrado($organizacaoId, $ano, $periodo, $includeAi = true)
    {
        // 1. Reutilizar a lógica do Relatório Executivo como base

        $mesLimite = 12;
        switch ($periodo) {
            case '1_semestre': $mesLimite = 6;
                break;
            case '2_semestre': $mesLimite = 12;
                break;
            case '1_trimestre': $mesLimite = 3;
                break;
            case '2_trimestre': $mesLimite = 6;
                break;
            case '3_trimestre': $mesLimite = 9;
                break;
            case '4_trimestre': $mesLimite = 12;
                break;
            default: $mesLimite = ($ano == date('Y') ? date('n') : 12);
        }

        $organizacao = Organization::findOrFail($organizacaoId);
        $identidade = MissaoVisaoValores::where('cod_organizacao', $organizacaoId)->first() ?? new MissaoVisaoValores;
        $pei = PEI::doContexto();

        // Identidade & Valores
        $valores = Valor::where('cod_pei', $pei?->cod_pei)
            ->where('cod_organizacao', $organizacaoId)
            ->orderBy('nom_valor')
            ->get();

        // Estratégia (BSC) com Eager Loading profundo para evitar N+1
        // Indicadores do objetivo: só os ligados à unidade do relatório (e às
        // subordinadas), como no Mapa — antes entravam os de todas as unidades.
        // Sem unidade (só o Super Admin chega aqui assim), o relatório é da instituição toda.
        $orgsDoRelatorio = $organizacaoId ? Organization::descendentesEProprio($organizacaoId) : null;
        $perspectivas = Perspectiva::where('cod_pei', $pei?->cod_pei)
            ->with([
                'objetivos.indicadores' => fn ($q) => $q->when($orgsDoRelatorio !== null, fn ($q) => $q->whereIn(
                    'performance_indicators.tab_indicador.cod_indicador',
                    fn ($sub) => $sub->select('cod_indicador')
                        ->from('performance_indicators.rel_indicador_objetivo_organizacao')
                        ->whereIn('cod_organizacao', $orgsDoRelatorio)
                )),
                'objetivos.indicadores.evolucoes' => function ($q) use ($ano) {
                    $q->where('num_ano', $ano)->orderBy('num_mes');
                },
                'objetivos.indicadores.metasPorAno' => function ($q) use ($ano) {
                    $q->where('num_ano', $ano);
                },
                'objetivos.planosAcao',
                'objetivos.ods',
            ])
            ->ordenadoPorNivel()
            ->get();

        // Iniciativas
        $planos = PlanoDeAcao::where('cod_organizacao', $organizacaoId)
            ->with(['entregas.responsaveis', 'objetivo.perspectiva'])
            ->where(function ($q) use ($ano) {
                $q->whereYear('dte_inicio', '<=', $ano)
                    ->whereYear('dte_fim', '>=', $ano);
            })
            ->get()
            ->map(function ($plano) use ($ano) {
                // Calcular progresso real do ano usando o Service
                $calculo = $this->calculoService->calcularProgressoPlanoNoAno($plano, $ano);

                // Sobrescrever propriedades para exibição no relatório
                $plano->progresso_anual = $calculo['progresso'];
                $plano->status_anual = $calculo['status_calculado'];
                $plano->entregas_ano_count = $calculo['total_entregas'];
                $plano->detalhes_calculo = $calculo['detalhes'];

                return $plano;
            })
            ->sortBy([
                ['objetivo.perspectiva.num_nivel_hierarquico_apresentacao', 'asc'],
                ['objetivo.num_nivel_hierarquico_apresentacao', 'asc'],
                ['dsc_plano_de_acao', 'asc'],
            ]);

        // SWOT
        $swot = AnaliseAmbiental::swot()
            ->where('cod_pei', $pei?->cod_pei)
            ->where('cod_organizacao', $organizacaoId)
            ->get()
            ->groupBy('dsc_categoria');

        // Riscos Detalhados
        $riscosDetalhado = Risco::where('cod_organizacao', $organizacaoId)
            ->where('cod_pei', $pei?->cod_pei)
            ->with(['mitigacoes', 'ocorrencias'])
            ->orderByRaw('(num_probabilidade * num_impacto) DESC')
            ->get();

        // Riscos Summary
        $riscosSummary = Risco::where('cod_organizacao', $organizacaoId)
            ->selectRaw("
                CASE
                    WHEN (num_probabilidade * num_impacto) >= 16 THEN 'Crítico'
                    WHEN (num_probabilidade * num_impacto) >= 10 THEN 'Alto'
                    WHEN (num_probabilidade * num_impacto) >= 5 THEN 'Médio'
                    ELSE 'Baixo'
                END as nivel,
                count(*) as total
            ")
            ->groupByRaw('nivel')
            ->pluck('total', 'nivel')
            ->toArray();

        // Indicadores Completos
        $indicadoresDetalhados = Indicador::whereHas('organizacoes', function ($q) use ($organizacaoId) {
            $q->where('tab_organizacoes.cod_organizacao', $organizacaoId);
        })
            ->with(['objetivo', 'evolucoes' => function ($q) use ($ano) {
                $q->where('num_ano', $ano)->orderBy('num_mes');
            }])
            ->get();

        // A régua do relatório é a do ciclo relatado. Sem o filtro, o PDF
        // podia pintar o farol com a faixa de outro PEI — e divergir da tela.
        $grausSatisfacao = GrauSatisfacao::doPei($pei?->cod_pei)->get();

        $periodosMap = [
            'anual' => 'Anual (Completo)',
            '1_semestre' => '1º Semestre',
            '2_semestre' => '2º Semestre',
            '1_trimestre' => '1º Trimestre',
            '2_trimestre' => '2º Trimestre',
            '3_trimestre' => '3º Trimestre',
            '4_trimestre' => '4º Trimestre',
        ];
        $periodoNome = $periodosMap[$periodo] ?? $periodo;

        $filtros = [
            'ano' => $ano,
            'mesLimite' => $mesLimite,
            'periodo' => $periodoNome,
            'perspectiva' => 'Todas (Integrado)',
        ];

        // --- INTEGRAÇÃO COM IA ---
        $aiSummary = null;
        $aiTrends = null;
        $aiEnabled = SystemSetting::getValue('ai_enabled', false);

        if ($aiEnabled && $includeAi) {
            $aiService = AiServiceFactory::make();
            if ($aiService) {
                $stats = [
                    'totalObjetivos' => Objetivo::whereHas('perspectiva', fn ($q) => $q->where('cod_pei', $pei?->cod_pei))->count(),
                    'totalIndicadores' => $indicadoresDetalhados->count(),
                    'totalPlanos' => $planos->count(),
                    'riscosCriticos' => collect($riscosDetalhado)->where('num_nivel_risco', '>=', 16)->count(),
                ];
                $aiSummary = $aiService->summarizeStrategy($stats, $organizacao->nom_organizacao);
            }
        }

        // Temas Norteadores
        $temasNorteadores = TemaNorteador::where('cod_pei', $pei?->cod_pei)
            ->where('cod_organizacao', $organizacaoId)
            ->get();

        // ════════════════════════════════════════════════════════════════════
        // MÓDULOS NOVOS DO SISTEMA (incorporados ao Relatório Estratégico Integrado)
        // ════════════════════════════════════════════════════════════════════
        $codPei = $pei?->cod_pei;
        $planoIds = $planos->pluck('cod_plano_de_acao')->all();

        // Módulo 01 — Inaugurar e Integrar
        $inaugurar = $this->safe(fn () => InauguraPei::where('cod_pei', $codPei)->first());
        $integracoes = $this->safe(fn () => IntegracaoInstrumento::where('cod_pei', $codPei)->orderBy('num_ordem')->get(), collect());
        $eventosPei = $this->safe(fn () => CalendarioEventoPei::where('cod_pei', $codPei)->orderBy('dte_evento')->get(), collect());

        // Cadeia de Valor
        $cadeiaValor = $this->safe(fn () => AtividadeCadeiaValor::with('processos', 'perspectiva')
            ->where('cod_pei', $codPei)->orderBy('dsc_tipo')->orderBy('num_ordem')->get()->groupBy('dsc_tipo'), collect());

        // Análise Ambiental expandida
        $pestel = $this->safe(fn () => AnaliseAmbiental::pestel()
            ->where('cod_pei', $codPei)->where('cod_organizacao', $organizacaoId)->get()->groupBy('dsc_categoria'), collect());
        $partesInteressadas = $this->safe(fn () => ParteInteressada::where('cod_pei', $codPei)
            ->orderBy('num_influencia', 'desc')->orderBy('num_interesse', 'desc')->get(), collect());
        // Cenários são da unidade (a tela da SWOT já filtrava; o PDF trazia os de todas).
        $cenarios = $this->safe(fn () => CenarioProspectivo::where('cod_pei', $codPei)
            ->when($organizacaoId, fn ($q) => $q->where('cod_organizacao', $organizacaoId))
            ->orderBy('dsc_tipo')->get(), collect());

        // Partes Interessadas e Comunicação (Domínio 5)
        $comunicacoes = $this->safe(fn () => PlanoComunicacao::whereIn('cod_plano_de_acao', $planoIds)
            ->with('plano')->orderBy('num_ordem')->get(), collect());

        // RACI (Domínio 3)
        $racis = $this->safe(fn () => Raci::whereIn('cod_plano_de_acao', $planoIds)
            ->with(['usuario', 'plano'])->get()->groupBy('cod_plano_de_acao'), collect());

        // Impacto e Aprendizado (Domínio 7) — Lições Aprendidas
        $licoesAprendidas = $this->safe(fn () => LicaoAprendida::whereIn('cod_plano_de_acao', $planoIds)
            ->with('plano')->orderBy('dsc_tipo')->get()->groupBy('dsc_tipo'), collect());

        // Monitorar e Avaliar — RAE
        $raes = $this->safe(fn () => Rae::where('cod_pei', $codPei)
            ->where('cod_organizacao', $organizacaoId)->orderByDesc('dte_referencia')->get(), collect());

        // Agenda 2030 — aderência institucional (PEI ↔ ODS) e cobertura por objetivos
        $odsAderencia = $this->safe(fn () => $pei?->ods()->get() ?? collect(), collect());

        $odsPorObjetivo = [];
        foreach ($perspectivas as $persp) {
            foreach ($persp->objetivos as $obj) {
                foreach ($obj->ods ?? [] as $o) {
                    if (! isset($odsPorObjetivo[$o->num_ods])) {
                        $odsPorObjetivo[$o->num_ods] = ['ods' => $o, 'objetivos' => []];
                    }
                    $odsPorObjetivo[$o->num_ods]['objetivos'][] = $obj->nom_objetivo;
                }
            }
        }
        ksort($odsPorObjetivo);

        // Renderização da View Integrada
        $pdf = Pdf::loadView('relatorios.integrado', compact(
            'organizacao', 'identidade', 'valores',
            'perspectivas', 'planos', 'filtros', 'swot', 'riscosSummary', 'riscosDetalhado', 'grausSatisfacao',
            'aiSummary', 'aiTrends', 'indicadoresDetalhados', 'temasNorteadores',
            // módulos novos
            'inaugurar', 'integracoes', 'eventosPei', 'cadeiaValor', 'pestel',
            'partesInteressadas', 'cenarios', 'comunicacoes', 'racis', 'licoesAprendidas', 'raes', 'pei',
            // Agenda 2030
            'odsAderencia', 'odsPorObjetivo'
        ));

        return [
            'content' => $this->finalizar($pdf, 'Relatório Estratégico Integrado', $organizacao, 'landscape', 'Exercício '.$ano),
            'filename' => "Relatorio_Estrategico_Integrado_{$organizacao->sgl_organizacao}_{$ano}.pdf",
        ];
    }

    /**
     * Executa uma query de módulo de forma resiliente: se a tabela ainda não
     * foi migrada ou ocorrer erro, retorna o fallback em vez de quebrar o PDF.
     */
    private function safe(callable $fn, $fallback = null)
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            return $fallback;
        }
    }
}
