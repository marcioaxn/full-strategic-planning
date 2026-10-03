<?php

namespace App\Support;

/**
 * Escolhe a cor de texto legível sobre um fundo de cor definida pelo usuário
 * (ex.: a cor de um Grau de Satisfação). Texto branco sobre amarelo dava
 * 1,6:1 — a WCAG pede 4,5:1.
 */
class CorLegivel
{
    /** Luminância relativa (WCAG 2.x) de uma cor #rrggbb ou #rgb. */
    public static function luminancia(string $hex): float
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (! preg_match('/^[0-9a-f]{6}$/i', $hex)) {
            return 0.0;
        }

        $canal = function (string $par): float {
            $v = hexdec($par) / 255;

            return $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
        };

        return 0.2126 * $canal(substr($hex, 0, 2))
            + 0.7152 * $canal(substr($hex, 2, 2))
            + 0.0722 * $canal(substr($hex, 4, 2));
    }

    /** "#212529" ou "#ffffff": o que tiver mais contraste com o fundo. */
    public static function textoSobre(string $fundo): string
    {
        $l = self::luminancia($fundo);
        $contrasteBranco = 1.05 / ($l + 0.05);
        $contrasteEscuro = ($l + 0.05) / (self::luminancia('#212529') + 0.05);

        return $contrasteEscuro >= $contrasteBranco ? '#212529' : '#ffffff';
    }

    /**
     * A mesma matiz, escurecida até 4,5:1 sobre branco — para a cor de farol
     * usada como TEXTO no PDF (o amarelo #ffc107 dava 1,6:1 na folha impressa).
     * Cor que já tem contraste volta inalterada; cor inválida vira grafite.
     */
    public static function paraTextoSobreBranco(string $cor): string
    {
        $hex = ltrim(trim($cor), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (! preg_match('/^[0-9a-f]{6}$/i', $hex)) {
            return '#2C2E35';
        }

        $rgb = array_map(fn (string $par): int => (int) hexdec($par), str_split($hex, 2));
        $atual = '#'.strtolower($hex);

        for ($fator = 1.0; $fator > 0; $fator -= 0.04) {
            $atual = sprintf('#%02x%02x%02x', ...array_map(fn (int $v): int => (int) round($v * $fator), $rgb));
            if (1.05 / (self::luminancia($atual) + 0.05) >= 4.5) {
                return $atual;
            }
        }

        return '#2C2E35';
    }
}
