<?php

use App\Livewire\LeadsTable;
use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\StrategicPlanning\MapaEstrategico::class)->name('welcome');

// CSRF Token Refresh Endpoint
Route::get('/refresh-csrf', function () {
    return response()->json([
        'csrf_token' => csrf_token()
    ]);
})->name('csrf.refresh');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', \App\Livewire\Dashboard\Index::class)->name('dashboard');

    Route::get('/trocar-senha', \App\Livewire\Auth\TrocarSenha::class)->name('auth.trocar-senha');

    Route::get('/leads', LeadsTable::class)->name('leads.index');

    // Strategic Planning Module
    Route::get('/organizacoes', \App\Livewire\Organization\ListarOrganizacoes::class)->name('organizacoes.index');
    Route::get('/organizacoes/{id}/detalhes', \App\Livewire\Organization\DetalharOrganizacao::class)->name('organizacoes.detalhes');
    Route::get('/usuarios', \App\Livewire\UserManagement\ListarUsuarios::class)->name('usuarios.index');
    Route::get('/usuarios/{id}/detalhes', \App\Livewire\UserManagement\DetalharUsuario::class)->name('usuarios.detalhes');
    Route::get('/configuracoes', \App\Livewire\Admin\ConfiguracaoSistema::class)->name('admin.configuracoes');
    Route::get('/graus-satisfacao', \App\Livewire\StrategicPlanning\ListarGrausSatisfacao::class)->name('graus-satisfacao.index');
    Route::get('/graus-satisfacao/{id}/detalhes', \App\Livewire\StrategicPlanning\DetalharGrauSatisfacao::class)->name('graus-satisfacao.detalhes');
    
    // Strategic Planning (PEI)
    Route::get('/pei', \App\Livewire\StrategicPlanning\MissaoVisao::class)->name('pei.index');
    Route::get('/pei/identidade/{id}/detalhes', \App\Livewire\StrategicPlanning\DetalharIdentidade::class)->name('pei.identidade.detalhes');
    Route::get('/pei/ciclos', \App\Livewire\StrategicPlanning\ListarPeis::class)->name('pei.ciclos');
    Route::get('/pei/{id}/detalhes', \App\Livewire\StrategicPlanning\DetalharPei::class)->name('pei.detalhes');
    Route::get('/pei/valores', \App\Livewire\StrategicPlanning\ListarValores::class)->name('pei.valores');
    Route::get('/pei/valores/{id}/detalhes', \App\Livewire\StrategicPlanning\DetalharValor::class)->name('pei.valores.detalhes');
    Route::get('/pei/perspectivas', \App\Livewire\StrategicPlanning\ListarPerspectivas::class)->name('pei.perspectivas');
    Route::get('/pei/perspectivas/{id}/detalhes', \App\Livewire\StrategicPlanning\DetalharPerspectiva::class)->name('pei.perspectivas.detalhes');
    Route::get('/pei/swot', \App\Livewire\StrategicPlanning\AnaliseSWOT::class)->name('pei.swot');
    Route::get('/pei/pestel', \App\Livewire\StrategicPlanning\AnalisePESTEL::class)->name('pei.pestel');
    Route::get('/pei/mapa', \App\Livewire\StrategicPlanning\MapaEstrategico::class)->name('pei.mapa');
    Route::get('/objetivos', \App\Livewire\StrategicPlanning\ListarObjetivos::class)->name('objetivos.index');
    Route::get('/objetivos/{id}/detalhes', \App\Livewire\StrategicPlanning\DetalharObjetivo::class)->name('objetivos.detalhes');
    Route::get('/objetivos-estrategicos', \App\Livewire\StrategicPlanning\GerenciarObjetivosEstrategicos::class)->name('objetivos-estrategicos.index');
    Route::get('/objetivos/{objetivoId}/futuro', \App\Livewire\StrategicPlanning\GerenciarFuturoAlmejado::class)->name('objetivos.futuro');
    
    // Entregas (Board Style)
    Route::get('/entregas', \App\Livewire\Deliverables\DeliverablesBoard::class)->name('entregas.index');
    
        // Action Plans
        Route::get('/planos', \App\Livewire\ActionPlan\ListarPlanos::class)->name('planos.index');
        Route::get('/planos/{id}/detalhes', \App\Livewire\ActionPlan\DetalharPlano::class)->name('planos.detalhes');
        Route::get('/planos/{planoId}/entregas', \App\Livewire\Deliverables\DeliverablesBoard::class)->name('planos.entregas');
        Route::get('/planos/{planoId}/responsaveis', \App\Livewire\ActionPlan\AtribuirResponsaveis::class)->name('planos.responsaveis');
    
                    // Indicators (KPIs)
    
                    Route::get('/indicadores', \App\Livewire\PerformanceIndicators\ListarIndicadores::class)->name('indicadores.index');
    
                    Route::get('/indicadores/{id}/detalhes', \App\Livewire\PerformanceIndicators\DetalharIndicador::class)->name('indicadores.detalhes');
    
                    Route::get('/indicadores/{indicadorId}/evolucao', \App\Livewire\PerformanceIndicators\LancarEvolucao::class)->name('indicadores.evolucao');
    
                
    
                            // Risk Management
                            Route::get('/riscos', \App\Livewire\RiskManagement\ListarRiscos::class)->name('riscos.index');
                            Route::get('/riscos/matriz', \App\Livewire\RiskManagement\MatrizRiscos::class)->name('riscos.matriz');
                            Route::get('/riscos/{riscoId}/mitigacao', \App\Livewire\RiskManagement\GerenciarMitigacoes::class)->name('riscos.mitigacao');
                            Route::get('/riscos/{riscoId}/ocorrencias', \App\Livewire\RiskManagement\RegistrarOcorrencias::class)->name('riscos.ocorrencias');
                        
                            // Audit
                            Route::get('/auditoria', \App\Livewire\Audit\ListarLogs::class)->name('audit.index');
                            Route::get('/auditoria/{id}/detalhes', \App\Livewire\Audit\DetalharLog::class)->name('audit.detalhes');

                            // Reports Menu
                            Route::get('/relatorios', \App\Livewire\Reports\ListarRelatorios::class)->name('relatorios.index');
                            Route::get('/relatorios/historico', \App\Livewire\Reports\HistoricoRelatorios::class)->name('relatorios.historico');

                            // Reports PDF/Excel    
                
    
                        Route::get('/relatorios/identidade/{organizacaoId}', [\App\Http\Controllers\Reports\RelatorioController::class, 'identidade'])->name('relatorios.identidade');
    
                
    
                            Route::get('/relatorios/objetivos/pdf', [\App\Http\Controllers\Reports\RelatorioController::class, 'objetivosPdf'])->name('relatorios.objetivos.pdf');
    
                
    
                            Route::get('/relatorios/objetivos/excel', [\App\Http\Controllers\Reports\RelatorioController::class, 'objetivosExcel'])->name('relatorios.objetivos.excel');
    
                
    
                                Route::get('/relatorios/indicadores/pdf/{organizacaoId?}', [\App\Http\Controllers\Reports\RelatorioController::class, 'indicadoresPdf'])->name('relatorios.indicadores.pdf');
    
                
    
                                Route::get('/relatorios/indicadores/excel/{organizacaoId?}', [\App\Http\Controllers\Reports\RelatorioController::class, 'indicadoresExcel'])->name('relatorios.indicadores.excel');
    
                
    
                                Route::get('/relatorios/executivo/{organizacaoId?}', [\App\Http\Controllers\Reports\RelatorioController::class, 'executivo'])->name('relatorios.executivo');

                                // Relatórios de Planos de Ação
                                Route::get('/relatorios/planos/pdf', [\App\Http\Controllers\Reports\RelatorioController::class, 'planosPdf'])->name('relatorios.planos.pdf');
                                Route::get('/relatorios/planos/excel', [\App\Http\Controllers\Reports\RelatorioController::class, 'planosExcel'])->name('relatorios.planos.excel');

                                // Relatórios de Riscos
                                Route::get('/relatorios/riscos/pdf', [\App\Http\Controllers\Reports\RelatorioController::class, 'riscosPdf'])->name('relatorios.riscos.pdf');
                                Route::get('/relatorios/riscos/excel', [\App\Http\Controllers\Reports\RelatorioController::class, 'riscosExcel'])->name('relatorios.riscos.excel');

    // Session ping endpoint for session renewal
    Route::post('/session/ping', function () {
        return response()->json(['success' => true, 'timestamp' => now()->toIso8601String()]);
    })->name('session.ping');
});
