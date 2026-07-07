<?php

namespace App\Policies;

use App\Models\PerfilAcesso;
use App\Models\RiskManagement\Risco;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class RiscoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('modulo.acessar', 'riscos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Risco $risco): bool
    {
        if (! Gate::forUser($user)->allows('modulo.acessar', 'riscos')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->podeAcessarOrganizacao($risco->cod_organizacao);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return Gate::forUser($user)->allows('modulo.criar', 'riscos');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Risco $risco): bool
    {
        if (! Gate::forUser($user)->allows('modulo.editar', 'riscos')) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin Unidade daquela organização específica
        $isAdminUnidade = $user->perfisAcesso()
            ->wherePivot('cod_organizacao', $risco->cod_organizacao)
            ->where('tab_perfil_acesso.cod_perfil', PerfilAcesso::ADMIN_UNIDADE)
            ->exists();

        if ($isAdminUnidade) {
            return true;
        }

        // ABAC — o próprio responsável pelo monitoramento do risco
        return $risco->cod_responsavel_monitoramento === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Risco $risco): bool
    {
        if (! Gate::forUser($user)->allows('modulo.excluir', 'riscos')) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // Apenas Admin Unidade
        return $user->perfisAcesso()
            ->wherePivot('cod_organizacao', $risco->cod_organizacao)
            ->where('tab_perfil_acesso.cod_perfil', PerfilAcesso::ADMIN_UNIDADE)
            ->exists();
    }
}
