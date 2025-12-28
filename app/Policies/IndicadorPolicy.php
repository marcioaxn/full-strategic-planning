<?php

namespace App\Policies;

use App\Models\PEI\Indicador;
use App\Models\User;
use App\Models\PerfilAcesso;
use Illuminate\Auth\Access\Response;

class IndicadorPolicy
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
    public function view(User $user, Indicador $indicador): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Se o indicador está vinculado a uma organização específica via pivot table
        if ($indicador->organizacoes()->where('tab_organizacoes.cod_organizacao', session('organizacao_selecionada_id'))->exists()) {
            return true;
        }

        // Se o indicador está vinculado a um objetivo/plano da organização do usuário
        if ($indicador->objetivoEstrategico && $user->organizacoes->contains('cod_organizacao', $indicador->objetivoEstrategico->cod_organizacao)) {
            return true;
        }

        if ($indicador->planoDeAcao && $user->organizacoes->contains('cod_organizacao', $indicador->planoDeAcao->cod_organizacao)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->perfisAcesso()->whereIn('cod_perfil', [
            PerfilAcesso::ADMIN_UNIDADE,
            PerfilAcesso::GESTOR_RESPONSAVEL
        ])->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Indicador $indicador): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin Unidade da organização vinculada
        $orgId = session('organizacao_selecionada_id');
        if ($orgId) {
            $isAdminUnidade = $user->perfisAcesso()
                ->wherePivot('cod_organizacao', $orgId)
                ->where('cod_perfil', PerfilAcesso::ADMIN_UNIDADE)
                ->exists();
            
            if ($isAdminUnidade) {
                return true;
            }
        }

        // Gestor Responsável do Plano vinculado (se houver)
        if ($indicador->cod_plano_de_acao && $user->isGestorResponsavel($indicador->cod_plano_de_acao)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Indicador $indicador): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Apenas Admin Unidade
        $orgId = session('organizacao_selecionada_id');
        return $user->perfisAcesso()
            ->wherePivot('cod_organizacao', $orgId)
            ->where('cod_perfil', PerfilAcesso::ADMIN_UNIDADE)
            ->exists();
    }
}