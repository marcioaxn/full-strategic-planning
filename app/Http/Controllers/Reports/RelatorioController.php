<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\ReportGenerationService;
use App\Exports\ObjetivosExport;
use App\Exports\IndicadoresExport;
use App\Exports\PlanosExport;
use App\Exports\RiscosExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RelatorioController extends Controller
{
    protected $reportService;

    public function __construct(ReportGenerationService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function executivo(Request $request, $organizacaoId = null)
    {
        $organizacaoId = $organizacaoId ?? $request->query('organizacaoId') ?? session('organizacao_selecionada_id');
        if (!$organizacaoId) return back();

        $ano = $request->query('ano') ?? session('ano_selecionado') ?? date('Y');
        $periodo = $request->query('periodo') ?? 'anual';
        $perspectivaId = $request->query('perspectiva');

        $result = $this->reportService->generateExecutivo($organizacaoId, $ano, $periodo, $perspectivaId);
        
        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename']);
    }

    public function identidade(Request $request, $organizacaoId)
    {
        $ano = $request->query('ano') ?? session('ano_selecionado') ?? date('Y');
        $result = $this->reportService->generateIdentidade($organizacaoId, $ano);
        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename']);
    }

    public function objetivosPdf(Request $request)
    {
        $organizacaoId = $request->query('organizacao_id');
        $perspectivaId = $request->query('perspectiva');
        $ano = $request->query('ano') ?? date('Y');

        $result = $this->reportService->generateObjetivos($organizacaoId, $ano, $perspectivaId);
        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename']);
    }

    public function objetivosExcel()
    {
        $pei = \App\Models\StrategicPlanning\PEI::ativos()->first();
        if (!$pei) { return back(); }
        return Excel::download(new ObjetivosExport($pei->cod_pei), "Objetivos_Estrategicos.xlsx");
    }

    public function indicadoresPdf(Request $request, $organizacaoId = null)
    {
        $organizacaoId = $organizacaoId ?? $request->query('organizacaoId') ?? session('organizacao_selecionada_id');
        $ano = $request->query('ano') ?? date('Y');
        $periodo = $request->query('periodo') ?? 'anual';

        $result = $this->reportService->generateIndicadores($organizacaoId, $ano, $periodo);
        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename']);
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

        $result = $this->reportService->generatePlanos($organizacaoId, $ano);
        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename']);
    }

    public function planosExcel(Request $request)
    {
        $organizacaoId = $request->query('organizacao_id') ?? session('organizacao_selecionada_id');
        $ano = $request->query('ano') ?? date('Y');

        $organizacao = $organizacaoId ? \App\Models\Organization::find($organizacaoId) : null;
        $nomeArquivo = $organizacao ? "Planos_Acao_{$organizacao->sgl_organizacao}_{$ano}.xlsx" : "Planos_Acao_{$ano}.xlsx";

        return Excel::download(new PlanosExport($organizacaoId, $ano), $nomeArquivo);
    }

    public function riscosPdf(Request $request)
    {
        $organizacaoId = $request->query('organizacao_id') ?? session('organizacao_selecionada_id');

        $result = $this->reportService->generateRiscos($organizacaoId);
        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename']);
    }

    public function riscosExcel(Request $request)
    {
        $organizacaoId = $request->query('organizacao_id') ?? session('organizacao_selecionada_id');

        $organizacao = $organizacaoId ? \App\Models\Organization::find($organizacaoId) : null;
        $nomeArquivo = $organizacao ? "Riscos_{$organizacao->sgl_organizacao}.xlsx" : "Riscos_Geral.xlsx";

        return Excel::download(new RiscosExport($organizacaoId), $nomeArquivo);
    }

    public function integrado(Request $request, $organizacaoId = null)
    {
        $organizacaoId = $organizacaoId ?? $request->query('organizacaoId') ?? session('organizacao_selecionada_id');
        if (!$organizacaoId) return back();

        $ano = $request->query('ano') ?? session('ano_selecionado') ?? date('Y');
        $periodo = $request->query('periodo') ?? 'anual';
        $includeAi = $request->query('include_ai') === '1';

        // Aumentar timeout para geração de PDF pesado
        set_time_limit(300); 

        $result = $this->reportService->generateIntegrado($organizacaoId, $ano, $periodo, $includeAi);
        
        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename']);
    }
}
