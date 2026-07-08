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

        // Admin Unidade de alguma organização REAL do indicador (nunca da
        // organização apenas selecionada na sessão — ver ehAdminUnidadeDoIndicador).
        return $this->ehAdminUnidadeDoIndicador($user, $indicador);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Indicador $indicador): bool
    {
        if (! Gate::forUser($user)->allows('modulo.excluir', 'indicadores')) {
            return false;
        }

        return $user->isSuperAdmin() || $this->ehAdminUnidadeDoIndicador($user, $indicador);
    }

    /**
     * Verifica se o usuário é Admin Unidade de alguma organização à qual o
     * indicador está REALMENTE vinculado (pivot direto, objetivo ou plano de
     * ação). Corrige uma falha em que a checagem usava apenas a organização
     * selecionada na sessão, sem confirmar que o indicador de fato pertence
     * a ela — permitindo que um Admin Unidade de qualquer organização
     * editasse/excluísse indicadores de outras organizações bastando manter
     * a sua própria selecionada no menu superior.
     */
    private function ehAdminUnidadeDoIndicador(User $user, Indicador $indicador): bool
    {
        $orgsDoIndicador = $indicador->organizacoes()->pluck('tab_organizacoes.cod_organizacao');

        if ($indicador->objetivo?->cod_organizacao) {
            $orgsDoIndicador->push($indicador->objetivo->cod_organizacao);
        }

        if ($indicador->planoDeAcao?->cod_organizacao) {
            $orgsDoIndicador->push($indicador->planoDeAcao->cod_organizacao);
        }

        $orgsDoIndicador = $orgsDoIndicador->unique()->filter();

        if ($orgsDoIndicador->isEmpty()) {
            return false;
        }

        return $user->perfisAcesso()
            ->wherePivotIn('cod_organizacao', $orgsDoIndicador->all())
            ->where('tab_perfil_acesso.cod_perfil', PerfilAcesso::ADMIN_UNIDADE)
            ->exists();
    }
}
