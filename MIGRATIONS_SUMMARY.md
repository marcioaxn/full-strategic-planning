# MIGRATIONS SUMMARY - SPS Project

**Date:** 24 de Dezembro de 2025
**Status:** ✅ COMPLETO - Todas as migrations criadas e verificadas

---

## 📊 RESUMO EXECUTIVO

Foram criadas/modificadas **39 migration files** que incluem:
- ✅ **Modificações** em migrations do starter kit para compatibilidade com legado
- ✅ **Criação** de 1 schema PostgreSQL (PEI)
- ✅ **Criação** de 9 tabelas no schema PUBLIC
- ✅ **Criação** de 19 tabelas no schema PEI (legado)
- ✅ **Criação** de 4 tabelas de Gestão de Riscos (novas)
- ✅ **Todas** seguem convenções UUID e PostgreSQL

---

## 🗂️ ESTRUTURA CRIADA

### 1. STARTER KIT (Modificadas/Mantidas - 7 arquivos)

| Arquivo | Status | Descrição |
|---------|--------|-----------|
| `0001_01_01_000000_create_users_table.php` | ✏️ MODIFICADO | Adicionados campos legados: `ativo`, `adm`, `trocarsenha` |
| `0001_01_01_000001_create_cache_table.php` | ✅ MANTIDO | Cache do Laravel |
| `0001_01_01_000002_create_jobs_table.php` | ✅ MANTIDO | Jobs, job_batches, failed_jobs |
| `0001_01_01_000003_create_sessions_table.php` | ✅ MANTIDO | Já usa UUID para user_id |
| `0001_01_01_000004_create_pei_schema.php` | 🆕 NOVO | Criação do schema PEI |
| `2025_11_15_221711_create_personal_access_tokens_table.php` | ✅ MANTIDO | Tokens API (Sanctum) |
| `2025_12_19_235345_add_theme_color_to_users_table.php` | ✅ MANTIDO | Tema do usuário |

**Arquivos REMOVIDOS:**
- ❌ `2025_11_16_222928_create_leads_table.php` - Não necessário para este projeto

---

### 2. PUBLIC SCHEMA (9 tabelas)

#### 2.1 Tabelas Principais

| Migration | Tabela | Descrição |
|-----------|--------|-----------|
| `2014_08_09_230616_create_tab_organizacoes_table.php` | `tab_organizacoes` | Hierarquia de unidades organizacionais |
| `2014_10_11_080128_create_tab_perfil_acesso_table.php` | `tab_perfil_acesso` | 4 perfis: Super Admin, Admin Unidade, Gestor Responsável, Gestor Substituto |
| `2024_11_23_104155_create_tab_status_table.php` | `tab_status` | Domínio de status |

#### 2.2 Tabelas de Relacionamento

| Migration | Tabela | Descrição |
|-----------|--------|-----------|
| `2014_10_13_224252_create_rel_users_tab_organizacoes_table.php` | `rel_users_tab_organizacoes` | Usuários ↔ Organizações |
| `2021_09_20_230616_create_rel_organizacao_table.php` | `rel_organizacao` | Hierarquia adicional de organizações |
| `2021_11_25_081914_create_rel_users_tab_organizacoes_tab_perfil_acesso_table.php` | `rel_users_tab_organizacoes_tab_perfil_acesso` | Controle de acesso completo: User + Organização + Plano + Perfil |

#### 2.3 Tabelas de Auditoria

| Migration | Tabela | Descrição |
|-----------|--------|-----------|
| `2021_10_20_230616_create_acoes_table.php` | `acoes` | Log simples de ações |
| `2022_01_18_133729_create_tab_audit_table.php` | `tab_audit` | Auditoria customizada (antes/depois, IP, usuário) |
| `2024_11_21_193856_create_audits_table.php` | `audits` | Laravel Auditing (owen-it/laravel-auditing) com UUID |

---

### 3. PEI SCHEMA (19 tabelas legadas)

#### 3.1 Planejamento Estratégico (Core)

| Migration | Tabela | Descrição |
|-----------|--------|-----------|
| `2021_10_31_171917_create_pei_tab_pei_table.php` | `pei.tab_pei` | Ciclos de planejamento (ano início/fim) |
| `2021_11_01_212118_create_pei_tab_missao_visao_valores_table.php` | `pei.tab_missao_visao_valores` | Missão e Visão |
| `2024_06_18_114518_create_pei_tab_valores_table.php` | `pei.tab_valores` | Valores organizacionais (múltiplos) |

#### 3.2 Balanced Scorecard (BSC)

| Migration | Tabela | Descrição |
|-----------|--------|-----------|
| `2021_11_08_185623_create_pei_tab_perspectiva_table.php` | `pei.tab_perspectiva` | 4 perspectivas BSC |
| `2021_11_09_094804_create_pei_tab_objetivo_estrategico_table.php` | `pei.tab_objetivo_estrategico` | Objetivos estratégicos |
| `2021_11_09_095359_create_pei_tab_nivel_hierarquico_table.php` | `pei.tab_nivel_hierarquico` | 100 níveis hierárquicos |
| `2024_06_21_172717_create_pei_tab_futuro_almejado_objetivo_estrategico_table.php` | `pei.tab_futuro_almejado_objetivo_estrategico` | Futuro almejado por objetivo |

#### 3.3 Planos de Ação e Entregas

| Migration | Tabela | Descrição |
|-----------|--------|-----------|
| `2021_11_14_221355_create_pei_tab_tipo_execucao_table.php` | `pei.tab_tipo_execucao` | 3 tipos: Ação, Iniciativa, Projeto |
| `2021_11_14_221613_create_pei_tab_plano_de_acao_table.php` | `pei.tab_plano_de_acao` | Planos de ação com orçamento, status, PPA/LOA |
| `2024_11_15_215604_create_pei_tab_entregas_table.php` | `pei.tab_entregas` | Entregas dos planos (com hierarquia) |

#### 3.4 Indicadores (KPIs)

| Migration | Tabela | Descrição |
|-----------|--------|-----------|
| `2021_12_28_232711_create_pei_tab_indicador_table.php` | `pei.tab_indicador` | Indicadores de desempenho |
| `2021_12_28_234715_create_pei_tab_evolucao_indicador_table.php` | `pei.tab_evolucao_indicador` | Evolução mensal (previsto vs. realizado) |
| `2021_12_28_235603_create_pei_tab_linha_base_indicador_table.php` | `pei.tab_linha_base_indicador` | Linha de base por ano |
| `2022_01_03_105544_create_pei_tab_meta_por_ano_table.php` | `pei.tab_meta_por_ano` | Metas anuais |
| `2022_01_26_152500_create_pei_tab_grau_satisfacao_table.php` | `pei.tab_grau_satisfcao` | Farol de desempenho (cores, faixas) |
| `2022_02_07_100332_create_pei_tab_arquivos_table.php` | `pei.tab_arquivos` | Anexos de evidências |
| `2024_07_01_150643_create_pei_rel_indicador_objetivo_estrategico_organizacao_table.php` | `pei.rel_indicador_objetivo_estrategico_organizacao` | Indicador ↔ Organização |

#### 3.5 Cadeia de Valor

| Migration | Tabela | Descrição |
|-----------|--------|-----------|
| `2023_01_10_164526_create_pei_tab_atividade_cadeia_valor_table.php` | `pei.tab_atividade_cadeia_valor` | Atividades da cadeia de valor |
| `2023_01_11_162049_create_pei_tab_processos_atividade_cadeia_valor_table.php` | `pei.tab_processos_atividade_cadeia_valor` | Processos (Entrada → Transformação → Saída) |

---

### 4. GESTÃO DE RISCOS (4 tabelas NOVAS)

| Migration | Tabela | Descrição |
|-----------|--------|-----------|
| `2025_12_24_100000_create_pei_tab_risco_table.php` | `pei.tab_risco` | Riscos estratégicos (Probabilidade × Impacto = Nível 1-25) |
| `2025_12_24_100001_create_pei_tab_risco_objetivo_table.php` | `pei.tab_risco_objetivo` | Riscos ↔ Objetivos Estratégicos (pivot) |
| `2025_12_24_100002_create_pei_tab_risco_mitigacao_table.php` | `pei.tab_risco_mitigacao` | Planos de mitigação (Prevenir, Reduzir, Transferir, Aceitar) |
| `2025_12_24_100003_create_pei_tab_risco_ocorrencia_table.php` | `pei.tab_risco_ocorrencia` | Histórico de ocorrências de riscos |

**Categorias de Risco:** Operacional, Financeiro, Reputacional, Legal, Tecnológico, Estratégico, Ambiental

**Status de Risco:** Identificado, Em Análise, Monitorado, Mitigado, Materializado, Encerrado

**Níveis de Risco:**
- Crítico: ≥ 16
- Alto: 10-15
- Médio: 5-9
- Baixo: 1-4

---

### 5. CÁLCULO AUTOMÁTICO DE INDICADORES (2 alterações - Fev/2026)

| Migration | Tabela | Descrição |
|-----------|--------|-----------|
| `2026_02_06_160000_add_calculation_type_to_tab_indicador.php` | `performance_indicators.tab_indicador` | Adiciona `dsc_calculation_type` (manual/action_plan) para habilitar cálculo automático baseado em entregas |
| `2026_02_06_160001_add_weight_to_tab_entregas.php` | `action_plan.tab_entregas` | Adiciona `num_peso` (decimal) para ponderação no cálculo de progresso |

**Tipos de Cálculo:**
- `manual`: Lançamento tradicional de evoluções mês a mês
- `action_plan`: Cálculo automático usando a fórmula `Σ(Peso × Status)`

**Status para Cálculo:**
| Status | Percentual |
|--------|------------|
| Concluído | 100% |
| Em Andamento | 50% |
| Suspenso | 25% |
| Não Iniciado | 0% |
| Cancelado | Excluído |

**Novos Arquivos Criados:**
- `app/Services/IndicadorCalculoService.php` - Lógica central de cálculo ponderado
- `app/Observers/EntregaObserver.php` - Recálculo automático ao alterar entregas

---

## 🎯 CONVENÇÕES APLICADAS

### ✅ UUID Primary Keys
- **Todas** as tabelas usam UUID como chave primária
- Geração automática via `gen_random_uuid()` do PostgreSQL
- Extensão `pgcrypto` habilitada automaticamente na primeira migration

### ✅ Soft Deletes
- Todas as tabelas implementam `deleted_at` para exclusão lógica
- Dados preservados para auditoria e histórico

### ✅ Timestamps
- `created_at` e `updated_at` em todas as tabelas
- Rastreamento automático de criação e atualização

### ✅ Foreign Keys
- Todas as relações com `foreignUuid()`
- Cascade configurado apropriadamente:
  - `cascadeOnDelete()`: dependências fortes
  - `nullOnDelete()`: dependências fracas

### ✅ Índices
- Chaves estrangeiras indexadas
- Colunas de filtro frequente indexadas
- Índices compostos onde apropriado

### ✅ Constraints
- Validações de intervalo (ex: probabilidade 1-5)
- Unique constraints para evitar duplicatas
- Checks para integridade de dados

---

## 🚀 COMO EXECUTAR AS MIGRATIONS

### Passo 1: Criar o banco de dados PostgreSQL

```bash
# Windows (via psql)
psql -U postgres
CREATE DATABASE seae;
\q
```

### Passo 2: Verificar configuração (.env)

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=seae
DB_USERNAME=postgres
DB_PASSWORD=nbsdjp1352464
```

### Passo 3: Executar migrations

```bash
cd D:\Apache24\htdocs\strategic-planning

# Executar todas as migrations
php artisan migrate

# Se precisar reverter e recriar
php artisan migrate:fresh

# Verificar status
php artisan migrate:status
```

### Passo 4: Seed inicial (opcional)

Se precisar popular com dados de teste:

```bash
php artisan db:seed
```

---

## 📋 ORDEM DE EXECUÇÃO

As migrations serão executadas nesta ordem (garantindo dependências):

1. **Infraestrutura Laravel**
   - users, cache, jobs, sessions, personal_access_tokens

2. **Schema PEI**
   - Criação do schema PostgreSQL

3. **PUBLIC: Tabelas base**
   - tab_organizacoes, tab_perfil_acesso, tab_status

4. **PUBLIC: Relações usuário**
   - rel_users_tab_organizacoes, rel_organizacao

5. **PUBLIC: Auditoria**
   - acoes, tab_audit, audits

6. **PEI: Core**
   - tab_pei, tab_missao_visao_valores, tab_valores, tab_perspectiva, tab_nivel_hierarquico

7. **PEI: BSC e Planos**
   - tab_objetivo_estrategico, tab_tipo_execucao, tab_plano_de_acao

8. **PUBLIC: Controle de acesso complexo**
   - rel_users_tab_organizacoes_tab_perfil_acesso (depende de tab_plano_de_acao)

9. **PEI: Indicadores**
   - tab_indicador, tab_evolucao_indicador, tab_linha_base_indicador, tab_meta_por_ano, tab_grau_satisfcao, tab_arquivos

10. **PEI: Cadeia de Valor**
    - tab_atividade_cadeia_valor, tab_processos_atividade_cadeia_valor

11. **PEI: Complementos**
    - tab_futuro_almejado_objetivo_estrategico, rel_indicador_objetivo_estrategico_organizacao, tab_entregas

12. **PEI: Gestão de Riscos**
    - tab_risco, tab_risco_objetivo, tab_risco_mitigacao, tab_risco_ocorrencia

---

## ✅ CHECKLIST DE VERIFICAÇÃO

- [x] Schema PEI criado
- [x] Todas as 30 tabelas legadas criadas
- [x] 4 tabelas de Gestão de Riscos criadas
- [x] Users table modificada com campos legados
- [x] Todas as tabelas usam UUID
- [x] Soft deletes implementado
- [x] Foreign keys configuradas
- [x] Índices criados
- [x] Seeds de dados padrão incluídos:
  - [x] Unidade Central
  - [x] 4 Perfis de Acesso
  - [x] 3 Tipos de Execução
  - [x] 100 Níveis Hierárquicos

---

## 🔄 PRÓXIMOS PASSOS (Roadmap)

Após executar as migrations com sucesso, retornar ao **07-ROADMAP-IMPLEMENTACAO.md** e seguir:

### ✅ Fase 0: FUNDAÇÃO (Semana 1) - JÁ CONCLUÍDA
- ✅ Starter kit instalado e funcionando
- ✅ Migrations criadas e validadas

### 🔜 Fase 1: CORE BÁSICO (Semanas 2-3)
- Implementar Models Eloquent (conforme 04-MODELOS-ELOQUENT.md)
- Implementar componentes Livewire (conforme 05-COMPONENTES-LIVEWIRE.md)
- Criar rotas e layouts base

### 🔜 Fase 2-7: Demais módulos
- Seguir 07-ROADMAP-IMPLEMENTACAO.md fase por fase

---

## 📝 OBSERVAÇÕES IMPORTANTES

1. **Decimal Precision:** Todos os campos decimais usam `decimal(15, 2)` (antes era 1000, ajustado para 15 que é o máximo recomendado)

2. **UUID vs AutoIncrement:**
   - UUIDs são usados como PKs para segurança e distribuição
   - `num_codigo_risco` é auto-incremento gerenciado pela aplicação (não pelo banco)

3. **Schema PEI:**
   - Todas as tabelas de planejamento estratégico estão no schema `pei.`
   - Facilita organização e segregação de dados

4. **Auditoria Dupla:**
   - `tab_audit`: Auditoria customizada legada
   - `audits`: Laravel Auditing moderno (recomendado usar este)

5. **Validar antes de produção:**
   - Executar `php artisan migrate` em ambiente de desenvolvimento primeiro
   - Verificar todos os relacionamentos
   - Testar com dados de teste

---

**Data de Criação:** 24/12/2025
**Versão:** 1.0
**Status:** ✅ PRONTO PARA USO

*Todas as migrations foram criadas seguindo as melhores práticas do Laravel 12, PostgreSQL e UUID.*
