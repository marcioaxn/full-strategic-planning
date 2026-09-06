<?php

/**
 * Regenera a dívida congelada do guarda de arquivo.
 *
 *     php .claude/hooks/gerar-divida.php
 *
 * Varre todo arquivo rastreado pelo git, roda o mesmo guarda que roda nos
 * hooks, e grava o que ele encontrou em divida-congelada.json. O guarda passa
 * a perdoar exatamente esse passivo — e nada além dele.
 *
 * A LISTA SÓ ENCOLHE. Rodar isto depois de limpar código é correto: a dívida
 * diminui. Rodar isto para calar um aviso em código NOVO é fraudar a trava —
 * o script recusa quando a dívida cresceria, a menos que se passe --aceitar-aumento
 * (que existe só para o caso de uma regra nova, deliberadamente adicionada ao
 * guarda, precisar registrar o passivo que ela acabou de revelar).
 */

$raiz = dirname(__DIR__, 2);
$guarda = __DIR__.'/guarda-arquivo.php';
$destino = __DIR__.'/divida-congelada.json';
$aceitarAumento = in_array('--aceitar-aumento', $argv, true);

chdir($raiz);

$saida = [];
exec('git ls-files 2>&1', $saida, $codigo);

if ($codigo !== 0) {
    fwrite(STDERR, "Não foi possível listar os arquivos do git.\n");
    exit(1);
}

$extensoes = ['php', 'xml', 'sql', 'js', 'json', 'env', 'yml', 'yaml'];
$arquivos = [];

foreach ($saida as $caminho) {
    $ext = strtolower(pathinfo($caminho, PATHINFO_EXTENSION));

    if (in_array($ext, $extensoes, true)) {
        $arquivos[] = $caminho;
    }
}

echo 'Verificando '.count($arquivos)." arquivos rastreados...\n";

$novaDivida = [];

foreach ($arquivos as $caminho) {
    $payload = json_encode(['tool_input' => ['file_path' => $raiz.'/'.$caminho]]);

    // A dívida precisa ficar DESLIGADA aqui: rodar o guarda com ela aplicada
    // faria o gerador não ver problema nenhum e gravar uma lista vazia,
    // apagando o passivo em vez de registrá-lo.
    $processo = proc_open(
        'php '.escapeshellarg($guarda),
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $canos,
        null,
        ['PEI_GUARDA_IGNORAR_DIVIDA' => '1'] + getenv()
    );

    if (! is_resource($processo)) {
        continue;
    }

    fwrite($canos[0], (string) $payload);
    fclose($canos[0]);
    $resposta = stream_get_contents($canos[1]);
    fclose($canos[1]);
    fclose($canos[2]);
    proc_close($processo);

    if (trim((string) $resposta) === '') {
        continue;
    }

    $json = json_decode((string) $resposta, true);
    $motivo = $json['reason'] ?? '';

    // Os códigos vêm entre colchetes, no começo de cada linha do motivo.
    if (preg_match_all('/^- \[([a-z0-9\-]+)\]/m', $motivo, $achados)) {
        $codigos = array_values(array_unique($achados[1]));
        sort($codigos);
        $novaDivida[$caminho] = $codigos;
    }
}

ksort($novaDivida);

// -------------------------------------------------------------------------
// A lista só encolhe
// -------------------------------------------------------------------------
$anterior = is_file($destino)
    ? (json_decode((string) file_get_contents($destino), true)['arquivos'] ?? [])
    : [];

$contar = static function (array $d): int {
    return array_sum(array_map('count', $d));
};

$antes = $contar($anterior);
$agora = $contar($novaDivida);

if ($anterior !== [] && $agora > $antes && ! $aceitarAumento) {
    fwrite(STDERR,
        "RECUSADO: a dívida passaria de {$antes} para {$agora} apontamentos.\n"
        ."A lista só encolhe. Se um apontamento novo apareceu, o certo é corrigir o código.\n"
        ."Se você acabou de ACRESCENTAR uma regra ao guarda e precisa registrar o passivo\n"
        ."que ela revelou, rode de novo com --aceitar-aumento e diga isso ao gestor.\n"
    );
    exit(1);
}

file_put_contents($destino, json_encode([
    'gerado_em' => date('Y-m-d'),
    'como_regerar' => 'php .claude/hooks/gerar-divida.php',
    'regra' => 'Esta lista SÓ ENCOLHE. Ela perdoa o passivo que já existia; nunca código novo.',
    'apontamentos' => $agora,
    'arquivos' => $novaDivida,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");

$delta = $antes === 0 ? '' : ' (antes: '.$antes.')';
echo 'Dívida congelada gravada: '.count($novaDivida).' arquivos, '.$agora.' apontamentos'.$delta.".\n";
