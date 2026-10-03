<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Conta sem perfil de acesso não entra na área restrita.
 *
 * 🔴 O CASO: o autocadastro está ativo (Fortify registration) e grava a conta
 * já ativa, sem perfil nenhum. Várias telas só conferiam se havia login — e
 * qualquer pessoa que se cadastrasse lia o diretório de usuários, o painel da
 * unidade raiz e os riscos. Uma conta sem vínculo agora vê só a página que
 * explica que o acesso aguarda liberação por um administrador.
 */
class ExigePerfilDeAcesso
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->temPerfilDeAcesso()) {
            if ($request->expectsJson()) {
                abort(403, 'Sua conta ainda não tem perfil de acesso.');
            }

            return redirect()->route('acesso.pendente');
        }

        return $next($request);
    }
}
