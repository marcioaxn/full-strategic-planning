<?php

/**
 * Hook PreToolUse (Bash) — guarda de comando do Sistema PEI.
 *
 * O sistema de permissões casa por prefixo; ele não enxerga o que vem depois do
 * `&&`, nem o que está embutido em `-c "..."`. Este guarda enxerga: recebe a
 * linha inteira e decide.
 *
 * Duas categorias:
 *   deny — não existe pedido que justifique; o comando não roda.
 *   ask  — pode ser legítimo, mas o gestor decide a cada vez.
 *
 * Devolve permissionDecision no hookSpecificOutput. Silêncio = segue o fluxo
 * normal de permissões.
 */

$payload = json_decode(stream_get_contents(STDIN), true);
$comando = $payload['tool_input']['command'] ?? null;

if (! is_string($comando) || trim($comando) === '') {
    exit(0);
}

$decidir = static function (string $decisao, string $motivo): void {
    echo json_encode([
        'hookSpecificOutput' => [
            'hookEventName' => 'PreToolUse',
            'permissionDecision' => $decisao,
            'permissionDecisionReason' => $motivo,
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    exit(0);
};

// O corpo de um heredoc é DADO, não comando: mensagem de commit, corpo de PR,
// conteúdo de arquivo. Uma mensagem que cite "rm -rf" ao explicar uma regra não
// executa nada — e o guarda barrava o próprio commit que o descrevia.
$analisavel = preg_replace('/<<-?\s*[\'"]?(\w+)[\'"]?\R.*?^\1\R?/ms', ' ', $comando) ?? $comando;

// -------------------------------------------------------------------------
// DENY — nenhum pedido justifica
// -------------------------------------------------------------------------
$proibidos = [
    '/\brm\s+(-[a-zA-Z]*\s+)*-[a-zA-Z]*[rR][a-zA-Z]*f|\brm\s+-[a-zA-Z]*f[a-zA-Z]*[rR]/'
        => 'rm -rf. Se a remoção é mesmo necessária, remova alvo a alvo, com o caminho explícito.',

    '/\bDROP\s+(DATABASE|SCHEMA)\b/i'
        => 'DROP DATABASE/SCHEMA. Mudança estrutural vai como SQL em documentacao/sql/, executado pelo gestor.',

    '/\bmigrate:fresh\b|\bmigrate:reset\b|\bdb:wipe\b/'
        => 'migrate:fresh/reset/db:wipe apagam o banco inteiro. Produção não roda migration, e em dev isso destrói o trabalho em curso.',

    '/\bgit\s+push\b.*--force.*\b(main|master)\b|\bgit\s+push\b.*\b(main|master)\b.*--force/'
        => 'force-push em main. Reescrever a linha principal publicada exige decisão explícita do gestor, fora de um comando composto.',

    '/\bgit\s+reset\s+--hard\b/'
        => 'git reset --hard descarta trabalho não commitado sem recuperação. Prefira git stash, ou peça autorização nominal.',
];

foreach ($proibidos as $regex => $motivo) {
    if (preg_match($regex, $analisavel)) {
        $decidir('deny', "Guarda de comando do Sistema PEI bloqueou: {$motivo}");
    }
}

// -------------------------------------------------------------------------
// ASK — pode ser legítimo, mas o gestor decide a cada vez
// -------------------------------------------------------------------------
$sensiveis = [
    '/\bTRUNCATE\b/i'
        => 'TRUNCATE apaga todas as linhas da tabela.',

    '/\b(UPDATE|DELETE|INSERT)\s+(FROM\s+)?[a-z_]+\./i'
        => 'escrita direta no banco. O CLAUDE.md exige autorização explícita antes de executar, em qualquer ambiente.',

    '/\bdb:seed\b/'
        => 'db:seed grava no banco configurado no .env.',

    '/\bbanco:zerar-dominio\b|\bTruncarBancoSeeder\b/'
        => 'limpeza destrutiva do banco de domínio.',

    '/\bphp\s+artisan\s+migrate\b/'
        => 'migration altera a estrutura do banco.',

    '/\bphp\s+artisan\s+tinker\b/'
        => 'tinker executa código arbitrário contra o banco.',

    '/\bgit\s+push\b/'
        => 'push publica no repositório remoto — que é PÚBLICO neste projeto.',

    '/\bgit\s+filter-branch\b|\bfilter-repo\b/'
        => 'reescrita de histórico troca todos os SHAs.',

    '/\bpsql\b/'
        => 'psql pode conter escrita na mesma linha.',

    '/\bALTER\s+(USER|ROLE)\b/i'
        => 'troca de credencial do banco afeta todo ambiente que usa esse usuário.',
];

foreach ($sensiveis as $regex => $motivo) {
    if (preg_match($regex, $analisavel)) {
        $decidir('ask', "Guarda de comando do Sistema PEI pede confirmação: {$motivo}");
    }
}

exit(0);
