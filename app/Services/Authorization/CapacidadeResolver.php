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
        // Restritos a Super Admin: nenhum outro perfil recebe capacidade.
        'auditoria' => [],
        'admin.perfis' => [],
        'admin.configuracoes' => [],
        'graus-satisfacao' => [],
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
}
