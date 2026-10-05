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
 * O HTML é impresso pelo Chrome sem janela (--headless). O sumário é montado
 * aqui, a partir dos títulos (# Parte, ## capítulo, ### seção, #### subseção),
 * com o número da página de cada um. O número vem do próprio PDF: o Chrome
 * grava em /Dests a página de cada âncora que recebe link. Por isso a geração
 * tem duas passagens — a primeira descobre as páginas, a segunda as escreve
 * (o número ocupa largura fixa, então o sumário não muda de tamanho).
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

// O título (# ...) e as linhas de citação logo abaixo viram a capa.
$markdown = preg_replace('/\A# [^\n]*\n+(> [^\n]*\n)*/u', '', $markdown);

$ambiente = new Environment;
$ambiente->addExtension(new CommonMarkCoreExtension);
$ambiente->addExtension(new GithubFlavoredMarkdownExtension);
$corpo = (string) (new MarkdownConverter($ambiente))->convert($markdown);

// Cada título ganha uma âncora simples (t1, t2…) e entra no sumário.
$titulos = [];
$corpo = preg_replace_callback('/<(h([1-4]))>(.*?)<\/\1>/su', function (array $m) use (&$titulos): string {
    $id = 't'.(count($titulos) + 1);
    $titulos[] = ['id' => $id, 'nivel' => (int) $m[2], 'html' => $m[3]];

    return sprintf('<%1$s id="%2$s">%3$s</%1$s>', $m[1], $id, $m[3]);
}, $corpo);

/** @param array<string, int> $paginas */
$montarSumario = function (array $paginas) use ($titulos): string {
    $linhas = '';
    foreach ($titulos as $t) {
        $pagina = $paginas[$t['id']] ?? '000';
        $linhas .= sprintf(
            '<li class="n%d"><a href="#%s"><span class="tx">%s</span><span class="pg">%s</span></a></li>',
            $t['nivel'], $t['id'], strip_tags($t['html'], '<em><strong><code>'), $pagina
        );
    }

    return '<section class="sumario"><h1 class="titulo-sumario">Sumário</h1><ul>'.$linhas.'</ul></section>';
};

$base = 'file:///'.str_replace('\\', '/', $pasta).'/';
$data = date('d/m/Y');

$montarHtml = fn (string $sumario): string => <<<HTML
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
  .sumario { break-after: page; }
  .sumario .titulo-sumario { font-size: 20pt; color: #1B408E; margin: 0 0 5mm; padding-top: 0; break-before: auto; text-align: left; }
  .sumario ul { list-style: none; margin: 0; padding: 0; }
  .sumario li { break-inside: avoid; }
  .sumario a { display: flex; align-items: baseline; color: #1f2937; }
  .sumario .tx { flex: 0 1 auto; }
  .sumario a::after { content: ""; flex: 1 1 auto; order: 1; border-bottom: 1px dotted #9ca3af; margin: 0 1.5mm; min-width: 6mm; }
  .sumario .pg { order: 2; flex: 0 0 9mm; text-align: right; font-variant-numeric: tabular-nums; }
  .sumario .n1 { font-weight: bold; font-size: 11pt; color: #1B408E; margin-top: 4mm; text-transform: uppercase; }
  .sumario .n1 a { color: #1B408E; }
  .sumario .n2 { font-weight: bold; font-size: 10pt; margin-top: 1.6mm; padding-left: 3mm; }
  .sumario .n3 { font-size: 9.5pt; padding-left: 9mm; }
  .sumario .n4 { font-size: 8.8pt; padding-left: 15mm; color: #4b5563; }
  .sumario .n4 a { color: #4b5563; }
  h1 { font-size: 24pt; color: #0d1b2e; break-before: page; padding-top: 90mm; text-align: center; }
  h1 + h2 { break-before: page; }
  h2 { font-size: 17pt; color: #1B408E; border-bottom: 2px solid #1B408E; padding-bottom: 2mm; margin-top: 0; break-before: page; }
  h3 { font-size: 13pt; color: #0d1b2e; margin-top: 7mm; break-after: avoid; }
  h4 { font-size: 11pt; break-after: avoid; }
  p, li { orphans: 3; widows: 3; }
  a { color: #1B408E; text-decoration: none; }
  img { display: block; max-width: 100%; max-height: 120mm; margin: 3mm auto 1mm; border: 1px solid #d1d5db; border-radius: 4px; break-inside: avoid; break-after: avoid; }
  p:has(> img) + p > em:only-child { display: block; text-align: center; font-size: 9pt; color: #4b5563; }
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
    <h1 style="break-before:auto;padding-top:0">Manual de Uso</h1>
    <div class="sub">Sistema PEI — Planejamento Estratégico Integrado</div>
    <div class="rodape">Versão de {$data}</div>
  </section>
  {$sumario}
  {$corpo}
</body>
</html>
HTML;

$destino = $pasta.DIRECTORY_SEPARATOR.'MANUAL-DE-USO.pdf';

$imprimir = function (string $html) use ($chrome, $destino): void {
    $htmlTemporario = sys_get_temp_dir().DIRECTORY_SEPARATOR.'manual-pei-'.getmypid().'.html';
    file_put_contents($htmlTemporario, $html);
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
};

/**
 * Página (1 = capa) de cada âncora t<N>, lida do dicionário /Dests do PDF.
 *
 * @return array<string, int>
 */
$lerPaginas = function () use ($destino): array {
    $pdf = file_get_contents($destino);

    // Ordem das páginas: a árvore /Pages, percorrida a partir da raiz.
    preg_match('/\/Root (\d+) 0 R/', $pdf, $r);
    $objeto = function (int $n) use ($pdf): string {
        preg_match('/(?:^|\n)'.$n.' 0 obj\s*(.*?)endobj/s', $pdf, $m);

        return $m[1] ?? '';
    };
    preg_match('/\/Pages (\d+) 0 R/', $objeto((int) $r[1]), $p);
    $ordem = [];
    $percorrer = function (int $n) use (&$percorrer, &$ordem, $objeto): void {
        $o = $objeto($n);
        if (preg_match('/\/Kids\s*\[([^\]]*)\]/', $o, $k)) {
            preg_match_all('/(\d+) 0 R/', $k[1], $filhos);
            foreach ($filhos[1] as $f) {
                $percorrer((int) $f);
            }
        } else {
            $ordem[$n] = count($ordem) + 1;
        }
    };
    $percorrer((int) $p[1]);

    preg_match('/\/Dests (\d+) 0 R/', $pdf, $d);
    preg_match_all('/\/(t\d+)\s*\[(\d+) 0 R/', $objeto((int) $d[1]), $destinos, PREG_SET_ORDER);
    $paginas = [];
    foreach ($destinos as [, $id, $obj]) {
        $paginas[$id] = $ordem[(int) $obj] ?? 0;
    }

    return $paginas;
};

// Passagem 1 (páginas desconhecidas) e até três ajustes, até o sumário estabilizar.
$paginas = [];
for ($passagem = 1; $passagem <= 4; $passagem++) {
    $imprimir($montarHtml($montarSumario($paginas)));
    $lidas = $lerPaginas();
    if ($lidas === $paginas) {
        break;
    }
    $paginas = $lidas;
}

$semPagina = count($titulos) - count(array_filter($paginas));
if ($semPagina > 0) {
    fwrite(STDERR, "Aviso: {$semPagina} título(s) sem página no sumário.\n");
}

printf("Gerado: %s (%.1f MB, %d títulos no sumário, %d passagem(ns))\n",
    $destino, filesize($destino) / 1048576, count($titulos), $passagem);
