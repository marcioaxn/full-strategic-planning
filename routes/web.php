<?php

use App\Livewire\LeadsTable;
use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\PEI\MapaEstrategico::class)->name('welcome');

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
    Route::get('/organizacoes', \App\Livewire\Organizacao\ListarOrganizacoes::class)->name('organizacoes.index');
    Route::get('/usuarios', \App\Livewire\Usuario\ListarUsuarios::class)->name('usuarios.index');
    Route::get('/graus-satisfacao', \App\Livewire\PEI\ListarGrausSatisfacao::class)->name('graus-satisfacao.index');
    
    // Strategic Planning (PEI)
    Route::get('/pei', \App\Livewire\PEI\MissaoVisao::class)->name('pei.index');
    Route::get('/pei/ciclos', \App\Livewire\PEI\ListarPeis::class)->name('pei.ciclos');
    Route::get('/pei/valores', \App\Livewire\PEI\ListarValores::class)->name('pei.valores');
    Route::get('/pei/perspectivas', \App\Livewire\PEI\ListarPerspectivas::class)->name('pei.perspectivas');
    Route::get('/pei/swot', \App\Livewire\PEI\AnaliseSWOT::class)->name('pei.swot');
    Route::get('/pei/pestel', \App\Livewire\PEI\AnalisePESTEL::class)->name('pei.pestel');
    Route::get('/pei/mapa', \App\Livewire\PEI\MapaEstrategico::class)->name('pei.mapa');
    Route::get('/objetivos', \App\Livewire\PEI\ListarObjetivos::class)->name('objetivos.index');
    Route::get('/objetivos-estrategicos', \App\Livewire\PEI\GerenciarObjetivosEstrategicos::class)->name('objetivos-estrategicos.index');
    Route::get('/objetivos/{objetivoId}/futuro', \App\Livewire\PEI\GerenciarFuturoAlmejado::class)->name('objetivos.futuro');
    
    // Entregas (Estilo Notion)
    Route::get('/entregas', \App\Livewire\Entregas\NotionBoard::class)->name('entregas.index');
    
        // Action Plans
        Route::get('/planos', \App\Livewire\PlanoAcao\ListarPlanos::class)->name('planos.index');
        Route::get('/planos/{planoId}/detalhes', \App\Livewire\PlanoAcao\DetalharPlano::class)->name('planos.detalhes');
        Route::get('/planos/{planoId}/entregas', \App\Livewire\Entregas\NotionBoard::class)->name('planos.entregas');
        Route::get('/planos/{planoId}/responsaveis', \App\Livewire\PlanoAcao\AtribuirResponsaveis::class)->name('planos.responsaveis');
    
                    // Indicators (KPIs)
    
                    Route::get('/indicadores', \App\Livewire\Indicador\ListarIndicadores::class)->name('indicadores.index');
    
                    Route::get('/indicadores/{indicadorId}/detalhes', \App\Livewire\Indicador\DetalharIndicador::class)->name('indicadores.detalhes');
    
                    Route::get('/indicadores/{indicadorId}/evolucao', \App\Livewire\Indicador\LancarEvolucao::class)->name('indicadores.evolucao');
    
                
    
                            // Risk Management
                            Route::get('/riscos', \App\Livewire\Risco\ListarRiscos::class)->name('riscos.index');
                            Route::get('/riscos/matriz', \App\Livewire\Risco\MatrizRiscos::class)->name('riscos.matriz');
                            Route::get('/riscos/{riscoId}/mitigacao', \App\Livewire\Risco\GerenciarMitigacoes::class)->name('riscos.mitigacao');
                            Route::get('/riscos/{riscoId}/ocorrencias', \App\Livewire\Risco\RegistrarOcorrencias::class)->name('riscos.ocorrencias');
                        
                            // Audit
                            Route::get('/auditoria', \App\Livewire\Audit\ListarLogs::class)->name('audit.index');

                            // Reports Menu
                            Route::get('/relatorios', \App\Livewire\Relatorio\ListarRelatorios::class)->name('relatorios.index');

                            // Reports PDF/Excel    
                
    
                        Route::get('/relatorios/identidade/{organizacaoId}', [\App\Http\Controllers\RelatorioController::class, 'identidade'])->name('relatorios.identidade');
    
                
    
                            Route::get('/relatorios/objetivos/pdf', [\App\Http\Controllers\RelatorioController::class, 'objetivosPdf'])->name('relatorios.objetivos.pdf');
    
                
    
                            Route::get('/relatorios/objetivos/excel', [\App\Http\Controllers\RelatorioController::class, 'objetivosExcel'])->name('relatorios.objetivos.excel');
    
                
    
                                Route::get('/relatorios/indicadores/pdf/{organizacaoId?}', [\App\Http\Controllers\RelatorioController::class, 'indicadoresPdf'])->name('relatorios.indicadores.pdf');
    
                
    
                                Route::get('/relatorios/indicadores/excel/{organizacaoId?}', [\App\Http\Controllers\RelatorioController::class, 'indicadoresExcel'])->name('relatorios.indicadores.excel');
    
                
    
                                Route::get('/relatorios/executivo/{organizacaoId?}', [\App\Http\Controllers\RelatorioController::class, 'executivo'])->name('relatorios.executivo');

                                // Relatórios de Planos de Ação
                                Route::get('/relatorios/planos/pdf', [\App\Http\Controllers\RelatorioController::class, 'planosPdf'])->name('relatorios.planos.pdf');
                                Route::get('/relatorios/planos/excel', [\App\Http\Controllers\RelatorioController::class, 'planosExcel'])->name('relatorios.planos.excel');

                                // Relatórios de Riscos
                                Route::get('/relatorios/riscos/pdf', [\App\Http\Controllers\RelatorioController::class, 'riscosPdf'])->name('relatorios.riscos.pdf');
                                Route::get('/relatorios/riscos/excel', [\App\Http\Controllers\RelatorioController::class, 'riscosExcel'])->name('relatorios.riscos.excel');

    // Session ping endpoint for session renewal
    Route::post('/session/ping', function () {
        return response()->json(['success' => true, 'timestamp' => now()->toIso8601String()]);
    })->name('session.ping');
});
