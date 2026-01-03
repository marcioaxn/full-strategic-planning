<?php

namespace App\Policies;

use App\Models\RiskManagement\Risco;
use App\Models\User;
use App\Models\PerfilAcesso;
use Illuminate\Auth\Access\Response;

class RiscoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Risco $risco): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->organizacoes->contains('cod_organizacao', $risco->cod_organizacao);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin Unidade pode criar riscos para sua unidade
        return $user->perfisAcesso()->where('cod_perfil', PerfilAcesso::ADMIN_UNIDADE)->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Risco $risco): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin Unidade daquela organização específica
        $isAdminUnidade = $user->perfisAcesso()
            ->wherePivot('cod_organizacao', $risco->cod_organizacao)
            ->where('cod_perfil', PerfilAcesso::ADMIN_UNIDADE)
            ->exists();

        if ($isAdminUnidade) {
            return true;
        }

        // O próprio responsável pelo monitoramento do risco
        return $risco->cod_responsavel_monitoramento === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Risco $risco): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Apenas Admin Unidade
        return $user->perfisAcesso()
            ->wherePivot('cod_organizacao', $risco->cod_organizacao)
            ->where('cod_perfil', PerfilAcesso::ADMIN_UNIDADE)
            ->exists();
    }
}