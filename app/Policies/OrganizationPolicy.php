<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class OrganizationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Todos usuários logados podem ver organizações
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Organization $organization): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Apenas Super Admin pode criar organizações
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Organization $organization): bool
    {
        if (! Gate::forUser($user)->allows('modulo.editar', 'organizacoes')) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // Verifica se é Admin da Unidade desta organização específica
        return $user->perfisAcesso()
            ->where('tab_perfil_acesso.cod_perfil', PerfilAcesso::ADMIN_UNIDADE)
            ->wherePivot('cod_organizacao', $organization->cod_organizacao)
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Organization $organization): bool
    {
        // Apenas Super Admin pode excluir
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Organization $organization): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Organization $organization): bool
    {
        return $user->isSuperAdmin();
    }
}
