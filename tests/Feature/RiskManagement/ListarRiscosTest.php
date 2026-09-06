<?php

/*
 * A UI do formulário de risco.
 *
 * Testes de POSIÇÃO, não de estilo: o que se guarda aqui é a ORDEM em que o
 * cliente encontra as seções — foi disso que ele reclamou.
 */

test('o Vínculo Estratégico aparece ANTES do Monitoramento no formulário', function () {
    // 🔴 Pedido do gestor: o vínculo estava enterrado no fim da coluna, depois
    // de Monitoramento e de Resposta ao Risco, dentro de um bloco com rolagem
    // própria — o cliente cadastrava o risco sem nunca ver essa parte.
    //
    // Risco sem objetivo vinculado é risco órfão: não entra no mapa, não entra
    // no Relatório de Gestão, e o módulo perde a razão de existir dentro do PEI.
    $blade = file_get_contents(resource_path('views/livewire/risco/listar-riscos.blade.php'));

    $vinculo = strpos($blade, 'Vínculo Estratégico');
    $monitoramento = strpos($blade, '>Monitoramento<');

    expect($vinculo)->not->toBeFalse('A seção "Vínculo Estratégico" sumiu do formulário.');
    expect($monitoramento)->not->toBeFalse('A seção "Monitoramento" sumiu do formulário.');
    expect($vinculo)->toBeLessThan(
        $monitoramento,
        'O Vínculo Estratégico voltou para depois do Monitoramento.'
    );
});
