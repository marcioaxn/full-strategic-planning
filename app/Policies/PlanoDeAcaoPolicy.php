<?php

namespace App\Policies;

use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\PerfilAcesso;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class PlanoDeAcaoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('modulo.acessar', 'planos-de-acao'); // filtro fino é aplicado na query
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PlanoDeAcao $planoDeAcao): bool
    {
        if (! Gate::forUser($user)->allows('modulo.acessar', 'planos-de-acao')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->podeAcessarOrganizacao($planoDeAcao->cod_organizacao);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return Gate::forUser($user)->allows('modulo.criar', 'planos-de-acao');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PlanoDeAcao $planoDeAcao): bool
    {
        if (! Gate::forUser($user)->allows('modulo.editar', 'planos-de-acao')) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // 1. Admin da Unidade da organização do plano
        $isAdminUnidade = $user->perfisAcesso()
            ->wherePivot('cod_organizacao', $planoDeAcao->cod_organizacao)
            ->where('tab_perfil_acesso.cod_perfil', PerfilAcesso::ADMIN_UNIDADE)
            ->exists();

        if ($isAdminUnidade) {
            return true;
        }

        // 2. Gestor Responsável vinculado a ESTE plano
        // A verificação `isGestorResponsavel` no User checa a pivot table usando cod_plano_de_acao
        if ($user->isGestorResponsavel($planoDeAcao->cod_plano_de_acao)) {
            return true;
        }

        // 3. Gestor Substituto vinculado a ESTE plano
        if ($user->isGestorSubstituto($planoDeAcao->cod_plano_de_acao)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PlanoDeAcao $planoDeAcao): bool
    {
        if (! Gate::forUser($user)->allows('modulo.excluir', 'planos-de-acao')) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // Apenas Admin da Unidade pode excluir (Gestores não excluem, apenas editam)
        return $user->perfisAcesso()
            ->wherePivot('cod_organizacao', $planoDeAcao->cod_organizacao)
            ->where('tab_perfil_acesso.cod_perfil', PerfilAcesso::ADMIN_UNIDADE)
            ->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PlanoDeAcao $planoDeAcao): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PlanoDeAcao $planoDeAcao): bool
    {
        return $user->isSuperAdmin();
    }
}
