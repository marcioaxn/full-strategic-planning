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
 *    iniciativa (não apaga nem inventa vínculo). No dev: 0. No cliente: o gestor
 *    informou em 04/10/2026 que ainda não há entregas cadastradas — sem aviso
 *    no roteiro, para não complicar o que não acontece.
 *  - StrategicPlanning/2026_10_03_233000_exclui_dependentes_de_ciclos_ja_excluidos —
 *    exclusão LÓGICA (deleted_at) do que pertence a ciclos PEI já excluídos.
 *  - PerformanceIndicators/2026_10_04_000001_amplia_precisao_valores_indicador —
 *    numeric(15,2) → numeric(19,4) em 4 colunas (ALTER TYPE, PG 9.3).
 *  - StrategicPlanning/2026_10_04_120000_tipo_de_encaminhamento_nova_iniciativa —
 *    UPDATE 'Novo Plano' → 'Nova Iniciativa' e troca do CHECK de dsc_tipo
 *    (DROP CONSTRAINT IF EXISTS + ADD CONSTRAINT, PG 9.3).
 *  - 2026_10_04_150000_adiciona_unidade_em_pei_audits — ADD COLUMN anuláveis
 *    (cod_organizacao, bln_institucional) + 2 índices em pei.audits. OBRIGATÓRIA
 *    junto com o código: os resolvedores de config/audit.php gravam nessas
 *    colunas a cada registro auditado (sem ela, todo salvamento auditado falha).
 *  - 2026_10_04_150100_create_pei_tab_atividade_leitura_table — tabela nova.
 * Da 2.0.0 (se o chamado de 03/10 não foi executado): 2026_10_03_120000,
 * 2026_10_03_180000, 2026_10_03_200000, 2026_10_03_220000 (e 2026_09_05_223000
 * em cópia anterior a 05/09); PerfilAcessoSeeder (perfil Consulta);
 * composer.lock (Symfony 7.4, PHP 8.2); JS (app.js, session-timer.js);
 * comando entregas:proteger-anexos; envio de e-mail obrigatório (autocadastro).
 * 2.1.0: sem composer/npm novos, sem comando, fila, cron ou variável de .env
 * novos. config/audit.php, config/fortify.php (autocadastro desligado — rota
 * /register some) e config/versao.php mudaram → optimize:clear (passo 3).
 * Rota nova indicadores.evidencia (dentro do grupo autenticado). Código novo com
 * worker no ar → queue:restart. Rodapé passa a mostrar v2.1.0.
 *
 * ── REGRAS ──────────────────────────────────────────────────────────────────
 * Só o que fazer.
 * Linguagem só técnica: a Infra (outro órgão) não conhece o negócio. Nada de
 * "entrega", "iniciativa", "indicador", "risco" ou caminho de menu no texto que
 * ela lê — só servidor, comandos, arquivos e o que conferir. Nomes de migration
 * e de comando aparecem como identificadores, sem explicação de negócio.
 */

return function (array $f): array {
    ['titulo' => $titulo, 'subtitulo' => $subtitulo, 'secao' => $secao,
        'texto' => $texto, 'comando' => $comando, 'alerta' => $alerta,
        'tabela' => $tabela, 'rodape' => $rodape] = $f;

    $b = [];

    $b[] = $titulo('CHAMADO DE IMPLANTAÇÃO — SISTEMA PEI v2.1.0');
    $b[] = $subtitulo('Atualização de código, dependências e banco de dados · 04/10/2026 · 3 passos');

    $b[] = $tabela([
        ['Sistema', 'Sistema PEI (aplicação Laravel / PHP, banco PostgreSQL)'],
        ['Versão', '2.1.0 (substitui a 2.0.0 do chamado de 03/10/2026)'],
        ['Repositório Git', 'https://github.com/marcioaxn/full-strategic-planning'],
        ['Branch', 'main'],
        ['Prioridade', 'Alta'],
        ['Banco de dados', 'Até 10 migrations (php artisan migrate --force aplica só as pendentes) e 2 seeders. Nenhum registro é apagado'],
        ['Pré-requisito', 'O servidor precisa conseguir enviar e-mail (variáveis MAIL_* do .env, já usadas pela aplicação)'],
    ]);

    $b[] = $alerta('Antes de começar: fazer o backup do banco de dados pelo procedimento usual da equipe.');
    $b[] = $texto('Este chamado substitui o de 03/10/2026. Se aquele já foi executado, siga este normalmente: os passos repetidos não alteram nada do que já foi feito.');

    // ── PASSO 1 ─────────────────────────────────────────────────────────────
    $b[] = $secao('PASSO 1 — Pôr em manutenção e atualizar o código');
    $b[] = $texto('Na pasta do projeto:');
    $b[] = $comando('php artisan down');
    $b[] = $comando('git pull origin main');
    $b[] = $texto('A aplicação lê a versão, o commit e a data do último deploy diretamente da pasta .git (gravada pelo próprio "git pull"). '
        .'A pasta .git deve permanecer no servidor e ser legível pelo usuário do servidor web.');

    // ── PASSO 2 ─────────────────────────────────────────────────────────────
    $b[] = $secao('PASSO 2 — Instalar dependências e atualizar o banco');
    $b[] = $texto('Na pasta do projeto, nesta ordem:');
    $b[] = $comando('composer install --no-dev --optimize-autoloader --no-interaction');
    $b[] = $comando('npm ci');
    $b[] = $comando('npm run build');
    $b[] = $comando('php artisan migrate --force');
    $b[] = $comando('php artisan db:seed --class=PerfilAcessoSeeder --force');
    $b[] = $comando('php artisan db:seed --class=TipoExecucaoSeeder --force');
    $b[] = $comando('php artisan storage:link');
    $b[] = $comando('php artisan entregas:proteger-anexos');
    $b[] = $texto('O "--force" é obrigatório em produção: sem ele o comando pede confirmação e, sem terminal interativo, é cancelado. '
        .'Os dois seeders, o storage:link e o entregas:proteger-anexos podem ser repetidos com segurança: não duplicam nem apagam dados. '
        .'O "entregas:proteger-anexos" move os arquivos enviados pelos usuários de storage/app/public para storage/app/private (fora do acesso público).');

    $b[] = $texto('Limite de upload: a aplicação aceita arquivos PDF de até 20 MB. No php.ini usado pelo servidor web (não o da linha de comando), confirmar ou ajustar:');
    $b[] = $comando('upload_max_filesize = 20M');
    $b[] = $comando('post_max_size = 25M');
    $b[] = $texto('Se houver nginx na frente da aplicação, ajustar também client_max_body_size 25m. Depois de alterar, reiniciar o PHP-FPM ou o Apache. '
        .'A pasta storage/ (inclusive storage/app/private) precisa de permissão de escrita para o usuário do servidor web.');

    // ── PASSO 3 ─────────────────────────────────────────────────────────────
    $b[] = $secao('PASSO 3 — Limpar caches, reiniciar a fila e voltar ao ar');
    $b[] = $comando('php artisan optimize:clear');
    $b[] = $comando('php artisan queue:restart');
    $b[] = $comando('php artisan up');
    $b[] = $texto('O "optimize:clear" é obrigatório nesta versão: três arquivos de configuração mudaram (config/audit.php, config/fortify.php e config/versao.php). '
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
        .'2026_10_03_233000_exclui_dependentes_de_ciclos_ja_excluidos, '
        .'2026_10_04_000001_amplia_precisao_valores_indicador, '
        .'2026_10_04_120000_tipo_de_encaminhamento_nova_iniciativa, '
        .'2026_10_04_150000_adiciona_unidade_em_pei_audits e '
        .'2026_10_04_150100_create_pei_tab_atividade_leitura_table.');
    $b[] = $comando('php artisan entregas:proteger-anexos --simular');
    $b[] = $texto('Deve informar "Seriam movidos: 0 arquivo(s)".');
    $b[] = $comando('php artisan about --only=environment');
    $b[] = $texto('Deve listar o ambiente sem erro: confirma que o código novo carrega com a configuração do servidor.');
    $b[] = $comando('git log -1 --format=%h');
    $b[] = $texto('Abrir no navegador a página de login da aplicação, sem entrar no sistema: o rodapé deve mostrar "v2.1.0 · <commit> · último deploy <data e hora do git pull>", com o mesmo código de 7 caracteres exibido pelo comando acima. '
        .'Se aparecer "deploy não identificado", o usuário do servidor web não está conseguindo ler a pasta .git.');

    $b[] = $secao('Contato do solicitante');
    $b[] = $tabela([
        ['Responsável', 'Marcio Alessandro Xavier Neto'],
        ['E-mail', 'marcio.neto@mdr.gov.br'],
    ]);

    $b[] = $rodape('Chamado Técnico — Sistema PEI v2.1.0 · 04/10/2026');

    return $b;
};
