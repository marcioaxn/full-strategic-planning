<?php

namespace App\Services\Reports;

use Barryvdh\DomPDF\PDF as PdfWrapper;

/**
 * O acabamento de TODOS os relatórios em PDF: cabeçalho, rodapé e capa.
 *
 * 🔴 POR QUE EXISTE UM RENDERIZADOR SÓ
 *
 * O gestor abriu o pedido dizendo que "não há padronização no layout dos
 * relatórios". Não havia mesmo: cada relatório trazia o próprio cabeçalho em
 * Blade, a própria margem e o próprio rodapé. Padronização mantida por onze
 * arquivos que se parecem dura até alguém mexer em um deles.
 *
 * Aqui o cabeçalho e o rodapé são desenhados por UMA classe, para os onze. Não
 * há como um relatório divergir do outro sem que todos divirjam juntos — e a
 * régua de cima e a de baixo saem da MESMA constante, que é o que torna a
 * simetria pedida impossível de quebrar pela metade.
 *
 * 🔴 POR QUE NO CANVAS, E NÃO EM BLADE
 *
 * Três coisas o DomPDF não faz por CSS:
 *
 *  1. CAPA SANGRADA. Elemento nenhum ultrapassa a margem de `@page`, e
 *     `@page :first` não é implementado. Só o canvas alcança a folha inteira.
 *
 *  2. CAPA SEM CABEÇALHO. Todo elemento `position: fixed` é desenhado também
 *     na página 1. Pintar por cima esconde, mas o texto continua na camada de
 *     texto: quem selecionasse a capa copiava "PÁGINA 1" de uma página que não
 *     mostra nada disso.
 *
 *  3. CABEÇALHO QUE MUDA POR SEÇÃO. Um elemento fixo é igual em todas as
 *     páginas. O Relatório de Gestão traz o capítulo corrente à direita, e é
 *     essa informação que orienta quem folheia um documento longo.
 *
 * A ORIENTAÇÃO É PARÂMETRO. Nem todo relatório é paisagem: uma lista de
 * objetivos ou um texto executivo se lê melhor em retrato. Quem escolhe é
 * quem gera, e a geometria abaixo se ajusta sozinha.
 */
class AcabamentoPdf
{
    /** A4 em pontos, retrato. Paisagem troca os dois. */
    private const A4_MENOR = 595.28;

    private const A4_MAIOR = 841.89;

    private const VERDE = [0.24, 0.61, 0.20];

    private const BRANCO = [1.0, 1.0, 1.0];

    private const AZUL = [0.208, 0.314, 0.627];

    private const CINZA = [0.478, 0.482, 0.502];

    private const FUNDO_CAPA = [0.106, 0.227, 0.184];

    /** Margem lateral de @page (52px = 39pt). O texto vive dentro dela. */
    private const MARGEM = 39.0;

    /**
     * Distância da régua verde até a borda — a MESMA em cima e embaixo.
     *
     * 🔴 É uma constante só, usada nas duas pontas, de propósito. O gestor
     * pediu "sempre optar pela simetria entre linha superior e inferior", e
     * simetria mantida por dois números iguais escritos em lugares diferentes
     * dura até alguém mexer em um deles. Aqui não há como quebrar pela metade.
     */
    private const REGUA = 46.5;

    private readonly float $largura;

    private readonly float $altura;

    public function __construct(private readonly string $orientacao = 'portrait')
    {
        $paisagem = $this->orientacao === 'landscape';

        $this->largura = $paisagem ? self::A4_MAIOR : self::A4_MENOR;
        $this->altura = $paisagem ? self::A4_MENOR : self::A4_MAIOR;
    }

    /**
     * @param  array{
     *     esquerda?: string, centro?: string, direita?: string|null,
     *     site?: string, emitido_em?: string,
     *     capa?: array|null, capitulos?: array|null
     * }  $contexto
     */
    public function aplicar(PdfWrapper $pdf, array $contexto): void
    {
        $dompdf = $pdf->getDomPDF();

        $porSecao = $this->rastrearSecoes($dompdf, $contexto['capitulos'] ?? null);

        $pdf->render();

        $canvas = $dompdf->getCanvas();
        $metricas = $dompdf->getFontMetrics();
        $faixas = $this->faixas($porSecao());

        $temCapa = ! empty($contexto['capa']);

        $canvas->page_script(function (int $pagina, int $total, $canvas) use ($contexto, $faixas, $metricas, $temCapa) {
            if ($temCapa && $pagina === 1) {
                $this->capa($canvas, $metricas, $contexto['capa']);

                return;
            }

            $this->cabecalho($canvas, $metricas, $contexto, $faixas, $pagina);
            $this->rodape($canvas, $metricas, $contexto, $pagina);
        });
    }

    // ------------------------------------------------------------------ capa

    /**
     * Pinta a página 1 inteira.
     *
     * O preenchimento vem ANTES de tudo e cobre a folha de borda a borda —
     * é ele que apaga qualquer coisa que o DomPDF já tenha desenhado ali.
     */
    private function capa($canvas, $metricas, array $capa): void
    {
        $canvas->filled_rectangle(0, 0, $this->largura, $this->altura, self::FUNDO_CAPA);

        if (! empty($capa['imagem'])) {
            $canvas->image($capa['imagem'], 0, 0, $this->largura, $this->altura);

            // Véu escuro no topo, para o título branco ter contraste sobre
            // qualquer foto. Sem ele, uma capa clara devolve texto ilegível —
            // e ninguém revisa o contraste de uma foto que o cliente subiu.
            $canvas->set_opacity(0.55);
            $canvas->filled_rectangle(0, 0, $this->largura, $this->altura * 0.38, [0.08, 0.14, 0.11]);
            $canvas->set_opacity(1.0);
        }

        $negrito = $metricas->getFont('DejaVu Sans', 'bold');
        $normal = $metricas->getFont('DejaVu Sans', 'normal');

        // Escada de três linhas, como no modelo: cada uma recuada da anterior.
        $recuo = $this->largura * 0.10;

        $canvas->text($recuo, 22, $capa['orgao'] ?? '', $negrito, 22, self::BRANCO);
        $canvas->text($recuo + 32, 60, $capa['titulo'] ?? '', $normal, 21, self::BRANCO);

        if (! empty($capa['ano'])) {
            $canvas->text($recuo + 116, 98, (string) $capa['ano'], $negrito, 21, self::BRANCO);
        }

        if (! empty($capa['credito_imagem'])) {
            $largura = $metricas->getTextWidth($capa['credito_imagem'], $normal, 7);
            $canvas->text($this->largura - $largura - 16, 12, $capa['credito_imagem'], $normal, 7, self::BRANCO);
        }

        foreach (array_values(array_filter($capa['rodape'] ?? [])) as $i => $linha) {
            $canvas->text($recuo, $this->altura - 46 + $i * 14, $linha, $normal, 9.5, self::BRANCO);
        }
    }

    // --------------------------------------------- cabeçalho e rodapé

    /**
     * Três colunas e a régua verde.
     *
     * Cada texto vive no SEU terço e é cortado para caber nele. A primeira
     * versão deixava a largura livre: o título do capítulo 4 tem 74 caracteres
     * e era impresso por cima do título central, letra sobre letra.
     */
    private function cabecalho($canvas, $metricas, array $contexto, array $faixas, int $pagina): void
    {
        $fonte = $metricas->getFont('DejaVu Sans', 'normal');
        $tamanho = 8.5;
        $linha = 13.5;

        $esquerda = self::MARGEM;
        $direita = $this->largura - self::MARGEM;
        $util = $direita - $esquerda;
        $terco = $util * 0.33;

        if (! empty($contexto['esquerda'])) {
            $canvas->text(
                $esquerda, $linha,
                $this->cortar($metricas, $contexto['esquerda'], $fonte, $tamanho, $terco),
                $fonte, $tamanho, self::VERDE
            );
        }

        if (! empty($contexto['centro'])) {
            $centro = $this->cortar($metricas, $contexto['centro'], $fonte, $tamanho, $util * 0.34);
            $larguraCentro = $metricas->getTextWidth($centro, $fonte, $tamanho);
            $canvas->text($esquerda + $util * 0.5 - $larguraCentro / 2, $linha, $centro, $fonte, $tamanho, self::VERDE);
        }

        // À direita: a seção corrente, quando o documento tem seções; senão,
        // o texto fixo que o relatório escolheu.
        $direitaTexto = $this->tituloDaSecao($faixas, $pagina) ?? ($contexto['direita'] ?? null);

        if ($direitaTexto) {
            $direitaTexto = $this->cortar($metricas, $direitaTexto, $fonte, $tamanho, $terco);
            $largura = $metricas->getTextWidth($direitaTexto, $fonte, $tamanho);
            $canvas->text($direita - $largura, $linha, $direitaTexto, $fonte, $tamanho, self::VERDE);
        }

        $canvas->line($esquerda, self::REGUA, $direita, self::REGUA, self::VERDE, 1.1);
    }

    /** Duas réguas verdes ladeando o número da página, com a URL e a data. */
    private function rodape($canvas, $metricas, array $contexto, int $pagina): void
    {
        $fonte = $metricas->getFont('DejaVu Sans', 'normal');

        $esquerda = self::MARGEM;
        $direita = $this->largura - self::MARGEM;
        $util = $direita - $esquerda;
        $y = $this->altura - self::REGUA;

        $rotulo = 'PÁGINA '.$pagina;
        $larguraRotulo = $metricas->getTextWidth($rotulo, $fonte, 9);
        $meio = $esquerda + $util * 0.5;

        $canvas->line($esquerda, $y, $meio - $larguraRotulo / 2 - 12, $y, self::VERDE, 1.1);
        $canvas->line($meio + $larguraRotulo / 2 + 12, $y, $direita, $y, self::VERDE, 1.1);

        $canvas->text($meio - $larguraRotulo / 2, $y - 5, $rotulo, $fonte, 9, self::VERDE);

        if (! empty($contexto['site'])) {
            $site = $this->cortar($metricas, $contexto['site'], $fonte, 7.5, $util * 0.4);
            $canvas->text($esquerda, $y + 11, $site, $fonte, 7.5, self::AZUL);
        }

        if (! empty($contexto['emitido_em'])) {
            $data = 'Emitido em '.$contexto['emitido_em'];
            $larguraData = $metricas->getTextWidth($data, $fonte, 7.5);
            $canvas->text($direita - $larguraData, $y + 11, $data, $fonte, 7.5, self::CINZA);
        }
    }

    private function tituloDaSecao(array $faixas, int $pagina): ?string
    {
        foreach ($faixas as $faixa) {
            if ($pagina >= $faixa['de'] && $pagina <= $faixa['ate']) {
                return $faixa['titulo'];
            }
        }

        return null;
    }

    /** Encurta com reticências até caber na largura dada. */
    private function cortar($metricas, string $texto, $fonte, float $tamanho, float $limite): string
    {
        if ($metricas->getTextWidth($texto, $fonte, $tamanho) <= $limite) {
            return $texto;
        }

        $letras = preg_split('//u', $texto, -1, PREG_SPLIT_NO_EMPTY);

        while ($letras !== [] && $metricas->getTextWidth(implode('', $letras).'…', $fonte, $tamanho) > $limite) {
            array_pop($letras);
        }

        return rtrim(implode('', $letras), " \t-—·").'…';
    }

    // ------------------------------------------------- seções por página

    /**
     * Descobre em que página cada seção começa, DURANTE a renderização.
     *
     * O DomPDF só sabe onde cada bloco caiu depois de paginar, e o
     * `page_script` roda depois disso, sem acesso ao conteúdo. O callback
     * `begin_frame` entrega a página corrente enquanto o documento é montado:
     * quando o título de uma seção entra na página, anota-se qual é.
     *
     * 🔴 A primeira versão descobria isto renderizando o documento SETE VEZES,
     * uma por pedaço. Além de lento, estourava o limite de memória do PHP
     * quando várias gerações rodavam no mesmo processo.
     *
     * @param  array<string, string>|null  $rotulos  valor do atributo => rótulo
     * @return callable(): array<int, string>
     */
    private function rastrearSecoes($dompdf, ?array $rotulos): callable
    {
        $inicios = [];

        if (! $rotulos) {
            return fn () => [];
        }

        // 🔴 `fn () => $inicios` NÃO serve aqui: arrow function captura por
        // VALOR, no momento em que é criada — devolveria sempre o array vazio,
        // e o cabeçalho sairia sem o nome da seção. A leitura precisa ser por
        // referência, porque o array só é preenchido durante a renderização.
        $leitor = function () use (&$inicios) {
            return $inicios;
        };

        $dompdf->setCallbacks([[
            'event' => 'begin_frame',
            'f' => function ($quadro, $canvas) use ($rotulos, &$inicios) {
                $no = $quadro->get_node();

                if (! $no instanceof \DOMElement || ! $no->hasAttribute('data-rpt-secao')) {
                    return;
                }

                $chave = $no->getAttribute('data-rpt-secao');
                $pagina = $canvas->get_page_number();

                // Um quadro pode entrar mais de uma vez; vale a primeira.
                if (isset($rotulos[$chave]) && ! isset($inicios[$pagina])) {
                    $inicios[$pagina] = $rotulos[$chave];
                }
            },
        ]]);

        return $leitor;
    }

    /**
     * Converte "seção X começa na página P" em faixas de páginas.
     *
     * @param  array<int, string>  $inicios  página => rótulo
     * @return array<int, array{de:int, ate:int, titulo:string}>
     */
    private function faixas(array $inicios): array
    {
        ksort($inicios);

        $paginas = array_keys($inicios);
        $faixas = [];

        foreach ($paginas as $i => $pagina) {
            $faixas[] = [
                'de' => $pagina,
                // A última seção vai até o fim do documento.
                'ate' => isset($paginas[$i + 1]) ? $paginas[$i + 1] - 1 : PHP_INT_MAX,
                'titulo' => $inicios[$pagina],
            ];
        }

        return $faixas;
    }
}
