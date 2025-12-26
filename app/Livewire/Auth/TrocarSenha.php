<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class TrocarSenha extends Component
{
    public $senhaAtual;
    public $novaSenha;
    public $novaSenha_confirmation;

    protected function rules()
    {
        return [
            'senhaAtual' => ['required', 'current_password'],
            'novaSenha' => ['required', 'string', 'min:8', 'confirmed', 'different:senhaAtual'],
        ];
    }

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
        
        // Atualização direta e persistência forçada
        $user->password = Hash::make($this->novaSenha);
        $user->trocarsenha = 2; // Já trocou
        $user->save();

        // Limpeza de sessão e logout
        Auth::guard('web')->logout();
        Session::invalidate();
        Session::regenerateToken();

        // Flash message que aparecerá na tela de login
        session()->flash('status', 'Senha alterada com sucesso! Faça login com a nova senha.');
        
        return redirect()->route('login');
    }

    public function logout()
    {
        Auth::guard('web')->logout();
        Session::invalidate();
        Session::regenerateToken();
        
        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.auth.trocar-senha')
            ->layout('layouts.guest');
    }
}