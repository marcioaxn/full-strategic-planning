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
        // Handle Authentication Exception (Session Expired / Unauthenticated)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            // Check if user had a previous session (session expired)
            if ($request->hasSession() && $request->session()->has('_previous')) {
                return redirect()
                    ->route('login')
                    ->with('status', 'Your session has expired. Please sign in again.');
            }

            // Unauthenticated user trying to access protected route
            return redirect()
                ->route('welcome')
                ->with('error', 'You need to be authenticated to access this page.');
        });

        // Handle CSRF Token Mismatch (419 Page Expired)
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'CSRF token mismatch.'], 419);
            }

            // Redirect to login for both authenticated and non-authenticated users
            return redirect()
                ->route('login')
                ->with('status', 'Your session has expired. Please sign in again.');
        });

        // Handle 403 Forbidden
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Access denied.'], 403);
            }

            // Only redirect to welcome if user is not authenticated
            if (!auth()->check()) {
                return redirect()
                    ->route('welcome')
                    ->with('error', 'You do not have permission to access this resource.');
            }

            // For authenticated users, show error page or redirect to dashboard
            return redirect()
                ->route('dashboard')
                ->with('error', 'You do not have permission to access this resource.');
        });

        // Handle 404 Not Found
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Resource not found.'], 404);
            }

            // Only redirect to welcome if user is not authenticated
            if (!auth()->check()) {
                return redirect()
                    ->route('welcome')
                    ->with('error', 'The page you are looking for could not be found.');
            }

            // For authenticated users, keep them in authenticated area
            // Let Laravel handle it normally (will show 404 page)
            return null;
        });

        // Handle 500 Server Errors (only for non-authenticated users)
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                return null; // Let Laravel handle JSON errors
            }

            // Only handle for non-authenticated users
            if (!auth()->check() && app()->environment('production')) {
                // Check if it's a server error (500)
                if (method_exists($e, 'getStatusCode') && $e->getStatusCode() >= 500) {
                    return redirect()
                        ->route('welcome')
                        ->with('error', 'An unexpected error occurred. Please try again later.');
                }
            }

            // Let Laravel handle the exception normally for authenticated users or in development
            return null;
        });
    })->create();
