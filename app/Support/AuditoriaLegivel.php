<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OwenIt\Auditing\Models\Audit;

/**
 * Traduz um registro de auditoria para quem não conhece o banco.
 *
 * A trilha (pei.tab_audit) guarda colunas ("dsc_origem"), UUIDs de chaves
 * estrangeiras ("019cba0f-…"), booleanos e datas em formato técnico. Na tela,
 * isso vira "Origem", "Ciclo PEI 2023–2027", "Sim" e "03/10/2026".
 *
 * Nada aqui escreve: só lê, e os nomes de tabela e coluna das consultas vêm de
 * constantes desta classe — nunca do conteúdo auditado.
 */
class AuditoriaLegivel
{
    /** Colunas que não dizem nada ao leitor (carimbos e a própria chave). */
    private const OCULTAS = ['id', 'created_at', 'updated_at', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    /** Colunas cujo valor nunca é mostrado. */
    private const SIGILOSAS = ['password', 'senha', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'api_key', 'service_account_json'];

    /** @var array<string, string> rótulos fixos de colunas frequentes */
    private const ROTULOS = [
        'cod_pei' => 'Ciclo PEI',
        'cod_organizacao' => 'Unidade',
        'rel_cod_organizacao' => 'Unidade superior',
        'cod_objetivo' => 'Objetivo estratégico',
        'cod_objetivo_pai' => 'Objetivo superior',
        'cod_objetivo_vinculado' => 'Objetivo vinculado',
        'cod_perspectiva' => 'Perspectiva',
        'cod_plano_de_acao' => 'Iniciativa',
        'cod_plano_vinculado' => 'Iniciativa vinculada',
        'cod_indicador' => 'Indicador',
        'cod_risco' => 'Risco',
        'cod_entrega' => 'Entrega',
        'cod_entrega_pai' => 'Entrega superior',
        'cod_tipo_execucao' => 'Tipo de execução',
        'cod_perfil' => 'Perfil de acesso',
        'cod_grau_satisfacao' => 'Grau de satisfação',
        'cod_usuario' => 'Usuário',
        'user_id' => 'Usuário',
        'cod_responsavel' => 'Responsável',
        'cod_responsavel_monitoramento' => 'Responsável pelo monitoramento',
        'cod_atividade_cadeia_valor' => 'Atividade da cadeia de valor',
        'num_ods' => 'ODS',
        'cod_prioridade' => 'Prioridade',
        'cod_ppa' => 'Código no PPA',
        'cod_loa' => 'Código na LOA',
        'nom_documento' => 'Nome do documento',
        'dsc_nome_arquivo' => 'Nome do arquivo',
        'dsc_caminho' => 'Local do arquivo',
        'dsc_link' => 'Link',
        'dsc_mime_type' => 'Tipo do arquivo',
        'nom_organizacao' => 'Nome da unidade',
        'sgl_organizacao' => 'Sigla da unidade',
        'dsc_plano_de_acao' => 'Nome da iniciativa',
        'nom_objetivo' => 'Nome do objetivo',
        'nom_indicador' => 'Nome do indicador',
        'dsc_titulo' => 'Título',
        'dsc_missao' => 'Missão',
        'dsc_visao' => 'Visão',
        'dsc_negocio' => 'Negócio',
        'dsc_pei' => 'Descrição do ciclo',
        'num_ano_inicio_pei' => 'Ano de início',
        'num_ano_fim_pei' => 'Ano de término',
        'bln_status' => 'Situação',
        'dsc_status' => 'Situação',
        'deleted_at' => 'Excluído em',
        'name' => 'Nome',
        'email' => 'E-mail',
        'ativo' => 'Ativo',
        'adm' => 'Administrador (legado)',
        'trocarsenha' => 'Troca de senha obrigatória',
        'password' => 'Senha',
        'num_peso' => 'Peso',
        'vlr_orcamento_previsto' => 'Orçamento previsto',
        'num_nivel_risco' => 'Nível do risco',
        'num_probabilidade' => 'Probabilidade',
        'num_impacto' => 'Impacto',
        'num_tamanho_bytes' => 'Tamanho do arquivo',
        'dsc_hash_sha256' => 'Código de integridade do arquivo (SHA-256)',
        'txt_descricao' => 'Descrição',
        'num_documento' => 'Número do documento',
        'dte_documento' => 'Data do documento',
        'num_ano_referencia' => 'Ano de referência',
    ];

    /** Acentos das palavras que aparecem nos nomes de coluna. */
    private const PALAVRAS = [
        'descricao' => 'descrição', 'situacao' => 'situação', 'acao' => 'ação', 'acoes' => 'ações',
        'periodo' => 'período', 'medicao' => 'medição', 'formula' => 'fórmula', 'missao' => 'missão',
        'visao' => 'visão', 'organizacao' => 'organização', 'observacao' => 'observação', 'inicio' => 'início',
        'conclusao' => 'conclusão', 'previsao' => 'previsão', 'orcamento' => 'orçamento', 'responsavel' => 'responsável',
        'comentario' => 'comentário', 'numero' => 'número', 'codigo' => 'código', 'analise' => 'análise',
        'estrategia' => 'estratégia', 'estrategica' => 'estratégica', 'titulo' => 'título', 'historico' => 'histórico',
        'ultima' => 'última', 'referencia' => 'referência', 'negocio' => 'negócio', 'mitigacao' => 'mitigação',
        'ocorrencia' => 'ocorrência', 'avaliacao' => 'avaliação', 'execucao' => 'execução', 'satisfacao' => 'satisfação',
        'unidade' => 'unidade', 'medida' => 'medida', 'termino' => 'término', 'conteudo' => 'conteúdo',
        'publico' => 'público', 'frequencia' => 'frequência', 'reuniao' => 'reunião', 'licao' => 'lição',
        'transformacao' => 'transformação', 'saida' => 'saída', 'prazo' => 'prazo', 'categoria' => 'categoria',
        'intensidade' => 'intensidade', 'vinculo' => 'vínculo', 'area' => 'área', 'ods' => 'ODS', 'pei' => 'PEI',
        'raci' => 'RACI', 'ppa' => 'PPA', 'loa' => 'LOA', 'url' => 'URL', 'ia' => 'IA', 'pdf' => 'PDF',
    ];

    /**
     * Chave estrangeira → [tabela, chave, colunas do nome].
     *
     * @var array<string, array{0: string, 1: string, 2: list<string>}>
     */
    private const CHAVES = [
        'cod_pei' => ['strategic_planning.tab_pei', 'cod_pei', ['dsc_pei', 'num_ano_inicio_pei', 'num_ano_fim_pei']],
        'cod_organizacao' => ['organization.tab_organizacoes', 'cod_organizacao', ['sgl_organizacao', 'nom_organizacao']],
        'rel_cod_organizacao' => ['organization.tab_organizacoes', 'cod_organizacao', ['sgl_organizacao', 'nom_organizacao']],
        'cod_objetivo' => ['strategic_planning.tab_objetivo', 'cod_objetivo', ['nom_objetivo']],
        'cod_objetivo_pai' => ['strategic_planning.tab_objetivo', 'cod_objetivo', ['nom_objetivo']],
        'cod_objetivo_vinculado' => ['strategic_planning.tab_objetivo', 'cod_objetivo', ['nom_objetivo']],
        'cod_perspectiva' => ['strategic_planning.tab_perspectiva', 'cod_perspectiva', ['dsc_perspectiva']],
        'cod_plano_de_acao' => ['action_plan.tab_plano_de_acao', 'cod_plano_de_acao', ['dsc_plano_de_acao']],
        'cod_plano_vinculado' => ['action_plan.tab_plano_de_acao', 'cod_plano_de_acao', ['dsc_plano_de_acao']],
        'cod_indicador' => ['performance_indicators.tab_indicador', 'cod_indicador', ['nom_indicador']],
        'cod_risco' => ['risk_management.tab_risco', 'cod_risco', ['dsc_titulo']],
        'cod_entrega' => ['action_plan.tab_entregas', 'cod_entrega', ['dsc_entrega']],
        'cod_entrega_pai' => ['action_plan.tab_entregas', 'cod_entrega', ['dsc_entrega']],
        'cod_tipo_execucao' => ['action_plan.tab_tipo_execucao', 'cod_tipo_execucao', ['dsc_tipo_execucao']],
        'cod_perfil' => ['organization.tab_perfil_acesso', 'cod_perfil', ['dsc_perfil']],
        'cod_grau_satisfacao' => ['strategic_planning.tab_grau_satisfacao', 'cod_grau_satisfacao', ['dsc_grau_satisfacao']],
        'cod_atividade_cadeia_valor' => ['strategic_planning.tab_atividade_cadeia_valor', 'cod_atividade_cadeia_valor', ['dsc_atividade']],
        'cod_usuario' => ['pei.users', 'id', ['name']],
        'user_id' => ['pei.users', 'id', ['name']],
        'cod_responsavel' => ['pei.users', 'id', ['name']],
        'cod_responsavel_monitoramento' => ['pei.users', 'id', ['name']],
        'num_ods' => ['strategic_planning.tab_ods', 'num_ods', ['num_ods', 'nom_ods']],
    ];

    /** Colunas que, nesta ordem, dão nome a um registro. */
    private const COLUNAS_DE_NOME = [
        'nom_documento', 'nom_objetivo', 'nom_indicador', 'nom_valor', 'nom_tema_norteador', 'nom_cenario', 'nom_parte',
        'dsc_plano_de_acao', 'dsc_titulo', 'dsc_entrega', 'dsc_perspectiva', 'dsc_grau_satisfacao', 'dsc_item',
        'dsc_atividade', 'dsc_estrategia', 'dsc_futuro_almejado', 'dsc_comentario', 'dsc_label', 'dsc_pei',
        'nom_organizacao', 'name', 'dsc_missao',
    ];

    /** @var array<string, ?string> */
    private static array $cacheNomes = [];

    public static function rotuloCampo(string $coluna): string
    {
        if (isset(self::ROTULOS[$coluna])) {
            return self::ROTULOS[$coluna];
        }

        $prefixo = (string) strtok($coluna, '_');
        $semPrefixo = preg_replace('/^(cod|dsc|nom|num|dte|bln|txt|sgl|jsn|vlr|rel)_/', '', $coluna);
        $texto = implode(' ', array_map(fn (string $p): string => self::PALAVRAS[$p] ?? $p, explode('_', (string) $semPrefixo)));

        // O prefixo diz o que o campo é: sem ele, número, data e nome do mesmo
        // assunto viravam três rótulos iguais ("Documento", "Documento"...).
        return match (true) {
            $prefixo === 'dte' => 'Data de '.$texto,
            $prefixo === 'num' && str_starts_with((string) $semPrefixo, 'ano_') => 'Ano de '.preg_replace('/^ano /', '', $texto),
            $prefixo === 'num' => 'Número de '.$texto,
            $prefixo === 'vlr' => 'Valor de '.$texto,
            default => Str::ucfirst($texto),
        };
    }

    /**
     * O valor como o leitor o entende.
     *
     * @return array{texto: string, vazio: bool, cor: ?string}
     */
    public static function valor(string $coluna, mixed $valor): array
    {
        if (in_array($coluna, self::SIGILOSAS, true)) {
            return ['texto' => '(conteúdo sigiloso — não exibido)', 'vazio' => true, 'cor' => null];
        }

        if ($valor === null || $valor === '' || $valor === []) {
            return ['texto' => '(vazio)', 'vazio' => true, 'cor' => null];
        }

        if (is_bool($valor) || (str_starts_with($coluna, 'bln_') && in_array($valor, [0, 1, '0', '1', 't', 'f'], true))
            || in_array($coluna, ['ativo', 'adm', 'trocarsenha'], true)) {
            $verdadeiro = in_array($valor, [true, 1, '1', 't', 'true', 'Sim'], true);

            return ['texto' => $verdadeiro ? 'Sim' : 'Não', 'vazio' => false, 'cor' => null];
        }

        if (is_array($valor)) {
            $linhas = [];
            foreach ($valor as $chave => $item) {
                $item = is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE) : (string) $item;
                $linhas[] = is_int($chave) ? $item : self::rotuloCampo((string) $chave).': '.$item;
            }

            return ['texto' => implode("\n", $linhas), 'vazio' => false, 'cor' => null];
        }

        $texto = (string) $valor;

        if ($coluna === 'num_tamanho_bytes' && is_numeric($texto)) {
            $bytes = (float) $texto;
            $legivel = $bytes >= 1048576 ? number_format($bytes / 1048576, 1, ',', '.').' MB' : ($bytes >= 1024 ? number_format($bytes / 1024, 1, ',', '.').' KB' : (int) $bytes.' bytes');

            return ['texto' => $legivel, 'vazio' => false, 'cor' => null];
        }

        if (isset(self::CHAVES[$coluna])) {
            $nome = self::nomePorChave($coluna, $texto);

            return ['texto' => $nome ?? 'registro não encontrado (excluído) · …'.substr($texto, -6), 'vazio' => $nome === null, 'cor' => null];
        }

        if (preg_match('/^#[0-9a-f]{6}$/i', $texto)) {
            return ['texto' => strtoupper($texto), 'vazio' => false, 'cor' => $texto];
        }

        if ((str_starts_with($coluna, 'dte_') || in_array($coluna, ['deleted_at', 'email_verified_at'], true))
            && preg_match('/^\d{4}-\d{2}-\d{2}/', $texto)) {
            try {
                $data = Carbon::parse($texto)->timezone(config('app.timezone'));

                return ['texto' => $data->format($data->format('H:i:s') === '00:00:00' ? 'd/m/Y' : 'd/m/Y \à\s H:i'), 'vazio' => false, 'cor' => null];
            } catch (\Throwable) {
                // segue como texto
            }
        }

        if (is_numeric($texto) && str_contains($texto, '.') && ! str_starts_with($coluna, 'cod_')) {
            return ['texto' => number_format((float) $texto, 2, ',', '.'), 'vazio' => false, 'cor' => null];
        }

        return ['texto' => $texto, 'vazio' => false, 'cor' => null];
    }

    /**
     * As mudanças do evento, já traduzidas e na ordem em que aparecem no registro.
     *
     * @return list<array{coluna: string, rotulo: string, antes: array, depois: array, longo: bool, trechos: list<array{0: string, 1: string}>}>
     */
    public static function mudancas(Audit $audit): array
    {
        $antes = (array) ($audit->old_values ?? []);
        $depois = (array) ($audit->new_values ?? []);
        $colunas = array_values(array_unique(array_merge(array_keys($antes), array_keys($depois))));

        $lista = [];
        foreach ($colunas as $coluna) {
            // A chave do próprio registro já está no cabeçalho (e nos detalhes técnicos).
            if (in_array($coluna, self::OCULTAS, true) || ($antes[$coluna] ?? $depois[$coluna] ?? null) === $audit->auditable_id) {
                continue;
            }

            $a = self::valor($coluna, $antes[$coluna] ?? null);
            $d = self::valor($coluna, $depois[$coluna] ?? null);
            $longo = mb_strlen($a['texto']) > 90 || mb_strlen($d['texto']) > 90;

            $lista[] = [
                'coluna' => $coluna,
                'rotulo' => self::rotuloCampo($coluna),
                'antes' => $a,
                'depois' => $d,
                'longo' => $longo,
                'trechos' => ($longo && ! $a['vazio'] && ! $d['vazio']) ? self::diferencaPorPalavra($a['texto'], $d['texto']) : [],
            ];
        }

        return $lista;
    }

    /**
     * Diferença palavra a palavra entre dois textos (LCS). Cada trecho é
     * [tipo, texto], com tipo 'igual', 'removido' ou 'incluido'. Texto grande
     * demais volta vazio: a tela mostra antes e depois inteiros.
     *
     * @return list<array{0: string, 1: string}>
     */
    public static function diferencaPorPalavra(string $antes, string $depois): array
    {
        $a = preg_split('/(\s+)/u', $antes, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [];
        $b = preg_split('/(\s+)/u', $depois, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [];
        $n = count($a);
        $m = count($b);

        if ($n * $m > 250000) {
            return [];
        }

        $lcs = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));
        for ($i = $n - 1; $i >= 0; $i--) {
            for ($j = $m - 1; $j >= 0; $j--) {
                $lcs[$i][$j] = $a[$i] === $b[$j] ? $lcs[$i + 1][$j + 1] + 1 : max($lcs[$i + 1][$j], $lcs[$i][$j + 1]);
            }
        }

        $trechos = [];
        $empurra = function (string $tipo, string $texto) use (&$trechos): void {
            $ultimo = array_key_last($trechos);
            if ($ultimo !== null && $trechos[$ultimo][0] === $tipo) {
                $trechos[$ultimo][1] .= $texto;
            } else {
                $trechos[] = [$tipo, $texto];
            }
        };

        $i = 0;
        $j = 0;
        while ($i < $n && $j < $m) {
            if ($a[$i] === $b[$j]) {
                $empurra('igual', $a[$i]);
                $i++;
                $j++;
            } elseif ($lcs[$i + 1][$j] >= $lcs[$i][$j + 1]) {
                $empurra('removido', $a[$i++]);
            } else {
                $empurra('incluido', $b[$j++]);
            }
        }
        while ($i < $n) {
            $empurra('removido', $a[$i++]);
        }
        while ($j < $m) {
            $empurra('incluido', $b[$j++]);
        }

        return $trechos;
    }

    /** Nome legível do registro auditado — mesmo excluído. */
    public static function nomeDoRegistro(Audit $audit): ?string
    {
        $chave = $audit->auditable_type.'#'.$audit->auditable_id;
        if (array_key_exists($chave, self::$cacheNomes)) {
            return self::$cacheNomes[$chave];
        }

        $nome = null;
        $classe = (string) $audit->auditable_type;

        if (class_exists($classe) && is_subclass_of($classe, Model::class)) {
            $modelo = new $classe;
            $linha = DB::table($modelo->getTable())->where($modelo->getKeyName(), $audit->auditable_id)->first();
            $nome = $linha ? self::nomeEmValores((array) $linha) : null;
        }

        $nome ??= self::nomeEmValores((array) ($audit->new_values ?? []))
            ?? self::nomeEmValores((array) ($audit->old_values ?? []));

        return self::$cacheNomes[$chave] = $nome;
    }

    /** O registro ainda existe (não foi excluído nem fisicamente nem logicamente)? */
    public static function registroExiste(Audit $audit): bool
    {
        $classe = (string) $audit->auditable_type;
        if (! class_exists($classe) || ! is_subclass_of($classe, Model::class)) {
            return false;
        }

        $modelo = new $classe;
        $linha = DB::table($modelo->getTable())->where($modelo->getKeyName(), $audit->auditable_id)->first();

        return $linha !== null && empty($linha->deleted_at ?? null);
    }

    /** "alterou", "criou"… — o verbo da frase-resumo. */
    public static function verbo(?string $evento): string
    {
        return match ($evento) {
            'created' => 'criou',
            'updated' => 'alterou',
            'deleted' => 'excluiu',
            'restored' => 'restaurou',
            default => 'registrou '.$evento.' em',
        };
    }

    /**
     * Os valores como gravados, para os detalhes técnicos — com os campos
     * sigilosos mascarados (o JSON bruto também aparece na tela).
     *
     * @param  array<string, mixed>|null  $valores
     */
    public static function brutoMascarado(?array $valores): string
    {
        $valores = (array) $valores;
        foreach ($valores as $coluna => $valor) {
            if (in_array($coluna, self::SIGILOSAS, true) && $valor !== null && $valor !== '') {
                $valores[$coluna] = '(sigiloso)';
            }
        }

        return (string) json_encode($valores ?: new \stdClass, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** "o documento", "a iniciativa" — o tipo do registro com o artigo, para a frase-resumo. */
    public static function comArtigo(string $tipo): string
    {
        $primeira = mb_strtolower(strtok($tipo, ' ') ?: $tipo);
        $feminino = (bool) preg_match('/(a|ção|ência|ância|dade|agem)$/u', $primeira);

        return ($feminino ? 'a ' : 'o ').mb_strtolower($tipo);
    }

    /** "Chrome 128 · Windows" a partir do user agent. */
    public static function navegador(?string $userAgent): string
    {
        $ua = (string) $userAgent;
        if ($ua === '') {
            return 'Não registrado';
        }

        $navegador = match (true) {
            (bool) preg_match('/Edg\/(\d+)/', $ua, $m) => 'Edge '.$m[1],
            (bool) preg_match('/OPR\/(\d+)/', $ua, $m) => 'Opera '.$m[1],
            (bool) preg_match('/Firefox\/(\d+)/', $ua, $m) => 'Firefox '.$m[1],
            (bool) preg_match('/Chrome\/(\d+)/', $ua, $m) => 'Chrome '.$m[1],
            (bool) preg_match('/Version\/(\d+).*Safari/', $ua, $m) => 'Safari '.$m[1],
            str_contains($ua, 'Symfony') || str_contains($ua, 'curl') => 'Sistema / linha de comando',
            default => 'Navegador não identificado',
        };

        $sistema = match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Mac OS') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => null,
        };

        return $sistema ? $navegador.' · '.$sistema : $navegador;
    }

    /** IP como o leitor entende ("::1" é o próprio servidor). */
    public static function origem(?string $ip): string
    {
        return match ((string) $ip) {
            '' => 'Não registrado',
            '::1', '127.0.0.1' => 'O próprio servidor (acesso local) · '.$ip,
            default => (string) $ip,
        };
    }

    private static function nomePorChave(string $coluna, string $valor): ?string
    {
        [$tabela, $pk, $colunasNome] = self::CHAVES[$coluna];
        $chave = $tabela.'#'.$valor;

        if (array_key_exists($chave, self::$cacheNomes)) {
            return self::$cacheNomes[$chave];
        }

        if ($pk !== 'num_ods' && ! Str::isUuid($valor)) {
            return self::$cacheNomes[$chave] = null;
        }

        try {
            $linha = DB::table($tabela)->where($pk, $valor)->first($colunasNome);
        } catch (\Throwable) {
            $linha = null;
        }

        if (! $linha) {
            return self::$cacheNomes[$chave] = null;
        }

        $l = (array) $linha;
        $nome = match ($coluna) {
            'cod_pei' => str_contains((string) ($l['dsc_pei'] ?? ''), (string) $l['num_ano_inicio_pei'])
                ? (string) $l['dsc_pei']
                : trim(($l['dsc_pei'] ?? 'Ciclo').' ('.$l['num_ano_inicio_pei'].'–'.$l['num_ano_fim_pei'].')'),
            'cod_organizacao', 'rel_cod_organizacao' => $l['sgl_organizacao'].' — '.$l['nom_organizacao'],
            'num_ods' => 'ODS '.$l['num_ods'].' — '.$l['nom_ods'],
            default => (string) reset($l),
        };

        return self::$cacheNomes[$chave] = Str::limit($nome, 120);
    }

    /** @param  array<string, mixed>  $valores */
    private static function nomeEmValores(array $valores): ?string
    {
        foreach (self::COLUNAS_DE_NOME as $coluna) {
            if (! empty($valores[$coluna]) && is_string($valores[$coluna])) {
                return Str::limit(trim(strip_tags($valores[$coluna])), 120);
            }
        }

        return null;
    }
}
