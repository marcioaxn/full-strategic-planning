# Documentacao completa do Sistema de Planejamento Estrategico - subsidio para upgrade

Data de geracao: 2026-05-22. Fonte: codigo local, `php artisan about`, `php artisan route:list`, arquivos de configuracao e catalogo PostgreSQL consultado via Laravel em modo somente leitura.

## 1. Sumario executivo

Este projeto e um Sistema de Planejamento Estrategico sustentado por Laravel 12, Livewire 3, Jetstream/Fortify, Sanctum, PostgreSQL multi-schema, Vite, Bootstrap 5, Sass e Alpine.js. O backend e sustentado por Eloquent Models, Policies/Gates, Services de dominio, observers e controller de relatorios. O frontend operacional e majoritariamente Livewire + Blade, com componentes compartilhados e views modulares para dashboard, planejamento estrategico, planos de acao, entregas, indicadores, riscos, auditoria, relatorios e administracao.

Ponto critico para upgrade: o banco real foi acessado e a tabela `migrations` possui 67 migrations aplicadas; existem 67 arquivos de migration no disco e nao foram encontrados arquivos pendentes nem migrations aplicadas sem arquivo correspondente. A estrutura real, contudo, distribui tabelas entre schemas PostgreSQL (`public`, `strategic_planning`, `action_plan`, `performance_indicators`, `risk_management`, `organization`) enquanto alguns Models usam nomes sem schema e dependem do `search_path` configurado em `config/database.php`.

## 2. Estado do workspace e limites de verdade

- O nome "Plataforma Visao 360 - Modulo Integra+" apareceu nas instrucoes operacionais do reposititorio (`AGENTS.md`), mas o dominio funcional real identificado pelo codigo, rotas, models, migrations, views e services e Planejamento Estrategico.
- O worktree estava sujo antes desta intervencao, com alteracoes preexistentes em model, lockfiles, migrations e seeders. Nada disso foi revertido ou limpo.
- Esta documentacao nao inventa regras externas ao codigo. Quando a responsabilidade de um metodo nao tem retorno tipado ou contrato explicito, a descricao e inferida pelo nome, assinatura e convencoes Livewire/Eloquent, e deve ser confirmada em leitura fina antes de refatoracao.
- A consulta ao banco foi somente leitura, usando `information_schema`, `pg_indexes` e `migrations`.
- Segredos do `.env` nao sao documentados. Apenas drivers e fatos operacionais nao sensiveis foram considerados.

## 3. Stack e dependencias

### 3.1 Runtime observado

- Laravel: 12.53.0.
- PHP: 8.3.28.
- Livewire instalado em runtime: 3.7.11.
- Composer: 2.6.5.
- Ambiente: local, debug habilitado.
- Database driver: PostgreSQL (`pgsql`).
- Cache, queue e session: `database`.
- Mail: `log`.
- Storage publico nao estava linkado no momento da analise.

### 3.2 Dependencias PHP (`composer.json`)

- `php`: `^8.2`
- `barryvdh/laravel-dompdf`: `*`
- `laravel/framework`: `^12.0`
- `laravel/jetstream`: `^5.3`
- `laravel/sanctum`: `^4.0`
- `laravel/tinker`: `^2.10.1`
- `livewire/livewire`: `^3.6.4`
- `maatwebsite/excel`: `*`
- `owen-it/laravel-auditing`: `^14.0`
- `spatie/laravel-html`: `^3.12`

### 3.3 Dependencias de desenvolvimento PHP

- `fakerphp/faker`: `^1.23`
- `laravel/pail`: `^1.2.2`
- `laravel/pint`: `^1.24`
- `laravel/sail`: `^1.41`
- `mockery/mockery`: `^1.6`
- `nunomaduro/collision`: `^8.6`
- `pestphp/pest`: `^4.1`
- `pestphp/pest-plugin-laravel`: `^4.0`

### 3.4 Dependencias JavaScript/CSS (`package.json`)

- `@alpinejs/mask`: `^3.15.2`
- `@alpinejs/focus`: `^3.15.2`
- `@popperjs/core`: `^2.11.8`
- `alpinejs`: `^3.15.2`
- `axios`: `^1.11.0`
- `bootstrap`: `^5.3.3`
- `bootstrap-icons`: `^1.11.3`
- `concurrently`: `^9.0.1`
- `laravel-vite-plugin`: `^2.0.0`
- `sass`: `^1.81.0`
- `sortablejs`: `^1.15.6`
- `vite`: `^7.0.7`

### 3.5 Scripts

- Composer `setup`: `composer install | @php -r "file_exists('.env') || copy('.env.example', '.env');" | @php artisan key:generate | @php artisan migrate --force | npm install | npm run build`
- Composer `dev`: `Composer\Config::disableProcessTimeout | npx concurrently -c "#93c5fd,#c4b5fd,#fdba74" "php artisan serve" "php artisan queue:listen --tries=1" "npm run dev" --names='server,queue,vite'`
- Composer `test`: `@php artisan config:clear --ansi | @php artisan test`
- Composer `post-autoload-dump`: `Illuminate\Foundation\ComposerScripts::postAutoloadDump | @php artisan package:discover --ansi`
- Composer `post-update-cmd`: `@php artisan vendor:publish --tag=laravel-assets --ansi --force`
- Composer `post-root-package-install`: `@php -r "file_exists('.env') || copy('.env.example', '.env');"`
- Composer `post-create-project-cmd`: `@php artisan key:generate --ansi | @php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');" | @php artisan migrate --graceful --ansi`
- Composer `pre-package-uninstall`: `Illuminate\Foundation\ComposerScripts::prePackageUninstall`
- NPM `build`: `vite build`
- NPM `dev`: `vite`

## 4. Arquitetura de alto nivel

- `routes/web.php`: concentra rotas web; exceto `/` e `/refresh-csrf`, as telas principais ficam protegidas por `auth:sanctum`, middleware de sessao Jetstream e `verified`.
- `routes/api.php`: expoe apenas `GET /api/user` protegido por `auth:sanctum`.
- `app/Livewire`: principal camada de caso de uso e UI stateful.
- `app/Models`: camada de dominio/persistencia, com UUID/string keys predominantes.
- `app/Services`: calculos, relatorios, IA e orientacao PEI.
- `app/Policies`: autorizacao via Gate para organizacoes, usuarios, planos, indicadores e riscos.
- `app/Observers/EntregaObserver.php`: recalcula indicadores automaticamente em eventos de entregas.
- `resources/views`: Blade/Livewire, layouts, relatorios, auth/profile e componentes visuais.
- `database/migrations`: migrations segmentadas por dominio e carregadas explicitamente em `AppServiceProvider`.

## 5. Configuracao Laravel relevante

- `bootstrap/app.php` registra rotas web/api/console e health check `/up`.
- Middleware web recebe `App\Http\Middleware\CheckPasswordChange` via append global.
- Exceptions customizadas tratam autenticacao, CSRF 419, acesso negado 403, 404 e violacao de FK PostgreSQL.
- `AppServiceProvider` define `Schema::defaultStringLength(191)`, carrega migrations por subpastas, registra observer de entregas, registra policies, ajusta `URL::forceRootUrl`, define endpoint Livewire `/livewire/update` e diretivas Blade `@brazil_number` e `@brazil_percent`.
- `JetstreamServiceProvider` configura permissoes de token `create/read/update/delete`, permissao padrao `read`, delecao de usuario via `DeleteUser` e prefetch Vite.

## 6. Banco de dados real

### 6.1 Schemas e tabelas reais

- `action_plan.acoes` - 8 colunas.
- `action_plan.rel_entrega_labels` - 3 colunas.
- `action_plan.rel_entrega_users_responsaveis` - 4 colunas.
- `action_plan.rel_plano_organizacao` - 2 colunas.
- `action_plan.tab_entrega_anexos` - 12 colunas.
- `action_plan.tab_entrega_comentarios` - 9 colunas.
- `action_plan.tab_entrega_historico` - 9 colunas.
- `action_plan.tab_entrega_labels` - 8 colunas.
- `action_plan.tab_entregas` - 18 colunas.
- `action_plan.tab_plano_de_acao` - 16 colunas.
- `action_plan.tab_tipo_execucao` - 5 colunas.
- `organization.rel_organizacao` - 6 colunas.
- `organization.rel_users_tab_organizacoes` - 6 colunas.
- `organization.rel_users_tab_organizacoes_tab_perfil_acesso` - 8 colunas.
- `organization.tab_organizacoes` - 7 colunas.
- `organization.tab_perfil_acesso` - 6 colunas.
- `performance_indicators.rel_indicador_objetivo_organizacao` - 5 colunas.
- `performance_indicators.tab_evolucao_indicador` - 11 colunas.
- `performance_indicators.tab_indicador` - 21 colunas.
- `performance_indicators.tab_linha_base_indicador` - 7 colunas.
- `performance_indicators.tab_meta_por_ano` - 7 colunas.
- `public.audits` - 14 colunas.
- `public.cache` - 3 colunas.
- `public.cache_locks` - 3 colunas.
- `public.failed_jobs` - 7 colunas.
- `public.job_batches` - 10 colunas.
- `public.jobs` - 7 colunas.
- `public.migrations` - 3 colunas.
- `public.password_reset_tokens` - 3 colunas.
- `public.personal_access_tokens` - 10 colunas.
- `public.sessions` - 6 colunas.
- `public.strategic_alerts` - 10 colunas.
- `public.system_settings` - 8 colunas.
- `public.tab_analise_ambiental` - 12 colunas.
- `public.tab_audit` - 14 colunas.
- `public.tab_relatorios_agendados` - 10 colunas.
- `public.tab_relatorios_gerados` - 9 colunas.
- `public.tab_status` - 2 colunas.
- `public.users` - 17 colunas.
- `risk_management.tab_risco` - 17 colunas.
- `risk_management.tab_risco_mitigacao` - 11 colunas.
- `risk_management.tab_risco_objetivo` - 5 colunas.
- `risk_management.tab_risco_ocorrencia` - 10 colunas.
- `strategic_planning.tab_arquivos` - 9 colunas.
- `strategic_planning.tab_atividade_cadeia_valor` - 7 colunas.
- `strategic_planning.tab_futuro_almejado_objetivo` - 6 colunas.
- `strategic_planning.tab_grau_satisfacao` - 10 colunas.
- `strategic_planning.tab_missao_visao_valores` - 8 colunas.
- `strategic_planning.tab_nivel_hierarquico` - 4 colunas.
- `strategic_planning.tab_objetivo` - 8 colunas.
- `strategic_planning.tab_objetivo_comentarios` - 8 colunas.
- `strategic_planning.tab_pei` - 7 colunas.
- `strategic_planning.tab_perspectiva` - 9 colunas.
- `strategic_planning.tab_processos_atividade_cadeia_valor` - 8 colunas.
- `strategic_planning.tab_tema_norteador` - 7 colunas.
- `strategic_planning.tab_valores` - 8 colunas.

### 6.2 Migrations aplicadas versus arquivos

- Migrations aplicadas no banco: 67.
- Arquivos de migration no disco: 67.
- Arquivos nao aplicados: nenhum.
- Migrations aplicadas sem arquivo local correspondente: nenhuma.

### 6.3 Ordem de migrations aplicadas

| Batch | Migration |
|---:|---|
| 1 | `0001_01_01_000000_create_users_table` |
| 1 | `0001_01_01_000001_create_cache_table` |
| 1 | `0001_01_01_000002_create_jobs_table` |
| 1 | `0001_01_01_000003_create_sessions_table` |
| 1 | `0001_01_01_000004_create_pei_schema` |
| 1 | `2014_08_09_230616_create_tab_organizacoes_table` |
| 1 | `2014_10_11_080128_create_tab_perfil_acesso_table` |
| 1 | `2014_10_13_224252_create_rel_users_tab_organizacoes_table` |
| 1 | `2021_09_20_230616_create_rel_organizacao_table` |
| 1 | `2021_10_20_230616_create_acoes_table` |
| 1 | `2021_10_31_171917_create_pei_tab_pei_table` |
| 1 | `2021_11_01_212118_create_pei_tab_missao_visao_valores_table` |
| 1 | `2021_11_08_185623_create_pei_tab_perspectiva_table` |
| 1 | `2021_11_09_094804_create_pei_tab_objetivo_estrategico_table` |
| 1 | `2021_11_09_095359_create_pei_tab_nivel_hierarquico_table` |
| 1 | `2021_11_14_221355_create_pei_tab_tipo_execucao_table` |
| 1 | `2021_11_14_221613_create_pei_tab_plano_de_acao_table` |
| 1 | `2021_11_25_081914_create_rel_users_tab_organizacoes_tab_perfil_acesso_table` |
| 1 | `2021_12_28_232711_create_pei_tab_indicador_table` |
| 1 | `2021_12_28_234715_create_pei_tab_evolucao_indicador_table` |
| 1 | `2021_12_28_235603_create_pei_tab_linha_base_indicador_table` |
| 1 | `2022_01_03_105544_create_pei_tab_meta_por_ano_table` |
| 1 | `2022_01_18_133729_create_tab_audit_table` |
| 1 | `2022_01_26_152500_create_pei_tab_grau_satisfacao_table` |
| 1 | `2022_02_07_100332_create_pei_tab_arquivos_table` |
| 1 | `2023_01_10_164526_create_pei_tab_atividade_cadeia_valor_table` |
| 1 | `2023_01_11_162049_create_pei_tab_processos_atividade_cadeia_valor_table` |
| 1 | `2024_06_18_114518_create_pei_tab_valores_table` |
| 1 | `2024_06_21_172717_create_pei_tab_futuro_almejado_objetivo_estrategico_table` |
| 1 | `2024_07_01_150643_create_pei_rel_indicador_objetivo_estrategico_organizacao_table` |
| 1 | `2024_11_15_215604_create_pei_tab_entregas_table` |
| 1 | `2024_11_21_193856_create_audits_table` |
| 1 | `2024_11_23_104155_create_tab_status_table` |
| 1 | `2025_11_15_221711_create_personal_access_tokens_table` |
| 1 | `2025_12_19_235345_add_theme_color_to_users_table` |
| 1 | `2025_12_24_100000_create_pei_tab_risco_table` |
| 1 | `2025_12_24_100001_create_pei_tab_risco_objetivo_table` |
| 1 | `2025_12_24_100002_create_pei_tab_risco_mitigacao_table` |
| 1 | `2025_12_24_100003_create_pei_tab_risco_ocorrencia_table` |
| 1 | `2025_12_26_161908_alter_rel_users_tab_organizacoes_tab_perfil_acesso_make_cod_plano_nullable` |
| 1 | `2025_12_26_163504_create_tab_analise_ambiental_table` |
| 1 | `2025_12_27_180000_alter_pei_tab_entregas_add_notion_fields` |
| 1 | `2025_12_27_180001_create_pei_tab_entrega_comentarios_table` |
| 1 | `2025_12_27_180002_create_pei_tab_entrega_labels_table` |
| 1 | `2025_12_27_180003_create_pei_rel_entrega_labels_table` |
| 1 | `2025_12_27_180004_create_pei_tab_entrega_anexos_table` |
| 1 | `2025_12_27_180005_create_pei_tab_entrega_historico_table` |
| 1 | `2025_12_27_190000_create_pei_rel_entrega_users_responsaveis_table` |
| 1 | `2025_12_27_193000_add_parent_to_entrega_comentarios_table` |
| 1 | `2025_12_28_185851_rename_objetivo_estrategico_to_objetivo` |
| 1 | `2025_12_28_203425_create_new_tab_objetivo_estrategico` |
| 1 | `2026_01_02_000002_fix_grau_satisfacao_table` |
| 1 | `2026_01_02_000003_fix_grau_satisfacao_columns` |
| 1 | `2026_01_03_185518_move_rel_indicador_objetivo_organizacao_to_performance_indicators_schema` |
| 1 | `2026_01_03_224537_create_system_settings_table` |
| 1 | `2026_01_04_162043_create_strategic_alerts_table` |
| 1 | `2026_01_05_000951_add_pei_and_year_to_grau_satisfacao_table` |
| 1 | `2026_01_05_003343_associate_existing_graus_to_first_pei` |
| 1 | `2026_01_12_231645_create_report_management_tables` |
| 1 | `2026_01_12_235053_create_objective_comments_table` |
| 1 | `2026_01_31_135018_rename_objetivo_estrategico_to_tema_norteador` |
| 1 | `2026_01_31_172616_add_txt_detalhamento_to_tab_plano_de_acao_table` |
| 1 | `2026_02_01_003107_add_dsc_polaridade_and_migrate_legacy_types` |
| 1 | `2026_02_02_023831_create_rel_plano_organizacao_table` |
| 1 | `2026_02_06_160000_add_calculation_type_to_tab_indicador` |
| 1 | `2026_02_06_160001_add_weight_to_tab_entregas` |
| 1 | `2026_02_08_014403_add_weights_to_perspectivas_table` |

### 6.4 Estrutura real por tabela

#### `action_plan.acoes`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `table_id` | `character varying(191) / varchar` | `NO` | `` |
| `user_id` | `uuid / uuid` | `NO` | `` |
| `table` | `character varying(191) / varchar` | `NO` | `` |
| `acao` | `text / text` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571329_599515_1_not_null` coluna ``
- `CHECK` `571329_599515_2_not_null` coluna ``
- `CHECK` `571329_599515_3_not_null` coluna ``
- `CHECK` `571329_599515_4_not_null` coluna ``
- `CHECK` `571329_599515_5_not_null` coluna ``
- `FOREIGN KEY` `action_plan_acoes_user_id_foreign` coluna `user_id`
- `PRIMARY KEY` `acoes_pkey` coluna `id` -> action_plan.acoes.id

Indices:
- `acoes_pkey`: `CREATE UNIQUE INDEX acoes_pkey ON action_plan.acoes USING btree (id)`
- `action_plan_acoes_table_table_id_index`: `CREATE INDEX action_plan_acoes_table_table_id_index ON action_plan.acoes USING btree ("table", table_id)`
- `action_plan_acoes_user_id_index`: `CREATE INDEX action_plan_acoes_user_id_index ON action_plan.acoes USING btree (user_id)`

#### `action_plan.rel_entrega_labels`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_entrega` | `uuid / uuid` | `NO` | `` |
| `cod_label` | `uuid / uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `NO` | `CURRENT_TIMESTAMP` |

Constraints:
- `CHECK` `571329_600063_1_not_null` coluna ``
- `CHECK` `571329_600063_2_not_null` coluna ``
- `CHECK` `571329_600063_3_not_null` coluna ``
- `FOREIGN KEY` `action_plan_rel_entrega_labels_cod_entrega_foreign` coluna `cod_entrega` -> action_plan.tab_entregas.cod_entrega
- `FOREIGN KEY` `action_plan_rel_entrega_labels_cod_label_foreign` coluna `cod_label` -> action_plan.tab_entrega_labels.cod_label
- `PRIMARY KEY` `rel_entrega_labels_pkey` coluna `cod_entrega` -> action_plan.rel_entrega_labels.cod_entrega
- `PRIMARY KEY` `rel_entrega_labels_pkey` coluna `cod_entrega` -> action_plan.rel_entrega_labels.cod_label
- `PRIMARY KEY` `rel_entrega_labels_pkey` coluna `cod_label` -> action_plan.rel_entrega_labels.cod_label
- `PRIMARY KEY` `rel_entrega_labels_pkey` coluna `cod_label` -> action_plan.rel_entrega_labels.cod_entrega

Indices:
- `rel_entrega_labels_pkey`: `CREATE UNIQUE INDEX rel_entrega_labels_pkey ON action_plan.rel_entrega_labels USING btree (cod_entrega, cod_label)`

#### `action_plan.rel_entrega_users_responsaveis`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_entrega` | `uuid / uuid` | `NO` | `` |
| `cod_usuario` | `uuid / uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571329_600115_1_not_null` coluna ``
- `CHECK` `571329_600115_2_not_null` coluna ``
- `FOREIGN KEY` `action_plan_rel_entrega_users_responsaveis_cod_entrega_foreign` coluna `cod_entrega` -> action_plan.tab_entregas.cod_entrega
- `FOREIGN KEY` `action_plan_rel_entrega_users_responsaveis_cod_usuario_foreign` coluna `cod_usuario`
- `PRIMARY KEY` `rel_entrega_users_responsaveis_pkey` coluna `cod_entrega` -> action_plan.rel_entrega_users_responsaveis.cod_usuario
- `PRIMARY KEY` `rel_entrega_users_responsaveis_pkey` coluna `cod_entrega` -> action_plan.rel_entrega_users_responsaveis.cod_entrega
- `PRIMARY KEY` `rel_entrega_users_responsaveis_pkey` coluna `cod_usuario` -> action_plan.rel_entrega_users_responsaveis.cod_entrega
- `PRIMARY KEY` `rel_entrega_users_responsaveis_pkey` coluna `cod_usuario` -> action_plan.rel_entrega_users_responsaveis.cod_usuario

Indices:
- `rel_entrega_users_responsaveis_pkey`: `CREATE UNIQUE INDEX rel_entrega_users_responsaveis_pkey ON action_plan.rel_entrega_users_responsaveis USING btree (cod_entrega, cod_usuario)`

#### `action_plan.rel_plano_organizacao`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_plano_de_acao` | `uuid / uuid` | `NO` | `` |
| `cod_organizacao` | `uuid / uuid` | `NO` | `` |

Constraints:
- `CHECK` `571329_600235_1_not_null` coluna ``
- `CHECK` `571329_600235_2_not_null` coluna ``
- `FOREIGN KEY` `action_plan_rel_plano_organizacao_cod_organizacao_foreign` coluna `cod_organizacao`
- `FOREIGN KEY` `action_plan_rel_plano_organizacao_cod_plano_de_acao_foreign` coluna `cod_plano_de_acao` -> action_plan.tab_plano_de_acao.cod_plano_de_acao
- `PRIMARY KEY` `rel_plano_organizacao_pkey` coluna `cod_plano_de_acao` -> action_plan.rel_plano_organizacao.cod_organizacao
- `PRIMARY KEY` `rel_plano_organizacao_pkey` coluna `cod_plano_de_acao` -> action_plan.rel_plano_organizacao.cod_plano_de_acao
- `PRIMARY KEY` `rel_plano_organizacao_pkey` coluna `cod_organizacao` -> action_plan.rel_plano_organizacao.cod_organizacao
- `PRIMARY KEY` `rel_plano_organizacao_pkey` coluna `cod_organizacao` -> action_plan.rel_plano_organizacao.cod_plano_de_acao

Indices:
- `rel_plano_organizacao_pkey`: `CREATE UNIQUE INDEX rel_plano_organizacao_pkey ON action_plan.rel_plano_organizacao USING btree (cod_plano_de_acao, cod_organizacao)`

#### `action_plan.tab_entrega_anexos`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_anexo` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `cod_entrega` | `uuid / uuid` | `NO` | `` |
| `cod_usuario` | `uuid / uuid` | `NO` | `` |
| `dsc_nome_arquivo` | `character varying(255) / varchar` | `NO` | `` |
| `dsc_caminho` | `character varying(500) / varchar` | `NO` | `` |
| `dsc_mime_type` | `character varying(100) / varchar` | `NO` | `` |
| `num_tamanho_bytes` | `bigint(64,0) / int8` | `NO` | `` |
| `dsc_descricao` | `character varying(500) / varchar` | `YES` | `` |
| `dsc_thumbnail` | `text / text` | `YES` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571329_600079_1_not_null` coluna ``
- `CHECK` `571329_600079_2_not_null` coluna ``
- `CHECK` `571329_600079_3_not_null` coluna ``
- `CHECK` `571329_600079_4_not_null` coluna ``
- `CHECK` `571329_600079_5_not_null` coluna ``
- `CHECK` `571329_600079_6_not_null` coluna ``
- `CHECK` `571329_600079_7_not_null` coluna ``
- `FOREIGN KEY` `action_plan_tab_entrega_anexos_cod_entrega_foreign` coluna `cod_entrega` -> action_plan.tab_entregas.cod_entrega
- `PRIMARY KEY` `tab_entrega_anexos_pkey` coluna `cod_anexo` -> action_plan.tab_entrega_anexos.cod_anexo

Indices:
- `idx_anexos_entrega`: `CREATE INDEX idx_anexos_entrega ON action_plan.tab_entrega_anexos USING btree (cod_entrega)`
- `idx_anexos_mime`: `CREATE INDEX idx_anexos_mime ON action_plan.tab_entrega_anexos USING btree (dsc_mime_type)`
- `idx_anexos_usuario`: `CREATE INDEX idx_anexos_usuario ON action_plan.tab_entrega_anexos USING btree (cod_usuario)`
- `tab_entrega_anexos_pkey`: `CREATE UNIQUE INDEX tab_entrega_anexos_pkey ON action_plan.tab_entrega_anexos USING btree (cod_anexo)`

#### `action_plan.tab_entrega_comentarios`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_comentario` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `cod_entrega` | `uuid / uuid` | `NO` | `` |
| `cod_usuario` | `uuid / uuid` | `NO` | `` |
| `dsc_comentario` | `text / text` | `NO` | `` |
| `json_mencoes` | `jsonb / jsonb` | `YES` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `cod_comentario_pai` | `uuid / uuid` | `YES` | `` |

Constraints:
- `CHECK` `571329_600031_1_not_null` coluna ``
- `CHECK` `571329_600031_2_not_null` coluna ``
- `CHECK` `571329_600031_3_not_null` coluna ``
- `CHECK` `571329_600031_4_not_null` coluna ``
- `FOREIGN KEY` `action_plan_tab_entrega_comentarios_cod_comentario_pai_foreign` coluna `cod_comentario_pai` -> action_plan.tab_entrega_comentarios.cod_comentario
- `FOREIGN KEY` `action_plan_tab_entrega_comentarios_cod_entrega_foreign` coluna `cod_entrega` -> action_plan.tab_entregas.cod_entrega
- `PRIMARY KEY` `tab_entrega_comentarios_pkey` coluna `cod_comentario` -> action_plan.tab_entrega_comentarios.cod_comentario

Indices:
- `idx_comentarios_data`: `CREATE INDEX idx_comentarios_data ON action_plan.tab_entrega_comentarios USING btree (created_at)`
- `idx_comentarios_entrega`: `CREATE INDEX idx_comentarios_entrega ON action_plan.tab_entrega_comentarios USING btree (cod_entrega)`
- `idx_comentarios_pai`: `CREATE INDEX idx_comentarios_pai ON action_plan.tab_entrega_comentarios USING btree (cod_comentario_pai)`
- `idx_comentarios_usuario`: `CREATE INDEX idx_comentarios_usuario ON action_plan.tab_entrega_comentarios USING btree (cod_usuario)`
- `tab_entrega_comentarios_pkey`: `CREATE UNIQUE INDEX tab_entrega_comentarios_pkey ON action_plan.tab_entrega_comentarios USING btree (cod_comentario)`

#### `action_plan.tab_entrega_historico`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_historico` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `cod_entrega` | `uuid / uuid` | `NO` | `` |
| `cod_usuario` | `uuid / uuid` | `YES` | `` |
| `dsc_acao` | `character varying(50) / varchar` | `NO` | `` |
| `dsc_campo` | `character varying(100) / varchar` | `YES` | `` |
| `json_valor_antigo` | `jsonb / jsonb` | `YES` | `` |
| `json_valor_novo` | `jsonb / jsonb` | `YES` | `` |
| `dsc_descricao` | `text / text` | `YES` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `NO` | `CURRENT_TIMESTAMP` |

Constraints:
- `CHECK` `571329_600096_1_not_null` coluna ``
- `CHECK` `571329_600096_2_not_null` coluna ``
- `CHECK` `571329_600096_4_not_null` coluna ``
- `CHECK` `571329_600096_9_not_null` coluna ``
- `FOREIGN KEY` `action_plan_tab_entrega_historico_cod_entrega_foreign` coluna `cod_entrega` -> action_plan.tab_entregas.cod_entrega
- `PRIMARY KEY` `tab_entrega_historico_pkey` coluna `cod_historico` -> action_plan.tab_entrega_historico.cod_historico

Indices:
- `idx_historico_acao`: `CREATE INDEX idx_historico_acao ON action_plan.tab_entrega_historico USING btree (dsc_acao)`
- `idx_historico_data`: `CREATE INDEX idx_historico_data ON action_plan.tab_entrega_historico USING btree (created_at)`
- `idx_historico_entrega`: `CREATE INDEX idx_historico_entrega ON action_plan.tab_entrega_historico USING btree (cod_entrega)`
- `idx_historico_usuario`: `CREATE INDEX idx_historico_usuario ON action_plan.tab_entrega_historico USING btree (cod_usuario)`
- `tab_entrega_historico_pkey`: `CREATE UNIQUE INDEX tab_entrega_historico_pkey ON action_plan.tab_entrega_historico USING btree (cod_historico)`

#### `action_plan.tab_entrega_labels`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_label` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `cod_plano_de_acao` | `uuid / uuid` | `NO` | `` |
| `dsc_label` | `character varying(100) / varchar` | `NO` | `` |
| `dsc_cor` | `character varying(7) / varchar` | `NO` | `'#6366f1'::character varying` |
| `dsc_icone` | `character varying(50) / varchar` | `YES` | `` |
| `num_ordem` | `integer(32,0) / int4` | `NO` | `0` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571329_600048_1_not_null` coluna ``
- `CHECK` `571329_600048_2_not_null` coluna ``
- `CHECK` `571329_600048_3_not_null` coluna ``
- `CHECK` `571329_600048_4_not_null` coluna ``
- `CHECK` `571329_600048_6_not_null` coluna ``
- `FOREIGN KEY` `action_plan_tab_entrega_labels_cod_plano_de_acao_foreign` coluna `cod_plano_de_acao` -> action_plan.tab_plano_de_acao.cod_plano_de_acao
- `PRIMARY KEY` `tab_entrega_labels_pkey` coluna `cod_label` -> action_plan.tab_entrega_labels.cod_label

Indices:
- `idx_labels_ordem`: `CREATE INDEX idx_labels_ordem ON action_plan.tab_entrega_labels USING btree (num_ordem)`
- `idx_labels_plano`: `CREATE INDEX idx_labels_plano ON action_plan.tab_entrega_labels USING btree (cod_plano_de_acao)`
- `tab_entrega_labels_pkey`: `CREATE UNIQUE INDEX tab_entrega_labels_pkey ON action_plan.tab_entrega_labels USING btree (cod_label)`

#### `action_plan.tab_entregas`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_entrega` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `cod_plano_de_acao` | `uuid / uuid` | `YES` | `` |
| `dsc_entrega` | `text / text` | `NO` | `` |
| `bln_status` | `character varying(191) / varchar` | `NO` | `` |
| `dsc_periodo_medicao` | `character varying(191) / varchar` | `NO` | `` |
| `num_nivel_hierarquico_apresentacao` | `smallint(16,0) / int2` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `cod_entrega_pai` | `uuid / uuid` | `YES` | `` |
| `dsc_tipo` | `character varying(50) / varchar` | `NO` | `'task'::character varying` |
| `json_propriedades` | `jsonb / jsonb` | `YES` | `` |
| `dte_prazo` | `date / date` | `YES` | `` |
| `cod_responsavel` | `uuid / uuid` | `YES` | `` |
| `cod_prioridade` | `character varying(20) / varchar` | `NO` | `'media'::character varying` |
| `num_ordem` | `integer(32,0) / int4` | `NO` | `0` |
| `bln_arquivado` | `boolean / bool` | `NO` | `false` |
| `num_peso` | `numeric(8,2) / numeric` | `NO` | `'0'::numeric` |

Constraints:
- `CHECK` `571329_599842_11_not_null` coluna ``
- `CHECK` `571329_599842_15_not_null` coluna ``
- `CHECK` `571329_599842_16_not_null` coluna ``
- `CHECK` `571329_599842_17_not_null` coluna ``
- `CHECK` `571329_599842_18_not_null` coluna ``
- `CHECK` `571329_599842_1_not_null` coluna ``
- `CHECK` `571329_599842_3_not_null` coluna ``
- `CHECK` `571329_599842_4_not_null` coluna ``
- `CHECK` `571329_599842_5_not_null` coluna ``
- `CHECK` `571329_599842_6_not_null` coluna ``
- `FOREIGN KEY` `action_plan_tab_entregas_cod_plano_de_acao_foreign` coluna `cod_plano_de_acao` -> action_plan.tab_plano_de_acao.cod_plano_de_acao
- `FOREIGN KEY` `fk_entregas_entrega_pai` coluna `cod_entrega_pai` -> action_plan.tab_entregas.cod_entrega
- `FOREIGN KEY` `fk_entregas_responsavel` coluna `cod_responsavel`
- `PRIMARY KEY` `tab_entregas_pkey` coluna `cod_entrega` -> action_plan.tab_entregas.cod_entrega

Indices:
- `action_plan_tab_entregas_cod_plano_de_acao_index`: `CREATE INDEX action_plan_tab_entregas_cod_plano_de_acao_index ON action_plan.tab_entregas USING btree (cod_plano_de_acao)`
- `idx_entregas_arquivado`: `CREATE INDEX idx_entregas_arquivado ON action_plan.tab_entregas USING btree (bln_arquivado)`
- `idx_entregas_entrega_pai`: `CREATE INDEX idx_entregas_entrega_pai ON action_plan.tab_entregas USING btree (cod_entrega_pai)`
- `idx_entregas_ordem`: `CREATE INDEX idx_entregas_ordem ON action_plan.tab_entregas USING btree (num_ordem)`
- `idx_entregas_peso`: `CREATE INDEX idx_entregas_peso ON action_plan.tab_entregas USING btree (num_peso)`
- `idx_entregas_prazo`: `CREATE INDEX idx_entregas_prazo ON action_plan.tab_entregas USING btree (dte_prazo)`
- `idx_entregas_prioridade`: `CREATE INDEX idx_entregas_prioridade ON action_plan.tab_entregas USING btree (cod_prioridade)`
- `idx_entregas_responsavel`: `CREATE INDEX idx_entregas_responsavel ON action_plan.tab_entregas USING btree (cod_responsavel)`
- `idx_entregas_tipo`: `CREATE INDEX idx_entregas_tipo ON action_plan.tab_entregas USING btree (dsc_tipo)`
- `tab_entregas_pkey`: `CREATE UNIQUE INDEX tab_entregas_pkey ON action_plan.tab_entregas USING btree (cod_entrega)`

#### `action_plan.tab_plano_de_acao`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_plano_de_acao` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `cod_objetivo` | `uuid / uuid` | `NO` | `` |
| `cod_tipo_execucao` | `uuid / uuid` | `NO` | `` |
| `cod_organizacao` | `uuid / uuid` | `NO` | `` |
| `num_nivel_hierarquico_apresentacao` | `smallint(16,0) / int2` | `NO` | `` |
| `dsc_plano_de_acao` | `text / text` | `NO` | `` |
| `dte_inicio` | `date / date` | `NO` | `` |
| `dte_fim` | `date / date` | `NO` | `` |
| `vlr_orcamento_previsto` | `numeric(15,2) / numeric` | `YES` | `` |
| `bln_status` | `character varying(191) / varchar` | `NO` | `` |
| `cod_ppa` | `character varying(191) / varchar` | `YES` | `` |
| `cod_loa` | `character varying(191) / varchar` | `YES` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `txt_detalhamento` | `text / text` | `YES` | `` |

Constraints:
- `CHECK` `571329_599598_10_not_null` coluna ``
- `CHECK` `571329_599598_1_not_null` coluna ``
- `CHECK` `571329_599598_2_not_null` coluna ``
- `CHECK` `571329_599598_3_not_null` coluna ``
- `CHECK` `571329_599598_4_not_null` coluna ``
- `CHECK` `571329_599598_5_not_null` coluna ``
- `CHECK` `571329_599598_6_not_null` coluna ``
- `CHECK` `571329_599598_7_not_null` coluna ``
- `CHECK` `571329_599598_8_not_null` coluna ``
- `FOREIGN KEY` `action_plan_tab_plano_de_acao_cod_objetivo_foreign` coluna `cod_objetivo`
- `FOREIGN KEY` `action_plan_tab_plano_de_acao_cod_organizacao_foreign` coluna `cod_organizacao`
- `FOREIGN KEY` `action_plan_tab_plano_de_acao_cod_tipo_execucao_foreign` coluna `cod_tipo_execucao` -> action_plan.tab_tipo_execucao.cod_tipo_execucao
- `PRIMARY KEY` `tab_plano_de_acao_pkey` coluna `cod_plano_de_acao` -> action_plan.tab_plano_de_acao.cod_plano_de_acao

Indices:
- `action_plan_tab_plano_de_acao_bln_status_index`: `CREATE INDEX action_plan_tab_plano_de_acao_bln_status_index ON action_plan.tab_plano_de_acao USING btree (bln_status)`
- `action_plan_tab_plano_de_acao_cod_objetivo_index`: `CREATE INDEX action_plan_tab_plano_de_acao_cod_objetivo_index ON action_plan.tab_plano_de_acao USING btree (cod_objetivo)`
- `action_plan_tab_plano_de_acao_cod_organizacao_index`: `CREATE INDEX action_plan_tab_plano_de_acao_cod_organizacao_index ON action_plan.tab_plano_de_acao USING btree (cod_organizacao)`
- `tab_plano_de_acao_pkey`: `CREATE UNIQUE INDEX tab_plano_de_acao_pkey ON action_plan.tab_plano_de_acao USING btree (cod_plano_de_acao)`

#### `action_plan.tab_tipo_execucao`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_tipo_execucao` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `dsc_tipo_execucao` | `character varying(191) / varchar` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571329_599592_1_not_null` coluna ``
- `CHECK` `571329_599592_2_not_null` coluna ``
- `PRIMARY KEY` `tab_tipo_execucao_pkey` coluna `cod_tipo_execucao` -> action_plan.tab_tipo_execucao.cod_tipo_execucao

Indices:
- `tab_tipo_execucao_pkey`: `CREATE UNIQUE INDEX tab_tipo_execucao_pkey ON action_plan.tab_tipo_execucao USING btree (cod_tipo_execucao)`

#### `organization.rel_organizacao`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `cod_organizacao` | `uuid / uuid` | `NO` | `` |
| `rel_cod_organizacao` | `uuid / uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571332_599497_1_not_null` coluna ``
- `CHECK` `571332_599497_2_not_null` coluna ``
- `CHECK` `571332_599497_3_not_null` coluna ``
- `FOREIGN KEY` `organization_rel_organizacao_cod_organizacao_foreign` coluna `cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `FOREIGN KEY` `organization_rel_organizacao_rel_cod_organizacao_foreign` coluna `rel_cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `PRIMARY KEY` `rel_organizacao_pkey` coluna `id` -> organization.rel_organizacao.id
- `UNIQUE` `organization_rel_organizacao_cod_organizacao_rel_cod_organizaca` coluna `cod_organizacao` -> organization.rel_organizacao.cod_organizacao
- `UNIQUE` `organization_rel_organizacao_cod_organizacao_rel_cod_organizaca` coluna `cod_organizacao` -> organization.rel_organizacao.rel_cod_organizacao
- `UNIQUE` `organization_rel_organizacao_cod_organizacao_rel_cod_organizaca` coluna `rel_cod_organizacao` -> organization.rel_organizacao.rel_cod_organizacao
- `UNIQUE` `organization_rel_organizacao_cod_organizacao_rel_cod_organizaca` coluna `rel_cod_organizacao` -> organization.rel_organizacao.cod_organizacao

Indices:
- `organization_rel_organizacao_cod_organizacao_rel_cod_organizaca`: `CREATE UNIQUE INDEX organization_rel_organizacao_cod_organizacao_rel_cod_organizaca ON organization.rel_organizacao USING btree (cod_organizacao, rel_cod_organizacao)`
- `rel_organizacao_pkey`: `CREATE UNIQUE INDEX rel_organizacao_pkey ON organization.rel_organizacao USING btree (id)`

#### `organization.rel_users_tab_organizacoes`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `user_id` | `uuid / uuid` | `NO` | `` |
| `cod_organizacao` | `uuid / uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571332_599479_1_not_null` coluna ``
- `CHECK` `571332_599479_2_not_null` coluna ``
- `CHECK` `571332_599479_3_not_null` coluna ``
- `FOREIGN KEY` `organization_rel_users_tab_organizacoes_cod_organizacao_foreign` coluna `cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `FOREIGN KEY` `organization_rel_users_tab_organizacoes_user_id_foreign` coluna `user_id`
- `PRIMARY KEY` `rel_users_tab_organizacoes_pkey` coluna `id` -> organization.rel_users_tab_organizacoes.id
- `UNIQUE` `organization_rel_users_tab_organizacoes_user_id_cod_organizacao` coluna `user_id` -> organization.rel_users_tab_organizacoes.cod_organizacao
- `UNIQUE` `organization_rel_users_tab_organizacoes_user_id_cod_organizacao` coluna `user_id` -> organization.rel_users_tab_organizacoes.user_id
- `UNIQUE` `organization_rel_users_tab_organizacoes_user_id_cod_organizacao` coluna `cod_organizacao` -> organization.rel_users_tab_organizacoes.user_id
- `UNIQUE` `organization_rel_users_tab_organizacoes_user_id_cod_organizacao` coluna `cod_organizacao` -> organization.rel_users_tab_organizacoes.cod_organizacao

Indices:
- `organization_rel_users_tab_organizacoes_user_id_cod_organizacao`: `CREATE UNIQUE INDEX organization_rel_users_tab_organizacoes_user_id_cod_organizacao ON organization.rel_users_tab_organizacoes USING btree (user_id, cod_organizacao)`
- `rel_users_tab_organizacoes_pkey`: `CREATE UNIQUE INDEX rel_users_tab_organizacoes_pkey ON organization.rel_users_tab_organizacoes USING btree (id)`

#### `organization.rel_users_tab_organizacoes_tab_perfil_acesso`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `user_id` | `uuid / uuid` | `NO` | `` |
| `cod_organizacao` | `uuid / uuid` | `NO` | `` |
| `cod_plano_de_acao` | `uuid / uuid` | `YES` | `` |
| `cod_perfil` | `uuid / uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571332_599625_1_not_null` coluna ``
- `CHECK` `571332_599625_2_not_null` coluna ``
- `CHECK` `571332_599625_3_not_null` coluna ``
- `CHECK` `571332_599625_5_not_null` coluna ``
- `FOREIGN KEY` `fk_uopp_org` coluna `cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `FOREIGN KEY` `fk_uopp_perfil` coluna `cod_perfil` -> organization.tab_perfil_acesso.cod_perfil
- `FOREIGN KEY` `fk_uopp_plano` coluna `cod_plano_de_acao`
- `FOREIGN KEY` `fk_uopp_user` coluna `user_id`
- `PRIMARY KEY` `rel_users_tab_organizacoes_tab_perfil_acesso_pkey` coluna `id` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.id
- `UNIQUE` `rel_uopp_unique` coluna `user_id` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_plano_de_acao
- `UNIQUE` `rel_uopp_unique` coluna `user_id` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_perfil
- `UNIQUE` `rel_uopp_unique` coluna `user_id` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_organizacao
- `UNIQUE` `rel_uopp_unique` coluna `user_id` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.user_id
- `UNIQUE` `rel_uopp_unique` coluna `cod_organizacao` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.user_id
- `UNIQUE` `rel_uopp_unique` coluna `cod_organizacao` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_perfil
- `UNIQUE` `rel_uopp_unique` coluna `cod_organizacao` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_plano_de_acao
- `UNIQUE` `rel_uopp_unique` coluna `cod_organizacao` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_organizacao
- `UNIQUE` `rel_uopp_unique` coluna `cod_plano_de_acao` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.user_id
- `UNIQUE` `rel_uopp_unique` coluna `cod_plano_de_acao` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_plano_de_acao
- `UNIQUE` `rel_uopp_unique` coluna `cod_plano_de_acao` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_organizacao
- `UNIQUE` `rel_uopp_unique` coluna `cod_plano_de_acao` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_perfil
- `UNIQUE` `rel_uopp_unique` coluna `cod_perfil` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_plano_de_acao
- `UNIQUE` `rel_uopp_unique` coluna `cod_perfil` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_organizacao
- `UNIQUE` `rel_uopp_unique` coluna `cod_perfil` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_perfil
- `UNIQUE` `rel_uopp_unique` coluna `cod_perfil` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.user_id

Indices:
- `organization_rel_users_tab_organizacoes_tab_perfil_acesso_cod_o`: `CREATE INDEX organization_rel_users_tab_organizacoes_tab_perfil_acesso_cod_o ON organization.rel_users_tab_organizacoes_tab_perfil_acesso USING btree (cod_organizacao)`
- `organization_rel_users_tab_organizacoes_tab_perfil_acesso_cod_p`: `CREATE INDEX organization_rel_users_tab_organizacoes_tab_perfil_acesso_cod_p ON organization.rel_users_tab_organizacoes_tab_perfil_acesso USING btree (cod_plano_de_acao)`
- `organization_rel_users_tab_organizacoes_tab_perfil_acesso_user_`: `CREATE INDEX organization_rel_users_tab_organizacoes_tab_perfil_acesso_user_ ON organization.rel_users_tab_organizacoes_tab_perfil_acesso USING btree (user_id)`
- `rel_uopp_unique`: `CREATE UNIQUE INDEX rel_uopp_unique ON organization.rel_users_tab_organizacoes_tab_perfil_acesso USING btree (user_id, cod_organizacao, cod_plano_de_acao, cod_perfil)`
- `rel_users_tab_organizacoes_tab_perfil_acesso_pkey`: `CREATE UNIQUE INDEX rel_users_tab_organizacoes_tab_perfil_acesso_pkey ON organization.rel_users_tab_organizacoes_tab_perfil_acesso USING btree (id)`

#### `organization.tab_organizacoes`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_organizacao` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `sgl_organizacao` | `character varying(191) / varchar` | `NO` | `` |
| `nom_organizacao` | `text / text` | `NO` | `` |
| `rel_cod_organizacao` | `uuid / uuid` | `YES` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571332_599461_1_not_null` coluna ``
- `CHECK` `571332_599461_2_not_null` coluna ``
- `CHECK` `571332_599461_3_not_null` coluna ``
- `PRIMARY KEY` `tab_organizacoes_pkey` coluna `cod_organizacao` -> organization.tab_organizacoes.cod_organizacao

Indices:
- `tab_organizacoes_pkey`: `CREATE UNIQUE INDEX tab_organizacoes_pkey ON organization.tab_organizacoes USING btree (cod_organizacao)`

#### `organization.tab_perfil_acesso`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_perfil` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `dsc_perfil` | `text / text` | `NO` | `` |
| `dsc_permissao` | `text / text` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571332_599470_1_not_null` coluna ``
- `CHECK` `571332_599470_2_not_null` coluna ``
- `CHECK` `571332_599470_3_not_null` coluna ``
- `PRIMARY KEY` `tab_perfil_acesso_pkey` coluna `cod_perfil` -> organization.tab_perfil_acesso.cod_perfil

Indices:
- `tab_perfil_acesso_pkey`: `CREATE UNIQUE INDEX tab_perfil_acesso_pkey ON organization.tab_perfil_acesso USING btree (cod_perfil)`

#### `performance_indicators.rel_indicador_objetivo_organizacao`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_indicador` | `uuid / uuid` | `NO` | `` |
| `cod_organizacao` | `uuid / uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571330_599827_1_not_null` coluna ``
- `CHECK` `571330_599827_2_not_null` coluna ``
- `FOREIGN KEY` `fk_rioo_indicador` coluna `cod_indicador` -> performance_indicators.tab_indicador.cod_indicador
- `FOREIGN KEY` `fk_rioo_org` coluna `cod_organizacao`
- `PRIMARY KEY` `rel_indicador_objetivo_estrategico_organizacao_pkey` coluna `cod_indicador` -> performance_indicators.rel_indicador_objetivo_organizacao.cod_organizacao
- `PRIMARY KEY` `rel_indicador_objetivo_estrategico_organizacao_pkey` coluna `cod_indicador` -> performance_indicators.rel_indicador_objetivo_organizacao.cod_indicador
- `PRIMARY KEY` `rel_indicador_objetivo_estrategico_organizacao_pkey` coluna `cod_organizacao` -> performance_indicators.rel_indicador_objetivo_organizacao.cod_organizacao
- `PRIMARY KEY` `rel_indicador_objetivo_estrategico_organizacao_pkey` coluna `cod_organizacao` -> performance_indicators.rel_indicador_objetivo_organizacao.cod_indicador

Indices:
- `rel_indicador_objetivo_estrategico_organizacao_pkey`: `CREATE UNIQUE INDEX rel_indicador_objetivo_estrategico_organizacao_pkey ON performance_indicators.rel_indicador_objetivo_organizacao USING btree (cod_indicador, cod_organizacao)`

#### `performance_indicators.tab_evolucao_indicador`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_evolucao_indicador` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `cod_indicador` | `uuid / uuid` | `NO` | `` |
| `num_ano` | `smallint(16,0) / int2` | `NO` | `` |
| `num_mes` | `smallint(16,0) / int2` | `NO` | `` |
| `vlr_previsto` | `numeric(15,2) / numeric` | `YES` | `` |
| `vlr_realizado` | `numeric(15,2) / numeric` | `YES` | `` |
| `txt_avaliacao` | `text / text` | `YES` | `` |
| `bln_atualizado` | `character varying(191) / varchar` | `YES` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571330_599677_1_not_null` coluna ``
- `CHECK` `571330_599677_2_not_null` coluna ``
- `CHECK` `571330_599677_3_not_null` coluna ``
- `CHECK` `571330_599677_4_not_null` coluna ``
- `FOREIGN KEY` `performance_indicators_tab_evolucao_indicador_cod_indicador_for` coluna `cod_indicador` -> performance_indicators.tab_indicador.cod_indicador
- `PRIMARY KEY` `tab_evolucao_indicador_pkey` coluna `cod_evolucao_indicador` -> performance_indicators.tab_evolucao_indicador.cod_evolucao_indicador

Indices:
- `performance_indicators_tab_evolucao_indicador_cod_indicador_num`: `CREATE INDEX performance_indicators_tab_evolucao_indicador_cod_indicador_num ON performance_indicators.tab_evolucao_indicador USING btree (cod_indicador, num_ano, num_mes)`
- `tab_evolucao_indicador_pkey`: `CREATE UNIQUE INDEX tab_evolucao_indicador_pkey ON performance_indicators.tab_evolucao_indicador USING btree (cod_evolucao_indicador)`

#### `performance_indicators.tab_indicador`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_indicador` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `cod_plano_de_acao` | `uuid / uuid` | `YES` | `` |
| `cod_objetivo` | `uuid / uuid` | `YES` | `` |
| `dsc_tipo` | `text / text` | `NO` | `` |
| `nom_indicador` | `text / text` | `NO` | `` |
| `dsc_indicador` | `text / text` | `NO` | `` |
| `txt_observacao` | `text / text` | `YES` | `` |
| `dsc_meta` | `text / text` | `YES` | `` |
| `dsc_atributos` | `text / text` | `YES` | `` |
| `dsc_referencial_comparativo` | `text / text` | `YES` | `` |
| `dsc_unidade_medida` | `text / text` | `NO` | `` |
| `num_peso` | `smallint(16,0) / int2` | `YES` | `` |
| `bln_acumulado` | `character varying(191) / varchar` | `NO` | `` |
| `dsc_formula` | `text / text` | `YES` | `` |
| `dsc_fonte` | `character varying(191) / varchar` | `YES` | `` |
| `dsc_periodo_medicao` | `character varying(191) / varchar` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `dsc_polaridade` | `character varying(191) / varchar` | `YES` | `` |
| `dsc_calculation_type` | `character varying(20) / varchar` | `NO` | `'manual'::character varying` |

Constraints:
- `CHECK` `571330_599656_11_not_null` coluna ``
- `CHECK` `571330_599656_13_not_null` coluna ``
- `CHECK` `571330_599656_16_not_null` coluna ``
- `CHECK` `571330_599656_1_not_null` coluna ``
- `CHECK` `571330_599656_21_not_null` coluna ``
- `CHECK` `571330_599656_4_not_null` coluna ``
- `CHECK` `571330_599656_5_not_null` coluna ``
- `CHECK` `571330_599656_6_not_null` coluna ``
- `FOREIGN KEY` `performance_indicators_tab_indicador_cod_objetivo_foreign` coluna `cod_objetivo`
- `FOREIGN KEY` `performance_indicators_tab_indicador_cod_plano_de_acao_foreign` coluna `cod_plano_de_acao`
- `PRIMARY KEY` `tab_indicador_pkey` coluna `cod_indicador` -> performance_indicators.tab_indicador.cod_indicador

Indices:
- `idx_indicador_calculation_type`: `CREATE INDEX idx_indicador_calculation_type ON performance_indicators.tab_indicador USING btree (dsc_calculation_type)`
- `performance_indicators_tab_indicador_cod_objetivo_index`: `CREATE INDEX performance_indicators_tab_indicador_cod_objetivo_index ON performance_indicators.tab_indicador USING btree (cod_objetivo)`
- `performance_indicators_tab_indicador_cod_plano_de_acao_index`: `CREATE INDEX performance_indicators_tab_indicador_cod_plano_de_acao_index ON performance_indicators.tab_indicador USING btree (cod_plano_de_acao)`
- `tab_indicador_pkey`: `CREATE UNIQUE INDEX tab_indicador_pkey ON performance_indicators.tab_indicador USING btree (cod_indicador)`

#### `performance_indicators.tab_linha_base_indicador`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_linha_base` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `cod_indicador` | `uuid / uuid` | `NO` | `` |
| `num_linha_base` | `numeric(15,2) / numeric` | `NO` | `` |
| `num_ano` | `smallint(16,0) / int2` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571330_599692_1_not_null` coluna ``
- `CHECK` `571330_599692_2_not_null` coluna ``
- `CHECK` `571330_599692_3_not_null` coluna ``
- `CHECK` `571330_599692_4_not_null` coluna ``
- `FOREIGN KEY` `performance_indicators_tab_linha_base_indicador_cod_indicador_f` coluna `cod_indicador` -> performance_indicators.tab_indicador.cod_indicador
- `PRIMARY KEY` `tab_linha_base_indicador_pkey` coluna `cod_linha_base` -> performance_indicators.tab_linha_base_indicador.cod_linha_base

Indices:
- `performance_indicators_tab_linha_base_indicador_cod_indicador_n`: `CREATE INDEX performance_indicators_tab_linha_base_indicador_cod_indicador_n ON performance_indicators.tab_linha_base_indicador USING btree (cod_indicador, num_ano)`
- `tab_linha_base_indicador_pkey`: `CREATE UNIQUE INDEX tab_linha_base_indicador_pkey ON performance_indicators.tab_linha_base_indicador USING btree (cod_linha_base)`

#### `performance_indicators.tab_meta_por_ano`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_meta_por_ano` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `cod_indicador` | `uuid / uuid` | `NO` | `` |
| `num_ano` | `smallint(16,0) / int2` | `NO` | `` |
| `meta` | `numeric(15,2) / numeric` | `YES` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571330_599704_1_not_null` coluna ``
- `CHECK` `571330_599704_2_not_null` coluna ``
- `CHECK` `571330_599704_3_not_null` coluna ``
- `FOREIGN KEY` `performance_indicators_tab_meta_por_ano_cod_indicador_foreign` coluna `cod_indicador` -> performance_indicators.tab_indicador.cod_indicador
- `PRIMARY KEY` `tab_meta_por_ano_pkey` coluna `cod_meta_por_ano` -> performance_indicators.tab_meta_por_ano.cod_meta_por_ano

Indices:
- `performance_indicators_tab_meta_por_ano_cod_indicador_num_ano_i`: `CREATE INDEX performance_indicators_tab_meta_por_ano_cod_indicador_num_ano_i ON performance_indicators.tab_meta_por_ano USING btree (cod_indicador, num_ano)`
- `tab_meta_por_ano_pkey`: `CREATE UNIQUE INDEX tab_meta_por_ano_pkey ON performance_indicators.tab_meta_por_ano USING btree (cod_meta_por_ano)`

#### `public.audits`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `bigint(64,0) / int8` | `NO` | `nextval('audits_id_seq'::regclass)` |
| `user_type` | `character varying(191) / varchar` | `YES` | `` |
| `user_id` | `uuid / uuid` | `YES` | `` |
| `event` | `character varying(191) / varchar` | `NO` | `` |
| `auditable_type` | `character varying(191) / varchar` | `NO` | `` |
| `auditable_id` | `uuid / uuid` | `NO` | `` |
| `old_values` | `text / text` | `YES` | `` |
| `new_values` | `text / text` | `YES` | `` |
| `url` | `text / text` | `YES` | `` |
| `ip_address` | `inet / inet` | `YES` | `` |
| `user_agent` | `character varying(1023) / varchar` | `YES` | `` |
| `tags` | `character varying(191) / varchar` | `YES` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `2200_599859_1_not_null` coluna ``
- `CHECK` `2200_599859_4_not_null` coluna ``
- `CHECK` `2200_599859_5_not_null` coluna ``
- `CHECK` `2200_599859_6_not_null` coluna ``
- `PRIMARY KEY` `audits_pkey` coluna `id` -> public.audits.id

Indices:
- `audits_auditable_id_auditable_type_index`: `CREATE INDEX audits_auditable_id_auditable_type_index ON public.audits USING btree (auditable_id, auditable_type)`
- `audits_pkey`: `CREATE UNIQUE INDEX audits_pkey ON public.audits USING btree (id)`
- `audits_user_id_user_type_index`: `CREATE INDEX audits_user_id_user_type_index ON public.audits USING btree (user_id, user_type)`

#### `public.cache`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `key` | `character varying(191) / varchar` | `NO` | `` |
| `value` | `text / text` | `NO` | `` |
| `expiration` | `integer(32,0) / int4` | `NO` | `` |

Constraints:
- `CHECK` `2200_599404_1_not_null` coluna ``
- `CHECK` `2200_599404_2_not_null` coluna ``
- `CHECK` `2200_599404_3_not_null` coluna ``
- `PRIMARY KEY` `cache_pkey` coluna `key` -> public.cache.key

Indices:
- `cache_pkey`: `CREATE UNIQUE INDEX cache_pkey ON public.cache USING btree (key)`

#### `public.cache_locks`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `key` | `character varying(191) / varchar` | `NO` | `` |
| `owner` | `character varying(191) / varchar` | `NO` | `` |
| `expiration` | `integer(32,0) / int4` | `NO` | `` |

Constraints:
- `CHECK` `2200_599412_1_not_null` coluna ``
- `CHECK` `2200_599412_2_not_null` coluna ``
- `CHECK` `2200_599412_3_not_null` coluna ``
- `PRIMARY KEY` `cache_locks_pkey` coluna `key` -> public.cache_locks.key

Indices:
- `cache_locks_pkey`: `CREATE UNIQUE INDEX cache_locks_pkey ON public.cache_locks USING btree (key)`

#### `public.failed_jobs`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `bigint(64,0) / int8` | `NO` | `nextval('failed_jobs_id_seq'::regclass)` |
| `uuid` | `character varying(191) / varchar` | `NO` | `` |
| `connection` | `text / text` | `NO` | `` |
| `queue` | `text / text` | `NO` | `` |
| `payload` | `text / text` | `NO` | `` |
| `exception` | `text / text` | `NO` | `` |
| `failed_at` | `timestamp without time zone / timestamp` | `NO` | `CURRENT_TIMESTAMP` |

Constraints:
- `CHECK` `2200_599439_1_not_null` coluna ``
- `CHECK` `2200_599439_2_not_null` coluna ``
- `CHECK` `2200_599439_3_not_null` coluna ``
- `CHECK` `2200_599439_4_not_null` coluna ``
- `CHECK` `2200_599439_5_not_null` coluna ``
- `CHECK` `2200_599439_6_not_null` coluna ``
- `CHECK` `2200_599439_7_not_null` coluna ``
- `PRIMARY KEY` `failed_jobs_pkey` coluna `id` -> public.failed_jobs.id
- `UNIQUE` `failed_jobs_uuid_unique` coluna `uuid` -> public.failed_jobs.uuid

Indices:
- `failed_jobs_pkey`: `CREATE UNIQUE INDEX failed_jobs_pkey ON public.failed_jobs USING btree (id)`
- `failed_jobs_uuid_unique`: `CREATE UNIQUE INDEX failed_jobs_uuid_unique ON public.failed_jobs USING btree (uuid)`

#### `public.job_batches`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `character varying(191) / varchar` | `NO` | `` |
| `name` | `character varying(191) / varchar` | `NO` | `` |
| `total_jobs` | `integer(32,0) / int4` | `NO` | `` |
| `pending_jobs` | `integer(32,0) / int4` | `NO` | `` |
| `failed_jobs` | `integer(32,0) / int4` | `NO` | `` |
| `failed_job_ids` | `text / text` | `NO` | `` |
| `options` | `text / text` | `YES` | `` |
| `cancelled_at` | `integer(32,0) / int4` | `YES` | `` |
| `created_at` | `integer(32,0) / int4` | `NO` | `` |
| `finished_at` | `integer(32,0) / int4` | `YES` | `` |

Constraints:
- `CHECK` `2200_599429_1_not_null` coluna ``
- `CHECK` `2200_599429_2_not_null` coluna ``
- `CHECK` `2200_599429_3_not_null` coluna ``
- `CHECK` `2200_599429_4_not_null` coluna ``
- `CHECK` `2200_599429_5_not_null` coluna ``
- `CHECK` `2200_599429_6_not_null` coluna ``
- `CHECK` `2200_599429_9_not_null` coluna ``
- `PRIMARY KEY` `job_batches_pkey` coluna `id` -> public.job_batches.id

Indices:
- `job_batches_pkey`: `CREATE UNIQUE INDEX job_batches_pkey ON public.job_batches USING btree (id)`

#### `public.jobs`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `bigint(64,0) / int8` | `NO` | `nextval('jobs_id_seq'::regclass)` |
| `queue` | `character varying(191) / varchar` | `NO` | `` |
| `payload` | `text / text` | `NO` | `` |
| `attempts` | `smallint(16,0) / int2` | `NO` | `` |
| `reserved_at` | `integer(32,0) / int4` | `YES` | `` |
| `available_at` | `integer(32,0) / int4` | `NO` | `` |
| `created_at` | `integer(32,0) / int4` | `NO` | `` |

Constraints:
- `CHECK` `2200_599419_1_not_null` coluna ``
- `CHECK` `2200_599419_2_not_null` coluna ``
- `CHECK` `2200_599419_3_not_null` coluna ``
- `CHECK` `2200_599419_4_not_null` coluna ``
- `CHECK` `2200_599419_6_not_null` coluna ``
- `CHECK` `2200_599419_7_not_null` coluna ``
- `PRIMARY KEY` `jobs_pkey` coluna `id` -> public.jobs.id

Indices:
- `jobs_pkey`: `CREATE UNIQUE INDEX jobs_pkey ON public.jobs USING btree (id)`
- `jobs_queue_index`: `CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue)`

#### `public.migrations`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `integer(32,0) / int4` | `NO` | `nextval('migrations_id_seq'::regclass)` |
| `migration` | `character varying(191) / varchar` | `NO` | `` |
| `batch` | `integer(32,0) / int4` | `NO` | `` |

Constraints:
- `CHECK` `2200_599379_1_not_null` coluna ``
- `CHECK` `2200_599379_2_not_null` coluna ``
- `CHECK` `2200_599379_3_not_null` coluna ``
- `PRIMARY KEY` `migrations_pkey` coluna `id` -> public.migrations.id

Indices:
- `migrations_pkey`: `CREATE UNIQUE INDEX migrations_pkey ON public.migrations USING btree (id)`

#### `public.password_reset_tokens`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `email` | `character varying(191) / varchar` | `NO` | `` |
| `token` | `character varying(191) / varchar` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `2200_599399_1_not_null` coluna ``
- `CHECK` `2200_599399_2_not_null` coluna ``
- `PRIMARY KEY` `password_reset_tokens_pkey` coluna `email` -> public.password_reset_tokens.email

Indices:
- `password_reset_tokens_pkey`: `CREATE UNIQUE INDEX password_reset_tokens_pkey ON public.password_reset_tokens USING btree (email)`

#### `public.personal_access_tokens`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `uuid / uuid` | `NO` | `` |
| `tokenable_type` | `character varying(191) / varchar` | `NO` | `` |
| `tokenable_id` | `uuid / uuid` | `NO` | `` |
| `name` | `text / text` | `NO` | `` |
| `token` | `character varying(64) / varchar` | `NO` | `` |
| `abilities` | `text / text` | `YES` | `` |
| `last_used_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `expires_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `2200_599879_1_not_null` coluna ``
- `CHECK` `2200_599879_2_not_null` coluna ``
- `CHECK` `2200_599879_3_not_null` coluna ``
- `CHECK` `2200_599879_4_not_null` coluna ``
- `CHECK` `2200_599879_5_not_null` coluna ``
- `PRIMARY KEY` `personal_access_tokens_pkey` coluna `id` -> public.personal_access_tokens.id
- `UNIQUE` `personal_access_tokens_token_unique` coluna `token` -> public.personal_access_tokens.token

Indices:
- `personal_access_tokens_expires_at_index`: `CREATE INDEX personal_access_tokens_expires_at_index ON public.personal_access_tokens USING btree (expires_at)`
- `personal_access_tokens_pkey`: `CREATE UNIQUE INDEX personal_access_tokens_pkey ON public.personal_access_tokens USING btree (id)`
- `personal_access_tokens_token_unique`: `CREATE UNIQUE INDEX personal_access_tokens_token_unique ON public.personal_access_tokens USING btree (token)`
- `personal_access_tokens_tokenable_type_tokenable_id_index`: `CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id)`

#### `public.sessions`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `character varying(191) / varchar` | `NO` | `` |
| `user_id` | `uuid / uuid` | `YES` | `` |
| `ip_address` | `character varying(45) / varchar` | `YES` | `` |
| `user_agent` | `text / text` | `YES` | `` |
| `payload` | `text / text` | `NO` | `` |
| `last_activity` | `integer(32,0) / int4` | `NO` | `` |

Constraints:
- `CHECK` `2200_599451_1_not_null` coluna ``
- `CHECK` `2200_599451_5_not_null` coluna ``
- `CHECK` `2200_599451_6_not_null` coluna ``
- `PRIMARY KEY` `sessions_pkey` coluna `id` -> public.sessions.id

Indices:
- `sessions_last_activity_index`: `CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity)`
- `sessions_pkey`: `CREATE UNIQUE INDEX sessions_pkey ON public.sessions USING btree (id)`
- `sessions_user_id_index`: `CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id)`

#### `public.strategic_alerts`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `uuid / uuid` | `NO` | `` |
| `user_id` | `uuid / uuid` | `NO` | `` |
| `cod_organizacao` | `uuid / uuid` | `YES` | `` |
| `title` | `character varying(191) / varchar` | `NO` | `` |
| `message` | `text / text` | `NO` | `` |
| `icon` | `character varying(191) / varchar` | `NO` | `'bi-info-circle'::character varying` |
| `type` | `character varying(191) / varchar` | `NO` | `'info'::character varying` |
| `read_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `2200_600169_1_not_null` coluna ``
- `CHECK` `2200_600169_2_not_null` coluna ``
- `CHECK` `2200_600169_4_not_null` coluna ``
- `CHECK` `2200_600169_5_not_null` coluna ``
- `CHECK` `2200_600169_6_not_null` coluna ``
- `CHECK` `2200_600169_7_not_null` coluna ``
- `FOREIGN KEY` `strategic_alerts_user_id_foreign` coluna `user_id` -> public.users.id
- `PRIMARY KEY` `strategic_alerts_pkey` coluna `id` -> public.strategic_alerts.id

Indices:
- `strategic_alerts_pkey`: `CREATE UNIQUE INDEX strategic_alerts_pkey ON public.strategic_alerts USING btree (id)`

#### `public.system_settings`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `bigint(64,0) / int8` | `NO` | `nextval('system_settings_id_seq'::regclass)` |
| `key` | `character varying(191) / varchar` | `NO` | `` |
| `value` | `text / text` | `YES` | `` |
| `type` | `character varying(191) / varchar` | `NO` | `'string'::character varying` |
| `is_encrypted` | `boolean / bool` | `NO` | `false` |
| `description` | `character varying(191) / varchar` | `YES` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `2200_600156_1_not_null` coluna ``
- `CHECK` `2200_600156_2_not_null` coluna ``
- `CHECK` `2200_600156_4_not_null` coluna ``
- `CHECK` `2200_600156_5_not_null` coluna ``
- `PRIMARY KEY` `system_settings_pkey` coluna `id` -> public.system_settings.id
- `UNIQUE` `system_settings_key_unique` coluna `key` -> public.system_settings.key

Indices:
- `system_settings_key_unique`: `CREATE UNIQUE INDEX system_settings_key_unique ON public.system_settings USING btree (key)`
- `system_settings_pkey`: `CREATE UNIQUE INDEX system_settings_pkey ON public.system_settings USING btree (id)`

#### `public.tab_analise_ambiental`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_analise` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `cod_pei` | `uuid / uuid` | `NO` | `` |
| `cod_organizacao` | `uuid / uuid` | `YES` | `` |
| `dsc_tipo_analise` | `character varying(10) / varchar` | `NO` | `` |
| `dsc_categoria` | `character varying(20) / varchar` | `NO` | `` |
| `dsc_item` | `character varying(500) / varchar` | `NO` | `` |
| `num_impacto` | `integer(32,0) / int4` | `NO` | `3` |
| `txt_observacao` | `text / text` | `YES` | `` |
| `num_ordem` | `integer(32,0) / int4` | `NO` | `0` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `2200_599986_1_not_null` coluna ``
- `CHECK` `2200_599986_2_not_null` coluna ``
- `CHECK` `2200_599986_4_not_null` coluna ``
- `CHECK` `2200_599986_5_not_null` coluna ``
- `CHECK` `2200_599986_6_not_null` coluna ``
- `CHECK` `2200_599986_7_not_null` coluna ``
- `CHECK` `2200_599986_9_not_null` coluna ``
- `FOREIGN KEY` `tab_analise_ambiental_cod_organizacao_foreign` coluna `cod_organizacao`
- `FOREIGN KEY` `tab_analise_ambiental_cod_pei_foreign` coluna `cod_pei`
- `PRIMARY KEY` `tab_analise_ambiental_pkey` coluna `cod_analise` -> public.tab_analise_ambiental.cod_analise

Indices:
- `tab_analise_ambiental_cod_organizacao_index`: `CREATE INDEX tab_analise_ambiental_cod_organizacao_index ON public.tab_analise_ambiental USING btree (cod_organizacao)`
- `tab_analise_ambiental_cod_pei_index`: `CREATE INDEX tab_analise_ambiental_cod_pei_index ON public.tab_analise_ambiental USING btree (cod_pei)`
- `tab_analise_ambiental_dsc_tipo_analise_dsc_categoria_index`: `CREATE INDEX tab_analise_ambiental_dsc_tipo_analise_dsc_categoria_index ON public.tab_analise_ambiental USING btree (dsc_tipo_analise, dsc_categoria)`
- `tab_analise_ambiental_pkey`: `CREATE UNIQUE INDEX tab_analise_ambiental_pkey ON public.tab_analise_ambiental USING btree (cod_analise)`

#### `public.tab_audit`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `acao` | `character varying(191) / varchar` | `NO` | `` |
| `antes` | `text / text` | `YES` | `` |
| `depois` | `text / text` | `YES` | `` |
| `table` | `character varying(191) / varchar` | `NO` | `` |
| `column_name` | `character varying(191) / varchar` | `NO` | `` |
| `data_type` | `character varying(191) / varchar` | `YES` | `` |
| `table_id` | `character varying(191) / varchar` | `NO` | `` |
| `ip` | `character varying(191) / varchar` | `NO` | `` |
| `user_id` | `uuid / uuid` | `NO` | `` |
| `dte_expired_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `2200_599716_10_not_null` coluna ``
- `CHECK` `2200_599716_1_not_null` coluna ``
- `CHECK` `2200_599716_2_not_null` coluna ``
- `CHECK` `2200_599716_5_not_null` coluna ``
- `CHECK` `2200_599716_6_not_null` coluna ``
- `CHECK` `2200_599716_8_not_null` coluna ``
- `CHECK` `2200_599716_9_not_null` coluna ``
- `FOREIGN KEY` `tab_audit_user_id_foreign` coluna `user_id` -> public.users.id
- `PRIMARY KEY` `tab_audit_pkey` coluna `id` -> public.tab_audit.id

Indices:
- `tab_audit_acao_index`: `CREATE INDEX tab_audit_acao_index ON public.tab_audit USING btree (acao)`
- `tab_audit_pkey`: `CREATE UNIQUE INDEX tab_audit_pkey ON public.tab_audit USING btree (id)`
- `tab_audit_table_table_id_index`: `CREATE INDEX tab_audit_table_table_id_index ON public.tab_audit USING btree ("table", table_id)`
- `tab_audit_user_id_index`: `CREATE INDEX tab_audit_user_id_index ON public.tab_audit USING btree (user_id)`

#### `public.tab_relatorios_agendados`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_agendamento` | `uuid / uuid` | `NO` | `` |
| `user_id` | `uuid / uuid` | `NO` | `` |
| `dsc_tipo_relatorio` | `character varying(191) / varchar` | `NO` | `` |
| `dsc_frequencia` | `character varying(191) / varchar` | `NO` | `` |
| `txt_filtros` | `jsonb / jsonb` | `YES` | `` |
| `dte_proxima_execucao` | `timestamp without time zone / timestamp` | `NO` | `` |
| `bln_ativo` | `boolean / bool` | `NO` | `true` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `2200_600190_1_not_null` coluna ``
- `CHECK` `2200_600190_2_not_null` coluna ``
- `CHECK` `2200_600190_3_not_null` coluna ``
- `CHECK` `2200_600190_4_not_null` coluna ``
- `CHECK` `2200_600190_6_not_null` coluna ``
- `CHECK` `2200_600190_7_not_null` coluna ``
- `FOREIGN KEY` `tab_relatorios_agendados_user_id_foreign` coluna `user_id` -> public.users.id
- `PRIMARY KEY` `tab_relatorios_agendados_pkey` coluna `cod_agendamento` -> public.tab_relatorios_agendados.cod_agendamento

Indices:
- `tab_relatorios_agendados_pkey`: `CREATE UNIQUE INDEX tab_relatorios_agendados_pkey ON public.tab_relatorios_agendados USING btree (cod_agendamento)`

#### `public.tab_relatorios_gerados`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_relatorio_gerado` | `uuid / uuid` | `NO` | `` |
| `user_id` | `uuid / uuid` | `YES` | `` |
| `dsc_tipo_relatorio` | `character varying(191) / varchar` | `NO` | `` |
| `dsc_caminho_arquivo` | `character varying(191) / varchar` | `NO` | `` |
| `dsc_formato` | `character varying(191) / varchar` | `NO` | `` |
| `txt_filtros_aplicados` | `jsonb / jsonb` | `YES` | `` |
| `num_tamanho_bytes` | `integer(32,0) / int4` | `YES` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `2200_600204_1_not_null` coluna ``
- `CHECK` `2200_600204_3_not_null` coluna ``
- `CHECK` `2200_600204_4_not_null` coluna ``
- `CHECK` `2200_600204_5_not_null` coluna ``
- `FOREIGN KEY` `tab_relatorios_gerados_user_id_foreign` coluna `user_id` -> public.users.id
- `PRIMARY KEY` `tab_relatorios_gerados_pkey` coluna `cod_relatorio_gerado` -> public.tab_relatorios_gerados.cod_relatorio_gerado

Indices:
- `tab_relatorios_gerados_pkey`: `CREATE UNIQUE INDEX tab_relatorios_gerados_pkey ON public.tab_relatorios_gerados USING btree (cod_relatorio_gerado)`

#### `public.tab_status`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_status` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `dsc_status` | `text / text` | `NO` | `` |

Constraints:
- `CHECK` `2200_599870_1_not_null` coluna ``
- `CHECK` `2200_599870_2_not_null` coluna ``
- `PRIMARY KEY` `tab_status_pkey` coluna `cod_status` -> public.tab_status.cod_status

Indices:
- `tab_status_pkey`: `CREATE UNIQUE INDEX tab_status_pkey ON public.tab_status USING btree (cod_status)`

#### `public.users`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `name` | `character varying(191) / varchar` | `NO` | `` |
| `email` | `character varying(191) / varchar` | `NO` | `` |
| `ativo` | `smallint(16,0) / int2` | `NO` | `'1'::smallint` |
| `adm` | `smallint(16,0) / int2` | `NO` | `'2'::smallint` |
| `email_verified_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `password` | `character varying(191) / varchar` | `NO` | `` |
| `trocarsenha` | `smallint(16,0) / int2` | `NO` | `'1'::smallint` |
| `two_factor_secret` | `text / text` | `YES` | `` |
| `two_factor_recovery_codes` | `text / text` | `YES` | `` |
| `two_factor_confirmed_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `remember_token` | `character varying(100) / varchar` | `YES` | `` |
| `current_team_id` | `uuid / uuid` | `YES` | `` |
| `profile_photo_path` | `character varying(2048) / varchar` | `YES` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `theme_color` | `character varying(191) / varchar` | `NO` | `'primary'::character varying` |

Constraints:
- `CHECK` `2200_599385_17_not_null` coluna ``
- `CHECK` `2200_599385_1_not_null` coluna ``
- `CHECK` `2200_599385_2_not_null` coluna ``
- `CHECK` `2200_599385_3_not_null` coluna ``
- `CHECK` `2200_599385_4_not_null` coluna ``
- `CHECK` `2200_599385_5_not_null` coluna ``
- `CHECK` `2200_599385_7_not_null` coluna ``
- `CHECK` `2200_599385_8_not_null` coluna ``
- `PRIMARY KEY` `users_pkey` coluna `id` -> public.users.id
- `UNIQUE` `users_email_unique` coluna `email` -> public.users.email

Indices:
- `users_email_unique`: `CREATE UNIQUE INDEX users_email_unique ON public.users USING btree (email)`
- `users_pkey`: `CREATE UNIQUE INDEX users_pkey ON public.users USING btree (id)`

#### `risk_management.tab_risco`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_risco` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `cod_pei` | `uuid / uuid` | `NO` | `` |
| `cod_organizacao` | `uuid / uuid` | `NO` | `` |
| `num_codigo_risco` | `integer(32,0) / int4` | `NO` | `` |
| `dsc_titulo` | `character varying(255) / varchar` | `NO` | `` |
| `txt_descricao` | `text / text` | `NO` | `` |
| `dsc_categoria` | `character varying(50) / varchar` | `NO` | `` |
| `dsc_status` | `character varying(50) / varchar` | `NO` | `` |
| `num_probabilidade` | `smallint(16,0) / int2` | `NO` | `` |
| `num_impacto` | `smallint(16,0) / int2` | `NO` | `` |
| `num_nivel_risco` | `smallint(16,0) / int2` | `NO` | `` |
| `txt_causas` | `text / text` | `YES` | `` |
| `txt_consequencias` | `text / text` | `YES` | `` |
| `cod_responsavel_monitoramento` | `uuid / uuid` | `YES` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571331_599892_10_not_null` coluna ``
- `CHECK` `571331_599892_11_not_null` coluna ``
- `CHECK` `571331_599892_1_not_null` coluna ``
- `CHECK` `571331_599892_2_not_null` coluna ``
- `CHECK` `571331_599892_3_not_null` coluna ``
- `CHECK` `571331_599892_4_not_null` coluna ``
- `CHECK` `571331_599892_5_not_null` coluna ``
- `CHECK` `571331_599892_6_not_null` coluna ``
- `CHECK` `571331_599892_7_not_null` coluna ``
- `CHECK` `571331_599892_8_not_null` coluna ``
- `CHECK` `571331_599892_9_not_null` coluna ``
- `CHECK` `chk_impacto` coluna `` -> risk_management.tab_risco.num_impacto
- `CHECK` `chk_nivel_risco` coluna `` -> risk_management.tab_risco.num_nivel_risco
- `CHECK` `chk_probabilidade` coluna `` -> risk_management.tab_risco.num_probabilidade
- `FOREIGN KEY` `risk_management_tab_risco_cod_organizacao_foreign` coluna `cod_organizacao`
- `FOREIGN KEY` `risk_management_tab_risco_cod_pei_foreign` coluna `cod_pei`
- `FOREIGN KEY` `risk_management_tab_risco_cod_responsavel_monitoramento_foreign` coluna `cod_responsavel_monitoramento`
- `PRIMARY KEY` `tab_risco_pkey` coluna `cod_risco` -> risk_management.tab_risco.cod_risco

Indices:
- `risk_management_tab_risco_cod_organizacao_index`: `CREATE INDEX risk_management_tab_risco_cod_organizacao_index ON risk_management.tab_risco USING btree (cod_organizacao)`
- `risk_management_tab_risco_cod_pei_index`: `CREATE INDEX risk_management_tab_risco_cod_pei_index ON risk_management.tab_risco USING btree (cod_pei)`
- `risk_management_tab_risco_cod_pei_num_codigo_risco_index`: `CREATE INDEX risk_management_tab_risco_cod_pei_num_codigo_risco_index ON risk_management.tab_risco USING btree (cod_pei, num_codigo_risco)`
- `risk_management_tab_risco_dsc_categoria_index`: `CREATE INDEX risk_management_tab_risco_dsc_categoria_index ON risk_management.tab_risco USING btree (dsc_categoria)`
- `risk_management_tab_risco_dsc_status_index`: `CREATE INDEX risk_management_tab_risco_dsc_status_index ON risk_management.tab_risco USING btree (dsc_status)`
- `risk_management_tab_risco_num_nivel_risco_index`: `CREATE INDEX risk_management_tab_risco_num_nivel_risco_index ON risk_management.tab_risco USING btree (num_nivel_risco)`
- `tab_risco_pkey`: `CREATE UNIQUE INDEX tab_risco_pkey ON risk_management.tab_risco USING btree (cod_risco)`

#### `risk_management.tab_risco_mitigacao`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_mitigacao` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `cod_risco` | `uuid / uuid` | `NO` | `` |
| `dsc_tipo_mitigacao` | `character varying(50) / varchar` | `NO` | `` |
| `txt_acao_mitigacao` | `text / text` | `NO` | `` |
| `cod_responsavel` | `uuid / uuid` | `YES` | `` |
| `dte_prazo` | `date / date` | `YES` | `` |
| `dsc_status` | `character varying(50) / varchar` | `NO` | `` |
| `txt_observacoes` | `text / text` | `YES` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571331_599940_1_not_null` coluna ``
- `CHECK` `571331_599940_2_not_null` coluna ``
- `CHECK` `571331_599940_3_not_null` coluna ``
- `CHECK` `571331_599940_4_not_null` coluna ``
- `CHECK` `571331_599940_7_not_null` coluna ``
- `FOREIGN KEY` `risk_management_tab_risco_mitigacao_cod_responsavel_foreign` coluna `cod_responsavel`
- `FOREIGN KEY` `risk_management_tab_risco_mitigacao_cod_risco_foreign` coluna `cod_risco` -> risk_management.tab_risco.cod_risco
- `PRIMARY KEY` `tab_risco_mitigacao_pkey` coluna `cod_mitigacao` -> risk_management.tab_risco_mitigacao.cod_mitigacao

Indices:
- `risk_management_tab_risco_mitigacao_cod_responsavel_index`: `CREATE INDEX risk_management_tab_risco_mitigacao_cod_responsavel_index ON risk_management.tab_risco_mitigacao USING btree (cod_responsavel)`
- `risk_management_tab_risco_mitigacao_cod_risco_index`: `CREATE INDEX risk_management_tab_risco_mitigacao_cod_risco_index ON risk_management.tab_risco_mitigacao USING btree (cod_risco)`
- `risk_management_tab_risco_mitigacao_dsc_status_index`: `CREATE INDEX risk_management_tab_risco_mitigacao_dsc_status_index ON risk_management.tab_risco_mitigacao USING btree (dsc_status)`
- `risk_management_tab_risco_mitigacao_dsc_tipo_mitigacao_index`: `CREATE INDEX risk_management_tab_risco_mitigacao_dsc_tipo_mitigacao_index ON risk_management.tab_risco_mitigacao USING btree (dsc_tipo_mitigacao)`
- `tab_risco_mitigacao_pkey`: `CREATE UNIQUE INDEX tab_risco_mitigacao_pkey ON risk_management.tab_risco_mitigacao USING btree (cod_mitigacao)`

#### `risk_management.tab_risco_objetivo`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_risco` | `uuid / uuid` | `NO` | `` |
| `cod_objetivo` | `uuid / uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571331_599925_1_not_null` coluna ``
- `CHECK` `571331_599925_2_not_null` coluna ``
- `FOREIGN KEY` `risk_management_tab_risco_objetivo_cod_objetivo_foreign` coluna `cod_objetivo`
- `FOREIGN KEY` `risk_management_tab_risco_objetivo_cod_risco_foreign` coluna `cod_risco` -> risk_management.tab_risco.cod_risco
- `PRIMARY KEY` `tab_risco_objetivo_pkey` coluna `cod_risco` -> risk_management.tab_risco_objetivo.cod_risco
- `PRIMARY KEY` `tab_risco_objetivo_pkey` coluna `cod_risco` -> risk_management.tab_risco_objetivo.cod_objetivo
- `PRIMARY KEY` `tab_risco_objetivo_pkey` coluna `cod_objetivo` -> risk_management.tab_risco_objetivo.cod_risco
- `PRIMARY KEY` `tab_risco_objetivo_pkey` coluna `cod_objetivo` -> risk_management.tab_risco_objetivo.cod_objetivo

Indices:
- `tab_risco_objetivo_pkey`: `CREATE UNIQUE INDEX tab_risco_objetivo_pkey ON risk_management.tab_risco_objetivo USING btree (cod_risco, cod_objetivo)`

#### `risk_management.tab_risco_ocorrencia`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_ocorrencia` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `cod_risco` | `uuid / uuid` | `NO` | `` |
| `dte_ocorrencia` | `date / date` | `NO` | `` |
| `txt_descricao_ocorrencia` | `text / text` | `NO` | `` |
| `vlr_impacto_financeiro` | `numeric(15,2) / numeric` | `YES` | `` |
| `txt_acoes_tomadas` | `text / text` | `YES` | `` |
| `txt_licoes_aprendidas` | `text / text` | `YES` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571331_599963_1_not_null` coluna ``
- `CHECK` `571331_599963_2_not_null` coluna ``
- `CHECK` `571331_599963_3_not_null` coluna ``
- `CHECK` `571331_599963_4_not_null` coluna ``
- `FOREIGN KEY` `risk_management_tab_risco_ocorrencia_cod_risco_foreign` coluna `cod_risco` -> risk_management.tab_risco.cod_risco
- `PRIMARY KEY` `tab_risco_ocorrencia_pkey` coluna `cod_ocorrencia` -> risk_management.tab_risco_ocorrencia.cod_ocorrencia

Indices:
- `risk_management_tab_risco_ocorrencia_cod_risco_index`: `CREATE INDEX risk_management_tab_risco_ocorrencia_cod_risco_index ON risk_management.tab_risco_ocorrencia USING btree (cod_risco)`
- `risk_management_tab_risco_ocorrencia_dte_ocorrencia_index`: `CREATE INDEX risk_management_tab_risco_ocorrencia_dte_ocorrencia_index ON risk_management.tab_risco_ocorrencia USING btree (dte_ocorrencia)`
- `tab_risco_ocorrencia_pkey`: `CREATE UNIQUE INDEX tab_risco_ocorrencia_pkey ON risk_management.tab_risco_ocorrencia USING btree (cod_ocorrencia)`

#### `strategic_planning.tab_arquivos`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_arquivo` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `cod_evolucao_indicador` | `uuid / uuid` | `NO` | `` |
| `txt_assunto` | `text / text` | `NO` | `` |
| `data` | `text / text` | `NO` | `` |
| `dsc_nome_arquivo` | `text / text` | `NO` | `` |
| `dsc_tipo` | `character varying(191) / varchar` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571328_599742_1_not_null` coluna ``
- `CHECK` `571328_599742_2_not_null` coluna ``
- `CHECK` `571328_599742_3_not_null` coluna ``
- `CHECK` `571328_599742_4_not_null` coluna ``
- `CHECK` `571328_599742_5_not_null` coluna ``
- `CHECK` `571328_599742_6_not_null` coluna ``
- `FOREIGN KEY` `strategic_planning_tab_arquivos_cod_evolucao_indicador_foreign` coluna `cod_evolucao_indicador`
- `PRIMARY KEY` `tab_arquivos_pkey` coluna `cod_arquivo` -> strategic_planning.tab_arquivos.cod_arquivo

Indices:
- `strategic_planning_tab_arquivos_cod_evolucao_indicador_index`: `CREATE INDEX strategic_planning_tab_arquivos_cod_evolucao_indicador_index ON strategic_planning.tab_arquivos USING btree (cod_evolucao_indicador)`
- `tab_arquivos_pkey`: `CREATE UNIQUE INDEX tab_arquivos_pkey ON strategic_planning.tab_arquivos USING btree (cod_arquivo)`

#### `strategic_planning.tab_atividade_cadeia_valor`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_atividade_cadeia_valor` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `cod_pei` | `uuid / uuid` | `NO` | `` |
| `cod_perspectiva` | `uuid / uuid` | `NO` | `` |
| `dsc_atividade` | `text / text` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571328_599757_1_not_null` coluna ``
- `CHECK` `571328_599757_2_not_null` coluna ``
- `CHECK` `571328_599757_3_not_null` coluna ``
- `CHECK` `571328_599757_4_not_null` coluna ``
- `FOREIGN KEY` `strategic_planning_tab_atividade_cadeia_valor_cod_pei_foreign` coluna `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `FOREIGN KEY` `strategic_planning_tab_atividade_cadeia_valor_cod_perspectiva_f` coluna `cod_perspectiva` -> strategic_planning.tab_perspectiva.cod_perspectiva
- `PRIMARY KEY` `tab_atividade_cadeia_valor_pkey` coluna `cod_atividade_cadeia_valor` -> strategic_planning.tab_atividade_cadeia_valor.cod_atividade_cadeia_valor

Indices:
- `strategic_planning_tab_atividade_cadeia_valor_cod_pei_index`: `CREATE INDEX strategic_planning_tab_atividade_cadeia_valor_cod_pei_index ON strategic_planning.tab_atividade_cadeia_valor USING btree (cod_pei)`
- `strategic_planning_tab_atividade_cadeia_valor_cod_perspectiva_i`: `CREATE INDEX strategic_planning_tab_atividade_cadeia_valor_cod_perspectiva_i ON strategic_planning.tab_atividade_cadeia_valor USING btree (cod_perspectiva)`
- `tab_atividade_cadeia_valor_pkey`: `CREATE UNIQUE INDEX tab_atividade_cadeia_valor_pkey ON strategic_planning.tab_atividade_cadeia_valor USING btree (cod_atividade_cadeia_valor)`

#### `strategic_planning.tab_futuro_almejado_objetivo`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_futuro_almejado` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `dsc_futuro_almejado` | `text / text` | `NO` | `` |
| `cod_objetivo` | `uuid / uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571328_599812_1_not_null` coluna ``
- `CHECK` `571328_599812_2_not_null` coluna ``
- `CHECK` `571328_599812_3_not_null` coluna ``
- `FOREIGN KEY` `strategic_planning_tab_futuro_almejado_objetivo_estrategico_cod` coluna `cod_objetivo` -> strategic_planning.tab_objetivo.cod_objetivo
- `PRIMARY KEY` `tab_futuro_almejado_objetivo_estrategico_pkey` coluna `cod_futuro_almejado` -> strategic_planning.tab_futuro_almejado_objetivo.cod_futuro_almejado

Indices:
- `strategic_planning_tab_futuro_almejado_objetivo_estrategico_cod`: `CREATE INDEX strategic_planning_tab_futuro_almejado_objetivo_estrategico_cod ON strategic_planning.tab_futuro_almejado_objetivo USING btree (cod_objetivo)`
- `tab_futuro_almejado_objetivo_estrategico_pkey`: `CREATE UNIQUE INDEX tab_futuro_almejado_objetivo_estrategico_pkey ON strategic_planning.tab_futuro_almejado_objetivo USING btree (cod_futuro_almejado)`

#### `strategic_planning.tab_grau_satisfacao`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_grau_satisfacao` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `dsc_grau_satisfacao` | `text / text` | `NO` | `` |
| `cor` | `character varying(191) / varchar` | `NO` | `` |
| `vlr_minimo` | `numeric(15,2) / numeric` | `NO` | `` |
| `vlr_maximo` | `numeric(15,2) / numeric` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `cod_pei` | `uuid / uuid` | `YES` | `` |
| `num_ano` | `integer(32,0) / int4` | `YES` | `` |

Constraints:
- `CHECK` `571328_599733_1_not_null` coluna ``
- `CHECK` `571328_599733_2_not_null` coluna ``
- `CHECK` `571328_599733_3_not_null` coluna ``
- `CHECK` `571328_599733_4_not_null` coluna ``
- `CHECK` `571328_599733_5_not_null` coluna ``
- `FOREIGN KEY` `strategic_planning_tab_grau_satisfacao_cod_pei_foreign` coluna `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `PRIMARY KEY` `tab_grau_satisfcao_pkey` coluna `cod_grau_satisfacao` -> strategic_planning.tab_grau_satisfacao.cod_grau_satisfacao

Indices:
- `idx_grau_satisfacao_pei_ano`: `CREATE INDEX idx_grau_satisfacao_pei_ano ON strategic_planning.tab_grau_satisfacao USING btree (cod_pei, num_ano)`
- `tab_grau_satisfcao_pkey`: `CREATE UNIQUE INDEX tab_grau_satisfcao_pkey ON strategic_planning.tab_grau_satisfacao USING btree (cod_grau_satisfacao)`

#### `strategic_planning.tab_missao_visao_valores`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_missao_visao_valores` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `dsc_missao` | `text / text` | `NO` | `` |
| `dsc_visao` | `text / text` | `NO` | `` |
| `cod_pei` | `uuid / uuid` | `NO` | `` |
| `cod_organizacao` | `uuid / uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571328_599540_1_not_null` coluna ``
- `CHECK` `571328_599540_2_not_null` coluna ``
- `CHECK` `571328_599540_3_not_null` coluna ``
- `CHECK` `571328_599540_4_not_null` coluna ``
- `CHECK` `571328_599540_5_not_null` coluna ``
- `FOREIGN KEY` `strategic_planning_tab_missao_visao_valores_cod_organizacao_for` coluna `cod_organizacao`
- `FOREIGN KEY` `strategic_planning_tab_missao_visao_valores_cod_pei_foreign` coluna `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `PRIMARY KEY` `tab_missao_visao_valores_pkey` coluna `cod_missao_visao_valores` -> strategic_planning.tab_missao_visao_valores.cod_missao_visao_valores

Indices:
- `tab_missao_visao_valores_pkey`: `CREATE UNIQUE INDEX tab_missao_visao_valores_pkey ON strategic_planning.tab_missao_visao_valores USING btree (cod_missao_visao_valores)`

#### `strategic_planning.tab_nivel_hierarquico`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `num_nivel_hierarquico_apresentacao` | `smallint(16,0) / int2` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571328_599587_1_not_null` coluna ``
- `PRIMARY KEY` `tab_nivel_hierarquico_pkey` coluna `num_nivel_hierarquico_apresentacao` -> strategic_planning.tab_nivel_hierarquico.num_nivel_hierarquico_apresentacao

Indices:
- `tab_nivel_hierarquico_pkey`: `CREATE UNIQUE INDEX tab_nivel_hierarquico_pkey ON strategic_planning.tab_nivel_hierarquico USING btree (num_nivel_hierarquico_apresentacao)`

#### `strategic_planning.tab_objetivo`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_objetivo` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `nom_objetivo` | `text / text` | `NO` | `` |
| `dsc_objetivo` | `text / text` | `NO` | `` |
| `num_nivel_hierarquico_apresentacao` | `smallint(16,0) / int2` | `NO` | `` |
| `cod_perspectiva` | `uuid / uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571328_599573_1_not_null` coluna ``
- `CHECK` `571328_599573_2_not_null` coluna ``
- `CHECK` `571328_599573_3_not_null` coluna ``
- `CHECK` `571328_599573_4_not_null` coluna ``
- `CHECK` `571328_599573_5_not_null` coluna ``
- `FOREIGN KEY` `strategic_planning_tab_objetivo_estrategico_cod_perspectiva_for` coluna `cod_perspectiva` -> strategic_planning.tab_perspectiva.cod_perspectiva
- `PRIMARY KEY` `tab_objetivo_estrategico_pkey` coluna `cod_objetivo` -> strategic_planning.tab_objetivo.cod_objetivo

Indices:
- `tab_objetivo_estrategico_pkey`: `CREATE UNIQUE INDEX tab_objetivo_estrategico_pkey ON strategic_planning.tab_objetivo USING btree (cod_objetivo)`

#### `strategic_planning.tab_objetivo_comentarios`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_comentario` | `uuid / uuid` | `NO` | `` |
| `cod_objetivo` | `uuid / uuid` | `NO` | `` |
| `user_id` | `uuid / uuid` | `NO` | `` |
| `dsc_comentario` | `text / text` | `NO` | `` |
| `cod_comentario_pai` | `uuid / uuid` | `YES` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571328_600217_1_not_null` coluna ``
- `CHECK` `571328_600217_2_not_null` coluna ``
- `CHECK` `571328_600217_3_not_null` coluna ``
- `CHECK` `571328_600217_4_not_null` coluna ``
- `FOREIGN KEY` `strategic_planning_tab_objetivo_comentarios_cod_objetivo_foreig` coluna `cod_objetivo` -> strategic_planning.tab_objetivo.cod_objetivo
- `FOREIGN KEY` `strategic_planning_tab_objetivo_comentarios_user_id_foreign` coluna `user_id`
- `PRIMARY KEY` `tab_objetivo_comentarios_pkey` coluna `cod_comentario` -> strategic_planning.tab_objetivo_comentarios.cod_comentario

Indices:
- `tab_objetivo_comentarios_pkey`: `CREATE UNIQUE INDEX tab_objetivo_comentarios_pkey ON strategic_planning.tab_objetivo_comentarios USING btree (cod_comentario)`

#### `strategic_planning.tab_pei`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_pei` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `dsc_pei` | `text / text` | `NO` | `` |
| `num_ano_inicio_pei` | `smallint(16,0) / int2` | `NO` | `` |
| `num_ano_fim_pei` | `smallint(16,0) / int2` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571328_599531_1_not_null` coluna ``
- `CHECK` `571328_599531_2_not_null` coluna ``
- `CHECK` `571328_599531_3_not_null` coluna ``
- `CHECK` `571328_599531_4_not_null` coluna ``
- `PRIMARY KEY` `tab_pei_pkey` coluna `cod_pei` -> strategic_planning.tab_pei.cod_pei

Indices:
- `tab_pei_pkey`: `CREATE UNIQUE INDEX tab_pei_pkey ON strategic_planning.tab_pei USING btree (cod_pei)`

#### `strategic_planning.tab_perspectiva`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_perspectiva` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `dsc_perspectiva` | `text / text` | `NO` | `` |
| `num_nivel_hierarquico_apresentacao` | `smallint(16,0) / int2` | `NO` | `` |
| `cod_pei` | `uuid / uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `num_peso_indicadores` | `integer(32,0) / int4` | `NO` | `100` |
| `num_peso_planos` | `integer(32,0) / int4` | `NO` | `0` |

Constraints:
- `CHECK` `571328_599559_1_not_null` coluna ``
- `CHECK` `571328_599559_2_not_null` coluna ``
- `CHECK` `571328_599559_3_not_null` coluna ``
- `CHECK` `571328_599559_4_not_null` coluna ``
- `CHECK` `571328_599559_8_not_null` coluna ``
- `CHECK` `571328_599559_9_not_null` coluna ``
- `FOREIGN KEY` `strategic_planning_tab_perspectiva_cod_pei_foreign` coluna `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `PRIMARY KEY` `tab_perspectiva_pkey` coluna `cod_perspectiva` -> strategic_planning.tab_perspectiva.cod_perspectiva

Indices:
- `tab_perspectiva_pkey`: `CREATE UNIQUE INDEX tab_perspectiva_pkey ON strategic_planning.tab_perspectiva USING btree (cod_perspectiva)`

#### `strategic_planning.tab_processos_atividade_cadeia_valor`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_processo_atividade_cadeia_valor` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `cod_atividade_cadeia_valor` | `uuid / uuid` | `NO` | `` |
| `dsc_entrada` | `text / text` | `NO` | `` |
| `dsc_transformacao` | `text / text` | `NO` | `` |
| `dsc_saida` | `text / text` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571328_599778_1_not_null` coluna ``
- `CHECK` `571328_599778_2_not_null` coluna ``
- `CHECK` `571328_599778_3_not_null` coluna ``
- `CHECK` `571328_599778_4_not_null` coluna ``
- `CHECK` `571328_599778_5_not_null` coluna ``
- `FOREIGN KEY` `strategic_planning_tab_processos_atividade_cadeia_valor_cod_ati` coluna `cod_atividade_cadeia_valor` -> strategic_planning.tab_atividade_cadeia_valor.cod_atividade_cadeia_valor
- `PRIMARY KEY` `tab_processos_atividade_cadeia_valor_pkey` coluna `cod_processo_atividade_cadeia_valor` -> strategic_planning.tab_processos_atividade_cadeia_valor.cod_processo_atividade_cadeia_valor

Indices:
- `strategic_planning_tab_processos_atividade_cadeia_valor_cod_ati`: `CREATE INDEX strategic_planning_tab_processos_atividade_cadeia_valor_cod_ati ON strategic_planning.tab_processos_atividade_cadeia_valor USING btree (cod_atividade_cadeia_valor)`
- `tab_processos_atividade_cadeia_valor_pkey`: `CREATE UNIQUE INDEX tab_processos_atividade_cadeia_valor_pkey ON strategic_planning.tab_processos_atividade_cadeia_valor USING btree (cod_processo_atividade_cadeia_valor)`

#### `strategic_planning.tab_tema_norteador`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_tema_norteador` | `uuid / uuid` | `NO` | `` |
| `nom_tema_norteador` | `text / text` | `NO` | `` |
| `cod_pei` | `uuid / uuid` | `NO` | `` |
| `cod_organizacao` | `uuid / uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571328_600136_1_not_null` coluna ``
- `CHECK` `571328_600136_2_not_null` coluna ``
- `CHECK` `571328_600136_3_not_null` coluna ``
- `CHECK` `571328_600136_4_not_null` coluna ``
- `FOREIGN KEY` `strategic_planning_tab_objetivo_estrategico_cod_organizacao_for` coluna `cod_organizacao`
- `FOREIGN KEY` `strategic_planning_tab_objetivo_estrategico_cod_pei_foreign` coluna `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `PRIMARY KEY` `tab_objetivo_estrategico_pkey1` coluna `cod_tema_norteador` -> strategic_planning.tab_tema_norteador.cod_tema_norteador

Indices:
- `tab_objetivo_estrategico_pkey1`: `CREATE UNIQUE INDEX tab_objetivo_estrategico_pkey1 ON strategic_planning.tab_tema_norteador USING btree (cod_tema_norteador)`

#### `strategic_planning.tab_valores`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_valor` | `uuid / uuid` | `NO` | `gen_random_uuid()` |
| `nom_valor` | `text / text` | `NO` | `` |
| `dsc_valor` | `text / text` | `NO` | `` |
| `cod_pei` | `uuid / uuid` | `NO` | `` |
| `cod_organizacao` | `uuid / uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |

Constraints:
- `CHECK` `571328_599793_1_not_null` coluna ``
- `CHECK` `571328_599793_2_not_null` coluna ``
- `CHECK` `571328_599793_3_not_null` coluna ``
- `CHECK` `571328_599793_4_not_null` coluna ``
- `CHECK` `571328_599793_5_not_null` coluna ``
- `FOREIGN KEY` `strategic_planning_tab_valores_cod_organizacao_foreign` coluna `cod_organizacao`
- `FOREIGN KEY` `strategic_planning_tab_valores_cod_pei_foreign` coluna `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `PRIMARY KEY` `tab_valores_pkey` coluna `cod_valor` -> strategic_planning.tab_valores.cod_valor

Indices:
- `tab_valores_pkey`: `CREATE UNIQUE INDEX tab_valores_pkey ON strategic_planning.tab_valores USING btree (cod_valor)`

## 7. Models e relacoes

Observacao: quando o model nao declara schema no `$table`, ele depende do `search_path` PostgreSQL configurado em `config/database.php`: `public`, `strategic_planning`, `action_plan`, `performance_indicators`, `risk_management`, `organization`.

| Model | Arquivo | Tabela | PK | Chave | Incremental | Auditado |
|---|---|---|---|---|---|---|
| `App\Models\ActionPlan\Acao` | `app/Models/ActionPlan/Acao.php` | `acoes` | `id` | `string` | `false` | `nao` |
| `App\Models\ActionPlan\Entrega` | `app/Models/ActionPlan/Entrega.php` | `tab_entregas` | `cod_entrega` | `string` | `false` | `nao` |
| `App\Models\ActionPlan\EntregaAnexo` | `app/Models/ActionPlan/EntregaAnexo.php` | `tab_entrega_anexos` | `cod_anexo` | `string` | `false` | `nao` |
| `App\Models\ActionPlan\EntregaComentario` | `app/Models/ActionPlan/EntregaComentario.php` | `tab_entrega_comentarios` | `cod_comentario` | `string` | `false` | `nao` |
| `App\Models\ActionPlan\EntregaHistorico` | `app/Models/ActionPlan/EntregaHistorico.php` | `tab_entrega_historico` | `cod_historico` | `string` | `false` | `nao` |
| `App\Models\ActionPlan\EntregaLabel` | `app/Models/ActionPlan/EntregaLabel.php` | `tab_entrega_labels` | `cod_label` | `string` | `false` | `nao` |
| `App\Models\ActionPlan\PlanoDeAcao` | `app/Models/ActionPlan/PlanoDeAcao.php` | `tab_plano_de_acao` | `cod_plano_de_acao` | `string` | `false` | `sim` |
| `App\Models\ActionPlan\TipoExecucao` | `app/Models/ActionPlan/TipoExecucao.php` | `tab_tipo_execucao` | `cod_tipo_execucao` | `string` | `false` | `nao` |
| `App\Models\Lead` | `app/Models/Lead.php` | `convencao Eloquent` | `id/convencao` | `convencao` | `convencao` | `nao` |
| `App\Models\Organization` | `app/Models/Organization.php` | `tab_organizacoes` | `cod_organizacao` | `string` | `false` | `nao` |
| `App\Models\PerfilAcesso` | `app/Models/PerfilAcesso.php` | `tab_perfil_acesso` | `cod_perfil` | `string` | `false` | `nao` |
| `App\Models\PerformanceIndicators\EvolucaoIndicador` | `app/Models/PerformanceIndicators/EvolucaoIndicador.php` | `tab_evolucao_indicador` | `cod_evolucao_indicador` | `string` | `false` | `nao` |
| `App\Models\PerformanceIndicators\Indicador` | `app/Models/PerformanceIndicators/Indicador.php` | `tab_indicador` | `cod_indicador` | `string` | `false` | `sim` |
| `App\Models\PerformanceIndicators\LinhaBaseIndicador` | `app/Models/PerformanceIndicators/LinhaBaseIndicador.php` | `tab_linha_base_indicador` | `cod_linha_base` | `string` | `false` | `nao` |
| `App\Models\PerformanceIndicators\MetaPorAno` | `app/Models/PerformanceIndicators/MetaPorAno.php` | `tab_meta_por_ano` | `cod_meta_por_ano` | `string` | `false` | `nao` |
| `App\Models\Reports\RelatorioAgendado` | `app/Models/Reports/RelatorioAgendado.php` | `tab_relatorios_agendados` | `cod_agendamento` | `string` | `false` | `nao` |
| `App\Models\Reports\RelatorioGerado` | `app/Models/Reports/RelatorioGerado.php` | `tab_relatorios_gerados` | `cod_relatorio_gerado` | `string` | `false` | `nao` |
| `App\Models\RiskManagement\Risco` | `app/Models/RiskManagement/Risco.php` | `tab_risco` | `cod_risco` | `string` | `false` | `sim` |
| `App\Models\RiskManagement\RiscoMitigacao` | `app/Models/RiskManagement/RiscoMitigacao.php` | `tab_risco_mitigacao` | `cod_mitigacao` | `string` | `false` | `sim` |
| `App\Models\RiskManagement\RiscoObjetivo` | `app/Models/RiskManagement/RiscoObjetivo.php` | `tab_risco_objetivo` | `cod_risco_objetivo` | `string` | `false` | `nao` |
| `App\Models\RiskManagement\RiscoOcorrencia` | `app/Models/RiskManagement/RiscoOcorrencia.php` | `tab_risco_ocorrencia` | `cod_ocorrencia` | `string` | `false` | `sim` |
| `App\Models\StrategicAlert` | `app/Models/StrategicAlert.php` | `strategic_alerts` | `id/convencao` | `convencao` | `convencao` | `nao` |
| `App\Models\StrategicPlanning\AnaliseAmbiental` | `app/Models/StrategicPlanning/AnaliseAmbiental.php` | `tab_analise_ambiental` | `cod_analise` | `string` | `false` | `nao` |
| `App\Models\StrategicPlanning\Arquivo` | `app/Models/StrategicPlanning/Arquivo.php` | `tab_arquivos` | `cod_arquivo` | `string` | `false` | `nao` |
| `App\Models\StrategicPlanning\AtividadeCadeiaValor` | `app/Models/StrategicPlanning/AtividadeCadeiaValor.php` | `tab_atividade_cadeia_valor` | `cod_atividade_cadeia_valor` | `string` | `false` | `nao` |
| `App\Models\StrategicPlanning\FuturoAlmejado` | `app/Models/StrategicPlanning/FuturoAlmejado.php` | `tab_futuro_almejado_objetivo` | `cod_futuro_almejado` | `string` | `false` | `nao` |
| `App\Models\StrategicPlanning\GrauSatisfacao` | `app/Models/StrategicPlanning/GrauSatisfacao.php` | `tab_grau_satisfacao` | `cod_grau_satisfacao` | `string` | `false` | `nao` |
| `App\Models\StrategicPlanning\MissaoVisaoValores` | `app/Models/StrategicPlanning/MissaoVisaoValores.php` | `tab_missao_visao_valores` | `cod_missao_visao_valores` | `string` | `false` | `sim` |
| `App\Models\StrategicPlanning\Objetivo` | `app/Models/StrategicPlanning/Objetivo.php` | `tab_objetivo` | `cod_objetivo` | `string` | `false` | `sim` |
| `App\Models\StrategicPlanning\ObjetivoComentario` | `app/Models/StrategicPlanning/ObjetivoComentario.php` | `tab_objetivo_comentarios` | `cod_comentario` | `string` | `false` | `nao` |
| `App\Models\StrategicPlanning\PEI` | `app/Models/StrategicPlanning/PEI.php` | `tab_pei` | `cod_pei` | `string` | `false` | `nao` |
| `App\Models\StrategicPlanning\Perspectiva` | `app/Models/StrategicPlanning/Perspectiva.php` | `tab_perspectiva` | `cod_perspectiva` | `string` | `false` | `nao` |
| `App\Models\StrategicPlanning\ProcessoAtividadeCadeiaValor` | `app/Models/StrategicPlanning/ProcessoAtividadeCadeiaValor.php` | `tab_processos_atividade_cadeia_valor` | `cod_processo_atividade_cadeia_valor` | `string` | `false` | `nao` |
| `App\Models\StrategicPlanning\TemaNorteador` | `app/Models/StrategicPlanning/TemaNorteador.php` | `strategic_planning.tab_tema_norteador` | `cod_tema_norteador` | `string` | `false` | `sim` |
| `App\Models\StrategicPlanning\Valor` | `app/Models/StrategicPlanning/Valor.php` | `tab_valores` | `cod_valor` | `string` | `false` | `sim` |
| `App\Models\SystemSetting` | `app/Models/SystemSetting.php` | `system_settings` | `id/convencao` | `convencao` | `convencao` | `nao` |
| `App\Models\TabAudit` | `app/Models/TabAudit.php` | `tab_audit` | `id` | `string` | `false` | `nao` |
| `App\Models\TabStatus` | `app/Models/TabStatus.php` | `tab_status` | `cod_status` | `string` | `false` | `nao` |
| `App\Models\User` | `app/Models/User.php` | `users` | `id` | `string` | `false` | `nao` |

### 7.1 Metodos dos Models

#### `App\Models\ActionPlan\Acao`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public user` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public scopePorTabela` | `$query, string $tabela` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePorRegistro` | `$query, string $tableId` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePorUsuario` | `$query, string $userId` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeRecentes` | `$query, int $dias = 7` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

#### `App\Models\ActionPlan\Entrega`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public planoDeAcao` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public entregaPai` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public subEntregas` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public responsavel` | `nenhuma` | `BelongsTo` | Valida entrada e persiste dados do formulario/entidade. |
| `public responsaveis` | `nenhuma` | `BelongsToMany` | Valida entrada e persiste dados do formulario/entidade. |
| `public comentarios` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public labels` | `nenhuma` | `BelongsToMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public anexos` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public historico` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public isConcluida` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public isAtrasada` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public isSubEntrega` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public hasSubEntregas` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public getStatusColor` | `nenhuma` | `string` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getPrioridadeInfo` | `nenhuma` | `array` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getTipoInfo` | `nenhuma` | `array` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getProp` | `string $key, $default = null` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public setProp` | `string $key, $value` | `self` | Define estado interno ou propriedade persistida. |
| `public calcularProgressoSubEntregas` | `nenhuma` | `float` | Executa calculo de indicador, progresso ou consolidacao de desempenho. |
| `public registrarHistorico` | `string $acao, ?string $campo = null, $valorAntigo = null, $valorNovo = null, ?string $descricao = null` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public scopePorStatus` | `$query, string $status` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeConcluidas` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePendentes` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeOrdenadoPorNivel` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeOrdenado` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeRaiz` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeAtivas` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeArquivadas` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePorPrioridade` | `$query, string $prioridade` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePorResponsavel` | `$query, int $userId` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeAtrasadas` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeTarefas` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeDeletadasRecentemente` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

#### `App\Models\ActionPlan\EntregaAnexo`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public entrega` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public usuario` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public isImagem` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public isDocumento` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public getUrl` | `nenhuma` | `string` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getTamanhoFormatado` | `nenhuma` | `string` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getIcone` | `nenhuma` | `string` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getExtensao` | `nenhuma` | `string` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |

#### `App\Models\ActionPlan\EntregaComentario`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public entrega` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public usuario` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public comentarioPai` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public respostas` | `nenhuma` | `\Illuminate\Database\Eloquent\Relations\HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public getUsuariosMencionados` | `nenhuma` | `array` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public mencionou` | `int $userId` | `bool` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

#### `App\Models\ActionPlan\EntregaHistorico`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public entrega` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public usuario` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public getAcaoInfo` | `nenhuma` | `array` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getValorAntigo` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getValorNovo` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getDescricaoLegivel` | `nenhuma` | `string` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `protected getCampoLabel` | `nenhuma` | `string` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getTempoRelativo` | `nenhuma` | `string` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public scopePorAcao` | `$query, string $acao` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePorUsuario` | `$query, int $userId` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeRecentes` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

#### `App\Models\ActionPlan\EntregaLabel`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public planoDeAcao` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public entregas` | `nenhuma` | `BelongsToMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public getCorRgb` | `nenhuma` | `array` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public isCorEscura` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public getCorTexto` | `nenhuma` | `string` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public scopeOrdenado` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

#### `App\Models\ActionPlan\PlanoDeAcao`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public objetivo` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public tipoExecucao` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public organizacao` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public entregas` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public indicadores` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public organizacoes` | `nenhuma` | `\Illuminate\Database\Eloquent\Relations\BelongsToMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public getSatisfacaoColor` | `nenhuma` | `string` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getSatisfacaoTextClass` | `nenhuma` | `string` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public isAtrasado` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public calcularProgressoEntregas` | `nenhuma` | `float` | Executa calculo de indicador, progresso ou consolidacao de desempenho. |
| `public scopePorTipo` | `$query, string $tipo` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePorStatus` | `$query, string $status` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeAtrasados` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeEmAndamento` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public getResponsaveisAttribute` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |

#### `App\Models\ActionPlan\TipoExecucao`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public planosAcao` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public isAcao` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public isIniciativa` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public isProjeto` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |

#### `App\Models\Lead`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public getStatusLabelAttribute` | `nenhuma` | `string` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getStatusBadgeClassAttribute` | `nenhuma` | `string` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |

#### `App\Models\Organization`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public pai` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public filhas` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public usuarios` | `nenhuma` | `BelongsToMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public planosAcao` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public identidadeEstrategica` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public valores` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public getDescendantsAndSelfIds` | `nenhuma` | `array` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public isRaiz` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public getNivelHierarquico` | `int $nivel = 0` | `int` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public scopeRaiz` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeFilhasDe` | `$query, string $codOrganizacaoPai` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

#### `App\Models\PerfilAcesso`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public usuarios` | `nenhuma` | `BelongsToMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public isSuperAdmin` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public isAdminUnidade` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public isGestor` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |

#### `App\Models\PerformanceIndicators\EvolucaoIndicador`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public indicador` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public arquivos` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public calcularAtingimento` | `nenhuma` | `float` | Executa calculo de indicador, progresso ou consolidacao de desempenho. |
| `public getNomeMes` | `nenhuma` | `string` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public scopePorAno` | `$query, int $ano` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePorPeriodo` | `$query, int $ano, int $mes` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeAtualizadas` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeNaoAtualizadas` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

#### `App\Models\PerformanceIndicators\Indicador`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public planoDeAcao` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public objetivo` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public evolucoes` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public linhaBase` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public metasPorAno` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public organizacoes` | `nenhuma` | `BelongsToMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public getUltimaEvolucao` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public calcularAtingimento` | `int $ano = null, int $mes = null` | `float` | Executa calculo de indicador, progresso ou consolidacao de desempenho. |
| `protected calcularPercentualPorTipo` | `float $realizado, float $previsto` | `float` | Executa calculo de indicador, progresso ou consolidacao de desempenho. |
| `public getCorFarol` | `int $ano = null` | `?string` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public scopeDeObjetivo` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeDePlano` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePorPeriodo` | `$query, string $periodo` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

#### `App\Models\PerformanceIndicators\LinhaBaseIndicador`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public indicador` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public scopePorAno` | `$query, int $ano` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

#### `App\Models\PerformanceIndicators\MetaPorAno`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public indicador` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public scopePorAno` | `$query, int $ano` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeAnoAtual` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

#### `App\Models\Reports\RelatorioAgendado`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public user` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

#### `App\Models\Reports\RelatorioGerado`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public user` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

#### `App\Models\RiskManagement\Risco`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public pei` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public organizacao` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public responsavel` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public objetivos` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public mitigacoes` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public ocorrencias` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public scopeAtivos` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeCriticos` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePorCategoria` | `$query, $categoria` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePorNivel` | `$query, $nivelMin, $nivelMax = null` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public calcularNivelRisco` | `nenhuma` | `nao declarado` | Executa calculo de indicador, progresso ou consolidacao de desempenho. |
| `public getNivelRiscoLabel` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getNivelRiscoCor` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getNivelRiscoBadgeClass` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public isCritico` | `nenhuma` | `nao declarado` | Predicado de dominio que retorna condicao booleana. |
| `public temPlanoMitigacao` | `nenhuma` | `nao declarado` | Predicado de dominio que retorna condicao booleana. |
| `public temOcorrencia` | `nenhuma` | `nao declarado` | Predicado de dominio que retorna condicao booleana. |
| `public getProbabilidadeLabel` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getImpactoLabel` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |

#### `App\Models\RiskManagement\RiscoMitigacao`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public risco` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public responsavel` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public scopeAtrasados` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePorStatus` | `$query, $status` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePorTipo` | `$query, $tipo` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public isAtrasado` | `nenhuma` | `nao declarado` | Predicado de dominio que retorna condicao booleana. |
| `public isConcluido` | `nenhuma` | `nao declarado` | Predicado de dominio que retorna condicao booleana. |
| `public getDiasRestantes` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getStatusBadgeClass` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |

#### `App\Models\RiskManagement\RiscoObjetivo`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public risco` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public objetivo` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

#### `App\Models\RiskManagement\RiscoOcorrencia`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public risco` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public scopeRecentes` | `$query, $dias = 30` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePorPeriodo` | `$query, $dataInicio, $dataFim` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public getImpactoRealLabel` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getImpactoRealCor` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public isRecente` | `$dias = 7` | `nao declarado` | Predicado de dominio que retorna condicao booleana. |

#### `App\Models\StrategicAlert`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public user` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public scopeUnread` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public markAsRead` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

#### `App\Models\StrategicPlanning\AnaliseAmbiental`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public pei` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public organizacao` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public scopeSwot` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePestel` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeCategoria` | `$query, string $categoria` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeOrdenado` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

#### `App\Models\StrategicPlanning\Arquivo`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public evolucaoIndicador` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public getExtensao` | `nenhuma` | `string` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public isImagem` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public isPdf` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public scopePorTipo` | `$query, string $tipo` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeRecentes` | `$query, int $dias = 30` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

#### `App\Models\StrategicPlanning\AtividadeCadeiaValor`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public pei` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public perspectiva` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public processos` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public scopePorPei` | `$query, string $codPei` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePorPerspectiva` | `$query, string $codPerspectiva` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

#### `App\Models\StrategicPlanning\FuturoAlmejado`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public objetivo` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

#### `App\Models\StrategicPlanning\GrauSatisfacao`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public pei` | `nenhuma` | `\Illuminate\Database\Eloquent\Relations\BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public scopeOrdenadoPorValor` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

#### `App\Models\StrategicPlanning\MissaoVisaoValores`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public pei` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public organizacao` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

#### `App\Models\StrategicPlanning\Objetivo`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public perspectiva` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public planosAcao` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public indicadores` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public futuroAlmejado` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public comentarios` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public calcularAtingimentoConsolidado` | `int $ano = null, int $mes = null` | `float` | Executa calculo de indicador, progresso ou consolidacao de desempenho. |
| `public getCorFarolConsolidado` | `int $ano = null, int $mes = null` | `?string` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getResumoDesempenho` | `int $ano = null` | `array` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public scopeOrdenadoPorNivel` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePorPerspectiva` | `$query, string $codPerspectiva` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

#### `App\Models\StrategicPlanning\ObjetivoComentario`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public user` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public objetivo` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

#### `App\Models\StrategicPlanning\PEI`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public perspectivas` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public identidadeEstrategica` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public valores` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public atividadesCadeiaValor` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public isAtivo` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public scopeAtivos` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeFuturos` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePassados` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

#### `App\Models\StrategicPlanning\Perspectiva`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public pei` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public objetivos` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public atividades` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public getIndicadoresAttribute` | `nenhuma` | `Collection` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public calcularDesempenho` | `?int $ano = null` | `float` | Executa calculo de indicador, progresso ou consolidacao de desempenho. |
| `protected calcularDesempenhoIndicadores` | `int $ano` | `float` | Executa calculo de indicador, progresso ou consolidacao de desempenho. |
| `protected calcularDesempenhoPlanos` | `int $ano` | `float` | Executa calculo de indicador, progresso ou consolidacao de desempenho. |
| `public scopeOrdenadoPorNivel` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

#### `App\Models\StrategicPlanning\ProcessoAtividadeCadeiaValor`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public atividadeCadeiaValor` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public scopePorAtividade` | `$query, string $codAtividade` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

#### `App\Models\StrategicPlanning\TemaNorteador`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public pei` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public organizacao` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

#### `App\Models\StrategicPlanning\Valor`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public pei` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public organizacao` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

#### `App\Models\SystemSetting`

- Sem metodos public/protected/private declarados alem da configuracao de propriedades.

#### `App\Models\TabAudit`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public user` | `nenhuma` | `BelongsTo` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public isExpirado` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public getDiferencas` | `nenhuma` | `array` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public scopePorTabela` | `$query, string $tabela` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePorRegistro` | `$query, string $tableId` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePorUsuario` | `$query, string $userId` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePorAcao` | `$query, string $acao` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopePorColuna` | `$query, string $coluna` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeRecentes` | `$query, int $dias = 7` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeNaoExpiradas` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeExpiradas` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

#### `App\Models\TabStatus`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `public scopeBuscarPorDescricao` | `$query, string $termo` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeOrdenadoPorDescricao` | `$query, string $direcao = 'asc'` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

#### `App\Models\User`

| Metodo | Entrada | Saida declarada | Responsabilidade |
|---|---|---|---|
| `protected casts` | `nenhuma` | `array` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public organizacoes` | `nenhuma` | `BelongsToMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public perfisAcesso` | `nenhuma` | `BelongsToMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public acoes` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public audits` | `nenhuma` | `HasMany` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public isSuperAdmin` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public isAtivo` | `nenhuma` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public deveTrocarSenha` | `nenhuma` | `bool` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public temPermissaoOrganizacao` | `Organization $org` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public perfisNaOrganizacao` | `Organization $org` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public isGestorResponsavel` | `string $codPlanoDeAcao` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public isGestorSubstituto` | `string $codPlanoDeAcao` | `bool` | Predicado de dominio que retorna condicao booleana. |
| `public scopeAtivos` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeAdministradores` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |
| `public scopeDevemTrocarSenha` | `$query` | `nao declarado` | Escopo Eloquent para compor consultas reutilizaveis. |

## 8. Controllers

### `App\Http\Controllers\Controller`

- Arquivo: `app/Http/Controllers/Controller.php`.
- Sem metodos declarados.

### `App\Http\Controllers\Reports\RelatorioController`

- Arquivo: `app/Http/Controllers/Reports/RelatorioController.php`.
- Declaracao: `class RelatorioController extends Controller`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public __construct` | `ReportGenerationService $reportService` | `nao declarado` | Inicializa dependencias ou estado do objeto. |
| `public executivo` | `Request $request, $organizacaoId = null` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public identidade` | `Request $request, $organizacaoId` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public objetivosPdf` | `Request $request` | `nao declarado` | Gera resposta de exportacao/relatorio. |
| `public objetivosExcel` | `nenhuma` | `nao declarado` | Gera resposta de exportacao/relatorio. |
| `public indicadoresPdf` | `Request $request, $organizacaoId = null` | `nao declarado` | Gera resposta de exportacao/relatorio. |
| `public indicadoresExcel` | `$organizacaoId = null` | `nao declarado` | Gera resposta de exportacao/relatorio. |
| `public planosPdf` | `Request $request` | `nao declarado` | Gera resposta de exportacao/relatorio. |
| `public planosExcel` | `Request $request` | `nao declarado` | Gera resposta de exportacao/relatorio. |
| `public riscosPdf` | `Request $request` | `nao declarado` | Gera resposta de exportacao/relatorio. |
| `public riscosExcel` | `Request $request` | `nao declarado` | Gera resposta de exportacao/relatorio. |
| `public integrado` | `Request $request, $organizacaoId = null` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

## 9. Componentes Livewire

### `App\Livewire\ActionPlan\AtribuirResponsaveis`

- Arquivo: `app/Livewire/ActionPlan/AtribuirResponsaveis.php`.
- Declaracao: `class AtribuirResponsaveis extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `$planoId` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public carregarDados` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public adicionar` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public remover` | `$pivotId` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\ActionPlan\DetalharPlano`

- Arquivo: `app/Livewire/ActionPlan/DetalharPlano.php`.
- Declaracao: `class DetalharPlano extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\ActionPlan\GerenciarEntregas`

- Arquivo: `app/Livewire/ActionPlan/GerenciarEntregas.php`.
- Declaracao: `class GerenciarEntregas extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `$planoId` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public carregarDados` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public create` | `nenhuma` | `nao declarado` | Prepara ou cria novo registro/estado de cadastro. |
| `public edit` | `$id` | `nao declarado` | Carrega registro existente para edicao. |
| `public save` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public delete` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public resetForm` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public redistribuirPesos` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\ActionPlan\ListarPlanos`

- Arquivo: `app/Livewire/ActionPlan/ListarPlanos.php`.
- Declaracao: `class ListarPlanos extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public closeSuccessModal` | `nenhuma` | `nao declarado` | Fecha modal ou cancela estado transitorio da UI. |
| `public pedirAjudaIA` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public aplicarSugestao` | `$nome, $justificativa = null` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public atualizarAno` | `$ano` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public atualizarPEI` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `private carregarPEI` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public carregarObjetivos` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public create` | `nenhuma` | `nao declarado` | Prepara ou cria novo registro/estado de cadastro. |
| `public edit` | `$id` | `nao declarado` | Carrega registro existente para edicao. |
| `public save` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public confirmDelete` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public delete` | `nenhuma` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public resetForm` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\Admin\ConfiguracaoSistema`

- Arquivo: `app/Livewire/Admin/ConfiguracaoSistema.php`.
- Declaracao: `class ConfiguracaoSistema extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public testConnection` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public saveAiSettings` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\Audit\DetalharLog`

- Arquivo: `app/Livewire/Audit/DetalharLog.php`.
- Declaracao: `class DetalharLog extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\Audit\ListarLogs`

- Arquivo: `app/Livewire/Audit/ListarLogs.php`.
- Declaracao: `class ListarLogs extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public updated` | `$propertyName` | `nao declarado` | Reage a alteracao de propriedade Livewire, normalmente validando ou recarregando dados dependentes. |
| `public verDetalhes` | `$id` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `protected getQuery` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public exportar` | `nenhuma` | `nao declarado` | Gera resposta de exportacao/relatorio. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\Auth\TrocarSenha`

- Arquivo: `app/Livewire/Auth/TrocarSenha.php`.
- Declaracao: `class TrocarSenha extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `protected rules` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public trocarSenha` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public logout` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\Dashboard\Index`

- Arquivo: `app/Livewire/Dashboard/Index.php`.
- Declaracao: `class Index extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public atualizarAno` | `$ano` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public atualizarPEI` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public carregarPEI` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public generateAiSummary` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `private carregarNomeOrganizacao` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public atualizarDadosGraficos` | `nenhuma` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |
| `private getStats` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `private getMinhasEntregas` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `private getMinhasEntregasAgrupadas` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `private getComentariosRecentes` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `private getChartBSC` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `private getChartRiscosNivel` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `private getChartPlanos` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `private getChartEvolucao` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `private getCorAtingimento` | `$percentual` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getMentorStatus` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |

### `App\Livewire\Dashboard\PeiChecklist`

- Arquivo: `app/Livewire/Dashboard/PeiChecklist.php`.
- Declaracao: `class PeiChecklist extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `PeiGuidanceService $service` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public dismiss` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public refreshGuidance` | `$id = null, PeiGuidanceService $service = null` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\Deliverables\DeliverablesBoard`

- Arquivo: `app/Livewire/Deliverables/DeliverablesBoard.php`.
- Declaracao: `class DeliverablesBoard extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `?string $planoId = null` | `void` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public carregarListasEstrategicas` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public updatedPerspectivaId` | `nenhuma` | `nao declarado` | Reage a alteracao de propriedade Livewire, normalmente validando ou recarregando dados dependentes. |
| `public updatedObjetivoId` | `nenhuma` | `nao declarado` | Reage a alteracao de propriedade Livewire, normalmente validando ou recarregando dados dependentes. |
| `public mudarPlano` | `$id` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |
| `protected getEntregas` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `protected getEntregasPorStatus` | `nenhuma` | `array` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `protected getLabels` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `protected getUsuarios` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `protected calcularProgresso` | `nenhuma` | `void` | Executa calculo de indicador, progresso ou consolidacao de desempenho. |
| `public setView` | `string $view` | `void` | Define estado interno ou propriedade persistida. |
| `public limparFiltros` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public toggleArquivados` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public toggleLixeira` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public calendarioAnterior` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public calendarioProximo` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public calendarioHoje` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public calendarioIrPara` | `int $mes, int $ano` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public timelineAnterior` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public timelineProximo` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public timelineHoje` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public timelineZoomIn` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public timelineZoomOut` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public timelineDefinirPeriodo` | `string $inicio, string $fim` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public atualizarPrazoEntrega` | `string $entregaId, string $novoPrazo` | `void` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public openQuickAdd` | `string $status = 'Não Iniciado'` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public closeQuickAdd` | `nenhuma` | `void` | Fecha modal ou cancela estado transitorio da UI. |
| `public criarRapido` | `nenhuma` | `void` | Prepara ou cria novo registro/estado de cadastro. |
| `public openEditModal` | `?string $entregaId = null` | `void` | Carrega registro existente para edicao. |
| `public closeEditModal` | `nenhuma` | `void` | Carrega registro existente para edicao. |
| `protected resetEditForm` | `nenhuma` | `void` | Carrega registro existente para edicao. |
| `public salvarEntrega` | `nenhuma` | `void` | Valida entrada e persiste dados do formulario/entidade. |
| `public openDetails` | `string $entregaId` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public closeDetails` | `nenhuma` | `void` | Fecha modal ou cancela estado transitorio da UI. |
| `public atualizarTitulo` | `string $entregaId, string $titulo` | `void` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public atualizarStatus` | `string $entregaId, string $status` | `void` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public atualizarPrioridade` | `string $entregaId, string $prioridade` | `void` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public atualizarResponsaveis` | `string $entregaId, array $userIds` | `void` | Valida entrada e persiste dados do formulario/entidade. |
| `public atualizarPrazo` | `string $entregaId, ?string $prazo` | `void` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public reordenarEntregas` | `array $ordem` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public moverParaStatus` | `string $entregaId, string $novoStatus, int $novaPosicao` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public arquivar` | `string $entregaId` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public desarquivar` | `string $entregaId` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public confirmDeleteEntrega` | `string $entregaId, bool $isPermanent = false` | `void` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public excluir` | `nenhuma` | `void` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public restaurar` | `string $entregaId` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public excluirPermanente` | `string $entregaId` | `void` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public openLabelsModal` | `string $entregaId` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public closeLabelsModal` | `nenhuma` | `void` | Fecha modal ou cancela estado transitorio da UI. |
| `public toggleLabel` | `string $entregaId, string $labelId` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public criarLabel` | `nenhuma` | `void` | Prepara ou cria novo registro/estado de cadastro. |
| `public setRespondendo` | `?string $comentarioId` | `void` | Define estado interno ou propriedade persistida. |
| `public adicionarComentario` | `$entregaId, $conteudo = null, $comentarioPaiId = null` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public excluirComentario` | `string $comentarioId` | `void` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public updatedAnexosUpload` | `nenhuma` | `void` | Reage a alteracao de propriedade Livewire, normalmente validando ou recarregando dados dependentes. |
| `public excluirAnexo` | `string $anexoId` | `void` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public closeSuccessModal` | `nenhuma` | `void` | Fecha modal ou cancela estado transitorio da UI. |
| `public poll` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

### `App\Livewire\LeadsTable`

- Arquivo: `app/Livewire/LeadsTable.php`.
- Declaracao: `class LeadsTable extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `protected rules` | `nenhuma` | `array` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public updatingSearch` | `string $value` | `void` | Reage antes da alteracao de propriedade Livewire, normalmente resetando paginacao/filtros. |
| `public updatingStatus` | `string $value` | `void` | Reage antes da alteracao de propriedade Livewire, normalmente resetando paginacao/filtros. |
| `public resetFilters` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public getStatusesProperty` | `nenhuma` | `array` | Computed property Livewire para expor dados derivados a view. |
| `public getStatusOptionsProperty` | `nenhuma` | `array` | Computed property Livewire para expor dados derivados a view. |
| `protected baseQuery` | `nenhuma` | `Builder` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `protected paginatedLeads` | `nenhuma` | `LengthAwarePaginator` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public create` | `nenhuma` | `void` | Prepara ou cria novo registro/estado de cadastro. |
| `public edit` | `int $leadId` | `void` | Carrega registro existente para edicao. |
| `public closeFormModal` | `nenhuma` | `void` | Fecha modal ou cancela estado transitorio da UI. |
| `public save` | `nenhuma` | `void` | Valida entrada e persiste dados do formulario/entidade. |
| `public confirmDelete` | `int $leadId` | `void` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public cancelDelete` | `nenhuma` | `void` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public delete` | `nenhuma` | `void` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public exportCsv` | `nenhuma` | `nao declarado` | Gera resposta de exportacao/relatorio. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |
| `protected resetForm` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `protected notify` | `string $message, string $style = 'success'` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `protected applySearchFilter` | `Builder $query, string $search` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

### `App\Livewire\Organization\DetalharOrganizacao`

- Arquivo: `app/Livewire/Organization/DetalharOrganizacao.php`.
- Declaracao: `class DetalharOrganizacao extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\Organization\ListarOrganizacoes`

- Arquivo: `app/Livewire/Organization/ListarOrganizacoes.php`.
- Declaracao: `class ListarOrganizacoes extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public pedirAjudaIA` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public aplicarSugestaoSigla` | `$sigla` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `protected rules` | `nenhuma` | `array` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public updatingSearch` | `string $value` | `void` | Reage antes da alteracao de propriedade Livewire, normalmente resetando paginacao/filtros. |
| `public resetFilters` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public getOrganizacoesPaiProperty` | `nenhuma` | `nao declarado` | Computed property Livewire para expor dados derivados a view. |
| `protected baseQuery` | `nenhuma` | `Builder` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `protected paginatedOrganizacoes` | `nenhuma` | `LengthAwarePaginator` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public create` | `nenhuma` | `void` | Prepara ou cria novo registro/estado de cadastro. |
| `public edit` | `string $id` | `void` | Carrega registro existente para edicao. |
| `public closeFormModal` | `nenhuma` | `void` | Fecha modal ou cancela estado transitorio da UI. |
| `public save` | `nenhuma` | `void` | Valida entrada e persiste dados do formulario/entidade. |
| `public closeSuccessModal` | `nenhuma` | `nao declarado` | Fecha modal ou cancela estado transitorio da UI. |
| `public closeErrorModal` | `nenhuma` | `nao declarado` | Fecha modal ou cancela estado transitorio da UI. |
| `public confirmDelete` | `string $id` | `void` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public cancelDelete` | `nenhuma` | `void` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public delete` | `nenhuma` | `void` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |
| `protected resetForm` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `protected notify` | `string $message, string $style = 'success'` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `protected applySearchFilter` | `Builder $query, string $search` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

### `App\Livewire\PerformanceIndicators\DetalharIndicador`

- Arquivo: `app/Livewire/PerformanceIndicators/DetalharIndicador.php`.
- Declaracao: `class DetalharIndicador extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public atualizarAno` | `$ano` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public mount` | `$id` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `protected carregarAnosDisponiveis` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public updatedAnoFiltro` | `nenhuma` | `nao declarado` | Reage a alteracao de propriedade Livewire, normalmente validando ou recarregando dados dependentes. |
| `protected prepareChartData` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\PerformanceIndicators\LancarEvolucao`

- Arquivo: `app/Livewire/PerformanceIndicators/LancarEvolucao.php`.
- Declaracao: `class LancarEvolucao extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public atualizarAno` | `$ano` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public mount` | `$indicadorId` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public updatedAno` | `nenhuma` | `nao declarado` | Reage a alteracao de propriedade Livewire, normalmente validando ou recarregando dados dependentes. |
| `public updatedMes` | `nenhuma` | `nao declarado` | Reage a alteracao de propriedade Livewire, normalmente validando ou recarregando dados dependentes. |
| `public carregarPeriodo` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public carregarHistorico` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public salvar` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public excluirArquivo` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\PerformanceIndicators\ListarIndicadores`

- Arquivo: `app/Livewire/PerformanceIndicators/ListarIndicadores.php`.
- Declaracao: `class ListarIndicadores extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public atualizarPEI` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public carregarListasAuxiliares` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public create` | `\App\Services\PeiGuidanceService $service` | `nao declarado` | Prepara ou cria novo registro/estado de cadastro. |
| `public edit` | `$id` | `nao declarado` | Carrega registro existente para edicao. |
| `public save` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public abrirMetas` | `$id` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public salvarMeta` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public excluirMeta` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public abrirLinhaBase` | `$id` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public salvarLinhaBase` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public excluirLinhaBase` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public confirmDelete` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public delete` | `nenhuma` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public resetForm` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public closeSuccessModal` | `nenhuma` | `nao declarado` | Fecha modal ou cancela estado transitorio da UI. |
| `public closeErrorModal` | `nenhuma` | `nao declarado` | Fecha modal ou cancela estado transitorio da UI. |
| `public pedirAjudaIA` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public aplicarSugestao` | `$nome, $desc, $unidade, $formula` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\Profile\UpdateThemeColorForm`

- Arquivo: `app/Livewire/Profile/UpdateThemeColorForm.php`.
- Declaracao: `class UpdateThemeColorForm extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public updated` | `$propertyName` | `nao declarado` | Reage a alteracao de propriedade Livewire, normalmente validando ou recarregando dados dependentes. |
| `public updateThemeColor` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\PublicNavbar`

- Arquivo: `app/Livewire/PublicNavbar.php`.
- Declaracao: `class PublicNavbar extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\Reports\AgendarRelatorio`

- Arquivo: `app/Livewire/Reports/AgendarRelatorio.php`.
- Declaracao: `class AgendarRelatorio extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public carregar` | `$tipo, $filtros` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public salvar` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\Reports\GerenciarAgendamentos`

- Arquivo: `app/Livewire/Reports/GerenciarAgendamentos.php`.
- Declaracao: `class GerenciarAgendamentos extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public toggleStatus` | `$id` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public delete` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\Reports\HistoricoRelatorios`

- Arquivo: `app/Livewire/Reports/HistoricoRelatorios.php`.
- Declaracao: `class HistoricoRelatorios extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public download` | `$id` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\Reports\ListarRelatorios`

- Arquivo: `app/Livewire/Reports/ListarRelatorios.php`.
- Declaracao: `class ListarRelatorios extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public atualizarAno` | `$ano` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public atualizarPEI` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `private carregarPEI` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `private carregarIdentidade` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `private carregarPerspectivas` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public updatedOrganizacaoId` | `$value` | `nao declarado` | Reage a alteracao de propriedade Livewire, normalmente validando ou recarregando dados dependentes. |
| `public setOrganizacao` | `$id` | `nao declarado` | Define estado interno ou propriedade persistida. |
| `public getQueryParamsProperty` | `nenhuma` | `nao declarado` | Computed property Livewire para expor dados derivados a view. |
| `public gerarInsightIA` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public download` | `$id` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\RiskManagement\GerenciarMitigacoes`

- Arquivo: `app/Livewire/RiskManagement/GerenciarMitigacoes.php`.
- Declaracao: `class GerenciarMitigacoes extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `$riscoId` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public carregarDados` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public create` | `nenhuma` | `nao declarado` | Prepara ou cria novo registro/estado de cadastro. |
| `public edit` | `$id` | `nao declarado` | Carrega registro existente para edicao. |
| `public save` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public delete` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public resetForm` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\RiskManagement\ListarRiscos`

- Arquivo: `app/Livewire/RiskManagement/ListarRiscos.php`.
- Declaracao: `class ListarRiscos extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public closeSuccessModal` | `nenhuma` | `nao declarado` | Fecha modal ou cancela estado transitorio da UI. |
| `public closeErrorModal` | `nenhuma` | `nao declarado` | Fecha modal ou cancela estado transitorio da UI. |
| `public pedirAjudaIA` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public aplicarSugestao` | `$titulo, $categoria, $descricao` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public atualizarPEI` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `private carregarPEI` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public carregarListasAuxiliares` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public updatingSearch` | `nenhuma` | `nao declarado` | Reage antes da alteracao de propriedade Livewire, normalmente resetando paginacao/filtros. |
| `public create` | `nenhuma` | `nao declarado` | Prepara ou cria novo registro/estado de cadastro. |
| `public edit` | `$id` | `nao declarado` | Carrega registro existente para edicao. |
| `public save` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public confirmDelete` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public delete` | `nenhuma` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public resetForm` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\RiskManagement\MatrizRiscos`

- Arquivo: `app/Livewire/RiskManagement/MatrizRiscos.php`.
- Declaracao: `class MatrizRiscos extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public atualizarMatriz` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public carregarMatriz` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\RiskManagement\RegistrarOcorrencias`

- Arquivo: `app/Livewire/RiskManagement/RegistrarOcorrencias.php`.
- Declaracao: `class RegistrarOcorrencias extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `$riscoId` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public carregarDados` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public create` | `nenhuma` | `nao declarado` | Prepara ou cria novo registro/estado de cadastro. |
| `public edit` | `$id` | `nao declarado` | Carrega registro existente para edicao. |
| `public save` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public delete` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public resetForm` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\Shared\SeletorAno`

- Arquivo: `app/Livewire/Shared/SeletorAno.php`.
- Declaracao: `class SeletorAno extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public atualizarPeiId` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public carregarAnos` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public selecionar` | `$ano` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `private trocarPeiSilencioso` | `PEI $pei` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\Shared\SeletorOrganizacao`

- Arquivo: `app/Livewire/Shared/SeletorOrganizacao.php`.
- Declaracao: `class SeletorOrganizacao extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public carregarOrganizacoes` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public selecionar` | `$id` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `private atualizarSessao` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\Shared\SeletorPei`

- Arquivo: `app/Livewire/Shared/SeletorPei.php`.
- Declaracao: `class SeletorPei extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public carregarPEIs` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `private definirSessao` | `PEI $pei` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public selecionar` | `$id` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\Shared\StrategicAlertsBell`

- Arquivo: `app/Livewire/Shared/StrategicAlertsBell.php`.
- Declaracao: `class StrategicAlertsBell extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public refreshCount` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public markAllAsRead` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public getRecentAlerts` | `nenhuma` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\StrategicPlanning\AnalisePESTEL`

- Arquivo: `app/Livewire/StrategicPlanning/AnalisePESTEL.php`.
- Declaracao: `class AnalisePESTEL extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public pedirAjudaIA` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public adicionarSugerido` | `$categoria, $item` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public atualizarPEI` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `private carregarPEI` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public carregarDados` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public create` | `$categoria` | `nao declarado` | Prepara ou cria novo registro/estado de cadastro. |
| `public edit` | `$id` | `nao declarado` | Carrega registro existente para edicao. |
| `public save` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public delete` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public resetForm` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\StrategicPlanning\AnaliseSWOT`

- Arquivo: `app/Livewire/StrategicPlanning/AnaliseSWOT.php`.
- Declaracao: `class AnaliseSWOT extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public pedirAjudaIA` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public adicionarSugerido` | `$categoria, $item` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public atualizarPEI` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `private carregarPEI` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public carregarDados` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public toggleModoVisualizacao` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public create` | `$categoria` | `nao declarado` | Prepara ou cria novo registro/estado de cadastro. |
| `public edit` | `$id` | `nao declarado` | Carrega registro existente para edicao. |
| `public save` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public delete` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public resetForm` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\StrategicPlanning\DetalharGrauSatisfacao`

- Arquivo: `app/Livewire/StrategicPlanning/DetalharGrauSatisfacao.php`.
- Declaracao: `class DetalharGrauSatisfacao extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\StrategicPlanning\DetalharIdentidade`

- Arquivo: `app/Livewire/StrategicPlanning/DetalharIdentidade.php`.
- Declaracao: `class DetalharIdentidade extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\StrategicPlanning\DetalharObjetivo`

- Arquivo: `app/Livewire/StrategicPlanning/DetalharObjetivo.php`.
- Declaracao: `class DetalharObjetivo extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public carregarObjetivo` | `$id` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `private getCorFarolManual` | `$val` | `nao declarado` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public postarComentario` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public removerComentario` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\StrategicPlanning\DetalharPei`

- Arquivo: `app/Livewire/StrategicPlanning/DetalharPei.php`.
- Declaracao: `class DetalharPei extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public carregarEstatisticas` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\StrategicPlanning\DetalharPerspectiva`

- Arquivo: `app/Livewire/StrategicPlanning/DetalharPerspectiva.php`.
- Declaracao: `class DetalharPerspectiva extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\StrategicPlanning\DetalharValor`

- Arquivo: `app/Livewire/StrategicPlanning/DetalharValor.php`.
- Declaracao: `class DetalharValor extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\StrategicPlanning\GerenciarFuturoAlmejado`

- Arquivo: `app/Livewire/StrategicPlanning/GerenciarFuturoAlmejado.php`.
- Declaracao: `class GerenciarFuturoAlmejado extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `$objetivoId` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public carregarFuturos` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public create` | `nenhuma` | `nao declarado` | Prepara ou cria novo registro/estado de cadastro. |
| `public edit` | `$id` | `nao declarado` | Carrega registro existente para edicao. |
| `public save` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public delete` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public resetForm` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\StrategicPlanning\GerenciarTemasNorteadores`

- Arquivo: `app/Livewire/StrategicPlanning/GerenciarTemasNorteadores.php`.
- Declaracao: `class GerenciarTemasNorteadores extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public pedirAjudaIA` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public aplicarSugestao` | `$nome` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public atualizarPEI` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `private carregarPEI` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public updatingSearch` | `nenhuma` | `nao declarado` | Reage antes da alteracao de propriedade Livewire, normalmente resetando paginacao/filtros. |
| `public create` | `nenhuma` | `nao declarado` | Prepara ou cria novo registro/estado de cadastro. |
| `public edit` | `$id` | `nao declarado` | Carrega registro existente para edicao. |
| `public save` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public confirmDelete` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public delete` | `nenhuma` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public resetForm` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\StrategicPlanning\ListarGrausSatisfacao`

- Arquivo: `app/Livewire/StrategicPlanning/ListarGrausSatisfacao.php`.
- Declaracao: `class ListarGrausSatisfacao extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public closeSuccessModal` | `nenhuma` | `nao declarado` | Fecha modal ou cancela estado transitorio da UI. |
| `public closeErrorModal` | `nenhuma` | `nao declarado` | Fecha modal ou cancela estado transitorio da UI. |
| `public pedirAjudaIA` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public aplicarSugestao` | `$nome, $cor, $min, $max` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `protected rules` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public updatingSearch` | `nenhuma` | `nao declarado` | Reage antes da alteracao de propriedade Livewire, normalmente resetando paginacao/filtros. |
| `public openModal` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public closeModal` | `nenhuma` | `nao declarado` | Fecha modal ou cancela estado transitorio da UI. |
| `public resetForm` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public save` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public edit` | `$id` | `nao declarado` | Carrega registro existente para edicao. |
| `public confirmDelete` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public delete` | `nenhuma` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public cancelDelete` | `nenhuma` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\StrategicPlanning\ListarObjetivos`

- Arquivo: `app/Livewire/StrategicPlanning/ListarObjetivos.php`.
- Declaracao: `class ListarObjetivos extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public closeSuccessModal` | `nenhuma` | `nao declarado` | Fecha modal ou cancela estado transitorio da UI. |
| `public closeErrorModal` | `nenhuma` | `nao declarado` | Fecha modal ou cancela estado transitorio da UI. |
| `public updatedNomObjetivo` | `$value` | `nao declarado` | Reage a alteracao de propriedade Livewire, normalmente validando ou recarregando dados dependentes. |
| `public auditSmart` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public pedirAjudaIA` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public aplicarSugestao` | `$nome, $descricao, $ordem` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public atualizarPEI` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `private carregarPEI` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public carregarPerspectivas` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public create` | `$perspectivaId = null, \App\Services\PeiGuidanceService $service = null` | `nao declarado` | Prepara ou cria novo registro/estado de cadastro. |
| `public edit` | `$id` | `nao declarado` | Carrega registro existente para edicao. |
| `public updatedCodPerspectiva` | `$value` | `nao declarado` | Reage a alteracao de propriedade Livewire, normalmente validando ou recarregando dados dependentes. |
| `public save` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public confirmDelete` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public delete` | `nenhuma` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public resetForm` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\StrategicPlanning\ListarPeis`

- Arquivo: `app/Livewire/StrategicPlanning/ListarPeis.php`.
- Declaracao: `class ListarPeis extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public closeSuccessModal` | `nenhuma` | `nao declarado` | Fecha modal ou cancela estado transitorio da UI. |
| `public closeErrorModal` | `nenhuma` | `nao declarado` | Fecha modal ou cancela estado transitorio da UI. |
| `public updatingSearch` | `nenhuma` | `nao declarado` | Reage antes da alteracao de propriedade Livewire, normalmente resetando paginacao/filtros. |
| `public resetFilters` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public create` | `nenhuma` | `nao declarado` | Prepara ou cria novo registro/estado de cadastro. |
| `public edit` | `$id` | `nao declarado` | Carrega registro existente para edicao. |
| `public save` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public confirmDelete` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public delete` | `nenhuma` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public resetForm` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\StrategicPlanning\ListarPerspectivas`

- Arquivo: `app/Livewire/StrategicPlanning/ListarPerspectivas.php`.
- Declaracao: `class ListarPerspectivas extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public closeSuccessModal` | `nenhuma` | `nao declarado` | Fecha modal ou cancela estado transitorio da UI. |
| `public closeErrorModal` | `nenhuma` | `nao declarado` | Fecha modal ou cancela estado transitorio da UI. |
| `public pedirAjudaIA` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public aplicarSugestao` | `$nome, $ordem` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public testarNotificacao` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public atualizarPEI` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `private carregarPEI` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public carregarPerspectivas` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public create` | `\App\Services\PeiGuidanceService $service` | `nao declarado` | Prepara ou cria novo registro/estado de cadastro. |
| `public edit` | `$id` | `nao declarado` | Carrega registro existente para edicao. |
| `public save` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public confirmDelete` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public delete` | `nenhuma` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public resetForm` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\StrategicPlanning\ListarValores`

- Arquivo: `app/Livewire/StrategicPlanning/ListarValores.php`.
- Declaracao: `class ListarValores extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public atualizarPEI` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `private carregarPEI` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public carregarValores` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public create` | `nenhuma` | `nao declarado` | Prepara ou cria novo registro/estado de cadastro. |
| `public edit` | `$id` | `nao declarado` | Carrega registro existente para edicao. |
| `public save` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public delete` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public resetForm` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\StrategicPlanning\MapaEstrategico`

- Arquivo: `app/Livewire/StrategicPlanning/MapaEstrategico.php`.
- Declaracao: `class MapaEstrategico extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public setViewMode` | `$mode` | `nao declarado` | Define estado interno ou propriedade persistida. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public atualizarPEI` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `private carregarPEI` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public carregarMapa` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public carregarIdentidadeEstrategica` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public getCorPorPercentual` | `$percentual` | `string` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public getCoresPerspectiva` | `$nivel` | `array` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public abrirMemoriaCalculo` | `$index` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public fecharMemoriaCalculo` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\StrategicPlanning\MissaoVisao`

- Arquivo: `app/Livewire/StrategicPlanning/MissaoVisao.php`.
- Declaracao: `class MissaoVisao extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `nenhuma` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public pedirAjudaIA` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public aplicarIdentidade` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public adicionarValorSugerido` | `$nome, $descricao` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public atualizarPEI` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `private carregarPEI` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public resetarDados` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public carregarDados` | `nenhuma` | `nao declarado` | Carrega dados auxiliares ou colecoes necessarias para tela/regra. |
| `public habilitarEdicao` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public cancelar` | `nenhuma` | `nao declarado` | Fecha modal ou cancela estado transitorio da UI. |
| `public salvar` | `nenhuma` | `nao declarado` | Valida entrada e persiste dados do formulario/entidade. |
| `public adicionarValor` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public confirmDeleteValor` | `$id` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public removerValor` | `nenhuma` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public editarValor` | `$id` | `nao declarado` | Carrega registro existente para edicao. |
| `public atualizarValor` | `nenhuma` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public cancelarEdicaoValor` | `nenhuma` | `nao declarado` | Fecha modal ou cancela estado transitorio da UI. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\UserManagement\DetalharUsuario`

- Arquivo: `app/Livewire/UserManagement/DetalharUsuario.php`.
- Declaracao: `class DetalharUsuario extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Inicializa estado do componente Livewire com parametros da rota/contexto. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |

### `App\Livewire\UserManagement\ListarUsuarios`

- Arquivo: `app/Livewire/UserManagement/ListarUsuarios.php`.
- Declaracao: `class ListarUsuarios extends Component`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `protected rules` | `nenhuma` | `array` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public updatingSearch` | `nenhuma` | `void` | Reage antes da alteracao de propriedade Livewire, normalmente resetando paginacao/filtros. |
| `public resetFilters` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public getOrganizacoesOptionsProperty` | `nenhuma` | `nao declarado` | Computed property Livewire para expor dados derivados a view. |
| `public getPerfisOptionsProperty` | `nenhuma` | `nao declarado` | Computed property Livewire para expor dados derivados a view. |
| `protected baseQuery` | `nenhuma` | `Builder` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `protected paginatedUsuarios` | `nenhuma` | `LengthAwarePaginator` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public create` | `nenhuma` | `void` | Prepara ou cria novo registro/estado de cadastro. |
| `public edit` | `string $id` | `void` | Carrega registro existente para edicao. |
| `public adicionarVinculo` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public removerVinculo` | `$index` | `nao declarado` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public closeFormModal` | `nenhuma` | `void` | Fecha modal ou cancela estado transitorio da UI. |
| `public save` | `nenhuma` | `void` | Valida entrada e persiste dados do formulario/entidade. |
| `public confirmDelete` | `string $id` | `void` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public cancelDelete` | `nenhuma` | `void` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public delete` | `nenhuma` | `void` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public render` | `nenhuma` | `nao declarado` | Retorna a view Blade do componente com dados calculados para exibicao. |
| `protected resetForm` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `protected notify` | `string $message, string $style = 'success'` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

## 10. Services

### `App\Services\AI\AiProviderInterface`

- Arquivo: `app/Services/AI/AiProviderInterface.php`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public suggest` | `string $prompt, string $context = ''` | `?string;` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public testConnection` | `nenhuma` | `array;` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public analyzeSmart` | `string $type, string $title, string $description = ''` | `?string;` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public summarizeStrategy` | `array $stats, string $orgName` | `?string;` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public analyzeTrends` | `array $indicatorData, string $orgName` | `?string;` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

### `App\Services\AI\AiServiceFactory`

- Arquivo: `app/Services/AI/AiServiceFactory.php`.
- Sem metodos declarados.

### `App\Services\AI\GeminiProvider`

- Arquivo: `app/Services/AI/GeminiProvider.php`.
- Declaracao: `class GeminiProvider implements AiProviderInterface`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public __construct` | `?string $apiKey = null` | `nao declarado` | Inicializa dependencias ou estado do objeto. |
| `public suggest` | `string $prompt, string $context = ''` | `?string` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public testConnection` | `nenhuma` | `array` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public analyzeSmart` | `string $type, string $title, string $description = ''` | `?string` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public summarizeStrategy` | `array $stats, string $orgName` | `?string` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public analyzeTrends` | `array $indicatorData, string $orgName` | `?string` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

### `App\Services\AI\OpenAiProvider`

- Arquivo: `app/Services/AI/OpenAiProvider.php`.
- Declaracao: `class OpenAiProvider implements AiProviderInterface`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public __construct` | `?string $apiKey = null` | `nao declarado` | Inicializa dependencias ou estado do objeto. |
| `public suggest` | `string $prompt, string $context = ''` | `?string` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public testConnection` | `nenhuma` | `array` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public analyzeSmart` | `string $type, string $title, string $description = ''` | `?string` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public summarizeStrategy` | `array $stats, string $orgName` | `?string` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public analyzeTrends` | `array $indicatorData, string $orgName` | `?string` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

### `App\Services\IndicadorCalculoService`

- Arquivo: `app/Services/IndicadorCalculoService.php`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public calcularProgressoPlano` | `PlanoDeAcao $plano, bool $apenasRaiz = true` | `float` | Executa calculo de indicador, progresso ou consolidacao de desempenho. |
| `protected calcularMediaSimples` | `Collection $entregas` | `float` | Executa calculo de indicador, progresso ou consolidacao de desempenho. |
| `protected calcularMediaPonderada` | `Collection $entregas, float $somaPesos` | `float` | Executa calculo de indicador, progresso ou consolidacao de desempenho. |
| `protected getProgressoEntrega` | `Entrega $entrega` | `float` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `protected calcularProgressoSubEntregas` | `Entrega $entrega` | `float` | Executa calculo de indicador, progresso ou consolidacao de desempenho. |
| `protected getEntregasValidas` | `PlanoDeAcao $plano, bool $apenasRaiz = true` | `Collection` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public atualizarIndicadorAutomatico` | `Indicador $indicador` | `?EvolucaoIndicador` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public atualizarIndicadoresDoPlano` | `PlanoDeAcao $plano` | `int` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |
| `public validarPesosPlano` | `PlanoDeAcao $plano` | `array` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public redistribuirPesosIguais` | `PlanoDeAcao $plano` | `int` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public getEstatisticasPlano` | `PlanoDeAcao $plano` | `array` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `public simularCalculo` | `PlanoDeAcao $plano` | `array` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public calcularProgressoPlanoNoAno` | `PlanoDeAcao $plano, int $ano` | `array` | Executa calculo de indicador, progresso ou consolidacao de desempenho. |
| `public calcularAtingimentoPerspectiva` | `\App\Models\StrategicPlanning\Perspectiva $perspectiva, int $ano` | `float` | Executa calculo de indicador, progresso ou consolidacao de desempenho. |
| `public calcularAtingimentoObjetivo` | `\App\Models\StrategicPlanning\Objetivo $objetivo, int $ano` | `float` | Executa calculo de indicador, progresso ou consolidacao de desempenho. |

### `App\Services\NotificationService`

- Arquivo: `app/Services/NotificationService.php`.
- Sem metodos declarados.

### `App\Services\PeiGuidanceService`

- Arquivo: `app/Services/PeiGuidanceService.php`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public analyzeCompleteness` | `?string $peiId = null` | `array` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `private buildResponse` | `array $phases, string $currentPhaseKey, int $progress, $pei, string $msg, string $route, string $label` | `array` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `private getNextStepInfo` | `string $currentPhase` | `?array` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |
| `private getEmptyPhasesStructure` | `nenhuma` | `array` | Consulta ou calcula informacao derivada usada por views, regras de negocio ou exports. |

### `App\Services\Reports\ReportGenerationService`

- Arquivo: `app/Services/Reports/ReportGenerationService.php`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public __construct` | `\App\Services\IndicadorCalculoService $calculoService` | `nao declarado` | Inicializa dependencias ou estado do objeto. |
| `public generateExecutivo` | `$organizacaoId, $ano, $periodo, $perspectivaId = null` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public generateIdentidade` | `$organizacaoId, $ano = null` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public __construct` | `$graus` | `nao declarado` | Inicializa dependencias ou estado do objeto. |
| `public __invoke` | `$percentual` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public generateObjetivos` | `$organizacaoId = null, $ano = null, $perspectivaId = null` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public generateIndicadores` | `$organizacaoId = null, $ano = null, $periodo = null` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public generatePlanos` | `$organizacaoId = null, $ano = null` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public generateRiscos` | `$organizacaoId = null` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public generateIntegrado` | `$organizacaoId, $ano, $periodo, $includeAi = true` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

## 11. Policies, Gates e autorizacao

### `App\Policies\IndicadorPolicy`

- Arquivo: `app/Policies/IndicadorPolicy.php`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public viewAny` | `User $user` | `bool` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public view` | `User $user, Indicador $indicador` | `bool` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public create` | `User $user` | `bool` | Prepara ou cria novo registro/estado de cadastro. |
| `public update` | `User $user, Indicador $indicador` | `bool` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public delete` | `User $user, Indicador $indicador` | `bool` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |

### `App\Policies\OrganizationPolicy`

- Arquivo: `app/Policies/OrganizationPolicy.php`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public viewAny` | `User $user` | `bool` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public view` | `User $user, Organization $organization` | `bool` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public create` | `User $user` | `bool` | Prepara ou cria novo registro/estado de cadastro. |
| `public update` | `User $user, Organization $organization` | `bool` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public delete` | `User $user, Organization $organization` | `bool` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public restore` | `User $user, Organization $organization` | `bool` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public forceDelete` | `User $user, Organization $organization` | `bool` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |

### `App\Policies\PlanoDeAcaoPolicy`

- Arquivo: `app/Policies/PlanoDeAcaoPolicy.php`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public viewAny` | `User $user` | `bool` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public view` | `User $user, PlanoDeAcao $planoDeAcao` | `bool` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public create` | `User $user` | `bool` | Prepara ou cria novo registro/estado de cadastro. |
| `public update` | `User $user, PlanoDeAcao $planoDeAcao` | `bool` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public delete` | `User $user, PlanoDeAcao $planoDeAcao` | `bool` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |
| `public restore` | `User $user, PlanoDeAcao $planoDeAcao` | `bool` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public forceDelete` | `User $user, PlanoDeAcao $planoDeAcao` | `bool` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |

### `App\Policies\RiscoPolicy`

- Arquivo: `app/Policies/RiscoPolicy.php`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public viewAny` | `User $user` | `bool` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public view` | `User $user, Risco $risco` | `bool` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public create` | `User $user` | `bool` | Prepara ou cria novo registro/estado de cadastro. |
| `public update` | `User $user, Risco $risco` | `bool` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public delete` | `User $user, Risco $risco` | `bool` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |

### `App\Policies\UserPolicy`

- Arquivo: `app/Policies/UserPolicy.php`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public viewAny` | `User $user` | `bool` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public view` | `User $user, User $model` | `bool` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public create` | `User $user` | `bool` | Prepara ou cria novo registro/estado de cadastro. |
| `public update` | `User $user, User $model` | `bool` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public delete` | `User $user, User $model` | `bool` | Remove, arquiva ou marca entidade para exclusao conforme regra do componente/modelo. |

## 12. Middlewares

### `App\Http\Middleware\CheckPasswordChange`

- Arquivo: `app/Http/Middleware/CheckPasswordChange.php`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public handle` | `Request $request, Closure $next` | `Response` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

## 13. Commands Artisan

### `App\Console\Commands\FixPlanosEntregasDates`

- Arquivo: `app/Console/Commands/FixPlanosEntregasDates.php`.
- Declaracao: `class FixPlanosEntregasDates extends Command`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public handle` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

### `App\Console\Commands\GenerateCsvTemplates`

- Arquivo: `app/Console/Commands/GenerateCsvTemplates.php`.
- Declaracao: `class GenerateCsvTemplates extends Command`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public handle` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `private generatePair` | `$path, $prefix, $columnsMap, $guideMap` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

### `App\Console\Commands\ProcessScheduledReports`

- Arquivo: `app/Console/Commands/ProcessScheduledReports.php`.
- Declaracao: `class ProcessScheduledReports extends Command`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public handle` | `ReportGenerationService $reportService` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `private atualizarProximaExecucao` | `RelatorioAgendado $agendamento` | `nao declarado` | Atualiza estado, filtro, entidade ou calculo conforme parametro recebido. |

### `App\Console\Commands\SeedMIDREnvironment`

- Arquivo: `app/Console/Commands/SeedMIDREnvironment.php`.
- Declaracao: `class SeedMIDREnvironment extends Command`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public handle` | `nenhuma` | `int` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

## 14. Exports

### `App\Exports\IndicadoresExport`

- Arquivo: `app/Exports/IndicadoresExport.php`.
- Declaracao: `class IndicadoresExport implements FromCollection, WithHeadings, WithMapping`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public __construct` | `$organizacaoId` | `nao declarado` | Inicializa dependencias ou estado do objeto. |
| `public collection` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public headings` | `nenhuma` | `array` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public map` | `$indicador` | `array` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

### `App\Exports\ObjetivosExport`

- Arquivo: `app/Exports/ObjetivosExport.php`.
- Declaracao: `class ObjetivosExport implements FromCollection, WithHeadings, WithMapping`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public __construct` | `$codPei` | `nao declarado` | Inicializa dependencias ou estado do objeto. |
| `public collection` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public headings` | `nenhuma` | `array` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public map` | `$objetivo` | `array` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

### `App\Exports\PlanosExport`

- Arquivo: `app/Exports/PlanosExport.php`.
- Declaracao: `class PlanosExport implements FromCollection, WithHeadings, WithMapping`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public __construct` | `$organizacaoId, $ano = null` | `nao declarado` | Inicializa dependencias ou estado do objeto. |
| `public collection` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public headings` | `nenhuma` | `array` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public map` | `$plano` | `array` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

### `App\Exports\RiscosExport`

- Arquivo: `app/Exports/RiscosExport.php`.
- Declaracao: `class RiscosExport implements FromCollection, WithHeadings, WithMapping`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public __construct` | `$organizacaoId` | `nao declarado` | Inicializa dependencias ou estado do objeto. |
| `public collection` | `nenhuma` | `nao declarado` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public headings` | `nenhuma` | `array` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public map` | `$risco` | `array` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

## 15. Providers

### `App\Providers\AppServiceProvider`

- Arquivo: `app/Providers/AppServiceProvider.php`.
- Declaracao: `class AppServiceProvider extends ServiceProvider`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public register` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public boot` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

### `App\Providers\FortifyServiceProvider`

- Arquivo: `app/Providers/FortifyServiceProvider.php`.
- Declaracao: `class FortifyServiceProvider extends ServiceProvider`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public register` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public boot` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

### `App\Providers\JetstreamServiceProvider`

- Arquivo: `app/Providers/JetstreamServiceProvider.php`.
- Declaracao: `class JetstreamServiceProvider extends ServiceProvider`.

| Metodo | Entrada | Saida declarada | Responsabilidade/saida esperada |
|---|---|---|---|
| `public register` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `public boot` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |
| `protected configurePermissions` | `nenhuma` | `void` | Responsabilidade identificada pelo nome/corpo do metodo; requer leitura fina antes de refatoracao. |

## 16. Rotas efetivas

Todas as rotas abaixo foram extraidas da colecao real do Laravel. As rotas de negocio em `routes/web.php`, exceto raiz e refresh CSRF, ficam dentro do grupo `auth:sanctum`, sessao Jetstream e `verified`.

| Metodo | URI | Nome | Acao | Middleware | Entrada esperada | Saida esperada |
|---|---|---|---|---|---|---|
| `GET|POST|PUT|PATCH|DELETE|OPTIONS` | `/` | `welcome` | `App\Livewire\StrategicPlanning\MapaEstrategico` | `web` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `api/user` | `` | `Closure` | `api, auth:sanctum` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `GET` | `auditoria` | `audit.index` | `App\Livewire\Audit\ListarLogs` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `auditoria/{id}/detalhes` | `audit.detalhes` | `App\Livewire\Audit\DetalharLog` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `configuracoes` | `admin.configuracoes` | `App\Livewire\Admin\ConfiguracaoSistema` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `dashboard` | `dashboard` | `App\Livewire\Dashboard\Index` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `entregas` | `entregas.index` | `App\Livewire\Deliverables\DeliverablesBoard` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `forgot-password` | `password.request` | `Laravel\Fortify\Http\Controllers\PasswordResetLinkController@create` | `web, guest:web` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `POST` | `forgot-password` | `password.email` | `Laravel\Fortify\Http\Controllers\PasswordResetLinkController@store` | `web, guest:web` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `GET` | `graus-satisfacao` | `graus-satisfacao.index` | `App\Livewire\StrategicPlanning\ListarGrausSatisfacao` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `graus-satisfacao/{id}/detalhes` | `graus-satisfacao.detalhes` | `App\Livewire\StrategicPlanning\DetalharGrauSatisfacao` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `indicadores` | `indicadores.index` | `App\Livewire\PerformanceIndicators\ListarIndicadores` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `indicadores/{id}/detalhes` | `indicadores.detalhes` | `App\Livewire\PerformanceIndicators\DetalharIndicador` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `indicadores/{indicadorId}/evolucao` | `indicadores.evolucao` | `App\Livewire\PerformanceIndicators\LancarEvolucao` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `leads` | `leads.index` | `App\Livewire\LeadsTable` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `livewire/livewire.js` | `` | `Livewire\Mechanisms\FrontendAssets\FrontendAssets@returnJavaScriptAsFile` | `` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `livewire/livewire.min.js.map` | `` | `Livewire\Mechanisms\FrontendAssets\FrontendAssets@maps` | `` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `livewire/preview-file/{filename}` | `livewire.preview-file` | `Livewire\Features\SupportFileUploads\FilePreviewController@handle` | `web` | parametros de rota indicados por chaves e request HTTP | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `POST` | `livewire/update` | `livewire.update` | `Livewire\Mechanisms\HandleRequests\HandleRequests@handleUpdate` | `web` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `POST` | `livewire/upload-file` | `livewire.upload-file` | `Livewire\Features\SupportFileUploads\FileUploadController@handle` | `web, throttle:60,1` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `login` | `login` | `Laravel\Fortify\Http\Controllers\AuthenticatedSessionController@create` | `web, guest:web` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `POST` | `login` | `login.store` | `Laravel\Fortify\Http\Controllers\AuthenticatedSessionController@store` | `web, guest:web, throttle:login` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `POST` | `logout` | `logout` | `Laravel\Fortify\Http\Controllers\AuthenticatedSessionController@destroy` | `web, auth:web` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `GET` | `objetivos` | `objetivos.index` | `App\Livewire\StrategicPlanning\ListarObjetivos` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `objetivos/{id}/detalhes` | `objetivos.detalhes` | `App\Livewire\StrategicPlanning\DetalharObjetivo` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `objetivos/{objetivoId}/futuro` | `objetivos.futuro` | `App\Livewire\StrategicPlanning\GerenciarFuturoAlmejado` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `organizacoes` | `organizacoes.index` | `App\Livewire\Organization\ListarOrganizacoes` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `organizacoes/{id}/detalhes` | `organizacoes.detalhes` | `App\Livewire\Organization\DetalharOrganizacao` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `pei` | `pei.index` | `App\Livewire\StrategicPlanning\MissaoVisao` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `pei/ciclos` | `pei.ciclos` | `App\Livewire\StrategicPlanning\ListarPeis` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `pei/identidade/{id}/detalhes` | `pei.identidade.detalhes` | `App\Livewire\StrategicPlanning\DetalharIdentidade` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `pei/mapa` | `pei.mapa` | `App\Livewire\StrategicPlanning\MapaEstrategico` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `pei/perspectivas` | `pei.perspectivas` | `App\Livewire\StrategicPlanning\ListarPerspectivas` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `pei/perspectivas/{id}/detalhes` | `pei.perspectivas.detalhes` | `App\Livewire\StrategicPlanning\DetalharPerspectiva` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `pei/pestel` | `pei.pestel` | `App\Livewire\StrategicPlanning\AnalisePESTEL` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `pei/swot` | `pei.swot` | `App\Livewire\StrategicPlanning\AnaliseSWOT` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `pei/valores` | `pei.valores` | `App\Livewire\StrategicPlanning\ListarValores` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `pei/valores/{id}/detalhes` | `pei.valores.detalhes` | `App\Livewire\StrategicPlanning\DetalharValor` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `pei/{id}/detalhes` | `pei.detalhes` | `App\Livewire\StrategicPlanning\DetalharPei` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `planos` | `planos.index` | `App\Livewire\ActionPlan\ListarPlanos` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `planos/{id}/detalhes` | `planos.detalhes` | `App\Livewire\ActionPlan\DetalharPlano` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `planos/{planoId}/entregas` | `planos.entregas` | `App\Livewire\Deliverables\DeliverablesBoard` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `planos/{planoId}/responsaveis` | `planos.responsaveis` | `App\Livewire\ActionPlan\AtribuirResponsaveis` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `refresh-csrf` | `csrf.refresh` | `Closure` | `web` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `GET` | `register` | `register` | `Laravel\Fortify\Http\Controllers\RegisteredUserController@create` | `web, guest:web` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `POST` | `register` | `register.store` | `Laravel\Fortify\Http\Controllers\RegisteredUserController@store` | `web, guest:web` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `GET` | `relatorios` | `relatorios.index` | `App\Livewire\Reports\ListarRelatorios` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `relatorios/executivo/{organizacaoId?}` | `relatorios.executivo` | `App\Http\Controllers\Reports\RelatorioController@executivo` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | PDF, Excel, view ou download conforme metodo do controller |
| `GET` | `relatorios/historico` | `relatorios.historico` | `App\Livewire\Reports\HistoricoRelatorios` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `relatorios/identidade/{organizacaoId}` | `relatorios.identidade` | `App\Http\Controllers\Reports\RelatorioController@identidade` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | PDF, Excel, view ou download conforme metodo do controller |
| `GET` | `relatorios/indicadores/excel/{organizacaoId?}` | `relatorios.indicadores.excel` | `App\Http\Controllers\Reports\RelatorioController@indicadoresExcel` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | PDF, Excel, view ou download conforme metodo do controller |
| `GET` | `relatorios/indicadores/pdf/{organizacaoId?}` | `relatorios.indicadores.pdf` | `App\Http\Controllers\Reports\RelatorioController@indicadoresPdf` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | PDF, Excel, view ou download conforme metodo do controller |
| `GET` | `relatorios/integrado/{organizacaoId?}` | `relatorios.integrado` | `App\Http\Controllers\Reports\RelatorioController@integrado` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | PDF, Excel, view ou download conforme metodo do controller |
| `GET` | `relatorios/objetivos/excel` | `relatorios.objetivos.excel` | `App\Http\Controllers\Reports\RelatorioController@objetivosExcel` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | PDF, Excel, view ou download conforme metodo do controller |
| `GET` | `relatorios/objetivos/pdf` | `relatorios.objetivos.pdf` | `App\Http\Controllers\Reports\RelatorioController@objetivosPdf` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | PDF, Excel, view ou download conforme metodo do controller |
| `GET` | `relatorios/planos/excel` | `relatorios.planos.excel` | `App\Http\Controllers\Reports\RelatorioController@planosExcel` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | PDF, Excel, view ou download conforme metodo do controller |
| `GET` | `relatorios/planos/pdf` | `relatorios.planos.pdf` | `App\Http\Controllers\Reports\RelatorioController@planosPdf` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | PDF, Excel, view ou download conforme metodo do controller |
| `GET` | `relatorios/riscos/excel` | `relatorios.riscos.excel` | `App\Http\Controllers\Reports\RelatorioController@riscosExcel` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | PDF, Excel, view ou download conforme metodo do controller |
| `GET` | `relatorios/riscos/pdf` | `relatorios.riscos.pdf` | `App\Http\Controllers\Reports\RelatorioController@riscosPdf` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | PDF, Excel, view ou download conforme metodo do controller |
| `POST` | `reset-password` | `password.update` | `Laravel\Fortify\Http\Controllers\NewPasswordController@store` | `web, guest:web` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `GET` | `reset-password/{token}` | `password.reset` | `Laravel\Fortify\Http\Controllers\NewPasswordController@create` | `web, guest:web` | parametros de rota indicados por chaves e request HTTP | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `GET` | `riscos` | `riscos.index` | `App\Livewire\RiskManagement\ListarRiscos` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `riscos/matriz` | `riscos.matriz` | `App\Livewire\RiskManagement\MatrizRiscos` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `riscos/{riscoId}/mitigacao` | `riscos.mitigacao` | `App\Livewire\RiskManagement\GerenciarMitigacoes` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `riscos/{riscoId}/ocorrencias` | `riscos.ocorrencias` | `App\Livewire\RiskManagement\RegistrarOcorrencias` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `sanctum/csrf-cookie` | `sanctum.csrf-cookie` | `Laravel\Sanctum\Http\Controllers\CsrfCookieController@show` | `web` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `POST` | `session/ping` | `session.ping` | `Closure` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `GET` | `storage/{path}` | `storage.local` | `Closure` | `` | parametros de rota indicados por chaves e request HTTP | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `PUT` | `storage/{path}` | `storage.local.upload` | `Closure` | `` | parametros de rota indicados por chaves e request HTTP | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `GET` | `temas-norteadores` | `temas-norteadores.index` | `App\Livewire\StrategicPlanning\GerenciarTemasNorteadores` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `trocar-senha` | `auth.trocar-senha` | `App\Livewire\Auth\TrocarSenha` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `two-factor-challenge` | `two-factor.login` | `Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController@create` | `web, guest:web` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `POST` | `two-factor-challenge` | `two-factor.login.store` | `Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController@store` | `web, guest:web, throttle:two-factor` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `GET` | `up` | `` | `Closure` | `` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `GET` | `user/confirm-password` | `password.confirm` | `Laravel\Fortify\Http\Controllers\ConfirmablePasswordController@show` | `web, auth:web` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `POST` | `user/confirm-password` | `password.confirm.store` | `Laravel\Fortify\Http\Controllers\ConfirmablePasswordController@store` | `web, auth:web` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `GET` | `user/confirmed-password-status` | `password.confirmation` | `Laravel\Fortify\Http\Controllers\ConfirmedPasswordStatusController@show` | `web, auth:web` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `POST` | `user/confirmed-two-factor-authentication` | `two-factor.confirm` | `Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController@store` | `web, auth:web, password.confirm` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `PUT` | `user/password` | `user-password.update` | `Laravel\Fortify\Http\Controllers\PasswordController@update` | `web, auth:web` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `GET` | `user/profile` | `profile.show` | `Laravel\Jetstream\Http\Controllers\Livewire\UserProfileController@show` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `PUT` | `user/profile-information` | `user-profile-information.update` | `Laravel\Fortify\Http\Controllers\ProfileInformationController@update` | `web, auth:web` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `DELETE` | `user/two-factor-authentication` | `two-factor.disable` | `Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController@destroy` | `web, auth:web, password.confirm` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `POST` | `user/two-factor-authentication` | `two-factor.enable` | `Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController@store` | `web, auth:web, password.confirm` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `GET` | `user/two-factor-qr-code` | `two-factor.qr-code` | `Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController@show` | `web, auth:web, password.confirm` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `GET` | `user/two-factor-recovery-codes` | `two-factor.recovery-codes` | `Laravel\Fortify\Http\Controllers\RecoveryCodeController@index` | `web, auth:web, password.confirm` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `POST` | `user/two-factor-recovery-codes` | `two-factor.regenerate-recovery-codes` | `Laravel\Fortify\Http\Controllers\RecoveryCodeController@store` | `web, auth:web, password.confirm` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `GET` | `user/two-factor-secret-key` | `two-factor.secret-key` | `Laravel\Fortify\Http\Controllers\TwoFactorSecretKeyController@show` | `web, auth:web, password.confirm` | request HTTP sem parametro de rota obrigatorio | resposta Laravel/Fortify/Sanctum/closure conforme action |
| `GET` | `usuarios` | `usuarios.index` | `App\Livewire\UserManagement\ListarUsuarios` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | request HTTP sem parametro de rota obrigatorio | pagina/componente Livewire ou payload Livewire nas rotas internas |
| `GET` | `usuarios/{id}/detalhes` | `usuarios.detalhes` | `App\Livewire\UserManagement\DetalharUsuario` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` | parametros de rota indicados por chaves e request HTTP | pagina/componente Livewire ou payload Livewire nas rotas internas |

## 17. Tipos de usuarios e acesso

- A autenticacao e baseada em `users`, Laravel Fortify/Jetstream, Sanctum e email verification.
- O model `User` usa chave primaria string/UUID (`id`) e possui relacionamento com organizacoes/perfis conforme tabelas `organization.rel_users_tab_organizacoes` e `organization.rel_users_tab_organizacoes_tab_perfil_acesso`.
- Perfis institucionais sao persistidos em `organization.tab_perfil_acesso`; o codigo de policies deve ser lido antes do upgrade para confirmar matriz de permissoes efetiva.
- As policies registradas cobrem `Organization`, `User`, `PlanoDeAcao`, `Indicador` e `Risco`.
- Rotas principais exigem usuario autenticado, sessao Jetstream e usuario verificado.

## 18. Auditoria

- Pacote `owen-it/laravel-auditing` habilitado por `config/audit.php`, driver database, tabela `public.audits`.
- Eventos auditados globalmente: `created`, `updated`, `deleted`, `restored`.
- Fila de auditoria desabilitada; auditoria ocorre de forma sincrona.
- Console auditing desabilitado.
- Models marcados como auditaveis no inventario: ver coluna `Auditado` da secao de Models.
- Existe tambem model legado/proprio `TabAudit` sobre `tab_audit`, alem da tabela do pacote `audits`.

## 19. Exceptions e tratamento de falhas

- `AuthenticationException`: JSON 401 para requests JSON; redireciona para `login` em sessao expirada; redireciona para `welcome` quando usuario nao autenticado tenta rota protegida.
- `TokenMismatchException`: invalida sessao, regenera token, faz logout se autenticado e redireciona para login com mensagem amigavel; AJAX/Livewire recebe JSON 419 com `redirect` e `session_expired`.
- `AccessDeniedHttpException`: JSON 403 ou redirect para `welcome`/`dashboard` com mensagem.
- `NotFoundHttpException`: JSON 404 ou redirect amigavel para usuarios nao autenticados; usuarios autenticados caem no fluxo padrao do Laravel.
- `QueryException` com codigo PostgreSQL 23503: retorna mensagem amigavel de integridade referencial e status 422 em JSON.
- Throwable generico em producao para usuario nao autenticado pode redirecionar para `welcome` em erros 500+.

## 20. Frontend e assets

- Vite compila `resources/js/app.js` e `resources/scss/app.scss` conforme `vite.config.js`.
- Stack visual declarada: Bootstrap 5.3, Bootstrap Icons, Alpine.js, plugins Alpine mask/focus, SortableJS para interacoes de ordenacao, Sass para estilos.
- Views Livewire estao em `resources/views/livewire`, separadas por dominio: dashboard, PEI, plano de acao, entregas, indicador, risco, audit, reports, shared, profile e admin.
- Layouts principais: `resources/views/layouts/app.blade.php`, `guest.blade.php`, `public.blade.php` e sidebar parcial.
- Relatorios possuem views dedicadas em `resources/views/relatorios`.

## 21. Riscos tecnicos para upgrade

- Dependencia forte de `search_path` PostgreSQL: qualquer mudanca de conexao, tenancy ou schema pode quebrar Models sem schema qualificado.
- Muitos metodos Livewire concentram validacao, persistencia, estado de UI e consulta no mesmo componente; upgrade deve priorizar extracao progressiva para Services/Form Objects sem alterar comportamento.
- `route:list` mostra 89 rotas efetivas, com rotas de framework misturadas a negocio; regressao em middleware pode afetar autenticacao, sessao e Livewire.
- Auditoria e observer de entregas causam efeitos colaterais em writes; qualquer mudanca em `Entrega` ou `Indicador` precisa de teste de regressao.
- Storage publico nao linkado no ambiente analisado; funcionalidades de anexo/download podem depender de configuracao operacional externa.
- O projeto usa Laravel 12, mas AGENTS menciona Livewire 4; o runtime real observado e Livewire 3.7.11. Upgrade para Livewire 4 deve ser tratado como migracao especifica, nao pressuposto atual.

## 22. Recomendacao objetiva de caminho de upgrade

1. Congelar baseline com dump estrutural e backup do banco antes de qualquer DDL.
2. Criar suite de smoke tests para rotas autenticadas, componentes Livewire criticos e relatorios.
3. Cobrir primeiro dominios com maior acoplamento: entregas, indicadores, planos e riscos.
4. Padronizar contratos de Services antes de trocar versoes de Livewire/Jetstream.
5. Remover dependencia implicita de `search_path` apenas com plano de migration/testes, pois isso toca persistencia central.
6. Validar matriz real de perfis em `tab_perfil_acesso` e policies antes de qualquer redesign de acesso.
