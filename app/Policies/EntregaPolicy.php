<?php

namespace App\Policies;

use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Entrega: segue a iniciativa. Capacidade na organização da iniciativa e,
 * para o Gestor, titularidade da iniciativa.
 */
class EntregaPolicy
{
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('modulo.acessar', 'entregas');
    }

    public function view(User $user, Entrega $entrega): bool
    {
        $org = $entrega->planoDeAcao?->cod_organizacao;

        return $user->isSuperAdmin()
            || ($user->podeAcessarOrganizacao($org)
                && Gate::forUser($user)->allows('modulo.acessar', ['entregas', $org]));
    }

    /** Criar entrega NESTA iniciativa. */
    public function create(User $user, ?PlanoDeAcao $plano = null): bool
    {
        if ($plano === null) {
            return Gate::forUser($user)->allows('modulo.criar', 'entregas');
        }

        return $this->podeNaIniciativa($user, 'criar', $plano);
    }

    public function update(User $user, Entrega $entrega): bool
    {
        return $entrega->planoDeAcao && $this->podeNaIniciativa($user, 'editar', $entrega->planoDeAcao);
    }

    public function delete(User $user, Entrega $entrega): bool
    {
        return $entrega->planoDeAcao && $this->podeNaIniciativa($user, 'excluir', $entrega->planoDeAcao);
    }

    private function podeNaIniciativa(User $user, string $ability, PlanoDeAcao $plano): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $org = $plano->cod_organizacao;

        if (! Gate::forUser($user)->allows("modulo.{$ability}", ['entregas', $org])) {
            return false;
        }

        return $user->ehAdministradorEm($org) || $user->ehGestorDaIniciativa($plano->cod_plano_de_acao);
    }
}
