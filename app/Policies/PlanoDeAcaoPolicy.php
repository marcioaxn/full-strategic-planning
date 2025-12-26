<?php

namespace App\Policies;

use App\Models\PEI\PlanoDeAcao;
use App\Models\User;
use App\Models\PerfilAcesso;
use Illuminate\Auth\Access\Response;

class PlanoDeAcaoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Todos usuários logados podem listar (filtro será aplicado na query)
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PlanoDeAcao $planoDeAcao): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Usuário deve pertencer à mesma organização do plano
        return $user->organizacoes->contains('cod_organizacao', $planoDeAcao->cod_organizacao);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Super Admin pode criar em qualquer lugar (selecionando a org)
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Verificar se usuário tem perfil de Admin Unidade ou Gestor Responsável em alguma organização
        // A lógica fina de "em qual organização" será feita no formulário, filtrando as opções.
        // Aqui validamos se ele tem CAPACIDADE de criar.
        
        return $user->perfisAcesso()->whereIn('cod_perfil', [
            PerfilAcesso::ADMIN_UNIDADE,
            PerfilAcesso::GESTOR_RESPONSAVEL
        ])->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PlanoDeAcao $planoDeAcao): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // 1. Admin da Unidade da organização do plano
        $isAdminUnidade = $user->perfisAcesso()
            ->wherePivot('cod_organizacao', $planoDeAcao->cod_organizacao)
            ->where('cod_perfil', PerfilAcesso::ADMIN_UNIDADE)
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
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Apenas Admin da Unidade pode excluir (Gestores não excluem, apenas editam)
        return $user->perfisAcesso()
            ->wherePivot('cod_organizacao', $planoDeAcao->cod_organizacao)
            ->where('cod_perfil', PerfilAcesso::ADMIN_UNIDADE)
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