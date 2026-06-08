# Migração de Dados — Sistema de Planejamento Estratégico (v1 → v2)

**Documento:** Mapa De→Para e plano de migração
**Natureza:** Migração de dados de produção (órgão de governo) — **alta criticidade**
**Status:** Planta para construção do *service* artisan de migração
**Premissa-mor:** o banco do cliente **não é acessível por nós**; a base de conhecimento é o código-fonte de ambas as versões (migrations + repositórios).

---

## 1. Contexto e objetivo

O cliente roda a **versão 1** (Laravel 8) do sistema e deseja migrar para a **versão 2** (Laravel 12) **sem perder os dados** já preenchidos. A execução será feita pelos **analistas de infra do órgão**, rodando **um único comando artisan** — eles não executam `migrate` manualmente; o service orquestra tudo.

---

## 2. Premissas técnicas verificadas no código (não são suposições)

| # | Premissa | Evidência |
|---|---|---|
| P1 | Origem e destino são **PostgreSQL** | Migrations do legado usam `pei.tabela` (qualificação por schema), inexistente em MySQL |
| P2 | UUID do legado é gerado **por código** (`Str::uuid()`), não pelo banco | `App\Traits\Uuids::boot()` → `creating` define `Str::uuid()->toString()`; sem `default` de UUID nas migrations |
| P3 | UUIDs podem ser **preservados 1:1** na migração | Consequência de P2: são valores literais; FKs continuam válidas sem regerar nada |
| P4 | A **v2 exige PostgreSQL ≥ 9.4** | Migrations v2 usam `jsonb` (≥9.4) e `gen_random_uuid()` via `pgcrypto` (≥9.4) |
| P5 | **6 schemas físicos** na v2 | `0001_01_01_000004_create_pei_schema` faz `CREATE SCHEMA` para `strategic_planning`, `action_plan`, `performance_indicators`, `risk_management`, `organization`, `public` |
| P6 | Legado usa schemas **`pei` + `public`** | Migrations v1 criam tabelas em `pei.*` e `public.*` |
| P7 | `tab_objetivo_estrategico` (v1) **= `tab_objetivo`** (v2) | Migration `rename_objetivo_estrategico_to_objetivo` (ALTER ... RENAME) |
| P8 | `tab_tema_norteador` (v2) é **conceito novo** — nasce vazio | Migração `create_new_tab_objetivo_estrategico` + `rename_..._to_tema_norteador`; **não** recebe dados do legado |
| P9 | A v1 **não possui**: Riscos, ODS, SWOT/PESTEL, Partes Interessadas, Cenários, RAE, Lições, RACI, Comunicação, Modelo Lógico, e o modelo "Notion" de entregas | Ausência das respectivas migrations no legado |

> **Importante sobre o "9.3":** o piso 9.3 vale como **boa prática de portabilidade no SQL de leitura** do legado. O banco real, por exigência da v2 (P4), será **≥ 9.4**. O service **detecta a versão em runtime** e aborta se < 9.4, antes de tocar em qualquer dado.

---

## 3. Estratégia aprovada — "Quarentena → Construção → Transferência → Validação → Descarte"

Tudo no **mesmo banco**, **uma conexão**, **uma versão**. Não-destrutivo até a validação.

| Fase | Ação | Segurança |
|---|---|---|
| **0. Pré-checagem** | Detecta versão do PG (aborta se < 9.4); exige confirmação de `pg_dump`; valida que o schema legado existe e o novo ainda não | Nada inicia sem rede de proteção |
| **1. Quarentena** | `ALTER SCHEMA pei RENAME TO legacy_pei`; **todas** as tabelas de `public` do legado → `legacy_public` (via `ALTER TABLE ... SET SCHEMA`), liberando `public` para a v2 sem precisar saber tabela a tabela | Preserva 100% dos dados sob outro schema; **reversível** |
| **2. Construção** | `Artisan::call('migrate')` cria os 6 schemas e todas as tabelas v2, limpas | Sem colisão (o legado saiu de cena) |
| **3. Transferência (ETL)** | Copia `legacy_*` → tabelas v2, na ordem de dependência de FK, **preservando UUIDs**, aplicando renomeações e *defaults* | Origem (quarentena) intacta — só leitura |
| **4. Validação** | Conta linhas origem × destino por tabela; checa FKs órfãs; emite relatório | Falha aqui → aborta antes do descarte |
| **5. Descarte** | Após validação OK **e** confirmação explícita: `DROP SCHEMA legacy_pei CASCADE` + drop das `legacy_*` | Mantido como fallback até liberação |

---

## 4. Pré-requisitos inegociáveis (entram no runbook do analista)

1. **`pg_dump` completo do banco** antes de qualquer execução. O service registra a confirmação.
2. PostgreSQL **≥ 9.4** (idealmente 13+). O service verifica e aborta se incompatível.
3. Aplicação v2 **instalada** (código + `vendor` + `.env` apontando para o banco do cliente).
4. Janela de manutenção (sistema fora do ar durante a migração).
5. Execução em **homologação** antes da produção, se houver ambiente.

---

## 5. Mapa De→Para — tabelas que MIGRAM dados

Legenda de tipo: **1:1** (cópia direta, muda só schema) · **REN** (renomeia tabela/coluna) · **TRANSF** (transformação de modelo/valores) · **+DEF** (preencher colunas novas `NOT NULL` com default).

| # | Legado (schema.tabela) | v2 (schema.tabela) | Tipo | Observações de mapeamento |
|---|---|---|---|---|
| 1 | `public.tab_organizacoes` | `organization.tab_organizacoes` | 1:1 | colunas idênticas (`cod_organizacao`, `sgl_organizacao`, `nom_organizacao`, `rel_cod_organizacao`) |
| 2 | `public.tab_perfil_acesso` | `organization.tab_perfil_acesso` | 1:1 | `cod_perfil`, `dsc_perfil`, `dsc_permissao` |
| 3 | `public.users` | `pei.users` | 1:1 +DEF | colunas-chave iguais; v2 acrescenta `theme_color` (default), `current_team_id`, `profile_photo_path` (nullable). **Super Admin na v2 é definido pelo PERFIL** (`PerfilAcesso::SUPER_ADMIN`), não pelo campo `adm`; após a carga, o ETL sincroniza `adm` como espelho do perfil (1=Super Admin, senão 0; default da coluna = 0) — ver Seção 8 |
| 4 | `public.rel_users_tab_organizacoes` | `organization.rel_users_tab_organizacoes` | 1:1 | pivô usuário↔organização |
| 5 | `public.rel_users_tab_organizacoes_tab_perfil_acesso` | `organization.rel_users_tab_organizacoes_tab_perfil_acesso` | 1:1 | v2 torna `cod_plano` nullable |
| 6 | `public.rel_organizacao` | `organization.rel_organizacao` | 1:1 | hierarquia de organizações |
| 7 | `pei.tab_pei` | `strategic_planning.tab_pei` | 1:1 | `cod_pei`, `dsc_pei`, `num_ano_inicio_pei`, `num_ano_fim_pei` |
| 8 | `pei.tab_missao_visao_valores` | `strategic_planning.tab_missao_visao_valores` | 1:1 | `dsc_missao`, `dsc_visao`, `cod_pei`, `cod_organizacao` |
| 9 | `pei.tab_valores` (`valores`) | `strategic_planning.tab_valores` | 1:1 | `cod_valor`, `nom_valor`, `dsc_valor`, `cod_pei`, `cod_organizacao` |
| 10 | `pei.tab_perspectiva` | `strategic_planning.tab_perspectiva` | 1:1 +DEF | v2 acrescenta pesos (`num_peso_indicadores`, `num_peso_planos`) → **default 100/0** |
| 11 | `pei.tab_objetivo_estrategico` | `strategic_planning.tab_objetivo` | **REN** | `cod_objetivo_estrategico`→`cod_objetivo`; `nom_objetivo_estrategico`→`nom_objetivo`; `dsc_objetivo_estrategico`→`dsc_objetivo` |
| 12 | `pei.tab_futuro_almejado_objetivo_estrategico` | `strategic_planning.tab_futuro_almejado_objetivo` | REN | FK `cod_objetivo_estrategico`→`cod_objetivo` |
| 13 | `pei.tab_nivel_hierarquico` | `strategic_planning.tab_nivel_hierarquico` | 1:1 | tabela de domínio |
| 14 | `pei.tab_grau_satisfcao` | `strategic_planning.tab_grau_satisfacao` | TRANSF +DEF | tabela renomeada (`satisfcao`→`satisfacao`); v2 acrescentou `cod_pei` e `num_ano` (**NOT NULL**) → associar ao PEI migrado / ano do ciclo |
| 15 | `pei.tab_tipo_execucao` | `action_plan.tab_tipo_execucao` | 1:1 | `cod_tipo_execucao`, `dsc_tipo_execucao` |
| 16 | `pei.tab_plano_de_acao` | `action_plan.tab_plano_de_acao` | REN | FK `cod_objetivo_estrategico`→`cod_objetivo`; `txt_principais_entregas`→`txt_detalhamento` (a confirmar semântica); **+** popular `rel_plano_organizacao` (N:N) com `(cod_plano, cod_organizacao)` |
| 17 | `pei.tab_indicador` | `performance_indicators.tab_indicador` | REN +DEF | FK `cod_objetivo_estrategico`→`cod_objetivo`; v2 acrescenta `dsc_polaridade` (default 'Positiva'), tipo de cálculo e `json_smart` (default `'{}'`/null) |
| 18 | `pei.tab_evolucao_indicador` | `performance_indicators.tab_evolucao_indicador` | 1:1 | `num_ano`, `num_mes`, `vlr_previsto`, `vlr_realizado`, `txt_avaliacao` |
| 19 | `pei.tab_linha_base_indicador` | `performance_indicators.tab_linha_base_indicador` | 1:1 | `num_linha_base`, `num_ano` |
| 20 | `pei.tab_meta_por_ano` | `performance_indicators.tab_meta_por_ano` | 1:1 | `num_ano`, `meta` |
| 21 | `pei.rel_indicador_objetivo_estrategico_organizacao` | `performance_indicators.rel_indicador_objetivo_organizacao` | REN | tabela renomeada; colunas `cod_indicador`, `cod_organizacao` |
| 22 | `pei.tab_arquivos` | `performance_indicators.tab_arquivos` | 1:1 | FK `cod_evolucao_indicador` (anexos de evolução) — confirmar schema de destino |
| 23 | `pei.tab_atividade_cadeia_valor` | `strategic_planning.tab_atividade_cadeia_valor` | 1:1 +DEF | v2 acrescenta `dsc_tipo` (default 'Finalística') e `num_ordem` (default sequencial) |
| 24 | `pei.tab_processos_atividade_cadeia_valor` | `strategic_planning.tab_processos_atividade_cadeia_valor` | 1:1 | `dsc_entrada`, `dsc_transformacao`, `dsc_saida` |
| 25 | `public.acoes` | `action_plan.acoes` | 1:1 | confirmar uso real no domínio |
| 26 | `public.tab_status` | `pei.tab_status` | 1:1 | `cod_status`, `dsc_status` |
| 27 | `public.tab_audit` / `public.audits` | — | **NÃO MIGRA** | ❌ decisão de projeto: auditoria histórica não é transferida (fica apenas no backup) |
| 28 | `public.tab_entregas` | `action_plan.tab_entregas` | **TRANSF +DEF** | ⚠️ ver Seção 6 — modelo mudou radicalmente |

---

## 6. Caso especial crítico — `tab_entregas` (modelo mudou de natureza)

O modelo de entrega da v1 era um **item de medição** (com unidade, item e quantidade). A v2 adota um modelo **tipo "Notion"/Kanban** (tarefa com prioridade, prazo, peso, hierarquia, propriedades JSON).

| Coluna v1 | Destino v2 | Tratamento |
|---|---|---|
| `cod_entrega` | `cod_entrega` | preservado (UUID) |
| `cod_plano_de_acao` | `cod_plano_de_acao` | preservado |
| `dsc_entrega` | `dsc_entrega` | direto |
| `bln_status` | `bln_status` | **mapear valores** v1→v2 (v2: `Não Iniciado`/`Em Andamento`/`Concluído`/`Cancelado`/`Suspenso`). *Os valores reais do legado precisam ser confirmados numa amostra; default proposto: o que não casar → `Não Iniciado`* |
| `dsc_periodo_medicao` | `dsc_periodo_medicao` | direto |
| `dsc_unidade_medida` | **sem coluna** | **preservar em `json_propriedades`** (campo flexível da v2) — nada se perde |
| `dsc_item_entregue` | **sem coluna** | idem `json_propriedades` |
| `num_quantidade_prevista` | **sem coluna** | idem `json_propriedades` |
| — | `num_nivel_hierarquico_apresentacao` (NOT NULL) | default por ordem sequencial dentro do plano |
| — | `cod_prioridade` | default `'media'` |
| — | `dsc_tipo` | default `'task'` |
| — | `num_ordem` | sequencial |
| — | `bln_arquivado` | default `false` |
| — | `cod_responsavel` / `dte_prazo` / `cod_entrega_pai` | nulos (sem origem) |

**✅ DECIDIDO:** preservar `dsc_unidade_medida` + `dsc_item_entregue` + `num_quantidade_prevista` dentro de `json_propriedades` (nenhum dado histórico se perde); status legado não reconhecido → `Não Iniciado`. *Implementado no transformador `transformEntrega()` do service.*

---

## 7. Tabelas da v2 que NASCEM VAZIAS (sem origem no legado)

Não há dado a migrar — criadas e deixadas vazias para preenchimento na nova operação:

- **Risk Management:** `tab_risco`, `tab_risco_objetivo`, `tab_risco_mitigacao`, `tab_risco_ocorrencia`
- **Agenda 2030:** `tab_ods` (populada pelo `OdsSeeder`), `rel_objetivo_ods`, `rel_pei_ods`
- **Strategic Planning (novos):** `tab_tema_norteador`, `tab_analise_ambiental` (SWOT/PESTEL), `tab_inaugurar_pei`, `tab_integracao_instrumentos`, `tab_calendario_eventos_pei`, `tab_partes_interessadas`, `tab_cenarios_prospectivos`, `tab_rae`
- **Action Plan (novos):** `tab_raci`, `tab_licoes_aprendidas`, `tab_plano_comunicacao`, `tab_entrega_comentarios`, `tab_entrega_labels`, `rel_entrega_labels`, `tab_entrega_anexos`, `tab_entrega_historico`, `rel_entrega_users_responsaveis`
- **Public/Infra:** `system_settings`, `strategic_alerts`, tabelas de gestão de relatórios

> O **ODS 18** e os 17 ODS são populados pelo `OdsSeeder` (parte da Fase 2/Construção), não pelo ETL.

---

## 8. Ordem de carga (topológica — respeita FKs)

1. `tab_organizacoes` → `rel_organizacao` → `tab_perfil_acesso` → `users` → pivôs de usuário → *(etapa derivada)* sincronizar `users.adm` pelo perfil Super Administrador
2. `tab_pei`
3. `tab_missao_visao_valores`, `tab_valores`, `tab_perspectiva`, `tab_nivel_hierarquico`, `tab_grau_satisfacao`
4. `tab_objetivo` → `tab_futuro_almejado_objetivo`
5. `tab_tipo_execucao` → `tab_plano_de_acao` → `rel_plano_organizacao`
6. `tab_indicador` → `tab_meta_por_ano`, `tab_linha_base_indicador`, `tab_evolucao_indicador` → `tab_arquivos`; `rel_indicador_objetivo_organizacao`
7. `tab_atividade_cadeia_valor` → `tab_processos_atividade_cadeia_valor`
8. `tab_entregas` (com transformação da Seção 6)
9. `tab_status`, `acoes` *(auditoria `tab_audit`/`audits` **não migra** — decisão de projeto)*

---

## 9. Princípio de implementação do ETL (reduz risco)

Para cada tabela, o service fará:
- **Mapa explícito** de renomeações (tabela/coluna) conforme Seção 5;
- **Cópia automática das colunas em comum** (interseção origem↔destino) — resiliente a divergências menores;
- **Defaults** para colunas novas `NOT NULL` sem origem (documentados acima);
- **Preservação de UUIDs** (sem regerar);
- **Em lote** (chunks) e dentro de **transação por fase**;
- **Idempotência**: detecta se a migração já rodou (ex.: presença de `legacy_pei` / contagem no destino) e não duplica.

---

## 10. Decisões de negócio (RESOLVIDAS)

1. ✅ **Entregas:** campos órfãos preservados em `json_propriedades`. (Seção 6)
2. ✅ **Status de entrega:** valor não reconhecido → `Não Iniciado` (default seguro aplicado no service).
3. ✅ **`txt_principais_entregas` (plano) → `txt_detalhamento`:** mapeamento aplicado.
4. ✅ **Auditoria histórica:** **NÃO migra** — sem backup específico, sem transferência. Permanece apenas no `pg_dump`.
5. ✅ **Usuários/senhas:** hashes migrados como estão (bcrypt compatível) + `trocarsenha` preservado para forçar redefinição no 1º acesso.

---

## 11. Status de implementação

1. ✅ Mapa De→Para validado.
2. ✅ **Service artisan** implementado: `app/Console/Commands/MigrarLegadoV1ParaV2.php` (`php artisan migracao:v1-para-v2`), Fases 0–5, com `--dry-run`, `--force`, `--pular-backup`, `--descartar-legado`. ETL com descoberta de colunas/schema em runtime.
3. ✅ **Runbook** dos analistas de infra: `documentacao/runbook-migracao-v1-para-v2.md`.

---

## 12. Validação contra o banco real da v1 (dev `governanca`)

Inventário executado direto no PostgreSQL da v1 (read-only, `information_schema` + `pg_class`):

- 🔑 **Todas as 32 tabelas da v1 estão no schema `pei`** — não há split `pei`/`public`. Logo, a Fase 1 (`ALTER SCHEMA pei RENAME TO legacy_pei`) **captura 100% do legado de uma vez**; o passo de mover tabelas de `public` é defensivo (no legado, `public` está vazio). Inclui `users`, `acoes`, `tab_status`, `audits`, `migrations`, `sessions` — todos em `pei`.
- ✅ **As 27 tabelas do mapa De→Para existem.** Incertezas resolvidas: a tabela de valores é **`tab_valores`** (não `valores`); o typo **`tab_grau_satisfcao`** confirmado; `users` vive em `pei` (o ETL a localiza em `legacy_pei`).
- ✅ `audits` (113) e `tab_audit` (22) existem mas **estão fora do mapa** → corretamente ignoradas. Infra Laravel (`migrations`, `sessions`, `failed_jobs`, `password_resets`, `personal_access_tokens`) também ignorada (a v2 recria).
- ⚠️ **Nota de ambiente (não afeta a migração):** o banco de dev `governanca` apresenta **corrupção no índice de catálogo `pg_aggregate_fnoid_index`** (resquício de recovery mode), que faz `count()` falhar. Correção local: `REINDEX INDEX pg_catalog.pg_aggregate_fnoid_index;`. Problema exclusivo deste banco de dev — sem relação com o service nem com o ambiente do cliente.
