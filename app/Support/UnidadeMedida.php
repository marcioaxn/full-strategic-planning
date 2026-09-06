<?php

namespace App\Support;

/**
 * Como cada unidade de medida se escreve e se lê.
 *
 * 🔴 O CASO QUE ORIGINOU ISTO
 * O CEO da Presidência selecionou "Monetário (R$)" e foi digitar
 * R$ 20.000.000.000,00. O campo era `<input type="number">`, que só aceita o
 * formato neutro (20000000000.00): em navegador pt-BR, ponto de milhar e
 * vírgula decimal são simplesmente recusados, sem mensagem nenhuma. O usuário
 * digita e nada acontece.
 *
 * Além disso, todo indicador usava o mesmo campo com `step="0.01"` — dinheiro,
 * percentual, índice e quantidade tratados igual. "Nº de Ocorrências" aceitava
 * 3,75 ocorrências; "Índice (0-1)" não tinha casas suficientes.
 *
 * Aqui ficam a regra de exibição e a de leitura de cada unidade, num lugar só.
 */
class UnidadeMedida
{
    /**
     * casas   — casas decimais que a unidade admite
     * prefixo — símbolo antes do número (dinheiro)
     * sufixo  — símbolo depois do número (percentual, medidas)
     * inteiro — true quando fração não faz sentido (contagem)
     */
    private const REGRAS = [
        'Percentual (%)' => ['casas' => 2, 'prefixo' => '', 'sufixo' => '%', 'inteiro' => false],
        'Monetário (R$)' => ['casas' => 2, 'prefixo' => 'R$', 'sufixo' => '', 'inteiro' => false],
        'Índice (0-1)' => ['casas' => 4, 'prefixo' => '', 'sufixo' => '', 'inteiro' => false],
        'Quantidade (un)' => ['casas' => 0, 'prefixo' => '', 'sufixo' => 'un', 'inteiro' => true],
        'Horas (h)' => ['casas' => 2, 'prefixo' => '', 'sufixo' => 'h', 'inteiro' => false],
        'Dias' => ['casas' => 0, 'prefixo' => '', 'sufixo' => 'dias', 'inteiro' => true],
        'Proporção' => ['casas' => 4, 'prefixo' => '', 'sufixo' => '', 'inteiro' => false],
        'Taxa' => ['casas' => 4, 'prefixo' => '', 'sufixo' => '', 'inteiro' => false],
        'Nº de Ocorrências' => ['casas' => 0, 'prefixo' => '', 'sufixo' => '', 'inteiro' => true],
        'Kilômetros (km)' => ['casas' => 2, 'prefixo' => '', 'sufixo' => 'km', 'inteiro' => false],
        'Metros (m)' => ['casas' => 2, 'prefixo' => '', 'sufixo' => 'm', 'inteiro' => false],
        'Toneladas (t)' => ['casas' => 3, 'prefixo' => '', 'sufixo' => 't', 'inteiro' => false],
        'Pontos' => ['casas' => 2, 'prefixo' => '', 'sufixo' => 'pts', 'inteiro' => false],
    ];

    private const PADRAO = ['casas' => 2, 'prefixo' => '', 'sufixo' => '', 'inteiro' => false];

    /** @return array{casas:int, prefixo:string, sufixo:string, inteiro:bool} */
    public static function regra(?string $unidade): array
    {
        return self::REGRAS[$unidade ?? ''] ?? self::PADRAO;
    }

    /** Número no formato que o brasileiro lê: 20.000.000.000,00 */
    public static function formatar(int|float|string|null $valor, ?string $unidade, bool $comSimbolo = true): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }

        $regra = self::regra($unidade);
        $numero = number_format((float) $valor, $regra['casas'], ',', '.');

        if (! $comSimbolo) {
            return $numero;
        }

        $texto = $regra['prefixo'] !== '' ? $regra['prefixo'].' '.$numero : $numero;

        return $regra['sufixo'] !== '' ? $texto.' '.$regra['sufixo'] : $texto;
    }

    /**
     * Lê o que o usuário digitou, em qualquer forma razoável, e devolve float.
     *
     * Aceita "20.000.000.000,00", "R$ 20.000.000.000,00", "20000000000,00" e
     * também o formato neutro "20000000000.00" — porque o valor pode vir de
     * importação de CSV ou de um teclado numérico que produz ponto.
     *
     * A regra de desempate: se há vírgula, ela é o separador decimal e o ponto
     * é milhar. Sem vírgula, um ponto só, com 1 ou 2 casas depois, é decimal;
     * qualquer outro ponto é milhar.
     */
    public static function paraFloat(mixed $entrada): ?float
    {
        if ($entrada === null || $entrada === '') {
            return null;
        }

        if (is_int($entrada) || is_float($entrada)) {
            return (float) $entrada;
        }

        $texto = trim((string) $entrada);

        // Fora símbolo de moeda, espaço fino e qualquer coisa que não seja
        // dígito, sinal, ponto ou vírgula.
        $texto = preg_replace('/[^\d,.\-]/u', '', $texto);

        if ($texto === '' || $texto === '-') {
            return null;
        }

        $temVirgula = str_contains($texto, ',');
        $temPonto = str_contains($texto, '.');

        if ($temVirgula) {
            // Vírgula manda: ponto é milhar.
            $texto = str_replace('.', '', $texto);
            $texto = str_replace(',', '.', $texto);
        } elseif ($temPonto) {
            $partes = explode('.', $texto);
            $ultima = end($partes);

            // "1.234.567" -> milhar. "1234.56" -> decimal.
            if (count($partes) > 2 || strlen($ultima) === 3) {
                $texto = str_replace('.', '', $texto);
            }
        }

        return is_numeric($texto) ? (float) $texto : null;
    }

    /** Texto de apoio abaixo do campo, para o usuário saber o que digitar. */
    public static function ajuda(?string $unidade): string
    {
        $regra = self::regra($unidade);

        if ($regra['inteiro']) {
            return 'Número inteiro. Ex.: 1.250';
        }

        return match ($unidade) {
            'Monetário (R$)' => 'Em reais, com centavos. Ex.: 20.000.000.000,00',
            'Percentual (%)' => 'Percentual com até duas casas. Ex.: 87,50',
            'Índice (0-1)' => 'Índice entre 0 e 1, com até quatro casas. Ex.: 0,8750',
            default => 'Use vírgula para as casas decimais. Ex.: 1.250,75',
        };
    }
}
