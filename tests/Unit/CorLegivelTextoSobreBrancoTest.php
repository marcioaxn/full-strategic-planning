<?php

use App\Support\CorLegivel;

/**
 * Cor de farol usada como texto no PDF: precisa de 4,5:1 sobre o papel branco
 * sem perder a matiz (o leitor ainda distingue amarelo de vermelho).
 */
function contrasteSobreBranco(string $cor): float
{
    return 1.05 / (CorLegivel::luminancia($cor) + 0.05);
}

test('cor fraca é escurecida até 4,5:1 sobre branco', function (string $cor) {
    $resultado = CorLegivel::paraTextoSobreBranco($cor);

    expect(contrasteSobreBranco($resultado))->toBeGreaterThanOrEqual(4.5)
        ->and($resultado)->not->toBe(strtolower($cor));
})->with(['#ffc107', '#28a745', '#fd7e14', '#17a2b8', '#ffff00']);

test('cor que já tem contraste volta inalterada', function () {
    expect(CorLegivel::paraTextoSobreBranco('#b02a37'))->toBe('#b02a37')
        ->and(CorLegivel::paraTextoSobreBranco('#2C2E35'))->toBe('#2c2e35');
});

test('mantém a matiz: o canal dominante continua dominante', function () {
    $amarelo = CorLegivel::paraTextoSobreBranco('#ffc107');
    [$r, $g, $b] = array_map('hexdec', str_split(ltrim($amarelo, '#'), 2));

    expect($r)->toBeGreaterThan($g)->and($g)->toBeGreaterThan($b);
});

test('cor inválida vira grafite', function () {
    expect(CorLegivel::paraTextoSobreBranco('vermelho'))->toBe('#2C2E35');
});
