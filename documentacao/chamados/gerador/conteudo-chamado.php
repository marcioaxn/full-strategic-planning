<?php

/**
 * CONTEÚDO DO CHAMADO — implantação das correções de 03/10/2026.
 *
 * ── O QUE FOI MEDIDO ANTES DE ESCREVER ──────────────────────────────────────
 * Banco: `php artisan migrate --force` basta. As 5 subpastas de
 * database/migrations são registradas no AppServiceProvider (loadMigrationsFrom),
 * e a instalação no cliente foi feita pelo `migracao:v1-para-v2`, que chama o
 * próprio `migrate --force` — a tabela `migrations` de lá sabe o que já rodou.
 * Pendente desde então: 2026_10_03_120000_alinhar_colunas_mitigacao_e_ocorrencia_de_risco
 * (RENAME + ADD COLUMN anulável, compatível com PostgreSQL 9.3) e, se a cópia do
 * cliente for anterior a 05/09, 2026_09_05_223000_add_rota_to_pei_tab_relatorios_gerados
 * (ADD COLUMN anulável). Nenhuma seed nova.
 * composer.lock mudou (Symfony 8 → 7.4, para PHP 8.2) → composer install.
 * JS/SCSS não mudaram, mas npm ci + build saem sempre (lição de 22/09).
 * Código novo com o worker da fila no ar → queue:restart.
 * lang/pt_BR é novo → o .env precisa de APP_LOCALE=pt_BR.
 *
 * Seeders PerfilAcesso/TipoExecucao e storage:link: idempotentes (busca pela
 * chave e atualiza/insere). Repetidos porque não há como confirmar daqui que o
 * complemento de 22/09 foi executado no cliente. Worker e cron são configuração
 * permanente do servidor e não voltam ao roteiro.
 *
 * ── REGRAS ──────────────────────────────────────────────────────────────────
 * Só o que fazer.
 */

return function (array $f): array {
    ['titulo' => $titulo, 'subtitulo' => $subtitulo, 'secao' => $secao,
        'texto' => $texto, 'comando' => $comando, 'alerta' => $alerta,
        'tabela' => $tabela, 'rodape' => $rodape] = $f;

    $b = [];

    $b[] = $titulo('CHAMADO DE IMPLANTAÇÃO — SISTEMA PEI');
    $b[] = $subtitulo('Planejamento Estratégico Integrado · correções de 03/10/2026 · 4 passos');

    $b[] = $tabela([
        ['Sistema', 'Sistema PEI — Planejamento Estratégico Integrado'],
        ['Repositório Git', 'https://github.com/marcioaxn/full-strategic-planning'],
        ['Branch', 'main'],
        ['Prioridade', 'Alta'],
        ['Banco de dados', '1 migration nova (php artisan migrate --force). Renomeia 3 colunas e acrescenta 2 nas tabelas de risco; nenhum dado é apagado'],
        ['Arquivo .env', '3 linhas (Passo 2)'],
    ]);

    $b[] = $alerta('Antes de começar: fazer o backup do banco de dados pelo procedimento usual da equipe.');

    // ── PASSO 1 ─────────────────────────────────────────────────────────────
    $b[] = $secao('PASSO 1 — Pôr em manutenção e atualizar o código');
    $b[] = $texto('Na pasta do projeto:');
    $b[] = $comando('php artisan down');
    $b[] = $comando('git pull origin main');

    // ── PASSO 2 ─────────────────────────────────────────────────────────────
    $b[] = $secao('PASSO 2 — Ajustar o arquivo .env');
    $b[] = $texto('Deixar estas três linhas exatamente assim (alterar se existirem com outro valor, incluir se não existirem):');
    $b[] = $comando('APP_NAME="Sistema PEI"');
    $b[] = $comando('APP_LOCALE=pt_BR');
    $b[] = $comando('APP_FALLBACK_LOCALE=pt_BR');
    $b[] = $texto('A primeira é o nome exibido no topo, no login e no portal público. As duas últimas fazem as mensagens do sistema saírem em português.');

    // ── PASSO 3 ─────────────────────────────────────────────────────────────
    $b[] = $secao('PASSO 3 — Instalar dependências e atualizar o banco');
    $b[] = $texto('Na pasta do projeto, nesta ordem:');
    $b[] = $comando('composer install --no-dev --optimize-autoloader --no-interaction');
    $b[] = $comando('npm ci');
    $b[] = $comando('npm run build');
    $b[] = $comando('php artisan migrate --force');
    $b[] = $comando('php artisan db:seed --class=PerfilAcessoSeeder --force');
    $b[] = $comando('php artisan db:seed --class=TipoExecucaoSeeder --force');
    $b[] = $comando('php artisan storage:link');
    $b[] = $texto('O "--force" é obrigatório em produção: sem ele o comando pede confirmação e, sem terminal interativo, é cancelado. '
        .'Os dois seeders e o storage:link podem ser repetidos com segurança: não duplicam nem apagam dados, e o link, se já existir, é mantido.');

    // ── PASSO 4 ─────────────────────────────────────────────────────────────
    $b[] = $secao('PASSO 4 — Limpar caches, reiniciar a fila e voltar ao ar');
    $b[] = $comando('php artisan optimize:clear');
    $b[] = $comando('php artisan queue:restart');
    $b[] = $comando('php artisan up');
    $b[] = $texto('O "queue:restart" faz o worker da fila (já em execução pelo Supervisor/systemd) recarregar o código novo.');

    // ── CONFERÊNCIA ─────────────────────────────────────────────────────────
    $b[] = $secao('Conferência final');
    $b[] = $comando('php artisan migrate:status');
    $b[] = $texto('A linha "2026_10_03_120000_alinhar_colunas_mitigacao_e_ocorrencia_de_risco" deve aparecer como "Ran", e nenhuma linha como "Pending".');
    $b[] = $texto('No sistema: Gestão de Riscos → abrir um risco → Planos de Mitigação → Novo Plano. O plano deve ser salvo sem erro.');

    $b[] = $secao('Contato do solicitante');
    $b[] = $tabela([
        ['Responsável', 'Marcio Alessandro Xavier Neto'],
        ['E-mail', 'marcio.neto@mdr.gov.br'],
    ]);

    $b[] = $rodape('Chamado Técnico — Sistema PEI · Planejamento Estratégico Integrado · 03/10/2026');

    return $b;
};
