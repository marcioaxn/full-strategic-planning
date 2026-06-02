# Análise de Requisitos por Módulo

> **Artefato extraído** do Documento Mestre de Evolução do Sistema PEI (`documentacao/documento-mestre-evolucao-sistema-pei.md`, v1.0 · 2026-05-30).

## 8.1 Módulo: Portal de Módulos (Home)

**Requisitos funcionais:**
- RF01: Exibir os 3 módulos GPPEI com status de progresso calculado pelo `PeiGuidanceService`
- RF02: Exibir nome e período do ciclo PEI vigente
- RF03: Cada módulo tem link de acesso rápido ao primeiro passo pendente
- RF04: Exibir indicadores resumidos: total de objetivos, indicadores ativos, planos em andamento
- RF05: Botão de acesso ao GPPEI PDF

**Requisitos não funcionais:**
- RNF01: A página deve carregar em menos de 2 segundos (sem queries pesadas)
- RNF02: Deve ser responsiva para tablets

**Dados necessários:** `PeiGuidanceService` (já existe), dados agregados de objetivos/indicadores/planos

---

## 8.2 Módulo: Inaugurar e Integrar

**Requisitos funcionais:**
- RF06: CRUD de "Planejamento do Planejamento" vinculado ao `cod_pei`
- RF07: CRUD de integrações com instrumentos de governo (PPA/LOA/ODS/Setoriais)
- RF08: Campo de cronograma de reuniões (simples: lista de eventos com data e descrição)
- RF09: Checklist de itens do Módulo 01 do GPPEI

**Banco de dados — novas tabelas sugeridas:**
```sql
-- strategic_planning schema
tab_inaugurar_pei (cod_inaugurar, cod_pei, equipe, diretrizes, metodologia, observacoes, created_at, updated_at)
tab_integracao_instrumentos (cod_integracao, cod_pei, tipo_instrumento, pontos_atencao, tarefas, intensidade, created_at, updated_at)
tab_calendario_eventos_pei (cod_evento, cod_pei, titulo, objetivo, data_evento, participantes, tipo_evento, created_at, updated_at)
```

---

## 8.3 Módulo: Cadeia de Valor (melhoria)

**Requisitos funcionais:**
- RF10: Tela dedicada `/pei/cadeia-valor` com diagrama visual
- RF11: Distinção visual entre atividades finalísticas e de suporte
- RF12: CRUD de atividades e processos (tabelas existentes: `tab_atividade_cadeia_valor` e `tab_processos_atividade_cadeia_valor`)
- RF13: Exportação do diagrama como PDF

**Banco de dados:** usar tabelas existentes, verificar se há campos faltantes

---

## 8.4 Módulo: Análise Ambiental (melhoria + extensão)

**Requisitos funcionais:**
- RF14: SWOT por quadrante com múltiplos itens (usando `tab_analise_ambiental` existente)
- RF15: Classificação GUT (Gravidade 1-5, Urgência 1-5, Tendência 1-5) para cada item SWOT
- RF16: PESTEL com 6 dimensões e múltiplos itens por dimensão
- RF17: Análise de Partes Interessadas com matriz Interesse × Influência

**Banco de dados — extensão sugerida:**
```sql
-- Adicionar coluna à tab_analise_ambiental existente:
ALTER TABLE public.tab_analise_ambiental ADD COLUMN num_gravidade smallint, ADD COLUMN num_urgencia smallint, ADD COLUMN num_tendencia smallint;

-- Nova tabela:
tab_partes_interessadas (cod_parte, cod_pei, cod_organizacao, nom_parte, tipo_parte, num_interesse, num_influencia, estrategia_engajamento, created_at, updated_at, deleted_at)
```

---

## 8.5 Módulo: Indicadores (correção UX crítica)

**Requisitos funcionais:**
- RF18: Botão "Lançar Evolução" visível na listagem (`indicadores.index`)
- RF19: Modal de lançamento acessível via botão na listagem E na tela de detalhe
- RF20: Modal pré-preenche valor previsto da `tab_meta_por_ano` para o período atual
- RF21: Validação SMART nos campos de meta do indicador

**Impacto:** Apenas UX — sem alterações de banco

---

## 8.6 Módulo: Planos de Ação e Entregas (extensão)

**Requisitos funcionais:**
- RF22: Aba "Modelo Lógico" no formulário de Plano de Ação
- RF23: Aba/expansão "5W2H" no formulário de Entrega
- RF24: Componente RACI no detalhe do Plano de Ação
- RF25: Link "Ver Entregas" visível no card e detalhe do Plano de Ação
- RF26: Tela "Minhas Entregas" filtrando por usuário responsável

**Banco de dados — extensão sugerida:**
```sql
-- Adicionar ao tab_plano_de_acao:
ALTER TABLE action_plan.tab_plano_de_acao ADD COLUMN json_modelo_logico jsonb;

-- Adicionar ao tab_entregas:
ALTER TABLE action_plan.tab_entregas ADD COLUMN json_5w2h jsonb;

-- Nova tabela:
action_plan.tab_raci (cod_raci, cod_plano_de_acao, cod_entrega, user_id, papel varchar(1), created_at, updated_at)
```

---

## 8.7 Módulo: RAE — Revisão e Avaliação da Estratégia (novo)

**Requisitos funcionais:**
- RF27: CRUD de registros de RAE vinculados ao `cod_pei`
- RF28: Para cada RAE: campos de período, status dos objetivos, destaques, problemas, encaminhamentos
- RF29: Geração de relatório PDF da RAE
- RF30: Histórico de RAEs do ciclo PEI

**Banco de dados — nova tabela:**
```sql
strategic_planning.tab_rae (
  cod_rae uuid DEFAULT gen_random_uuid() PRIMARY KEY,
  cod_pei uuid NOT NULL REFERENCES strategic_planning.tab_pei(cod_pei),
  cod_organizacao uuid NOT NULL REFERENCES organization.tab_organizacoes(cod_organizacao),
  dte_referencia date NOT NULL,
  txt_destaques_positivos text,
  txt_problemas_identificados text,
  txt_encaminhamentos text,
  dte_reuniao date,
  json_participantes jsonb,
  num_progresso_geral numeric(5,2),
  created_at timestamp,
  updated_at timestamp,
  deleted_at timestamp
)
```

---

## 8.8 Módulo: Viewer e Links do GPPEI

**Requisitos funcionais:**
- RF31: Rota `/documentos/gppei` serve o PDF via viewer embutido (PDF.js ou iframe)
- RF32: Cada tela principal tem ícone/botão "Ver no GPPEI" que abre o viewer na página correta
- RF33: Tabela de mapeamento módulo → página do PDF armazenada em `public.system_settings` ou arquivo de configuração

**Mapeamento módulo → página do GPPEI:**

| Módulo do sistema | Página no GPPEI |
|---|---|
| Inaugurar/Planejar o Planejamento | 10 |
| Integração com Instrumentos | 14 |
| Cadeia de Valor | 24 |
| Análise Ambiental (geral) | 26 |
| Análise SWOT | 66 |
| Análise PESTEL | 70 |
| Referencial Estratégico | 29 |
| Mapa Estratégico | 30 |
| Métricas / Indicadores | 31 |
| Metas SMART | 77 |
| Carteira de Projetos / Planos | 32 |
| Modelo Lógico | 86 |
| 5W2H | 116 |
| RACI | 120 |
| Monitoramento | 42 |
| Avaliação da Estratégia | 46 |
| RAE | 138 |
| Comunicação | 48 |
| Partes Interessadas | 89 |
| Matriz de Riscos | 93 |
| Gráfico de Gantt / Cronograma | 83 |

---

## 8.9 Gestão de Perfis de Acesso

**Requisitos funcionais:**
- RF34: Tela de gestão de perfis com tabela de permissões
- RF35: Atribuição de perfis a usuários por organização
- RF36: Função de impersonate para o Administrador Geral

**Perfis a formalizar:**

| Perfil | Código | Permissões |
|---|---|---|
| Administrador Geral | `admin_geral` | CRUD total + impersonate + configurações |
| Gestor PEI | `gestor_pei` | CRUD de todos os módulos estratégicos |
| Gestor com Edição | `gestor_edicao` | CRUD de planos e entregas da sua org |
| Visualizador | `visualizador` | Somente leitura em todos os módulos |
