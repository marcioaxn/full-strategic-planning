<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * A versão em execução e o momento do último deploy — o que o rodapé mostra
 * em todas as telas, com ou sem login.
 *
 * Existe para responder, sem abrir chamado, "o deploy subiu mesmo a última
 * versão?". O número sozinho não responde: depende de alguém lembrar de
 * incrementá-lo. O commit e a data respondem.
 *
 * DE ONDE VEM: a Infra implanta com `git pull origin main` (ver o chamado em
 * documentacao/chamados/gerador). Então o servidor TEM .git, e o próprio git
 * já registra o deploy:
 *
 *   - commit: .git/HEAD → refs/heads/<branch> (ou packed-refs);
 *   - data do deploy: a última linha de .git/logs/HEAD (o reflog), gravada no
 *     instante em que o pull moveu o HEAD. Sem reflog, a data de modificação
 *     do arquivo da ref, que o pull também regrava.
 *
 * Tudo por LEITURA DE ARQUIVO — nenhum processo `git` por requisição, e nenhum
 * passo novo no roteiro da Infra.
 *
 * Sem .git (cópia de pastas), o rodapé diz que o deploy não é identificável,
 * em vez de inventar um dado: rodapé que mente sobre a versão leva a concluir
 * que o deploy subiu quando não subiu.
 */
class VersaoAplicacao
{
    /** @var array{numero: string, commit: ?string, branch: ?string, deploy: ?Carbon}|null */
    private static ?array $cache = null;

    /**
     * @return array{numero: string, commit: ?string, branch: ?string, deploy: ?Carbon}
     */
    public static function atual(?string $raiz = null): array
    {
        if ($raiz === null && self::$cache !== null) {
            return self::$cache;
        }

        $git = rtrim($raiz ?? base_path(), '\\/').DIRECTORY_SEPARATOR.'.git';

        $dados = [
            'numero' => (string) config('versao.numero', '—'),
            'commit' => null,
            'branch' => null,
            'deploy' => null,
        ];

        try {
            $dados = array_merge($dados, self::lerGit($git));
        } catch (\Throwable) {
            // Rodapé nunca derruba a página: sem leitura, fica sem o dado.
        }

        if ($raiz === null) {
            self::$cache = $dados;
        }

        return $dados;
    }

    /** Limpa a memoização do processo. */
    public static function esquecer(): void
    {
        self::$cache = null;
    }

    /**
     * Texto do rodapé. Ex.: "v2.0.0 · 4468184 · último deploy 03/10/2026 14:32"
     */
    public static function paraExibicao(?string $raiz = null): string
    {
        $v = self::atual($raiz);
        $partes = ['v'.$v['numero']];

        if ($v['commit'] !== null) {
            $partes[] = $v['commit'];
        }

        $partes[] = $v['deploy'] !== null
            ? 'último deploy '.$v['deploy']->format('d/m/Y H:i')
            : 'deploy não identificado';

        return implode(' · ', $partes);
    }

    /**
     * @return array{commit?: string, branch?: string, deploy?: Carbon}
     */
    private static function lerGit(string $git): array
    {
        $head = self::lerLinha($git.'/HEAD');

        if ($head === null) {
            return [];
        }

        $resultado = [];
        $arquivoRef = null;

        if (str_starts_with($head, 'ref: ')) {
            $ref = trim(substr($head, 5));
            $resultado['branch'] = preg_replace('#^refs/heads/#', '', $ref);
            $arquivoRef = $git.'/'.$ref;
            $hash = self::lerLinha($arquivoRef) ?? self::hashEmPackedRefs($git, $ref);
        } else {
            // HEAD destacado (checkout de uma tag ou de um commit).
            $hash = $head;
        }

        if ($hash !== null && preg_match('/^[0-9a-f]{40}$/', $hash)) {
            $resultado['commit'] = substr($hash, 0, 7);
        }

        $momento = self::momentoDoUltimoMovimento($git.'/logs/HEAD');

        if ($momento === null && $arquivoRef !== null && is_file($arquivoRef)) {
            $momento = filemtime($arquivoRef) ?: null;
        }

        if ($momento !== null) {
            $resultado['deploy'] = Carbon::createFromTimestamp($momento, config('app.timezone'));
        }

        return $resultado;
    }

    private static function lerLinha(string $arquivo): ?string
    {
        if (! is_file($arquivo) || ! is_readable($arquivo)) {
            return null;
        }

        $linha = trim((string) file_get_contents($arquivo, false, null, 0, 512));

        return $linha === '' ? null : $linha;
    }

    private static function hashEmPackedRefs(string $git, string $ref): ?string
    {
        $arquivo = $git.'/packed-refs';

        if (! is_file($arquivo)) {
            return null;
        }

        foreach (file($arquivo, FILE_IGNORE_NEW_LINES) ?: [] as $linha) {
            if (str_ends_with($linha, ' '.$ref)) {
                return strtok($linha, ' ') ?: null;
            }
        }

        return null;
    }

    /**
     * Timestamp da última linha do reflog. Formato da linha:
     * "<antigo> <novo> Nome <email> <unix> <fuso>\t<mensagem>".
     * Lê só o fim do arquivo: o reflog cresce a cada operação.
     */
    private static function momentoDoUltimoMovimento(string $reflog): ?int
    {
        if (! is_file($reflog) || ! is_readable($reflog)) {
            return null;
        }

        $tamanho = filesize($reflog) ?: 0;
        $fim = (string) file_get_contents($reflog, false, null, max(0, $tamanho - 4096));
        $linhas = array_values(array_filter(explode("\n", $fim), fn (string $l): bool => trim($l) !== ''));
        $ultima = end($linhas);

        if ($ultima === false) {
            return null;
        }

        $cabecalho = explode("\t", $ultima, 2)[0];

        return preg_match('/>\s+(\d{9,})\s+[+-]\d{4}$/', $cabecalho, $m) ? (int) $m[1] : null;
    }
}
