<?php

/**
 * Cria o banco `pei_manual` (vazio) com os seis schemas e a extensão pgcrypto.
 *
 * Usa as credenciais da conexão `pgsql` do Laravel (lidas do .env pela própria
 * aplicação). Só executa CREATE DATABASE / CREATE SCHEMA / CREATE EXTENSION:
 * não escreve nada em outro banco.
 *
 * Uso (PowerShell): $env:DB_PORT='5434'; php documentacao/manual/gerador/criar-banco-demonstracao.php
 */

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

const BANCO_DEMONSTRACAO = 'pei_manual';

chdir(__DIR__.'/../../..');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (DB::connection()->getDatabaseName() === BANCO_DEMONSTRACAO) {
    fwrite(STDERR, 'Rode sem $env:DB_DATABASE apontando para '.BANCO_DEMONSTRACAO.' (não se apaga o banco em que se está conectado).'.PHP_EOL);
    exit(1);
}

$existe = DB::selectOne('select 1 as x from pg_database where datname = ?', [BANCO_DEMONSTRACAO]);

// --recriar: apaga SOMENTE o banco de demonstração (nome fixo) e cria de novo.
if ($existe && in_array('--recriar', $argv, true)) {
    DB::statement('DROP DATABASE '.BANCO_DEMONSTRACAO);
    echo BANCO_DEMONSTRACAO.' apagado'.PHP_EOL;
    $existe = null;
}

if (! $existe) {
    DB::statement('CREATE DATABASE '.BANCO_DEMONSTRACAO);
}
echo BANCO_DEMONSTRACAO.($existe ? ' já existia' : ' criado').PHP_EOL;

config(['database.connections.demo' => array_merge(config('database.connections.pgsql'), ['database' => BANCO_DEMONSTRACAO])]);
$demo = DB::connection('demo');

if ($demo->getDatabaseName() !== BANCO_DEMONSTRACAO) {
    fwrite(STDERR, 'Conexão não aponta para '.BANCO_DEMONSTRACAO.PHP_EOL);
    exit(1);
}

$schemas = (array) config('database.connections.pgsql.search_path');
foreach ($schemas as $schema) {
    $demo->statement('CREATE SCHEMA IF NOT EXISTS "'.$schema.'"');
}
$demo->statement('CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA "'.$schemas[0].'"');

echo 'schemas: '.implode(', ', $schemas).' | pgcrypto em '.$schemas[0].PHP_EOL;
