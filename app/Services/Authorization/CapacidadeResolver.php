<?php

namespace App\Services\Authorization;

use App\Models\PerfilAcesso;
use App\Models\User;

/**
 * Traduz os perfis de acesso fixos (Super Admin, Admin Unidade, Gestor
 * Responsável, Gestor Substituto e Consulta) em capacidades nomeadas por
 * módulo (RBAC), servindo de fonte única para os Gates "modulo.*".
 *
 * 🔴 A capacidade vale NA ORGANIZAÇÃO. Antes, todos os perfis do usuário eram
 * somados, em qualquer unidade: Administrador na unidade A e Gestor Substituto
 * na B dava poderes de Administrador também na B. Agora só contam os vínculos
 * que valem para a organização em questão (User::perfisEfetivosNaOrganizacao):
 * a informada na checagem ou, sem ela, a selecionada no topo da tela.
 *
 * A titularidade (o Gestor só age na iniciativa a que está vinculado) e o
 * escopo do registro continuam nas Policies e em ResolveEscopoOrganizacional.
 */
final class CapacidadeResolver
{
    /**
     * Matriz de capacidades por módulo e perfil.
     *
     * Chave externa: nomPath do módulo.
     * Chave interna: cod_perfil (PerfilAcesso).
     * Valor: lista de abilities concedidas ('acessar', 'criar', 'editar',
     * 'excluir', 'exportar').
     *
     * O Super Admin não aparece aqui: é liberado incondicionalmente em
     * podeNoModulo(). Módulos ausentes ou sem entrada para o perfil não
     * concedem nenhuma capacidade (nega por padrão).
     *
     * O recorte segue a regra que a tela "Papéis e responsabilidades" declara
     * ao cliente: o Gestor Responsável não é um crachá geral, é um vínculo com
     * iniciativas específicas. Por isso ele LÊ o planejamento da unidade, mas
     * não reescreve missão, análises, objetivos ou a RAE — isso é do
     * Administrador da Unidade. Ele atua nas iniciativas, entregas,
     * indicadores e riscos pelos quais responde (titularidade nas Policies).
     */
    private const MATRIZ = [
        'planejamento-estrategico' => [
            PerfilAcesso::ADMIN_UNIDADE => ['acessar', 'criar', 'editar', 'excluir', 'exportar'],
            PerfilAcesso::GESTOR_RESPONSAVEL => ['acessar', 'exportar'],
            PerfilAcesso::GESTOR_SUBSTITUTO => ['acessar'],
            PerfilAcesso::CONSULTA => ['acessar', 'exportar'],
        ],
        // Iniciativa nasce pelas mãos do Administrador, que designa os
        // gestores. O Gestor edita a SUA (PlanoDeAcaoPolicy confere o vínculo).
        'planos-de-acao' => [
            PerfilAcesso::ADMIN_UNIDADE => ['acessar', 'criar', 'editar', 'excluir', 'exportar'],
            PerfilAcesso::GESTOR_RESPONSAVEL => ['acessar', 'editar', 'exportar'],
            PerfilAcesso::GESTOR_SUBSTITUTO => ['acessar', 'editar'],
            PerfilAcesso::CONSULTA => ['acessar', 'exportar'],
        ],
        'entregas' => [
            PerfilAcesso::ADMIN_UNIDADE => ['acessar', 'criar', 'editar', 'excluir', 'exportar'],
            PerfilAcesso::GESTOR_RESPONSAVEL => ['acessar', 'criar', 'editar', 'excluir', 'exportar'],
            PerfilAcesso::GESTOR_SUBSTITUTO => ['acessar', 'criar', 'editar'],
            PerfilAcesso::CONSULTA => ['acessar', 'exportar'],
        ],
        'indicadores' => [
            PerfilAcesso::ADMIN_UNIDADE => ['acessar', 'criar', 'editar', 'excluir', 'exportar'],
            PerfilAcesso::GESTOR_RESPONSAVEL => ['acessar', 'criar', 'editar', 'exportar'],
            PerfilAcesso::GESTOR_SUBSTITUTO => ['acessar', 'editar'],
            PerfilAcesso::CONSULTA => ['acessar', 'exportar'],
        ],
        'riscos' => [
            PerfilAcesso::ADMIN_UNIDADE => ['acessar', 'criar', 'editar', 'excluir', 'exportar'],
            PerfilAcesso::GESTOR_RESPONSAVEL => ['acessar', 'criar', 'editar', 'exportar'],
            PerfilAcesso::GESTOR_SUBSTITUTO => ['acessar', 'editar'],
            PerfilAcesso::CONSULTA => ['acessar', 'exportar'],
        ],
        'organizacoes' => [
            PerfilAcesso::ADMIN_UNIDADE => ['acessar', 'editar'],
            PerfilAcesso::GESTOR_RESPONSAVEL => ['acessar'],
            PerfilAcesso::GESTOR_SUBSTITUTO => ['acessar'],
            PerfilAcesso::CONSULTA => ['acessar'],
        ],
        // O diretório de pessoas (nome, e-mail, vínculos) é dado de gestão de
        // acesso: só quem administra a unidade o consulta.
        'usuarios' => [
            PerfilAcesso::ADMIN_UNIDADE => ['acessar'],
        ],
        'relatorios' => [
            PerfilAcesso::ADMIN_UNIDADE => ['acessar', 'exportar'],
            PerfilAcesso::GESTOR_RESPONSAVEL => ['acessar', 'exportar'],
            PerfilAcesso::GESTOR_SUBSTITUTO => ['acessar', 'exportar'],
            PerfilAcesso::CONSULTA => ['acessar', 'exportar'],
        ],
        /*
         * Grau de Satisfação — a RÉGUA com que a organização julga o próprio
         * desempenho: as faixas que pintam o farol de cada indicador.
         *
         * A régua é do ciclo inteiro (a tabela não tem organização). Por isso,
         * além da capacidade abaixo, gravar exige User::podeEditarInstitucional()
         * — Super Admin ou Administrador da unidade raiz. Gestores e Consulta
         * apenas veem: mudar a faixa depois do resultado lançado é reescrever a
         * nota depois da prova.
         */
        'graus-satisfacao' => [
            PerfilAcesso::ADMIN_UNIDADE => ['acessar', 'criar', 'editar', 'excluir'],
            PerfilAcesso::GESTOR_RESPONSAVEL => ['acessar'],
            PerfilAcesso::GESTOR_SUBSTITUTO => ['acessar'],
            PerfilAcesso::CONSULTA => ['acessar'],
        ],

        /*
         * Documentos (acervo em PDF): todos os perfis leem os do seu escopo;
         * envia, altera e exclui o Administrador da Unidade, na unidade dele e
         * nas subordinadas. Documento sem unidade é institucional e exige,
         * além disto, User::podeEditarInstitucional() (DocumentoPolicy).
         */
        'documentos' => [
            PerfilAcesso::ADMIN_UNIDADE => ['acessar', 'criar', 'editar', 'excluir'],
            PerfilAcesso::GESTOR_RESPONSAVEL => ['acessar'],
            PerfilAcesso::GESTOR_SUBSTITUTO => ['acessar'],
            PerfilAcesso::CONSULTA => ['acessar'],
        ],

        // Restritos a Super Admin: nenhum outro perfil recebe capacidade.
        'auditoria' => [],
        'admin.perfis' => [],
        'admin.configuracoes' => [],
    ];

    /**
     * @param  string|null  $codOrganizacao  organização em que a ação acontece;
     *                                       sem ela, vale a selecionada no topo.
     */
    public static function podeNoModulo(User $user, string $nomPath, string $ability, ?string $codOrganizacao = null): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $codOrganizacao ??= $user->organizacaoSelecionadaId();

        // Sem organização nenhuma no escopo, não há vínculo que valha.
        if (! $codOrganizacao) {
            return false;
        }

        $abilitiesPorPerfil = self::MATRIZ[$nomPath] ?? [];

        foreach ($user->perfisEfetivosNaOrganizacao($codOrganizacao) as $codPerfil) {
            if (in_array($ability, $abilitiesPorPerfil[$codPerfil] ?? [], true)) {
                return true;
            }
        }

        return false;
    }

    /** Todas as abilities que o sistema reconhece, na ordem em que se leem. */
    public const ABILITIES = ['acessar', 'criar', 'editar', 'excluir', 'exportar'];

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
