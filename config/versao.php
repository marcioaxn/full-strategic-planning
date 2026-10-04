<?php

/**
 * Versão do PEI — Planejamento Estratégico Integrado.
 *
 * O rodapé de todas as telas (com ou sem login) mostra três dados, lidos por
 * App\Support\VersaoAplicacao:
 *
 *  - NÚMERO (aqui): legível, para dizer "o cliente está na 2.0" num chamado.
 *    A linha 2 é a desta base de código, que recebe a migração v1 → v2
 *    (comando migracao:v1-para-v2).
 *  - COMMIT e DATA DO ÚLTIMO DEPLOY: lidos do .git do servidor, que a Infra
 *    atualiza com `git pull`. São automáticos — respondem "o deploy subiu?"
 *    mesmo quando ninguém incrementou o número.
 */

return [

    'numero' => env('APP_VERSAO', '2.1.0'),

];
