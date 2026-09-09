<?php

/*
 * A ordem do Tab na tela de login.
 *
 * O link "Esqueci minha senha" era renderizado dentro da linha do rótulo, ou
 * seja, ANTES do campo de senha no HTML. Como o Tab segue a ordem do documento,
 * quem digitava o e-mail e apertava Tab caía no link — e o Enter seguinte
 * abandonava o login em vez de enviá-lo.
 *
 * O teste olha a POSIÇÃO no HTML renderizado, não o CSS: é a ordem do documento
 * que o navegador usa para tabular, e é ela que precisa continuar certa se
 * alguém mexer no layout depois.
 */

test('do e-mail o Tab vai para a senha, não para "Esqueci minha senha"', function () {
    $html = $this->get(route('login'))->assertOk()->getContent();

    $email = strpos($html, 'id="email"');
    $senha = strpos($html, 'id="password"');
    $esqueci = strpos($html, 'Esqueci minha senha');

    expect($email)->not->toBeFalse()
        ->and($senha)->not->toBeFalse()
        ->and($esqueci)->not->toBeFalse();

    expect($senha)->toBeGreaterThan($email)
        ->and($esqueci)->toBeGreaterThan($senha);
});

test('nenhum tabindex positivo reordena o formulário de login', function () {
    // tabindex positivo não conserta ordem: tira o elemento da sequência natural
    // e obriga TODO o resto do formulário a ser numerado junto.
    $blade = file_get_contents(resource_path('views/auth/login.blade.php'));

    expect($blade)->not->toMatch('/tabindex\s*=\s*"[1-9]/');
});
