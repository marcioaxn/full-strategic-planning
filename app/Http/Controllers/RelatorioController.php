<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\PEI\PEI;
use App\Models\PEI\Perspectiva;
use App\Models\PEI\MissaoVisaoValores;
use App\Models\PEI\Valor;
use App\Models\PEI\Objetivo;
use App\Models\PEI\ObjetivoEstrategico;
use App\Models\PEI\Indicador;
use App\Models\PEI\PlanoDeAcao;
use App\Models\PEI\GrauSatisfacao;
use App\Models\Risco;
use App\Exports\ObjetivosExport;
use App\Exports\IndicadoresExport;
use App\Exports\PlanosExport;
use App\Exports\RiscosExport;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class RelatorioController extends Controller
{
    // ... outros métodos ...

    public function executivo(Request $request, $organizacaoId = null)
    {
        $organizacaoId = $organizacaoId ?? $request->query('organizacaoId') ?? session('organizacao_selecionada_id');
        if (!$organizacaoId) return back();

        $ano = $request->query('ano') ?? date('Y');
        $periodo = $request->query('periodo') ?? 'anual';
        $perspectivaId = $request->query('perspectiva');

        // Traduzir período para mês limite de cálculo
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
        
        // 1. Valores e Objetivos Estratégicos (Nova Entidade)
        $valores = Valor::where('cod_pei', $pei?->cod_pei)
            ->where('cod_organizacao', $organizacaoId)
            ->orderBy('nom_valor')
            ->get();

        $objetivosEstrategicos = ObjetivoEstrategico::where('cod_pei', $pei?->cod_pei)
            ->where('cod_organizacao', $organizacaoId)
            ->orderBy('created_at', 'asc')
            ->get();

        // 2. Perspectivas e Objetivos (BSC)
        $queryPerspectivas = Perspectiva::where('cod_pei', $pei?->cod_pei);
        if ($perspectivaId) {
            $queryPerspectivas->where('cod_perspectiva', $perspectivaId);
        }
        $perspectivas = $queryPerspectivas->with(['objetivos.indicadores'])->ordenadoPorNivel()->get();
        
        // 3. Planos de Ação (Ordenados por Perspectiva > Objetivo > Plano)
        $planos = PlanoDeAcao::where('cod_organizacao', $organizacaoId)
            ->with(['entregas', 'objetivo.perspectiva'])
            ->where(function($q) use ($ano) {
                $q->whereYear('dte_inicio', '<=', $ano)
                  ->whereYear('dte_fim', '>=', $ano);
            })
            ->get()
            ->sortBy([
                ['objetivo.perspectiva.num_nivel_hierarquico_apresentacao', 'asc'],
                ['objetivo.num_nivel_hierarquico_apresentacao', 'asc'],
                ['dsc_plano_de_acao', 'asc']
            ]);

        // 4. Análise SWOT
        $swot = \App\Models\PEI\AnaliseAmbiental::swot() 
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

        $pdf = Pdf::loadView('relatorios.executivo', compact(
            'organizacao', 'identidade', 'valores', 'objetivosEstrategicos', 
            'perspectivas', 'planos', 'filtros', 'swot', 'riscosSummary', 'riscosDetalhado', 'grausSatisfacao'
        ));
        
        return $pdf->download("Relatorio_Executivo_{$organizacao->sgl_organizacao}_{$ano}.pdf");
    }

    public function identidade($organizacaoId)
    {
        $organizacao = Organization::findOrFail($organizacaoId);
        $identidade = MissaoVisaoValores::where('cod_organizacao', $organizacaoId)->first();
        $valores = Valor::where('cod_organizacao', $organizacaoId)->orderBy('nom_valor')->get();
        if (!$identidade) { $identidade = new MissaoVisaoValores(); }
        $pdf = Pdf::loadView('relatorios.identidade', compact('organizacao', 'identidade', 'valores'));
        return $pdf->download("Identidade_{$organizacao->sgl_organizacao}.pdf");
    }

    public function objetivosPdf(Request $request)
    {
        $organizacaoId = $request->query('organizacao_id');
        $perspectivaId = $request->query('perspectiva');
        $ano = $request->query('ano') ?? date('Y');

        $pei = PEI::ativos()->first();
        if (!$pei) { return back(); }

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
        return $pdf->download("Objetivos_Estrategicos_{$ano}.pdf");
    }

    public function objetivosExcel()
    {
        $pei = PEI::ativos()->first();
        if (!$pei) { return back(); }
        return Excel::download(new ObjetivosExport($pei->cod_pei), "Objetivos_Estrategicos.xlsx");
    }

    public function indicadoresPdf(Request $request, $organizacaoId = null)
    {
        $organizacaoId = $organizacaoId ?? $request->query('organizacaoId') ?? session('organizacao_selecionada_id');
        $ano = $request->query('ano') ?? date('Y');
        $periodo = $request->query('periodo') ?? 'anual';

        $organizacao = Organization::find($organizacaoId);
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
        return $pdf->download("Indicadores_Desempenho_{$ano}.pdf");
    }

    public function indicadoresExcel($organizacaoId = null)
    {
        $organizacaoId = $organizacaoId ?? session('organizacao_selecionada_id');
        return Excel::download(new IndicadoresExport($organizacaoId), "Indicadores_Desempenho.xlsx");
    }

    public function planosPdf(Request $request)
    {
        $organizacaoId = $request->query('organizacao_id') ?? session('organizacao_selecionada_id');
        $ano = $request->query('ano') ?? date('Y');

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
        return $pdf->download($nomeArquivo);
    }

    public function planosExcel(Request $request)
    {
        $organizacaoId = $request->query('organizacao_id') ?? session('organizacao_selecionada_id');
        $ano = $request->query('ano') ?? date('Y');

        $organizacao = $organizacaoId ? Organization::find($organizacaoId) : null;
        $nomeArquivo = $organizacao ? "Planos_Acao_{$organizacao->sgl_organizacao}_{$ano}.xlsx" : "Planos_Acao_{$ano}.xlsx";

        return Excel::download(new PlanosExport($organizacaoId, $ano), $nomeArquivo);
    }

    public function riscosPdf(Request $request)
    {
        $organizacaoId = $request->query('organizacao_id') ?? session('organizacao_selecionada_id');

        $organizacao = $organizacaoId ? Organization::find($organizacaoId) : null;

        $query = Risco::query()->with(['mitigacoes', 'ocorrencias']);

        if ($organizacaoId) {
            $query->where('cod_organizacao', $organizacaoId);
        }

        $riscos = $query->orderByRaw('(num_probabilidade * num_impacto) DESC')->get();

        $pdf = Pdf::loadView('relatorios.riscos', compact('riscos', 'organizacao'));
        $nomeArquivo = $organizacao ? "Riscos_{$organizacao->sgl_organizacao}.pdf" : "Riscos_Geral.pdf";
        return $pdf->download($nomeArquivo);
    }

    public function riscosExcel(Request $request)
    {
        $organizacaoId = $request->query('organizacao_id') ?? session('organizacao_selecionada_id');

        $organizacao = $organizacaoId ? Organization::find($organizacaoId) : null;
        $nomeArquivo = $organizacao ? "Riscos_{$organizacao->sgl_organizacao}.xlsx" : "Riscos_Geral.xlsx";

        return Excel::download(new RiscosExport($organizacaoId), $nomeArquivo);
    }
}
