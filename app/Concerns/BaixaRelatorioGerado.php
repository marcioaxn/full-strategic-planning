<?php

namespace App\Concerns;

use App\Models\Reports\RelatorioGerado;
use Illuminate\Support\Facades\Storage;

/**
 * Download de relatório já gerado, com verificação de autorização.
 *
 * Existe como trait porque o mesmo download aparece em dois componentes
 * (ListarRelatorios e HistoricoRelatorios). Duplicar a verificação é como a
 * falha nasceu: a listagem filtra por usuário, o método público não filtrava
 * nada — e todo método público de componente Livewire é invocável direto pelo
 * navegador, sem passar pela listagem.
 */
trait BaixaRelatorioGerado
{
    /** Disco privado atual. */
    private const DISCO_RELATORIOS = 'relatorios';

    /** Onde os relatórios eram gravados antes; mantido só para leitura. */
    private const DISCO_RELATORIOS_LEGADO = 'public';

    public function download(string $id)
    {
        $relatorio = RelatorioGerado::findOrFail($id);

        $this->authorize('download', $relatorio);

        $caminho = $this->caminhoConfinado($relatorio->dsc_caminho_arquivo);

        if ($caminho === null) {
            return $this->recusarDownload('Caminho de arquivo inválido.');
        }

        foreach ([self::DISCO_RELATORIOS, self::DISCO_RELATORIOS_LEGADO] as $disco) {
            if (Storage::disk($disco)->exists($caminho)) {
                return Storage::disk($disco)->download($caminho);
            }
        }

        return $this->recusarDownload(
            'O arquivo não está mais disponível. Gere o relatório novamente.'
        );
    }

    /**
     * Evento em vez de flash de sessão: o flash só aparece no próximo
     * carregamento de página, e aqui não há recarga — o usuário clicaria de
     * novo sem entender por que nada aconteceu.
     */
    private function recusarDownload(string $mensagem): null
    {
        $this->dispatch('notify', message: $mensagem, style: 'warning');

        return null;
    }

    /**
     * O caminho vem do banco. Confina ao diretório do disco: recusa caminho
     * absoluto, esquema de protocolo e travessia com "..".
     */
    private function caminhoConfinado(?string $caminho): ?string
    {
        if ($caminho === null || trim($caminho) === '') {
            return null;
        }

        $caminho = str_replace('\\', '/', trim($caminho));

        if (str_contains($caminho, '://') || preg_match('#^(?:/|[A-Za-z]:/)#', $caminho)) {
            return null;
        }

        foreach (explode('/', $caminho) as $segmento) {
            if ($segmento === '..') {
                return null;
            }
        }

        return ltrim($caminho, '/');
    }
}
