<?php

/*
 * O caso real que originou isto: o CEO da Presidência selecionou
 * "Monetário (R$)" e foi digitar R$ 20.000.000.000,00. O campo era
 * <input type="number">, que em navegador pt-BR recusa ponto de milhar e
 * vírgula decimal — sem mensagem nenhuma. O usuário digitava e nada acontecia.
 *
 * A leitura do valor é o ponto perigoso: um erro aqui não quebra a tela,
 * grava o número ERRADO. Vinte bilhões vira vinte. Por isso a bateria de
 * casos abaixo é sobre o VALOR RESULTANTE, não sobre a máscara.
 */

use App\Models\PerformanceIndicators\Indicador;
use App\Support\UnidadeMedida;

test('lê o valor que travou o CEO: vinte bilhões em reais', function () {
    expect(UnidadeMedida::paraFloat('20.000.000.000,00'))->toBe(20000000000.0)
        ->and(UnidadeMedida::paraFloat('R$ 20.000.000.000,00'))->toBe(20000000000.0);
});

test('lê o formato brasileiro em qualquer escala', function () {
    expect(UnidadeMedida::paraFloat('1.250,75'))->toBe(1250.75)
        ->and(UnidadeMedida::paraFloat('0,50'))->toBe(0.5)
        ->and(UnidadeMedida::paraFloat('1.300.000.000.000,00'))->toBe(1300000000000.0);
});

test('lê também o formato neutro, que vem de CSV e de teclado numérico', function () {
    expect(UnidadeMedida::paraFloat('1234.56'))->toBe(1234.56)
        ->and(UnidadeMedida::paraFloat(1234.56))->toBe(1234.56)
        ->and(UnidadeMedida::paraFloat(20000000000))->toBe(20000000000.0);
});

test('não confunde milhar com decimal', function () {
    // "1.250" em pt-BR é mil duzentos e cinquenta, não um vírgula vinte e cinco.
    expect(UnidadeMedida::paraFloat('1.250'))->toBe(1250.0)
        ->and(UnidadeMedida::paraFloat('1.250.000'))->toBe(1250000.0);
});

test('devolve null para vazio, em vez de zero', function () {
    // Zero e "não informado" são coisas diferentes: zero entra na média.
    expect(UnidadeMedida::paraFloat(''))->toBeNull()
        ->and(UnidadeMedida::paraFloat(null))->toBeNull()
        ->and(UnidadeMedida::paraFloat('R$'))->toBeNull();
});

test('aceita valor negativo', function () {
    expect(UnidadeMedida::paraFloat('-1.250,75'))->toBe(-1250.75);
});

test('formata cada unidade do jeito que ela se lê', function () {
    expect(UnidadeMedida::formatar(20000000000, 'Monetário (R$)'))->toBe('R$ 20.000.000.000,00')
        ->and(UnidadeMedida::formatar(87.5, 'Percentual (%)'))->toBe('87,50 %')
        ->and(UnidadeMedida::formatar(0.875, 'Índice (0-1)'))->toBe('0,8750')
        ->and(UnidadeMedida::formatar(1250, 'Quantidade (un)'))->toBe('1.250 un');
});

test('contagem não admite fração', function () {
    // "3,75 ocorrências" não existe.
    foreach (['Quantidade (un)', 'Dias', 'Nº de Ocorrências'] as $unidade) {
        expect(UnidadeMedida::regra($unidade)['casas'])->toBe(0)
            ->and(UnidadeMedida::regra($unidade)['inteiro'])->toBeTrue();
    }
});

test('índice e taxa têm casas suficientes para não arredondar o resultado', function () {
    // Com 2 casas, 0,8750 virava 0,88 — e o farol mudava de faixa por causa
    // do arredondamento da EXIBIÇÃO.
    expect(UnidadeMedida::regra('Índice (0-1)')['casas'])->toBe(4)
        ->and(UnidadeMedida::regra('Taxa')['casas'])->toBe(4)
        ->and(UnidadeMedida::regra('Proporção')['casas'])->toBe(4);
});

test('toda unidade cadastrada no sistema tem regra declarada', function () {
    // Unidade sem regra cai no padrão silenciosamente. Se alguém acrescentar
    // uma nova em Indicador::UNIDADES_MEDIDA, este teste cobra a regra junto.
    $semRegra = [];

    foreach (Indicador::UNIDADES_MEDIDA as $unidade) {
        $reflexao = new ReflectionClass(UnidadeMedida::class);
        $regras = $reflexao->getConstant('REGRAS');

        if (! array_key_exists($unidade, $regras)) {
            $semRegra[] = $unidade;
        }
    }

    expect($semRegra)->toBe([]);
});
