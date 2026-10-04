<?php

namespace App\Support;

/**
 * Neutraliza texto do usuário para destinos que interpretam o texto, não só o
 * HTML: Markdown de e-mail e células de planilha.
 *
 * {{ }} do Blade escapa HTML, mas não impede que "[Clique](https://…)" vire
 * link no e-mail oficial, nem que "=HYPERLINK(…)" vire fórmula no Excel.
 */
class TextoSeguro
{
    /** Escapa a sintaxe Markdown: o texto aparece literal no e-mail. */
    public static function markdownLiteral(?string $texto): string
    {
        return (string) preg_replace('/([\\\\`*_{}\[\]()#+\-.!|<>~])/', '\\\\$1', (string) $texto);
    }

    /**
     * Valor de célula de planilha que o Excel/LibreOffice não executa.
     *
     * Texto que começa com = + - @ (ou tabulação/retorno) é lido como fórmula
     * (CSV/Formula Injection, OWASP). O apóstrofo inicial faz a célula ser
     * texto e não aparece na planilha.
     */
    public static function celula(mixed $valor): mixed
    {
        if (! is_string($valor) || $valor === '') {
            return $valor;
        }

        return in_array($valor[0], ['=', '+', '-', '@', "\t", "\r"], true) && ! is_numeric($valor)
            ? "'".$valor
            : $valor;
    }
}
