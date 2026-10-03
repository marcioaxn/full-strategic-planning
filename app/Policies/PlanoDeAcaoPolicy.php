<?php

namespace App\Policies;

use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Iniciativa: a capacidade vale NA ORGANIZAÇÃO DA INICIATIVA, nunca na
 * selecionada no topo. O Administrador da unidade (ou de uma superior) age em
 * todas; o Gestor, só na iniciativa a que está vinculado.
 */
class PlanoDeAcaoPolicy
{
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('modulo.acessar', 'planos-de-acao'); // filtro fino é aplicado na query
    }

    public function view(User $user, PlanoDeAcao $planoDeAcao): bool
    {
        return $user->isSuperAdmin()
            || ($user->podeAcessarOrganizacao($planoDeAcao->cod_organizacao)
                && Gate::forUser($user)->allows('modulo.acessar', ['planos-de-acao', $planoDeAcao->cod_organizacao]));
    }

    public function create(User $user, ?string $codOrganizacao = null): bool
    {
        return Gate::forUser($user)->allows('modulo.criar', ['planos-de-acao', $codOrganizacao]);
    }

    public function update(User $user, PlanoDeAcao $planoDeAcao): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $org = $planoDeAcao->cod_organizacao;

        if (! Gate::forUser($user)->allows('modulo.editar', ['planos-de-acao', $org])) {
            return false;
        }

        return $user->ehAdministradorEm($org) || $user->ehGestorDaIniciativa($planoDeAcao->cod_plano_de_acao);
    }

    /**
     * Designar gestores (Responsável e Substituto) da iniciativa.
     *
     * 🔴 Era a mesma regra de "editar": o Gestor Substituto podia se promover a
     * Responsável, ou dar o papel a qualquer pessoa. Designar é ato de quem
     * administra a unidade.
     */
    public function designarGestores(User $user, PlanoDeAcao $planoDeAcao): bool
    {
        return $user->isSuperAdmin() || $user->ehAdministradorEm($planoDeAcao->cod_organizacao);
    }

    public function delete(User $user, PlanoDeAcao $planoDeAcao): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Excluir é do Administrador (a MATRIZ não dá "excluir" a Gestores).
        return Gate::forUser($user)->allows('modulo.excluir', ['planos-de-acao', $planoDeAcao->cod_organizacao])
            && $user->ehAdministradorEm($planoDeAcao->cod_organizacao);
    }

    public function restore(User $user, PlanoDeAcao $planoDeAcao): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, PlanoDeAcao $planoDeAcao): bool
    {
        return $user->isSuperAdmin();
    }
}
