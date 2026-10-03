<?php

namespace App\Policies;

use App\Models\Documento;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Documento do acervo. Lê quem tem perfil no escopo; grava o Super Admin e o
 * Administrador da Unidade — na unidade do documento ou numa subordinada.
 *
 * Documento sem unidade é institucional: grava só quem edita o institucional
 * (Super Admin ou Administrador da unidade raiz), para que o Administrador de
 * uma unidade não substitua o decreto que vale para o órgão inteiro.
 */
class DocumentoPolicy
{
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('modulo.acessar', 'documentos');
    }

    public function view(User $user, Documento $documento): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $documento->cod_organizacao) {
            return $this->viewAny($user);
        }

        return $user->podeAcessarOrganizacao($documento->cod_organizacao)
            && Gate::forUser($user)->allows('modulo.acessar', ['documentos', $documento->cod_organizacao]);
    }

    public function create(User $user, ?string $codOrganizacao = null): bool
    {
        return $this->pode($user, 'criar', $codOrganizacao);
    }

    public function update(User $user, Documento $documento): bool
    {
        return $this->pode($user, 'editar', $documento->cod_organizacao);
    }

    public function delete(User $user, Documento $documento): bool
    {
        return $this->pode($user, 'excluir', $documento->cod_organizacao);
    }

    private function pode(User $user, string $ability, ?string $codOrganizacao): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $codOrganizacao) {
            return $user->podeEditarInstitucional()
                && Gate::forUser($user)->allows("modulo.{$ability}", 'documentos');
        }

        return $user->podeAcessarOrganizacao($codOrganizacao)
            && Gate::forUser($user)->allows("modulo.{$ability}", ['documentos', $codOrganizacao]);
    }
}
