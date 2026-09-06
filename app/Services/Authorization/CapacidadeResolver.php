<?php

namespace App\Services\Authorization;

use App\Models\PerfilAcesso;
use App\Models\User;

/**
 * Traduz os 4 perfis de acesso fixos (Super Admin, Admin Unidade, Gestor
 * Responsável, Gestor Substituto) em capacidades nomeadas por módulo
 * (RBAC), servindo de fonte única para os Gates "modulo.*".
 *
 * Não decide nada com base em atributos do registro/organização — isso é
 * responsabilidade da camada ABAC (ver ResolveEscopoOrganizacional e as
 * Policies), combinada por cima do resultado desta classe.
 */
final class CapacidadeResolver
{
    /**
     * Matriz de capacidades por módulo e perfil.
     *
     * Chave externa: nomPath do módulo.
     * Chave interna: cod_perfil (PerfilAcesso).
     * Valor: lista de abilities concedidas ('acessar', 'ver-sensivel',
     * 'criar', 'editar', 'excluir', 'exportar').
     *
     * O Super Admin não aparece aqui: é liberado incondicionalmente em
     * podeNoModulo(). Módulos ausentes ou sem entrada para o perfil não
     * concedem nenhuma capacidade (nega por padrão).
     */
    private const MATRIZ = [
        'planejamento-estrategico' => [
            PerfilAcesso::ADMIN_UNIDADE => ['acessar', 'ver-sensivel', 'criar', 'editar', 'excluir', 'exportar'],
            PerfilAcesso::GESTOR_RESPONSAVEL => ['acessar', 'ver-sensivel', 'criar', 'editar', 'exportar'],
            PerfilAcesso::GESTOR_SUBSTITUTO => ['acessar', 'ver-sensivel', 'editar'],
        ],
        'planos-de-acao' => [
            PerfilAcesso::ADMIN_UNIDADE => ['acessar', 'ver-sensivel', 'criar', 'editar', 'excluir', 'exportar'],
            PerfilAcesso::GESTOR_RESPONSAVEL => ['acessar', 'ver-sensivel', 'criar', 'editar', 'exportar'],
            PerfilAcesso::GESTOR_SUBSTITUTO => ['acessar', 'ver-sensivel', 'editar'],
        ],
        'entregas' => [
            PerfilAcesso::ADMIN_UNIDADE => ['acessar', 'ver-sensivel', 'criar', 'editar', 'excluir', 'exportar'],
            PerfilAcesso::GESTOR_RESPONSAVEL => ['acessar', 'ver-sensivel', 'criar', 'editar', 'excluir', 'exportar'],
            PerfilAcesso::GESTOR_SUBSTITUTO => ['acessar', 'ver-sensivel', 'criar', 'editar'],
        ],
        'indicadores' => [
            PerfilAcesso::ADMIN_UNIDADE => ['acessar', 'ver-sensivel', 'criar', 'editar', 'excluir', 'exportar'],
            PerfilAcesso::GESTOR_RESPONSAVEL => ['acessar', 'ver-sensivel', 'criar', 'editar', 'exportar'],
            PerfilAcesso::GESTOR_SUBSTITUTO => ['acessar', 'ver-sensivel', 'editar'],
        ],
        'riscos' => [
            PerfilAcesso::ADMIN_UNIDADE => ['acessar', 'ver-sensivel', 'criar', 'editar', 'excluir', 'exportar'],
            PerfilAcesso::GESTOR_RESPONSAVEL => ['acessar', 'ver-sensivel', 'criar', 'editar', 'exportar'],
            PerfilAcesso::GESTOR_SUBSTITUTO => ['acessar', 'ver-sensivel', 'editar'],
        ],
        'organizacoes' => [
            PerfilAcesso::ADMIN_UNIDADE => ['acessar', 'editar'],
            PerfilAcesso::GESTOR_RESPONSAVEL => ['acessar'],
            PerfilAcesso::GESTOR_SUBSTITUTO => ['acessar'],
        ],
        'usuarios' => [
            PerfilAcesso::ADMIN_UNIDADE => ['acessar'],
            PerfilAcesso::GESTOR_RESPONSAVEL => ['acessar'],
            PerfilAcesso::GESTOR_SUBSTITUTO => ['acessar'],
        ],
        'relatorios' => [
            PerfilAcesso::ADMIN_UNIDADE => ['acessar', 'ver-sensivel', 'exportar'],
            PerfilAcesso::GESTOR_RESPONSAVEL => ['acessar', 'ver-sensivel', 'exportar'],
            PerfilAcesso::GESTOR_SUBSTITUTO => ['acessar', 'exportar'],
        ],
        /*
         * Grau de Satisfação — a RÉGUA com que a organização julga o próprio
         * desempenho: as faixas que pintam o farol de cada indicador.
         *
         * Estava restrito ao Super Admin, e isso quebrava o ciclo: o
         * PeiGuidanceService manda o cliente configurar as faixas logo depois
         * dos objetivos (fase 5 de 7) e oferece o botão "Configurar Níveis" —
         * que devolvia 403 para todo perfil que não fosse Super Admin. O
         * sistema mandava ir a uma tela que ele mesmo proibia.
         *
         * O recorte abaixo segue a responsabilidade real:
         *  - ADMIN_UNIDADE define a régua: responde pelo planejamento da unidade.
         *  - GESTOR_RESPONSAVEL e GESTOR_SUBSTITUTO apenas VEEM: precisam da
         *    régua para interpretar o farol, mas mudar a faixa depois do
         *    resultado lançado é reescrever a nota depois da prova.
         */
        'graus-satisfacao' => [
            PerfilAcesso::ADMIN_UNIDADE => ['acessar', 'ver-sensivel', 'criar', 'editar', 'excluir'],
            PerfilAcesso::GESTOR_RESPONSAVEL => ['acessar', 'ver-sensivel'],
            PerfilAcesso::GESTOR_SUBSTITUTO => ['acessar', 'ver-sensivel'],
        ],

        // Restritos a Super Admin: nenhum outro perfil recebe capacidade.
        'auditoria' => [],
        'admin.perfis' => [],
        'admin.configuracoes' => [],
    ];

    public static function podeNoModulo(User $user, string $nomPath, string $ability): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->relationLoaded('perfisAcesso')) {
            $user->load('perfisAcesso');
        }

        $abilitiesPorPerfil = self::MATRIZ[$nomPath] ?? [];

        foreach ($user->perfisAcesso->pluck('cod_perfil')->unique() as $codPerfil) {
            if (in_array($ability, $abilitiesPorPerfil[$codPerfil] ?? [], true)) {
                return true;
            }
        }

        return false;
    }

    /** Todas as abilities que o sistema reconhece, na ordem em que se leem. */
    public const ABILITIES = ['acessar', 'ver-sensivel', 'criar', 'editar', 'excluir', 'exportar'];

    /**
     * A matriz, para leitura.
     *
     * Existe para que a página de ajuda "Papéis e responsabilidades" seja
     * DERIVADA daqui, e não escrita à mão. Documentação copiada envelhece na
     * primeira mudança de regra; derivada, não envelhece nunca.
     *
     * @return array<string, array<string, array<int, string>>>
     */
    public static function matriz(): array
    {
        return self::MATRIZ;
    }

    /** Os módulos que só o Super Admin alcança (entrada vazia na matriz). */
    public static function modulosRestritos(): array
    {
        return array_keys(array_filter(self::MATRIZ, fn (array $perfis) => $perfis === []));
    }

    /**
     * O que um perfil pode num módulo — sem precisar de um usuário.
     *
     * @return array<int, string>
     */
    public static function capacidadesDoPerfil(string $codPerfil, string $nomPath): array
    {
        if ($codPerfil === PerfilAcesso::SUPER_ADMIN) {
            return self::ABILITIES;
        }

        return self::MATRIZ[$nomPath][$codPerfil] ?? [];
    }
}
