<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('modulo.acessar', 'organizacoes');
    }

    /**
     * 🔴 Devolvia true para qualquer pessoa logada: o detalhe de QUALQUER
     * unidade, com nome e e-mail dos usuários dela, abria para todo mundo.
     */
    public function view(User $user, Organization $organization): bool
    {
        return $user->isSuperAdmin()
            || ($user->podeAcessarOrganizacao($organization->cod_organizacao)
                && Gate::forUser($user)->allows('modulo.acessar', ['organizacoes', $organization->cod_organizacao]));
    }

    public function create(User $user): bool
    {
        // Apenas Super Admin pode criar organizações
        return $user->isSuperAdmin();
    }

    /** Administrador da unidade, ou de uma superior a ela. */
    public function update(User $user, Organization $organization): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return Gate::forUser($user)->allows('modulo.editar', ['organizacoes', $organization->cod_organizacao])
            && $user->ehAdministradorEm($organization->cod_organizacao);
    }

    public function delete(User $user, Organization $organization): bool
    {
        // Apenas Super Admin pode excluir
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Organization $organization): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Organization $organization): bool
    {
        return $user->isSuperAdmin();
    }
}
