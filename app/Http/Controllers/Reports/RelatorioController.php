<?php

namespace App\Http\Controllers\Reports;

use App\Exports\IndicadoresExport;
use App\Exports\ObjetivosExport;
use App\Exports\PlanosExport;
use App\Exports\RiscosExport;
use App\Http\Controllers\Controller;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\Organization;
use App\Models\Reports\RelatorioGerado;
use App\Models\StrategicPlanning\PEI;
use App\Models\SystemSetting;
use App\Services\Reports\AcabamentoPdf;
use App\Services\Reports\RelatorioGestao\EstruturaRelatorioGestao;
use App\Services\Reports\RelatorioGestao\RenderizadorDocx;
use App\Services\Reports\ReportGenerationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class RelatorioController extends Controller
{
    use AuthorizesRequests;

    protected $reportService;

    public function __construct(ReportGenerationService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Entrega o arquivo E registra a geração no histórico.
     *
     * 🔴 POR QUE ISTO EXISTE
     * A tela "Histórico de Relatórios Gerados" estava vazia em praticamente
     * toda instalação — e ia continuar vazia para sempre. `RelatorioGerado` era
     * instanciado em UM único lugar do projeto: o comando de agendamento, que
     * depende de uma Tarefa Agendada do sistema operacional que ninguém
     * configurou. Os relatórios que o cliente de fato baixa, clicando na tela,
     * não eram registrados em lugar nenhum.
     *
     * Resultado: o cliente gerava um relatório, abria o Histórico e via uma
     * tabela vazia. A tela prometia "Histórico de Relatórios Gerados" e
     * mostrava só o que um agendador parado não produziu.
     *
     * Guarda-se o REGISTRO, não o arquivo: o PDF sai direto para o navegador.
     * Guardar cada PDF gerado encheria o disco do cliente sem que ninguém
     * pedisse — e um relatório de três meses atrás é menos útil do que gerar de
     * novo com o dado de hoje.
     */
    private function entregar(array $result, string $tipo, array $filtros = [], ?string $formato = null)
    {
        try {
            RelatorioGerado::create([
                'user_id' => Auth::id(),
                'dsc_tipo_relatorio' => $tipo,
                // Guarda QUAL rota gerou isto, para o histórico poder oferecer
                // "gerar de novo" em vez de um download que não existe.
                'dsc_rota' => request()->route()?->getName(),
                'dsc_caminho_arquivo' => '',
                'dsc_formato' => $formato ?? (str_ends_with(strtolower($result['filename'] ?? ''), '.pdf') ? 'pdf' : 'excel'),
                'txt_filtros_aplicados' => $filtros,
                'num_tamanho_bytes' => strlen($result['content'] ?? ''),
            ]);
        } catch (\Throwable $e) {
            // O histórico é registro, não o produto: uma falha aqui não pode
            // impedir o cliente de baixar o relatório que ele pediu.
            report($e);
        }

        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename']);
    }

    public function executivo(Request $request, $organizacaoId = null)
    {
        $organizacaoId = $organizacaoId ?? $request->query('organizacaoId') ?? session('organizacao_selecionada_id');
        if (! $organizacaoId) {
            return back();
        }

        $ano = $request->query('ano') ?? session('ano_selecionado') ?? date('Y');
        $periodo = $request->query('periodo') ?? 'anual';
        $perspectivaId = $request->query('perspectiva');

        $result = $this->reportService->generateExecutivo($organizacaoId, $ano, $periodo, $perspectivaId);

        return $this->entregar($result, 'Relatório Executivo', $request->query());
    }

    public function identidade(Request $request, $organizacaoId)
    {
        $ano = $request->query('ano') ?? session('ano_selecionado') ?? date('Y');
        $result = $this->reportService->generateIdentidade($organizacaoId, $ano);

        return $this->entregar($result, 'Mapa Estratégico', $request->query());
    }

    public function objetivosPdf(Request $request)
    {
        $organizacaoId = $request->query('organizacao_id');
        $perspectivaId = $request->query('perspectiva');
        $ano = $request->query('ano') ?? date('Y');

        $result = $this->reportService->generateObjetivos($organizacaoId, $ano, $perspectivaId);

        return $this->entregar($result, 'Objetivos Estratégicos', $request->query());
    }

    public function objetivosExcel()
    {
        $pei = PEI::ativos()->first();
        if (! $pei) {
            return back();
        }

        return Excel::download(new ObjetivosExport($pei->cod_pei), 'Objetivos_Estrategicos.xlsx');
    }

    public function indicadoresPdf(Request $request, $organizacaoId = null)
    {
        $organizacaoId = $organizacaoId ?? $request->query('organizacaoId') ?? session('organizacao_selecionada_id');
        $ano = $request->query('ano') ?? date('Y');
        $periodo = $request->query('periodo') ?? 'anual';

        $result = $this->reportService->generateIndicadores($organizacaoId, $ano, $periodo);

        return $this->entregar($result, 'Indicadores', $request->query());
    }

    public function indicadoresExcel($organizacaoId = null)
    {
        $organizacaoId = $organizacaoId ?? session('organizacao_selecionada_id');

        return Excel::download(new IndicadoresExport($organizacaoId), 'Indicadores_Desempenho.xlsx');
    }

    public function planosPdf(Request $request)
    {
        $organizacaoId = $request->query('organizacao_id') ?? session('organizacao_selecionada_id');
        $ano = $request->query('ano') ?? date('Y');

        $result = $this->reportService->generatePlanos($organizacaoId, $ano);

        return $this->entregar($result, 'Iniciativas', $request->query());
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

        $result = $this->reportService->generateRiscos($organizacaoId);

        return $this->entregar($result, 'Gestão de Riscos', $request->query());
    }

    public function riscosExcel(Request $request)
    {
        $organizacaoId = $request->query('organizacao_id') ?? session('organizacao_selecionada_id');

        $organizacao = $organizacaoId ? Organization::find($organizacaoId) : null;
        $nomeArquivo = $organizacao ? "Riscos_{$organizacao->sgl_organizacao}.xlsx" : 'Riscos_Geral.xlsx';

        return Excel::download(new RiscosExport($organizacaoId), $nomeArquivo);
    }

    public function integrado(Request $request, $organizacaoId = null)
    {
        $organizacaoId = $organizacaoId ?? $request->query('organizacaoId') ?? session('organizacao_selecionada_id');
        if (! $organizacaoId) {
            return back();
        }

        $ano = $request->query('ano') ?? session('ano_selecionado') ?? date('Y');
        $periodo = $request->query('periodo') ?? 'anual';
        $includeAi = $request->query('include_ai') === '1';

        // Aumentar recursos para geração de PDF pesado (Relatório Estratégico Integrado)
        ini_set('memory_limit', '512M');
        set_time_limit(600);

        $result = $this->reportService->generateIntegrado($organizacaoId, $ano, $periodo, $includeAi);

        return $this->entregar($result, 'Relatório Estratégico Integrado', $request->query());
    }

    /**
     * Relatório consolidado do Plano de Comunicação do PEI.
     * Reúne todos os itens de comunicação de todos os planos da organização/PEI.
     */
    public function comunicacao(Request $request)
    {
        $organizacaoId = $request->query('organizacao_id') ?? session('organizacao_selecionada_id');
        $peiId = session('pei_selecionado_id');

        $organizacao = $organizacaoId ? Organization::find($organizacaoId) : null;
        $pei = $peiId ? PEI::find($peiId) : PEI::ativos()->first();

        $planosQuery = PlanoDeAcao::query()
            ->with(['comunicacoes' => fn ($q) => $q->orderBy('num_ordem')]);

        if ($pei) {
            $planosQuery->whereHas('objetivo.perspectiva', fn ($q) => $q->where('cod_pei', $pei->cod_pei));
        }
        if ($organizacaoId) {
            $planosQuery->where('cod_organizacao', $organizacaoId);
        }

        $planos = $planosQuery->get()->filter(fn ($p) => $p->comunicacoes->isNotEmpty());

        $pdf = Pdf::loadView('relatorios.comunicacao', [
            'planos' => $planos,
            'pei' => $pei,
            'organizacao' => $organizacao,
            'data' => now()->format('d/m/Y'),
        ])->setPaper('a4', 'portrait');

        (new AcabamentoPdf('portrait'))->aplicar($pdf, [
            'esquerda' => $organizacao?->nom_organizacao ?? 'Todas as unidades',
            'centro' => 'Plano de Comunicação',
            'site' => (string) SystemSetting::getValue('orgao_site', ''),
            'emitido_em' => now()->format('d/m/Y'),
        ]);

        // Vai pelo entregar() como os demais: geração de relatório é registro de
        // histórico, e esta era a única que não registrava nada.
        return $this->entregar([
            'content' => $pdf->output(),
            'filename' => 'Plano_Comunicacao_PEI_'.now()->format('Y_m_d').'.pdf',
        ], 'Plano de Comunicação', $request->query(), 'pdf');
    }

    /**
     * Relatório de Gestão — PDF.
     *
     * Duas variantes, e a diferença entre elas não é estética:
     *
     *   réplica — a estrutura COMPLETA do modelo oficial, capítulos 1 a 5.
     *             As seções que este sistema não alimenta (execução
     *             orçamentária, força de trabalho, licitações, demonstrações
     *             contábeis) vêm marcadas, nomeando a fonte que as preenche.
     *             É o documento que a unidade completa à mão.
     *
     *   autoral — só o que o Planejamento Estratégico Institucional de fato
     *             registra. Seção vazia não aparece. É o documento que se
     *             publica sem completar nada.
     *
     * 🔴 O ANO É PARÂMETRO, e o ciclo é buscado pelo ano — não pelo "PEI
     * ativo". Relatório de Gestão de 2025 emitido em 2026 tem de trazer o
     * ciclo vigente em 2025, ou mente sobre o exercício que relata.
     */
    public function gestaoPdf(Request $request)
    {
        $this->authorize('modulo.exportar', 'relatorios');

        [$organizacaoId, $ano, $variante] = $this->parametrosGestao($request);

        $dados = (new EstruturaRelatorioGestao($variante))->montar($organizacaoId, $ano);

        $pdf = Pdf::loadView('relatorios.gestao.relatorio', ['dados' => $dados])
            ->setPaper('a4', 'landscape');

        // Mesmo renderizador dos outros dez relatórios. O que o Gestão tem a
        // mais — capa sangrada e o capítulo corrente no cabeçalho — entra por
        // parâmetro, não por uma segunda implementação.
        (new AcabamentoPdf('landscape'))->aplicar($pdf, [
            'esquerda' => $dados['capa']['orgao'],
            'centro' => 'Relatório de Gestão '.$dados['ano'],
            'site' => $dados['capa']['site'],
            'emitido_em' => $dados['capa']['emitido_em'],
            'capa' => [
                'orgao' => $dados['capa']['orgao'],
                'titulo' => 'Relatório de Gestão',
                'ano' => $dados['capa']['ano'],
                'imagem' => $dados['capa']['imagem'],
                'credito_imagem' => $dados['capa']['credito_imagem'],
                'rodape' => [
                    $dados['capa']['ciclo']
                        ? 'Ciclo do Planejamento Estratégico Institucional: '.$dados['capa']['ciclo']
                        : 'Planejamento Estratégico Institucional',
                    'Documento emitido em '.$dados['capa']['emitido_em'].'.',
                ],
            ],
            'capitulos' => collect($dados['capitulos'])
                ->mapWithKeys(fn ($c) => [
                    $c['numero'] => 'Capítulo '.str_pad($c['numero'], 2, '0', STR_PAD_LEFT).' — '.$c['titulo'],
                ])
                ->all(),
        ]);

        return $this->entregar([
            'content' => $pdf->output(),
            'filename' => $this->nomeArquivoGestao($dados, $variante, 'pdf'),
        ], 'Relatório de Gestão ('.$variante.')', $request->query(), 'pdf');
    }

    /**
     * Relatório de Gestão — DOCX.
     *
     * Mesma estrutura, mesmo serviço, mesma variante. O DOCX existe para ser
     * EDITADO: é nele que a unidade completa as seções de fonte externa.
     */
    public function gestaoDocx(Request $request)
    {
        $this->authorize('modulo.exportar', 'relatorios');

        [$organizacaoId, $ano, $variante] = $this->parametrosGestao($request);

        $dados = (new EstruturaRelatorioGestao($variante))->montar($organizacaoId, $ano);

        $caminho = (new RenderizadorDocx)->gerar($dados);

        try {
            $conteudo = file_get_contents($caminho);
        } finally {
            @unlink($caminho);
        }

        return $this->entregar([
            'content' => $conteudo,
            'filename' => $this->nomeArquivoGestao($dados, $variante, 'docx'),
        ], 'Relatório de Gestão ('.$variante.')', $request->query(), 'docx');
    }

    /**
     * Ano, organização e variante — validados aqui, uma vez só.
     *
     * Variante desconhecida cai na autoral: entregar a estrutura completa a
     * quem não a pediu enche o documento de seções vazias.
     */
    private function parametrosGestao(Request $request): array
    {
        $organizacaoId = $request->query('organizacao_id')
            ?? $request->query('organizacaoId')
            ?? session('organizacao_selecionada_id');

        $ano = (int) ($request->query('ano') ?? session('ano_selecionado') ?? date('Y'));

        if ($ano < 2000 || $ano > (int) date('Y') + 10) {
            $ano = (int) date('Y');
        }

        $variante = $request->query('variante') === EstruturaRelatorioGestao::VARIANTE_REPLICA
            ? EstruturaRelatorioGestao::VARIANTE_REPLICA
            : EstruturaRelatorioGestao::VARIANTE_AUTORAL;

        return [$organizacaoId, $ano, $variante];
    }

    private function nomeArquivoGestao(array $dados, string $variante, string $extensao): string
    {
        $sigla = $dados['capa']['sigla'] ?: $dados['capa']['orgao'];
        $sigla = preg_replace('/[^A-Za-z0-9]+/', '_', (string) $sigla);
        $sigla = trim((string) $sigla, '_') ?: 'Organizacao';

        return 'Relatorio_de_Gestao_'.$dados['ano'].'_'.$sigla.'_'.$variante.'.'.$extensao;
    }
}
