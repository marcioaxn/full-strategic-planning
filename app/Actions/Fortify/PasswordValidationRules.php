<?php

namespace App\Actions\Fortify;

trait PasswordValidationRules
{
    /**
     * Regras de validação de senha usadas em todo o sistema.
     *
     * Exige: mínimo de 8 caracteres, letra minúscula, letra maiúscula,
     * número e caractere especial. As mesmas regras são aplicadas na tela
     * de troca de senha (App\Livewire\Auth\TrocarSenha), mantendo coerência.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function passwordRules(): array
    {
        return [
            'required',
            'string',
            'confirmed',
            'min:8',
            'regex:/[a-z]/',          // ao menos uma letra minúscula
            'regex:/[A-Z]/',          // ao menos uma letra maiúscula
            'regex:/[0-9]/',          // ao menos um número
            'regex:/[^A-Za-z0-9]/',   // ao menos um caractere especial
        ];
    }

    /**
     * Mensagens em Português para as regras de senha.
     *
     * @return array<string, string>
     */
    protected function passwordMessages(): array
    {
        return [
            'password.required'  => 'A senha é obrigatória.',
            'password.min'       => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'password.regex'     => 'A senha deve conter letra maiúscula, letra minúscula, número e caractere especial.',
        ];
    }
}
