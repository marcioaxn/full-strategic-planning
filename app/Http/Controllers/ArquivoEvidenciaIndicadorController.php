<?php

namespace App\Http\Controllers;

use App\Models\StrategicPlanning\Arquivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Entrega a evidência de um lançamento de indicador só a quem pode ver o
 * indicador (IndicadorPolicy::view). Mesmo padrão de ArquivoAnexoEntregaController.
 *
 * 🔴 A evidência era gravada no disco privado mas não havia rota que a
 * servisse: a tela listava o nome do arquivo e ninguém conseguia abri-lo.
 */
class ArquivoEvidenciaIndicadorController extends Controller
{
    /** Pasta em que LancarEvolucao::salvar() grava as evidências. */
    private const PASTA = 'pei/evidencias';

    /** Formatos que o navegador pode abrir na própria aba; o resto é baixado. */
    private const INLINE = [
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
    ];

    public function __invoke(Request $request, string $arquivo): StreamedResponse
    {
        $registro = Arquivo::findOrFail($arquivo);
        $indicador = $registro->evolucaoIndicador()->withTrashed()->first()?->indicador()->withTrashed()->first();

        abort_unless($indicador, 404);
        Gate::authorize('view', $indicador);

        $caminho = (string) $registro->dsc_nome_arquivo;
        abort_unless(str_starts_with($caminho, self::PASTA.'/') && ! str_contains($caminho, '..'), 404);

        $disco = collect(['local', 'public'])
            ->map(fn (string $nome) => Storage::disk($nome))
            ->first(fn ($d) => $d->exists($caminho));

        abort_unless($disco, 404, 'O arquivo desta evidência não foi encontrado no servidor.');

        $mime = self::INLINE[strtolower($registro->getExtensao())] ?? 'application/octet-stream';
        $cabecalhos = ['Content-Type' => $mime, 'X-Content-Type-Options' => 'nosniff'];
        // O nome vem de quem enviou: barra ou contrabarra no Content-Disposition
        // faz o Symfony lançar exceção (500). Só o nome-base, sem separadores.
        $nome = str_replace(['/', '\\'], '_', (string) ($registro->txt_assunto ?: basename($caminho)));

        return $mime === 'application/octet-stream' || $request->boolean('baixar')
            ? $disco->download($caminho, $nome, $cabecalhos)
            : $disco->response($caminho, $nome, $cabecalhos, 'inline');
    }
}
