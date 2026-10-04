<?php

namespace App\Support\Auditoria;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

/**
 * Coluna "cod_organizacao" da trilha de auditoria: a unidade a que pertence o
 * registro auditado. É o que recorta a aba "Atividade" do sino por unidade.
 *
 * Ordem da derivação:
 *  1. o atributo cod_organizacao do próprio registro (iniciativa, risco,
 *     documento, valor, vínculo de perfil…);
 *  2. a relação natural: indicador e o que pende dele (evolução, meta, linha
 *     de base) → primeira unidade vinculada ao indicador (ou a da iniciativa);
 *     mitigação/ocorrência → risco; entrega, comentário, anexo, RACI →
 *     iniciativa; usuário → unidade do primeiro vínculo de perfil;
 *  3. nada encontrado → null.
 *
 * Com null, a coluna bln_institucional (InstitucionalDaAuditoria) diz se o
 * registro é do ciclo inteiro — visível a todos — ou se é só desconhecido, e
 * então fica para o Super Admin (nega por padrão).
 *
 * Só lê tabelas e colunas fixas desta classe, nunca nomes vindos do registro.
 * Registrado em config/audit.php ("resolvers.cod_organizacao").
 */
class UnidadeDaAuditoria implements Resolver
{
    /**
     * Tipos que, sem unidade, são do ciclo inteiro (nome curto da classe).
     *
     * @var list<string>
     */
    public const INSTITUCIONAIS = [
        'PEI', 'Perspectiva', 'Objetivo', 'IdentidadeEstrategica', 'TemaNorteador', 'Valor', 'Documento',
        'GrauSatisfacao', 'AtividadeCadeiaValor', 'FuturoAlmejado',
    ];

    public static function resolve(Auditable $auditable): ?string
    {
        return $auditable instanceof Model ? static::unidadeDe($auditable) : null;
    }

    /** Sem unidade e de um tipo do ciclo inteiro? Dado pessoal nunca é institucional. */
    public static function institucional(Model $model): bool
    {
        if ($model instanceof User) {
            return false;
        }

        return static::unidadeDe($model) === null && in_array(class_basename($model), self::INSTITUCIONAIS, true);
    }

    public static function unidadeDe(Model $model): ?string
    {
        $atributos = $model->getAttributes();

        if (! empty($atributos['cod_organizacao'])) {
            return (string) $atributos['cod_organizacao'];
        }

        try {
            return match (true) {
                $model instanceof User => static::doUsuario((string) $model->getKey()),
                ! empty($atributos['cod_indicador']) => static::doIndicador((string) $atributos['cod_indicador']),
                ! empty($atributos['cod_risco']) => static::valor('risk_management.tab_risco', 'cod_risco', $atributos['cod_risco']),
                ! empty($atributos['cod_plano_de_acao']) => static::doPlano($atributos['cod_plano_de_acao']),
                ! empty($atributos['cod_entrega']) => static::doPlano(
                    static::valor('action_plan.tab_entregas', 'cod_entrega', $atributos['cod_entrega'], 'cod_plano_de_acao')
                ),
                default => null,
            };
        } catch (\Throwable) {
            // A auditoria nunca pode impedir a gravação do registro.
            return null;
        }
    }

    private static function doIndicador(string $codIndicador): ?string
    {
        $primeira = DB::table('performance_indicators.rel_indicador_objetivo_organizacao')
            ->where('cod_indicador', $codIndicador)
            ->whereNull('deleted_at')
            ->orderBy('created_at')
            ->orderBy('cod_organizacao')
            ->value('cod_organizacao');

        return $primeira ?: static::doPlano(
            static::valor('performance_indicators.tab_indicador', 'cod_indicador', $codIndicador, 'cod_plano_de_acao')
        );
    }

    private static function doPlano(mixed $codPlano): ?string
    {
        return $codPlano ? static::valor('action_plan.tab_plano_de_acao', 'cod_plano_de_acao', $codPlano) : null;
    }

    private static function doUsuario(string $userId): ?string
    {
        $org = DB::table('organization.rel_users_tab_organizacoes_tab_perfil_acesso')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->whereNotNull('cod_organizacao')
            ->orderBy('created_at')
            ->orderBy('cod_organizacao')
            ->value('cod_organizacao');

        return $org ? (string) $org : null;
    }

    private static function valor(string $tabela, string $chave, mixed $id, string $coluna = 'cod_organizacao'): ?string
    {
        if (! $id) {
            return null;
        }

        $valor = DB::table($tabela)->where($chave, $id)->value($coluna);

        return $valor ? (string) $valor : null;
    }
}
