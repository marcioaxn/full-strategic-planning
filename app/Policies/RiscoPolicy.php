<?php

namespace App\Policies;

use App\Models\RiskManagement\Risco;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Risco: capacidade na organização do risco. Além do Administrador, edita o
 * responsável pelo monitoramento — desde que o perfil dele, NAQUELA unidade,
 * permita editar riscos (Consulta, por exemplo, não permite).
 */
class RiscoPolicy
{
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('modulo.acessar', 'riscos');
    }

    public function view(User $user, Risco $risco): bool
    {
        return $user->isSuperAdmin()
            || ($user->podeAcessarOrganizacao($risco->cod_organizacao)
                && Gate::forUser($user)->allows('modulo.acessar', ['riscos', $risco->cod_organizacao]));
    }

    public function create(User $user, ?string $codOrganizacao = null): bool
    {
        return Gate::forUser($user)->allows('modulo.criar', ['riscos', $codOrganizacao]);
    }

    public function update(User $user, Risco $risco): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $org = $risco->cod_organizacao;

        if (! Gate::forUser($user)->allows('modulo.editar', ['riscos', $org])) {
            return false;
        }

        return $user->ehAdministradorEm($org) || $risco->cod_responsavel_monitoramento === $user->id;
    }

    public function delete(User $user, Risco $risco): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return Gate::forUser($user)->allows('modulo.excluir', ['riscos', $risco->cod_organizacao])
            && $user->ehAdministradorEm($risco->cod_organizacao);
    }
}
