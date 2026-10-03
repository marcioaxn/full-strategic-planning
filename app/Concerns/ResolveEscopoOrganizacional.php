<?php

namespace App\Concerns;

use App\Models\Organization;
use App\Models\PerfilAcesso;
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
     *
     * Super Admin enxerga todas. Os demais, as unidades a que estão vinculados
     * e — onde o vínculo é de Administrador da Unidade ou de Consulta — também
     * as subordinadas a elas (a regra que a tela de perfis sempre declarou).
     */
    public function organizacaoIdsPermitidas(): Collection
    {
        if ($this->isSuperAdmin()) {
            return Organization::query()->pluck('cod_organizacao');
        }

        if (! $this->relationLoaded('perfisAcesso')) {
            $this->load('perfisAcesso');
        }

        // 🔴 O escopo sai SÓ dos vínculos de PERFIL — é o que dá permissão.
        // Antes somava também o vínculo genérico usuário × unidade, que fica
        // para trás quando o Gestor é retirado de uma iniciativa de outra
        // unidade: a unidade continuava "dele", o sistema o colocava nela por
        // padrão e lá ele não tinha perfil nenhum — tudo negado.
        $ids = collect();

        foreach ($this->perfisAcesso as $perfil) {
            $org = $perfil->pivot->cod_organizacao;

            if (! $org) {
                continue;
            }

            $ids->push($org);

            if (in_array($perfil->cod_perfil, PerfilAcesso::PERFIS_HIERARQUICOS, true)) {
                $ids = $ids->merge(Organization::descendentesEProprio($org));
            }
        }

        return $ids->unique()->values();
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

        if ($selecionada && $this->podeAcessarOrganizacao($selecionada)) {
            return $selecionada;
        }

        // Sem seleção válida, quem não é Super Admin cai na PRIMEIRA unidade do
        // próprio escopo — nunca em "nenhuma", que várias telas liam como
        // "todas as unidades". Super Admin sem seleção continua vendo tudo.
        if ($this->isSuperAdmin()) {
            return null;
        }

        $primeira = $this->organizacaoIdsPermitidas()->first();

        if ($primeira) {
            session(['organizacao_selecionada_id' => $primeira]);
        }

        return $primeira;
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
