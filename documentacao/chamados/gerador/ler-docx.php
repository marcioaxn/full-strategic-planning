<?php

declare(strict_types=1);

/**
 * Imprime o texto de um .docx, parágrafo por parágrafo, para conferir o chamado
 * sem abrir o Word.
 *
 * Uso: php documentacao/chamados/gerador/ler-docx.php <arquivo.docx>
 */
$arquivo = $argv[1] ?? null;

if ($arquivo === null || ! is_file($arquivo)) {
    fwrite(STDERR, "uso: php ler-docx.php <arquivo.docx>\n");
    exit(1);
}

$zip = new ZipArchive;

if ($zip->open($arquivo) !== true) {
    fwrite(STDERR, "nao abriu o arquivo\n");
    exit(1);
}

$xml = (string) $zip->getFromName('word/document.xml');
$zip->close();

$doc = new DOMDocument;
$doc->loadXML($xml);
$xp = new DOMXPath($doc);
$xp->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

foreach ($xp->query('//w:p') as $p) {
    $linha = '';

    foreach ($xp->query('.//w:t', $p) as $t) {
        $linha .= $t->textContent;
    }

    echo $linha.PHP_EOL;
}
