# ANÁLISE E ESCOPO DO PROJETO
## Sistema de Planejamento Estratégico - Modernização

**Versão:** 1.0
**Data:** 23/12/2025
**Stack:** Laravel 12 + Livewire 3 + Bootstrap 5 + PostgreSQL
**Desenvolvedor:** Solo

---

## 1. CONTEXTO E OBJETIVO

### 1.1 Situação Atual

O cliente possui um **sistema legado de planejamento estratégico** funcionando em produção, com:

- ✅ Banco de dados PostgreSQL sólido e bem estruturado
- ✅ Dados históricos importantes preservados
- ✅ Utilizado por uma grande organização
- ❌ Interface desatualizada
- ❌ Funcionalidades limitadas
- ❌ Baixa usabilidade

### 1.2 Objetivo do Projeto

**Modernizar completamente o sistema** mantendo 100% de compatibilidade com o banco de dados legado, utilizando:

- Laravel 12 (framework backend)
- Livewire 3 (reatividade)
- Bootstrap 5 (UI/UX)
- Alpine.js (interatividade)
- PostgreSQL (banco existente)

### 1.3 Restrições e Premissas

**CRÍTICO:**
- ✅ **NÃO alterar estrutura do banco de dados existente**
- ✅ **Manter todos os dados históricos intactos**
- ✅ **Adicionar novas tabelas conforme a necessidade e sempre observando o padrão utilizado de nomeclatura de tabelas e colunas, sempre utilizar o tipo UUID como chave primária**
- ✅ **Foco em interface e experiência do usuário**

---

## 2. ANÁLISE DO BANCO DE DADOS LEGADO

### 2.1 Schema PUBLIC (Gestão de Acesso)

| Tabela | Descrição | Status |
|--------|-----------|--------|
| `tab_organizacoes` | Estrutura hierárquica de unidades | ✅ OK |
| `users` | Usuários do sistema | ✅ OK |
| `tab_perfil_acesso` | 4 perfis: Super Admin, Admin Unidade, Gestor Responsável, Gestor Substituto | ✅ OK |
| `rel_users_tab_organizacoes` | Usuário ↔ Organização | ✅ OK |
| `rel_users_tab_organizacoes_tab_perfil_acesso` | Permissões granulares | ✅ OK |
| `rel_organizacao` | Relacionamentos adicionais entre organizações | ✅ OK |
| `acoes` | Log simplificado de ações | ✅ OK |
| `tab_audit` | Auditoria detalhada customizada | ✅ OK |
| `audits` | Auditoria Laravel (owen-it/laravel-auditing) | ✅ OK |
| `sessions` | Sessões de usuários | ✅ OK |
| `password_resets` | Reset de senha | ✅ OK |
| `failed_jobs` | Fila de jobs | ✅ OK |
| `personal_access_tokens` | Tokens API (Sanctum) | ✅ OK |
| `tab_status` | Domínio de status | ✅ OK |

### 2.2 Schema PEI (Planejamento Estratégico Institucional)

| Tabela | Descrição | Status |
|--------|-----------|--------|
| `tab_pei` | Ciclos de planejamento (ex: PEI 2024-2028) | ✅ OK |
| `tab_missao_visao_valores` | Missão e Visão da organização | ✅ OK |
| `tab_valores` | Valores organizacionais (separados) | ✅ OK |
| `tab_futuro_almejado_objetivo_estrategico` | Futuro almejado por objetivo | ✅ OK |
| `tab_perspectiva` | 4 Perspectivas BSC | ✅ OK |
| `tab_nivel_hierarquico` | 100 níveis de hierarquia (1-100) | ✅ OK |
| `tab_objetivo_estrategico` | Objetivos estratégicos por perspectiva | ✅ OK |
| `tab_tipo_execucao` | Ação / Iniciativa / Projeto | ✅ OK |
| `tab_plano_de_acao` | Planos de ação (ações, iniciativas, projetos) | ✅ OK |
| `tab_entregas` | Entregas dos planos de ação | ✅ OK |
| `tab_indicador` | Indicadores (KPIs) | ✅ OK |
| `tab_evolucao_indicador` | Evolução mensal dos indicadores | ✅ OK |
| `tab_linha_base_indicador` | Linha de base (baseline) | ✅ OK |
| `tab_meta_por_ano` | Metas anuais | ✅ OK |
| `rel_indicador_objetivo_estrategico_organizacao` | Indicador ↔ Organização | ✅ OK |
| `tab_grau_satisfacao` | Farol de desempenho (cores e faixas) | ✅ OK |
| `tab_arquivos` | Anexos de evidências | ✅ OK |
| `tab_atividade_cadeia_valor` | Atividades da cadeia de valor | ✅ OK |
| `tab_processos_atividade_cadeia_valor` | Processos (entrada → transformação → saída) | ✅ OK |

### 2.3 Funcionalidades Existentes (Já no Banco)

Com base nas tabelas existentes, o sistema legado já suporta:

#### ✅ Módulo de Gestão Organizacional
- Hierarquia de organizações (pai ↔ filhas)
- Múltiplos níveis de estrutura

#### ✅ Módulo de Identidade Estratégica
- Missão
- Visão
- Valores (múltiplos)
- Futuro Almejado por Objetivo

#### ✅ Módulo de Balanced Scorecard (BSC)
- 4 Perspectivas padrão
- Objetivos estratégicos por perspectiva
- Hierarquia de objetivos (nível 1-100)

#### ✅ Módulo de Planos de Ação
- Classificação em: Ação / Iniciativa / Projeto
- Vinculação com Objetivos Estratégicos
- Vinculação com Organizações
- Datas de início e fim
- Orçamento previsto
- Status
- Integração com PPA e LOA (planejamento governamental)
- Entregas com periodicidade

#### ✅ Módulo de Indicadores (KPIs)
- Indicadores de Objetivos Estratégicos
- Indicadores de Planos de Ação
- Linha de base
- Metas anuais
- Evolução mensal (previsto vs. realizado)
- Unidade de medida
- Fórmula de cálculo
- Fonte de dados
- Periodicidade
- Peso
- Acumulado (sim/não)
- Observações e avaliações
- Anexos de evidências

#### ✅ Módulo de Cadeia de Valor
- Atividades por perspectiva
- Processos (Entrada → Transformação → Saída)

#### ✅ Módulo de Auditoria e Segurança
- Log detalhado de alterações (antes/depois)
- Rastreabilidade completa
- IP e timestamp
- Integração com pacote Laravel Auditing

#### ✅ Módulo de Controle de Acesso
- 4 perfis pré-definidos
- Permissões granulares por organização e plano de ação
- Gestores responsáveis e substitutos

---

## 3. FUNCIONALIDADES PROPOSTAS NO TEXTO INICIAL

### 3.1 Funcionalidades que JÁ EXISTEM no Banco

| Funcionalidade Solicitada | Existe no Banco? | Tabela Correspondente |
|---------------------------|------------------|----------------------|
| Missão, Visão, Valores | ✅ SIM | `tab_missao_visao_valores`, `tab_valores` |
| Objetivos Estratégicos | ✅ SIM | `tab_objetivo_estrategico` |
| Perspectivas BSC | ✅ SIM | `tab_perspectiva` |
| Planos de Ação | ✅ SIM | `tab_plano_de_acao` |
| Indicadores (KPIs) | ✅ SIM | `tab_indicador` |
| Evolução de Indicadores | ✅ SIM | `tab_evolucao_indicador` |
| Cadeia de Valor | ✅ SIM | `tab_atividade_cadeia_valor` |
| Auditoria | ✅ SIM | `tab_audit`, `audits` |
| Hierarquia Organizacional | ✅ SIM | `tab_organizacoes` |

### 3.2 Funcionalidades que NÃO EXISTEM no Banco

| Funcionalidade Solicitada | Existe? | Necessidade |
|---------------------------|---------|-------------|
| Análise SWOT | ❌ NÃO | **OPCIONAL** - Pode ser módulo adicional |
| Análise PESTEL | ❌ NÃO | **OPCIONAL** - Pode ser módulo adicional |
| Canvas de Modelo de Negócio | ❌ NÃO | **OPCIONAL** - Pode ser módulo adicional |
| 5 Forças de Porter | ❌ NÃO | **OPCIONAL** - Pode ser módulo adicional |
| Matriz BCG | ❌ NÃO | **OPCIONAL** - Pode ser módulo adicional |
| Gestão de Riscos | ❌ NÃO | **OPCIONAL** - Pode ser módulo adicional |
| Comentários/Discussões | ❌ NÃO | **RECOMENDADO** - Facilita colaboração |
| Notificações | ❌ NÃO | **RECOMENDADO** - Alertas de prazos e desvios |

---

## 4. DECISÃO DE ESCOPO

### 4.1 Escopo CORE (Prioridade ALTA)

**Modernizar interface das funcionalidades existentes:**

1. **Gestão de Ciclos de Planejamento (PEI)**
   - Criar, editar, visualizar ciclos
   - Ativar/desativar ciclos

2. **Identidade Estratégica**
   - CRUD de Missão, Visão
   - CRUD de Valores (múltiplos)
   - CRUD de Futuro Almejado

3. **Balanced Scorecard (BSC)**
   - Gestão de Perspectivas
   - CRUD de Objetivos Estratégicos
   - Hierarquia e ordenação
   - Vinculação com análises

4. **Planos de Ação**
   - CRUD completo
   - Classificação (Ação/Iniciativa/Projeto)
   - Gestão de Entregas
   - Gestão de responsáveis

5. **Indicadores (KPIs)**
   - CRUD de indicadores
   - Lançamento de evolução mensal
   - Gestão de linha de base
   - Gestão de metas anuais
   - Anexos de evidências
   - Farol de desempenho (cores)

6. **Cadeia de Valor**
   - Gestão de atividades
   - Gestão de processos (E→T→S)

7. **Dashboards Executivos**
   - Visão consolidada por organização
   - Gráficos de evolução
   - KPIs críticos
   - Mapa Estratégico visual

8. **Relatórios**
   - Exportação PDF
   - Filtros por período
   - Comparativos

9. **Gestão de Usuários e Permissões**
   - CRUD de usuários
   - Atribuição de organizações
   - Atribuição de perfis
   - Gestão de responsáveis

10. **Auditoria**
    - Visualização de logs
    - Filtros e buscas
    - Timeline de alterações

### 4.2 Escopo ADICIONAL (Prioridade MÉDIA - Fase 2)

**Funcionalidades novas que agregam valor:**

1. **Sistema de Comentários**
   - Permitir discussões em objetivos, planos, indicadores
   - Menções de usuários (@usuario)

2. **Sistema de Notificações**
   - Alertas de prazos próximos
   - Alertas de desvios de meta
   - Alertas de pendências de lançamento

3. **Análises Estratégicas Estruturadas (OPCIONAL)**
   - SWOT
   - PESTEL
   - Canvas
   - Porter
   - BCG

4. **API REST**
   - Integração com outros sistemas
   - Mobile app (futuro)

### 4.3 Escopo QUE ESTAVA FORA, MAS AGORA É NECESSÁRIO IMPLEMENTAR

**Módulo de Gestão de Riscos:**
- Identificação de riscos estratégicos
- Classificação de riscos (operacional, financeiro, reputacional, legal, etc.)
- Avaliação de probabilidade e impacto
- Matriz de riscos (Probabilidade x Impacto)
- Planos de mitigação e contingência
- Monitoramento de riscos
- Relatórios e dashboards de riscos
- Vinculação de riscos com objetivos estratégicos
- Histórico de ocorrências

### 4.4 Escopo FORA (Não será implementado agora)

- IA/Machine Learning para previsões
- Integrações com ERPs externos
- Mobile app nativo

---

## 5. ESTRATÉGIA DE IMPLEMENTAÇÃO

### 5.1 Abordagem

**Desenvolvimento Incremental por Módulos:**

1. ✅ **Fase 0 - Fundação (JÁ CONCLUÍDA)**
   - ✅ Configuração Laravel 12 + Jetstream + Livewire 3
   - ✅ Configuração Bootstrap 5 com starter kit moderno
   - ✅ Estrutura de autenticação
   - ✅ Conexão com banco legado
   - ✅ Layout base responsivo
   - **AÇÃO ATUAL:** Validação profunda do starter kit para confirmar que tudo está funcionando corretamente

2. ✅ **Fase 1 - Core Básico** (2 semanas)
   - Gestão de Organizações
   - Gestão de Usuários
   - Dashboard inicial
   - Navegação entre organizações

3. ✅ **Fase 2 - Identidade e BSC** (2 semanas)
   - CRUD de Missão/Visão/Valores
   - CRUD de Perspectivas
   - CRUD de Objetivos Estratégicos
   - Visualização de Mapa Estratégico

4. ✅ **Fase 3 - Planos de Ação** (2 semanas)
   - CRUD de Planos de Ação
   - Gestão de Entregas
   - Gestão de Responsáveis

5. ✅ **Fase 4 - Indicadores** (3 semanas)
   - CRUD de Indicadores
   - Lançamento de evolução
   - Linha de base e metas
   - Farol de desempenho
   - Anexos

6. ✅ **Fase 5 - Dashboards e Relatórios** (2 semanas)
   - Dashboards executivos
   - Gráficos Chart.js
   - Relatórios PDF
   - Exportações

7. 🆕 **Fase 6 - Gestão de Riscos** (2 semanas)
   - CRUD de Riscos Estratégicos
   - Classificação de riscos (tipo, categoria)
   - Matriz de Probabilidade x Impacto
   - Planos de Mitigação e Contingência
   - Vinculação com Objetivos Estratégicos
   - Dashboard de Riscos
   - Histórico de ocorrências
   - Relatórios de riscos

8. ✅ **Fase 7 - Refinamentos** (1 semana)
   - Auditoria e logs
   - Performance
   - Testes
   - Documentação

### 5.2 Prazo Total Estimado

**14-16 semanas** para escopo COMPLETO incluindo Gestão de Riscos (considerando desenvolvedor solo)

---

## 6. PRÓXIMOS ARTEFATOS

Com base nesta análise, os próximos documentos serão:

1. ✅ **02-REQUISITOS-FUNCIONAIS.md** - Requisitos detalhados por módulo
2. ✅ **03-REQUISITOS-NAO-FUNCIONAIS.md** - Performance, segurança, usabilidade
3. ✅ **04-MODELOS-ELOQUENT.md** - Models completos com relacionamentos
4. ✅ **05-COMPONENTES-LIVEWIRE.md** - Lista de componentes a criar
5. ✅ **06-MIGRATIONS-NOVAS.md** - Migrations para tabelas adicionais (comentários, notificações)
6. ✅ **07-ESTRUTURA-PASTAS.md** - Organização de arquivos
7. ✅ **08-ROADMAP-IMPLEMENTACAO.md** - Cronograma detalhado
8. ✅ **09-GUIA-DESENVOLVIMENTO.md** - Padrões e boas práticas
9. ✅ **10-CASOS-DE-USO.md** - Fluxos principais do sistema

---

## 7. RESUMO EXECUTIVO

| Aspecto | Decisão |
|---------|---------|
| **Banco de Dados** | Usar 100% o banco legado, apenas adicionar tabelas opcionais |
| **Stack** | Laravel 12 + Livewire 3 + Bootstrap 5 + PostgreSQL |
| **Estratégia** | Desenvolvimento incremental por módulos |
| **Prioridade** | Modernizar interface das funcionalidades existentes |
| **Prazo Estimado** | 12-14 semanas (escopo CORE) |
| **Desenvolvedor** | Solo (1 pessoa) |
| **Ambiente** | Web responsivo (desktop e tablet) |

---

**Conclusão:** O foco deve ser em criar uma interface moderna e intuitiva para as funcionalidades que já existem no banco de dados legado, garantindo que o CEO e os usuários tenham uma experiência significativamente melhor do que o sistema anterior. Funcionalidades adicionais (SWOT, PESTEL, etc.) podem ser implementadas em uma segunda fase, após validação com o cliente.
