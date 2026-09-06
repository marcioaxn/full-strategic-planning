<?php

/*
 * O gestor pediu para verificar se a polaridade NEGATIVA (quanto menor, melhor)
 * é calculada certo — e as outras também.
 *
 * Três erros foram encontrados, e cada um está travado por um teste abaixo.
 * Este é o número que o CEO lê no farol: se ele mente, a plataforma inteira
 * perde a credibilidade.
 */

use App\Models\PerformanceIndicators\Indicador;
use App\Support\CalculoPolaridade;

// ------------------------------------------------------------- POSITIVA

test('positiva: realizado sobre previsto', function () {
    expect(CalculoPolaridade::atingimento(80, 100, 'Positiva'))->toBe(80.0)
        ->and(CalculoPolaridade::atingimento(120, 100, 'Positiva'))->toBe(120.0)
        ->and(CalculoPolaridade::atingimento(100, 100, 'Positiva'))->toBe(100.0);
});

// ------------------------------------------------------------- NEGATIVA

test('negativa: reduzir abaixo da meta passa de 100%', function () {
    // Meta: reduzir o tempo de espera para 10 dias. Realizado: 8 dias.
    expect(CalculoPolaridade::atingimento(8, 10, 'Negativa'))->toBe(125.0);
});

test('negativa: estourar a meta fica abaixo de 100%', function () {
    // Meta 10 dias, realizado 20 dias.
    expect(CalculoPolaridade::atingimento(20, 10, 'Negativa'))->toBe(50.0);
});

test('negativa com META ZERO e resultado zero vale 100%, não 0%', function () {
    // 🔴 O erro mais grave dos três.
    // "Zero acidentes", "zero fraudes", "zero reincidência" são metas legítimas
    // e ambiciosas. O código devolvia 0% para meta zero em QUALQUER polaridade:
    // quem alcançava zero acidentes recebia zero por cento de atingimento.
    // O número dizia fracasso total onde houve êxito total.
    expect(CalculoPolaridade::atingimento(0, 0, 'Negativa'))->toBe(100.0);
});

test('negativa com meta zero e resultado acima de zero não atinge', function () {
    expect(CalculoPolaridade::atingimento(3, 0, 'Negativa'))->toBe(0.0);
});

test('negativa: zerar um indicador que se queria reduzir é êxito pleno', function () {
    expect(CalculoPolaridade::atingimento(0, 10, 'Negativa'))->toBe(100.0);
});

// -------------------------------------------------------- ESTABILIDADE

test('estabilidade: no alvo é 100%', function () {
    expect(CalculoPolaridade::atingimento(100, 100, 'Estabilidade'))->toBe(100.0);
});

test('estabilidade: desviar PARA CIMA penaliza, não premia', function () {
    // 🔴 Antes usava a fórmula positiva: 150 sobre alvo 100 marcava 150%,
    // premiando exatamente o desvio que esta polaridade existe para evitar.
    expect(CalculoPolaridade::atingimento(150, 100, 'Estabilidade'))->toBe(50.0);
});

test('estabilidade: desviar para baixo penaliza igual', function () {
    expect(CalculoPolaridade::atingimento(50, 100, 'Estabilidade'))->toBe(50.0);
});

test('estabilidade: desvio maior que o alvo não fica negativo', function () {
    expect(CalculoPolaridade::atingimento(300, 100, 'Estabilidade'))->toBe(0.0);
});

// ------------------------------------------------------ NÃO APLICÁVEL

test('informativo não pontua e é reconhecido como informativo', function () {
    expect(CalculoPolaridade::atingimento(999, 100, 'Não Aplicável'))->toBe(0.0)
        ->and(CalculoPolaridade::ehInformativo('Não Aplicável'))->toBeTrue()
        ->and(CalculoPolaridade::ehInformativo('Positiva'))->toBeFalse();
});

// ------------------------------------------------------- NORMALIZAÇÃO

test('o rótulo LONGO é entendido igual à chave curta', function () {
    // 🔴 O formulário grava a chave curta, mas dado migrado do legado e
    // importação de CSV trazem o rótulo longo. O match comparava só com a
    // chave curta: o rótulo longo caía no default e um indicador de polaridade
    // NEGATIVA era calculado como POSITIVA, sem erro e sem aviso.
    expect(CalculoPolaridade::atingimento(8, 10, 'Negativa (Quanto menor, melhor)'))->toBe(125.0)
        ->and(CalculoPolaridade::atingimento(150, 100, 'Estabilidade (Quanto mais próximo do alvo, melhor)'))->toBe(50.0)
        ->and(CalculoPolaridade::ehInformativo('Não Aplicável (Informativo)'))->toBeTrue();
});

test('toda polaridade do sistema é entendida pelo cálculo', function () {
    // Se alguém acrescentar uma polaridade em Indicador::POLARIDADES, este
    // teste cobra o tratamento — em vez de deixá-la cair no default calada.
    foreach (Indicador::POLARIDADES as $chave => $rotulo) {
        expect(CalculoPolaridade::normalizar($chave))->toBe($chave, "chave [{$chave}]")
            ->and(CalculoPolaridade::normalizar($rotulo))->toBe($chave, "rótulo [{$rotulo}]");
    }
});

test('polaridade ausente é tratada como positiva', function () {
    expect(CalculoPolaridade::atingimento(80, 100, null))->toBe(80.0)
        ->and(CalculoPolaridade::atingimento(80, 100, ''))->toBe(80.0);
});

test('sem meta declarada, indicador positivo não inventa atingimento', function () {
    expect(CalculoPolaridade::atingimento(50, 0, 'Positiva'))->toBe(0.0);
});
