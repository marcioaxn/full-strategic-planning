<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Entrega o PDF do acervo: aberto no navegador ("Abrir") ou como download
 * ("Baixar", com ?baixar=1). O arquivo está no disco privado; só passa por aqui,
 * depois da DocumentoPolicy.
 */
class ArquivoDocumentoController extends Controller
{
    public function __invoke(Request $request, string $documento): StreamedResponse
    {
        $registro = Documento::findOrFail($documento);
        Gate::authorize('view', $registro);

        $caminho = (string) $registro->dsc_caminho;
        $disco = Storage::disk(Documento::DISCO);

        // O caminho vem do banco, mas só se serve o que está na pasta do acervo.
        abort_unless(
            str_starts_with($caminho, Documento::PASTA.'/') && ! str_contains($caminho, '..') && $disco->exists($caminho),
            404,
            'O arquivo deste documento não foi encontrado no servidor.'
        );

        $nome = $registro->dsc_nome_arquivo ?: 'documento.pdf';
        $cabecalhos = ['Content-Type' => 'application/pdf'];

        return $request->boolean('baixar')
            ? $disco->download($caminho, $nome, $cabecalhos)
            : $disco->response($caminho, $nome, $cabecalhos, 'inline');
    }
}
