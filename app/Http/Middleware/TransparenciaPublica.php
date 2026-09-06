<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guarda da área pública de Transparência.
 *
 * O pedido do gestor é claro: o cidadão navega o Mapa Estratégico sem login,
 * inclusive mergulhando nos dados pelos cliques — mas nada pode ser escrito.
 *
 * Este middleware garante a segunda metade:
 *
 *  1. Só GET e HEAD. Qualquer outro verbo é recusado antes de chegar ao
 *     componente. Não basta confiar na Policy: método público de componente
 *     Livewire é invocável direto pelo navegador.
 *  2. Limite de taxa. Estas telas calculam atingimento por objetivo; sem
 *     limite, uma página pública vira amplificador de negação de serviço.
 *  3. Cabeçalho de não-indexação de dado individual. A página em si pode ser
 *     indexada; a URL de um indicador específico, não.
 */
class TransparenciaPublica
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            abort(405, 'A área de Transparência é somente leitura.');
        }

        $resposta = $next($request);

        if ($resposta instanceof Response) {
            $resposta->headers->set('X-Robots-Tag', 'noindex, nofollow', false);
        }

        return $resposta;
    }
}
