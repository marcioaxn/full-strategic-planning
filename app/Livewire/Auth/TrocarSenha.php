<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class TrocarSenha extends Component
{
    public $senhaAtual;
    public $novaSenha;
    public $novaSenha_confirmation;

    protected $rules = [
        'senhaAtual' => 'required|current_password',
        'novaSenha' => 'required|min:8|confirmed|different:senhaAtual',
    ];

    protected $messages = [
        'senhaAtual.required' => 'A senha atual é obrigatória.',
        'senhaAtual.current_password' => 'A senha atual está incorreta.',
        'novaSenha.required' => 'A nova senha é obrigatória.',
        'novaSenha.min' => 'A nova senha deve ter no mínimo 8 caracteres.',
        'novaSenha.confirmed' => 'A confirmação da nova senha não confere.',
        'novaSenha.different' => 'A nova senha deve ser diferente da senha atual.',
    ];

    public function trocarSenha()
    {
        $this->validate();

        $user = Auth::user();

        $user->forceFill([
            'password' => Hash::make($this->novaSenha),
            'trocarsenha' => 2, // Marca como trocada (integer 2)
        ])->save();

        session()->flash('status', 'Senha alterada com sucesso!');
        
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.auth.trocar-senha')
            ->layout('layouts.guest');
    }
}
