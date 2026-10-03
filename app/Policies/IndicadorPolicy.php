<?php

namespace App\Policies;

use App\Models\PerformanceIndicators\Indicador;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Indicador: a capacidade vale nas organizações REAIS do indicador (as do
 * vínculo direto e a da iniciativa), nunca na apenas selecionada no topo.
 *
 * - Administrador de uma dessas unidades (ou de uma superior): edita e exclui.
 * - Gestor Responsável ou Substituto da iniciativa do indicador: edita (lança
 *   evolução). Antes só o Responsável passava aqui, embora a MATRIZ desse
 *   "editar" ao Substituto — o botão aparecia e a gravação era negada.
 * - Indicador de objetivo sem nenhuma organização é da instituição: só quem
 *   pode editar o que é institucional.
 */
class IndicadorPolicy
{
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('modulo.acessar', 'indicadores');
    }

    public function view(User $user, Indicador $indicador): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $orgs = $this->organizacoesDoIndicador($indicador);

        if ($orgs === []) {
            return Gate::forUser($user)->allows('modulo.acessar', 'indicadores');
        }

        foreach ($orgs as $org) {
            if ($user->podeAcessarOrganizacao($org) && Gate::forUser($user)->allows('modulo.acessar', ['indicadores', $org])) {
                return true;
            }
        }

        return false;
    }

    public function create(User $user, ?string $codOrganizacao = null): bool
    {
        return Gate::forUser($user)->allows('modulo.criar', ['indicadores', $codOrganizacao]);
    }

    public function update(User $user, Indicador $indicador): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $planoOrg = $indicador->planoDeAcao?->cod_organizacao;

        if ($indicador->cod_plano_de_acao
            && $user->ehGestorDaIniciativa($indicador->cod_plano_de_acao)
            && Gate::forUser($user)->allows('modulo.editar', ['indicadores', $planoOrg])) {
            return true;
        }

        return $this->ehAdministradorDoIndicador($user, $indicador, 'editar');
    }

    public function delete(User $user, Indicador $indicador): bool
    {
        return $user->isSuperAdmin() || $this->ehAdministradorDoIndicador($user, $indicador, 'excluir');
    }

    private function ehAdministradorDoIndicador(User $user, Indicador $indicador, string $ability): bool
    {
        $orgs = $this->organizacoesDoIndicador($indicador);

        if ($orgs === []) {
            return $user->podeEditarInstitucional();
        }

        foreach ($orgs as $org) {
            if ($user->ehAdministradorEm($org) && Gate::forUser($user)->allows("modulo.{$ability}", ['indicadores', $org])) {
                return true;
            }
        }

        return false;
    }

    /** @return array<int, string> */
    private function organizacoesDoIndicador(Indicador $indicador): array
    {
        $orgs = $indicador->organizacoes()->pluck('tab_organizacoes.cod_organizacao');

        if ($indicador->planoDeAcao?->cod_organizacao) {
            $orgs->push($indicador->planoDeAcao->cod_organizacao);
        }

        return $orgs->filter()->unique()->values()->all();
    }
}
