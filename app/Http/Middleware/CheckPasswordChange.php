<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->deveTrocarSenha()) {
            if (!$request->routeIs('auth.trocar-senha') && !$request->routeIs('logout') && !$request->routeIs('current-user.destroy')) {
                return redirect()->route('auth.trocar-senha');
            }
        }

        return $next($request);
    }
}
