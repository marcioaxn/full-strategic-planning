<?php

/**
 * Hook PostToolUse (Write|Edit) — guarda de arquivo do Sistema PEI.
 *
 * Três verificações, na ordem em que falham mais barato:
 *   1. Sintaxe PHP (php -l) — arquivo que não compila derruba a aplicação.
 *   2. Segredo ou padrão proibido — a tabela do CLAUDE.md, executável.
 *   3. Sintaxe posterior ao PostgreSQL 9.3 — passa em dev e quebra em produção.
 *
 * Bloqueia (decision: block) e devolve o motivo, que volta para o modelo
 * corrigir no mesmo turno. Regra escrita depende de alguém lembrar; esta não.
 *
 * Lê o payload do hook em stdin. Sem jq nesta máquina — daí PHP.
 */

$payload = json_decode(stream_get_contents(STDIN), true);

if (! is_array($payload)) {
    exit(0); // Sem payload legível: não é papel do hook adivinhar.
}

$arquivo = $payload['tool_response']['filePath']
    ?? $payload['tool_input']['file_path']
    ?? null;

if (! is_string($arquivo) || $arquivo === '' || ! is_file($arquivo)) {
    exit(0);
}

$conteudo = @file_get_contents($arquivo);

if ($conteudo === false) {
    exit(0);
}

$ext = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));
$relativo = str_replace('\\', '/', $arquivo);

// Caminho relativo à raiz do projeto: é a chave da dívida congelada.
$raiz = str_replace('\\', '/', dirname(__DIR__, 2)).'/';
$chave = str_starts_with($relativo, $raiz) ? substr($relativo, strlen($raiz)) : $relativo;

/**
 * Problemas encontrados, indexados pelo CÓDIGO da regra — é por ele que a
 * dívida congelada perdoa o passivo já existente sem perdoar código novo.
 *
 * @var array<string, string>
 */
$problemas = [];

// -----------------------------------------------------------------------
// 1. Sintaxe PHP
// -----------------------------------------------------------------------
if ($ext === 'php') {
    $saida = [];
    $codigo = 0;
    exec('php -l '.escapeshellarg($arquivo).' 2>&1', $saida, $codigo);

    if ($codigo !== 0) {
        // Sintaxe quebrada NUNCA entra na dívida: arquivo que não compila
        // derruba a aplicação, e não há passivo histórico que justifique.
        $problemas['sintaxe-php'] = 'SINTAXE PHP QUEBRADA: '.trim(implode(' ', $saida));
    }
}

// -----------------------------------------------------------------------
// 2. Padrões proibidos (tabela de segurança do CLAUDE.md)
// -----------------------------------------------------------------------
$proibidos = [
    'tls-withoutverifying' => ['/->withoutVerifying\(\)/', 'TLS desligado (->withoutVerifying) — MITM trivial.'],
    'tls-verify-false' => ["/'verify'\s*=>\s*false/", "TLS desligado ('verify' => false)."],
    'tls-verify-peer' => ['/"verify_peer"\s*=>\s*false/', 'TLS desligado (verify_peer => false).'],
    'sql-injection' => [
        '/DB::(select|statement|raw)\s*\(\s*"[^"]*\'\s*\.\s*\$/',
        'SQL Injection: variável concatenada dentro de aspas no SQL. Use bind (?).',
    ],
    'dompdf-ssrf' => ["/'isRemoteEnabled'\s*=>\s*true/", 'DomPDF com isRemoteEnabled => true — vetor de SSRF.'],
    'trust-proxies' => ['/trustProxies\(\s*at:\s*[\'"]\*[\'"]/', 'trustProxies(at: "*") permite IP spoofing.'],
    // Só as formas inequívocas: "=======" sozinho é sublinhado legítimo em Markdown.
    'conflito-git' => ['/^(?:<{7}|>{7}) /m', 'Marcador de conflito do git no arquivo — quebra a aplicação.'],
];

// Documentação DESCREVE os padrões proibidos — é o oposto de cometê-los. Em
// Markdown só vale o marcador de conflito, que quebra o arquivo em qualquer
// formato. Segredo em .md continua verificado adiante: foi no README que a
// senha do administrador ficou publicada.
// Os próprios guardas contêm os padrões em forma de regex: é onde eles são
// DEFINIDOS. Sem esta isenção o guarda barra a si mesmo a cada edição.
// Sem barra à esquerda: o hook às vezes recebe caminho relativo à raiz.
$ehOProprioGuarda = str_contains($relativo, '.claude/hooks/');

$ehDocumentacao = in_array($ext, ['md', 'markdown'], true) || $ehOProprioGuarda;

foreach ($proibidos as $codigo => [$regex, $motivo]) {
    if ($ehDocumentacao && $codigo !== 'conflito-git') {
        continue;
    }

    if (preg_match($regex, $conteudo)) {
        $problemas[$codigo] = 'PADRÃO PROIBIDO: '.$motivo;
    }
}

// {!! !!} em Blade: XSS armazenado. Só alerta em .blade.php.
if (str_ends_with($relativo, '.blade.php') && preg_match('/\{!!\s*\$/', $conteudo)) {
    $problemas['xss-blade'] = 'XSS: {!! $variavel !!} em Blade. Use {{ }} ou strip_tags() com allowlist. '
        .'Se a origem do dado é comprovadamente segura, diga isso ao gestor antes de manter.';
}

// -----------------------------------------------------------------------
// 3. Segredo em arquivo versionado
// -----------------------------------------------------------------------
$ehExemplo = str_contains($relativo, '.env.example');

if (! $ehExemplo) {
    // Credencial em atributo XML (o caso do phpunit.xml).
    if (preg_match('/<env\s+name="[^"]*(?:PASSWORD|SECRET|TOKEN|KEY)"\s+value="(?!\s*")[^"]{4,}"/i', $conteudo)) {
        $problemas['segredo-xml'] = 'SEGREDO EM ARQUIVO VERSIONADO: credencial literal em atributo XML. '
            .'Foi exatamente assim que a senha do PostgreSQL vazou no repositório público.';
    }

    // Credencial literal em atribuição PHP/dotenv. O valor precisa parecer segredo:
    // regra de validação do Laravel ("required|string"), placeholder e chamada a
    // env()/config() não contam — senão o guarda vira ruído e ninguém o lê.
    $atribuicao = '/(?:PASSWORD|PASSWD|SENHA|SECRET|API_?KEY|ACCESS_KEY|CLIENT_SECRET)'
        .'[\'"]?\s*(?:=>|=|:)\s*[\'"]([^\'"\s]{8,})[\'"]/i';

    // Credencial em tabela ou linha de documentação — a forma que o README usava
    // para publicar a senha do administrador: "| **Senha** | `Pei@...` |".
    // Não há sinal de igual nenhum ali, então a regra de atribuição não a via.
    $emDocumento = '/(?:senha|password|token|secret|chave|api[_ ]?key)\D{0,20}?'
        .'[`"\']([^`"\'\s\/\\\\]{8,})[`"\']/iu';

    // preg_match_all, não preg_match: o README antigo tinha um placeholder inócuo
    // ANTES da senha real. Parar no primeiro casamento fazia o guarda aprovar o
    // arquivo justamente por causa do achado inofensivo.
    if (! $ehOProprioGuarda && preg_match_all($emDocumento, $conteudo, $todos, PREG_SET_ORDER)) {
        foreach ($todos as $d) {
            $valor = $d[1];

            // Só acusa o que PARECE senha: dígito e símbolo, como manda a política
            // do sistema. Sem isso, toda menção a `password` em prosa viraria alarme.
            $pareceSenha = preg_match('/\d/', $valor) && preg_match('/[!@#$%&*?]/', $valor);
            $ehPlaceholderDoc = (bool) preg_match(
                '/^(sua|seu|your|nova|change|troque|xxx|exemplo|\.\.\.|\$|SEED_|env\()/i',
                $valor
            );

            if ($pareceSenha && ! $ehPlaceholderDoc) {
                $problemas['segredo-documento'] = 'POSSÍVEL CREDENCIAL PUBLICADA EM DOCUMENTAÇÃO: '
                    .trim($d[0]).' — foi assim que a senha do administrador ficou no README. '
                    .'Descreva de onde a senha vem; não escreva a senha.';
                break;
            }
        }
    }

    // Também com preg_match_all, pelo mesmo motivo: um placeholder no começo do
    // arquivo não pode blindar uma credencial real mais abaixo.
    if (preg_match_all($atribuicao, $conteudo, $todas, PREG_SET_ORDER)) {
        foreach ($todas as $m) {
            $valor = $m[1];
            $ehRegraDeValidacao = str_contains($valor, '|') || str_contains($valor, ':');
            $ehPlaceholder = (bool) preg_match(
                '/^(sua|seu|your|change|troque|xxx|exemplo|\.\.\.|\$|env\(|config\()/i',
                $valor
            );

            if (! $ehRegraDeValidacao && ! $ehPlaceholder) {
                $problemas['segredo-literal'] = 'POSSÍVEL SEGREDO LITERAL: '.trim($m[0])
                    .' — se for credencial real, mova para o .env (ignorado pelo git). '
                    .'Se for fixture de teste, diga isso ao gestor.';
                break;
            }
        }
    }
}

// -----------------------------------------------------------------------
// 4. Sintaxe posterior ao PostgreSQL 9.3 (produção roda 9.3)
// -----------------------------------------------------------------------
$areaDeBanco = preg_match('#/(app|database)/#', $relativo)
    || preg_match('#/documentacao/sql/.*\.sql$#', $relativo);

if ($areaDeBanco) {
    $pos93 = [
        '/\bON\s+CONFLICT\b/i'              => 'ON CONFLICT (9.5+)',
        '/->upsert\s*\(/'                   => '->upsert() emite ON CONFLICT (9.5+)',
        '/->insertOrIgnore\s*\(/'           => '->insertOrIgnore() emite ON CONFLICT (9.5+)',
        '/\bjson_build_object\b/i'          => 'json_build_object (9.4+)',
        '/\bjsonb\b/i'                      => 'jsonb (9.4+)',
        '/\bGENERATED\s+.*AS\s+IDENTITY\b/i' => 'GENERATED AS IDENTITY (10+)',
        '/\bFILTER\s*\(\s*WHERE\b/i'        => 'FILTER (WHERE ...) (9.4+)',
        '/CREATE\s+INDEX\s+IF\s+NOT\s+EXISTS/i' => 'CREATE INDEX IF NOT EXISTS (9.5+)',
        '/ADD\s+COLUMN\s+IF\s+NOT\s+EXISTS/i'   => 'ADD COLUMN IF NOT EXISTS (9.6+)',
        '/\bST_[A-Za-z]+\s*\(/'             => 'função PostGIS (ST_*)',
    ];

    foreach ($pos93 as $regex => $recurso) {
        if (preg_match($regex, $conteudo)) {
            $codigo = 'pg93-'.preg_replace('/[^a-z0-9]+/', '-', strtolower(strtok($recurso, ' (')));
            $problemas[$codigo] = 'INCOMPATÍVEL COM PostgreSQL 9.3: '.$recurso
                .'. Passa em dev (PG 12) e quebra em produção.';
        }
    }
}

// -----------------------------------------------------------------------
// Resposta
// -----------------------------------------------------------------------
// Dívida congelada: o passivo que já existia quando o guarda nasceu não barra
// edição. A lista SÓ ENCOLHE — regravá-la depois de limpar é correto; regravá-la
// para acomodar código novo sujo é fraudar a própria trava.
// Regenerar: php .claude/hooks/gerar-divida.php
// gerar-divida.php liga PEI_GUARDA_IGNORAR_DIVIDA: sem isso ele rodaria o guarda
// com a dívida já aplicada, não veria problema nenhum e gravaria uma lista vazia
// — apagando o passivo em vez de registrá-lo.
$arquivoDivida = __DIR__.'/divida-congelada.json';
$divida = (getenv('PEI_GUARDA_IGNORAR_DIVIDA') === '1' || ! is_file($arquivoDivida))
    ? []
    : (json_decode((string) file_get_contents($arquivoDivida), true)['arquivos'] ?? []);

$perdoados = $divida[$chave] ?? [];

foreach ($perdoados as $codigoPerdoado) {
    // Sintaxe quebrada nunca é perdoável, esteja onde estiver na lista.
    if ($codigoPerdoado !== 'sintaxe-php') {
        unset($problemas[$codigoPerdoado]);
    }
}

if ($problemas === []) {
    exit(0);
}

$motivo = "Guarda de arquivo do Sistema PEI barrou \"{$chave}\":\n\n"
    .implode("\n", array_map(
        static fn (string $codigo, string $mensagem): string => "- [{$codigo}] {$mensagem}",
        array_keys($problemas),
        $problemas
    ))
    ."\n\nCorrija agora, no mesmo turno. Se algum apontamento for falso positivo, "
    .'diga qual e por quê ao gestor antes de seguir — não contorne o guarda em silêncio, '
    .'e não acrescente o código à dívida congelada para calar o aviso: a lista só encolhe.';

echo json_encode([
    'decision' => 'block',
    'reason' => $motivo,
    'systemMessage' => 'Guarda de arquivo: '.count($problemas).' problema(s) em '.basename($relativo),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

exit(0);
