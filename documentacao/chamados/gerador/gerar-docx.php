<?php

declare(strict_types=1);

/**
 * Gera o .docx do chamado de implantação para a Infra.
 *
 * O TEXTO vive em conteudo-chamado.php: revisar a redação não deve exigir ler
 * marcação OOXML. Este arquivo só formata e empacota — e monta o pacote inteiro
 * do zero (sem modelo), para nada de outro documento vazar para o chamado.
 *
 * Uso: php documentacao/chamados/gerador/gerar-docx.php <destino.docx>
 */
$destino = $argv[1] ?? null;

if ($destino === null) {
    fwrite(STDERR, "uso: php gerar-docx.php <destino.docx>\n");
    exit(1);
}

function esc(string $t): string
{
    return htmlspecialchars($t, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function p(string $pPr, string $rPr, string $texto): string
{
    return '<w:p><w:pPr>'.$pPr.'</w:pPr><w:r><w:rPr>'.$rPr.'</w:rPr>'
        .'<w:t xml:space="preserve">'.esc($texto).'</w:t></w:r></w:p>';
}

function titulo(string $t): string
{
    return p('<w:spacing w:after="60"/><w:jc w:val="center"/>',
        '<w:b/><w:bCs/><w:color w:val="1F4E79"/><w:sz w:val="28"/><w:szCs w:val="28"/>', $t);
}

function subtitulo(string $t): string
{
    return p('<w:spacing w:after="240"/><w:jc w:val="center"/>',
        '<w:i/><w:iCs/><w:sz w:val="22"/><w:szCs w:val="22"/>', $t);
}

function secao(string $t): string
{
    return p('<w:spacing w:before="280" w:after="120"/>',
        '<w:b/><w:bCs/><w:color w:val="1F4E79"/><w:sz w:val="24"/><w:szCs w:val="24"/>', $t);
}

function texto(string $t): string
{
    return p('<w:spacing w:after="120"/>', '', $t);
}

/** Comando de terminal ou linha de .env — Courier New, negrito, vermelho, recuado. */
function comando(string $t): string
{
    return p('<w:spacing w:before="40" w:after="40"/><w:ind w:left="284"/>',
        '<w:rFonts w:ascii="Courier New" w:eastAsia="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:b/><w:bCs/><w:color w:val="EE0000"/>',
        $t);
}

function marcador(string $t): string
{
    return p('<w:spacing w:after="60"/><w:ind w:left="284"/>', '', '•  '.$t);
}

function alerta(string $t, string $fundo = 'FFF2CC', string $borda = 'BF8F00'): string
{
    return p(
        '<w:pBdr>'
        .'<w:top w:val="single" w:sz="6" w:space="4" w:color="'.$borda.'"/>'
        .'<w:left w:val="single" w:sz="6" w:space="4" w:color="'.$borda.'"/>'
        .'<w:bottom w:val="single" w:sz="6" w:space="4" w:color="'.$borda.'"/>'
        .'<w:right w:val="single" w:sz="6" w:space="4" w:color="'.$borda.'"/>'
        .'</w:pBdr><w:shd w:val="clear" w:color="auto" w:fill="'.$fundo.'"/>'
        .'<w:spacing w:before="120" w:after="120"/><w:ind w:left="113" w:right="113"/>',
        '<w:b/><w:bCs/>',
        $t
    );
}

/** @param list<array{0:string,1:string}> $linhas */
function tabela(array $linhas): string
{
    $x = '<w:tbl><w:tblPr><w:tblW w:w="9600" w:type="dxa"/><w:tblBorders>'
        .'<w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/>'
        .'<w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/>'
        .'<w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/>'
        .'<w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/>'
        .'<w:insideH w:val="single" w:sz="4" w:space="0" w:color="auto"/>'
        .'<w:insideV w:val="single" w:sz="4" w:space="0" w:color="auto"/>'
        .'</w:tblBorders><w:tblCellMar><w:left w:w="80" w:type="dxa"/><w:right w:w="80" w:type="dxa"/></w:tblCellMar>'
        .'</w:tblPr><w:tblGrid><w:gridCol w:w="3400"/><w:gridCol w:w="6200"/></w:tblGrid>';

    foreach ($linhas as [$rotulo, $valor]) {
        $x .= '<w:tr>'
            .'<w:tc><w:tcPr><w:tcW w:w="3400" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="F2F2F2"/></w:tcPr>'
            .p('<w:spacing w:after="20"/>', '<w:b/><w:bCs/>', $rotulo).'</w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="6200" w:type="dxa"/></w:tcPr>'
            .p('<w:spacing w:after="20"/>', '', $valor).'</w:tc></w:tr>';
    }

    return $x.'</w:tbl>';
}

function rodape(string $t): string
{
    return p('<w:pBdr><w:top w:val="single" w:sz="4" w:space="1" w:color="1F4E79"/></w:pBdr>'
        .'<w:spacing w:before="300"/><w:jc w:val="center"/>',
        '<w:i/><w:iCs/><w:color w:val="666666"/><w:sz w:val="16"/><w:szCs w:val="16"/>', $t);
}

// ── Conteúdo ────────────────────────────────────────────────────────────────

$montar = require __DIR__.'/conteudo-chamado.php';

$b = $montar([
    'titulo' => 'titulo', 'subtitulo' => 'subtitulo', 'secao' => 'secao', 'texto' => 'texto',
    'comando' => 'comando', 'marcador' => 'marcador', 'alerta' => 'alerta',
    'tabela' => 'tabela', 'rodape' => 'rodape',
]);

// ── Partes do pacote OOXML ──────────────────────────────────────────────────

$ns = 'xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"';

$documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<w:document '.$ns.'><w:body>'.implode('', $b)
    .'<w:sectPr><w:pgSz w:w="11906" w:h="16838"/>'
    .'<w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1134" w:header="708" w:footer="708" w:gutter="0"/>'
    .'</w:sectPr></w:body></w:document>';

$stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<w:styles '.$ns.'><w:docDefaults><w:rPrDefault><w:rPr>'
    .'<w:rFonts w:ascii="Calibri" w:eastAsia="Calibri" w:hAnsi="Calibri" w:cs="Calibri"/>'
    .'<w:sz w:val="22"/><w:szCs w:val="22"/><w:lang w:val="pt-BR"/>'
    .'</w:rPr></w:rPrDefault><w:pPrDefault><w:pPr><w:spacing w:after="120" w:line="264" w:lineRule="auto"/></w:pPr></w:pPrDefault>'
    .'</w:docDefaults></w:styles>';

$contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
    .'<Default Extension="xml" ContentType="application/xml"/>'
    .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
    .'<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
    .'</Types>';

$rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
    .'</Relationships>';

$documentRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
    .'</Relationships>';

// Valida cada XML antes de empacotar — docx corrompido só se descobre ao abrir.
foreach (['document.xml' => $documentXml, 'styles.xml' => $stylesXml] as $nome => $xml) {
    $doc = new DOMDocument;
    libxml_use_internal_errors(true);

    if (! $doc->loadXML($xml)) {
        fwrite(STDERR, "XML invalido em {$nome}:\n");

        foreach (libxml_get_errors() as $e) {
            fwrite(STDERR, '  '.trim($e->message).PHP_EOL);
        }

        exit(1);
    }
}

// ── Empacotamento ───────────────────────────────────────────────────────────

if (is_file($destino)) {
    unlink($destino);
}

$zip = new ZipArchive;

if ($zip->open($destino, ZipArchive::CREATE) !== true) {
    fwrite(STDERR, "nao criou o destino\n");
    exit(1);
}

$zip->addFromString('[Content_Types].xml', $contentTypes);
$zip->addFromString('_rels/.rels', $rels);
$zip->addFromString('word/_rels/document.xml.rels', $documentRels);
$zip->addFromString('word/document.xml', $documentXml);
$zip->addFromString('word/styles.xml', $stylesXml);
$zip->close();

echo 'Gerado: '.$destino.PHP_EOL;
echo 'Blocos: '.count($b).PHP_EOL;
