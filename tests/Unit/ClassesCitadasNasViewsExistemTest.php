<?php

/**
 * Classe citada por nome completo dentro de uma view Blade só falha quando
 * aquela linha é renderizada — e o teste que gera o PDF pode nem passar por
 * ela (farol sem indicador, relatório sem risco). Foi assim que
 * "AppSupportCorLegivel" (barras perdidas numa substituição em massa) deu 500
 * em cinco relatórios com 62 testes de relatório verdes.
 *
 * A asserção é sobre o texto das views: toda referência \App\...::  existe, e
 * nenhum namespace aparece colado sem as barras.
 */
function arquivosBlade(): array
{
    $iterador = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__, 2).'/resources/views'));
    $arquivos = [];

    foreach ($iterador as $arquivo) {
        if (str_ends_with($arquivo->getFilename(), '.blade.php')) {
            $arquivos[] = $arquivo->getPathname();
        }
    }

    return $arquivos;
}

test('toda classe \\App\\... citada numa view existe', function () {
    $faltando = [];

    foreach (arquivosBlade() as $arquivo) {
        preg_match_all('/\\\\?(App(?:\\\\[A-Za-z_]\w*)+)\s*::/', file_get_contents($arquivo), $achados);

        foreach (array_unique($achados[1]) as $classe) {
            if (! class_exists($classe) && ! enum_exists($classe) && ! interface_exists($classe)) {
                $faltando[] = basename($arquivo).': '.$classe;
            }
        }
    }

    expect($faltando)->toBe([]);
});

test('nenhuma view cita namespace da aplicação sem as barras', function () {
    $colados = [];

    foreach (arquivosBlade() as $arquivo) {
        if (preg_match_all('/(?<![\\\\\w])App(?:Support|Models|Services|Http|Livewire|Concerns|Enums|Policies)[A-Z]\w*/', file_get_contents($arquivo), $achados)) {
            $colados[] = basename($arquivo).': '.implode(', ', array_unique($achados[0]));
        }
    }

    expect($colados)->toBe([]);
});
