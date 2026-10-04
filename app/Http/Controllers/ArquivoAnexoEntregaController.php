<?php

namespace App\Http\Controllers;

use App\Models\ActionPlan\EntregaAnexo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Entrega o anexo de uma entrega só a quem pode ver a entrega (EntregaPolicy).
 *
 * 🔴 Antes o anexo ficava no disco público e era baixado por URL direta, sem
 * login e sem Policy — a URL vazava por histórico, e-mail ou log e continuava
 * válida para quem já tinha perdido o acesso à iniciativa.
 *
 * Arquivo novo vai para o disco privado; o antigo, até `entregas:proteger-anexos`
 * movê-lo, ainda é lido do público — mas só por esta rota.
 */
class ArquivoAnexoEntregaController extends Controller
{
    /** Formatos que o navegador pode abrir na própria aba; o resto é baixado. */
    private const INLINE = ['application/pdf', 'image/png', 'image/jpeg', 'image/gif'];

    public function __invoke(Request $request, string $anexo): StreamedResponse
    {
        $registro = EntregaAnexo::findOrFail($anexo);
        $entrega = $registro->entrega()->withTrashed()->first();

        abort_unless($entrega, 404);
        Gate::authorize('view', $entrega);

        $caminho = (string) $registro->dsc_caminho;
        abort_unless(str_starts_with($caminho, EntregaAnexo::PASTA.'/') && ! str_contains($caminho, '..'), 404);

        $disco = collect([EntregaAnexo::DISCO, 'public'])
            ->map(fn (string $nome) => Storage::disk($nome))
            ->first(fn ($d) => $d->exists($caminho));

        abort_unless($disco, 404, 'O arquivo deste anexo não foi encontrado no servidor.');

        $mime = in_array($registro->dsc_mime_type, self::INLINE, true) ? $registro->dsc_mime_type : 'application/octet-stream';
        $cabecalhos = ['Content-Type' => $mime, 'X-Content-Type-Options' => 'nosniff'];
        $nome = $registro->dsc_nome_arquivo ?: basename($caminho);

        return $mime === 'application/octet-stream' || $request->boolean('baixar')
            ? $disco->download($caminho, $nome, $cabecalhos)
            : $disco->response($caminho, $nome, $cabecalhos, 'inline');
    }
}
