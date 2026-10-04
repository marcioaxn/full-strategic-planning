<?php

/*
 * Autocadastro desligado (decisão do gestor, 04/10/2026): só quem tem
 * permissão cria contas, pela tela de Usuários. Ninguém se cadastra sozinho.
 */

use App\Models\User;
use Illuminate\Support\Facades\Route;

// Página inexistente, para visitante, volta à página inicial com aviso
// (bootstrap/app.php, tratamento de 404): é o que /register faz agora.

test('a tela de autocadastro não existe', function () {
    expect(Route::has('register'))->toBeFalse();

    $this->get('/register')->assertRedirect(route('welcome'));
});

test('ninguém cria conta enviando o formulário de cadastro direto', function () {
    $this->post('/register', [
        'name' => 'Pessoa de fora',
        'email' => 'fora@exemplo.test',
        'password' => 'Senha!Forte123',
        'password_confirmation' => 'Senha!Forte123',
    ])->assertRedirect(route('welcome'));

    expect(User::where('email', 'fora@exemplo.test')->exists())->toBeFalse();
    $this->assertGuest();
});

test('a tela de login não oferece "Criar conta"', function () {
    $this->get('/login')->assertOk()->assertDontSee('Criar conta');
});
