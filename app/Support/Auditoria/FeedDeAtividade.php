<?php

namespace App\Support\Auditoria;

use App\Models\AtividadeLeitura;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\User;
use App\Support\AuditoriaLegivel;
use App\Support\RotuloAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use OwenIt\Auditing\Models\Audit;

/**
 * A aba "Atividade" do sino: o que OUTRAS pessoas fizeram no sistema, lido da
 * trilha de auditoria (pei.audits) e recortado pela unidade.
 *
 * Quem vê o quê:
 *  - Super Admin: tudo, inclusive os registros antigos, sem unidade gravada;
 *  - demais perfis (Consulta inclusive): registros das unidades do escopo
 *    (organizacaoIdsPermitidas) e os institucionais do ciclo;
 *  - dado pessoal (usuário): só o Administrador da unidade e o Super Admin.
 *  - nunca as ações do próprio usuário.
 *
 * Só lê; a única escrita é o "visto até" do próprio usuário.
 */
final class FeedDeAtividade
{
    /** Linhas (já agrupadas) que o sino mostra. */
    public const LIMITE = 30;

    /** Registros lidos para montar as linhas: folga para o agrupamento. */
    private const LOTE = 150;

    /** Edições do mesmo autor no mesmo registro dentro desta janela viram uma linha. */
    public const JANELA_AGRUPAMENTO_MINUTOS = 10;

    /** O contador para de contar aqui ("99+"). */
    public const TETO_CONTADOR = 99;

    /** Sem "visto até" gravado, conta-se o que é novo nos últimos dias. */
    private const DIAS_SEM_LEITURA = 7;

    /**
     * Tipos que são dado pessoal: só o Administrador da unidade e o Super Admin os veem.
     *
     * @var list<class-string>
     */
    public const TIPOS_PESSOAIS = [User::class];

    /** @var array<string, array{rotulo: string, icone: string, peso: int}> */
    public const NIVEIS = [
        'informativo' => ['rotulo' => 'Informativo', 'icone' => 'bi-info-circle', 'peso' => 0],
        'atencao' => ['rotulo' => 'Atenção', 'icone' => 'bi-exclamation-triangle', 'peso' => 1],
        'critico' => ['rotulo' => 'Crítico', 'icone' => 'bi-exclamation-octagon', 'peso' => 2],
    ];

    /** Campos cuja alteração muda o compromisso: prazo, situação, meta, valores, pesos e faixas. */
    private const PADRAO_ATENCAO = '/(^|_)(prazo|status|meta|peso|previsto|realizado)(_|$)|^vlr_(minimo|maximo)$|linha_base|^cod_grau_satisfacao$|^num_(probabilidade|impacto|nivel_risco)$|^dsc_estrategia_resposta$/';

    /** Mudança de perfil, vínculo ou situação da conta é crítica. */
    private const CAMPOS_CRITICOS = ['cod_perfil', 'adm', 'ativo'];

    /** @var array<string, ?object> linhas dos registros auditados, por requisição */
    private static array $cacheLinhas = [];

    /** Os registros de auditoria que este usuário pode ver no feed. */
    public static function consulta(User $user): Builder
    {
        $query = Audit::query()->where(function (Builder $q) use ($user) {
            $q->whereNull('user_id')->orWhere('user_id', '<>', $user->getKey());
        });

        if ($user->isSuperAdmin()) {
            return $query;
        }

        $unidades = $user->organizacaoIdsPermitidas()->all();
        $administradas = self::unidadesAdministradas($user);

        return $query->where(function (Builder $q) use ($unidades, $administradas) {
            $q->where(fn (Builder $w) => $w->whereIn('cod_organizacao', $unidades)->whereNotIn('auditable_type', self::TIPOS_PESSOAIS))
                ->orWhere(fn (Builder $w) => $w->where('bln_institucional', true)->whereNotIn('auditable_type', self::TIPOS_PESSOAIS))
                ->orWhere(fn (Builder $w) => $w->whereIn('auditable_type', self::TIPOS_PESSOAIS)->whereIn('cod_organizacao', $administradas));
        });
    }

    /** Quantos registros novos desde a última vez que o usuário abriu a aba (até o teto). */
    public static function quantidadeNova(User $user): int
    {
        return self::consulta($user)
            ->where('created_at', '>', self::vistoAte($user))
            ->orderByDesc('id')
            ->limit(self::TETO_CONTADOR + 1)
            ->pluck('id')
            ->count();
    }

    public static function vistoAte(User $user): Carbon
    {
        $visto = AtividadeLeitura::query()->whereKey($user->getKey())->value('dte_visto_ate');

        return $visto ? Carbon::parse($visto) : now()->subDays(self::DIAS_SEM_LEITURA);
    }

    /** Grava "visto até agora". Sem upsert: o banco do cliente pode ser PostgreSQL 9.3. */
    public static function marcarVisto(User $user): void
    {
        $agora = now();
        $atualizados = AtividadeLeitura::query()->whereKey($user->getKey())->update(['dte_visto_ate' => $agora, 'updated_at' => $agora]);

        if ($atualizados > 0) {
            return;
        }

        try {
            AtividadeLeitura::create(['user_id' => $user->getKey(), 'dte_visto_ate' => $agora]);
        } catch (QueryException) {
            // Outra aba criou a linha no mesmo instante.
            AtividadeLeitura::query()->whereKey($user->getKey())->update(['dte_visto_ate' => $agora, 'updated_at' => $agora]);
        }
    }

    /**
     * As linhas do feed, mais recentes primeiro, já agrupadas e em texto humano.
     *
     * @return list<array{chave: int, evento: string, tipo: string, nivel: string, rotuloNivel: string, icone: string, frase: string, quando: Carbon, relativo: string, link: ?string, novo: bool, edicoes: int}>
     */
    public static function itens(User $user, ?Carbon $novosDepoisDe = null): array
    {
        $registros = self::consulta($user)->with('user')->orderByDesc('id')->limit(self::LOTE)->get();
        $podeVerAuditoria = $user->can('modulo.acessar', 'auditoria');

        $itens = [];
        foreach (self::agrupar($registros->all()) as $grupo) {
            $recente = $grupo[0];
            $nivel = self::nivelDoGrupo($grupo);

            $itens[] = [
                'chave' => (int) $recente->getKey(),
                'evento' => (string) $recente->event,
                'tipo' => (string) $recente->auditable_type,
                'nivel' => $nivel,
                'rotuloNivel' => self::NIVEIS[$nivel]['rotulo'],
                'icone' => self::NIVEIS[$nivel]['icone'],
                'frase' => self::frase($grupo),
                'quando' => $recente->created_at,
                'relativo' => $recente->created_at->diffForHumans(),
                'link' => self::link($recente, $podeVerAuditoria),
                'novo' => $novosDepoisDe !== null && $recente->created_at->greaterThan($novosDepoisDe),
                'edicoes' => count($grupo),
            ];

            if (count($itens) >= self::LIMITE) {
                break;
            }
        }

        return $itens;
    }

    /** Nível de um registro: crítico, atenção ou informativo. */
    public static function nivel(Audit $audit): string
    {
        if (in_array($audit->event, ['deleted', 'forceDeleted', 'restored'], true)) {
            return 'critico';
        }

        if ($audit->event !== 'updated') {
            return 'informativo';
        }

        $colunas = array_keys(array_merge((array) $audit->old_values, (array) $audit->new_values));

        if (array_intersect($colunas, self::CAMPOS_CRITICOS) !== []) {
            return 'critico';
        }

        foreach ($colunas as $coluna) {
            if (preg_match(self::PADRAO_ATENCAO, (string) $coluna)) {
                return 'atencao';
            }
        }

        return 'informativo';
    }

    /**
     * Agrupa, na ordem do feed, as edições do mesmo autor no mesmo registro
     * feitas dentro da janela (contada a partir da mais recente do grupo).
     *
     * @param  list<Audit>  $registros  mais recentes primeiro
     * @return list<list<Audit>> cada grupo com o mais recente primeiro
     */
    private static function agrupar(array $registros): array
    {
        $grupos = [];
        $abertos = [];

        foreach ($registros as $audit) {
            if ($audit->event !== 'updated') {
                $grupos[] = [$audit];

                continue;
            }

            $chave = $audit->user_id.'|'.$audit->auditable_type.'|'.$audit->auditable_id;
            $indice = $abertos[$chave] ?? null;

            if ($indice !== null
                && $grupos[$indice][0]->created_at->diffInMinutes($audit->created_at, true) <= self::JANELA_AGRUPAMENTO_MINUTOS) {
                $grupos[$indice][] = $audit;

                continue;
            }

            $grupos[] = [$audit];
            $abertos[$chave] = array_key_last($grupos);
        }

        return $grupos;
    }

    /** @param  list<Audit>  $grupo */
    private static function nivelDoGrupo(array $grupo): string
    {
        $nivel = 'informativo';
        foreach ($grupo as $audit) {
            $deste = self::nivel($audit);
            if (self::NIVEIS[$deste]['peso'] > self::NIVEIS[$nivel]['peso']) {
                $nivel = $deste;
            }
        }

        return $nivel;
    }

    /**
     * "Fulana alterou a data de prazo da entrega «X» de 30/11/2026 para 15/12/2026".
     *
     * @param  list<Audit>  $grupo  mais recente primeiro
     */
    private static function frase(array $grupo): string
    {
        $recente = $grupo[0];
        $autor = $recente->user_id ? ($recente->user->name ?? 'Usuário excluído') : 'Sistema';
        $tipo = AuditoriaLegivel::comArtigo(RotuloAuditoria::registro($recente->auditable_type));
        $nome = AuditoriaLegivel::nomeDoRegistro($recente);
        $registro = $nome ? $tipo.' «'.$nome.'»' : $tipo;

        if ($recente->event !== 'updated') {
            return $autor.' '.AuditoriaLegivel::verbo($recente->event).' '.$registro;
        }

        $mudancas = AuditoriaLegivel::mudancas(self::mesclar($grupo));
        $edicoes = count($grupo) > 1 ? ' ('.count($grupo).' edições)' : '';

        if (count($mudancas) === 1) {
            $m = $mudancas[0];
            $campo = AuditoriaLegivel::comArtigo($m['rotulo']);
            $valores = '';

            if (! AuditoriaLegivel::ehSigilosa($m['coluna']) && ! $m['longo']) {
                $valores = $m['antes']['vazio']
                    ? ' para '.$m['depois']['texto']
                    : ' de '.$m['antes']['texto'].' para '.$m['depois']['texto'];
            }

            return $autor.' alterou '.$campo.' '.self::de($registro).$valores.$edicoes;
        }

        if ($mudancas === []) {
            return $autor.' alterou '.$registro.$edicoes;
        }

        $rotulos = array_map(fn (array $m) => mb_strtolower($m['rotulo']), array_slice($mudancas, 0, 3));
        $mais = count($mudancas) > 3 ? '…' : '';

        return $autor.' alterou '.count($mudancas).' campos '.self::de($registro).' ('.implode(', ', $rotulos).$mais.')'.$edicoes;
    }

    /**
     * Um registro com o "antes" da edição mais antiga e o "depois" da mais recente.
     *
     * @param  list<Audit>  $grupo  mais recente primeiro
     */
    private static function mesclar(array $grupo): Audit
    {
        if (count($grupo) === 1) {
            return $grupo[0];
        }

        $antes = [];
        $depois = [];
        foreach (array_reverse($grupo) as $audit) {
            foreach ((array) $audit->old_values as $coluna => $valor) {
                if (! array_key_exists($coluna, $antes)) {
                    $antes[$coluna] = $valor;
                }
            }
            foreach ((array) $audit->new_values as $coluna => $valor) {
                $depois[$coluna] = $valor;
            }
        }

        $mesclado = clone $grupo[0];
        $mesclado->old_values = $antes;
        $mesclado->new_values = $depois;

        return $mesclado;
    }

    /** "a entrega" → "da entrega"; "o indicador" → "do indicador". */
    private static function de(string $comArtigo): string
    {
        return 'd'.$comArtigo;
    }

    /**
     * Onde o clique leva: a tela do registro, se houver; senão o detalhe da
     * auditoria a quem pode vê-la; senão nada. As telas de destino fazem a
     * própria autorização.
     */
    private static function link(Audit $audit, bool $podeVerAuditoria): ?string
    {
        $detalhe = $podeVerAuditoria && Route::has('audit.detalhes') ? route('audit.detalhes', $audit->getKey()) : null;

        if (in_array($audit->event, ['deleted', 'forceDeleted'], true)) {
            return $detalhe;
        }

        $id = (string) $audit->auditable_id;
        $campo = fn (string $coluna) => self::campoDoRegistro($audit, $coluna);

        [$rota, $parametros] = match (class_basename((string) $audit->auditable_type)) {
            'PlanoDeAcao' => ['planos.detalhes', [$id]],
            'Entrega', 'Raci', 'EntregaComentario', 'EntregaAnexo' => ['planos.entregas', [$campo('cod_plano_de_acao')]],
            'Indicador' => ['indicadores.detalhes', [$id]],
            'EvolucaoIndicador', 'MetaPorAno', 'LinhaBaseIndicador' => ['indicadores.detalhes', [$campo('cod_indicador')]],
            'Risco' => ['riscos.index', ['search' => AuditoriaLegivel::nomeDoRegistro($audit)]],
            'RiscoMitigacao' => ['riscos.mitigacao', [$campo('cod_risco')]],
            'RiscoOcorrencia' => ['riscos.ocorrencias', [$campo('cod_risco')]],
            'Objetivo' => ['objetivos.detalhes', [$id]],
            'Perspectiva' => ['pei.perspectivas.detalhes', [$id]],
            'Valor' => ['pei.valores.detalhes', [$id]],
            'PEI' => ['pei.detalhes', [$id]],
            'IdentidadeEstrategica' => ['pei.identidade.detalhes', [$id]],
            'User' => ['usuarios.detalhes', [$id]],
            default => [null, []],
        };

        $completo = $rota && Route::has($rota) && ! in_array(null, $parametros, true) && ! in_array('', $parametros, true);

        return $completo ? route($rota, $parametros) : $detalhe;
    }

    /** Uma coluna do registro auditado: dos valores gravados ou, se não mudou, da própria linha. */
    private static function campoDoRegistro(Audit $audit, string $coluna): ?string
    {
        $valor = ((array) $audit->new_values)[$coluna] ?? ((array) $audit->old_values)[$coluna] ?? null;

        if ($valor) {
            return (string) $valor;
        }

        $classe = (string) $audit->auditable_type;
        $chave = $classe.'#'.$audit->auditable_id;

        if (! array_key_exists($chave, self::$cacheLinhas)) {
            self::$cacheLinhas[$chave] = null;

            if (class_exists($classe) && is_subclass_of($classe, Model::class)) {
                $modelo = new $classe;
                self::$cacheLinhas[$chave] = DB::table($modelo->getTable())->where($modelo->getKeyName(), $audit->auditable_id)->first();
            }
        }

        $linha = self::$cacheLinhas[$chave];

        return $linha && ! empty($linha->{$coluna}) ? (string) $linha->{$coluna} : null;
    }

    /**
     * Unidades em que o usuário é Administrador — a do vínculo e as subordinadas.
     *
     * @return list<string>
     */
    private static function unidadesAdministradas(User $user): array
    {
        if (! $user->relationLoaded('perfisAcesso')) {
            $user->load('perfisAcesso');
        }

        $ids = [];
        foreach ($user->perfisAcesso as $perfil) {
            if ($perfil->cod_perfil === PerfilAcesso::ADMIN_UNIDADE && $perfil->pivot->cod_organizacao) {
                $ids = array_merge($ids, Organization::descendentesEProprio($perfil->pivot->cod_organizacao));
            }
        }

        return array_values(array_unique($ids));
    }
}
