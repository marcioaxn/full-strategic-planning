<?php

use App\Http\Controllers\ArquivoDocumentoController;
use App\Http\Controllers\DocumentosController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\Reports\RelatorioController;
use App\Livewire\ActionPlan\AtribuirResponsaveis;
use App\Livewire\ActionPlan\DetalharPlano;
use App\Livewire\ActionPlan\LicoesAprendidas;
use App\Livewire\ActionPlan\ListarPlanos;
use App\Livewire\Admin\ConfiguracaoSistema;
use App\Livewire\Admin\GestaoPerfis;
use App\Livewire\Agenda2030\PainelODS;
use App\Livewire\Ajuda\PapeisResponsabilidades;
use App\Livewire\Audit\DetalharLog;
use App\Livewire\Audit\ListarLogs;
use App\Livewire\Auth\TrocarSenha;
use App\Livewire\Dashboard\Index;
use App\Livewire\Deliverables\DeliverablesBoard;
use App\Livewire\Deliverables\MinhasEntregas;
use App\Livewire\Documentos\ListarDocumentos;
use App\Livewire\LandingPage;
use App\Livewire\Organization\DetalharOrganizacao;
use App\Livewire\Organization\ListarOrganizacoes;
use App\Livewire\PerformanceIndicators\DetalharIndicador;
use App\Livewire\PerformanceIndicators\LancarEvolucao;
use App\Livewire\PerformanceIndicators\ListarIndicadores;
use App\Livewire\Reports\HistoricoRelatorios;
use App\Livewire\Reports\ListarRelatorios;
use App\Livewire\RiskManagement\GerenciarMitigacoes;
use App\Livewire\RiskManagement\ListarRiscos;
use App\Livewire\RiskManagement\MatrizRiscos;
use App\Livewire\RiskManagement\RegistrarOcorrencias;
use App\Livewire\StrategicPlanning\AnalisePESTEL;
use App\Livewire\StrategicPlanning\AnaliseSWOT;
use App\Livewire\StrategicPlanning\CadeiaDeValor;
use App\Livewire\StrategicPlanning\DetalharGrauSatisfacao;
use App\Livewire\StrategicPlanning\DetalharIdentidade;
use App\Livewire\StrategicPlanning\DetalharObjetivo;
use App\Livewire\StrategicPlanning\DetalharPei;
use App\Livewire\StrategicPlanning\DetalharPerspectiva;
use App\Livewire\StrategicPlanning\DetalharValor;
use App\Livewire\StrategicPlanning\GerenciarFuturoAlmejado;
use App\Livewire\StrategicPlanning\GerenciarRae;
use App\Livewire\StrategicPlanning\GerenciarTemasNorteadores;
use App\Livewire\StrategicPlanning\InaugurarIntegrar;
use App\Livewire\StrategicPlanning\ListarGrausSatisfacao;
use App\Livewire\StrategicPlanning\ListarObjetivos;
use App\Livewire\StrategicPlanning\ListarPeis;
use App\Livewire\StrategicPlanning\ListarPerspectivas;
use App\Livewire\StrategicPlanning\ListarValores;
use App\Livewire\StrategicPlanning\MapaEstrategico;
use App\Livewire\StrategicPlanning\MissaoVisao;
use App\Livewire\UserManagement\DetalharUsuario;
use App\Livewire\UserManagement\ListarUsuarios;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPage::class)->name('welcome');

/*
|--------------------------------------------------------------------------
| Transparência — as MESMAS telas, abertas ao cidadão, somente leitura
|--------------------------------------------------------------------------
|
| O pedido é que o visitante navegue o Mapa Estratégico exatamente como quem
| está autenticado, inclusive mergulhando nos dados pelos cliques.
|
| Por isso aqui NÃO há cópia das telas: são as mesmas rotas e os mesmos
| componentes, apenas fora do grupo `auth`. Cada componente troca de layout
| quando não há sessão, e todo método de escrita passa por Policy — que exige
| um User e portanto nega o visitante. O middleware `transparencia` recusa
| qualquer verbo diferente de GET, porque método público de componente Livewire
| é invocável direto pelo navegador.
*/
Route::middleware(['transparencia'])->group(function () {
    Route::get('/pei/mapa', MapaEstrategico::class)->name('pei.mapa');

    // O mergulho a partir do mapa: as mesmas telas, sem sessão.
    // Todo botão de ação está sob @auth na Blade, e todo método de
    // escrita passa por Policy — que exige um User.
    Route::get('/objetivos', ListarObjetivos::class)->name('objetivos.index');
    Route::get('/objetivos/{id}/detalhes', DetalharObjetivo::class)->name('objetivos.detalhes');
    Route::get('/indicadores', ListarIndicadores::class)->name('indicadores.index');
    Route::get('/indicadores/{id}/detalhes', DetalharIndicador::class)->name('indicadores.detalhes');
    Route::get('/planos', ListarPlanos::class)->name('planos.index');
    Route::get('/planos/{id}/detalhes', DetalharPlano::class)->name('planos.detalhes');
});

// Encerrar a impersonação fica fora do grupo que exige perfil: o Super Admin
// que assumiu a identidade de uma conta sem perfil precisa conseguir voltar.
Route::middleware(['auth:sanctum', config('jetstream.auth_session')])
    ->post('/impersonate-stop', [ImpersonateController::class, 'stop'])
    ->name('impersonate.stop');

// Conta autenticada SEM perfil de acesso (autocadastro) cai aqui e só aqui.
Route::middleware(['auth:sanctum', config('jetstream.auth_session')])
    ->get('/acesso-pendente', fn () => view('auth.acesso-pendente'))
    ->name('acesso.pendente');

// 🔴 A troca de senha obrigatória fica FORA do grupo que exige perfil. Dentro
// dele, a conta recém-criada pelo autocadastro (que nasce com troca de senha
// pendente e sem perfil) entrava em laço: troca de senha → exige perfil →
// acesso pendente → exige troca de senha → … até o navegador desistir.
Route::middleware(['auth:sanctum', config('jetstream.auth_session')])
    ->get('/trocar-senha', TrocarSenha::class)
    ->name('auth.trocar-senha');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'perfil',
])->group(function () {
    // CSRF Token Refresh Endpoint — requer autenticação
    Route::get('/refresh-csrf', function () {
        return response()->json([
            'csrf_token' => csrf_token(),
        ]);
    })->name('csrf.refresh');

    Route::get('/dashboard', Index::class)->name('dashboard');

    // Documentos de referência metodológica
    Route::get('/documentos/gppei', [DocumentosController::class, 'gppei'])->name('documentos.gppei');
    Route::get('/documentos/projetos/pdf', [DocumentosController::class, 'projetosPdf'])->name('documentos.projetos.pdf');
    Route::get('/documentos/projetos', [DocumentosController::class, 'viewerProjetos'])->name('documentos.projetos');
    Route::get('/guia-gppei', [DocumentosController::class, 'viewerGppei'])->name('documentos.viewer-gppei');

    // Acervo de documentos em PDF (decretos, portarias, relatórios de gestão...)
    Route::get('/acervo-documentos', ListarDocumentos::class)->name('acervo.index');
    Route::get('/acervo-documentos/{documento}/arquivo', ArquivoDocumentoController::class)
        ->whereUuid('documento')
        ->name('acervo.arquivo');
    // Ajuda — "quem pode fazer o quê", derivada da MATRIZ de capacidades.
    // Aberta a todo perfil autenticado: é a resposta a uma dúvida, não um dado.
    Route::get('/ajuda/papeis', PapeisResponsabilidades::class)->name('ajuda.papeis');

    Route::get('/licoes-aprendidas', LicoesAprendidas::class)->name('licoes.index');

    // Strategic Planning Module
    Route::get('/organizacoes', ListarOrganizacoes::class)->name('organizacoes.index');
    Route::get('/organizacoes/{id}/detalhes', DetalharOrganizacao::class)->name('organizacoes.detalhes');
    Route::get('/usuarios', ListarUsuarios::class)->name('usuarios.index');
    Route::get('/usuarios/{id}/detalhes', DetalharUsuario::class)->name('usuarios.detalhes');

    // Gestão de Perfis de Acesso e Impersonação (Administrador Geral)
    Route::get('/admin/perfis', GestaoPerfis::class)->name('admin.perfis');
    // POST com CSRF: em GET, um simples link ou <img> numa página qualquer
    // fazia o Super Admin assumir uma identidade sem perceber.
    Route::post('/impersonate/{userId}', [ImpersonateController::class, 'start'])->name('impersonate.start');
    Route::get('/configuracoes', ConfiguracaoSistema::class)->name('admin.configuracoes');
    Route::get('/graus-satisfacao', ListarGrausSatisfacao::class)->name('graus-satisfacao.index');
    Route::get('/graus-satisfacao/{id}/detalhes', DetalharGrauSatisfacao::class)->name('graus-satisfacao.detalhes');

    // Strategic Planning (PEI)
    Route::get('/pei/inaugurar', InaugurarIntegrar::class)->name('pei.inaugurar');
    Route::get('/monitoramento/rae', GerenciarRae::class)->name('monitoramento.rae');
    Route::get('/pei/cadeia-valor', CadeiaDeValor::class)->name('pei.cadeia-valor');
    Route::get('/minhas-entregas', MinhasEntregas::class)->name('entregas.minhas');
    Route::get('/pei', MissaoVisao::class)->name('pei.index');
    Route::get('/pei/identidade/{id}/detalhes', DetalharIdentidade::class)->name('pei.identidade.detalhes');
    Route::get('/pei/ciclos', ListarPeis::class)->name('pei.ciclos');
    Route::get('/pei/{id}/detalhes', DetalharPei::class)->name('pei.detalhes');
    Route::get('/pei/valores', ListarValores::class)->name('pei.valores');
    Route::get('/pei/valores/{id}/detalhes', DetalharValor::class)->name('pei.valores.detalhes');
    Route::get('/pei/perspectivas', ListarPerspectivas::class)->name('pei.perspectivas');
    Route::get('/pei/perspectivas/{id}/detalhes', DetalharPerspectiva::class)->name('pei.perspectivas.detalhes');
    Route::get('/pei/swot', AnaliseSWOT::class)->name('pei.swot');
    Route::get('/pei/pestel', AnalisePESTEL::class)->name('pei.pestel');
    // /pei/mapa saiu deste grupo: agora e publica (bloco Transparencia acima).
    // O componente troca de layout sozinho quando nao ha sessao.
    Route::get('/temas-norteadores', GerenciarTemasNorteadores::class)->name('temas-norteadores.index');

    // Agenda 2030 — Painel de contribuição aos ODS
    Route::get('/agenda2030', PainelODS::class)->name('agenda2030.index');
    Route::get('/objetivos/{objetivoId}/futuro', GerenciarFuturoAlmejado::class)->name('objetivos.futuro');

    // Entregas (Board Style)
    Route::get('/entregas', DeliverablesBoard::class)->name('entregas.index');

    // Action Plans
    Route::get('/planos/{planoId}/entregas', DeliverablesBoard::class)->name('planos.entregas');
    Route::get('/planos/{planoId}/responsaveis', AtribuirResponsaveis::class)->name('planos.responsaveis');

    // Indicators (KPIs)

    Route::get('/indicadores/{indicadorId}/evolucao', LancarEvolucao::class)->name('indicadores.evolucao');

    // Risk Management
    Route::get('/riscos', ListarRiscos::class)->name('riscos.index');
    Route::get('/riscos/matriz', MatrizRiscos::class)->name('riscos.matriz');
    Route::get('/riscos/{riscoId}/mitigacao', GerenciarMitigacoes::class)->name('riscos.mitigacao');
    Route::get('/riscos/{riscoId}/ocorrencias', RegistrarOcorrencias::class)->name('riscos.ocorrencias');

    // Audit
    Route::get('/auditoria', ListarLogs::class)->name('audit.index');
    Route::get('/auditoria/{id}/detalhes', DetalharLog::class)->name('audit.detalhes');

    // Reports Menu
    Route::get('/relatorios', ListarRelatorios::class)->name('relatorios.index');
    Route::get('/relatorios/historico', HistoricoRelatorios::class)->name('relatorios.historico');
    Route::get('/relatorios/comunicacao', [RelatorioController::class, 'comunicacao'])->name('relatorios.comunicacao');

    // Reports PDF/Excel

    Route::get('/relatorios/identidade/{organizacaoId}', [RelatorioController::class, 'identidade'])->name('relatorios.identidade');

    Route::get('/relatorios/objetivos/pdf', [RelatorioController::class, 'objetivosPdf'])->name('relatorios.objetivos.pdf');

    Route::get('/relatorios/objetivos/excel', [RelatorioController::class, 'objetivosExcel'])->name('relatorios.objetivos.excel');

    Route::get('/relatorios/indicadores/pdf/{organizacaoId?}', [RelatorioController::class, 'indicadoresPdf'])->name('relatorios.indicadores.pdf');

    Route::get('/relatorios/indicadores/excel/{organizacaoId?}', [RelatorioController::class, 'indicadoresExcel'])->name('relatorios.indicadores.excel');

    Route::get('/relatorios/executivo/{organizacaoId?}', [RelatorioController::class, 'executivo'])->name('relatorios.executivo');

    // Relatórios de Iniciativas
    Route::get('/relatorios/planos/pdf', [RelatorioController::class, 'planosPdf'])->name('relatorios.planos.pdf');
    Route::get('/relatorios/planos/excel', [RelatorioController::class, 'planosExcel'])->name('relatorios.planos.excel');

    // Relatórios de Riscos
    Route::get('/relatorios/riscos/pdf', [RelatorioController::class, 'riscosPdf'])->name('relatorios.riscos.pdf');
    Route::get('/relatorios/riscos/excel', [RelatorioController::class, 'riscosExcel'])->name('relatorios.riscos.excel');

    /*
     * Relatório de Gestão — o documento que o órgão presta à sociedade.
     *
     * Dois formatos (pdf, docx) e duas variantes (?variante=replica|autoral).
     * O ano vem por query string: é o EXERCÍCIO relatado, não o ano corrente.
     */
    Route::get('/relatorios/gestao/pdf', [RelatorioController::class, 'gestaoPdf'])->name('relatorios.gestao.pdf');
    Route::get('/relatorios/gestao/docx', [RelatorioController::class, 'gestaoDocx'])->name('relatorios.gestao.docx');

    // Relatório Integrado
    Route::get('/relatorios/integrado/{organizacaoId?}', [RelatorioController::class, 'integrado'])->name('relatorios.integrado');

    // Session ping endpoint for session renewal
    Route::post('/session/ping', function () {
        return response()->json(['success' => true, 'timestamp' => now()->toIso8601String()]);
    })->name('session.ping');
});
