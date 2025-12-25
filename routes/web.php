<?php

use App\Livewire\LeadsTable;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

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
    
    // Placeholder Routes for Phase 1 (Remaining)
    Route::get('/pei', function() { return view('dashboard'); })->name('pei.index');
    Route::get('/objetivos', function() { return view('dashboard'); })->name('objetivos.index');
    Route::get('/planos', function() { return view('dashboard'); })->name('planos.index');
    Route::get('/indicadores', function() { return view('dashboard'); })->name('indicadores.index');
    Route::get('/riscos', function() { return view('dashboard'); })->name('riscos.index');

    // Session ping endpoint for session renewal
    Route::post('/session/ping', function () {
        return response()->json(['success' => true, 'timestamp' => now()->toIso8601String()]);
    })->name('session.ping');
});
