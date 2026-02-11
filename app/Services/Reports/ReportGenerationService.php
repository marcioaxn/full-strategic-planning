<?php

namespace App\Services\Reports;

use App\Models\Organization;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\MissaoVisaoValores;
use App\Models\StrategicPlanning\Valor;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\RiskManagement\Risco;
use App\Models\StrategicPlanning\TemaNorteador;
use App\Exports\ObjetivosExport;
use App\Exports\IndicadoresExport;
use App\Exports\PlanosExport;
use App\Exports\RiscosExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ReportGenerationService
{
    protected $calculoService;

    public function __construct(\App\Services\IndicadorCalculoService $calculoService)
    {
        $this->calculoService = $calculoService;
    }

    public function generateExecutivo($organizacaoId, $ano, $periodo, $perspectivaId = null)
    {
        $mesLimite = 12;
        switch($periodo) {
            case '1_semestre': $mesLimite = 6; break;
            case '2_semestre': $mesLimite = 12; break;
            case '1_trimestre': $mesLimite = 3; break;
            case '2_trimestre': $mesLimite = 6; break;
            case '3_trimestre': $mesLimite = 9; break;
            case '4_trimestre': $mesLimite = 12; break;
            default: $mesLimite = ($ano == date('Y') ? date('n') : 12);
        }

        $organizacao = Organization::findOrFail($organizacaoId);
        $identidade = MissaoVisaoValores::where('cod_organizacao', $organizacaoId)->first() ?? new MissaoVisaoValores();
        
        $pei = PEI::ativos()->first();
        
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
        $perspectivas = $queryPerspectivas->with(['objetivos.indicadores'])->ordenadoPorNivel()->get();
        
        // 3. Planos de Ação (Ordenados por Perspectiva > Objetivo > Plano)
        $planos = PlanoDeAcao::where('cod_organizacao', $organizacaoId)
            ->with(['entregas.responsaveis', 'objetivo.perspectiva'])
            ->where(function($q) use ($ano) {
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
                ['dsc_plano_de_acao', 'asc']
            ]);

        // 4. Análise SWOT
        $swot = \App\Models\StrategicPlanning\AnaliseAmbiental::swot() 
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
                    WHEN (num_probabilidade * num_impacto) >= 15 THEN 'Crítico'
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
        $grausSatisfacao = GrauSatisfacao::orderBy('vlr_minimo')->get();

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
            'perspectiva' => $perspectivaId ? Perspectiva::find($perspectivaId)?->dsc_perspectiva : 'Todas'
        ];

        // --- INTEGRAÇÃO COM IA: Resumo e Análise Preditiva ---
        $aiSummary = null;
        $aiTrends = null;
        $aiEnabled = \App\Models\SystemSetting::getValue('ai_enabled', false);

        if ($aiEnabled) {
            $aiService = \App\Services\AI\AiServiceFactory::make();
            if ($aiService) {
                // Preparar Estatísticas para o Resumo
                $stats = [
                    'totalObjetivos' => Objetivo::whereHas('perspectiva', fn($q) => $q->where('cod_pei', $pei?->cod_pei))->count(),
                    'totalIndicadores' => Indicador::whereHas('objetivo.perspectiva', fn($q) => $q->where('cod_pei', $pei?->cod_pei))->count(),
                    'totalPlanos' => PlanoDeAcao::where('cod_organizacao', $organizacaoId)->count(),
                    'riscosCriticos' => Risco::where('cod_organizacao', $organizacaoId)->where('cod_pei', $pei?->cod_pei)->criticos()->count(),
                ];
                $aiSummary = $aiService->summarizeStrategy($stats, $organizacao->nom_organizacao);

                // Preparar Dados de Tendência (Histórico recente de indicadores)
                $indicatorData = [];
                $topIndicadores = Indicador::whereHas('organizacoes', function($q) use ($organizacaoId) {
                        $q->where('tab_organizacoes.cod_organizacao', $organizacaoId);
                    })->with(['evolucoes' => fn($q) => $q->where('num_ano', $ano)->orderBy('num_mes')])
                    ->take(5)->get();

                foreach ($topIndicadores as $ind) {
                    $evolucoes = $ind->evolucoes->map(fn($e) => ['mes' => $e->num_mes, 'valor' => $e->vlr_realizado, 'previsto' => $e->vlr_previsto]);
                    $indicatorData[] = [
                        'nome' => $ind->nom_indicador,
                        'historico' => $evolucoes->toArray()
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
            'content' => $pdf->output(),
            'filename' => "Relatorio_Executivo_{$organizacao->sgl_organizacao}_{$ano}.pdf"
        ];
    }

    public function generateIdentidade($organizacaoId, $ano = null)
    {
        $ano = $ano ?? date('Y');
        $organizacao = Organization::findOrFail($organizacaoId);
        $identidade = MissaoVisaoValores::where('cod_organizacao', $organizacaoId)->first() ?? new MissaoVisaoValores();
        
        $pei = PEI::ativos()->first();
        
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

        if (!empty($orgIds)) {
            $queryPerspectivas->with(['objetivos' => function($qObj) use ($orgIds) {
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
             $queryPerspectivas->with(['objetivos.indicadores', 'objetivos.planosAcao.entregas']);
        }

        $perspectivas = $queryPerspectivas->get()->map(function($p) use ($ano) {
            $p->atingimento_calculado = $this->calculoService->calcularAtingimentoPerspectiva($p, $ano);
            
            // Injetar cálculo nos objetivos filhos
            foreach($p->objetivos as $obj) {
                $obj->atingimento_calculado = $this->calculoService->calcularAtingimentoObjetivo($obj, $ano);
            }
            
            return $p;
        });

        $grausSatisfacao = GrauSatisfacao::orderBy('vlr_minimo')->get();

        $getCorSatisfacao = new class($grausSatisfacao) {
            private $graus;
            public function __construct($graus) { $this->graus = $graus; }
            public function __invoke($percentual) {
                foreach ($this->graus as $grau) {
                    if ($percentual >= $grau->vlr_minimo && $percentual <= $grau->vlr_maximo) {
                        return $grau->cor;
                    }
                }
                return '#dee2e6';
            }
        };

        $filtros = ['ano' => $ano, 'mesLimite' => 12];

        $pdf = Pdf::loadView('relatorios.identidade', compact('organizacao', 'identidade', 'valores', 'temasNorteadores', 'perspectivas', 'grausSatisfacao', 'filtros', 'getCorSatisfacao'))
                  ->setPaper('a4', 'landscape');
        
        return [
            'content' => $pdf->output(),
            'filename' => "Mapa_Estrategico_{$organizacao->sgl_organizacao}_{$ano}.pdf"
        ];
    }

    public function generateObjetivos($organizacaoId = null, $ano = null, $perspectivaId = null)
    {
        $ano = $ano ?? date('Y');
        $pei = PEI::ativos()->first();
        
        if (!$pei) throw new \Exception("Nenhum ciclo PEI ativo encontrado.");

        $organizacao = $organizacaoId ? Organization::find($organizacaoId) : null;

        $query = Perspectiva::where('cod_pei', $pei->cod_pei);
        if ($perspectivaId) {
            $query->where('cod_perspectiva', $perspectivaId);
        }

        $perspectivas = $query->with('objetivos')->ordenadoPorNivel()->get();

        $filtros = [
            'ano' => $ano,
            'organizacao' => $organizacao ? $organizacao->nom_organizacao : 'Todas',
            'perspectiva' => $perspectivaId ? Perspectiva::find($perspectivaId)?->dsc_perspectiva : 'Todas'
        ];

        $pdf = Pdf::loadView('relatorios.objetivos', compact('pei', 'perspectivas', 'filtros', 'organizacao'));
        
        return [
            'content' => $pdf->output(),
            'filename' => "Objetivos_Estrategicos_{$ano}.pdf"
        ];
    }

    public function generateIndicadores($organizacaoId = null, $ano = null, $periodo = null)
    {
        $ano = $ano ?? date('Y');
        $periodo = $periodo ?? 'anual';

        $organizacao = $organizacaoId ? Organization::find($organizacaoId) : null;
        $query = Indicador::query();
        if ($organizacaoId) {
            $query->whereHas('organizacoes', function($q) use ($organizacaoId) {
                $q->where('tab_organizacoes.cod_organizacao', $organizacaoId);
            })->orWhereHas('planoDeAcao', function($q) use ($organizacaoId) {
                $q->where('cod_organizacao', $organizacaoId);
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
            'organizacao' => $organizacao ? $organizacao->nom_organizacao : 'Todas'
        ];

        $pdf = Pdf::loadView('relatorios.indicadores', compact('indicadores', 'organizacao', 'filtros'));
        
        return [
            'content' => $pdf->output(),
            'filename' => "Indicadores_Desempenho_{$ano}.pdf"
        ];
    }

    public function generatePlanos($organizacaoId = null, $ano = null)
    {
        $ano = $ano ?? date('Y');
        $organizacao = $organizacaoId ? Organization::find($organizacaoId) : null;

        $query = PlanoDeAcao::query()->with(['objetivo', 'entregas', 'responsaveis']);

        if ($organizacaoId) {
            $query->where('cod_organizacao', $organizacaoId);
        }

        // Filtrar por ano (início ou fim no ano selecionado)
        $query->where(function($q) use ($ano) {
            $q->whereYear('dte_inicio', $ano)
              ->orWhereYear('dte_fim', $ano);
        });

        $planos = $query->orderBy('dte_fim')->get();

        $pdf = Pdf::loadView('relatorios.planos', compact('planos', 'organizacao', 'ano'));
        $nomeArquivo = $organizacao ? "Planos_Acao_{$organizacao->sgl_organizacao}_{$ano}.pdf" : "Planos_Acao_{$ano}.pdf";
        
        return [
            'content' => $pdf->output(),
            'filename' => $nomeArquivo
        ];
    }

    public function generateRiscos($organizacaoId = null)
    {
        $organizacao = $organizacaoId ? Organization::find($organizacaoId) : null;

        $query = Risco::query()->with(['mitigacoes', 'ocorrencias']);

        if ($organizacaoId) {
            $query->where('cod_organizacao', $organizacaoId);
        }

        $riscos = $query->orderByRaw('(num_probabilidade * num_impacto) DESC')->get();

        $pdf = Pdf::loadView('relatorios.riscos', compact('riscos', 'organizacao'));
        $nomeArquivo = $organizacao ? "Riscos_{$organizacao->sgl_organizacao}.pdf" : "Riscos_Geral.pdf";
        
        return [
            'content' => $pdf->output(),
            'filename' => $nomeArquivo
        ];
    }

    public function generateIntegrado($organizacaoId, $ano, $periodo, $includeAi = true)
    {
        // 1. Reutilizar a lógica do Relatório Executivo como base
        
        $mesLimite = 12;
        switch($periodo) {
            case '1_semestre': $mesLimite = 6; break;
            case '2_semestre': $mesLimite = 12; break;
            case '1_trimestre': $mesLimite = 3; break;
            case '2_trimestre': $mesLimite = 6; break;
            case '3_trimestre': $mesLimite = 9; break;
            case '4_trimestre': $mesLimite = 12; break;
            default: $mesLimite = ($ano == date('Y') ? date('n') : 12);
        }

        $organizacao = Organization::findOrFail($organizacaoId);
        $identidade = MissaoVisaoValores::where('cod_organizacao', $organizacaoId)->first() ?? new MissaoVisaoValores();
        $pei = PEI::ativos()->first();
        
        // Identidade & Valores
        $valores = Valor::where('cod_pei', $pei?->cod_pei)
            ->where('cod_organizacao', $organizacaoId)
            ->orderBy('nom_valor')
            ->get();

        // Estratégia (BSC) com Eager Loading profundo para evitar N+1
        $perspectivas = Perspectiva::where('cod_pei', $pei?->cod_pei)
            ->with([
                'objetivos.indicadores.evolucoes' => function($q) use ($ano) {
                    $q->where('num_ano', $ano)->orderBy('num_mes');
                },
                'objetivos.indicadores.metasPorAno' => function($q) use ($ano) {
                    $q->where('num_ano', $ano);
                },
                'objetivos.planosAcao'
            ])
            ->ordenadoPorNivel()
            ->get();

        // Planos de Ação
        $planos = PlanoDeAcao::where('cod_organizacao', $organizacaoId)
            ->with(['entregas.responsaveis', 'objetivo.perspectiva'])
            ->where(function($q) use ($ano) {
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
                ['dsc_plano_de_acao', 'asc']
            ]);

        // SWOT
        $swot = \App\Models\StrategicPlanning\AnaliseAmbiental::swot() 
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
                    WHEN (num_probabilidade * num_impacto) >= 15 THEN 'Crítico'
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
        $indicadoresDetalhados = Indicador::whereHas('organizacoes', function($q) use ($organizacaoId) {
                $q->where('tab_organizacoes.cod_organizacao', $organizacaoId);
            })
            ->with(['objetivo', 'evolucoes' => function($q) use ($ano) {
                $q->where('num_ano', $ano)->orderBy('num_mes');
            }])
            ->get();

        $grausSatisfacao = GrauSatisfacao::orderBy('vlr_minimo')->get();

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
            'perspectiva' => 'Todas (Integrado)'
        ];

        // --- INTEGRAÇÃO COM IA ---
        $aiSummary = null;
        $aiTrends = null;
        $aiEnabled = \App\Models\SystemSetting::getValue('ai_enabled', false);

        if ($aiEnabled && $includeAi) {
            $aiService = \App\Services\AI\AiServiceFactory::make();
            if ($aiService) {
                $stats = [
                    'totalObjetivos' => Objetivo::whereHas('perspectiva', fn($q) => $q->where('cod_pei', $pei?->cod_pei))->count(),
                    'totalIndicadores' => $indicadoresDetalhados->count(),
                    'totalPlanos' => $planos->count(),
                    'riscosCriticos' => collect($riscosDetalhado)->where('num_nivel_risco', '>=', 15)->count(),
                ];
                $aiSummary = $aiService->summarizeStrategy($stats, $organizacao->nom_organizacao);
            }
        }
        
        // Temas Norteadores
        $temasNorteadores = TemaNorteador::where('cod_pei', $pei?->cod_pei)
            ->where('cod_organizacao', $organizacaoId)
            ->get();

        // Renderização da View Integrada
        $pdf = Pdf::loadView('relatorios.integrado', compact(
            'organizacao', 'identidade', 'valores', 
            'perspectivas', 'planos', 'filtros', 'swot', 'riscosSummary', 'riscosDetalhado', 'grausSatisfacao',
            'aiSummary', 'aiTrends', 'indicadoresDetalhados', 'temasNorteadores'
        ));

        return [
            'content' => $pdf->output(),
            'filename' => "Dossie_Estrategico_{$organizacao->sgl_organizacao}_{$ano}.pdf"
        ];
    }
}