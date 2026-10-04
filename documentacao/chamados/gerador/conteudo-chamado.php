<?php

/**
 * CONTEÚDO DO CHAMADO — implantação da versão 2.1.0 (04/10/2026).
 *
 * Substitui o chamado de 03/10/2026 (versão 2.0.0). Não se sabe daqui se aquele
 * foi executado no cliente; por isso este roteiro é CUMULATIVO e todos os passos
 * podem ser repetidos sem efeito colateral (migrate só aplica o pendente; seeders,
 * storage:link e entregas:proteger-anexos são idempotentes).
 *
 * ── O QUE FOI MEDIDO ANTES DE ESCREVER ──────────────────────────────────────
 * Banco: `php artisan migrate --force` basta (5 subpastas registradas em
 * loadMigrationsFrom no AppServiceProvider; a instalação no cliente veio do
 * migracao:v1-para-v2, que chama migrate --force).
 * Migrations novas desde a 2.0.0 (git diff origin/main...HEAD, 04/10/2026):
 *  - ActionPlan/2026_10_03_230000_entrega_exige_iniciativa — SET NOT NULL em
 *    tab_entregas.cod_plano_de_acao. PARA com mensagem se houver entrega sem
 *    iniciativa (não apaga nem inventa vínculo). No dev: 0. No cliente: não medido.
 *  - StrategicPlanning/2026_10_03_233000_exclui_dependentes_de_ciclos_ja_excluidos —
 *    exclusão LÓGICA (deleted_at) do que pertence a ciclos PEI já excluídos.
 *  - PerformanceIndicators/2026_10_04_000001_amplia_precisao_valores_indicador —
 *    numeric(15,2) → numeric(19,4) em 4 colunas (ALTER TYPE, PG 9.3).
 * Ordem por nome de arquivo: se a 1ª parar, as duas seguintes não rodam (cada
 * migration roda na própria transação; nada fica pela metade).
 * Da 2.0.0 (se o chamado de 03/10 não foi executado): 2026_10_03_120000,
 * 2026_10_03_180000, 2026_10_03_200000, 2026_10_03_220000 (e 2026_09_05_223000
 * em cópia anterior a 05/09); PerfilAcessoSeeder (perfil Consulta);
 * composer.lock (Symfony 7.4, PHP 8.2); JS (app.js, session-timer.js);
 * comando entregas:proteger-anexos; envio de e-mail obrigatório (autocadastro).
 * 2.1.0: sem composer/npm novos, sem comando, fila, cron ou variável de .env
 * novos. config/audit.php e config/versao.php mudaram → optimize:clear (passo 3).
 * Rota nova indicadores.evidencia (dentro do grupo autenticado). Código novo com
 * worker no ar → queue:restart. Rodapé passa a mostrar v2.1.0.
 *
 * ── REGRAS ──────────────────────────────────────────────────────────────────
 * Só o que fazer.
 */

return function (array $f): array {
    ['titulo' => $titulo, 'subtitulo' => $subtitulo, 'secao' => $secao,
        'texto' => $texto, 'comando' => $comando, 'alerta' => $alerta,
        'tabela' => $tabela, 'rodape' => $rodape] = $f;

    $b = [];

    $b[] = $titulo('CHAMADO DE IMPLANTAÇÃO — SISTEMA PEI v2.1.0');
    $b[] = $subtitulo('Planejamento Estratégico Integrado · correções do teste funcional (indicadores, iniciativas, entregas, riscos, relatórios e usuários) · 04/10/2026 · 3 passos');

    $b[] = $tabela([
        ['Sistema', 'Sistema PEI — Planejamento Estratégico Integrado'],
        ['Versão', '2.1.0 (substitui a 2.0.0 do chamado de 03/10/2026)'],
        ['Repositório Git', 'https://github.com/marcioaxn/full-strategic-planning'],
        ['Branch', 'main'],
        ['Prioridade', 'Alta'],
        ['Banco de dados', 'Até 7 migrations (php artisan migrate --force aplica só as pendentes) e 1 perfil de acesso (seeder). Nenhum dado é apagado de fato: as exclusões são lógicas'],
        ['Pré-requisito', 'O servidor precisa enviar e-mail (configuração MAIL_* do .env, a mesma do "Esqueci minha senha")'],
    ]);

    $b[] = $alerta('Antes de começar: fazer o backup do banco de dados pelo procedimento usual da equipe.');
    $b[] = $texto('Este chamado substitui o de 03/10/2026. Se aquele já foi executado, siga este normalmente: os passos repetidos não alteram nada do que já foi feito.');

    // ── PASSO 1 ─────────────────────────────────────────────────────────────
    $b[] = $secao('PASSO 1 — Pôr em manutenção e atualizar o código');
    $b[] = $texto('Na pasta do projeto:');
    $b[] = $comando('php artisan down');
    $b[] = $comando('git pull origin main');
    $b[] = $texto('O rodapé de todas as telas (inclusive a de login) mostra a versão, o commit e a data e hora do último deploy, lidos da pasta .git que o próprio "git pull" grava. '
        .'A pasta .git deve permanecer no servidor e ser legível pelo usuário do servidor web.');

    // ── PASSO 2 ─────────────────────────────────────────────────────────────
    $b[] = $secao('PASSO 2 — Instalar dependências e atualizar o banco');
    $b[] = $texto('Na pasta do projeto, nesta ordem:');
    $b[] = $comando('composer install --no-dev --optimize-autoloader --no-interaction');
    $b[] = $comando('npm ci');
    $b[] = $comando('npm run build');
    $b[] = $comando('php artisan migrate --force');
    $b[] = $alerta('Se o migrate parar com a mensagem "Há N entrega(s) sem iniciativa em action_plan.tab_entregas", NÃO tente corrigir o banco: '
        .'siga os passos seguintes normalmente (o sistema funciona) e envie a mensagem completa ao solicitante. '
        .'Nada fica pela metade: as migrations que já rodaram estão completas e as restantes serão aplicadas por um novo "php artisan migrate --force" depois que o solicitante tratar essas entregas.');
    $b[] = $comando('php artisan db:seed --class=PerfilAcessoSeeder --force');
    $b[] = $comando('php artisan db:seed --class=TipoExecucaoSeeder --force');
    $b[] = $comando('php artisan storage:link');
    $b[] = $comando('php artisan entregas:proteger-anexos');
    $b[] = $texto('O "--force" é obrigatório em produção: sem ele o comando pede confirmação e, sem terminal interativo, é cancelado. '
        .'Os seeders, o storage:link e o entregas:proteger-anexos podem ser repetidos com segurança: não duplicam nem apagam dados. '
        .'O "entregas:proteger-anexos" move anexos de entrega e evidências de indicadores da pasta pública para a privada (de storage/app/public para storage/app/private).');

    $b[] = $texto('Limite de envio de arquivos (menu Documentos, PDF de até 20 MB). No php.ini usado pelo servidor web (não o da linha de comando), confirmar ou ajustar:');
    $b[] = $comando('upload_max_filesize = 20M');
    $b[] = $comando('post_max_size = 25M');
    $b[] = $texto('Se houver nginx na frente da aplicação, ajustar também client_max_body_size 25m. Depois de alterar, reiniciar o PHP-FPM ou o Apache. '
        .'A pasta storage/ (inclusive storage/app/private) precisa de permissão de escrita para o usuário do servidor web.');

    // ── PASSO 3 ─────────────────────────────────────────────────────────────
    $b[] = $secao('PASSO 3 — Limpar caches, reiniciar a fila e voltar ao ar');
    $b[] = $comando('php artisan optimize:clear');
    $b[] = $comando('php artisan queue:restart');
    $b[] = $comando('php artisan up');
    $b[] = $texto('O "optimize:clear" é obrigatório nesta versão: a configuração da auditoria (config/audit.php) e a da versão (config/versao.php) mudaram. '
        .'O "queue:restart" faz o worker da fila (já em execução pelo Supervisor/systemd) recarregar o código novo. Cron e worker não mudam.');

    // ── CONFERÊNCIA ─────────────────────────────────────────────────────────
    $b[] = $secao('Conferência final');
    $b[] = $comando('php artisan migrate:status');
    $b[] = $texto('Devem aparecer como "Ran", e nenhuma linha como "Pending": '
        .'2026_10_03_120000_alinhar_colunas_mitigacao_e_ocorrencia_de_risco, '
        .'2026_10_03_180000_exclui_dependentes_de_iniciativas_ja_excluidas, '
        .'2026_10_03_200000_create_tab_documentos_table, '
        .'2026_10_03_220000_marca_email_verificado_de_contas_com_perfil, '
        .'2026_10_03_230000_entrega_exige_iniciativa, '
        .'2026_10_03_233000_exclui_dependentes_de_ciclos_ja_excluidos e '
        .'2026_10_04_000001_amplia_precisao_valores_indicador. '
        .'(Exceção: se o migrate parou pelas entregas sem iniciativa, as três últimas aparecem como "Pending" — é o esperado, conforme o aviso do passo 2.)');
    $b[] = $comando('php artisan entregas:proteger-anexos --simular');
    $b[] = $texto('Deve informar "Seriam movidos: 0 arquivo(s)".');
    $b[] = $texto('No sistema: Indicadores → em um indicador, menu ⋮ → Gerenciar Metas → digitar 20000000000,00 no campo da meta. O campo deve mostrar 20.000.000.000,00 e a meta deve ser adicionada.');
    $b[] = $texto('No sistema: Gestão de Riscos → abrir um risco → Planos de Mitigação → Novo Plano, sem preencher o custo. O plano deve ser salvo sem erro.');
    $b[] = $texto('No sistema: Administração → Usuários → Novo Usuário. O campo de perfil deve oferecer a opção "Consulta".');
    $b[] = $comando('git log -1 --format=%h');
    $b[] = $texto('Na tela de login, sem entrar no sistema: o rodapé deve mostrar "v2.1.0 · <commit> · último deploy <data e hora do git pull>", com o mesmo código de 7 caracteres exibido pelo comando acima. '
        .'Se aparecer "deploy não identificado", o usuário do servidor web não está conseguindo ler a pasta .git.');

    $b[] = $secao('Contato do solicitante');
    $b[] = $tabela([
        ['Responsável', 'Marcio Alessandro Xavier Neto'],
        ['E-mail', 'marcio.neto@mdr.gov.br'],
    ]);

    $b[] = $rodape('Chamado Técnico — Sistema PEI v2.1.0 · Planejamento Estratégico Integrado · 04/10/2026');

    return $b;
};
