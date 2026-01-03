<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\CheckPasswordChange::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle Authentication Exception (Sessão Expirada / Não Autenticado)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Não autenticado.'], 401);
            }

            // Sessão expirada
            if ($request->hasSession() && $request->session()->has('_previous')) {
                return redirect()
                    ->route('login')
                    ->with('status', 'Sua sessão expirou. Por favor, faça login novamente.');
            }

            // Usuário não autenticado tentando acessar rota protegida
            return redirect()
                ->route('welcome')
                ->with('error', 'Você precisa estar autenticado para acessar esta página.');
        });

        // Handle CSRF Token Mismatch (419 Página Expirada)
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Token CSRF inválido.'], 419);
            }

            return redirect()
                ->to(url('/'))
                ->with('status', 'Sua sessão expirou por inatividade. Você foi redirecionado para a página inicial.');
        });

        // Handle 403 Forbidden (Acesso Negado)
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Acesso negado.'], 403);
            }

            if (!auth()->check()) {
                return redirect()
                    ->route('welcome')
                    ->with('error', 'Você não tem permissão para acessar este recurso.');
            }

            return redirect()
                ->route('dashboard')
                ->with('error', 'Você não tem permissão para realizar esta ação.');
        });

        // Handle 404 Not Found (Não Encontrado)
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Recurso não encontrado.'], 404);
            }

            if (!auth()->check()) {
                return redirect()
                    ->route('welcome')
                    ->with('error', 'A página que você está procurando não foi encontrada.');
            }

            return null; // Deixa o Laravel renderizar a view errors::404 padrão
        });

        // Handle Database Query Exceptions (Integridade Referencial)
        $exceptions->render(function (\Illuminate\Database\QueryException $e, Request $request) {
            $errorCode = $e->errorInfo[1] ?? 0;

            // Postgres Foreign Key Violation (23503)
            if ($errorCode == 23503) {
                $message = 'Não é possível excluir ou alterar este registro pois ele está vinculado a outros dados do sistema.';
                
                if ($request->expectsJson()) {
                    return response()->json(['message' => $message], 422);
                }

                return back()->with('error', $message);
            }

            return null; // Deixa outros erros de banco explodirem (em dev) ou 500 (em prod)
        });

        // Handle 500 Server Errors (Produção / Não Autenticado)
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            if (!auth()->check() && app()->environment('production')) {
                // Se for erro de servidor 500+, redireciona para welcome amigável
                if (method_exists($e, 'getStatusCode') && $e->getStatusCode() >= 500) {
                    return redirect()
                        ->route('welcome')
                        ->with('error', 'Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.');
                }
            }

            return null;
        });
    })->create();
