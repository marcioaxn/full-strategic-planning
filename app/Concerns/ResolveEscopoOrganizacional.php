<?php

namespace App\Concerns;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Camada ABAC de escopo de organização: resolve, para o usuário autenticado,
 * quais organizações ele pode ver/operar, e qual é a organização
 * "atualmente selecionada" na sessão — sempre validada contra esse escopo.
 *
 * Importante: a sessão nunca é fonte de permissão por si só. Ela guarda uma
 * preferência de navegação (qual organização o usuário quer ver agora), mas
 * organizacaoSelecionadaId() só devolve o valor se ele estiver dentro do
 * escopo real do usuário — do contrário retorna null.
 */
trait ResolveEscopoOrganizacional
{
    /**
     * IDs de todas as organizações que o usuário pode enxergar.
     * Super Admin enxerga todas; os demais, apenas as vinculadas.
     */
    public function organizacaoIdsPermitidas(): Collection
    {
        if ($this->isSuperAdmin()) {
            return Organization::query()->pluck('cod_organizacao');
        }

        if (! $this->relationLoaded('organizacoes')) {
            $this->load('organizacoes');
        }

        return $this->organizacoes->pluck('cod_organizacao');
    }

    public function podeAcessarOrganizacao(?string $codOrganizacao): bool
    {
        if (! $codOrganizacao) {
            return false;
        }

        return $this->isSuperAdmin() || $this->organizacaoIdsPermitidas()->contains($codOrganizacao);
    }

    /**
     * Organização atualmente selecionada na sessão, validada contra o
     * escopo do usuário. Retorna null se não houver seleção ou se a
     * seleção estiver fora do escopo (ex.: sessão desatualizada).
     */
    public function organizacaoSelecionadaId(): ?string
    {
        $selecionada = session('organizacao_selecionada_id');

        if (! $selecionada) {
            return null;
        }

        return $this->podeAcessarOrganizacao($selecionada) ? $selecionada : null;
    }

    /**
     * Aplica whereIn($coluna, ...) num Builder respeitando o escopo do
     * usuário. Super Admin não sofre filtro (vê tudo).
     */
    public function aplicarEscopoOrganizacional(Builder $query, string $coluna = 'cod_organizacao'): Builder
    {
        if ($this->isSuperAdmin()) {
            return $query;
        }

        return $query->whereIn($coluna, $this->organizacaoIdsPermitidas());
    }
}
