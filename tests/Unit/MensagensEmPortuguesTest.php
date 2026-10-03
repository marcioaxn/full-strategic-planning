<?php

/*
 * O projeto não tinha lang/pt_BR: com APP_LOCALE=pt_BR (o que o .env.example
 * manda e o que a implantação usa) toda mensagem de validação saía como chave
 * crua — "validation.required" — na tela do usuário.
 */

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

// Sem banco: só precisa da aplicação para o tradutor.
uses(TestCase::class);

test('a implantação usa o idioma pt_BR', function () {
    $exemplo = file_get_contents(base_path('.env.example'));

    expect($exemplo)->toContain('APP_LOCALE=pt_BR');
});

test('mensagem de validação sai em português e com o nome legível do campo', function () {
    app()->setLocale('pt_BR');

    $erros = Validator::make([], ['form.organizacoes_ids' => 'required'])->errors();

    expect($erros->first('form.organizacoes_ids'))
        ->toBe('O campo unidades organizacionais é obrigatório.');
});

test('textos do Jetstream e do menu saem em português', function () {
    app()->setLocale('pt_BR');

    expect(__('View profile'))->toBe('Ver perfil')
        ->and(__('Two Factor Authentication'))->toBe('Autenticação em dois fatores')
        ->and(__('A operacao nao foi concluida'))->toBe('A operação não foi concluída');
});

test('nenhuma regra de validação cai em chave crua no pt_BR', function () {
    app()->setLocale('pt_BR');

    $regras = ['required', 'email', 'integer', 'date', 'numeric', 'string', 'array', 'uuid', 'boolean'];

    foreach ($regras as $regra) {
        $mensagem = Validator::make(['campo' => ['x']], ['campo' => $regra])->errors()->first('campo')
            ?: Validator::make([], ['campo' => 'required'])->errors()->first('campo');

        expect($mensagem)->not->toStartWith('validation.');
    }
});
