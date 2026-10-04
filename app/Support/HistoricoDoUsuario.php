<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * O que um usuário deixou registrado no sistema — o que a exclusão física
 * apagaria (as FKs para pei.users são ON DELETE CASCADE) ou deixaria órfão.
 *
 * 🔴 "Excluir usuário" fazia DELETE em pei.users: a trilha de auditoria, as
 * atribuições RACI, as responsabilidades de entrega e os comentários dele
 * sumiam em cascata, e o modal só dizia "perderá o acesso". Quem tem qualquer
 * item desta lista é DESATIVADO, nunca excluído. Exclusão física só para a
 * conta que nunca deixou rastro (ex.: autocadastro nunca usado).
 *
 * Os vínculos de perfil por UNIDADE não entram: são o acesso, não o histórico,
 * e a cascata deles não apaga nada que alguém precise reaver.
 *
 * Lista medida no information_schema em 04/10/2026 (colunas uuid que apontam
 * para pei.users). Tabela nova com autor ou responsável entra aqui.
 */
class HistoricoDoUsuario
{
    /** @var list<array{0: string, 1: string, 2: string}> tabela, coluna, rótulo */
    public const FONTES = [
        ['pei.audits', 'user_id', 'registros na trilha de auditoria'],
        ['pei.tab_audit', 'user_id', 'registros na auditoria legada'],
        ['action_plan.tab_raci', 'user_id', 'atribuições na matriz RACI'],
        ['action_plan.rel_entrega_users_responsaveis', 'cod_usuario', 'responsabilidades de entrega'],
        ['action_plan.tab_entregas', 'cod_responsavel', 'entregas sob sua responsabilidade'],
        ['action_plan.tab_entrega_comentarios', 'cod_usuario', 'comentários em entregas'],
        ['action_plan.tab_entrega_anexos', 'cod_usuario', 'anexos de entregas'],
        ['action_plan.tab_entrega_historico', 'cod_usuario', 'registros no histórico de entregas'],
        ['action_plan.acoes', 'user_id', 'ações registradas'],
        ['strategic_planning.tab_objetivo_comentarios', 'user_id', 'comentários em objetivos'],
        ['strategic_planning.tab_documentos', 'cod_usuario', 'documentos enviados'],
        ['strategic_planning.tab_rae_encaminhamento', 'cod_responsavel', 'encaminhamentos de RAE'],
        ['risk_management.tab_risco', 'cod_responsavel_monitoramento', 'riscos que monitora'],
        ['risk_management.tab_risco_mitigacao', 'cod_responsavel', 'mitigações de risco'],
        ['pei.tab_relatorios_gerados', 'user_id', 'relatórios gerados'],
        ['pei.tab_relatorios_agendados', 'user_id', 'relatórios agendados'],
    ];

    /**
     * Rótulo => quantidade, só do que existe. Vazio = conta sem histórico.
     *
     * @return array<string, int>
     */
    public static function de(User $user): array
    {
        $achados = [];

        foreach (self::FONTES as [$tabela, $coluna, $rotulo]) {
            $total = DB::table($tabela)->where($coluna, $user->id)->count();

            if ($total > 0) {
                $achados[$rotulo] = ($achados[$rotulo] ?? 0) + $total;
            }
        }

        // Gestor de iniciativa: o vínculo de perfil com cod_plano_de_acao é a
        // própria responsabilidade pela iniciativa, não só o acesso.
        $gestorDeIniciativa = DB::table('organization.rel_users_tab_organizacoes_tab_perfil_acesso')
            ->where('user_id', $user->id)
            ->whereNotNull('cod_plano_de_acao')
            ->count();

        if ($gestorDeIniciativa > 0) {
            $achados['vínculos de gestor de iniciativa'] = $gestorDeIniciativa;
        }

        return $achados;
    }

    public static function temHistorico(User $user): bool
    {
        return self::de($user) !== [];
    }
}
