<?php

namespace App\Policies;

use App\Models\ActionPlan\Entrega;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class EntregaPolicy
{
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('modulo.acessar', 'entregas');
    }

    public function view(User $user, Entrega $entrega): bool
    {
        if (! Gate::forUser($user)->allows('modulo.acessar', 'entregas')) {
            return false;
        }

        return $user->podeAcessarOrganizacao($entrega->planoDeAcao?->cod_organizacao);
    }

    public function create(User $user): bool
    {
        return Gate::forUser($user)->allows('modulo.criar', 'entregas');
    }

    public function update(User $user, Entrega $entrega): bool
    {
        if (! Gate::forUser($user)->allows('modulo.editar', 'entregas')) {
            return false;
        }

        return $user->podeAcessarOrganizacao($entrega->planoDeAcao?->cod_organizacao);
    }

    public function delete(User $user, Entrega $entrega): bool
    {
        if (! Gate::forUser($user)->allows('modulo.excluir', 'entregas')) {
            return false;
        }

        return $user->podeAcessarOrganizacao($entrega->planoDeAcao?->cod_organizacao);
    }
}
