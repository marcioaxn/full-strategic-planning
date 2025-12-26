<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordChange
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se não estiver logado, segue o fluxo normal
        if (!auth()->check()) {
            return $next($request);
        }

        // Se o usuário precisa trocar a senha
        if (auth()->user()->deveTrocarSenha()) {
            
            // Lista de exceções (coisas que ele PODE fazer mesmo sem trocar a senha)
            $excecoes = [
                'auth.trocar-senha',
                'logout',
                'current-user.destroy',
                'livewire.update', // CRÍTICO: Permite que o Livewire funcione
                'livewire.upload',
                'session.ping'
            ];

            // Se a rota atual não estiver na lista de exceções, redireciona
            if (!$request->routeIs($excecoes)) {
                return redirect()->route('auth.trocar-senha');
            }
        }

        return $next($request);
    }
}