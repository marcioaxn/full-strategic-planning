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
        if (! auth()->check()) {
            return $next($request);
        }

        // 🔴 Durante a impersonação a senha pendente é a do usuário ASSUMIDO,
        // não a de quem está ao teclado. Forçar a troca prendia o Super Admin
        // em /trocar-senha (layout sem o "Voltar"), e trocar exigia a senha
        // atual do outro: a única saída era "Sair". Teste:
        // ImpersonacaoComTrocaDeSenhaPendenteTest.
        if ($request->hasSession() && $request->session()->has('impersonator_id')) {
            return $next($request);
        }

        // Se o usuário precisa trocar a senha
        if (auth()->user()->deveTrocarSenha()) {

            // Lista de exceções (coisas que ele PODE fazer mesmo sem trocar a senha)
            $excecoes = [
                'auth.trocar-senha',
                'logout',
                'current-user.destroy',
                'impersonate.stop', // A volta da impersonação nunca pode ser bloqueada.
                'livewire.update', // CRÍTICO: Permite que o Livewire funcione
                'livewire.upload',
                'session.ping',
            ];

            // Se a rota atual não estiver na lista de exceções, redireciona
            if (! $request->routeIs($excecoes)) {
                return redirect()->route('auth.trocar-senha');
            }
        }

        return $next($request);
    }
}
