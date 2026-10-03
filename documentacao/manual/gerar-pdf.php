<?php

declare(strict_types=1);

/**
 * Gera documentacao/manual/MANUAL-DE-USO.pdf a partir do MANUAL-DE-USO.md.
 *
 * O Markdown é a fonte única; o PDF é derivado e deve ser refeito sempre que o
 * manual mudar:
 *
 *     php documentacao/manual/gerar-pdf.php
 *
 * O HTML é impresso pelo Chrome sem janela (--headless): ele respeita as
 * imagens, as tabelas, as âncoras do sumário e a numeração de páginas.
 */

require __DIR__.'/../../vendor/autoload.php';

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

$pasta = __DIR__;
$markdown = file_get_contents($pasta.'/MANUAL-DE-USO.md');

$chrome = getenv('CHROME_PATH') ?: (function (): string {
    foreach ([
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        '/usr/bin/google-chrome',
        '/usr/bin/chromium',
    ] as $caminho) {
        if (is_file($caminho)) {
            return $caminho;
        }
    }
    fwrite(STDERR, "Chrome não encontrado. Defina CHROME_PATH.\n");
    exit(1);
})();

// O título (# ...) e a linha de versão viram a capa.
$markdown = preg_replace('/\A# [^\n]*\n+(> [^\n]*\n)*/u', '', $markdown);

$ambiente = new Environment;
$ambiente->addExtension(new CommonMarkCoreExtension);
$ambiente->addExtension(new GithubFlavoredMarkdownExtension);
$corpo = (string) (new MarkdownConverter($ambiente))->convert($markdown);

// Âncoras no mesmo formato do GitHub, que é como o sumário do manual as escreve.
$slugsUsados = [];
$corpo = preg_replace_callback('/<(h[1-4])>(.*?)<\/\1>/su', function (array $m) use (&$slugsUsados): string {
    $texto = html_entity_decode(strip_tags($m[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $slug = mb_strtolower($texto, 'UTF-8');
    $slug = preg_replace('/[^\p{L}\p{N}\s\-]/u', '', $slug);
    $slug = preg_replace('/\s/u', '-', $slug);
    if (isset($slugsUsados[$slug])) {
        $slug .= '-'.(++$slugsUsados[$slug]);
    } else {
        $slugsUsados[$slug] = 0;
    }

    return sprintf('<%1$s id="%2$s">%3$s</%1$s>', $m[1], htmlspecialchars($slug, ENT_QUOTES), $m[2]);
}, $corpo);

$base = 'file:///'.str_replace('\\', '/', $pasta).'/';
$data = date('d/m/Y');

$html = <<<HTML
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<base href="{$base}">
<title>Manual de Uso — Sistema PEI</title>
<style>
  @page {
    size: A4;
    margin: 18mm 16mm 18mm 16mm;
    @bottom-left { content: "Manual de Uso — Sistema PEI · Planejamento Estratégico Integrado"; font: 8pt Arial, sans-serif; color: #6b7280; }
    @bottom-right { content: "Página " counter(page) " de " counter(pages); font: 8pt Arial, sans-serif; color: #6b7280; }
  }
  @page :first { @bottom-left { content: none; } @bottom-right { content: none; } }
  * { box-sizing: border-box; }
  body { font: 10.5pt/1.55 Arial, Helvetica, sans-serif; color: #1f2937; }
  .capa { height: 255mm; display: flex; flex-direction: column; justify-content: center; text-align: center; break-after: page; }
  .capa .selo { font-size: 10pt; letter-spacing: .12em; text-transform: uppercase; color: #1B408E; font-weight: bold; }
  .capa h1 { font-size: 30pt; margin: 14mm 0 4mm; color: #0d1b2e; border: 0; }
  .capa .sub { font-size: 14pt; color: #374151; }
  .capa .rodape { margin-top: 30mm; font-size: 10pt; color: #6b7280; }
  h1 { font-size: 20pt; }
  h2 { font-size: 17pt; color: #1B408E; border-bottom: 2px solid #1B408E; padding-bottom: 2mm; margin-top: 0; break-before: page; }
  h3 { font-size: 13pt; color: #0d1b2e; margin-top: 7mm; break-after: avoid; }
  h4 { font-size: 11pt; break-after: avoid; }
  p, li { orphans: 3; widows: 3; }
  a { color: #1B408E; text-decoration: none; }
  img { display: block; max-width: 100%; max-height: 120mm; margin: 3mm auto 5mm; border: 1px solid #d1d5db; border-radius: 4px; break-inside: avoid; }
  table { width: 100%; border-collapse: collapse; margin: 3mm 0 5mm; font-size: 9.5pt; break-inside: auto; }
  tr { break-inside: avoid; }
  th, td { border: 1px solid #d1d5db; padding: 1.6mm 2.2mm; vertical-align: top; text-align: left; }
  th { background: #eef2fb; color: #0d1b2e; }
  blockquote { margin: 4mm 0; padding: 2.5mm 4mm; border-left: 4px solid #f59e0b; background: #fffbeb; color: #374151; }
  code { font: 9pt Consolas, monospace; background: #f3f4f6; padding: 0 1mm; border-radius: 2px; }
  hr { display: none; }
</style>
</head>
<body>
  <section class="capa">
    <div class="selo">GPPEI/MGI 2025 · Gestão Pública Federal</div>
    <h1>Manual de Uso</h1>
    <div class="sub">Sistema PEI — Planejamento Estratégico Integrado</div>
    <div class="rodape">Versão de {$data}</div>
  </section>
  {$corpo}
</body>
</html>
HTML;

$htmlTemporario = sys_get_temp_dir().DIRECTORY_SEPARATOR.'manual-pei-'.getmypid().'.html';
file_put_contents($htmlTemporario, $html);

$destino = $pasta.DIRECTORY_SEPARATOR.'MANUAL-DE-USO.pdf';
@unlink($destino);

$comando = sprintf(
    '"%s" --headless=new --disable-gpu --no-pdf-header-footer --allow-file-access-from-files --print-to-pdf=%s %s 2>&1',
    $chrome,
    escapeshellarg($destino),
    escapeshellarg('file:///'.str_replace('\\', '/', $htmlTemporario))
);
exec($comando, $saida, $codigo);
@unlink($htmlTemporario);

if (! is_file($destino) || filesize($destino) === 0) {
    fwrite(STDERR, "Falha ao gerar o PDF (código {$codigo}):\n".implode("\n", $saida)."\n");
    exit(1);
}

printf("Gerado: %s (%.1f MB)\n", $destino, filesize($destino) / 1048576);
