# Documentacao tecnica - Sistema de Planejamento Estrategico

Data base: 2026-05-23. Atualização: 2026-06-01 — inclui os novos módulos e o redesenho de interface entregues após a versão base (ver seção **"Atualização técnica (2026-06) — Novos módulos e funcionalidades"**, logo após o sumário). Finalidade: subsidiar avaliação técnica, planejamento de upgrade e servir de base para o manual operacional do Sistema de Planejamento Estratégico.

## Controle de confianca

- `Verificado no codigo`: afirmacao comprovada por arquivos do repositorio.
- `Verificado no banco`: afirmacao comprovada por consulta somente leitura ao PostgreSQL real.
- `Verificado em runtime`: afirmacao comprovada por comandos Artisan de leitura.
- `Inferido tecnicamente`: conclusao baseada em padroes do codigo, mas que deve ser validada antes de decisao executiva.
- `Nao confirmado`: ponto que o codigo nao prova integralmente.

## Fontes usadas

| Fonte | Uso nesta documentacao | Confianca |
|---|---|---|
| Codigo em `app`, `routes`, `config`, `bootstrap`, `resources` | Fonte primaria de arquitetura, regras e fluxo | Verificado no codigo |
| PostgreSQL real via `information_schema`, `pg_indexes`, `migrations` | Fonte primaria de schema, constraints, indices e migrations aplicadas | Verificado no banco |
| `php artisan about` e `php artisan route:list` | Fonte de runtime e rotas efetivas | Verificado em runtime |
| `README.md` | Apoio para descricao macro quando coerente com codigo | Verificado no codigo, mas tratado como documentacao de apoio |

## Identidade correta do projeto

Verificado no codigo e no README: este repositorio implementa um Sistema de Planejamento Estrategico institucional. O dominio funcional real e PEI/BSC, com objetivos estrategicos, perspectivas, identidade estrategica, indicadores de desempenho, planos de acao, entregas, riscos, relatorios, auditoria e governanca por organizacao/perfil.

## Sumario executivo tecnico

- Aplicacao Laravel 12.53.0, PHP 8.3.28 e Livewire 3.7.11 em runtime local.
- Arquitetura principal: Laravel + Livewire + Blade, com Jetstream/Fortify/Sanctum para autenticacao, PostgreSQL multi-schema para persistencia, Policies/Gates para autorizacao, Services para calculos/relatorios/IA e Observer para recalculo automatico de indicadores a partir de entregas.
- O fluxo metodologico central e guiado por `PeiGuidanceService`: ciclo PEI, identidade, perspectivas BSC, objetivos, graus de satisfacao, indicadores, planos de acao e monitoramento.
- O mapa estrategico calcula atingimento hibrido por perspectiva combinando indicadores e planos/entregas com pesos configuraveis.
- O banco real possui 56 tabelas em schemas PostgreSQL de dominio e infraestrutura.
- A tabela `migrations` possui 67 migrations aplicadas; ha 67 arquivos de migration no disco.

## Correcoes de regras de negocio e ajustes (2026-06-25)

> Confianca: `Verificado no codigo`. Estas correcoes foram aplicadas apos auditoria de violacoes de regras de negocio identificadas em componentes Livewire.

### Violacoes corrigidas em componentes Livewire

| Componente | Problema corrigido |
|---|---|
| `ListarGrausSatisfacao` | `cod_pei` ausente das `rules()` — permitia gravar grau sem vinculo com PEI. Adicionado `required\|exists:strategic_planning.tab_pei,cod_pei`. Metodo `aplicarSugestao()` da IA tambem corrigido para validar `cod_pei` antes de gravar. |
| `ListarRiscos` | `$peiAtivo` e `$organizacaoId` sem atributo `#[Locked]` — campos publicos manipulaveis via Livewire. Adicionado `#[Locked]` em ambos. Guard de null adicionado em `save()` para novos registros. |
| `AnaliseSWOT` | `save()` usava `$this->peiAtivo->cod_pei` sem verificar null — causaria erro 500 sem PEI na sessao. Guard adicionado antes do `validate()`. |
| `AnalisePESTEL` | Idem ao SWOT. |
| `ListarPerspectivas` | Idem. |
| `ListarValores` | Guard de `save()` verificava apenas `$peiAtivo`, nao `$organizacaoId` — poderia gravar `cod_organizacao = NULL` em coluna NOT NULL. Corrigido para verificar ambos. |

### Alteracao de schema — `action_plan.tab_entregas`

- Coluna `dsc_periodo_medicao`: era `NOT NULL`, tornada **nullable** (migration `2026_06_25_032649`).
- Motivacao: campo vestigial para entregas (sem enum proprio no model `Entrega`); `criarRapido()` gravava string vazia `''`, semanticamente invalido.
- O `down()` da migration restaura `NOT NULL` e converte NULL de volta para `''`.

### Reorganizacao da sidebar (`resources/views/layouts/app.blade.php`)

| Item | Antes | Depois |
|---|---|---|
| Graus de Satisfacao | Administracao | Planejar (antes de Indicadores) |
| Licoes Aprendidas | Meu Espaco | Monitorar e Avaliar (apos Historico de Relatorios) |

### Novo comando Artisan

- `demo:preparar` (`app/Console/Commands/DemoPreparar.php`): zera tabelas de dominio estrategico (39 tabelas, ordem FK-safe via `session_replication_role = replica`) e popula dados de demo. Opcoes `--nivel=2` (50% automatico) e `--nivel=3` (95% automatico). Seeders: `database/seeders/Demo/DemoNivel2Seeder.php` e `DemoNivel3Seeder.php`.

---

## Atualização técnica (2026-06) — Novos módulos e funcionalidades

> Esta seção consolida tudo o que foi adicionado **após** a versão base (2026-05-23). É a referência mais atual e deve prevalecer sobre os inventários antigos abaixo quando houver divergência. Confiança: `Verificado no código` (arquivos, migrations e rotas no repositório). Os inventários originais (Models, Livewire, Rotas, Banco) seguem válidos para o núcleo, mas **não incluem** os itens listados aqui.

### Visão geral das adições

O sistema evoluiu de um núcleo PEI/BSC para uma plataforma alinhada de ponta a ponta ao **Guia Prático de PEI (GPPEI/MGI 2025)**, organizada nos três módulos metodológicos oficiais, com a inclusão transversal da **Agenda 2030 (ODS)** e um redesenho completo das telas públicas e do tema visual.

| Tema | O que foi adicionado | Rota principal |
|---|---|---|
| Agenda 2030 (ODS) | Eixo transversal opcional: vínculo de ODS a objetivos e ao PEI, painel de cobertura, ícones oficiais | `/agenda2030` |
| Módulo 01 — Inaugurar e Integrar | Planejamento do processo, integração com instrumentos (PPA/LOA/Planos), calendário de eventos | `/pei/inaugurar` |
| Módulo 02 — Cadeia de Valor | Atividades finalísticas e de suporte com processos | `/pei/cadeia-valor` |
| Módulo 02 — Análise de ambiente expandida | SWOT com GUT, PESTEL, Partes Interessadas, Cenários Prospectivos | `/pei/swot`, `/pei/pestel` |
| Módulo 02 — Execução enriquecida | Modelo Lógico, Matriz RACI, Metas SMART, Plano de Comunicação | telas de planos/entregas |
| Módulo 03 — Monitorar e Avaliar | RAE (Revisão e Avaliação da Estratégia), Lições Aprendidas | `/monitoramento/rae`, `/licoes-aprendidas` |
| Área pessoal | Minhas Entregas (tarefas do usuário agrupadas por plano) | `/minhas-entregas` |
| Relatórios | Reconstrução visual + Dossiê Estratégico Integrado + marcadores ODS | `/relatorios` |
| Portal público | Landing page como painel público de transparência + Mapa Estratégico read-only | `/` |
| Visualizadores de guias | GPPEI e Guia de Projetos com sumário navegável embutido | `/guia-gppei`, `/documentos/projetos` |
| Administração | Gestão de Perfis de Acesso, Impersonação, barra de progresso do PEI | `/admin/perfis`, `/impersonate/*` |
| Tema visual | Dark mode em landing/login/dashboard; sidebar reorganizada por módulos GPPEI | — |

### Agenda 2030 (ODS) — eixo transversal

Integração opcional e estruturada com a Agenda 2030. O gestor decide, ao criar/editar cada objetivo, se vincula ODS — não há obrigatoriedade. Existem **dois níveis** de vínculo, espelhando a metodologia de integração:

- **Granular** — `strategic_planning.rel_objetivo_ods` (objetivo ↔ ODS, com `txt_contribuicao`). Limite recomendado de 3 ODS por objetivo.
- **Institucional** — `strategic_planning.rel_pei_ods` (PEI ↔ ODS, com `txt_contribuicao` e `dsc_intensidade`: Alta/Média/Baixa). Declarado no módulo Inaugurar e Integrar.
- **Referência** — `strategic_planning.tab_ods` com os **18 ODS** (17 da ONU + ODS 18 nacional brasileiro "Igualdade Étnico-Racial"). Colunas: `num_ods` (PK 1..18), `nom_ods`, `nom_ods_abreviado`, `dsc_ods`, `cod_cor` (hex oficial), `nom_icone`. Populada pelo `OdsSeeder` (idempotente).

Artefatos: model `App\Models\Agenda2030\ODS`; relações `ods()` em `Objetivo` e `PEI`; componente Blade `<x-ods-badge>` (usa os ícones oficiais em PT-BR de `public/img/ods/ods-01.png`..`ods-18.png`, com fallback automático para badge colorido); painel `App\Livewire\Agenda2030\PainelODS` em `/agenda2030` (cobertura X/18, grade clicável, objetivos por ODS). Marcadores de ODS aparecem na listagem de objetivos, no mapa estratégico, no widget do dashboard e nos relatórios PDF.

### Módulo 01 GPPEI — Inaugurar e Integrar

Componente `App\Livewire\StrategicPlanning\InaugurarIntegrar` (`/pei/inaugurar`) com quatro abas: Planejar o Processo, Integração com Instrumentos, Agenda 2030 (aderência institucional do PEI aos ODS) e Calendário de Eventos.

- `strategic_planning.tab_inaugurar_pei` — `cod_inaugurar` (PK), `cod_pei`, `txt_equipe`, `txt_diretrizes`, `txt_metodologia`, `txt_observacoes`, `dte_inicio_processo`, `dte_fim_previsto`, `bln_aprovado`. Model `InauguraPei`.
- `strategic_planning.tab_integracao_instrumentos` — `cod_integracao` (PK), `cod_pei`, `dsc_instrumento`, `dsc_tipo_instrumento` (PPA/LOA/Plano Setorial/Outro), `txt_pontos_atencao`, `txt_tarefas`, `dsc_intensidade`, `num_ordem`. Model `IntegracaoInstrumento`.
- `strategic_planning.tab_calendario_eventos_pei` — `cod_evento` (PK), `cod_pei`, `dsc_titulo`, `dsc_objetivo`, `dte_evento`, `dsc_participantes`, `dsc_tipo_evento`, `bln_realizado`. Model `CalendarioEventoPei`.

### Módulo 02 — Cadeia de Valor

Componente `App\Livewire\StrategicPlanning\CadeiaDeValor` (`/pei/cadeia-valor`). A tabela `tab_atividade_cadeia_valor` ganhou as colunas `dsc_tipo` (Finalística/Suporte) e `num_ordem`, permitindo separar atividades finalísticas das de suporte, cada uma com seus processos (entrada → transformação → saída).

### Módulo 02 — Análise de ambiente expandida

- **SWOT com GUT**: `tab_analise_ambiental` recebeu `num_gravidade`, `num_urgencia`, `num_tendencia` (priorização GUT dos itens). Componente `AnaliseSWOT`.
- **PESTEL**: análise por dimensões (Política, Econômica, Social, Tecnológica, Ambiental, Legal) reutilizando `AnaliseAmbiental`. Componente `AnalisePESTEL`.
- **Partes Interessadas** — `strategic_planning.tab_partes_interessadas`: `cod_parte` (PK), `cod_pei`, `nom_parte`, `dsc_tipo`, `num_interesse`, `num_influencia`, `txt_estrategia_engajamento`, `num_ordem`. Model `ParteInteressada` (com quadrante influência × interesse).
- **Cenários Prospectivos** — `strategic_planning.tab_cenarios_prospectivos`: `cod_cenario` (PK), `cod_pei`, `cod_organizacao`, `nom_cenario`, `dsc_tipo` (Otimista/Tendencial/Pessimista), `dsc_descricao`, `txt_implicacoes`, `txt_resposta_estrategica`, `num_probabilidade`, `num_impacto`, `num_ordem`. Model `CenarioProspectivo`.

### Módulo 02 — Execução enriquecida (planos e entregas)

- **Modelo Lógico**: `tab_plano_de_acao` recebeu `json_modelo_logico` (insumos → atividades → produtos → resultados → impacto).
- **Matriz RACI** — `action_plan.tab_raci`: `cod_raci` (PK), `cod_plano_de_acao`, `cod_entrega`, `user_id`, `dsc_papel` (Responsável/Aprovador/Consultado/Informado). Model `Raci`.
- **Metas SMART**: `tab_indicador` recebeu `json_smart` (Específico, Mensurável, Atingível, Relevante, Temporal).
- **Plano de Comunicação** — `action_plan.tab_plano_comunicacao`: `cod_comunicacao` (PK), `cod_plano_de_acao`, `nom_publico_alvo`, `dsc_mensagem_chave`, `dsc_canal`, `dsc_frequencia`, `nom_responsavel`, `num_ordem`. Model `PlanoComunicacao`. Relatório consolidado em `/relatorios/comunicacao`.

### Módulo 03 — Monitorar e Avaliar

- **RAE (Revisão e Avaliação da Estratégia)** — componente `GerenciarRae` (`/monitoramento/rae`); tabela `strategic_planning.tab_rae`: `cod_rae` (PK), `cod_pei`, `cod_organizacao`, `dte_referencia`, `dte_reuniao`, `txt_destaques_positivos`, `txt_problemas_identificados`, `txt_encaminhamentos`, `json_participantes`, `num_progresso_geral`, `dsc_tipo_reuniao`. Model `Rae`. Relatório dedicado em `relatorios/rae`.
- **Lições Aprendidas** — componente `App\Livewire\ActionPlan\LicoesAprendidas` (`/licoes-aprendidas`); tabela `action_plan.tab_licoes_aprendidas`: `cod_licao` (PK), `cod_plano_de_acao`, `dsc_categoria`, `dsc_tipo` (Aprendizado/Problema/Melhoria/Boas Práticas), `txt_descricao`, `txt_recomendacao`, `num_ordem`. Model `LicaoAprendida`.

### Minhas Entregas (área pessoal)

Componente `App\Livewire\Deliverables\MinhasEntregas` (`/minhas-entregas`): lista as entregas não concluídas atribuídas ao usuário autenticado, agrupadas por plano de ação, com filtros (busca, status, prioridade), KPIs de resumo (pendentes, em andamento, atrasadas, planos), destaque de atrasadas e cores oficiais de prioridade/status. Interface redesenhada e compatível com dark mode.

### Relatórios reconstruídos e Dossiê Estratégico Integrado

`ReportGenerationService` e as views em `resources/views/relatorios/` foram reconstruídos sobre um sistema de design comum (partials `cabecalho`, `rodape`, `estilos`). Pontos principais:

- Relatórios: executivo, mapa/identidade (paisagem), objetivos, indicadores (agrupados por perspectiva), planos, riscos (com matriz e mitigações), comunicação, RAE e cadeia de valor.
- **Dossiê Estratégico Integrado** (`/relatorios/integrado`): documento consolidado em capítulos cobrindo identidade, inaugurar/integrar, cadeia de valor, análise de ambiente, mapa estratégico, indicadores, portfólio de planos com RACI/Modelo Lógico, riscos, comunicação, RAE, lições aprendidas e **contribuição à Agenda 2030**.
- Ordem das perspectivas no mapa corrigida para o padrão BSC (base/sustentação na parte inferior).
- Marcadores de ODS (chips coloridos) ao lado dos objetivos nos relatórios.

### Landing page pública e redesenho de UI

- **Landing page** (`App\Livewire\LandingPage`, rota `/`): deixou de ser vitrine de marketing e passou a ser um **painel público de transparência estratégica**. Quando há PEI configurado, exibe panorama BSC com atingimento, KPIs, riscos críticos e um **Mapa Estratégico read-only** (swimlanes de perspectivas com objetivos coloridos por farol) antes do CTA de acesso. Mantém abaixo a apresentação metodológica (módulos GPPEI, funcionalidades). Dados em cache de 5 min.
- **Tema claro/escuro responsivo**: landing, login e dashboard com dark mode aderente; alternador de tema disponível nas páginas públicas (guest layout).
- **Login** (`auth/login`): redesenhado no mesmo padrão visual da landing, com reatividade no botão de submit e dark mode.
- **Sidebar** (`layouts/partials/sidebar`): reorganizada por módulos GPPEI (Inaugurar/Planejar/Monitorar), com separadores de seção, grupo "Meu Espaço", "Referências" e "Administração". Suporte a item externo (abrir em nova aba). Posição dos itens: "Graus de Satisfação" está em Planejar (antes de Indicadores); "Lições Aprendidas" está em Monitorar e Avaliar (não em Meu Espaço).

### Visualizadores de guias

`App\Http\Controllers\DocumentosController` serve o PDF e os viewers embutidos:

- `/guia-gppei` (`documentos.viewer-gppei`) — Guia GPPEI com sumário lateral navegável por seção/página.
- `/documentos/projetos` (`documentos.projetos`) — Guia Prático de Projetos com sumário por domínios; PDF bruto em `/documentos/projetos/pdf`.
- Componentes Blade `<x-gppei-link>` e `<x-projetos-link>` para referências contextuais às páginas dos guias; `<x-module-header>` para cabeçalho padronizado dos módulos.

### Administração e governança

- **Gestão de Perfis de Acesso** — `App\Livewire\Admin\GestaoPerfis` (`/admin/perfis`).
- **Impersonação** — `App\Http\Controllers\ImpersonateController` (`/impersonate/{userId}` e `/impersonate-stop`), com banner de modo impersonação no layout.
- **Barra de progresso do PEI** — `App\Livewire\Shared\PeiProgressBar` no rodapé da sidebar, refletindo o avanço das fases metodológicas.

### Novas tabelas no banco (resumo)

`Verificado no código` (migrations no disco; lote `2026_05_30_*` e `2026_05_31_*`). Tabelas novas que ampliam o schema documentado na versão base:

| Schema | Tabela | Propósito |
|---|---|---|
| `strategic_planning` | `tab_ods` | 18 ODS de referência (Agenda 2030) |
| `strategic_planning` | `rel_objetivo_ods` | Vínculo objetivo ↔ ODS (granular) |
| `strategic_planning` | `rel_pei_ods` | Aderência institucional PEI ↔ ODS |
| `strategic_planning` | `tab_inaugurar_pei` | Planejamento do processo (Módulo 01) |
| `strategic_planning` | `tab_integracao_instrumentos` | Integração com PPA/LOA/Planos |
| `strategic_planning` | `tab_calendario_eventos_pei` | Calendário de eventos do PEI |
| `strategic_planning` | `tab_partes_interessadas` | Stakeholders (influência × interesse) |
| `strategic_planning` | `tab_cenarios_prospectivos` | Cenários prospectivos |
| `strategic_planning` | `tab_rae` | Revisão e Avaliação da Estratégia |
| `action_plan` | `tab_raci` | Matriz RACI de planos/entregas |
| `action_plan` | `tab_licoes_aprendidas` | Lições aprendidas |
| `action_plan` | `tab_plano_comunicacao` | Plano de comunicação |

Colunas adicionadas a tabelas existentes: `tab_analise_ambiental` (`num_gravidade`, `num_urgencia`, `num_tendencia`); `tab_atividade_cadeia_valor` (`dsc_tipo`, `num_ordem`); `tab_plano_de_acao` (`json_modelo_logico`); `tab_indicador` (`json_smart`).

### Novas rotas (resumo)

`Verificado no código` em `routes/web.php`:

| Rota | Nome | Componente/Controller |
|---|---|---|
| `/agenda2030` | `agenda2030.index` | `Agenda2030\PainelODS` |
| `/pei/inaugurar` | `pei.inaugurar` | `StrategicPlanning\InaugurarIntegrar` |
| `/pei/cadeia-valor` | `pei.cadeia-valor` | `StrategicPlanning\CadeiaDeValor` |
| `/monitoramento/rae` | `monitoramento.rae` | `StrategicPlanning\GerenciarRae` |
| `/licoes-aprendidas` | `licoes.index` | `ActionPlan\LicoesAprendidas` |
| `/minhas-entregas` | `entregas.minhas` | `Deliverables\MinhasEntregas` |
| `/temas-norteadores` | `temas-norteadores.index` | `StrategicPlanning\GerenciarTemasNorteadores` |
| `/guia-gppei` | `documentos.viewer-gppei` | `DocumentosController@viewerGppei` |
| `/documentos/projetos` | `documentos.projetos` | `DocumentosController@viewerProjetos` |
| `/documentos/projetos/pdf` | `documentos.projetos.pdf` | `DocumentosController@projetosPdf` |
| `/admin/perfis` | `admin.perfis` | `Admin\GestaoPerfis` |
| `/impersonate/{userId}` | `impersonate.start` | `ImpersonateController@start` |
| `/impersonate-stop` | `impersonate.stop` | `ImpersonateController@stop` |
| `/relatorios/comunicacao` | `relatorios.comunicacao` | `Reports\RelatorioController@comunicacao` |

### Novos models, componentes e seeders (resumo)

- **Models**: `Agenda2030\ODS`; `StrategicPlanning\{InauguraPei, IntegracaoInstrumento, CalendarioEventoPei, ParteInteressada, CenarioProspectivo, Rae}`; `ActionPlan\{Raci, LicaoAprendida, PlanoComunicacao}`. Models existentes alterados: `Objetivo` e `PEI` (relação `ods()`), `AtividadeCadeiaValor`, `AnaliseAmbiental`, `Indicador`, `PlanoDeAcao`, `RiscoMitigacao`.
- **Componentes Livewire**: `Agenda2030\PainelODS`, `LandingPage`, `Deliverables\MinhasEntregas`, `StrategicPlanning\{InaugurarIntegrar, CadeiaDeValor, GerenciarRae}`, `ActionPlan\LicoesAprendidas`, `Admin\GestaoPerfis`, `Shared\PeiProgressBar`.
- **Componentes Blade**: `x-ods-badge`, `x-module-header`, `x-gppei-link`, `x-projetos-link`.
- **Controllers**: `DocumentosController`, `ImpersonateController`.
- **Seeders**: `OdsSeeder` (18 ODS), `BaseStrategicSeeder`, conjunto `MIDR*` (ambiente de demonstração) e `SeedMIDREnvironment` (command Artisan).

---

## Runtime e dependencias

Verificado em runtime por `php artisan about`: Laravel 12.53.0, PHP 8.3.28, Composer 2.6.5, ambiente local, debug habilitado, banco `pgsql`, cache/queue/session em database, mail em log, Livewire 3.7.11, storage publico nao linkado.

### Dependencias PHP

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

### Dependencias PHP de desenvolvimento

- `fakerphp/faker`: `^1.23`
- `laravel/pail`: `^1.2.2`
- `laravel/pint`: `^1.24`
- `laravel/sail`: `^1.41`
- `mockery/mockery`: `^1.6`
- `nunomaduro/collision`: `^8.6`
- `pestphp/pest`: `^4.1`
- `pestphp/pest-plugin-laravel`: `^4.0`

### Dependencias frontend/build

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

## Arquitetura de dominio

| Dominio | Evidencia | Responsabilidade real observada |
|---|---|---|
| PEI/ciclos | `StrategicPlanning\PEI`, rotas `pei/*` | Define periodo do planejamento estrategico e ancora perspectivas, identidade e monitoramento. |
| Identidade estrategica | `MissaoVisao`, `MissaoVisaoValores`, `Valor`, `TemaNorteador` | Mantem missao, visao, valores e temas por organizacao e PEI. |
| BSC/perspectivas | `Perspectiva`, `ListarPerspectivas`, `MapaEstrategico` | Organiza objetivos por perspectivas e calcula desempenho por pesos de indicadores/planos. |
| Objetivos estrategicos | `Objetivo`, `ListarObjetivos`, `DetalharObjetivo` | Cadastro, ordenacao e detalhamento de objetivos vinculados a perspectivas. |
| Graus de satisfacao | `GrauSatisfacao`, rotas `graus-satisfacao` | Faixas/cor para classificar desempenho percentual. |
| Indicadores/KPIs | `Indicador`, `EvolucaoIndicador`, `MetaPorAno`, `LinhaBaseIndicador` | Define KPI, metas, linha de base, evolucao e calculo de atingimento. |
| Planos de acao | `PlanoDeAcao`, `ListarPlanos`, `DetalharPlano` | Vincula iniciativas/projetos a objetivos, organizacoes e periodo. |
| Entregas | `Entrega`, `DeliverablesBoard`, anexos/comentarios/labels/historico | Gerencia execucao operacional em kanban/lista/timeline/calendario, com responsaveis e anexos. |
| Riscos | `Risco`, `RiscoMitigacao`, `RiscoOcorrencia`, componentes RiskManagement | Mapeia riscos, matriz 5x5, mitigacoes e ocorrencias. |
| Relatorios | `RelatorioController`, `ReportGenerationService`, Exports | Gera PDFs e Excel de identidade, objetivos, indicadores, planos, riscos, executivo e integrado. |
| Auditoria | pacote `owen-it/laravel-auditing`, `TabAudit`, telas Audit | Registra e lista mudancas/acoes em entidades criticas. |

## Fluxo metodologico PEI verificado

Verificado no codigo em `app/Services/PeiGuidanceService.php`: o sistema avalia completude do PEI em fases sequenciais. Se nao ha PEI vigente, retorna status critical e direciona para `pei.ciclos`. Com PEI, marca ciclo como completo e exige identidade com missao e visao preenchidas. Depois valida perspectivas, objetivos, graus de satisfacao, indicadores e planos de acao. Quando todos existem, retorna status success, progresso 100 e direciona para dashboard.

| Fase | Criterio observado | Rota de acao |
|---|---|---|
| Ciclo PEI | Existencia de PEI ativo ou selecionado | `pei.ciclos` |
| Identidade | Missao e visao com conteudo minimo | `pei.index` |
| Perspectivas | Pelo menos uma perspectiva; recomenda 4 pilares | `pei.perspectivas` |
| Objetivos | Pelo menos um objetivo nas perspectivas do PEI | `objetivos.index` |
| Graus | Existencia de graus de satisfacao | `graus-satisfacao.index` |
| Indicadores | Indicadores vinculados aos objetivos do PEI | `indicadores.index` |
| Planos | Planos vinculados aos objetivos | `planos.index` |
| Monitoramento | Todas as fases anteriores concluidas | `dashboard` |

## Modulos criticos lidos semanticamente

| Modulo | Fonte lida | Fatos verificados | Entradas | Saidas | Riscos/lacunas |
|---|---|---|---|---|---|
| Identidade estrategica | `app/Livewire/StrategicPlanning/MissaoVisao.php` | Carrega PEI de sessao ou primeiro ativo; filtra por organizacao selecionada; salva missao/visao por `cod_organizacao` e `cod_pei`; gerencia valores; pode solicitar sugestao de IA; envia notificacao do mentor ao salvar. | Sessao `pei_selecionado_id`, `organizacao_selecionada_id`, campos missao/visao/valores. | View `livewire.p-e-i.missao-visao`, flash/status, eventos `mentor-notification`, registros em `MissaoVisaoValores` e `Valor`. | Validacoes existem, mas nem todos os metodos usam `messages()` humanizadas; IA depende de JSON bem-formado. |
| Mapa estrategico | `app/Livewire/StrategicPlanning/MapaEstrategico.php` | Rota publica e autenticada usam o mesmo componente; escolhe organizacao via sessao, usuario ou raiz; suporta modo agrupado com roll-up de descendentes; calcula desempenho por perspectiva com indicadores e planos; usa graus de satisfacao para cor. | Organizacao, PEI, ano de sessao, modo `grouped`/outro. | View `livewire.p-e-i.mapa-estrategico`, layout app ou publico, memoria de calculo em modal. | Calculo pesado dentro de componente; usa queries e loops com colecoes; mudancas em pesos ou status impactam dashboard/relatorios. |
| Objetivos estrategicos | `app/Livewire/StrategicPlanning/ListarObjetivos.php` | Lista objetivos agrupados por perspectiva; valida objetivo, descricao, ordem e perspectiva; usa `PeiGuidanceService` para bloquear criacao antes das fases anteriores; possui auditoria SMART via IA; calcula impacto de exclusao por indicadores e planos. | PEI selecionado, perspectiva, campos do objetivo. | Objetivo criado/atualizado/excluido, modais de sucesso/erro, view `livewire.p-e-i.listar-objetivos`. | Exclusao e direta no model; precisa confirmar constraints e comportamento esperado se houver dependencias. |
| Planos de acao | `app/Livewire/ActionPlan/ListarPlanos.php` | Filtra por organizacao, objetivo, status, tipo e ano; autoriza create/update/delete por policy; valida datas contra anos do PEI do objetivo; suporta multivinculacao por `organizacoes_ids` e mantem `cod_organizacao` legado com primeiro ID. | Organizacao/ano/PEI da sessao, filtros, formulario de plano. | Registros em `PlanoDeAcao`, sync com `organizacoes`, view `livewire.plano-acao.listar-planos`. | Compatibilidade legada com `cod_organizacao` e pivot pode gerar divergencia se nao mantida. |
| Entregas | `app/Livewire/Deliverables/DeliverablesBoard.php` | Gerencia entregas por plano com views kanban/lista/timeline/calendario; filtra por status, prioridade, responsavel e busca; cria/edita entrega, responsaveis, labels, comentarios, anexos, arquivamento, lixeira e exclusao permanente; autoriza via policy do plano. | `planoId`, filtros por URL, uploads, eventos Livewire, formulario de entrega. | Registros em `tab_entregas`, pivots de responsaveis/labels, anexos em storage public, historico/comentarios, eventos UI. | Componente concentra muitas responsabilidades; exclusao permanente existe; storage publico nao estava linkado no ambiente. |
| Indicadores | `app/Livewire/PerformanceIndicators/ListarIndicadores.php` | Gerencia KPIs vinculados a objetivo ou plano; carrega unidades, polaridades e tipos de calculo; suporta multivinculacao a organizacoes; gerencia metas anuais e linha de base; filtra por objetivo, organizacao e tipo de vinculo. | Formulario do indicador, metas, linha base, organizacao, PEI. | Registros em `Indicador`, `MetaPorAno`, `LinhaBaseIndicador`, pivot de organizacoes e view de listagem. | No codigo lido ha regra `exists:tab_planos_acao,cod_plano_de_acao`, enquanto o model/migrations usam `tab_plano_de_acao`; isso deve ser validado antes de upgrade. |
| Calculo de indicadores | `app/Services/IndicadorCalculoService.php` | Calcula progresso de planos por media simples ou ponderada de entregas; exclui canceladas; trata sub-entregas recursivamente; atualiza indicadores automaticos de tipo `action_plan`; valida soma de pesos igual a 100; calcula desempenho de perspectiva e objetivo. | Plano, indicador, ano. | Percentuais, arrays de simulacao/estatisticas, `EvolucaoIndicador` atualizada para periodo atual. | Observer de entrega pode disparar atualizacao automatica; mudanca de status/peso tem impacto em indicadores e relatorios. |
| Riscos | `app/Livewire/RiskManagement/ListarRiscos.php` | Gerencia riscos por organizacao e PEI; valida titulo, categoria, probabilidade, impacto e responsavel; vincula objetivos; filtra por categoria/nivel; cria risco com `cod_pei` e `cod_organizacao`; pode sugerir riscos por IA. | Organizacao, PEI, objetivos, formulario de risco. | Registros em `Risco` e pivot com objetivos, notificacoes mentor. | Filtro de nivel esta parcial no codigo (`... outros filtros`); ampliar exige leitura dos demais componentes de matriz/mitigacao/ocorrencia. |
| Relatorios | `app/Http/Controllers/Reports/RelatorioController.php` | Gera downloads streamados para executivo, identidade, objetivos, indicadores, planos, riscos e integrado; usa `ReportGenerationService` para PDF/conteudo e `maatwebsite/excel` para exports. | Query params `organizacaoId`, `organizacao_id`, `ano`, `periodo`, `perspectiva`, `include_ai`, sessao. | `streamDownload` para PDFs/conteudos e arquivos Excel. | Relatorio integrado aumenta memory_limit para 512M e timeout para 600s; indica carga pesada. |
| Acesso e perfis | `app/Models/User.php e app/Policies/*.php` | Usuario usa UUID, Jetstream, Fortify, Sanctum, 2FA, flags `ativo`, `adm`, `trocarsenha`; super admin e determinado pelo PERFIL vinculado (`PerfilAcesso::SUPER_ADMIN`) via `isSuperAdmin()` e scope `administradores`, com o campo `adm` mantido apenas como espelho/compatibilidade (sincronizado no cadastro; default 0); perfis por pivot com organizacao e plano; policies restringem create/update/delete conforme super admin, admin unidade, gestor responsavel/substituto e responsavel por risco. | Usuario autenticado, organizacao, plano, indicador, risco. | Autorizacao booleana via Gate/Policy. | Acesso de listagem depende tambem de filtros nas queries; policies `viewAny` retornam true em varias entidades. |

## Banco de dados real

Verificado no banco: 67 migrations aplicadas. Verificado no disco: 67 arquivos de migration. Arquivos nao aplicados: nenhum. Migrations aplicadas sem arquivo: nenhuma.

### Tabelas reais por schema

- `action_plan.acoes` com 8 colunas.
- `action_plan.rel_entrega_labels` com 3 colunas.
- `action_plan.rel_entrega_users_responsaveis` com 4 colunas.
- `action_plan.rel_plano_organizacao` com 2 colunas.
- `action_plan.tab_entrega_anexos` com 12 colunas.
- `action_plan.tab_entrega_comentarios` com 9 colunas.
- `action_plan.tab_entrega_historico` com 9 colunas.
- `action_plan.tab_entrega_labels` com 8 colunas.
- `action_plan.tab_entregas` com 18 colunas.
- `action_plan.tab_plano_de_acao` com 16 colunas.
- `action_plan.tab_tipo_execucao` com 5 colunas.
- `organization.rel_organizacao` com 6 colunas.
- `organization.rel_users_tab_organizacoes` com 6 colunas.
- `organization.rel_users_tab_organizacoes_tab_perfil_acesso` com 8 colunas.
- `organization.tab_organizacoes` com 7 colunas.
- `organization.tab_perfil_acesso` com 6 colunas.
- `performance_indicators.rel_indicador_objetivo_organizacao` com 5 colunas.
- `performance_indicators.tab_evolucao_indicador` com 11 colunas.
- `performance_indicators.tab_indicador` com 21 colunas.
- `performance_indicators.tab_linha_base_indicador` com 7 colunas.
- `performance_indicators.tab_meta_por_ano` com 7 colunas.
- `pei.audits` com 14 colunas.
- `pei.cache` com 3 colunas.
- `pei.cache_locks` com 3 colunas.
- `pei.failed_jobs` com 7 colunas.
- `pei.job_batches` com 10 colunas.
- `pei.jobs` com 7 colunas.
- `pei.migrations` com 3 colunas.
- `pei.password_reset_tokens` com 3 colunas.
- `pei.personal_access_tokens` com 10 colunas.
- `pei.sessions` com 6 colunas.
- `pei.strategic_alerts` com 10 colunas.
- `pei.system_settings` com 8 colunas.
- `strategic_planning.tab_analise_ambiental` com 12 colunas.
- `pei.tab_audit` com 14 colunas.
- `pei.tab_relatorios_agendados` com 10 colunas.
- `pei.tab_relatorios_gerados` com 9 colunas.
- `pei.tab_status` com 2 colunas.
- `pei.users` com 17 colunas.
- `risk_management.tab_risco` com 17 colunas.
- `risk_management.tab_risco_mitigacao` com 11 colunas.
- `risk_management.tab_risco_objetivo` com 5 colunas.
- `risk_management.tab_risco_ocorrencia` com 10 colunas.
- `strategic_planning.tab_arquivos` com 9 colunas.
- `strategic_planning.tab_atividade_cadeia_valor` com 7 colunas.
- `strategic_planning.tab_futuro_almejado_objetivo` com 6 colunas.
- `strategic_planning.tab_grau_satisfacao` com 10 colunas.
- `strategic_planning.tab_missao_visao_valores` com 8 colunas.
- `strategic_planning.tab_nivel_hierarquico` com 4 colunas.
- `strategic_planning.tab_objetivo` com 8 colunas.
- `strategic_planning.tab_objetivo_comentarios` com 8 colunas.
- `strategic_planning.tab_pei` com 7 colunas.
- `strategic_planning.tab_perspectiva` com 9 colunas.
- `strategic_planning.tab_processos_atividade_cadeia_valor` com 8 colunas.
- `strategic_planning.tab_tema_norteador` com 7 colunas.
- `strategic_planning.tab_valores` com 8 colunas.

### Estrutura real das tabelas

#### `action_plan.acoes`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `table_id` | `character varying/varchar(191)` | `NO` | `` |
| `user_id` | `uuid/uuid` | `NO` | `` |
| `table` | `character varying/varchar(191)` | `NO` | `` |
| `acao` | `text/text` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571329_599515_1_not_null` em ``
- `CHECK` `571329_599515_2_not_null` em ``
- `CHECK` `571329_599515_3_not_null` em ``
- `CHECK` `571329_599515_4_not_null` em ``
- `CHECK` `571329_599515_5_not_null` em ``
- `FOREIGN KEY` `action_plan_acoes_user_id_foreign` em `user_id`
- `PRIMARY KEY` `acoes_pkey` em `id` -> action_plan.acoes.id

Indices verificados:
- `acoes_pkey`: `CREATE UNIQUE INDEX acoes_pkey ON action_plan.acoes USING btree (id)`
- `action_plan_acoes_table_table_id_index`: `CREATE INDEX action_plan_acoes_table_table_id_index ON action_plan.acoes USING btree ("table", table_id)`
- `action_plan_acoes_user_id_index`: `CREATE INDEX action_plan_acoes_user_id_index ON action_plan.acoes USING btree (user_id)`

#### `action_plan.rel_entrega_labels`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_entrega` | `uuid/uuid` | `NO` | `` |
| `cod_label` | `uuid/uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `NO` | `CURRENT_TIMESTAMP` |

Constraints verificadas:
- `CHECK` `571329_600063_1_not_null` em ``
- `CHECK` `571329_600063_2_not_null` em ``
- `CHECK` `571329_600063_3_not_null` em ``
- `FOREIGN KEY` `action_plan_rel_entrega_labels_cod_entrega_foreign` em `cod_entrega` -> action_plan.tab_entregas.cod_entrega
- `FOREIGN KEY` `action_plan_rel_entrega_labels_cod_label_foreign` em `cod_label` -> action_plan.tab_entrega_labels.cod_label
- `PRIMARY KEY` `rel_entrega_labels_pkey` em `cod_entrega` -> action_plan.rel_entrega_labels.cod_entrega
- `PRIMARY KEY` `rel_entrega_labels_pkey` em `cod_entrega` -> action_plan.rel_entrega_labels.cod_label
- `PRIMARY KEY` `rel_entrega_labels_pkey` em `cod_label` -> action_plan.rel_entrega_labels.cod_label
- `PRIMARY KEY` `rel_entrega_labels_pkey` em `cod_label` -> action_plan.rel_entrega_labels.cod_entrega

Indices verificados:
- `rel_entrega_labels_pkey`: `CREATE UNIQUE INDEX rel_entrega_labels_pkey ON action_plan.rel_entrega_labels USING btree (cod_entrega, cod_label)`

#### `action_plan.rel_entrega_users_responsaveis`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_entrega` | `uuid/uuid` | `NO` | `` |
| `cod_usuario` | `uuid/uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571329_600115_1_not_null` em ``
- `CHECK` `571329_600115_2_not_null` em ``
- `FOREIGN KEY` `action_plan_rel_entrega_users_responsaveis_cod_entrega_foreign` em `cod_entrega` -> action_plan.tab_entregas.cod_entrega
- `FOREIGN KEY` `action_plan_rel_entrega_users_responsaveis_cod_usuario_foreign` em `cod_usuario`
- `PRIMARY KEY` `rel_entrega_users_responsaveis_pkey` em `cod_entrega` -> action_plan.rel_entrega_users_responsaveis.cod_usuario
- `PRIMARY KEY` `rel_entrega_users_responsaveis_pkey` em `cod_entrega` -> action_plan.rel_entrega_users_responsaveis.cod_entrega
- `PRIMARY KEY` `rel_entrega_users_responsaveis_pkey` em `cod_usuario` -> action_plan.rel_entrega_users_responsaveis.cod_entrega
- `PRIMARY KEY` `rel_entrega_users_responsaveis_pkey` em `cod_usuario` -> action_plan.rel_entrega_users_responsaveis.cod_usuario

Indices verificados:
- `rel_entrega_users_responsaveis_pkey`: `CREATE UNIQUE INDEX rel_entrega_users_responsaveis_pkey ON action_plan.rel_entrega_users_responsaveis USING btree (cod_entrega, cod_usuario)`

#### `action_plan.rel_plano_organizacao`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_plano_de_acao` | `uuid/uuid` | `NO` | `` |
| `cod_organizacao` | `uuid/uuid` | `NO` | `` |

Constraints verificadas:
- `CHECK` `571329_600235_1_not_null` em ``
- `CHECK` `571329_600235_2_not_null` em ``
- `FOREIGN KEY` `action_plan_rel_plano_organizacao_cod_organizacao_foreign` em `cod_organizacao`
- `FOREIGN KEY` `action_plan_rel_plano_organizacao_cod_plano_de_acao_foreign` em `cod_plano_de_acao` -> action_plan.tab_plano_de_acao.cod_plano_de_acao
- `PRIMARY KEY` `rel_plano_organizacao_pkey` em `cod_plano_de_acao` -> action_plan.rel_plano_organizacao.cod_organizacao
- `PRIMARY KEY` `rel_plano_organizacao_pkey` em `cod_plano_de_acao` -> action_plan.rel_plano_organizacao.cod_plano_de_acao
- `PRIMARY KEY` `rel_plano_organizacao_pkey` em `cod_organizacao` -> action_plan.rel_plano_organizacao.cod_organizacao
- `PRIMARY KEY` `rel_plano_organizacao_pkey` em `cod_organizacao` -> action_plan.rel_plano_organizacao.cod_plano_de_acao

Indices verificados:
- `rel_plano_organizacao_pkey`: `CREATE UNIQUE INDEX rel_plano_organizacao_pkey ON action_plan.rel_plano_organizacao USING btree (cod_plano_de_acao, cod_organizacao)`

#### `action_plan.tab_entrega_anexos`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_anexo` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `cod_entrega` | `uuid/uuid` | `NO` | `` |
| `cod_usuario` | `uuid/uuid` | `NO` | `` |
| `dsc_nome_arquivo` | `character varying/varchar(255)` | `NO` | `` |
| `dsc_caminho` | `character varying/varchar(500)` | `NO` | `` |
| `dsc_mime_type` | `character varying/varchar(100)` | `NO` | `` |
| `num_tamanho_bytes` | `bigint/int8` | `NO` | `` |
| `dsc_descricao` | `character varying/varchar(500)` | `YES` | `` |
| `dsc_thumbnail` | `text/text` | `YES` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571329_600079_1_not_null` em ``
- `CHECK` `571329_600079_2_not_null` em ``
- `CHECK` `571329_600079_3_not_null` em ``
- `CHECK` `571329_600079_4_not_null` em ``
- `CHECK` `571329_600079_5_not_null` em ``
- `CHECK` `571329_600079_6_not_null` em ``
- `CHECK` `571329_600079_7_not_null` em ``
- `FOREIGN KEY` `action_plan_tab_entrega_anexos_cod_entrega_foreign` em `cod_entrega` -> action_plan.tab_entregas.cod_entrega
- `PRIMARY KEY` `tab_entrega_anexos_pkey` em `cod_anexo` -> action_plan.tab_entrega_anexos.cod_anexo

Indices verificados:
- `idx_anexos_entrega`: `CREATE INDEX idx_anexos_entrega ON action_plan.tab_entrega_anexos USING btree (cod_entrega)`
- `idx_anexos_mime`: `CREATE INDEX idx_anexos_mime ON action_plan.tab_entrega_anexos USING btree (dsc_mime_type)`
- `idx_anexos_usuario`: `CREATE INDEX idx_anexos_usuario ON action_plan.tab_entrega_anexos USING btree (cod_usuario)`
- `tab_entrega_anexos_pkey`: `CREATE UNIQUE INDEX tab_entrega_anexos_pkey ON action_plan.tab_entrega_anexos USING btree (cod_anexo)`

#### `action_plan.tab_entrega_comentarios`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_comentario` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `cod_entrega` | `uuid/uuid` | `NO` | `` |
| `cod_usuario` | `uuid/uuid` | `NO` | `` |
| `dsc_comentario` | `text/text` | `NO` | `` |
| `json_mencoes` | `jsonb/jsonb` | `YES` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `cod_comentario_pai` | `uuid/uuid` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571329_600031_1_not_null` em ``
- `CHECK` `571329_600031_2_not_null` em ``
- `CHECK` `571329_600031_3_not_null` em ``
- `CHECK` `571329_600031_4_not_null` em ``
- `FOREIGN KEY` `action_plan_tab_entrega_comentarios_cod_comentario_pai_foreign` em `cod_comentario_pai` -> action_plan.tab_entrega_comentarios.cod_comentario
- `FOREIGN KEY` `action_plan_tab_entrega_comentarios_cod_entrega_foreign` em `cod_entrega` -> action_plan.tab_entregas.cod_entrega
- `PRIMARY KEY` `tab_entrega_comentarios_pkey` em `cod_comentario` -> action_plan.tab_entrega_comentarios.cod_comentario

Indices verificados:
- `idx_comentarios_data`: `CREATE INDEX idx_comentarios_data ON action_plan.tab_entrega_comentarios USING btree (created_at)`
- `idx_comentarios_entrega`: `CREATE INDEX idx_comentarios_entrega ON action_plan.tab_entrega_comentarios USING btree (cod_entrega)`
- `idx_comentarios_pai`: `CREATE INDEX idx_comentarios_pai ON action_plan.tab_entrega_comentarios USING btree (cod_comentario_pai)`
- `idx_comentarios_usuario`: `CREATE INDEX idx_comentarios_usuario ON action_plan.tab_entrega_comentarios USING btree (cod_usuario)`
- `tab_entrega_comentarios_pkey`: `CREATE UNIQUE INDEX tab_entrega_comentarios_pkey ON action_plan.tab_entrega_comentarios USING btree (cod_comentario)`

#### `action_plan.tab_entrega_historico`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_historico` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `cod_entrega` | `uuid/uuid` | `NO` | `` |
| `cod_usuario` | `uuid/uuid` | `YES` | `` |
| `dsc_acao` | `character varying/varchar(50)` | `NO` | `` |
| `dsc_campo` | `character varying/varchar(100)` | `YES` | `` |
| `json_valor_antigo` | `jsonb/jsonb` | `YES` | `` |
| `json_valor_novo` | `jsonb/jsonb` | `YES` | `` |
| `dsc_descricao` | `text/text` | `YES` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `NO` | `CURRENT_TIMESTAMP` |

Constraints verificadas:
- `CHECK` `571329_600096_1_not_null` em ``
- `CHECK` `571329_600096_2_not_null` em ``
- `CHECK` `571329_600096_4_not_null` em ``
- `CHECK` `571329_600096_9_not_null` em ``
- `FOREIGN KEY` `action_plan_tab_entrega_historico_cod_entrega_foreign` em `cod_entrega` -> action_plan.tab_entregas.cod_entrega
- `PRIMARY KEY` `tab_entrega_historico_pkey` em `cod_historico` -> action_plan.tab_entrega_historico.cod_historico

Indices verificados:
- `idx_historico_acao`: `CREATE INDEX idx_historico_acao ON action_plan.tab_entrega_historico USING btree (dsc_acao)`
- `idx_historico_data`: `CREATE INDEX idx_historico_data ON action_plan.tab_entrega_historico USING btree (created_at)`
- `idx_historico_entrega`: `CREATE INDEX idx_historico_entrega ON action_plan.tab_entrega_historico USING btree (cod_entrega)`
- `idx_historico_usuario`: `CREATE INDEX idx_historico_usuario ON action_plan.tab_entrega_historico USING btree (cod_usuario)`
- `tab_entrega_historico_pkey`: `CREATE UNIQUE INDEX tab_entrega_historico_pkey ON action_plan.tab_entrega_historico USING btree (cod_historico)`

#### `action_plan.tab_entrega_labels`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_label` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `cod_plano_de_acao` | `uuid/uuid` | `NO` | `` |
| `dsc_label` | `character varying/varchar(100)` | `NO` | `` |
| `dsc_cor` | `character varying/varchar(7)` | `NO` | `'#6366f1'::character varying` |
| `dsc_icone` | `character varying/varchar(50)` | `YES` | `` |
| `num_ordem` | `integer/int4` | `NO` | `0` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571329_600048_1_not_null` em ``
- `CHECK` `571329_600048_2_not_null` em ``
- `CHECK` `571329_600048_3_not_null` em ``
- `CHECK` `571329_600048_4_not_null` em ``
- `CHECK` `571329_600048_6_not_null` em ``
- `FOREIGN KEY` `action_plan_tab_entrega_labels_cod_plano_de_acao_foreign` em `cod_plano_de_acao` -> action_plan.tab_plano_de_acao.cod_plano_de_acao
- `PRIMARY KEY` `tab_entrega_labels_pkey` em `cod_label` -> action_plan.tab_entrega_labels.cod_label

Indices verificados:
- `idx_labels_ordem`: `CREATE INDEX idx_labels_ordem ON action_plan.tab_entrega_labels USING btree (num_ordem)`
- `idx_labels_plano`: `CREATE INDEX idx_labels_plano ON action_plan.tab_entrega_labels USING btree (cod_plano_de_acao)`
- `tab_entrega_labels_pkey`: `CREATE UNIQUE INDEX tab_entrega_labels_pkey ON action_plan.tab_entrega_labels USING btree (cod_label)`

#### `action_plan.tab_entregas`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_entrega` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `cod_plano_de_acao` | `uuid/uuid` | `YES` | `` |
| `dsc_entrega` | `text/text` | `NO` | `` |
| `bln_status` | `character varying/varchar(191)` | `NO` | `` |
| `dsc_periodo_medicao` | `character varying/varchar(191)` | `YES` | `` |
| `num_nivel_hierarquico_apresentacao` | `smallint/int2` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `cod_entrega_pai` | `uuid/uuid` | `YES` | `` |
| `dsc_tipo` | `character varying/varchar(50)` | `NO` | `'task'::character varying` |
| `json_propriedades` | `jsonb/jsonb` | `YES` | `` |
| `dte_prazo` | `date/date` | `YES` | `` |
| `cod_responsavel` | `uuid/uuid` | `YES` | `` |
| `cod_prioridade` | `character varying/varchar(20)` | `NO` | `'media'::character varying` |
| `num_ordem` | `integer/int4` | `NO` | `0` |
| `bln_arquivado` | `boolean/bool` | `NO` | `false` |
| `num_peso` | `numeric/numeric` | `NO` | `'0'::numeric` |

Constraints verificadas:
- `CHECK` `571329_599842_11_not_null` em ``
- `CHECK` `571329_599842_15_not_null` em ``
- `CHECK` `571329_599842_16_not_null` em ``
- `CHECK` `571329_599842_17_not_null` em ``
- `CHECK` `571329_599842_18_not_null` em ``
- `CHECK` `571329_599842_1_not_null` em ``
- `CHECK` `571329_599842_3_not_null` em ``
- `CHECK` `571329_599842_4_not_null` em ``
- `CHECK` `571329_599842_5_not_null` em ``
- `CHECK` `571329_599842_6_not_null` em ``
- `FOREIGN KEY` `action_plan_tab_entregas_cod_plano_de_acao_foreign` em `cod_plano_de_acao` -> action_plan.tab_plano_de_acao.cod_plano_de_acao
- `FOREIGN KEY` `fk_entregas_entrega_pai` em `cod_entrega_pai` -> action_plan.tab_entregas.cod_entrega
- `FOREIGN KEY` `fk_entregas_responsavel` em `cod_responsavel`
- `PRIMARY KEY` `tab_entregas_pkey` em `cod_entrega` -> action_plan.tab_entregas.cod_entrega

Indices verificados:
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
| `cod_plano_de_acao` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `cod_objetivo` | `uuid/uuid` | `NO` | `` |
| `cod_tipo_execucao` | `uuid/uuid` | `NO` | `` |
| `cod_organizacao` | `uuid/uuid` | `NO` | `` |
| `num_nivel_hierarquico_apresentacao` | `smallint/int2` | `NO` | `` |
| `dsc_plano_de_acao` | `text/text` | `NO` | `` |
| `dte_inicio` | `date/date` | `NO` | `` |
| `dte_fim` | `date/date` | `NO` | `` |
| `vlr_orcamento_previsto` | `numeric/numeric` | `YES` | `` |
| `bln_status` | `character varying/varchar(191)` | `NO` | `` |
| `cod_ppa` | `character varying/varchar(191)` | `YES` | `` |
| `cod_loa` | `character varying/varchar(191)` | `YES` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `txt_detalhamento` | `text/text` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571329_599598_10_not_null` em ``
- `CHECK` `571329_599598_1_not_null` em ``
- `CHECK` `571329_599598_2_not_null` em ``
- `CHECK` `571329_599598_3_not_null` em ``
- `CHECK` `571329_599598_4_not_null` em ``
- `CHECK` `571329_599598_5_not_null` em ``
- `CHECK` `571329_599598_6_not_null` em ``
- `CHECK` `571329_599598_7_not_null` em ``
- `CHECK` `571329_599598_8_not_null` em ``
- `FOREIGN KEY` `action_plan_tab_plano_de_acao_cod_objetivo_foreign` em `cod_objetivo`
- `FOREIGN KEY` `action_plan_tab_plano_de_acao_cod_organizacao_foreign` em `cod_organizacao`
- `FOREIGN KEY` `action_plan_tab_plano_de_acao_cod_tipo_execucao_foreign` em `cod_tipo_execucao` -> action_plan.tab_tipo_execucao.cod_tipo_execucao
- `PRIMARY KEY` `tab_plano_de_acao_pkey` em `cod_plano_de_acao` -> action_plan.tab_plano_de_acao.cod_plano_de_acao

Indices verificados:
- `action_plan_tab_plano_de_acao_bln_status_index`: `CREATE INDEX action_plan_tab_plano_de_acao_bln_status_index ON action_plan.tab_plano_de_acao USING btree (bln_status)`
- `action_plan_tab_plano_de_acao_cod_objetivo_index`: `CREATE INDEX action_plan_tab_plano_de_acao_cod_objetivo_index ON action_plan.tab_plano_de_acao USING btree (cod_objetivo)`
- `action_plan_tab_plano_de_acao_cod_organizacao_index`: `CREATE INDEX action_plan_tab_plano_de_acao_cod_organizacao_index ON action_plan.tab_plano_de_acao USING btree (cod_organizacao)`
- `tab_plano_de_acao_pkey`: `CREATE UNIQUE INDEX tab_plano_de_acao_pkey ON action_plan.tab_plano_de_acao USING btree (cod_plano_de_acao)`

#### `action_plan.tab_tipo_execucao`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_tipo_execucao` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `dsc_tipo_execucao` | `character varying/varchar(191)` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571329_599592_1_not_null` em ``
- `CHECK` `571329_599592_2_not_null` em ``
- `PRIMARY KEY` `tab_tipo_execucao_pkey` em `cod_tipo_execucao` -> action_plan.tab_tipo_execucao.cod_tipo_execucao

Indices verificados:
- `tab_tipo_execucao_pkey`: `CREATE UNIQUE INDEX tab_tipo_execucao_pkey ON action_plan.tab_tipo_execucao USING btree (cod_tipo_execucao)`

#### `organization.rel_organizacao`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `cod_organizacao` | `uuid/uuid` | `NO` | `` |
| `rel_cod_organizacao` | `uuid/uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571332_599497_1_not_null` em ``
- `CHECK` `571332_599497_2_not_null` em ``
- `CHECK` `571332_599497_3_not_null` em ``
- `FOREIGN KEY` `organization_rel_organizacao_cod_organizacao_foreign` em `cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `FOREIGN KEY` `organization_rel_organizacao_rel_cod_organizacao_foreign` em `rel_cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `PRIMARY KEY` `rel_organizacao_pkey` em `id` -> organization.rel_organizacao.id
- `UNIQUE` `organization_rel_organizacao_cod_organizacao_rel_cod_organizaca` em `cod_organizacao` -> organization.rel_organizacao.cod_organizacao
- `UNIQUE` `organization_rel_organizacao_cod_organizacao_rel_cod_organizaca` em `cod_organizacao` -> organization.rel_organizacao.rel_cod_organizacao
- `UNIQUE` `organization_rel_organizacao_cod_organizacao_rel_cod_organizaca` em `rel_cod_organizacao` -> organization.rel_organizacao.rel_cod_organizacao
- `UNIQUE` `organization_rel_organizacao_cod_organizacao_rel_cod_organizaca` em `rel_cod_organizacao` -> organization.rel_organizacao.cod_organizacao

Indices verificados:
- `organization_rel_organizacao_cod_organizacao_rel_cod_organizaca`: `CREATE UNIQUE INDEX organization_rel_organizacao_cod_organizacao_rel_cod_organizaca ON organization.rel_organizacao USING btree (cod_organizacao, rel_cod_organizacao)`
- `rel_organizacao_pkey`: `CREATE UNIQUE INDEX rel_organizacao_pkey ON organization.rel_organizacao USING btree (id)`

#### `organization.rel_users_tab_organizacoes`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `user_id` | `uuid/uuid` | `NO` | `` |
| `cod_organizacao` | `uuid/uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571332_599479_1_not_null` em ``
- `CHECK` `571332_599479_2_not_null` em ``
- `CHECK` `571332_599479_3_not_null` em ``
- `FOREIGN KEY` `organization_rel_users_tab_organizacoes_cod_organizacao_foreign` em `cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `FOREIGN KEY` `organization_rel_users_tab_organizacoes_user_id_foreign` em `user_id`
- `PRIMARY KEY` `rel_users_tab_organizacoes_pkey` em `id` -> organization.rel_users_tab_organizacoes.id
- `UNIQUE` `organization_rel_users_tab_organizacoes_user_id_cod_organizacao` em `user_id` -> organization.rel_users_tab_organizacoes.cod_organizacao
- `UNIQUE` `organization_rel_users_tab_organizacoes_user_id_cod_organizacao` em `user_id` -> organization.rel_users_tab_organizacoes.user_id
- `UNIQUE` `organization_rel_users_tab_organizacoes_user_id_cod_organizacao` em `cod_organizacao` -> organization.rel_users_tab_organizacoes.user_id
- `UNIQUE` `organization_rel_users_tab_organizacoes_user_id_cod_organizacao` em `cod_organizacao` -> organization.rel_users_tab_organizacoes.cod_organizacao

Indices verificados:
- `organization_rel_users_tab_organizacoes_user_id_cod_organizacao`: `CREATE UNIQUE INDEX organization_rel_users_tab_organizacoes_user_id_cod_organizacao ON organization.rel_users_tab_organizacoes USING btree (user_id, cod_organizacao)`
- `rel_users_tab_organizacoes_pkey`: `CREATE UNIQUE INDEX rel_users_tab_organizacoes_pkey ON organization.rel_users_tab_organizacoes USING btree (id)`

#### `organization.rel_users_tab_organizacoes_tab_perfil_acesso`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `user_id` | `uuid/uuid` | `NO` | `` |
| `cod_organizacao` | `uuid/uuid` | `NO` | `` |
| `cod_plano_de_acao` | `uuid/uuid` | `YES` | `` |
| `cod_perfil` | `uuid/uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571332_599625_1_not_null` em ``
- `CHECK` `571332_599625_2_not_null` em ``
- `CHECK` `571332_599625_3_not_null` em ``
- `CHECK` `571332_599625_5_not_null` em ``
- `FOREIGN KEY` `fk_uopp_org` em `cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `FOREIGN KEY` `fk_uopp_perfil` em `cod_perfil` -> organization.tab_perfil_acesso.cod_perfil
- `FOREIGN KEY` `fk_uopp_plano` em `cod_plano_de_acao`
- `FOREIGN KEY` `fk_uopp_user` em `user_id`
- `PRIMARY KEY` `rel_users_tab_organizacoes_tab_perfil_acesso_pkey` em `id` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.id
- `UNIQUE` `rel_uopp_unique` em `user_id` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_plano_de_acao
- `UNIQUE` `rel_uopp_unique` em `user_id` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_perfil
- `UNIQUE` `rel_uopp_unique` em `user_id` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_organizacao
- `UNIQUE` `rel_uopp_unique` em `user_id` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.user_id
- `UNIQUE` `rel_uopp_unique` em `cod_organizacao` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.user_id
- `UNIQUE` `rel_uopp_unique` em `cod_organizacao` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_perfil
- `UNIQUE` `rel_uopp_unique` em `cod_organizacao` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_plano_de_acao
- `UNIQUE` `rel_uopp_unique` em `cod_organizacao` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_organizacao
- `UNIQUE` `rel_uopp_unique` em `cod_plano_de_acao` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.user_id
- `UNIQUE` `rel_uopp_unique` em `cod_plano_de_acao` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_plano_de_acao
- `UNIQUE` `rel_uopp_unique` em `cod_plano_de_acao` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_organizacao
- `UNIQUE` `rel_uopp_unique` em `cod_plano_de_acao` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_perfil
- `UNIQUE` `rel_uopp_unique` em `cod_perfil` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_plano_de_acao
- `UNIQUE` `rel_uopp_unique` em `cod_perfil` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_organizacao
- `UNIQUE` `rel_uopp_unique` em `cod_perfil` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.cod_perfil
- `UNIQUE` `rel_uopp_unique` em `cod_perfil` -> organization.rel_users_tab_organizacoes_tab_perfil_acesso.user_id

Indices verificados:
- `organization_rel_users_tab_organizacoes_tab_perfil_acesso_cod_o`: `CREATE INDEX organization_rel_users_tab_organizacoes_tab_perfil_acesso_cod_o ON organization.rel_users_tab_organizacoes_tab_perfil_acesso USING btree (cod_organizacao)`
- `organization_rel_users_tab_organizacoes_tab_perfil_acesso_cod_p`: `CREATE INDEX organization_rel_users_tab_organizacoes_tab_perfil_acesso_cod_p ON organization.rel_users_tab_organizacoes_tab_perfil_acesso USING btree (cod_plano_de_acao)`
- `organization_rel_users_tab_organizacoes_tab_perfil_acesso_user_`: `CREATE INDEX organization_rel_users_tab_organizacoes_tab_perfil_acesso_user_ ON organization.rel_users_tab_organizacoes_tab_perfil_acesso USING btree (user_id)`
- `rel_uopp_unique`: `CREATE UNIQUE INDEX rel_uopp_unique ON organization.rel_users_tab_organizacoes_tab_perfil_acesso USING btree (user_id, cod_organizacao, cod_plano_de_acao, cod_perfil)`
- `rel_users_tab_organizacoes_tab_perfil_acesso_pkey`: `CREATE UNIQUE INDEX rel_users_tab_organizacoes_tab_perfil_acesso_pkey ON organization.rel_users_tab_organizacoes_tab_perfil_acesso USING btree (id)`

#### `organization.tab_organizacoes`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_organizacao` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `sgl_organizacao` | `character varying/varchar(191)` | `NO` | `` |
| `nom_organizacao` | `text/text` | `NO` | `` |
| `rel_cod_organizacao` | `uuid/uuid` | `YES` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571332_599461_1_not_null` em ``
- `CHECK` `571332_599461_2_not_null` em ``
- `CHECK` `571332_599461_3_not_null` em ``
- `PRIMARY KEY` `tab_organizacoes_pkey` em `cod_organizacao` -> organization.tab_organizacoes.cod_organizacao

Indices verificados:
- `tab_organizacoes_pkey`: `CREATE UNIQUE INDEX tab_organizacoes_pkey ON organization.tab_organizacoes USING btree (cod_organizacao)`

#### `organization.tab_perfil_acesso`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_perfil` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `dsc_perfil` | `text/text` | `NO` | `` |
| `dsc_permissao` | `text/text` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571332_599470_1_not_null` em ``
- `CHECK` `571332_599470_2_not_null` em ``
- `CHECK` `571332_599470_3_not_null` em ``
- `PRIMARY KEY` `tab_perfil_acesso_pkey` em `cod_perfil` -> organization.tab_perfil_acesso.cod_perfil

Indices verificados:
- `tab_perfil_acesso_pkey`: `CREATE UNIQUE INDEX tab_perfil_acesso_pkey ON organization.tab_perfil_acesso USING btree (cod_perfil)`

#### `performance_indicators.rel_indicador_objetivo_organizacao`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_indicador` | `uuid/uuid` | `NO` | `` |
| `cod_organizacao` | `uuid/uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571330_599827_1_not_null` em ``
- `CHECK` `571330_599827_2_not_null` em ``
- `FOREIGN KEY` `fk_rioo_indicador` em `cod_indicador` -> performance_indicators.tab_indicador.cod_indicador
- `FOREIGN KEY` `fk_rioo_org` em `cod_organizacao`
- `PRIMARY KEY` `rel_indicador_objetivo_estrategico_organizacao_pkey` em `cod_indicador` -> performance_indicators.rel_indicador_objetivo_organizacao.cod_organizacao
- `PRIMARY KEY` `rel_indicador_objetivo_estrategico_organizacao_pkey` em `cod_indicador` -> performance_indicators.rel_indicador_objetivo_organizacao.cod_indicador
- `PRIMARY KEY` `rel_indicador_objetivo_estrategico_organizacao_pkey` em `cod_organizacao` -> performance_indicators.rel_indicador_objetivo_organizacao.cod_organizacao
- `PRIMARY KEY` `rel_indicador_objetivo_estrategico_organizacao_pkey` em `cod_organizacao` -> performance_indicators.rel_indicador_objetivo_organizacao.cod_indicador

Indices verificados:
- `rel_indicador_objetivo_estrategico_organizacao_pkey`: `CREATE UNIQUE INDEX rel_indicador_objetivo_estrategico_organizacao_pkey ON performance_indicators.rel_indicador_objetivo_organizacao USING btree (cod_indicador, cod_organizacao)`

#### `performance_indicators.tab_evolucao_indicador`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_evolucao_indicador` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `cod_indicador` | `uuid/uuid` | `NO` | `` |
| `num_ano` | `smallint/int2` | `NO` | `` |
| `num_mes` | `smallint/int2` | `NO` | `` |
| `vlr_previsto` | `numeric/numeric` | `YES` | `` |
| `vlr_realizado` | `numeric/numeric` | `YES` | `` |
| `txt_avaliacao` | `text/text` | `YES` | `` |
| `bln_atualizado` | `character varying/varchar(191)` | `YES` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571330_599677_1_not_null` em ``
- `CHECK` `571330_599677_2_not_null` em ``
- `CHECK` `571330_599677_3_not_null` em ``
- `CHECK` `571330_599677_4_not_null` em ``
- `FOREIGN KEY` `performance_indicators_tab_evolucao_indicador_cod_indicador_for` em `cod_indicador` -> performance_indicators.tab_indicador.cod_indicador
- `PRIMARY KEY` `tab_evolucao_indicador_pkey` em `cod_evolucao_indicador` -> performance_indicators.tab_evolucao_indicador.cod_evolucao_indicador

Indices verificados:
- `performance_indicators_tab_evolucao_indicador_cod_indicador_num`: `CREATE INDEX performance_indicators_tab_evolucao_indicador_cod_indicador_num ON performance_indicators.tab_evolucao_indicador USING btree (cod_indicador, num_ano, num_mes)`
- `tab_evolucao_indicador_pkey`: `CREATE UNIQUE INDEX tab_evolucao_indicador_pkey ON performance_indicators.tab_evolucao_indicador USING btree (cod_evolucao_indicador)`

#### `performance_indicators.tab_indicador`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_indicador` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `cod_plano_de_acao` | `uuid/uuid` | `YES` | `` |
| `cod_objetivo` | `uuid/uuid` | `YES` | `` |
| `dsc_tipo` | `text/text` | `NO` | `` |
| `nom_indicador` | `text/text` | `NO` | `` |
| `dsc_indicador` | `text/text` | `NO` | `` |
| `txt_observacao` | `text/text` | `YES` | `` |
| `dsc_meta` | `text/text` | `YES` | `` |
| `dsc_atributos` | `text/text` | `YES` | `` |
| `dsc_referencial_comparativo` | `text/text` | `YES` | `` |
| `dsc_unidade_medida` | `text/text` | `NO` | `` |
| `num_peso` | `smallint/int2` | `YES` | `` |
| `bln_acumulado` | `character varying/varchar(191)` | `NO` | `` |
| `dsc_formula` | `text/text` | `YES` | `` |
| `dsc_fonte` | `character varying/varchar(191)` | `YES` | `` |
| `dsc_periodo_medicao` | `character varying/varchar(191)` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `dsc_polaridade` | `character varying/varchar(191)` | `YES` | `` |
| `dsc_calculation_type` | `character varying/varchar(20)` | `NO` | `'manual'::character varying` |

Constraints verificadas:
- `CHECK` `571330_599656_11_not_null` em ``
- `CHECK` `571330_599656_13_not_null` em ``
- `CHECK` `571330_599656_16_not_null` em ``
- `CHECK` `571330_599656_1_not_null` em ``
- `CHECK` `571330_599656_21_not_null` em ``
- `CHECK` `571330_599656_4_not_null` em ``
- `CHECK` `571330_599656_5_not_null` em ``
- `CHECK` `571330_599656_6_not_null` em ``
- `FOREIGN KEY` `performance_indicators_tab_indicador_cod_objetivo_foreign` em `cod_objetivo`
- `FOREIGN KEY` `performance_indicators_tab_indicador_cod_plano_de_acao_foreign` em `cod_plano_de_acao`
- `PRIMARY KEY` `tab_indicador_pkey` em `cod_indicador` -> performance_indicators.tab_indicador.cod_indicador

Indices verificados:
- `idx_indicador_calculation_type`: `CREATE INDEX idx_indicador_calculation_type ON performance_indicators.tab_indicador USING btree (dsc_calculation_type)`
- `performance_indicators_tab_indicador_cod_objetivo_index`: `CREATE INDEX performance_indicators_tab_indicador_cod_objetivo_index ON performance_indicators.tab_indicador USING btree (cod_objetivo)`
- `performance_indicators_tab_indicador_cod_plano_de_acao_index`: `CREATE INDEX performance_indicators_tab_indicador_cod_plano_de_acao_index ON performance_indicators.tab_indicador USING btree (cod_plano_de_acao)`
- `tab_indicador_pkey`: `CREATE UNIQUE INDEX tab_indicador_pkey ON performance_indicators.tab_indicador USING btree (cod_indicador)`

#### `performance_indicators.tab_linha_base_indicador`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_linha_base` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `cod_indicador` | `uuid/uuid` | `NO` | `` |
| `num_linha_base` | `numeric/numeric` | `NO` | `` |
| `num_ano` | `smallint/int2` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571330_599692_1_not_null` em ``
- `CHECK` `571330_599692_2_not_null` em ``
- `CHECK` `571330_599692_3_not_null` em ``
- `CHECK` `571330_599692_4_not_null` em ``
- `FOREIGN KEY` `performance_indicators_tab_linha_base_indicador_cod_indicador_f` em `cod_indicador` -> performance_indicators.tab_indicador.cod_indicador
- `PRIMARY KEY` `tab_linha_base_indicador_pkey` em `cod_linha_base` -> performance_indicators.tab_linha_base_indicador.cod_linha_base

Indices verificados:
- `performance_indicators_tab_linha_base_indicador_cod_indicador_n`: `CREATE INDEX performance_indicators_tab_linha_base_indicador_cod_indicador_n ON performance_indicators.tab_linha_base_indicador USING btree (cod_indicador, num_ano)`
- `tab_linha_base_indicador_pkey`: `CREATE UNIQUE INDEX tab_linha_base_indicador_pkey ON performance_indicators.tab_linha_base_indicador USING btree (cod_linha_base)`

#### `performance_indicators.tab_meta_por_ano`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_meta_por_ano` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `cod_indicador` | `uuid/uuid` | `NO` | `` |
| `num_ano` | `smallint/int2` | `NO` | `` |
| `meta` | `numeric/numeric` | `YES` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571330_599704_1_not_null` em ``
- `CHECK` `571330_599704_2_not_null` em ``
- `CHECK` `571330_599704_3_not_null` em ``
- `FOREIGN KEY` `performance_indicators_tab_meta_por_ano_cod_indicador_foreign` em `cod_indicador` -> performance_indicators.tab_indicador.cod_indicador
- `PRIMARY KEY` `tab_meta_por_ano_pkey` em `cod_meta_por_ano` -> performance_indicators.tab_meta_por_ano.cod_meta_por_ano

Indices verificados:
- `performance_indicators_tab_meta_por_ano_cod_indicador_num_ano_i`: `CREATE INDEX performance_indicators_tab_meta_por_ano_cod_indicador_num_ano_i ON performance_indicators.tab_meta_por_ano USING btree (cod_indicador, num_ano)`
- `tab_meta_por_ano_pkey`: `CREATE UNIQUE INDEX tab_meta_por_ano_pkey ON performance_indicators.tab_meta_por_ano USING btree (cod_meta_por_ano)`

#### `pei.audits`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `bigint/int8` | `NO` | `nextval('audits_id_seq'::regclass)` |
| `user_type` | `character varying/varchar(191)` | `YES` | `` |
| `user_id` | `uuid/uuid` | `YES` | `` |
| `event` | `character varying/varchar(191)` | `NO` | `` |
| `auditable_type` | `character varying/varchar(191)` | `NO` | `` |
| `auditable_id` | `uuid/uuid` | `NO` | `` |
| `old_values` | `text/text` | `YES` | `` |
| `new_values` | `text/text` | `YES` | `` |
| `url` | `text/text` | `YES` | `` |
| `ip_address` | `inet/inet` | `YES` | `` |
| `user_agent` | `character varying/varchar(1023)` | `YES` | `` |
| `tags` | `character varying/varchar(191)` | `YES` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `2200_599859_1_not_null` em ``
- `CHECK` `2200_599859_4_not_null` em ``
- `CHECK` `2200_599859_5_not_null` em ``
- `CHECK` `2200_599859_6_not_null` em ``
- `PRIMARY KEY` `audits_pkey` em `id` -> pei.audits.id

Indices verificados:
- `audits_auditable_id_auditable_type_index`: `CREATE INDEX audits_auditable_id_auditable_type_index ON pei.audits USING btree (auditable_id, auditable_type)`
- `audits_pkey`: `CREATE UNIQUE INDEX audits_pkey ON pei.audits USING btree (id)`
- `audits_user_id_user_type_index`: `CREATE INDEX audits_user_id_user_type_index ON pei.audits USING btree (user_id, user_type)`

#### `pei.cache`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `key` | `character varying/varchar(191)` | `NO` | `` |
| `value` | `text/text` | `NO` | `` |
| `expiration` | `integer/int4` | `NO` | `` |

Constraints verificadas:
- `CHECK` `2200_599404_1_not_null` em ``
- `CHECK` `2200_599404_2_not_null` em ``
- `CHECK` `2200_599404_3_not_null` em ``
- `PRIMARY KEY` `cache_pkey` em `key` -> pei.cache.key

Indices verificados:
- `cache_pkey`: `CREATE UNIQUE INDEX cache_pkey ON pei.cache USING btree (key)`

#### `pei.cache_locks`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `key` | `character varying/varchar(191)` | `NO` | `` |
| `owner` | `character varying/varchar(191)` | `NO` | `` |
| `expiration` | `integer/int4` | `NO` | `` |

Constraints verificadas:
- `CHECK` `2200_599412_1_not_null` em ``
- `CHECK` `2200_599412_2_not_null` em ``
- `CHECK` `2200_599412_3_not_null` em ``
- `PRIMARY KEY` `cache_locks_pkey` em `key` -> pei.cache_locks.key

Indices verificados:
- `cache_locks_pkey`: `CREATE UNIQUE INDEX cache_locks_pkey ON pei.cache_locks USING btree (key)`

#### `pei.failed_jobs`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `bigint/int8` | `NO` | `nextval('failed_jobs_id_seq'::regclass)` |
| `uuid` | `character varying/varchar(191)` | `NO` | `` |
| `connection` | `text/text` | `NO` | `` |
| `queue` | `text/text` | `NO` | `` |
| `payload` | `text/text` | `NO` | `` |
| `exception` | `text/text` | `NO` | `` |
| `failed_at` | `timestamp without time zone/timestamp` | `NO` | `CURRENT_TIMESTAMP` |

Constraints verificadas:
- `CHECK` `2200_599439_1_not_null` em ``
- `CHECK` `2200_599439_2_not_null` em ``
- `CHECK` `2200_599439_3_not_null` em ``
- `CHECK` `2200_599439_4_not_null` em ``
- `CHECK` `2200_599439_5_not_null` em ``
- `CHECK` `2200_599439_6_not_null` em ``
- `CHECK` `2200_599439_7_not_null` em ``
- `PRIMARY KEY` `failed_jobs_pkey` em `id` -> pei.failed_jobs.id
- `UNIQUE` `failed_jobs_uuid_unique` em `uuid` -> pei.failed_jobs.uuid

Indices verificados:
- `failed_jobs_pkey`: `CREATE UNIQUE INDEX failed_jobs_pkey ON pei.failed_jobs USING btree (id)`
- `failed_jobs_uuid_unique`: `CREATE UNIQUE INDEX failed_jobs_uuid_unique ON pei.failed_jobs USING btree (uuid)`

#### `pei.job_batches`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `character varying/varchar(191)` | `NO` | `` |
| `name` | `character varying/varchar(191)` | `NO` | `` |
| `total_jobs` | `integer/int4` | `NO` | `` |
| `pending_jobs` | `integer/int4` | `NO` | `` |
| `failed_jobs` | `integer/int4` | `NO` | `` |
| `failed_job_ids` | `text/text` | `NO` | `` |
| `options` | `text/text` | `YES` | `` |
| `cancelled_at` | `integer/int4` | `YES` | `` |
| `created_at` | `integer/int4` | `NO` | `` |
| `finished_at` | `integer/int4` | `YES` | `` |

Constraints verificadas:
- `CHECK` `2200_599429_1_not_null` em ``
- `CHECK` `2200_599429_2_not_null` em ``
- `CHECK` `2200_599429_3_not_null` em ``
- `CHECK` `2200_599429_4_not_null` em ``
- `CHECK` `2200_599429_5_not_null` em ``
- `CHECK` `2200_599429_6_not_null` em ``
- `CHECK` `2200_599429_9_not_null` em ``
- `PRIMARY KEY` `job_batches_pkey` em `id` -> pei.job_batches.id

Indices verificados:
- `job_batches_pkey`: `CREATE UNIQUE INDEX job_batches_pkey ON pei.job_batches USING btree (id)`

#### `pei.jobs`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `bigint/int8` | `NO` | `nextval('jobs_id_seq'::regclass)` |
| `queue` | `character varying/varchar(191)` | `NO` | `` |
| `payload` | `text/text` | `NO` | `` |
| `attempts` | `smallint/int2` | `NO` | `` |
| `reserved_at` | `integer/int4` | `YES` | `` |
| `available_at` | `integer/int4` | `NO` | `` |
| `created_at` | `integer/int4` | `NO` | `` |

Constraints verificadas:
- `CHECK` `2200_599419_1_not_null` em ``
- `CHECK` `2200_599419_2_not_null` em ``
- `CHECK` `2200_599419_3_not_null` em ``
- `CHECK` `2200_599419_4_not_null` em ``
- `CHECK` `2200_599419_6_not_null` em ``
- `CHECK` `2200_599419_7_not_null` em ``
- `PRIMARY KEY` `jobs_pkey` em `id` -> pei.jobs.id

Indices verificados:
- `jobs_pkey`: `CREATE UNIQUE INDEX jobs_pkey ON pei.jobs USING btree (id)`
- `jobs_queue_index`: `CREATE INDEX jobs_queue_index ON pei.jobs USING btree (queue)`

#### `pei.migrations`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `integer/int4` | `NO` | `nextval('migrations_id_seq'::regclass)` |
| `migration` | `character varying/varchar(191)` | `NO` | `` |
| `batch` | `integer/int4` | `NO` | `` |

Constraints verificadas:
- `CHECK` `2200_599379_1_not_null` em ``
- `CHECK` `2200_599379_2_not_null` em ``
- `CHECK` `2200_599379_3_not_null` em ``
- `PRIMARY KEY` `migrations_pkey` em `id` -> pei.migrations.id

Indices verificados:
- `migrations_pkey`: `CREATE UNIQUE INDEX migrations_pkey ON pei.migrations USING btree (id)`

#### `pei.password_reset_tokens`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `email` | `character varying/varchar(191)` | `NO` | `` |
| `token` | `character varying/varchar(191)` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `2200_599399_1_not_null` em ``
- `CHECK` `2200_599399_2_not_null` em ``
- `PRIMARY KEY` `password_reset_tokens_pkey` em `email` -> pei.password_reset_tokens.email

Indices verificados:
- `password_reset_tokens_pkey`: `CREATE UNIQUE INDEX password_reset_tokens_pkey ON pei.password_reset_tokens USING btree (email)`

#### `pei.personal_access_tokens`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `uuid/uuid` | `NO` | `` |
| `tokenable_type` | `character varying/varchar(191)` | `NO` | `` |
| `tokenable_id` | `uuid/uuid` | `NO` | `` |
| `name` | `text/text` | `NO` | `` |
| `token` | `character varying/varchar(64)` | `NO` | `` |
| `abilities` | `text/text` | `YES` | `` |
| `last_used_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `expires_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `2200_599879_1_not_null` em ``
- `CHECK` `2200_599879_2_not_null` em ``
- `CHECK` `2200_599879_3_not_null` em ``
- `CHECK` `2200_599879_4_not_null` em ``
- `CHECK` `2200_599879_5_not_null` em ``
- `PRIMARY KEY` `personal_access_tokens_pkey` em `id` -> pei.personal_access_tokens.id
- `UNIQUE` `personal_access_tokens_token_unique` em `token` -> pei.personal_access_tokens.token

Indices verificados:
- `personal_access_tokens_expires_at_index`: `CREATE INDEX personal_access_tokens_expires_at_index ON pei.personal_access_tokens USING btree (expires_at)`
- `personal_access_tokens_pkey`: `CREATE UNIQUE INDEX personal_access_tokens_pkey ON pei.personal_access_tokens USING btree (id)`
- `personal_access_tokens_token_unique`: `CREATE UNIQUE INDEX personal_access_tokens_token_unique ON pei.personal_access_tokens USING btree (token)`
- `personal_access_tokens_tokenable_type_tokenable_id_index`: `CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON pei.personal_access_tokens USING btree (tokenable_type, tokenable_id)`

#### `pei.sessions`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `character varying/varchar(191)` | `NO` | `` |
| `user_id` | `uuid/uuid` | `YES` | `` |
| `ip_address` | `character varying/varchar(45)` | `YES` | `` |
| `user_agent` | `text/text` | `YES` | `` |
| `payload` | `text/text` | `NO` | `` |
| `last_activity` | `integer/int4` | `NO` | `` |

Constraints verificadas:
- `CHECK` `2200_599451_1_not_null` em ``
- `CHECK` `2200_599451_5_not_null` em ``
- `CHECK` `2200_599451_6_not_null` em ``
- `PRIMARY KEY` `sessions_pkey` em `id` -> pei.sessions.id

Indices verificados:
- `sessions_last_activity_index`: `CREATE INDEX sessions_last_activity_index ON pei.sessions USING btree (last_activity)`
- `sessions_pkey`: `CREATE UNIQUE INDEX sessions_pkey ON pei.sessions USING btree (id)`
- `sessions_user_id_index`: `CREATE INDEX sessions_user_id_index ON pei.sessions USING btree (user_id)`

#### `pei.strategic_alerts`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `uuid/uuid` | `NO` | `` |
| `user_id` | `uuid/uuid` | `NO` | `` |
| `cod_organizacao` | `uuid/uuid` | `YES` | `` |
| `title` | `character varying/varchar(191)` | `NO` | `` |
| `message` | `text/text` | `NO` | `` |
| `icon` | `character varying/varchar(191)` | `NO` | `'bi-info-circle'::character varying` |
| `type` | `character varying/varchar(191)` | `NO` | `'info'::character varying` |
| `read_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `2200_600169_1_not_null` em ``
- `CHECK` `2200_600169_2_not_null` em ``
- `CHECK` `2200_600169_4_not_null` em ``
- `CHECK` `2200_600169_5_not_null` em ``
- `CHECK` `2200_600169_6_not_null` em ``
- `CHECK` `2200_600169_7_not_null` em ``
- `FOREIGN KEY` `strategic_alerts_user_id_foreign` em `user_id` -> pei.users.id
- `PRIMARY KEY` `strategic_alerts_pkey` em `id` -> pei.strategic_alerts.id

Indices verificados:
- `strategic_alerts_pkey`: `CREATE UNIQUE INDEX strategic_alerts_pkey ON pei.strategic_alerts USING btree (id)`

#### `pei.system_settings`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `bigint/int8` | `NO` | `nextval('system_settings_id_seq'::regclass)` |
| `key` | `character varying/varchar(191)` | `NO` | `` |
| `value` | `text/text` | `YES` | `` |
| `type` | `character varying/varchar(191)` | `NO` | `'string'::character varying` |
| `is_encrypted` | `boolean/bool` | `NO` | `false` |
| `description` | `character varying/varchar(191)` | `YES` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `2200_600156_1_not_null` em ``
- `CHECK` `2200_600156_2_not_null` em ``
- `CHECK` `2200_600156_4_not_null` em ``
- `CHECK` `2200_600156_5_not_null` em ``
- `PRIMARY KEY` `system_settings_pkey` em `id` -> pei.system_settings.id
- `UNIQUE` `system_settings_key_unique` em `key` -> pei.system_settings.key

Indices verificados:
- `system_settings_key_unique`: `CREATE UNIQUE INDEX system_settings_key_unique ON pei.system_settings USING btree (key)`
- `system_settings_pkey`: `CREATE UNIQUE INDEX system_settings_pkey ON pei.system_settings USING btree (id)`

#### `strategic_planning.tab_analise_ambiental`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_analise` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `cod_pei` | `uuid/uuid` | `NO` | `` |
| `cod_organizacao` | `uuid/uuid` | `YES` | `` |
| `dsc_tipo_analise` | `character varying/varchar(10)` | `NO` | `` |
| `dsc_categoria` | `character varying/varchar(20)` | `NO` | `` |
| `dsc_item` | `character varying/varchar(500)` | `NO` | `` |
| `num_impacto` | `integer/int4` | `NO` | `3` |
| `txt_observacao` | `text/text` | `YES` | `` |
| `num_ordem` | `integer/int4` | `NO` | `0` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `2200_599986_1_not_null` em ``
- `CHECK` `2200_599986_2_not_null` em ``
- `CHECK` `2200_599986_4_not_null` em ``
- `CHECK` `2200_599986_5_not_null` em ``
- `CHECK` `2200_599986_6_not_null` em ``
- `CHECK` `2200_599986_7_not_null` em ``
- `CHECK` `2200_599986_9_not_null` em ``
- `FOREIGN KEY` `tab_analise_ambiental_cod_organizacao_foreign` em `cod_organizacao`
- `FOREIGN KEY` `tab_analise_ambiental_cod_pei_foreign` em `cod_pei`
- `PRIMARY KEY` `tab_analise_ambiental_pkey` em `cod_analise` -> strategic_planning.tab_analise_ambiental.cod_analise

Indices verificados:
- `tab_analise_ambiental_cod_organizacao_index`: `CREATE INDEX tab_analise_ambiental_cod_organizacao_index ON strategic_planning.tab_analise_ambiental USING btree (cod_organizacao)`
- `tab_analise_ambiental_cod_pei_index`: `CREATE INDEX tab_analise_ambiental_cod_pei_index ON strategic_planning.tab_analise_ambiental USING btree (cod_pei)`
- `tab_analise_ambiental_dsc_tipo_analise_dsc_categoria_index`: `CREATE INDEX tab_analise_ambiental_dsc_tipo_analise_dsc_categoria_index ON strategic_planning.tab_analise_ambiental USING btree (dsc_tipo_analise, dsc_categoria)`
- `tab_analise_ambiental_pkey`: `CREATE UNIQUE INDEX tab_analise_ambiental_pkey ON strategic_planning.tab_analise_ambiental USING btree (cod_analise)`

#### `pei.tab_audit`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `acao` | `character varying/varchar(191)` | `NO` | `` |
| `antes` | `text/text` | `YES` | `` |
| `depois` | `text/text` | `YES` | `` |
| `table` | `character varying/varchar(191)` | `NO` | `` |
| `column_name` | `character varying/varchar(191)` | `NO` | `` |
| `data_type` | `character varying/varchar(191)` | `YES` | `` |
| `table_id` | `character varying/varchar(191)` | `NO` | `` |
| `ip` | `character varying/varchar(191)` | `NO` | `` |
| `user_id` | `uuid/uuid` | `NO` | `` |
| `dte_expired_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `2200_599716_10_not_null` em ``
- `CHECK` `2200_599716_1_not_null` em ``
- `CHECK` `2200_599716_2_not_null` em ``
- `CHECK` `2200_599716_5_not_null` em ``
- `CHECK` `2200_599716_6_not_null` em ``
- `CHECK` `2200_599716_8_not_null` em ``
- `CHECK` `2200_599716_9_not_null` em ``
- `FOREIGN KEY` `tab_audit_user_id_foreign` em `user_id` -> pei.users.id
- `PRIMARY KEY` `tab_audit_pkey` em `id` -> pei.tab_audit.id

Indices verificados:
- `tab_audit_acao_index`: `CREATE INDEX tab_audit_acao_index ON pei.tab_audit USING btree (acao)`
- `tab_audit_pkey`: `CREATE UNIQUE INDEX tab_audit_pkey ON pei.tab_audit USING btree (id)`
- `tab_audit_table_table_id_index`: `CREATE INDEX tab_audit_table_table_id_index ON pei.tab_audit USING btree ("table", table_id)`
- `tab_audit_user_id_index`: `CREATE INDEX tab_audit_user_id_index ON pei.tab_audit USING btree (user_id)`

#### `pei.tab_relatorios_agendados`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_agendamento` | `uuid/uuid` | `NO` | `` |
| `user_id` | `uuid/uuid` | `NO` | `` |
| `dsc_tipo_relatorio` | `character varying/varchar(191)` | `NO` | `` |
| `dsc_frequencia` | `character varying/varchar(191)` | `NO` | `` |
| `txt_filtros` | `jsonb/jsonb` | `YES` | `` |
| `dte_proxima_execucao` | `timestamp without time zone/timestamp` | `NO` | `` |
| `bln_ativo` | `boolean/bool` | `NO` | `true` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `2200_600190_1_not_null` em ``
- `CHECK` `2200_600190_2_not_null` em ``
- `CHECK` `2200_600190_3_not_null` em ``
- `CHECK` `2200_600190_4_not_null` em ``
- `CHECK` `2200_600190_6_not_null` em ``
- `CHECK` `2200_600190_7_not_null` em ``
- `FOREIGN KEY` `tab_relatorios_agendados_user_id_foreign` em `user_id` -> pei.users.id
- `PRIMARY KEY` `tab_relatorios_agendados_pkey` em `cod_agendamento` -> pei.tab_relatorios_agendados.cod_agendamento

Indices verificados:
- `tab_relatorios_agendados_pkey`: `CREATE UNIQUE INDEX tab_relatorios_agendados_pkey ON pei.tab_relatorios_agendados USING btree (cod_agendamento)`

#### `pei.tab_relatorios_gerados`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_relatorio_gerado` | `uuid/uuid` | `NO` | `` |
| `user_id` | `uuid/uuid` | `YES` | `` |
| `dsc_tipo_relatorio` | `character varying/varchar(191)` | `NO` | `` |
| `dsc_caminho_arquivo` | `character varying/varchar(191)` | `NO` | `` |
| `dsc_formato` | `character varying/varchar(191)` | `NO` | `` |
| `txt_filtros_aplicados` | `jsonb/jsonb` | `YES` | `` |
| `num_tamanho_bytes` | `integer/int4` | `YES` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `2200_600204_1_not_null` em ``
- `CHECK` `2200_600204_3_not_null` em ``
- `CHECK` `2200_600204_4_not_null` em ``
- `CHECK` `2200_600204_5_not_null` em ``
- `FOREIGN KEY` `tab_relatorios_gerados_user_id_foreign` em `user_id` -> pei.users.id
- `PRIMARY KEY` `tab_relatorios_gerados_pkey` em `cod_relatorio_gerado` -> pei.tab_relatorios_gerados.cod_relatorio_gerado

Indices verificados:
- `tab_relatorios_gerados_pkey`: `CREATE UNIQUE INDEX tab_relatorios_gerados_pkey ON pei.tab_relatorios_gerados USING btree (cod_relatorio_gerado)`

#### `pei.tab_status`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_status` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `dsc_status` | `text/text` | `NO` | `` |

Constraints verificadas:
- `CHECK` `2200_599870_1_not_null` em ``
- `CHECK` `2200_599870_2_not_null` em ``
- `PRIMARY KEY` `tab_status_pkey` em `cod_status` -> pei.tab_status.cod_status

Indices verificados:
- `tab_status_pkey`: `CREATE UNIQUE INDEX tab_status_pkey ON pei.tab_status USING btree (cod_status)`

#### `pei.users`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `id` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `name` | `character varying/varchar(191)` | `NO` | `` |
| `email` | `character varying/varchar(191)` | `NO` | `` |
| `ativo` | `smallint/int2` | `NO` | `'1'::smallint` |
| `adm` | `smallint/int2` | `NO` | `'2'::smallint` |
| `email_verified_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `password` | `character varying/varchar(191)` | `NO` | `` |
| `trocarsenha` | `smallint/int2` | `NO` | `'1'::smallint` |
| `two_factor_secret` | `text/text` | `YES` | `` |
| `two_factor_recovery_codes` | `text/text` | `YES` | `` |
| `two_factor_confirmed_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `remember_token` | `character varying/varchar(100)` | `YES` | `` |
| `current_team_id` | `uuid/uuid` | `YES` | `` |
| `profile_photo_path` | `character varying/varchar(2048)` | `YES` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `theme_color` | `character varying/varchar(191)` | `NO` | `'primary'::character varying` |

Constraints verificadas:
- `CHECK` `2200_599385_17_not_null` em ``
- `CHECK` `2200_599385_1_not_null` em ``
- `CHECK` `2200_599385_2_not_null` em ``
- `CHECK` `2200_599385_3_not_null` em ``
- `CHECK` `2200_599385_4_not_null` em ``
- `CHECK` `2200_599385_5_not_null` em ``
- `CHECK` `2200_599385_7_not_null` em ``
- `CHECK` `2200_599385_8_not_null` em ``
- `PRIMARY KEY` `users_pkey` em `id` -> pei.users.id
- `UNIQUE` `users_email_unique` em `email` -> pei.users.email

Indices verificados:
- `users_email_unique`: `CREATE UNIQUE INDEX users_email_unique ON pei.users USING btree (email)`
- `users_pkey`: `CREATE UNIQUE INDEX users_pkey ON pei.users USING btree (id)`

#### `risk_management.tab_risco`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_risco` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `cod_pei` | `uuid/uuid` | `NO` | `` |
| `cod_organizacao` | `uuid/uuid` | `NO` | `` |
| `num_codigo_risco` | `integer/int4` | `NO` | `` |
| `dsc_titulo` | `character varying/varchar(255)` | `NO` | `` |
| `txt_descricao` | `text/text` | `NO` | `` |
| `dsc_categoria` | `character varying/varchar(50)` | `NO` | `` |
| `dsc_status` | `character varying/varchar(50)` | `NO` | `` |
| `num_probabilidade` | `smallint/int2` | `NO` | `` |
| `num_impacto` | `smallint/int2` | `NO` | `` |
| `num_nivel_risco` | `smallint/int2` | `NO` | `` |
| `txt_causas` | `text/text` | `YES` | `` |
| `txt_consequencias` | `text/text` | `YES` | `` |
| `cod_responsavel_monitoramento` | `uuid/uuid` | `YES` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571331_599892_10_not_null` em ``
- `CHECK` `571331_599892_11_not_null` em ``
- `CHECK` `571331_599892_1_not_null` em ``
- `CHECK` `571331_599892_2_not_null` em ``
- `CHECK` `571331_599892_3_not_null` em ``
- `CHECK` `571331_599892_4_not_null` em ``
- `CHECK` `571331_599892_5_not_null` em ``
- `CHECK` `571331_599892_6_not_null` em ``
- `CHECK` `571331_599892_7_not_null` em ``
- `CHECK` `571331_599892_8_not_null` em ``
- `CHECK` `571331_599892_9_not_null` em ``
- `CHECK` `chk_impacto` em `` -> risk_management.tab_risco.num_impacto
- `CHECK` `chk_nivel_risco` em `` -> risk_management.tab_risco.num_nivel_risco
- `CHECK` `chk_probabilidade` em `` -> risk_management.tab_risco.num_probabilidade
- `FOREIGN KEY` `risk_management_tab_risco_cod_organizacao_foreign` em `cod_organizacao`
- `FOREIGN KEY` `risk_management_tab_risco_cod_pei_foreign` em `cod_pei`
- `FOREIGN KEY` `risk_management_tab_risco_cod_responsavel_monitoramento_foreign` em `cod_responsavel_monitoramento`
- `PRIMARY KEY` `tab_risco_pkey` em `cod_risco` -> risk_management.tab_risco.cod_risco

Indices verificados:
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
| `cod_mitigacao` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `cod_risco` | `uuid/uuid` | `NO` | `` |
| `dsc_tipo_mitigacao` | `character varying/varchar(50)` | `NO` | `` |
| `txt_acao_mitigacao` | `text/text` | `NO` | `` |
| `cod_responsavel` | `uuid/uuid` | `YES` | `` |
| `dte_prazo` | `date/date` | `YES` | `` |
| `dsc_status` | `character varying/varchar(50)` | `NO` | `` |
| `txt_observacoes` | `text/text` | `YES` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571331_599940_1_not_null` em ``
- `CHECK` `571331_599940_2_not_null` em ``
- `CHECK` `571331_599940_3_not_null` em ``
- `CHECK` `571331_599940_4_not_null` em ``
- `CHECK` `571331_599940_7_not_null` em ``
- `FOREIGN KEY` `risk_management_tab_risco_mitigacao_cod_responsavel_foreign` em `cod_responsavel`
- `FOREIGN KEY` `risk_management_tab_risco_mitigacao_cod_risco_foreign` em `cod_risco` -> risk_management.tab_risco.cod_risco
- `PRIMARY KEY` `tab_risco_mitigacao_pkey` em `cod_mitigacao` -> risk_management.tab_risco_mitigacao.cod_mitigacao

Indices verificados:
- `risk_management_tab_risco_mitigacao_cod_responsavel_index`: `CREATE INDEX risk_management_tab_risco_mitigacao_cod_responsavel_index ON risk_management.tab_risco_mitigacao USING btree (cod_responsavel)`
- `risk_management_tab_risco_mitigacao_cod_risco_index`: `CREATE INDEX risk_management_tab_risco_mitigacao_cod_risco_index ON risk_management.tab_risco_mitigacao USING btree (cod_risco)`
- `risk_management_tab_risco_mitigacao_dsc_status_index`: `CREATE INDEX risk_management_tab_risco_mitigacao_dsc_status_index ON risk_management.tab_risco_mitigacao USING btree (dsc_status)`
- `risk_management_tab_risco_mitigacao_dsc_tipo_mitigacao_index`: `CREATE INDEX risk_management_tab_risco_mitigacao_dsc_tipo_mitigacao_index ON risk_management.tab_risco_mitigacao USING btree (dsc_tipo_mitigacao)`
- `tab_risco_mitigacao_pkey`: `CREATE UNIQUE INDEX tab_risco_mitigacao_pkey ON risk_management.tab_risco_mitigacao USING btree (cod_mitigacao)`

#### `risk_management.tab_risco_objetivo`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_risco` | `uuid/uuid` | `NO` | `` |
| `cod_objetivo` | `uuid/uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571331_599925_1_not_null` em ``
- `CHECK` `571331_599925_2_not_null` em ``
- `FOREIGN KEY` `risk_management_tab_risco_objetivo_cod_objetivo_foreign` em `cod_objetivo`
- `FOREIGN KEY` `risk_management_tab_risco_objetivo_cod_risco_foreign` em `cod_risco` -> risk_management.tab_risco.cod_risco
- `PRIMARY KEY` `tab_risco_objetivo_pkey` em `cod_risco` -> risk_management.tab_risco_objetivo.cod_risco
- `PRIMARY KEY` `tab_risco_objetivo_pkey` em `cod_risco` -> risk_management.tab_risco_objetivo.cod_objetivo
- `PRIMARY KEY` `tab_risco_objetivo_pkey` em `cod_objetivo` -> risk_management.tab_risco_objetivo.cod_risco
- `PRIMARY KEY` `tab_risco_objetivo_pkey` em `cod_objetivo` -> risk_management.tab_risco_objetivo.cod_objetivo

Indices verificados:
- `tab_risco_objetivo_pkey`: `CREATE UNIQUE INDEX tab_risco_objetivo_pkey ON risk_management.tab_risco_objetivo USING btree (cod_risco, cod_objetivo)`

#### `risk_management.tab_risco_ocorrencia`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_ocorrencia` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `cod_risco` | `uuid/uuid` | `NO` | `` |
| `dte_ocorrencia` | `date/date` | `NO` | `` |
| `txt_descricao_ocorrencia` | `text/text` | `NO` | `` |
| `vlr_impacto_financeiro` | `numeric/numeric` | `YES` | `` |
| `txt_acoes_tomadas` | `text/text` | `YES` | `` |
| `txt_licoes_aprendidas` | `text/text` | `YES` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571331_599963_1_not_null` em ``
- `CHECK` `571331_599963_2_not_null` em ``
- `CHECK` `571331_599963_3_not_null` em ``
- `CHECK` `571331_599963_4_not_null` em ``
- `FOREIGN KEY` `risk_management_tab_risco_ocorrencia_cod_risco_foreign` em `cod_risco` -> risk_management.tab_risco.cod_risco
- `PRIMARY KEY` `tab_risco_ocorrencia_pkey` em `cod_ocorrencia` -> risk_management.tab_risco_ocorrencia.cod_ocorrencia

Indices verificados:
- `risk_management_tab_risco_ocorrencia_cod_risco_index`: `CREATE INDEX risk_management_tab_risco_ocorrencia_cod_risco_index ON risk_management.tab_risco_ocorrencia USING btree (cod_risco)`
- `risk_management_tab_risco_ocorrencia_dte_ocorrencia_index`: `CREATE INDEX risk_management_tab_risco_ocorrencia_dte_ocorrencia_index ON risk_management.tab_risco_ocorrencia USING btree (dte_ocorrencia)`
- `tab_risco_ocorrencia_pkey`: `CREATE UNIQUE INDEX tab_risco_ocorrencia_pkey ON risk_management.tab_risco_ocorrencia USING btree (cod_ocorrencia)`

#### `strategic_planning.tab_arquivos`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_arquivo` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `cod_evolucao_indicador` | `uuid/uuid` | `NO` | `` |
| `txt_assunto` | `text/text` | `NO` | `` |
| `data` | `text/text` | `NO` | `` |
| `dsc_nome_arquivo` | `text/text` | `NO` | `` |
| `dsc_tipo` | `character varying/varchar(191)` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571328_599742_1_not_null` em ``
- `CHECK` `571328_599742_2_not_null` em ``
- `CHECK` `571328_599742_3_not_null` em ``
- `CHECK` `571328_599742_4_not_null` em ``
- `CHECK` `571328_599742_5_not_null` em ``
- `CHECK` `571328_599742_6_not_null` em ``
- `FOREIGN KEY` `strategic_planning_tab_arquivos_cod_evolucao_indicador_foreign` em `cod_evolucao_indicador`
- `PRIMARY KEY` `tab_arquivos_pkey` em `cod_arquivo` -> strategic_planning.tab_arquivos.cod_arquivo

Indices verificados:
- `strategic_planning_tab_arquivos_cod_evolucao_indicador_index`: `CREATE INDEX strategic_planning_tab_arquivos_cod_evolucao_indicador_index ON strategic_planning.tab_arquivos USING btree (cod_evolucao_indicador)`
- `tab_arquivos_pkey`: `CREATE UNIQUE INDEX tab_arquivos_pkey ON strategic_planning.tab_arquivos USING btree (cod_arquivo)`

#### `strategic_planning.tab_atividade_cadeia_valor`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_atividade_cadeia_valor` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `cod_pei` | `uuid/uuid` | `NO` | `` |
| `cod_perspectiva` | `uuid/uuid` | `NO` | `` |
| `dsc_atividade` | `text/text` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571328_599757_1_not_null` em ``
- `CHECK` `571328_599757_2_not_null` em ``
- `CHECK` `571328_599757_3_not_null` em ``
- `CHECK` `571328_599757_4_not_null` em ``
- `FOREIGN KEY` `strategic_planning_tab_atividade_cadeia_valor_cod_pei_foreign` em `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `FOREIGN KEY` `strategic_planning_tab_atividade_cadeia_valor_cod_perspectiva_f` em `cod_perspectiva` -> strategic_planning.tab_perspectiva.cod_perspectiva
- `PRIMARY KEY` `tab_atividade_cadeia_valor_pkey` em `cod_atividade_cadeia_valor` -> strategic_planning.tab_atividade_cadeia_valor.cod_atividade_cadeia_valor

Indices verificados:
- `strategic_planning_tab_atividade_cadeia_valor_cod_pei_index`: `CREATE INDEX strategic_planning_tab_atividade_cadeia_valor_cod_pei_index ON strategic_planning.tab_atividade_cadeia_valor USING btree (cod_pei)`
- `strategic_planning_tab_atividade_cadeia_valor_cod_perspectiva_i`: `CREATE INDEX strategic_planning_tab_atividade_cadeia_valor_cod_perspectiva_i ON strategic_planning.tab_atividade_cadeia_valor USING btree (cod_perspectiva)`
- `tab_atividade_cadeia_valor_pkey`: `CREATE UNIQUE INDEX tab_atividade_cadeia_valor_pkey ON strategic_planning.tab_atividade_cadeia_valor USING btree (cod_atividade_cadeia_valor)`

#### `strategic_planning.tab_futuro_almejado_objetivo`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_futuro_almejado` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `dsc_futuro_almejado` | `text/text` | `NO` | `` |
| `cod_objetivo` | `uuid/uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571328_599812_1_not_null` em ``
- `CHECK` `571328_599812_2_not_null` em ``
- `CHECK` `571328_599812_3_not_null` em ``
- `FOREIGN KEY` `strategic_planning_tab_futuro_almejado_objetivo_estrategico_cod` em `cod_objetivo` -> strategic_planning.tab_objetivo.cod_objetivo
- `PRIMARY KEY` `tab_futuro_almejado_objetivo_estrategico_pkey` em `cod_futuro_almejado` -> strategic_planning.tab_futuro_almejado_objetivo.cod_futuro_almejado

Indices verificados:
- `strategic_planning_tab_futuro_almejado_objetivo_estrategico_cod`: `CREATE INDEX strategic_planning_tab_futuro_almejado_objetivo_estrategico_cod ON strategic_planning.tab_futuro_almejado_objetivo USING btree (cod_objetivo)`
- `tab_futuro_almejado_objetivo_estrategico_pkey`: `CREATE UNIQUE INDEX tab_futuro_almejado_objetivo_estrategico_pkey ON strategic_planning.tab_futuro_almejado_objetivo USING btree (cod_futuro_almejado)`

#### `strategic_planning.tab_grau_satisfacao`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_grau_satisfacao` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `dsc_grau_satisfacao` | `text/text` | `NO` | `` |
| `cor` | `character varying/varchar(191)` | `NO` | `` |
| `vlr_minimo` | `numeric/numeric` | `NO` | `` |
| `vlr_maximo` | `numeric/numeric` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `cod_pei` | `uuid/uuid` | `YES` | `` |
| `num_ano` | `integer/int4` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571328_599733_1_not_null` em ``
- `CHECK` `571328_599733_2_not_null` em ``
- `CHECK` `571328_599733_3_not_null` em ``
- `CHECK` `571328_599733_4_not_null` em ``
- `CHECK` `571328_599733_5_not_null` em ``
- `FOREIGN KEY` `strategic_planning_tab_grau_satisfacao_cod_pei_foreign` em `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `PRIMARY KEY` `tab_grau_satisfcao_pkey` em `cod_grau_satisfacao` -> strategic_planning.tab_grau_satisfacao.cod_grau_satisfacao

Indices verificados:
- `idx_grau_satisfacao_pei_ano`: `CREATE INDEX idx_grau_satisfacao_pei_ano ON strategic_planning.tab_grau_satisfacao USING btree (cod_pei, num_ano)`
- `tab_grau_satisfcao_pkey`: `CREATE UNIQUE INDEX tab_grau_satisfcao_pkey ON strategic_planning.tab_grau_satisfacao USING btree (cod_grau_satisfacao)`

#### `strategic_planning.tab_missao_visao_valores`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_missao_visao_valores` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `dsc_missao` | `text/text` | `NO` | `` |
| `dsc_visao` | `text/text` | `NO` | `` |
| `cod_pei` | `uuid/uuid` | `NO` | `` |
| `cod_organizacao` | `uuid/uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571328_599540_1_not_null` em ``
- `CHECK` `571328_599540_2_not_null` em ``
- `CHECK` `571328_599540_3_not_null` em ``
- `CHECK` `571328_599540_4_not_null` em ``
- `CHECK` `571328_599540_5_not_null` em ``
- `FOREIGN KEY` `strategic_planning_tab_missao_visao_valores_cod_organizacao_for` em `cod_organizacao`
- `FOREIGN KEY` `strategic_planning_tab_missao_visao_valores_cod_pei_foreign` em `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `PRIMARY KEY` `tab_missao_visao_valores_pkey` em `cod_missao_visao_valores` -> strategic_planning.tab_missao_visao_valores.cod_missao_visao_valores

Indices verificados:
- `tab_missao_visao_valores_pkey`: `CREATE UNIQUE INDEX tab_missao_visao_valores_pkey ON strategic_planning.tab_missao_visao_valores USING btree (cod_missao_visao_valores)`

#### `strategic_planning.tab_nivel_hierarquico`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `num_nivel_hierarquico_apresentacao` | `smallint/int2` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571328_599587_1_not_null` em ``
- `PRIMARY KEY` `tab_nivel_hierarquico_pkey` em `num_nivel_hierarquico_apresentacao` -> strategic_planning.tab_nivel_hierarquico.num_nivel_hierarquico_apresentacao

Indices verificados:
- `tab_nivel_hierarquico_pkey`: `CREATE UNIQUE INDEX tab_nivel_hierarquico_pkey ON strategic_planning.tab_nivel_hierarquico USING btree (num_nivel_hierarquico_apresentacao)`

#### `strategic_planning.tab_objetivo`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_objetivo` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `nom_objetivo` | `text/text` | `NO` | `` |
| `dsc_objetivo` | `text/text` | `NO` | `` |
| `num_nivel_hierarquico_apresentacao` | `smallint/int2` | `NO` | `` |
| `cod_perspectiva` | `uuid/uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571328_599573_1_not_null` em ``
- `CHECK` `571328_599573_2_not_null` em ``
- `CHECK` `571328_599573_3_not_null` em ``
- `CHECK` `571328_599573_4_not_null` em ``
- `CHECK` `571328_599573_5_not_null` em ``
- `FOREIGN KEY` `strategic_planning_tab_objetivo_estrategico_cod_perspectiva_for` em `cod_perspectiva` -> strategic_planning.tab_perspectiva.cod_perspectiva
- `PRIMARY KEY` `tab_objetivo_estrategico_pkey` em `cod_objetivo` -> strategic_planning.tab_objetivo.cod_objetivo

Indices verificados:
- `tab_objetivo_estrategico_pkey`: `CREATE UNIQUE INDEX tab_objetivo_estrategico_pkey ON strategic_planning.tab_objetivo USING btree (cod_objetivo)`

#### `strategic_planning.tab_objetivo_comentarios`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_comentario` | `uuid/uuid` | `NO` | `` |
| `cod_objetivo` | `uuid/uuid` | `NO` | `` |
| `user_id` | `uuid/uuid` | `NO` | `` |
| `dsc_comentario` | `text/text` | `NO` | `` |
| `cod_comentario_pai` | `uuid/uuid` | `YES` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571328_600217_1_not_null` em ``
- `CHECK` `571328_600217_2_not_null` em ``
- `CHECK` `571328_600217_3_not_null` em ``
- `CHECK` `571328_600217_4_not_null` em ``
- `FOREIGN KEY` `strategic_planning_tab_objetivo_comentarios_cod_objetivo_foreig` em `cod_objetivo` -> strategic_planning.tab_objetivo.cod_objetivo
- `FOREIGN KEY` `strategic_planning_tab_objetivo_comentarios_user_id_foreign` em `user_id`
- `PRIMARY KEY` `tab_objetivo_comentarios_pkey` em `cod_comentario` -> strategic_planning.tab_objetivo_comentarios.cod_comentario

Indices verificados:
- `tab_objetivo_comentarios_pkey`: `CREATE UNIQUE INDEX tab_objetivo_comentarios_pkey ON strategic_planning.tab_objetivo_comentarios USING btree (cod_comentario)`

#### `strategic_planning.tab_pei`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_pei` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `dsc_pei` | `text/text` | `NO` | `` |
| `num_ano_inicio_pei` | `smallint/int2` | `NO` | `` |
| `num_ano_fim_pei` | `smallint/int2` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571328_599531_1_not_null` em ``
- `CHECK` `571328_599531_2_not_null` em ``
- `CHECK` `571328_599531_3_not_null` em ``
- `CHECK` `571328_599531_4_not_null` em ``
- `PRIMARY KEY` `tab_pei_pkey` em `cod_pei` -> strategic_planning.tab_pei.cod_pei

Indices verificados:
- `tab_pei_pkey`: `CREATE UNIQUE INDEX tab_pei_pkey ON strategic_planning.tab_pei USING btree (cod_pei)`

#### `strategic_planning.tab_perspectiva`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_perspectiva` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `dsc_perspectiva` | `text/text` | `NO` | `` |
| `num_nivel_hierarquico_apresentacao` | `smallint/int2` | `NO` | `` |
| `cod_pei` | `uuid/uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `num_peso_indicadores` | `integer/int4` | `NO` | `100` |
| `num_peso_planos` | `integer/int4` | `NO` | `0` |

Constraints verificadas:
- `CHECK` `571328_599559_1_not_null` em ``
- `CHECK` `571328_599559_2_not_null` em ``
- `CHECK` `571328_599559_3_not_null` em ``
- `CHECK` `571328_599559_4_not_null` em ``
- `CHECK` `571328_599559_8_not_null` em ``
- `CHECK` `571328_599559_9_not_null` em ``
- `FOREIGN KEY` `strategic_planning_tab_perspectiva_cod_pei_foreign` em `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `PRIMARY KEY` `tab_perspectiva_pkey` em `cod_perspectiva` -> strategic_planning.tab_perspectiva.cod_perspectiva

Indices verificados:
- `tab_perspectiva_pkey`: `CREATE UNIQUE INDEX tab_perspectiva_pkey ON strategic_planning.tab_perspectiva USING btree (cod_perspectiva)`

#### `strategic_planning.tab_processos_atividade_cadeia_valor`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_processo_atividade_cadeia_valor` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `cod_atividade_cadeia_valor` | `uuid/uuid` | `NO` | `` |
| `dsc_entrada` | `text/text` | `NO` | `` |
| `dsc_transformacao` | `text/text` | `NO` | `` |
| `dsc_saida` | `text/text` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571328_599778_1_not_null` em ``
- `CHECK` `571328_599778_2_not_null` em ``
- `CHECK` `571328_599778_3_not_null` em ``
- `CHECK` `571328_599778_4_not_null` em ``
- `CHECK` `571328_599778_5_not_null` em ``
- `FOREIGN KEY` `strategic_planning_tab_processos_atividade_cadeia_valor_cod_ati` em `cod_atividade_cadeia_valor` -> strategic_planning.tab_atividade_cadeia_valor.cod_atividade_cadeia_valor
- `PRIMARY KEY` `tab_processos_atividade_cadeia_valor_pkey` em `cod_processo_atividade_cadeia_valor` -> strategic_planning.tab_processos_atividade_cadeia_valor.cod_processo_atividade_cadeia_valor

Indices verificados:
- `strategic_planning_tab_processos_atividade_cadeia_valor_cod_ati`: `CREATE INDEX strategic_planning_tab_processos_atividade_cadeia_valor_cod_ati ON strategic_planning.tab_processos_atividade_cadeia_valor USING btree (cod_atividade_cadeia_valor)`
- `tab_processos_atividade_cadeia_valor_pkey`: `CREATE UNIQUE INDEX tab_processos_atividade_cadeia_valor_pkey ON strategic_planning.tab_processos_atividade_cadeia_valor USING btree (cod_processo_atividade_cadeia_valor)`

#### `strategic_planning.tab_tema_norteador`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_tema_norteador` | `uuid/uuid` | `NO` | `` |
| `nom_tema_norteador` | `text/text` | `NO` | `` |
| `cod_pei` | `uuid/uuid` | `NO` | `` |
| `cod_organizacao` | `uuid/uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571328_600136_1_not_null` em ``
- `CHECK` `571328_600136_2_not_null` em ``
- `CHECK` `571328_600136_3_not_null` em ``
- `CHECK` `571328_600136_4_not_null` em ``
- `FOREIGN KEY` `strategic_planning_tab_objetivo_estrategico_cod_organizacao_for` em `cod_organizacao`
- `FOREIGN KEY` `strategic_planning_tab_objetivo_estrategico_cod_pei_foreign` em `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `PRIMARY KEY` `tab_objetivo_estrategico_pkey1` em `cod_tema_norteador` -> strategic_planning.tab_tema_norteador.cod_tema_norteador

Indices verificados:
- `tab_objetivo_estrategico_pkey1`: `CREATE UNIQUE INDEX tab_objetivo_estrategico_pkey1 ON strategic_planning.tab_tema_norteador USING btree (cod_tema_norteador)`

#### `strategic_planning.tab_valores`

| Coluna | Tipo | Nulo | Default |
|---|---|---|---|
| `cod_valor` | `uuid/uuid` | `NO` | `gen_random_uuid()` |
| `nom_valor` | `text/text` | `NO` | `` |
| `dsc_valor` | `text/text` | `NO` | `` |
| `cod_pei` | `uuid/uuid` | `NO` | `` |
| `cod_organizacao` | `uuid/uuid` | `NO` | `` |
| `created_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `updated_at` | `timestamp without time zone/timestamp` | `YES` | `` |
| `deleted_at` | `timestamp without time zone/timestamp` | `YES` | `` |

Constraints verificadas:
- `CHECK` `571328_599793_1_not_null` em ``
- `CHECK` `571328_599793_2_not_null` em ``
- `CHECK` `571328_599793_3_not_null` em ``
- `CHECK` `571328_599793_4_not_null` em ``
- `CHECK` `571328_599793_5_not_null` em ``
- `FOREIGN KEY` `strategic_planning_tab_valores_cod_organizacao_foreign` em `cod_organizacao`
- `FOREIGN KEY` `strategic_planning_tab_valores_cod_pei_foreign` em `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `PRIMARY KEY` `tab_valores_pkey` em `cod_valor` -> strategic_planning.tab_valores.cod_valor

Indices verificados:
- `tab_valores_pkey`: `CREATE UNIQUE INDEX tab_valores_pkey ON strategic_planning.tab_valores USING btree (cod_valor)`

## Rotas efetivas

Verificado em runtime por `php artisan route:list` e colecao real de rotas Laravel.

| Metodo | URI | Nome | Acao | Middleware |
|---|---|---|---|---|
| `GET|POST|PUT|PATCH|DELETE|OPTIONS` | `/` | `welcome` | `App\Livewire\StrategicPlanning\MapaEstrategico` | `web` |
| `GET` | `api/user` | `` | `Closure` | `api, auth:sanctum` |
| `GET` | `auditoria` | `audit.index` | `App\Livewire\Audit\ListarLogs` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `auditoria/{id}/detalhes` | `audit.detalhes` | `App\Livewire\Audit\DetalharLog` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `configuracoes` | `admin.configuracoes` | `App\Livewire\Admin\ConfiguracaoSistema` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `dashboard` | `dashboard` | `App\Livewire\Dashboard\Index` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `entregas` | `entregas.index` | `App\Livewire\Deliverables\DeliverablesBoard` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `forgot-password` | `password.request` | `Laravel\Fortify\Http\Controllers\PasswordResetLinkController@create` | `web, guest:web` |
| `POST` | `forgot-password` | `password.email` | `Laravel\Fortify\Http\Controllers\PasswordResetLinkController@store` | `web, guest:web` |
| `GET` | `graus-satisfacao` | `graus-satisfacao.index` | `App\Livewire\StrategicPlanning\ListarGrausSatisfacao` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `graus-satisfacao/{id}/detalhes` | `graus-satisfacao.detalhes` | `App\Livewire\StrategicPlanning\DetalharGrauSatisfacao` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `indicadores` | `indicadores.index` | `App\Livewire\PerformanceIndicators\ListarIndicadores` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `indicadores/{id}/detalhes` | `indicadores.detalhes` | `App\Livewire\PerformanceIndicators\DetalharIndicador` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `indicadores/{indicadorId}/evolucao` | `indicadores.evolucao` | `App\Livewire\PerformanceIndicators\LancarEvolucao` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `leads` | `leads.index` | `App\Livewire\LeadsTable` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `livewire/livewire.js` | `` | `Livewire\Mechanisms\FrontendAssets\FrontendAssets@returnJavaScriptAsFile` | `` |
| `GET` | `livewire/livewire.min.js.map` | `` | `Livewire\Mechanisms\FrontendAssets\FrontendAssets@maps` | `` |
| `GET` | `livewire/preview-file/{filename}` | `livewire.preview-file` | `Livewire\Features\SupportFileUploads\FilePreviewController@handle` | `web` |
| `POST` | `livewire/update` | `livewire.update` | `Livewire\Mechanisms\HandleRequests\HandleRequests@handleUpdate` | `web` |
| `POST` | `livewire/upload-file` | `livewire.upload-file` | `Livewire\Features\SupportFileUploads\FileUploadController@handle` | `web, throttle:60,1` |
| `GET` | `login` | `login` | `Laravel\Fortify\Http\Controllers\AuthenticatedSessionController@create` | `web, guest:web` |
| `POST` | `login` | `login.store` | `Laravel\Fortify\Http\Controllers\AuthenticatedSessionController@store` | `web, guest:web, throttle:login` |
| `POST` | `logout` | `logout` | `Laravel\Fortify\Http\Controllers\AuthenticatedSessionController@destroy` | `web, auth:web` |
| `GET` | `objetivos` | `objetivos.index` | `App\Livewire\StrategicPlanning\ListarObjetivos` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `objetivos/{id}/detalhes` | `objetivos.detalhes` | `App\Livewire\StrategicPlanning\DetalharObjetivo` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `objetivos/{objetivoId}/futuro` | `objetivos.futuro` | `App\Livewire\StrategicPlanning\GerenciarFuturoAlmejado` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `organizacoes` | `organizacoes.index` | `App\Livewire\Organization\ListarOrganizacoes` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `organizacoes/{id}/detalhes` | `organizacoes.detalhes` | `App\Livewire\Organization\DetalharOrganizacao` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `pei` | `pei.index` | `App\Livewire\StrategicPlanning\MissaoVisao` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `pei/ciclos` | `pei.ciclos` | `App\Livewire\StrategicPlanning\ListarPeis` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `pei/identidade/{id}/detalhes` | `pei.identidade.detalhes` | `App\Livewire\StrategicPlanning\DetalharIdentidade` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `pei/mapa` | `pei.mapa` | `App\Livewire\StrategicPlanning\MapaEstrategico` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `pei/perspectivas` | `pei.perspectivas` | `App\Livewire\StrategicPlanning\ListarPerspectivas` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `pei/perspectivas/{id}/detalhes` | `pei.perspectivas.detalhes` | `App\Livewire\StrategicPlanning\DetalharPerspectiva` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `pei/pestel` | `pei.pestel` | `App\Livewire\StrategicPlanning\AnalisePESTEL` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `pei/swot` | `pei.swot` | `App\Livewire\StrategicPlanning\AnaliseSWOT` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `pei/valores` | `pei.valores` | `App\Livewire\StrategicPlanning\ListarValores` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `pei/valores/{id}/detalhes` | `pei.valores.detalhes` | `App\Livewire\StrategicPlanning\DetalharValor` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `pei/{id}/detalhes` | `pei.detalhes` | `App\Livewire\StrategicPlanning\DetalharPei` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `planos` | `planos.index` | `App\Livewire\ActionPlan\ListarPlanos` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `planos/{id}/detalhes` | `planos.detalhes` | `App\Livewire\ActionPlan\DetalharPlano` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `planos/{planoId}/entregas` | `planos.entregas` | `App\Livewire\Deliverables\DeliverablesBoard` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `planos/{planoId}/responsaveis` | `planos.responsaveis` | `App\Livewire\ActionPlan\AtribuirResponsaveis` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `refresh-csrf` | `csrf.refresh` | `Closure` | `web` |
| `GET` | `register` | `register` | `Laravel\Fortify\Http\Controllers\RegisteredUserController@create` | `web, guest:web` |
| `POST` | `register` | `register.store` | `Laravel\Fortify\Http\Controllers\RegisteredUserController@store` | `web, guest:web` |
| `GET` | `relatorios` | `relatorios.index` | `App\Livewire\Reports\ListarRelatorios` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `relatorios/executivo/{organizacaoId?}` | `relatorios.executivo` | `App\Http\Controllers\Reports\RelatorioController@executivo` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `relatorios/historico` | `relatorios.historico` | `App\Livewire\Reports\HistoricoRelatorios` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `relatorios/identidade/{organizacaoId}` | `relatorios.identidade` | `App\Http\Controllers\Reports\RelatorioController@identidade` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `relatorios/indicadores/excel/{organizacaoId?}` | `relatorios.indicadores.excel` | `App\Http\Controllers\Reports\RelatorioController@indicadoresExcel` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `relatorios/indicadores/pdf/{organizacaoId?}` | `relatorios.indicadores.pdf` | `App\Http\Controllers\Reports\RelatorioController@indicadoresPdf` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `relatorios/integrado/{organizacaoId?}` | `relatorios.integrado` | `App\Http\Controllers\Reports\RelatorioController@integrado` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `relatorios/objetivos/excel` | `relatorios.objetivos.excel` | `App\Http\Controllers\Reports\RelatorioController@objetivosExcel` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `relatorios/objetivos/pdf` | `relatorios.objetivos.pdf` | `App\Http\Controllers\Reports\RelatorioController@objetivosPdf` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `relatorios/planos/excel` | `relatorios.planos.excel` | `App\Http\Controllers\Reports\RelatorioController@planosExcel` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `relatorios/planos/pdf` | `relatorios.planos.pdf` | `App\Http\Controllers\Reports\RelatorioController@planosPdf` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `relatorios/riscos/excel` | `relatorios.riscos.excel` | `App\Http\Controllers\Reports\RelatorioController@riscosExcel` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `relatorios/riscos/pdf` | `relatorios.riscos.pdf` | `App\Http\Controllers\Reports\RelatorioController@riscosPdf` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `POST` | `reset-password` | `password.update` | `Laravel\Fortify\Http\Controllers\NewPasswordController@store` | `web, guest:web` |
| `GET` | `reset-password/{token}` | `password.reset` | `Laravel\Fortify\Http\Controllers\NewPasswordController@create` | `web, guest:web` |
| `GET` | `riscos` | `riscos.index` | `App\Livewire\RiskManagement\ListarRiscos` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `riscos/matriz` | `riscos.matriz` | `App\Livewire\RiskManagement\MatrizRiscos` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `riscos/{riscoId}/mitigacao` | `riscos.mitigacao` | `App\Livewire\RiskManagement\GerenciarMitigacoes` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `riscos/{riscoId}/ocorrencias` | `riscos.ocorrencias` | `App\Livewire\RiskManagement\RegistrarOcorrencias` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `sanctum/csrf-cookie` | `sanctum.csrf-cookie` | `Laravel\Sanctum\Http\Controllers\CsrfCookieController@show` | `web` |
| `POST` | `session/ping` | `session.ping` | `Closure` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `storage/{path}` | `storage.local` | `Closure` | `` |
| `PUT` | `storage/{path}` | `storage.local.upload` | `Closure` | `` |
| `GET` | `temas-norteadores` | `temas-norteadores.index` | `App\Livewire\StrategicPlanning\GerenciarTemasNorteadores` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `trocar-senha` | `auth.trocar-senha` | `App\Livewire\Auth\TrocarSenha` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `two-factor-challenge` | `two-factor.login` | `Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController@create` | `web, guest:web` |
| `POST` | `two-factor-challenge` | `two-factor.login.store` | `Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController@store` | `web, guest:web, throttle:two-factor` |
| `GET` | `up` | `` | `Closure` | `` |
| `GET` | `user/confirm-password` | `password.confirm` | `Laravel\Fortify\Http\Controllers\ConfirmablePasswordController@show` | `web, auth:web` |
| `POST` | `user/confirm-password` | `password.confirm.store` | `Laravel\Fortify\Http\Controllers\ConfirmablePasswordController@store` | `web, auth:web` |
| `GET` | `user/confirmed-password-status` | `password.confirmation` | `Laravel\Fortify\Http\Controllers\ConfirmedPasswordStatusController@show` | `web, auth:web` |
| `POST` | `user/confirmed-two-factor-authentication` | `two-factor.confirm` | `Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController@store` | `web, auth:web, password.confirm` |
| `PUT` | `user/password` | `user-password.update` | `Laravel\Fortify\Http\Controllers\PasswordController@update` | `web, auth:web` |
| `GET` | `user/profile` | `profile.show` | `Laravel\Jetstream\Http\Controllers\Livewire\UserProfileController@show` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession` |
| `PUT` | `user/profile-information` | `user-profile-information.update` | `Laravel\Fortify\Http\Controllers\ProfileInformationController@update` | `web, auth:web` |
| `DELETE` | `user/two-factor-authentication` | `two-factor.disable` | `Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController@destroy` | `web, auth:web, password.confirm` |
| `POST` | `user/two-factor-authentication` | `two-factor.enable` | `Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController@store` | `web, auth:web, password.confirm` |
| `GET` | `user/two-factor-qr-code` | `two-factor.qr-code` | `Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController@show` | `web, auth:web, password.confirm` |
| `GET` | `user/two-factor-recovery-codes` | `two-factor.recovery-codes` | `Laravel\Fortify\Http\Controllers\RecoveryCodeController@index` | `web, auth:web, password.confirm` |
| `POST` | `user/two-factor-recovery-codes` | `two-factor.regenerate-recovery-codes` | `Laravel\Fortify\Http\Controllers\RecoveryCodeController@store` | `web, auth:web, password.confirm` |
| `GET` | `user/two-factor-secret-key` | `two-factor.secret-key` | `Laravel\Fortify\Http\Controllers\TwoFactorSecretKeyController@show` | `web, auth:web, password.confirm` |
| `GET` | `usuarios` | `usuarios.index` | `App\Livewire\UserManagement\ListarUsuarios` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |
| `GET` | `usuarios/{id}/detalhes` | `usuarios.detalhes` | `App\Livewire\UserManagement\DetalharUsuario` | `web, auth:sanctum, Laravel\Jetstream\Http\Middleware\AuthenticateSession, verified` |

## Inventario de Models

Inventario extraido do codigo. Para models de dominio, a tabela declarada e relevante porque o banco usa schemas PostgreSQL e `search_path`.

### `App\Models\ActionPlan\Acao`

- Fonte: verificado no codigo em `app/Models/ActionPlan/Acao.php`.
- Tabela declarada: `acoes` com PK `id`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public user` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorTabela` | `$query, string $tabela` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorRegistro` | `$query, string $tableId` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorUsuario` | `$query, string $userId` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeRecentes` | `$query, int $dias = 7` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\ActionPlan\Entrega`

- Fonte: verificado no codigo em `app/Models/ActionPlan/Entrega.php`.
- Tabela declarada: `tab_entregas` com PK `cod_entrega`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public planoDeAcao` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public entregaPai` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public subEntregas` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public responsavel` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public responsaveis` | `sem parametros` | `BelongsToMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public comentarios` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public labels` | `sem parametros` | `BelongsToMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public anexos` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public historico` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isConcluida` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isAtrasada` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isSubEntrega` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public hasSubEntregas` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getStatusColor` | `sem parametros` | `string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getPrioridadeInfo` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getTipoInfo` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getProp` | `string $key, $default = null` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public setProp` | `string $key, $value` | `self` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public calcularProgressoSubEntregas` | `sem parametros` | `float` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public registrarHistorico` | `string $acao, ?string $campo = null, $valorAntigo = null, $valorNovo = null, ?string $descricao = null` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorStatus` | `$query, string $status` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeConcluidas` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePendentes` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeOrdenadoPorNivel` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeOrdenado` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeRaiz` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeAtivas` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeArquivadas` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorPrioridade` | `$query, string $prioridade` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorResponsavel` | `$query, int $userId` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeAtrasadas` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeTarefas` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeDeletadasRecentemente` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\ActionPlan\EntregaAnexo`

- Fonte: verificado no codigo em `app/Models/ActionPlan/EntregaAnexo.php`.
- Tabela declarada: `tab_entrega_anexos` com PK `cod_anexo`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public entrega` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public usuario` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isImagem` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isDocumento` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getUrl` | `sem parametros` | `string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getTamanhoFormatado` | `sem parametros` | `string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getIcone` | `sem parametros` | `string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getExtensao` | `sem parametros` | `string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\ActionPlan\EntregaComentario`

- Fonte: verificado no codigo em `app/Models/ActionPlan/EntregaComentario.php`.
- Tabela declarada: `tab_entrega_comentarios` com PK `cod_comentario`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public entrega` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public usuario` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public comentarioPai` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public respostas` | `sem parametros` | `\Illuminate\Database\Eloquent\Relations\HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getUsuariosMencionados` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public mencionou` | `int $userId` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\ActionPlan\EntregaHistorico`

- Fonte: verificado no codigo em `app/Models/ActionPlan/EntregaHistorico.php`.
- Tabela declarada: `tab_entrega_historico` com PK `cod_historico`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public entrega` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public usuario` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getAcaoInfo` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getValorAntigo` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getValorNovo` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getDescricaoLegivel` | `sem parametros` | `string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected getCampoLabel` | `sem parametros` | `string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getTempoRelativo` | `sem parametros` | `string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorAcao` | `$query, string $acao` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorUsuario` | `$query, int $userId` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeRecentes` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\ActionPlan\EntregaLabel`

- Fonte: verificado no codigo em `app/Models/ActionPlan/EntregaLabel.php`.
- Tabela declarada: `tab_entrega_labels` com PK `cod_label`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public planoDeAcao` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public entregas` | `sem parametros` | `BelongsToMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getCorRgb` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isCorEscura` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getCorTexto` | `sem parametros` | `string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeOrdenado` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\ActionPlan\PlanoDeAcao`

- Fonte: verificado no codigo em `app/Models/ActionPlan/PlanoDeAcao.php`.
- Tabela declarada: `tab_plano_de_acao` com PK `cod_plano_de_acao`.
- Auditoria: implementa contrato `Auditable`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public objetivo` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public tipoExecucao` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public organizacao` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public entregas` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public indicadores` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public organizacoes` | `sem parametros` | `\Illuminate\Database\Eloquent\Relations\BelongsToMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getSatisfacaoColor` | `sem parametros` | `string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getSatisfacaoTextClass` | `sem parametros` | `string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isAtrasado` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public calcularProgressoEntregas` | `sem parametros` | `float` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorTipo` | `$query, string $tipo` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorStatus` | `$query, string $status` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeAtrasados` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeEmAndamento` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getResponsaveisAttribute` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\ActionPlan\TipoExecucao`

- Fonte: verificado no codigo em `app/Models/ActionPlan/TipoExecucao.php`.
- Tabela declarada: `tab_tipo_execucao` com PK `cod_tipo_execucao`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public planosAcao` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isAcao` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isIniciativa` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isProjeto` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\Lead`

- Fonte: verificado no codigo em `app/Models/Lead.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public getStatusLabelAttribute` | `sem parametros` | `string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getStatusBadgeClassAttribute` | `sem parametros` | `string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\Organization`

- Fonte: verificado no codigo em `app/Models/Organization.php`.
- Tabela declarada: `tab_organizacoes` com PK `cod_organizacao`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public pai` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public filhas` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public usuarios` | `sem parametros` | `BelongsToMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public planosAcao` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public identidadeEstrategica` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public valores` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getDescendantsAndSelfIds` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isRaiz` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getNivelHierarquico` | `int $nivel = 0` | `int` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeRaiz` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeFilhasDe` | `$query, string $codOrganizacaoPai` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\PerfilAcesso`

- Fonte: verificado no codigo em `app/Models/PerfilAcesso.php`.
- Tabela declarada: `tab_perfil_acesso` com PK `cod_perfil`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public usuarios` | `sem parametros` | `BelongsToMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isSuperAdmin` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isAdminUnidade` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isGestor` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\PerformanceIndicators\EvolucaoIndicador`

- Fonte: verificado no codigo em `app/Models/PerformanceIndicators/EvolucaoIndicador.php`.
- Tabela declarada: `tab_evolucao_indicador` com PK `cod_evolucao_indicador`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public indicador` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public arquivos` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public calcularAtingimento` | `sem parametros` | `float` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getNomeMes` | `sem parametros` | `string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorAno` | `$query, int $ano` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorPeriodo` | `$query, int $ano, int $mes` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeAtualizadas` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeNaoAtualizadas` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\PerformanceIndicators\Indicador`

- Fonte: verificado no codigo em `app/Models/PerformanceIndicators/Indicador.php`.
- Tabela declarada: `tab_indicador` com PK `cod_indicador`.
- Auditoria: implementa contrato `Auditable`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public planoDeAcao` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public objetivo` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public evolucoes` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public linhaBase` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public metasPorAno` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public organizacoes` | `sem parametros` | `BelongsToMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getUltimaEvolucao` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public calcularAtingimento` | `int $ano = null, int $mes = null` | `float` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected calcularPercentualPorTipo` | `float $realizado, float $previsto` | `float` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getCorFarol` | `int $ano = null` | `?string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeDeObjetivo` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeDePlano` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorPeriodo` | `$query, string $periodo` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\PerformanceIndicators\LinhaBaseIndicador`

- Fonte: verificado no codigo em `app/Models/PerformanceIndicators/LinhaBaseIndicador.php`.
- Tabela declarada: `tab_linha_base_indicador` com PK `cod_linha_base`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public indicador` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorAno` | `$query, int $ano` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\PerformanceIndicators\MetaPorAno`

- Fonte: verificado no codigo em `app/Models/PerformanceIndicators/MetaPorAno.php`.
- Tabela declarada: `tab_meta_por_ano` com PK `cod_meta_por_ano`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public indicador` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorAno` | `$query, int $ano` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeAnoAtual` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\Reports\RelatorioAgendado`

- Fonte: verificado no codigo em `app/Models/Reports/RelatorioAgendado.php`.
- Tabela declarada: `tab_relatorios_agendados` com PK `cod_agendamento`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public user` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\Reports\RelatorioGerado`

- Fonte: verificado no codigo em `app/Models/Reports/RelatorioGerado.php`.
- Tabela declarada: `tab_relatorios_gerados` com PK `cod_relatorio_gerado`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public user` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\RiskManagement\Risco`

- Fonte: verificado no codigo em `app/Models/RiskManagement/Risco.php`.
- Tabela declarada: `tab_risco` com PK `cod_risco`.
- Auditoria: implementa contrato `Auditable`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public pei` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public organizacao` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public responsavel` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public objetivos` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public mitigacoes` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public ocorrencias` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeAtivos` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeCriticos` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorCategoria` | `$query, $categoria` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorNivel` | `$query, $nivelMin, $nivelMax = null` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public calcularNivelRisco` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getNivelRiscoLabel` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getNivelRiscoCor` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getNivelRiscoBadgeClass` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isCritico` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public temPlanoMitigacao` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public temOcorrencia` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getProbabilidadeLabel` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getImpactoLabel` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\RiskManagement\RiscoMitigacao`

- Fonte: verificado no codigo em `app/Models/RiskManagement/RiscoMitigacao.php`.
- Tabela declarada: `tab_risco_mitigacao` com PK `cod_mitigacao`.
- Auditoria: implementa contrato `Auditable`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public risco` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public responsavel` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeAtrasados` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorStatus` | `$query, $status` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorTipo` | `$query, $tipo` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isAtrasado` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isConcluido` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getDiasRestantes` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getStatusBadgeClass` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\RiskManagement\RiscoObjetivo`

- Fonte: verificado no codigo em `app/Models/RiskManagement/RiscoObjetivo.php`.
- Tabela declarada: `tab_risco_objetivo` com PK `cod_risco_objetivo`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public risco` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public objetivo` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\RiskManagement\RiscoOcorrencia`

- Fonte: verificado no codigo em `app/Models/RiskManagement/RiscoOcorrencia.php`.
- Tabela declarada: `tab_risco_ocorrencia` com PK `cod_ocorrencia`.
- Auditoria: implementa contrato `Auditable`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public risco` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeRecentes` | `$query, $dias = 30` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorPeriodo` | `$query, $dataInicio, $dataFim` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getImpactoRealLabel` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getImpactoRealCor` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isRecente` | `$dias = 7` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\StrategicAlert`

- Fonte: verificado no codigo em `app/Models/StrategicAlert.php`.
- Tabela declarada: `strategic_alerts`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public user` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeUnread` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public markAsRead` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\StrategicPlanning\AnaliseAmbiental`

- Fonte: verificado no codigo em `app/Models/StrategicPlanning/AnaliseAmbiental.php`.
- Tabela declarada: `tab_analise_ambiental` com PK `cod_analise`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public pei` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public organizacao` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeSwot` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePestel` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeCategoria` | `$query, string $categoria` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeOrdenado` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\StrategicPlanning\Arquivo`

- Fonte: verificado no codigo em `app/Models/StrategicPlanning/Arquivo.php`.
- Tabela declarada: `tab_arquivos` com PK `cod_arquivo`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public evolucaoIndicador` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getExtensao` | `sem parametros` | `string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isImagem` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isPdf` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorTipo` | `$query, string $tipo` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeRecentes` | `$query, int $dias = 30` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\StrategicPlanning\AtividadeCadeiaValor`

- Fonte: verificado no codigo em `app/Models/StrategicPlanning/AtividadeCadeiaValor.php`.
- Tabela declarada: `tab_atividade_cadeia_valor` com PK `cod_atividade_cadeia_valor`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public pei` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public perspectiva` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public processos` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorPei` | `$query, string $codPei` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorPerspectiva` | `$query, string $codPerspectiva` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\StrategicPlanning\FuturoAlmejado`

- Fonte: verificado no codigo em `app/Models/StrategicPlanning/FuturoAlmejado.php`.
- Tabela declarada: `tab_futuro_almejado_objetivo` com PK `cod_futuro_almejado`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public objetivo` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\StrategicPlanning\GrauSatisfacao`

- Fonte: verificado no codigo em `app/Models/StrategicPlanning/GrauSatisfacao.php`.
- Tabela declarada: `tab_grau_satisfacao` com PK `cod_grau_satisfacao`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public pei` | `sem parametros` | `\Illuminate\Database\Eloquent\Relations\BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeOrdenadoPorValor` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\StrategicPlanning\MissaoVisaoValores`

- Fonte: verificado no codigo em `app/Models/StrategicPlanning/MissaoVisaoValores.php`.
- Tabela declarada: `tab_missao_visao_valores` com PK `cod_missao_visao_valores`.
- Auditoria: implementa contrato `Auditable`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public pei` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public organizacao` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\StrategicPlanning\Objetivo`

- Fonte: verificado no codigo em `app/Models/StrategicPlanning/Objetivo.php`.
- Tabela declarada: `tab_objetivo` com PK `cod_objetivo`.
- Auditoria: implementa contrato `Auditable`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public perspectiva` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public planosAcao` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public indicadores` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public futuroAlmejado` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public comentarios` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public calcularAtingimentoConsolidado` | `int $ano = null, int $mes = null` | `float` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getCorFarolConsolidado` | `int $ano = null, int $mes = null` | `?string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getResumoDesempenho` | `int $ano = null` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeOrdenadoPorNivel` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorPerspectiva` | `$query, string $codPerspectiva` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\StrategicPlanning\ObjetivoComentario`

- Fonte: verificado no codigo em `app/Models/StrategicPlanning/ObjetivoComentario.php`.
- Tabela declarada: `tab_objetivo_comentarios` com PK `cod_comentario`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public user` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public objetivo` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\StrategicPlanning\PEI`

- Fonte: verificado no codigo em `app/Models/StrategicPlanning/PEI.php`.
- Tabela declarada: `tab_pei` com PK `cod_pei`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public perspectivas` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public identidadeEstrategica` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public valores` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atividadesCadeiaValor` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isAtivo` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeAtivos` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeFuturos` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePassados` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\StrategicPlanning\Perspectiva`

- Fonte: verificado no codigo em `app/Models/StrategicPlanning/Perspectiva.php`.
- Tabela declarada: `tab_perspectiva` com PK `cod_perspectiva`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public pei` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public objetivos` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atividades` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getIndicadoresAttribute` | `sem parametros` | `Collection` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public calcularDesempenho` | `?int $ano = null` | `float` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected calcularDesempenhoIndicadores` | `int $ano` | `float` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected calcularDesempenhoPlanos` | `int $ano` | `float` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeOrdenadoPorNivel` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\StrategicPlanning\ProcessoAtividadeCadeiaValor`

- Fonte: verificado no codigo em `app/Models/StrategicPlanning/ProcessoAtividadeCadeiaValor.php`.
- Tabela declarada: `tab_processos_atividade_cadeia_valor` com PK `cod_processo_atividade_cadeia_valor`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public atividadeCadeiaValor` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorAtividade` | `$query, string $codAtividade` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\StrategicPlanning\TemaNorteador`

- Fonte: verificado no codigo em `app/Models/StrategicPlanning/TemaNorteador.php`.
- Tabela declarada: `strategic_planning.tab_tema_norteador` com PK `cod_tema_norteador`.
- Auditoria: implementa contrato `Auditable`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public pei` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public organizacao` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\StrategicPlanning\Valor`

- Fonte: verificado no codigo em `app/Models/StrategicPlanning/Valor.php`.
- Tabela declarada: `tab_valores` com PK `cod_valor`.
- Auditoria: implementa contrato `Auditable`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public pei` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public organizacao` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\SystemSetting`

- Fonte: verificado no codigo em `app/Models/SystemSetting.php`.
- Tabela declarada: `system_settings`.
- Metodos declarados: nenhum metodo proprio identificado.

### `App\Models\TabAudit`

- Fonte: verificado no codigo em `app/Models/TabAudit.php`.
- Tabela declarada: `tab_audit` com PK `id`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public user` | `sem parametros` | `BelongsTo` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isExpirado` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getDiferencas` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorTabela` | `$query, string $tabela` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorRegistro` | `$query, string $tableId` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorUsuario` | `$query, string $userId` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorAcao` | `$query, string $acao` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopePorColuna` | `$query, string $coluna` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeRecentes` | `$query, int $dias = 7` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeNaoExpiradas` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeExpiradas` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\TabStatus`

- Fonte: verificado no codigo em `app/Models/TabStatus.php`.
- Tabela declarada: `tab_status` com PK `cod_status`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public scopeBuscarPorDescricao` | `$query, string $termo` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeOrdenadoPorDescricao` | `$query, string $direcao = 'asc'` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Models\User`

- Fonte: verificado no codigo em `app/Models/User.php`.
- Tabela declarada: `users` com PK `id`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `protected casts` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public organizacoes` | `sem parametros` | `BelongsToMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public perfisAcesso` | `sem parametros` | `BelongsToMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public acoes` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public audits` | `sem parametros` | `HasMany` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isSuperAdmin` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isAtivo` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public deveTrocarSenha` | `sem parametros` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public temPermissaoOrganizacao` | `Organization $org` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public perfisNaOrganizacao` | `Organization $org` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isGestorResponsavel` | `string $codPlanoDeAcao` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public isGestorSubstituto` | `string $codPlanoDeAcao` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeAtivos` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeAdministradores` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public scopeDevemTrocarSenha` | `$query` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

## Inventario de Componentes Livewire

Assinaturas extraidas do codigo. Os modulos criticos lidos semanticamente estao resumidos acima.

### `App\Livewire\ActionPlan\AtribuirResponsaveis`

- Fonte: verificado no codigo em `app/Livewire/ActionPlan/AtribuirResponsaveis.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `$planoId` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarDados` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public adicionar` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public remover` | `$pivotId` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\ActionPlan\DetalharPlano`

- Fonte: verificado no codigo em `app/Livewire/ActionPlan/DetalharPlano.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\ActionPlan\GerenciarEntregas`

- Fonte: verificado no codigo em `app/Livewire/ActionPlan/GerenciarEntregas.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `$planoId` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarDados` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public edit` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public save` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetForm` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public redistribuirPesos` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\ActionPlan\ListarPlanos`

- Fonte: verificado no codigo em `app/Livewire/ActionPlan/ListarPlanos.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeSuccessModal` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public pedirAjudaIA` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public aplicarSugestao` | `$nome, $justificativa = null` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarAno` | `$ano` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarPEI` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private carregarPEI` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarObjetivos` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public edit` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public save` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public confirmDelete` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetForm` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\Admin\ConfiguracaoSistema`

- Fonte: verificado no codigo em `app/Livewire/Admin/ConfiguracaoSistema.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public testConnection` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public saveAiSettings` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\Audit\DetalharLog`

- Fonte: verificado no codigo em `app/Livewire/Audit/DetalharLog.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\Audit\ListarLogs`

- Fonte: verificado no codigo em `app/Livewire/Audit/ListarLogs.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updated` | `$propertyName` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public verDetalhes` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected getQuery` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public exportar` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\Auth\TrocarSenha`

- Fonte: verificado no codigo em `app/Livewire/Auth/TrocarSenha.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `protected rules` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public trocarSenha` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public logout` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\Dashboard\Index`

- Fonte: verificado no codigo em `app/Livewire/Dashboard/Index.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarAno` | `$ano` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarPEI` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarPEI` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public generateAiSummary` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private carregarNomeOrganizacao` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarDadosGraficos` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private getStats` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private getMinhasEntregas` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private getMinhasEntregasAgrupadas` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private getComentariosRecentes` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private getChartBSC` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private getChartRiscosNivel` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private getChartPlanos` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private getChartEvolucao` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private getCorAtingimento` | `$percentual` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getMentorStatus` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\Dashboard\PeiChecklist`

- Fonte: verificado no codigo em `app/Livewire/Dashboard/PeiChecklist.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `PeiGuidanceService $service` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public dismiss` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public refreshGuidance` | `$id = null, PeiGuidanceService $service = null` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\Deliverables\DeliverablesBoard`

- Fonte: verificado no codigo em `app/Livewire/Deliverables/DeliverablesBoard.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `?string $planoId = null` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarListasEstrategicas` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updatedPerspectivaId` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updatedObjetivoId` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public mudarPlano` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected getEntregas` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected getEntregasPorStatus` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected getLabels` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected getUsuarios` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected calcularProgresso` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public setView` | `string $view` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public limparFiltros` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public toggleArquivados` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public toggleLixeira` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public calendarioAnterior` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public calendarioProximo` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public calendarioHoje` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public calendarioIrPara` | `int $mes, int $ano` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public timelineAnterior` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public timelineProximo` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public timelineHoje` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public timelineZoomIn` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public timelineZoomOut` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public timelineDefinirPeriodo` | `string $inicio, string $fim` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarPrazoEntrega` | `string $entregaId, string $novoPrazo` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public openQuickAdd` | `string $status = 'Não Iniciado'` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeQuickAdd` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public criarRapido` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public openEditModal` | `?string $entregaId = null` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeEditModal` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected resetEditForm` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public salvarEntrega` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public openDetails` | `string $entregaId` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeDetails` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarTitulo` | `string $entregaId, string $titulo` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarStatus` | `string $entregaId, string $status` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarPrioridade` | `string $entregaId, string $prioridade` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarResponsaveis` | `string $entregaId, array $userIds` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarPrazo` | `string $entregaId, ?string $prazo` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public reordenarEntregas` | `array $ordem` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public moverParaStatus` | `string $entregaId, string $novoStatus, int $novaPosicao` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public arquivar` | `string $entregaId` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public desarquivar` | `string $entregaId` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public confirmDeleteEntrega` | `string $entregaId, bool $isPermanent = false` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public excluir` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public restaurar` | `string $entregaId` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public excluirPermanente` | `string $entregaId` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public openLabelsModal` | `string $entregaId` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeLabelsModal` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public toggleLabel` | `string $entregaId, string $labelId` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public criarLabel` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public setRespondendo` | `?string $comentarioId` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public adicionarComentario` | `$entregaId, $conteudo = null, $comentarioPaiId = null` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public excluirComentario` | `string $comentarioId` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updatedAnexosUpload` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public excluirAnexo` | `string $anexoId` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeSuccessModal` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public poll` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\LeadsTable`

- Fonte: verificado no codigo em `app/Livewire/LeadsTable.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `protected rules` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updatingSearch` | `string $value` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updatingStatus` | `string $value` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetFilters` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getStatusesProperty` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getStatusOptionsProperty` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected baseQuery` | `sem parametros` | `Builder` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected paginatedLeads` | `sem parametros` | `LengthAwarePaginator` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public edit` | `int $leadId` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeFormModal` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public save` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public confirmDelete` | `int $leadId` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public cancelDelete` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public exportCsv` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected resetForm` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected notify` | `string $message, string $style = 'success'` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected applySearchFilter` | `Builder $query, string $search` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\Organization\DetalharOrganizacao`

- Fonte: verificado no codigo em `app/Livewire/Organization/DetalharOrganizacao.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\Organization\ListarOrganizacoes`

- Fonte: verificado no codigo em `app/Livewire/Organization/ListarOrganizacoes.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public pedirAjudaIA` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public aplicarSugestaoSigla` | `$sigla` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected rules` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updatingSearch` | `string $value` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetFilters` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getOrganizacoesPaiProperty` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected baseQuery` | `sem parametros` | `Builder` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected paginatedOrganizacoes` | `sem parametros` | `LengthAwarePaginator` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public edit` | `string $id` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeFormModal` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public save` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeSuccessModal` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeErrorModal` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public confirmDelete` | `string $id` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public cancelDelete` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected resetForm` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected notify` | `string $message, string $style = 'success'` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected applySearchFilter` | `Builder $query, string $search` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\PerformanceIndicators\DetalharIndicador`

- Fonte: verificado no codigo em `app/Livewire/PerformanceIndicators/DetalharIndicador.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public atualizarAno` | `$ano` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public mount` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected carregarAnosDisponiveis` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updatedAnoFiltro` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected prepareChartData` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\PerformanceIndicators\LancarEvolucao`

- Fonte: verificado no codigo em `app/Livewire/PerformanceIndicators/LancarEvolucao.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public atualizarAno` | `$ano` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public mount` | `$indicadorId` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updatedAno` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updatedMes` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarPeriodo` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarHistorico` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public salvar` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public excluirArquivo` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\PerformanceIndicators\ListarIndicadores`

- Fonte: verificado no codigo em `app/Livewire/PerformanceIndicators/ListarIndicadores.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarPEI` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarListasAuxiliares` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `\App\Services\PeiGuidanceService $service` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public edit` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public save` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public abrirMetas` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public salvarMeta` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public excluirMeta` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public abrirLinhaBase` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public salvarLinhaBase` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public excluirLinhaBase` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public confirmDelete` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetForm` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeSuccessModal` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeErrorModal` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public pedirAjudaIA` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public aplicarSugestao` | `$nome, $desc, $unidade, $formula` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\Profile\UpdateThemeColorForm`

- Fonte: verificado no codigo em `app/Livewire/Profile/UpdateThemeColorForm.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updated` | `$propertyName` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updateThemeColor` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\PublicNavbar`

- Fonte: verificado no codigo em `app/Livewire/PublicNavbar.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\Reports\AgendarRelatorio`

- Fonte: verificado no codigo em `app/Livewire/Reports/AgendarRelatorio.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public carregar` | `$tipo, $filtros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public salvar` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\Reports\GerenciarAgendamentos`

- Fonte: verificado no codigo em `app/Livewire/Reports/GerenciarAgendamentos.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public toggleStatus` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\Reports\HistoricoRelatorios`

- Fonte: verificado no codigo em `app/Livewire/Reports/HistoricoRelatorios.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public download` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\Reports\ListarRelatorios`

- Fonte: verificado no codigo em `app/Livewire/Reports/ListarRelatorios.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public atualizarAno` | `$ano` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarPEI` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private carregarPEI` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private carregarIdentidade` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private carregarPerspectivas` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updatedOrganizacaoId` | `$value` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public setOrganizacao` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getQueryParamsProperty` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public gerarInsightIA` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public download` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\RiskManagement\GerenciarMitigacoes`

- Fonte: verificado no codigo em `app/Livewire/RiskManagement/GerenciarMitigacoes.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `$riscoId` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarDados` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public edit` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public save` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetForm` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\RiskManagement\ListarRiscos`

- Fonte: verificado no codigo em `app/Livewire/RiskManagement/ListarRiscos.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeSuccessModal` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeErrorModal` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public pedirAjudaIA` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public aplicarSugestao` | `$titulo, $categoria, $descricao` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarPEI` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private carregarPEI` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarListasAuxiliares` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updatingSearch` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public edit` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public save` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public confirmDelete` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetForm` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\RiskManagement\MatrizRiscos`

- Fonte: verificado no codigo em `app/Livewire/RiskManagement/MatrizRiscos.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarMatriz` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarMatriz` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\RiskManagement\RegistrarOcorrencias`

- Fonte: verificado no codigo em `app/Livewire/RiskManagement/RegistrarOcorrencias.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `$riscoId` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarDados` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public edit` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public save` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetForm` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\Shared\SeletorAno`

- Fonte: verificado no codigo em `app/Livewire/Shared/SeletorAno.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarPeiId` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarAnos` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public selecionar` | `$ano` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private trocarPeiSilencioso` | `PEI $pei` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\Shared\SeletorOrganizacao`

- Fonte: verificado no codigo em `app/Livewire/Shared/SeletorOrganizacao.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarOrganizacoes` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public selecionar` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private atualizarSessao` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\Shared\SeletorPei`

- Fonte: verificado no codigo em `app/Livewire/Shared/SeletorPei.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarPEIs` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private definirSessao` | `PEI $pei` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public selecionar` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\Shared\StrategicAlertsBell`

- Fonte: verificado no codigo em `app/Livewire/Shared/StrategicAlertsBell.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public refreshCount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public markAllAsRead` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getRecentAlerts` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\StrategicPlanning\AnalisePESTEL`

- Fonte: verificado no codigo em `app/Livewire/StrategicPlanning/AnalisePESTEL.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public pedirAjudaIA` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public adicionarSugerido` | `$categoria, $item` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarPEI` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private carregarPEI` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarDados` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `$categoria` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public edit` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public save` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetForm` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\StrategicPlanning\AnaliseSWOT`

- Fonte: verificado no codigo em `app/Livewire/StrategicPlanning/AnaliseSWOT.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public pedirAjudaIA` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public adicionarSugerido` | `$categoria, $item` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarPEI` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private carregarPEI` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarDados` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public toggleModoVisualizacao` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `$categoria` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public edit` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public save` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetForm` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\StrategicPlanning\DetalharGrauSatisfacao`

- Fonte: verificado no codigo em `app/Livewire/StrategicPlanning/DetalharGrauSatisfacao.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\StrategicPlanning\DetalharIdentidade`

- Fonte: verificado no codigo em `app/Livewire/StrategicPlanning/DetalharIdentidade.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\StrategicPlanning\DetalharObjetivo`

- Fonte: verificado no codigo em `app/Livewire/StrategicPlanning/DetalharObjetivo.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarObjetivo` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private getCorFarolManual` | `$val` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public postarComentario` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public removerComentario` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\StrategicPlanning\DetalharPei`

- Fonte: verificado no codigo em `app/Livewire/StrategicPlanning/DetalharPei.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarEstatisticas` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\StrategicPlanning\DetalharPerspectiva`

- Fonte: verificado no codigo em `app/Livewire/StrategicPlanning/DetalharPerspectiva.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\StrategicPlanning\DetalharValor`

- Fonte: verificado no codigo em `app/Livewire/StrategicPlanning/DetalharValor.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\StrategicPlanning\GerenciarFuturoAlmejado`

- Fonte: verificado no codigo em `app/Livewire/StrategicPlanning/GerenciarFuturoAlmejado.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `$objetivoId` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarFuturos` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public edit` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public save` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetForm` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\StrategicPlanning\GerenciarTemasNorteadores`

- Fonte: verificado no codigo em `app/Livewire/StrategicPlanning/GerenciarTemasNorteadores.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public pedirAjudaIA` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public aplicarSugestao` | `$nome` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarPEI` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private carregarPEI` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updatingSearch` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public edit` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public save` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public confirmDelete` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetForm` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\StrategicPlanning\ListarGrausSatisfacao`

- Fonte: verificado no codigo em `app/Livewire/StrategicPlanning/ListarGrausSatisfacao.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeSuccessModal` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeErrorModal` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public pedirAjudaIA` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public aplicarSugestao` | `$nome, $cor, $min, $max` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected rules` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updatingSearch` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public openModal` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeModal` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetForm` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public save` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public edit` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public confirmDelete` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public cancelDelete` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\StrategicPlanning\ListarObjetivos`

- Fonte: verificado no codigo em `app/Livewire/StrategicPlanning/ListarObjetivos.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public closeSuccessModal` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeErrorModal` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updatedNomObjetivo` | `$value` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public auditSmart` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public pedirAjudaIA` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public aplicarSugestao` | `$nome, $descricao, $ordem` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarPEI` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private carregarPEI` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarPerspectivas` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `$perspectivaId = null, \App\Services\PeiGuidanceService $service = null` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public edit` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updatedCodPerspectiva` | `$value` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public save` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public confirmDelete` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetForm` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\StrategicPlanning\ListarPeis`

- Fonte: verificado no codigo em `app/Livewire/StrategicPlanning/ListarPeis.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeSuccessModal` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeErrorModal` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updatingSearch` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetFilters` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public edit` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public save` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public confirmDelete` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetForm` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\StrategicPlanning\ListarPerspectivas`

- Fonte: verificado no codigo em `app/Livewire/StrategicPlanning/ListarPerspectivas.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeSuccessModal` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeErrorModal` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public pedirAjudaIA` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public aplicarSugestao` | `$nome, $ordem` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public testarNotificacao` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarPEI` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private carregarPEI` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarPerspectivas` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `\App\Services\PeiGuidanceService $service` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public edit` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public save` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public confirmDelete` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetForm` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\StrategicPlanning\ListarValores`

- Fonte: verificado no codigo em `app/Livewire/StrategicPlanning/ListarValores.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarPEI` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private carregarPEI` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarValores` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public edit` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public save` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetForm` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\StrategicPlanning\MapaEstrategico`

- Fonte: verificado no codigo em `app/Livewire/StrategicPlanning/MapaEstrategico.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public setViewMode` | `$mode` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarPEI` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private carregarPEI` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarMapa` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarIdentidadeEstrategica` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getCorPorPercentual` | `$percentual` | `string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getCoresPerspectiva` | `$nivel` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public abrirMemoriaCalculo` | `$index` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public fecharMemoriaCalculo` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\StrategicPlanning\MissaoVisao`

- Fonte: verificado no codigo em `app/Livewire/StrategicPlanning/MissaoVisao.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public pedirAjudaIA` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public aplicarIdentidade` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public adicionarValorSugerido` | `$nome, $descricao` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarPEI` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private carregarPEI` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarOrganizacao` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetarDados` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public carregarDados` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public habilitarEdicao` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public cancelar` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public salvar` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public adicionarValor` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public confirmDeleteValor` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public removerValor` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public editarValor` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarValor` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public cancelarEdicaoValor` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\UserManagement\DetalharUsuario`

- Fonte: verificado no codigo em `app/Livewire/UserManagement/DetalharUsuario.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public mount` | `$id` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Livewire\UserManagement\ListarUsuarios`

- Fonte: verificado no codigo em `app/Livewire/UserManagement/ListarUsuarios.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `protected rules` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public updatingSearch` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public resetFilters` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getOrganizacoesOptionsProperty` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getPerfisOptionsProperty` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected baseQuery` | `sem parametros` | `Builder` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected paginatedUsuarios` | `sem parametros` | `LengthAwarePaginator` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public edit` | `string $id` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public adicionarVinculo` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public removerVinculo` | `$index` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public closeFormModal` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public save` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public confirmDelete` | `string $id` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public cancelDelete` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public render` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected resetForm` | `sem parametros` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected notify` | `string $message, string $style = 'success'` | `void` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

## Inventario de Controllers

### `App\Http\Controllers\Controller`

- Fonte: verificado no codigo em `app/Http/Controllers/Controller.php`.
- Metodos declarados: nenhum metodo proprio identificado.

### `App\Http\Controllers\Reports\RelatorioController`

- Fonte: verificado no codigo em `app/Http/Controllers/Reports/RelatorioController.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public __construct` | `ReportGenerationService $reportService` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public executivo` | `Request $request, $organizacaoId = null` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public identidade` | `Request $request, $organizacaoId` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public objetivosPdf` | `Request $request` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public objetivosExcel` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public indicadoresPdf` | `Request $request, $organizacaoId = null` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public indicadoresExcel` | `$organizacaoId = null` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public planosPdf` | `Request $request` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public planosExcel` | `Request $request` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public riscosPdf` | `Request $request` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public riscosExcel` | `Request $request` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public integrado` | `Request $request, $organizacaoId = null` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

## Inventario de Services

### `App\Services\AI\AiProviderInterface`

- Fonte: verificado no codigo em `app/Services/AI/AiProviderInterface.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public suggest` | `string $prompt, string $context = ''` | `?string;` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public testConnection` | `sem parametros` | `array;` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public analyzeSmart` | `string $type, string $title, string $description = ''` | `?string;` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public summarizeStrategy` | `array $stats, string $orgName` | `?string;` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public analyzeTrends` | `array $indicatorData, string $orgName` | `?string;` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Services\AI\AiServiceFactory`

- Fonte: verificado no codigo em `app/Services/AI/AiServiceFactory.php`.
- Metodos declarados: nenhum metodo proprio identificado.

### `App\Services\AI\GeminiProvider`

- Fonte: verificado no codigo em `app/Services/AI/GeminiProvider.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public __construct` | `?string $apiKey = null` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public suggest` | `string $prompt, string $context = ''` | `?string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public testConnection` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public analyzeSmart` | `string $type, string $title, string $description = ''` | `?string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public summarizeStrategy` | `array $stats, string $orgName` | `?string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public analyzeTrends` | `array $indicatorData, string $orgName` | `?string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Services\AI\OpenAiProvider`

- Fonte: verificado no codigo em `app/Services/AI/OpenAiProvider.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public __construct` | `?string $apiKey = null` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public suggest` | `string $prompt, string $context = ''` | `?string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public testConnection` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public analyzeSmart` | `string $type, string $title, string $description = ''` | `?string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public summarizeStrategy` | `array $stats, string $orgName` | `?string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public analyzeTrends` | `array $indicatorData, string $orgName` | `?string` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Services\IndicadorCalculoService`

- Fonte: verificado no codigo em `app/Services/IndicadorCalculoService.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public calcularProgressoPlano` | `PlanoDeAcao $plano, bool $apenasRaiz = true` | `float` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected calcularMediaSimples` | `Collection $entregas` | `float` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected calcularMediaPonderada` | `Collection $entregas, float $somaPesos` | `float` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected getProgressoEntrega` | `Entrega $entrega` | `float` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected calcularProgressoSubEntregas` | `Entrega $entrega` | `float` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `protected getEntregasValidas` | `PlanoDeAcao $plano, bool $apenasRaiz = true` | `Collection` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarIndicadorAutomatico` | `Indicador $indicador` | `?EvolucaoIndicador` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public atualizarIndicadoresDoPlano` | `PlanoDeAcao $plano` | `int` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public validarPesosPlano` | `PlanoDeAcao $plano` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public redistribuirPesosIguais` | `PlanoDeAcao $plano` | `int` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public getEstatisticasPlano` | `PlanoDeAcao $plano` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public simularCalculo` | `PlanoDeAcao $plano` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public calcularProgressoPlanoNoAno` | `PlanoDeAcao $plano, int $ano` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public calcularAtingimentoPerspectiva` | `\App\Models\StrategicPlanning\Perspectiva $perspectiva, int $ano` | `float` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public calcularAtingimentoObjetivo` | `\App\Models\StrategicPlanning\Objetivo $objetivo, int $ano` | `float` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Services\NotificationService`

- Fonte: verificado no codigo em `app/Services/NotificationService.php`.
- Metodos declarados: nenhum metodo proprio identificado.

### `App\Services\PeiGuidanceService`

- Fonte: verificado no codigo em `app/Services/PeiGuidanceService.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public analyzeCompleteness` | `?string $peiId = null` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private buildResponse` | `array $phases, string $currentPhaseKey, int $progress, $pei, string $msg, string $route, string $label` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private getNextStepInfo` | `string $currentPhase` | `?array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private getEmptyPhasesStructure` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Services\Reports\ReportGenerationService`

- Fonte: verificado no codigo em `app/Services/Reports/ReportGenerationService.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public __construct` | `\App\Services\IndicadorCalculoService $calculoService` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public generateExecutivo` | `$organizacaoId, $ano, $periodo, $perspectivaId = null` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public generateIdentidade` | `$organizacaoId, $ano = null` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public __construct` | `$graus` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public __invoke` | `$percentual` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public generateObjetivos` | `$organizacaoId = null, $ano = null, $perspectivaId = null` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public generateIndicadores` | `$organizacaoId = null, $ano = null, $periodo = null` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public generatePlanos` | `$organizacaoId = null, $ano = null` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public generateRiscos` | `$organizacaoId = null` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public generateIntegrado` | `$organizacaoId, $ano, $periodo, $includeAi = true` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

## Inventario de Policies

### `App\Policies\IndicadorPolicy`

- Fonte: verificado no codigo em `app/Policies/IndicadorPolicy.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public viewAny` | `User $user` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public view` | `User $user, Indicador $indicador` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `User $user` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public update` | `User $user, Indicador $indicador` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `User $user, Indicador $indicador` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Policies\OrganizationPolicy`

- Fonte: verificado no codigo em `app/Policies/OrganizationPolicy.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public viewAny` | `User $user` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public view` | `User $user, Organization $organization` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `User $user` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public update` | `User $user, Organization $organization` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `User $user, Organization $organization` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public restore` | `User $user, Organization $organization` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public forceDelete` | `User $user, Organization $organization` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Policies\PlanoDeAcaoPolicy`

- Fonte: verificado no codigo em `app/Policies/PlanoDeAcaoPolicy.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public viewAny` | `User $user` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public view` | `User $user, PlanoDeAcao $planoDeAcao` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `User $user` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public update` | `User $user, PlanoDeAcao $planoDeAcao` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `User $user, PlanoDeAcao $planoDeAcao` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public restore` | `User $user, PlanoDeAcao $planoDeAcao` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public forceDelete` | `User $user, PlanoDeAcao $planoDeAcao` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Policies\RiscoPolicy`

- Fonte: verificado no codigo em `app/Policies/RiscoPolicy.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public viewAny` | `User $user` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public view` | `User $user, Risco $risco` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `User $user` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public update` | `User $user, Risco $risco` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `User $user, Risco $risco` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Policies\UserPolicy`

- Fonte: verificado no codigo em `app/Policies/UserPolicy.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public viewAny` | `User $user` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public view` | `User $user, User $model` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public create` | `User $user` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public update` | `User $user, User $model` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public delete` | `User $user, User $model` | `bool` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

## Inventario de Middlewares

### `App\Http\Middleware\CheckPasswordChange`

- Fonte: verificado no codigo em `app/Http/Middleware/CheckPasswordChange.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public handle` | `Request $request, Closure $next` | `Response` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

## Inventario de Commands Artisan

### `App\Console\Commands\FixPlanosEntregasDates`

- Fonte: verificado no codigo em `app/Console/Commands/FixPlanosEntregasDates.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public handle` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Console\Commands\GenerateCsvTemplates`

- Fonte: verificado no codigo em `app/Console/Commands/GenerateCsvTemplates.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public handle` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private generatePair` | `$path, $prefix, $columnsMap, $guideMap` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Console\Commands\ProcessScheduledReports`

- Fonte: verificado no codigo em `app/Console/Commands/ProcessScheduledReports.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public handle` | `ReportGenerationService $reportService` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `private atualizarProximaExecucao` | `RelatorioAgendado $agendamento` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Console\Commands\SeedMIDREnvironment`

- Fonte: verificado no codigo em `app/Console/Commands/SeedMIDREnvironment.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public handle` | `sem parametros` | `int` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

## Inventario de Exports

### `App\Exports\IndicadoresExport`

- Fonte: verificado no codigo em `app/Exports/IndicadoresExport.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public __construct` | `$organizacaoId` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public collection` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public headings` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public map` | `$indicador` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Exports\ObjetivosExport`

- Fonte: verificado no codigo em `app/Exports/ObjetivosExport.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public __construct` | `$codPei` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public collection` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public headings` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public map` | `$objetivo` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Exports\PlanosExport`

- Fonte: verificado no codigo em `app/Exports/PlanosExport.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public __construct` | `$organizacaoId, $ano = null` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public collection` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public headings` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public map` | `$plano` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

### `App\Exports\RiscosExport`

- Fonte: verificado no codigo em `app/Exports/RiscosExport.php`.

| Metodo | Entrada | Saida declarada | Nivel de confianca |
|---|---|---|---|
| `public __construct` | `$organizacaoId` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public collection` | `sem parametros` | `nao declarado` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public headings` | `sem parametros` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |
| `public map` | `$risco` | `array` | Assinatura extraida do codigo; semantica exige leitura do corpo para alteracao. |

## Auditoria, eventos e efeitos colaterais

- Verificado no codigo: `config/audit.php` habilita `owen-it/laravel-auditing`, driver database, tabela `audits`, eventos `created`, `updated`, `deleted`, `restored`, sem fila e sem auditoria de console.
- Verificado no codigo: models de dominio como `PlanoDeAcao`, `Indicador`, `Risco`, `RiscoMitigacao`, `RiscoOcorrencia`, `MissaoVisaoValores`, `Objetivo`, `TemaNorteador` e `Valor` implementam `Auditable` no inventario extraido.
- Verificado no codigo: `AppServiceProvider` registra `EntregaObserver` para `Entrega`; o observer recalcula indicadores quando entregas sao criadas, atualizadas, deletadas ou restauradas.

## Exceptions e resiliencia

- Verificado no codigo: `bootstrap/app.php` personaliza `AuthenticationException`, `TokenMismatchException`, 403, 404, `QueryException` PostgreSQL 23503 e Throwable generico em producao para usuario nao autenticado.
- Para CSRF 419, invalida sessao, regenera token, efetua logout se autenticado e responde JSON para AJAX/Livewire com `session_expired` e `redirect`.
- Para FK 23503, retorna mensagem amigavel e status 422 em JSON ou `back()->with(error)`.

## Frontend e UX real

- Verificado no codigo: frontend e Blade/Livewire com layouts `app`, `guest` e `public`.
- Verificado nas dependencias: Bootstrap 5, Bootstrap Icons, Alpine.js, plugins mask/focus, SortableJS, Sass e Vite.
- Verificado em componentes: uso intensivo de modais, eventos Livewire, notificacoes `notify` e `mentor-notification`, seletores globais de PEI/organizacao/ano, e board de entregas com kanban/lista/timeline/calendario.

## Inconsistencias e riscos encontrados

- README informa Laravel 11 em trecho de Tech Stack, mas runtime e `composer.json` confirmam Laravel 12. Tratar README como parcialmente desatualizado.
- Runtime real e Livewire 3.7.11, embora instrucoes externas possam mencionar Livewire 4. Upgrade para Livewire 4 deve ser projeto especifico.
- `ListarIndicadores` contem validacao `exists:tab_planos_acao,cod_plano_de_acao`; schema/model indicam `tab_plano_de_acao`. Isso deve ser testado/corrigido antes de upgrade.
- Dependencia de `search_path` PostgreSQL: varios models declaram tabelas sem schema, enquanto o banco real distribui dominio em schemas especificos.
- `DeliverablesBoard` concentra CRUD, filtros, anexos, comentarios, labels, lixeira e calendario/timeline; alto risco de regressao em refactor.
- Relatorio integrado aumenta memoria/timeout em runtime, sinalizando necessidade de profiling antes de upgrade.
- Storage publico nao estava linkado no ambiente analisado; anexos e arquivos podem falhar operacionalmente se o link nao existir.

## Recomendacao para upgrade

1. Congelar baseline com dump estrutural do PostgreSQL e backup completo antes de qualquer DDL.
2. Criar smoke tests para rotas principais autenticadas e publicas, incluindo mapa publico, dashboard, PEI, objetivos, indicadores, planos, entregas, riscos e relatorios.
3. Corrigir inconsistencias conhecidas de validacao/tabela antes de upgrades de framework.
4. Isolar calculos de mapa/indicadores/planos em services testaveis antes de mexer em Livewire.
5. Refatorar `DeliverablesBoard` apenas com testes de regressao de board, anexos, comentarios, labels e lixeira.
6. Revisar policies e filtros de query em conjunto, porque `viewAny=true` depende de filtros corretos para isolamento de dados.
7. Tratar upgrade de Livewire como frente propria, com auditoria de atributos, eventos, uploads, query string e renderizacao.

## Conclusao

Esta documentacao descreve o repositorio como Sistema de Planejamento Estrategico, com base em evidencias do codigo, banco real e runtime. Pontos marcados como risco ou nao confirmado devem ser tratados antes de qualquer upgrade de versao ou refatoracao estrutural.

---

# Apêndice técnico ampliado — Inventário detalhado de componentes, modelos, serviços e frontend

> Adicionado em 2026-06-24. Confiança: `Verificado no código` (leitura direta dos arquivos do repositório). Esta seção complementa o inventário de assinaturas já existente acima com semântica real: props, slots, propriedades públicas Livewire, fillable/casts/scopes/boot events dos models, detalhes de services, stores Alpine e estrutura de testes.

---

## A. Catálogo de Componentes Blade Reutilizáveis

**Localização:** `resources/views/components/`

Todos os componentes abaixo são invocados via `<x-nome-do-componente ...>` nas views Blade e Livewire.

### Componentes de UI fundamentais

| Componente | Arquivo | Props principais | Slots | Descrição |
|---|---|---|---|---|
| `x-modal` | `modal.blade.php` | `id` (required), `maxWidth='2xl'` (sm/md/lg/xl/2xl/3xl/4xl/5xl) | `$slot` | Modal Alpine.js com `wire:model`, transition suave, gerencia tooltips ao abrir/fechar (destroy + re-init). O `maxWidth` mapeia para classes CSS internas. |
| `x-toast` | `toast.blade.php` | `title`, `subtitle`, `icon`, `autohide=true`, `delay=5000`, `closeLabel` | `$slot` | Toast Bootstrap com auto-dismiss configurável. Instanciado em `DOMContentLoaded` e `livewire:navigated`. |
| `x-alert` | `alert.blade.php` | `variant='primary'`, `dismissible=false`, `icon`, `heading` | `$slot` | Alerta Bootstrap com ícone opcional. Variant mapeia diretamente para `alert-{variant}`. |
| `x-tooltip` | `tooltip.blade.php` | `title` (required), `placement='top'`, `html=false` | — | Renderiza ícone Bootstrap com tooltip. Re-inicializado em `DOMContentLoaded`, `livewire:navigated` e `livewire:initialized` via `app.js`. |
| `x-data-table` | `data-table.blade.php` | `title`, `description` | `head`, `actions`, `filters`, `footer`, `empty` | Tabela responsiva com header customizável via named slots. |
| `x-button` | `button.blade.php` | `type='submit'`, `class` (merge) | `$slot` | Botão com merge de classes Bootstrap. |
| `x-input` | `input.blade.php` | `disabled=false`, `type` (detecta `file`), `class` (merge) | — | Input genérico; detecta `type=file` para aplicar classes específicas. |
| `x-checkbox` | `checkbox.blade.php` | Atributos do `<input>` | — | Checkbox `form-check-input` Bootstrap. |
| `x-label` | `label.blade.php` | `for` | `$slot` | Label HTML com `for`. |
| `x-input-error` | `input-error.blade.php` | `messages` (array) | — | Exibe array de erros de validação Livewire sob um campo. |
| `x-validation-errors` | `validation-errors.blade.php` | Automático | — | Exibe erros globais de validação da sessão flash. |
| `x-password-strength` | `password-strength.blade.php` | `strength='weak'` | — | Indicador visual de força de senha (barra colorida). |
| `x-dropdown` | `dropdown.blade.php` | `trigger` label, `align` | `trigger`, `content` | Dropdown genérico com trigger customizável. |
| `x-dropdown-link` | `dropdown-link.blade.php` | `href` | `$slot` | Link de item dentro de dropdown. |
| `x-nav-link` | `nav-link.blade.php` | `href`, `active=false` | `$slot` | Link de navegação com estado ativo. |
| `x-responsive-nav-link` | `responsive-nav-link.blade.php` | `href`, `active=false` | `$slot` | Link de nav responsivo (mobile). |
| `x-section-title` | `section-title.blade.php` | `title`, `subtitle` | — | Título de seção com subtítulo. |
| `x-section-border` | `section-border.blade.php` | — | `$slot` | Divisor visual de seção com linha `<hr>`. |
| `x-action-message` | `action-message.blade.php` | `on`, `timeout=2000` | `$slot` | Mensagem transiente que some após `timeout` ms. |

### Componentes de formulário e modais avançados

| Componente | Arquivo | Props | Slots | Descrição |
|---|---|---|---|---|
| `x-form-section` | `form-section.blade.php` | `title`, `description` | `form`, `actions` | Seção de formulário com título lateral e área de ações. |
| `x-action-section` | `action-section.blade.php` | `title`, `description` | `content`, `actions` | Seção de ações (ex: deletar conta). |
| `x-action-button` | `action-button.blade.php` | `type='button'` | `$slot` | Botão genérico de ação. |
| `x-secondary-button` | `secondary-button.blade.php` | Classes merge | `$slot` | Botão secundário (cinza). |
| `x-danger-button` | `danger-button.blade.php` | Classes merge | `$slot` | Botão de perigo (vermelho). |
| `x-confirmation-modal` | `confirmation-modal.blade.php` | `title`, `description`, `action` | — | Modal de confirmação de ação destrutiva. |
| `x-dialog-modal` | `dialog-modal.blade.php` | `id`, `maxWidth` | `title`, `content`, `footer` | Modal estilo Jetstream com named slots. |
| `x-confirms-password` | `confirms-password.blade.php` | — | — | Modal de re-confirmação de senha (Jetstream). |

### Componentes de autenticação (Jetstream)

| Componente | Arquivo | Descrição |
|---|---|---|
| `x-authentication-card` | `authentication-card.blade.php` | Card container para páginas de auth. |
| `x-authentication-card-logo` | `authentication-card-logo.blade.php` | Logo dentro do card de auth. |
| `x-application-logo` | `application-logo.blade.php` | Logo completo da aplicação. |
| `x-application-mark` | `application-mark.blade.php` | Mark/ícone reduzido. |
| `x-banner` | `banner.blade.php` | Banner de notificação no topo (Jetstream flash messages). |
| `x-switchable-team` | `switchable-team.blade.php` | Seletor de time/organização (Jetstream, adaptado para org). |

### Componentes de domínio (PEI-específicos)

| Componente | Arquivo | Props | Descrição |
|---|---|---|---|
| `x-ods-badge` | `ods-badge.blade.php` | `numOds` (1-18), `cor` (hex) | Badge colorido de ODS com ícone oficial `public/img/ods/ods-{NN}.png`; fallback automático para badge colorido se imagem ausente. |
| `x-module-header` | `module-header.blade.php` | `title`, `subtitle` | `actions` slot | Cabeçalho padronizado de módulo GPPEI com título, subtítulo e área de ações. |
| `x-gppei-link` | `gppei-link.blade.php` | `href`, `label` | — | Link contextual para página específica do Guia GPPEI com ícone e estilo padronizado. |
| `x-projetos-link` | `projetos-link.blade.php` | `href`, `label` | — | Link contextual para o Guia Prático de Projetos. |
| `x-welcome` | `welcome.blade.php` | — | — | Componente de boas-vindas (legado). |

---

## B. Inventário detalhado de Componentes Livewire

**Localização:** `app/Livewire/`

### B.1 Dashboard — `Dashboard\Index`

**Propriedades públicas:**

| Propriedade | Tipo | Inicialização | Descrição |
|---|---|---|---|
| `$organizacaoId` | string\|null | `mount()` via sessão | ID da organização ativa |
| `$organizacaoNome` | string | `mount()` | Nome display da organização |
| `$peiAtivo` | array\|null | `mount()` | Dados do PEI selecionado |
| `$aiSummary` | string | '' | Resumo estratégico gerado por IA |
| `$anoSelecionado` | int | `date('Y')` | Ano de referência para filtros |
| `$chartData` | array | [] | Dados de gráficos: `bsc`, `riscos`, `planos`, `evolucao` |

**Listeners (`#[On]`):**
- `organizacaoSelecionada` → `atualizarOrganizacao($id)`
- `peiSelecionado` → `atualizarPEI($id)`
- `anoSelecionado` → `atualizarAno($ano)`

**Métodos de cálculo privados:**
- `getStats()` — totais de objetivos, perspectivas, indicadores, planos, riscos
- `getMinhasEntregas()` — entregas do usuário autenticado
- `getMinhasEntregasAgrupadas()` — idem, agrupadas por plano
- `getComentariosRecentes()` — últimos 5 comentários
- `getAlertasPrazos()` — entregas com prazo ≤ 7 dias ou vencidas
- `getChartBSC()` — percentual de atingimento por perspectiva
- `getChartRiscosNivel()` — contagem por nível (Crítico, Alto, Médio, Baixo)
- `getChartPlanos()` — distribuição de status dos planos
- `getChartEvolucao()` — média de atingimento por mês (últimos 12 meses)
- `getCorAtingimento($percentual)` — cor do farol via `GrauSatisfacao`
- `getOdsCobertura()` — ODS com objetivos vinculados no ciclo atual
- `getMentorStatus()` — checklist: identidade, mapa, objetivos, indicadores, planos

---

### B.2 Entregas — `Deliverables\DeliverablesBoard`

Este é o componente mais complexo do sistema. Concentra 4 views, filtros, drag-and-drop, upload, comentários em thread e lixeira.

**Estado da view:**

| Propriedade | Tipo | Descrição |
|---|---|---|
| `$plano` | PlanoDeAcao | Plano atual |
| `$view` | string (`#[Url]`) | `kanban` / `lista` / `timeline` / `calendario` |
| `$filtroStatus` | string | Filtro por status |
| `$filtroPrioridade` | string | Filtro por prioridade |
| `$filtroResponsavel` | string | Filtro por responsável |
| `$busca` | string | Busca textual |
| `$mostrarArquivados` | bool | Toggle arquivados |
| `$mostrarLixeira` | bool | Toggle lixeira (soft deleted) |
| `$progresso` | float | % conclusão do plano |

**Seletores estratégicos (navegação entre planos):**

| Propriedade | Descrição |
|---|---|
| `$perspectivaId` | Filtro de perspectiva para listar objetivos |
| `$objetivoId` | Filtro de objetivo para listar planos |
| `$perspectivasDisponiveis` | Array para o select de perspectivas |
| `$objetivosDisponiveis` | Array para o select de objetivos |
| `$planosDisponiveis` | Array para o select de planos |

**Calendário:**

| Propriedade | Descrição |
|---|---|
| `$calendarioMes` | Mês exibido (1-12) |
| `$calendarioAno` | Ano exibido |

**Timeline/Gantt:**

| Propriedade | Descrição |
|---|---|
| `$timelineInicio` | Data início (Y-m-d) |
| `$timelineFim` | Data fim (Y-m-d) |
| `$timelineZoom` | `dia` / `semana` / `mes` |

**Listeners (`#[On]`):**
- `reordenar-entregas` → `reordenarEntregas(array $ordem)` — SortableJS drag-and-drop
- `mover-para-status` → `moverParaStatus(string $entregaId, string $status, int $posicao)` — drop entre colunas Kanban
- `adicionar-comentario` → `adicionarComentario(...)` — suporte a thread
- `force-delete-entrega` → `excluirPermanente(string $entregaId)` — da lixeira

**Status suportados:** `Não Iniciado`, `Em Andamento`, `Concluído`, `Cancelado`, `Suspenso`

**Prioridades suportadas com cores:**

| Prioridade | Cor de fundo |
|---|---|
| `baixa` | `#e3e2e0` |
| `media` | `#fdecc8` |
| `alta` | `#ffe2dd` |
| `urgente` | `#e03e3e` |

**Tipos de entrega:**

| Tipo | Ícone Bootstrap | Semântica |
|---|---|---|
| `task` | `check2-square` | Tarefa padrão |
| `heading` | `type-h1` | Título de seção |
| `text` | `text-paragraph` | Bloco de texto |
| `divider` | `dash-lg` | Divisor visual |
| `checklist` | `list-check` | Lista de verificação |

---

### B.3 Indicadores — `PerformanceIndicators\ListarIndicadores`

**Propriedades de formulário relevantes:**

| Propriedade | Descrição |
|---|---|
| `$form['dsc_calculation_type']` | `manual` ou `action_plan` — se `action_plan`, o valor é calculado automaticamente pelo `IndicadorCalculoService` via `EntregaObserver` |
| `$form['dsc_polaridade']` | Positiva / Negativa / Estabilidade / Não Aplicável — altera a fórmula de atingimento |
| `$form['bln_acumulado']` | `Sim` / `Não` — se acumulado, usa soma; senão, usa último valor do período |
| `$form['json_smart']` | Array com 5 chaves: `especifico`, `mensuravel`, `atingivel`, `relevante`, `temporal` |
| `$form['organizacoes_ids']` | Array de UUIDs — multivinculação de indicador a organizações |

**Modais:**

| Modal | Propriedade | Finalidade |
|---|---|---|
| CRUD | `$showModal` | Criação/edição |
| Metas | `$showMetasModal` | Gerenciar `MetaPorAno` |
| Linha base | `$showLinhaBaseModal` | Gerenciar `LinhaBaseIndicador` |
| Exclusão | `$showDeleteModal` | Confirmação de exclusão |
| Sucesso/Erro | `$showSuccessModal`, `$showErrorModal` | Feedback visual |

---

### B.4 Componentes compartilhados — `Shared\*`

| Componente | Arquivo | Eventos emitidos | Descrição |
|---|---|---|---|
| `SeletorOrganizacao` | `Shared/SeletorOrganizacao.php` | `organizacaoSelecionada` | Tree selector da hierarquia de organizações. Persiste ID na sessão `organizacao_selecionada_id`. |
| `SeletorPei` | `Shared/SeletorPei.php` | `peiSelecionado` | Dropdown de PEIs. Persiste `pei_selecionado_id` na sessão. |
| `SeletorAno` | `Shared/SeletorAno.php` | `anoSelecionado` | Dropdown de anos extraídos dos PEIs cadastrados. |
| `PeiProgressBar` | `Shared/PeiProgressBar.php` | — | Barra de progresso no rodapé da sidebar. Usa `PeiGuidanceService::analyzeCompleteness()` e exibe as 7 fases com cores de semáforo. |
| `StrategicAlertsBell` | `Shared/StrategicAlertsBell.php` | — | Ícone de sino com badge contador. Lê `pei.strategic_alerts` não lidos do usuário. |

---

## C. Inventário detalhado de Models

### C.1 `ActionPlan\Entrega` — boot events e métodos de negócio

O model `Entrega` possui boot events registrados que geram automaticamente entradas na tabela `tab_entrega_historico`:

| Evento | Condição | Ação no histórico |
|---|---|---|
| `created` | Sempre | Registra `dsc_acao = 'created'` |
| `updating` | Campos monitorados alterados | Registra `dsc_acao = 'updated'`, `dsc_campo`, `json_valor_antigo`, `json_valor_novo` |
| `deleting` (soft) | Sempre | Registra `dsc_acao = 'deleted'` |
| `restored` | Sempre | Registra `dsc_acao = 'restored'` |

**Campos monitorados no `updating`:** `bln_status`, `cod_prioridade`, `dsc_entrega`, `dte_prazo`, `num_ordem`, `num_peso`, `bln_arquivado`, `cod_entrega_pai` (excluído `updated_at` e `json_propriedades`).

**Scopes do model `Entrega`:**

| Scope | Filtro aplicado |
|---|---|
| `porStatus($status)` | `bln_status = $status` |
| `concluidas()` | `bln_status = 'Concluído'` |
| `pendentes()` | `bln_status != 'Concluído'` e não canceladas |
| `ordenado()` | `ORDER BY num_ordem ASC` |
| `raiz()` | `whereNull cod_entrega_pai` |
| `ativas()` | Não arquivadas e não deletadas |
| `arquivadas()` | `bln_arquivado = true` |
| `porPrioridade()` | Ordena urgente → alta → media → baixa |
| `atrasadas()` | `dte_prazo < today` e não concluídas/canceladas |
| `tarefas()` | `dsc_tipo = 'task'` |
| `deletadasRecentemente()` | `deleted_at > 24h atrás` |

**Método `getProp($key)` / `setProp($key, $value)`:** Acessa/altera o campo `json_propriedades` de forma segura, preservando outros valores do JSON.

---

### C.2 `PerformanceIndicators\Indicador` — lógica de atingimento

O método `calcularAtingimento(int $ano = null, int $mes = null)` implementa a seguinte lógica:

```
Se dsc_calculation_type == 'action_plan':
    Delega para IndicadorCalculoService (usa progresso de entregas do plano)
Senão:
    Busca evoluções do período (ano/mês)
    Se bln_acumulado == 'Sim': usa soma das evoluções
    Senão: usa último valor registrado
    Aplica calcularPercentualPorTipo($realizado, $previsto) com polaridade
```

**Fórmulas por polaridade:**

| Polaridade | Fórmula de atingimento |
|---|---|
| `Positiva (↑ melhor)` | `(realizado / previsto) × 100` |
| `Negativa (↓ melhor)` | `(previsto / realizado) × 100` |
| `Estabilidade (= melhor)` | `100 - abs(realizado - previsto) / previsto × 100` |
| `Não Aplicável (informativo)` | `null` (não calcula) |

---

### C.3 `RiskManagement\Risco` — matriz de risco

**Fórmula:** `num_nivel_risco = num_probabilidade × num_impacto` (escala 1–5 × 1–5 = 1–25)

**Classificação de nível:**

| Range | Classificação | Cor |
|---|---|---|
| ≥ 16 | Crítico | `#dc2626` (vermelho) |
| ≥ 10 e < 16 | Alto | `#f97316` (laranja) |
| ≥ 5 e < 10 | Médio | `#eab308` (amarelo) |
| < 5 | Baixo | `#65a30d` (verde) |

---

### C.4 `Organization\PerfilAcesso` — constantes de perfil

```php
const SUPER_ADMIN = 'Super Admin';
const ADMIN_UNIDADE = 'Admin Unidade';
const GESTOR_RESPONSAVEL = 'Gestor Responsável';
const GESTOR_SUBSTITUTO = 'Gestor Substituto';
const CONSULTOR = 'Consultor';
const VISUALIZADOR = 'Visualizador';
```

A hierarquia de autorização real é determinada por `User::isSuperAdmin()`, que verifica a presença do perfil `SUPER_ADMIN` no pivot `rel_users_tab_organizacoes_tab_perfil_acesso`, **não** pelo campo `adm` da tabela `users` (mantido apenas por compatibilidade).

---

### C.5 `Agenda2030\ODS`

**Tabela:** `strategic_planning.tab_ods`

| Coluna | Tipo | Descrição |
|---|---|---|
| `num_ods` | int (PK) | Número 1–18 (17 ODS ONU + ODS 18 Igualdade Étnico-Racial) |
| `nom_ods` | string | Nome completo |
| `nom_ods_abreviado` | string | Nome abreviado |
| `dsc_ods` | text | Descrição |
| `cod_cor` | string | Cor oficial hexadecimal |
| `nom_icone` | string | Nome do arquivo de ícone |

Imagens em `public/img/ods/ods-01.png` ... `ods-18.png`. O componente `<x-ods-badge>` tenta `<img src="/img/ods/ods-{NN}.png">` e faz fallback para badge colorido via `cod_cor` se o arquivo não existir.

---

## D. Inventário detalhado de Services

### D.1 `IndicadorCalculoService`

**Constante `STATUS_DECIMAL`:**

| Status da entrega | Decimal |
|---|---|
| `Concluído` | `1.0` |
| `Em Andamento` | `0.5` |
| `Suspenso` | `0.25` |
| `Não Iniciado` | `0.0` |
| `Cancelado` | Excluído do cálculo |

**Método `calcularProgressoPlano(PlanoDeAcao $plano, bool $apenasRaiz = true)`:**
- Se `$apenasRaiz = true`: processa apenas entregas raiz (`cod_entrega_pai IS NULL`), calculando sub-entregas recursivamente.
- Fórmula: `Σ(peso × progresso) / Σ(peso)`, onde `progresso` é `STATUS_DECIMAL[status]`.
- Retorna 0 se não há entregas elegíveis.

**Método `atualizarIndicadoresDoPlano(PlanoDeAcao $plano)`:**
- Busca indicadores com `dsc_calculation_type = 'action_plan'` vinculados ao plano.
- Para cada indicador, cria ou atualiza `EvolucaoIndicador` com `vlr_realizado = calcularProgressoPlano()` para o mês/ano atual.
- Retorna contagem de indicadores atualizados.

---

### D.2 `PeiGuidanceService`

**Método `analyzeCompleteness(string $codPei)`:**

Retorna array com:
- `status`: `critical` / `warning` / `success`
- `progress`: 0–100
- `current_phase`: slug da fase incompleta
- `message`: texto de orientação
- `action_route`: nome da rota Laravel para redirecionar

**Fases em ordem:**

| Fase | Critério | Rota de ação |
|---|---|---|
| `ciclo` | `PEI` existe e está ativo | `pei.ciclos` |
| `identidade` | `MissaoVisaoValores` com missão e visão não vazias | `pei.index` |
| `perspectivas` | Pelo menos 1 `Perspectiva` no PEI | `pei.perspectivas` |
| `objetivos` | Pelo menos 1 `Objetivo` em perspectivas do PEI | `objetivos.index` |
| `graus` | Pelo menos 1 `GrauSatisfacao` no PEI | `graus-satisfacao.index` |
| `indicadores` | Pelo menos 1 `Indicador` vinculado a objetivos do PEI | `indicadores.index` |
| `planos` | Pelo menos 1 `PlanoDeAcao` vinculado a objetivos do PEI | `planos.index` |

---

### D.3 Services de Inteligência Artificial

**Localização:** `app/Services/AI/`

**`AiServiceFactory`:**
- Método estático `make()`: retorna instância do provider ativo com base em `config('services.ai.provider')` ou `null` se IA desabilitada em `system_settings`.
- Providers disponíveis: `VertexAiProvider`, `OpenAiProvider`, `GeminiProvider`.

**Interface `AiProviderInterface`:**

```php
interface AiProviderInterface
{
    public function suggest(string $prompt): string;
    public function analyzeSmart(string $tipo, string $titulo, string $descricao): string;
    public function summarizeStrategy(array $stats, string $organizacaoNome): string;
}
```

**Uso nos componentes Livewire:**
- `pedirAjudaIA()` — chama `suggest()` com prompt estruturado e espera JSON com array de sugestões.
- `auditSmart()` — chama `analyzeSmart()` com nome e descrição do objetivo/indicador; retorna JSON com análise por critério SMART.
- `generateAiSummary()` no Dashboard — chama `summarizeStrategy()` com estatísticas do PEI.

**Habilitação:** controlada por `system_settings.setting_key = 'ai_enabled'`. Se `0`, os botões de IA ficam ocultos nos formulários via `$aiEnabled = false`.

---

### D.4 `Reports\ReportGenerationService`

**Relatórios suportados:**

| Tipo | Método | Formato | Observações |
|---|---|---|---|
| Executivo | `gerarRelatorioExecutivo()` | PDF | Visão geral do PEI |
| Identidade | `gerarRelatorioIdentidade()` | PDF | Missão, visão, valores, cadeia de valor (paisagem) |
| Objetivos | `gerarRelatorioObjetivos()` | PDF + XLSX | Por perspectiva |
| Indicadores | `gerarRelatorioIndicadores()` | PDF + XLSX | Agrupados por perspectiva |
| Planos | `gerarRelatorioPlanos()` | PDF + XLSX | Com RACI e modelo lógico |
| Riscos | `gerarRelatorioRiscos()` | PDF + XLSX | Com matriz 5×5 e mitigações |
| Comunicação | `gerarRelatorioComunicacao()` | PDF | Plano de comunicação consolidado |
| RAE | `gerarRelatorioRae()` | PDF | Revisão e Avaliação da Estratégia |
| Cadeia de valor | `gerarRelatorioCadeiaValor()` | PDF | Atividades finalísticas e de suporte |
| Integrado (Dossiê) | `gerarRelatorioIntegrado()` | PDF | Todos os capítulos em um documento; `memory_limit=512M`, `max_execution_time=600` |

**Partials de layout compartilhados:** `resources/views/relatorios/partials/cabecalho.blade.php`, `rodape.blade.php`, `estilos.blade.php`.

---

## E. Frontend — JavaScript e Alpine.js

### E.1 `resources/js/app.js` — estrutura e Alpine stores

**Imports e globals:**
```js
import Alpine from 'alpinejs';
import mask from '@alpinejs/mask';      // Máscara de input
import focus from '@alpinejs/focus';    // Gerência de foco
import Tooltip from 'bootstrap/js/dist/tooltip';
import Toast from 'bootstrap/js/dist/toast';
window.bootstrap = { Tooltip, Toast };  // Expõe Bootstrap globalmente para Blade
```

**Store Alpine `appLayout()`:**

Retornado por `Alpine.data('appLayout', () => ({...}))`. Utilizado no `<body x-data="appLayout()">` do layout `app.blade.php`.

| Propriedade | Default | Descrição |
|---|---|---|
| `theme` | `localStorage.getItem('app.theme') \|\| 'light'` | Tema ativo (`light` / `dark`) |
| `sidebarCollapsed` | `localStorage.getItem('appSidebarCollapsed') === 'true'` | Estado da sidebar |

| Método | Descrição |
|---|---|
| `init()` | Aplica tema inicial, inicializa tooltips |
| `toggleTheme()` | Alterna `light`↔`dark`, persiste em `localStorage` |
| `toggleSidebar()` | Alterna sidebar, persiste em `localStorage` |

**Ciclo de inicialização de tooltips:**
- `DOMContentLoaded`: inicialização inicial
- `livewire:navigated`: re-inicializa após navegação Livewire
- `livewire:initialized`: re-inicializa após hidratação
- `Livewire.hook('commit', ...)`: re-inicializa após cada request Livewire (para tooltips em elementos renderizados dinamicamente)

**Inicialização de Toasts:**
- `DOMContentLoaded` e `livewire:navigated`: ativa todos os `[data-bs-toggle="toast"]` com `autohide` e `delay` configurados.

---

### E.2 `resources/js/session-timer.js`

**Objetivo:** Monitorar inatividade e renovar/expirar a sessão.

**Fluxo:**
1. Registra eventos de atividade do usuário: `mousemove`, `keydown`, `click`, `scroll`.
2. A cada interação, atualiza `lastActivity = Date.now()`.
3. Interval a cada 60s: se `Date.now() - lastActivity > SESSION_LIFETIME_MS - 60000`, faz `POST /session/ping`.
4. Se `POST /session/ping` retorna `401` ou `session_expired`, redireciona para `/login`.
5. `SESSION_LIFETIME_MS` lido da meta tag `<meta name="session-lifetime" content="...">` no layout.

**Rota de suporte:** `POST /session/ping` — retorna `200 OK` se sessão válida, `401` se expirada.

---

### E.3 `vite.config.js`

```js
export default defineConfig({
    base: '/fs-v1/public/build/',    // Subfolder deployment (XAMPP)
    plugins: [
        laravel({
            input: [
                'resources/scss/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,           // Livewire hot-reload
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: { quietDeps: true }  // Suprime warnings de bootstrap SCSS
        }
    }
});
```

**Ponto crítico:** O `base` `/fs-v1/public/build/` é necessário porque a aplicação roda em subfolder do XAMPP. Em produção com domínio próprio, deve ser alterado para `/build/` ou `''`.

---

## F. Seeders — catálogo completo

| Seeder | Responsabilidade |
|---|---|
| `DatabaseSeeder` | Orquestra os demais seeders em ordem |
| `BaseStrategicSeeder` | PEI base + perspectivas BSC padrão |
| `PEIDataSeeder` | Dados genéricos de PEI para testes |
| `MIDROrganizationSeeder` | Organizações de exemplo (estrutura MIDR) |
| `MIDRIdentitySeeder` | Missão, visão e valores da organização MIDR |
| `MIDRStrategicSeeder` | Perspectivas, objetivos e valores estratégicos |
| `MIDRAnalysisSeeder` | Análise SWOT e PESTEL com dados de exemplo |
| `TemaNorteadorSeeder` | Temas norteadores estratégicos |
| `MIDRBusinessSeeder` | Planos de ação com modelo lógico |
| `EntregaSeeder` | Entregas com hierarquia e diferentes status |
| `IndicadorSeeder` | Indicadores vinculados a objetivos e planos |
| `EvolucaoIndicadorSeeder` | Dados mensais de evolução (previsto/realizado) |
| `LinhaBaseIndicadorSeeder` | Valores de linha de base por ano |
| `MetaPorAnoSeeder` | Metas anuais por indicador |
| `MIDRRiskSeeder` | Riscos com categorias e níveis |
| `RiscoMitigacaoSeeder` | Ações de mitigação por risco |
| `RiscoOcorrenciaSeeder` | Incidentes/ocorrências registradas |
| `OdsSeeder` | Os 18 ODS (idempotente — usa `updateOrCreate`) |
| `MIDRModulosGppeiSeeder` | Módulos metodológicos GPPEI de demonstração |
| `MIDRSupportSeeder` | Dados de suporte (tipos de execução, perfis, etc) |

**Command Artisan para ambiente de demonstração:** `php artisan db:seed-midr` (via `SeedMIDREnvironment`).

---

## G. Testes — estrutura atual

**Localização:** `tests/`

```
tests/
├── Feature/
│   ├── ExampleTest.php
│   ├── Auth/
│   │   ├── AuthenticationTest.php
│   │   ├── EmailVerificationTest.php
│   │   ├── PasswordResetTest.php
│   │   └── RegistrationTest.php
│   ├── API/
│   │   ├── ApiTokenPermissionsTest.php
│   │   ├── CreateApiTokenTest.php
│   │   └── DeleteApiTokenTest.php
│   ├── Profile/
│   │   ├── BrowserSessionsTest.php
│   │   ├── DeleteAccountTest.php
│   │   ├── ProfileInformationTest.php
│   │   ├── TwoFactorAuthenticationSettingsTest.php
│   │   └── UpdatePasswordTest.php
│   ├── Livewire/
│   │   └── ListarIndicadoresTest.php
│   └── UserManagement/
│       └── ListarUsuariosCadastroSemTruncateTest.php
├── Unit/
│   └── ExampleTest.php
├── Pest.php               ← Configuração global do Pest (helpers, datasets)
└── TestCase.php           ← Base class com helpers de autenticação
```

**Cobertura atual:** Majoritariamente focada em auth e profile (Jetstream padrão). Testes de domínio PEI são escassos — apenas `ListarIndicadoresTest` cobre um componente Livewire de negócio. Esta é uma lacuna crítica identificada para evolução.

**Comando de execução:** `php artisan test` ou `vendor/bin/pest --parallel`.

---

## H. Commands Artisan customizados

| Command | Assinatura | Responsabilidade |
|---|---|---|
| `FixPlanosEntregasDates` | `pei:fix-datas` | Corrige datas de planos/entregas que ficaram fora do range do PEI |
| `GenerateCsvTemplates` | `pei:gerar-templates-csv` | Gera templates CSV para importação em massa (pares guia+template) |
| `ProcessScheduledReports` | `pei:processar-relatorios` | Processa fila de relatórios agendados; atualiza `dte_proxima_execucao` conforme frequência (Diária/Semanal/Mensal/Única) |
| `SeedMIDREnvironment` | `db:seed-midr` | Popula ambiente de demonstração com dados MIDR completos |

---

## I. Catálogo de eventos Livewire (barramento de comunicação)

O sistema utiliza eventos Livewire como barramento principal entre componentes. Segue o catálogo completo:

### Eventos globais de contexto (emitidos pelos seletores, escutados por múltiplos componentes)

| Evento | Emitido por | Escutado por | Dado transportado |
|---|---|---|---|
| `organizacaoSelecionada` | `Shared\SeletorOrganizacao` | Dashboard, ListarPlanos, ListarRiscos, MapaEstrategico, ListarObjetivos, e outros | `$codOrganizacao` (UUID) |
| `peiSelecionado` | `Shared\SeletorPei` | Dashboard, ListarObjetivos, MapaEstrategico, ListarPerspectivas, e outros | `$codPei` (UUID) |
| `anoSelecionado` | `Shared\SeletorAno` | Dashboard, ListarIndicadores, ListarPlanos | `$ano` (int) |

### Eventos de UI (notificações e feedback)

| Evento | Emitido por | Escutado por | Dado transportado |
|---|---|---|---|
| `notify` | Componentes de domínio após save | Layout (JS listener) → inicializa Toast | `{type: 'success'/'error', message: '...'}` |
| `mentor-notification` | `MissaoVisao`, `ListarObjetivos`, `ListarRiscos` | Layout sidebar (mentor widget) | `{phase: '...', message: '...'}` |

### Eventos do DeliveriesBoard (drag-and-drop e ações em linha)

| Evento | Emitido por | Escutado por | Dado |
|---|---|---|---|
| `reordenar-entregas` | Alpine.js + SortableJS (JS) | `DeliverablesBoard` (#[On]) | `array $ordem` [{id, ordem}] |
| `mover-para-status` | Alpine.js drop entre colunas | `DeliverablesBoard` (#[On]) | `$entregaId`, `$status`, `$posicao` |
| `adicionar-comentario` | View Blade (form submit) | `DeliverablesBoard` (#[On]) | `$entregaId`, `$comentario`, `$comentarioPaiId` |
| `force-delete-entrega` | View Blade (botão lixeira) | `DeliverablesBoard` (#[On]) | `$entregaId` |
| `atualizar-prazo-entrega` | Alpine.js (timeline drag) | `DeliverablesBoard` (#[On]) | `$entregaId`, `$novoPrazo` |

---

## J. Layouts — estrutura detalhada

### `layouts/app.blade.php` — layout autenticado

**Seções e stacks:**
- `@yield('content')` — conteúdo principal (usado por Livewire full-page)
- `@stack('scripts')` — scripts adicionais por página
- `@stack('styles')` — estilos adicionais por página

**Meta tags relevantes:**
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="session-lifetime" content="{{ config('session.lifetime') * 60 }}">
<meta name="api-url" content="{{ url('/') }}">
```

**Imports CDN:**
- Bootstrap Icons `1.11.x` (CSS)
- Chart.js (para gráficos do Dashboard)

**Imports via Vite:**
- `resources/scss/app.scss` — Bootstrap + customizações
- `resources/js/app.js` — Alpine, session-timer, tooltips

**Impersonation banner:** Exibido em sticky no topo quando `session('impersonating') = true`. Mostra nome do usuário impersonado e link "Encerrar impersonação" → `GET /impersonate-stop`.

---

### `layouts/guest.blade.php` — layout público (login/register)

Card centralizado sem sidebar. Inclui alternador de tema (dark/light) via Alpine. Usa `resources/scss/app.scss` e `resources/js/app.js` via Vite.

---

### `layouts/public.blade.php` — layout da landing page

Navbar pública com logo, links de módulos GPPEI e botão "Acessar sistema". Suporte a dark mode com alternador persistido em `localStorage`. Inclui rodapé com informações institucionais.

---

## K. Convenções de nomenclatura de banco de dados

O sistema usa convenções de prefixo rígidas para todos os identificadores do banco:

| Prefixo | Uso | Exemplo |
|---|---|---|
| `tab_` | Tabelas de dados | `tab_pei`, `tab_entregas` |
| `rel_` | Tabelas de relacionamento (pivot) | `rel_entrega_labels`, `rel_plano_organizacao` |
| `cod_` | Chaves primárias UUID | `cod_pei`, `cod_entrega` |
| `dsc_` | Campos descritivos curtos (varchar) | `dsc_plano_de_acao`, `dsc_tipo` |
| `txt_` | Campos de texto longo (text) | `txt_descricao`, `txt_detalhamento` |
| `nom_` | Campos de nome | `nom_indicador`, `nom_organizacao` |
| `bln_` | Campos booleanos | `bln_status`, `bln_arquivado` |
| `num_` | Campos numéricos | `num_peso`, `num_probabilidade` |
| `dte_` | Campos de data | `dte_inicio`, `dte_prazo` |
| `vlr_` | Campos de valor decimal | `vlr_realizado`, `vlr_orcamento_previsto` |
| `json_` | Campos JSON/JSONB | `json_propriedades`, `json_smart` |
| `sgl_` | Campos de sigla | `sgl_organizacao` |

---

## Conclusão do apêndice

Este apêndice eleva a documentação do nível de inventário de assinaturas para o nível de referência semântica completa. Com ele, um engenheiro pode implementar novos componentes, integrações ou testes sem precisar derivar comportamento por inspeção de código — as regras de negócio, convenções e contratos de interface estão explicitados acima.
