<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

class UserPolicy
{
    /**
     * O diretório de usuários (nome, e-mail, vínculos).
     *
     * 🔴 Bastava ter qualquer perfil — e a tela nem chamava esta checagem:
     * qualquer conta logada, inclusive a criada pelo autocadastro, lia o
     * diretório inteiro. Agora é do Administrador da unidade (MATRIZ
     * "usuarios"), e a lista se restringe ao escopo dele.
     */
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('modulo.acessar', 'usuarios');
    }

    /** O próprio usuário; ou quem administra uma unidade a que ele pertence. */
    public function view(User $user, User $model): bool
    {
        if ($user->isSuperAdmin() || $user->id === $model->id) {
            return true;
        }

        if (! Gate::forUser($user)->allows('modulo.acessar', 'usuarios')) {
            return false;
        }

        $dele = $model->organizacoes->pluck('cod_organizacao')
            ->merge($model->perfisAcesso->pluck('pivot.cod_organizacao'))
            ->filter()
            ->unique();

        return $dele->contains(fn ($org) => $user->ehAdministradorEm($org));
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isSuperAdmin() && $user->id !== $model->id; // Não pode se auto-excluir
    }
}
