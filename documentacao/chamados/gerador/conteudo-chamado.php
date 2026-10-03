<?php

/**
 * CONTEÚDO DO CHAMADO — implantação das correções de 03/10/2026.
 *
 * ── O QUE FOI MEDIDO ANTES DE ESCREVER ──────────────────────────────────────
 * Banco: `php artisan migrate --force` basta. As 5 subpastas de
 * database/migrations são registradas no AppServiceProvider (loadMigrationsFrom),
 * e a instalação no cliente foi feita pelo `migracao:v1-para-v2`, que chama o
 * próprio `migrate --force` — a tabela `migrations` de lá sabe o que já rodou.
 * Pendentes desde então:
 *  - 2026_10_03_120000_alinhar_colunas_mitigacao_e_ocorrencia_de_risco (RENAME +
 *    ADD COLUMN anulável, compatível com PostgreSQL 9.3);
 *  - 2026_10_03_180000_exclui_dependentes_de_iniciativas_ja_excluidas (UPDATE ...
 *    FROM de exclusão lógica — entregas, indicadores e vínculos de Gestor de
 *    iniciativas já excluídas; nada é apagado de fato);
 *  - 2026_10_03_200000_create_tab_documentos_table (CREATE TABLE do acervo de
 *    Documentos em PDF; compatível com PostgreSQL 9.3);
 *  - se a cópia do cliente for anterior a 05/09,
 *    2026_09_05_223000_add_rota_to_pei_tab_relatorios_gerados (ADD COLUMN anulável).
 * Seed NOVA e obrigatória: PerfilAcessoSeeder cria o perfil "Consulta"
 * (c00b...6e). Sem ela, o perfil não aparece no cadastro de usuários.
 * composer.lock mudou (Symfony 8 → 7.4, para PHP 8.2) → composer install.
 * JS mudou (app.js, session-timer.js) → npm ci + npm run build.
 * Código novo com o worker da fila no ar → queue:restart.
 * .env: nada. config/app.php fixa o idioma em pt_BR, troca o "Laravel" do
 * esqueleto por "Sistema PEI" e assume o fuso America/Sao_Paulo; a trava de
 * sessão (config/session.php) usa o cache em banco, cuja tabela já existe.
 *
 * Upload do acervo de Documentos: PDF de até 20 MB (config/livewire.php,
 * temporary_file_upload.rules = max:20480). O padrão do PHP é 2 MB/8 MB: o
 * servidor precisa de upload_max_filesize >= 20M e post_max_size >= 25M (e,
 * com nginx na frente, client_max_body_size >= 25m). O arquivo vai para o disco
 * privado storage/app/private/documentos — não depende de storage:link.
 *
 * TipoExecucaoSeeder e storage:link: idempotentes, repetidos porque não há como
 * confirmar daqui que o complemento de 22/09 foi executado no cliente. Worker e
 * cron são configuração permanente do servidor e não voltam ao roteiro.
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
    $b[] = $subtitulo('Planejamento Estratégico Integrado · correções, Documentos e Salvar como · 03/10/2026 · 3 passos');

    $b[] = $tabela([
        ['Sistema', 'Sistema PEI — Planejamento Estratégico Integrado'],
        ['Repositório Git', 'https://github.com/marcioaxn/full-strategic-planning'],
        ['Branch', 'main'],
        ['Prioridade', 'Alta'],
        ['Banco de dados', '3 migrations novas (php artisan migrate --force) e 1 perfil de acesso novo (seeder). Nenhum dado é apagado'],
    ]);

    $b[] = $alerta('Antes de começar: fazer o backup do banco de dados pelo procedimento usual da equipe.');

    // ── PASSO 1 ─────────────────────────────────────────────────────────────
    $b[] = $secao('PASSO 1 — Pôr em manutenção e atualizar o código');
    $b[] = $texto('Na pasta do projeto:');
    $b[] = $comando('php artisan down');
    $b[] = $comando('git pull origin main');

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
    $b[] = $texto('O "--force" é obrigatório em produção: sem ele o comando pede confirmação e, sem terminal interativo, é cancelado. '
        .'O PerfilAcessoSeeder cadastra o novo perfil "Consulta" (somente leitura). '
        .'Os dois seeders e o storage:link podem ser repetidos com segurança: não duplicam nem apagam dados, e o link, se já existir, é mantido.');

    $b[] = $texto('Limite de envio de arquivos: o novo menu Documentos aceita PDF de até 20 MB. No php.ini usado pelo servidor web (não o da linha de comando), confirmar ou ajustar:');
    $b[] = $comando('upload_max_filesize = 20M');
    $b[] = $comando('post_max_size = 25M');
    $b[] = $texto('Se houver nginx na frente da aplicação, ajustar também client_max_body_size 25m. Depois de alterar, reiniciar o PHP-FPM ou o Apache. '
        .'Os arquivos ficam em storage/app/private/documentos, que precisa de permissão de escrita para o usuário do servidor web, como o restante de storage/.');

    // ── PASSO 3 ─────────────────────────────────────────────────────────────
    $b[] = $secao('PASSO 3 — Limpar caches, reiniciar a fila e voltar ao ar');
    $b[] = $comando('php artisan optimize:clear');
    $b[] = $comando('php artisan queue:restart');
    $b[] = $comando('php artisan up');
    $b[] = $texto('O "queue:restart" faz o worker da fila (já em execução pelo Supervisor/systemd) recarregar o código novo.');

    // ── CONFERÊNCIA ─────────────────────────────────────────────────────────
    $b[] = $secao('Conferência final');
    $b[] = $comando('php artisan migrate:status');
    $b[] = $texto('As linhas "2026_10_03_120000_alinhar_colunas_mitigacao_e_ocorrencia_de_risco", "2026_10_03_180000_exclui_dependentes_de_iniciativas_ja_excluidas" e "2026_10_03_200000_create_tab_documentos_table" devem aparecer como "Ran", e nenhuma linha como "Pending".');
    $b[] = $texto('No sistema: Gestão de Riscos → abrir um risco → Planos de Mitigação → Novo Plano. O plano deve ser salvo sem erro.');
    $b[] = $texto('No sistema: menu Documentos → Enviar documento → escolher um PDF entre 5 MB e 20 MB, preencher nome e tipo e enviar. O documento deve aparecer na lista e abrir pelo botão "Abrir PDF em nova aba".');
    $b[] = $texto('No sistema: Administração → Usuários → Novo Usuário. O campo de perfil deve oferecer a opção "Consulta".');

    $b[] = $secao('Contato do solicitante');
    $b[] = $tabela([
        ['Responsável', 'Marcio Alessandro Xavier Neto'],
        ['E-mail', 'marcio.neto@mdr.gov.br'],
    ]);

    $b[] = $rodape('Chamado Técnico — Sistema PEI · Planejamento Estratégico Integrado · 03/10/2026');

    return $b;
};
