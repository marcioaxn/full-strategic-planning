<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\PEI\PEI;
use App\Models\PEI\Perspectiva;
use App\Models\PEI\MissaoVisaoValores;
use App\Models\PEI\Valor;
use App\Models\PEI\Indicador;
use App\Models\PEI\PlanoDeAcao;
use App\Exports\ObjetivosExport;
use App\Exports\IndicadoresExport;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class RelatorioController extends Controller
{
    public function identidade($organizacaoId)
    {
        $organizacao = Organization::findOrFail($organizacaoId);
        $identidade = MissaoVisaoValores::where('cod_organizacao', $organizacaoId)->first();
        $valores = Valor::where('cod_organizacao', $organizacaoId)->orderBy('nom_valor')->get();
        if (!$identidade) { $identidade = new MissaoVisaoValores(); }
        $pdf = Pdf::loadView('relatorios.identidade', compact('organizacao', 'identidade', 'valores'));
        return $pdf->download("Identidade_{$organizacao->sgl_organizacao}.pdf");
    }

    public function objetivosPdf()
    {
        $pei = PEI::ativos()->first();
        if (!$pei) { return back(); }
        $perspectivas = Perspectiva::where('cod_pei', $pei->cod_pei)->with('objetivos')->ordenadoPorNivel()->get();
        $pdf = Pdf::loadView('relatorios.objetivos', compact('pei', 'perspectivas'));
        return $pdf->download("Objetivos_Estrategicos.pdf");
    }

    public function objetivosExcel()
    {
        $pei = PEI::ativos()->first();
        if (!$pei) { return back(); }
        return Excel::download(new ObjetivosExport($pei->cod_pei), "Objetivos_Estrategicos.xlsx");
    }

    public function indicadoresPdf($organizacaoId = null)
    {
        $organizacaoId = $organizacaoId ?? session('organizacao_selecionada_id');
        $organizacao = Organization::find($organizacaoId);
        $query = Indicador::query();
        if ($organizacaoId) {
            $query->whereHas('organizacoes', function($q) use ($organizacaoId) {
                $q->where('public.tab_organizacoes.cod_organizacao', $organizacaoId);
            })->orWhereHas('planoDeAcao', function($q) use ($organizacaoId) {
                $q->where('cod_organizacao', $organizacaoId);
            });
        }
        $indicadores = $query->with(['objetivoEstrategico', 'planoDeAcao'])->get();
        $pdf = Pdf::loadView('relatorios.indicadores', compact('indicadores', 'organizacao'));
        return $pdf->download("Indicadores_Desempenho.pdf");
    }

    public function indicadoresExcel($organizacaoId = null)
    {
        $organizacaoId = $organizacaoId ?? session('organizacao_selecionada_id');
        return Excel::download(new IndicadoresExport($organizacaoId), "Indicadores_Desempenho.xlsx");
    }

    public function executivo($organizacaoId = null)
    {
        $organizacaoId = $organizacaoId ?? session('organizacao_selecionada_id');
        if (!$organizacaoId) return back();

        $organizacao = Organization::findOrFail($organizacaoId);
        $identidade = MissaoVisaoValores::where('cod_organizacao', $organizacaoId)->first() ?? new MissaoVisaoValores();
        
        $pei = PEI::ativos()->first();
        $perspectivas = $pei ? Perspectiva::where('cod_pei', $pei->cod_pei)->with('objetivos.indicadores')->ordenadoPorNivel()->get() : [];
        
        $planos = PlanoDeAcao::where('cod_organizacao', $organizacaoId)->with('entregas')->orderBy('dte_fim')->get();

        $pdf = Pdf::loadView('relatorios.executivo', compact('organizacao', 'identidade', 'perspectivas', 'planos'));
        return $pdf->download("Relatorio_Executivo_{$organizacao->sgl_organizacao}.pdf");
    }
}
