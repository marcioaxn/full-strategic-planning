<?php

/**
 * Nenhum pacote que vai para o servidor pode exigir PHP acima do piso do projeto.
 *
 * O composer.json declara "php": "^8.2", mas fixa config.platform.php em 8.3.0
 * para instalar Pest 4 / PHPUnit 12, que exigem 8.3. Essa fixação faz o
 * Composer resolver TODA a árvore como se o PHP fosse 8.3: um `composer update`
 * pode trazer, sem aviso, um pacote de runtime que exige 8.3 — e o servidor do
 * cliente em 8.2 cai no primeiro `composer install --no-dev`.
 *
 * As ferramentas de desenvolvimento (packages-dev) ficam de fora: não vão para
 * o servidor. A asserção é sobre o composer.lock, que é o que a Infra instala.
 */
function exigenciaAceitaPhp(string $exigencia, string $versao): bool
{
    foreach (preg_split('/\s*\|\|?\s*/', trim($exigencia)) as $alternativa) {
        // Intervalo com hífen ("8.1 - 8.5"): inclusivo nas duas pontas.
        if (preg_match('/^v?(\d+(?:\.\d+)*)\s+-\s+v?(\d+(?:\.\d+)*)$/', trim($alternativa), $intervalo)) {
            $teto = substr_count($intervalo[2], '.') < 2 ? $intervalo[2].'.99' : $intervalo[2];

            if (version_compare($versao, $intervalo[1], '>=') && version_compare($versao, $teto, '<=')) {
                return true;
            }

            continue;
        }

        $aceitaTodas = true;

        foreach (preg_split('/\s*,\s*|\s+/', trim($alternativa)) as $restricao) {
            if ($restricao === '' || $restricao === '*') {
                continue;
            }

            if (! restricaoAceitaPhp($restricao, $versao)) {
                $aceitaTodas = false;
                break;
            }
        }

        if ($aceitaTodas) {
            return true;
        }
    }

    return false;
}

function restricaoAceitaPhp(string $restricao, string $versao): bool
{
    if (! preg_match('/^(\^|~|>=|>|<=|<|=)?\s*v?(\d+(?:\.\d+){0,2})/', $restricao, $m)) {
        return true;
    }

    [$operador, $base] = [$m[1] ?: '=', $m[2]];
    $partes = explode('.', $base);

    return match ($operador) {
        '^' => version_compare($versao, $base, '>=') && (int) explode('.', $versao)[0] === (int) $partes[0],
        '~' => version_compare($versao, $base, '>=')
            && version_compare($versao, count($partes) > 2 ? $partes[0].'.'.($partes[1] + 1) : ($partes[0] + 1).'.0', '<'),
        '>=' => version_compare($versao, $base, '>='),
        '>' => version_compare($versao, $base, '>'),
        '<=' => version_compare($versao, $base, '<='),
        '<' => version_compare($versao, $base, '<'),
        default => str_starts_with($versao, $base),
    };
}

test('o piso de PHP do composer.json continua sendo 8.2', function () {
    $composer = json_decode(file_get_contents(dirname(__DIR__, 2).'/composer.json'), true);

    expect(exigenciaAceitaPhp($composer['require']['php'], '8.2.0'))->toBeTrue();
});

test('nenhum pacote de runtime do composer.lock exige PHP acima de 8.2', function () {
    $lock = json_decode(file_get_contents(dirname(__DIR__, 2).'/composer.lock'), true);

    $recusam = collect($lock['packages'])
        ->filter(fn (array $pacote): bool => isset($pacote['require']['php'])
            && ! exigenciaAceitaPhp($pacote['require']['php'], '8.2.99'))
        ->map(fn (array $pacote): string => "{$pacote['name']} {$pacote['version']} (php {$pacote['require']['php']})")
        ->values()
        ->all();

    expect($recusam)->toBe([], "Pacote de runtime que não roda em PHP 8.2:\n  ".implode("\n  ", $recusam));
});

test('o verificador reconhece as formas de restrição usadas no lock', function (string $exigencia, bool $aceita82) {
    expect(exigenciaAceitaPhp($exigencia, '8.2.99'))->toBe($aceita82);
})->with([
    ['^8.2', true],
    ['>=8.1', true],
    ['^7.4|^8.0', true],
    ['^8.1 || ^8.2', true],
    ['>=7.2 <8.5', true],
    ['8.1 - 8.5', true],
    ['8.3 - 8.5', false],
    ['^8.3', false],
    ['>=8.3', false],
    ['~8.3.0 || ~8.4.0', false],
]);
