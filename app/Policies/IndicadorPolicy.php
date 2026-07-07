<?php

namespace App\Policies;

use App\Models\PerfilAcesso;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class IndicadorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('modulo.acessar', 'indicadores');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Indicador $indicador): bool
    {
        if (! Gate::forUser($user)->allows('modulo.acessar', 'indicadores')) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // Se o indicador está vinculado a uma organização específica via pivot table,
        // usando a organização atualmente selecionada (já validada contra o escopo do usuário).
        $orgSelecionada = $user->organizacaoSelecionadaId();
        if ($orgSelecionada && $indicador->organizacoes()->where('tab_organizacoes.cod_organizacao', $orgSelecionada)->exists()) {
            return true;
        }

        // Se o indicador está vinculado a um objetivo/plano de uma organização do usuário
        if ($indicador->objetivo && $user->podeAcessarOrganizacao($indicador->objetivo->cod_organizacao)) {
            return true;
        }

        if ($indicador->planoDeAcao && $user->podeAcessarOrganizacao($indicador->planoDeAcao->cod_organizacao)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return Gate::forUser($user)->allows('modulo.criar', 'indicadores');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Indicador $indicador): bool
    {
        if (! Gate::forUser($user)->allows('modulo.editar', 'indicadores')) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // Gestor Responsável do Plano vinculado (regra ABAC pontual — dono do plano)
        if ($indicador->cod_plano_de_acao && $user->isGestorResponsavel($indicador->cod_plano_de_acao)) {
            return true;
        }

        // Admin Unidade da organização atualmente selecionada (só vale se essa
        // organização estiver dentro do escopo real do usuário).
        return $this->ehAdminUnidadeDaOrganizacaoSelecionada($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Indicador $indicador): bool
    {
        if (! Gate::forUser($user)->allows('modulo.excluir', 'indicadores')) {
            return false;
        }

        return $user->isSuperAdmin() || $this->ehAdminUnidadeDaOrganizacaoSelecionada($user);
    }

    private function ehAdminUnidadeDaOrganizacaoSelecionada(User $user): bool
    {
        $orgSelecionada = $user->organizacaoSelecionadaId();

        if (! $orgSelecionada) {
            return false;
        }

        return $user->perfisAcesso()
            ->wherePivot('cod_organizacao', $orgSelecionada)
            ->where('tab_perfil_acesso.cod_perfil', PerfilAcesso::ADMIN_UNIDADE)
            ->exists();
    }
}
