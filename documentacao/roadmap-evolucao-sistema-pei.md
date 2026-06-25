# Roadmap de Evolução — Sistema PEI
# Planejamento Estratégico Integrado

**Versão:** 1.0  
**Data de criação:** 2026-06-25  
**Baseado em:** Varredura metodológica completa realizada em 2026-06-25  
**Calibragem:** BSC (Kaplan & Norton), Hoshin Kanri, PDCA (Deming), ISO 31000:2018, GPPEI/MGI 2025, TOWS Matrix (Weihrich), Gestão Estratégica Japonesa  

---

## Como ler este documento

- **Status:** `🔴 Pendente` | `🟡 Em andamento` | `🟢 Concluído` | `⏸️ Suspenso` | `❌ Cancelado`
- **Prioridade:** `P1 — Crítico` | `P2 — Importante` | `P3 — Melhoria`
- **Impacto:** `Alto` | `Médio` | `Baixo`
- Cada item registra: o que será feito, arquivos envolvidos, critério de conclusão e valor agregado.

---

## Contexto da varredura

A varredura realizada em 2026-06-25 avaliou o sistema contra a literatura e melhores práticas mundiais de planejamento estratégico. O sistema demonstrou solidez em ~70% da implementação. Os gaps identificados concentram-se em três eixos:

1. **Integração entre módulos** — dados coletados em silos sem rastreabilidade cruzada.
2. **Formalização de ciclos** — ferramentas existem mas não formam ciclo completo (PDCA).
3. **Restrições metodológicas** — o sistema orienta mas não impede desvios da metodologia.

---

## BLOCO 1 — Correções de Alta Prioridade (P1)

### ROAD-001 — Ciclo PDCA completo: RAE deve gerar ajustes formais
**Prioridade:** P1 — Crítico  
**Status:** 🟢 Concluído — 2026-06-25  
**Impacto:** Alto  
**Referência:** PDCA (Deming, 1950); Hoshin Kanri; GPPEI/MGI 2025 Módulo 03  

#### Problema identificado
A RAE (Revisão e Avaliação da Estratégia) implementa o "Check" do ciclo PDCA, mas o "Act" — os ajustes estratégicos que a revisão gera — não retroalimenta o sistema de forma estruturada. Um problema identificado em reunião de RAE não gera novo plano de ação, não altera meta de indicador e não modifica objetivo de forma rastreável. O ciclo PDCA está aberto: Plan ✅ → Do ✅ → Check ✅ → **Act ❌**.

#### O que será feito

**Fase 1 — Modelo de dados:**
- Criar migration para tabela `strategic_planning.tab_rae_encaminhamento`:
  - `cod_encaminhamento` (PK UUID)
  - `cod_rae` (FK → `tab_rae`)
  - `dsc_tipo` (enum: 'Novo Plano', 'Revisão de Meta', 'Revisão de Objetivo', 'Revisão de Risco', 'Outro')
  - `txt_descricao` (text — descrição da ação de ajuste)
  - `dsc_status` (enum: 'Pendente', 'Em Execução', 'Concluído')
  - `cod_responsavel` (FK → `pei.users`)
  - `dte_prazo` (date)
  - `cod_plano_vinculado` (FK nullable → `action_plan.tab_plano_de_acao`)
  - timestamps + soft delete

**Fase 2 — Model e Component:**
- Criar `App\Models\StrategicPlanning\RaeEncaminhamento`
- Expandir `App\Livewire\StrategicPlanning\GerenciarRae` para incluir aba "Encaminhamentos"
- Cada encaminhamento do tipo "Novo Plano" deve permitir criar um plano de ação diretamente da RAE com link bidirecional

**Fase 3 — Dashboard:**
- Widget no Dashboard mostrando encaminhamentos pendentes de RAEs anteriores
- Alerta quando encaminhamento estiver atrasado (prazo vencido sem conclusão)

**Fase 4 — Relatório:**
- Incluir encaminhamentos no relatório executivo com status de execução

#### Arquivos envolvidos
- `database/migrations/StrategicPlanning/` (nova migration)
- `app/Models/StrategicPlanning/RaeEncaminhamento.php` (novo)
- `app/Models/StrategicPlanning/Rae.php` (adicionar relacionamento)
- `app/Livewire/StrategicPlanning/GerenciarRae.php`
- `resources/views/livewire/p-e-i/gerenciar-rae.blade.php`
- `app/Livewire/Dashboard/Index.php`

#### Critério de conclusão
Um problema identificado em reunião de RAE pode gerar encaminhamento que: tem responsável, prazo, status e — quando do tipo "Novo Plano" — cria um plano de ação vinculado ao objetivo que originou o problema.

#### Valor agregado
Fecha o ciclo PDCA. O sistema deixa de ser um repositório de diagnósticos para se tornar um motor de melhoria contínua. Sem este fechamento, a RAE é apenas uma reunião registrada — com ele, cada revisão produz ações rastreáveis que alimentam o próximo ciclo. É a diferença entre uma organização que avalia e uma que aprende.

---

### ROAD-002 — Desdobramento em cascata de objetivos (Hoshin Kanri)
**Prioridade:** P1 — Crítico  
**Status:** 🟢 Concluído — 2026-06-25  
**Impacto:** Alto  
**Referência:** Hoshin Kanri (Toyota, 1965); BSC (Kaplan & Norton, 2004 — Strategy Maps)  

#### Problema identificado
Não existe vínculo objetivo-pai → objetivo-filho. Um objetivo estratégico de nível corporativo não se desdobra em objetivos táticos ou operacionais. Isso impede rastrear se as equipes e unidades estão alinhadas com a direção estratégica definida pela liderança. No Hoshin Kanri, este processo de desdobramento e negociação é chamado de "catchball" — a estratégia desce e sobe em iterações até haver consenso e alinhamento.

#### O que será feito

**Fase 1 — Modelo de dados:**
- Adicionar coluna `cod_objetivo_pai` (FK nullable self-referencing) em `strategic_planning.tab_objetivo`
- Adicionar coluna `num_nivel_desdobramento` (smallint, default 1) para identificar a profundidade da cascata
- Nova migration (nunca alterar migration existente)

**Fase 2 — Model:**
- Adicionar em `App\Models\StrategicPlanning\Objetivo`:
  - Relacionamento `objetivoPai()` (BelongsTo self)
  - Relacionamento `objetivosFilhos()` (HasMany self)
  - Método `calcularAtingimentoCascata()` — considera atingimento dos filhos no cálculo do pai
  - Scope `raiz()` — objetivos sem pai (nível 1)

**Fase 3 — Componente Livewire:**
- Expandir `ListarObjetivos` para exibir árvore de desdobramento
- Ao criar objetivo, permitir selecionar objetivo-pai (opcional)
- Visualização em modo árvore (accordion por perspectiva → objetivo raiz → filhos)

**Fase 4 — Mapa Estratégico:**
- Exibir indicação visual de objetivos com filhos desdobrados
- Link "Ver desdobramento" abrindo árvore completa

#### Arquivos envolvidos
- `database/migrations/StrategicPlanning/` (nova migration)
- `app/Models/StrategicPlanning/Objetivo.php`
- `app/Livewire/StrategicPlanning/ListarObjetivos.php`
- `resources/views/livewire/p-e-i/listar-objetivos.blade.php`
- `app/Livewire/StrategicPlanning/MapaEstrategico.php`

#### Critério de conclusão
Um objetivo estratégico de nível 1 pode ter N objetivos filhos. O atingimento do objetivo pai considera os filhos. O mapa estratégico identifica visualmente quais objetivos possuem desdobramento.

#### Valor agregado
Implementa o conceito central do Hoshin Kanri: alinhamento vertical. A estratégia da alta direção desce formalmente até as unidades executoras, e o progresso sobe rastreável. Elimina o gap entre "o que foi planejado no topo" e "o que está sendo executado na base" — causa número 1 de fracasso em planejamento estratégico segundo Kaplan & Norton (2004).

---

## BLOCO 2 — Melhorias Importantes (P2)

### ROAD-003 — Estratégias de resposta a riscos (ISO 31000:2018)
**Prioridade:** P2 — Importante  
**Status:** 🔴 Pendente  
**Impacto:** Médio  
**Referência:** ISO 31000:2018 — Gestão de Riscos; ERM (COSO, 2017)  

#### Problema identificado
O sistema implementa identificação, análise (probabilidade × impacto) e avaliação de riscos corretamente. Porém faltam as 4 estratégias canônicas de resposta definidas pela ISO 31000 e pelo COSO ERM: **Mitigar** (reduzir probabilidade/impacto), **Evitar** (eliminar a causa), **Transferir** (seguro, terceirização), **Aceitar** (registrar e monitorar). O campo `dsc_status` atual ('Identificado', 'Em Monitoramento', 'Mitigado', 'Encerrado') mistura estado com estratégia, gerando ambiguidade.

#### O que será feito

**Fase 1 — Modelo de dados:**
- Adicionar coluna `dsc_estrategia_resposta` (enum: 'Mitigar', 'Evitar', 'Transferir', 'Aceitar') em `risk_management.tab_risco`
- Adicionar coluna `txt_justificativa_estrategia` (text nullable) — obrigatória quando estratégia = 'Aceitar'
- Adicionar coluna `dte_proxima_revisao` (date nullable) — prazo para reavaliar o risco
- Nova migration

**Fase 2 — Model e validação:**
- Atualizar `App\Models\RiskManagement\Risco` com constantes de estratégia
- Atualizar `ListarRiscos` — validação: quando `dsc_estrategia_resposta = 'Aceitar'`, `txt_justificativa_estrategia` é obrigatória
- Adicionar método `estaVencido()` — retorna true quando `dte_proxima_revisao` < hoje

**Fase 3 — Integração com RAE:**
- Na RAE, exibir riscos com revisão vencida como alerta automático
- Widget no Dashboard: "Riscos com revisão pendente"

**Fase 4 — Matriz de riscos:**
- Atualizar `MatrizRiscos` para filtrar por estratégia de resposta
- Exibir badge com estratégia em cada célula da matriz

#### Arquivos envolvidos
- `database/migrations/RiskManagement/` (nova migration)
- `app/Models/RiskManagement/Risco.php`
- `app/Livewire/RiskManagement/ListarRiscos.php`
- `app/Livewire/RiskManagement/MatrizRiscos.php`
- `resources/views/livewire/risco/`
- `app/Livewire/StrategicPlanning/GerenciarRae.php`

#### Critério de conclusão
Todo risco tem estratégia de resposta declarada. Riscos com estratégia 'Aceitar' exigem justificativa. Riscos com `dte_proxima_revisao` vencida aparecem como alerta na RAE e no Dashboard.

#### Valor agregado
Alinha a gestão de riscos ao padrão ISO 31000:2018 e COSO ERM — os dois frameworks mais adotados globalmente. A distinção entre "aceitar conscientemente" e "mitigar" é a diferença entre gestão de riscos madura e registro passivo de problemas. Organizações auditadas (TCU, CGU) são cobradas exatamente nesta distinção.

---

### ROAD-004 — Índice consolidado de desempenho do PEI
**Prioridade:** P2 — Importante  
**Status:** 🔴 Pendente  
**Impacto:** Médio  
**Referência:** BSC (Kaplan & Norton — Scorecard Consolidado); IQG (Índice de Qualidade de Gestão)  

#### Problema identificado
O cálculo de desempenho está implementado ao nível de indicador e perspectiva, mas não existe um índice único que represente a saúde geral do ciclo PEI. O gestor executivo precisa de um número — como o Índice de Qualidade de Gestão (IQG) usado em empresas japonesas e no modelo de excelência da FNQ — para ter visão imediata sem navegar por cada perspectiva.

#### O que será feito

**Fase 1 — Serviço de cálculo:**
- Criar método `calcularIQG(string $codPei, int $ano, int $mes)` em `IndicadorCalculoService` (ou novo `PeiDesempenhoService`)
- Fórmula: média ponderada dos desempenhos das perspectivas pelo peso configurado em cada uma
- Considerar apenas perspectivas com pelo menos 1 indicador com evolução no período
- Retornar: `['valor' => float, 'grau' => GrauSatisfacao, 'perspectivas' => array, 'tendencia' => string]`

**Fase 2 — Dashboard:**
- Widget principal: Índice do PEI em destaque (grande, colorido pelo grau de satisfação)
- Gauge visual (semicírculo) representando 0–100%
- Histórico dos últimos 6 meses em sparkline

**Fase 3 — Relatórios:**
- Incluir IQG na capa de todos os relatórios executivos
- Seção de evolução do IQG por período

**Fase 4 — Mapa Estratégico:**
- Exibir IQG como indicador-síntese no topo do mapa

#### Arquivos envolvidos
- `app/Services/IndicadorCalculoService.php` (ou novo PeiDesempenhoService)
- `app/Livewire/Dashboard/Index.php`
- `resources/views/livewire/dashboard/index.blade.php`
- `resources/views/relatorios/executivo.blade.php`
- `app/Livewire/StrategicPlanning/MapaEstrategico.php`

#### Critério de conclusão
O Dashboard exibe um índice único de desempenho do PEI, colorido pelo grau de satisfação correspondente, com evolução histórica dos últimos 6 meses.

#### Valor agregado
Um executivo que entra no sistema deve saber em 3 segundos se a organização está no caminho certo. O IQG entrega isso. É o conceito japonês de "kanban visual" aplicado à gestão estratégica — a informação mais crítica deve ser a mais visível. Elimina a necessidade de navegar por perspectivas para ter a visão executiva.

---

### ROAD-005 — Rastreabilidade bidirecional: Plano de Ação ↔ Indicador
**Prioridade:** P2 — Importante  
**Status:** 🔴 Pendente  
**Impacto:** Médio  
**Referência:** BSC (teoria da causalidade estratégica); Gestão por Resultados  

#### Problema identificado
Um Plano de Ação é vinculado a um Objetivo, e Indicadores também são vinculados a Objetivos. Mas não existe relação direta "este plano de ação foi criado para mover este indicador específico". Impossível responder: "Quais indicadores ficam sem plano de ação que os suporte?" ou "Este plano contribui para melhorar o indicador X?". No BSC, a causalidade entre ações e resultados medidos é explícita e fundamental.

#### O que será feito

**Fase 1 — Modelo de dados:**
- Criar tabela pivot `performance_indicators.rel_indicador_plano_de_acao`:
  - `cod_indicador` (FK)
  - `cod_plano_de_acao` (FK)
  - `txt_contribuicao` (text nullable — descrição de como o plano move o indicador)
  - PK composta

**Fase 2 — Model:**
- Adicionar em `Indicador`: relacionamento `planosDeAcao()` (BelongsToMany)
- Adicionar em `PlanoDeAcao`: relacionamento `indicadores()` (BelongsToMany)
- Método em `Indicador`: `temCoberturaDePlano()` — retorna bool

**Fase 3 — Componentes:**
- Em `DetalharIndicador`: seção "Planos que suportam este indicador" com link para cada plano e % de progresso
- Em `DetalharPlano`: seção "Indicadores que este plano visa mover" com valor atual e meta
- Em `ListarIndicadores`: badge/alerta para indicadores sem nenhum plano vinculado
- Em `PeiGuidanceService`: aviso quando há indicadores sem cobertura de plano

#### Arquivos envolvidos
- `database/migrations/PerformanceIndicators/` (nova migration)
- `app/Models/PerformanceIndicators/Indicador.php`
- `app/Models/ActionPlan/PlanoDeAcao.php`
- `app/Livewire/PerformanceIndicators/DetalharIndicador.php`
- `app/Livewire/ActionPlan/DetalharPlano.php`
- `app/Livewire/PerformanceIndicators/ListarIndicadores.php`
- `app/Services/PeiGuidanceService.php`

#### Critério de conclusão
É possível visualizar, a partir de um indicador, todos os planos que o suportam. É possível visualizar, a partir de um plano, todos os indicadores que ele visa impactar. Indicadores sem cobertura de plano são sinalizados.

#### Valor agregado
Implementa a espinha dorsal do BSC: a cadeia de causalidade entre ação e resultado. Responde a pergunta que todo gestor público enfrenta em auditoria: "Como você sabe que este plano está contribuindo para este resultado?". Transforma o sistema de um repositório de dados em um sistema de gestão por causalidade.

---

### ROAD-006 — Análise de tendência temporal dos indicadores
**Prioridade:** P2 — Importante  
**Status:** 🔴 Pendente  
**Impacto:** Médio  
**Referência:** Statistical Process Control (SPC — Shewhart/Deming); Gestão da Qualidade Total (TQM)  

#### Problema identificado
O sistema registra histórico de evolução dos indicadores (`tab_evolucao_indicador`), mas não calcula tendência (crescente/decrescente/estável). Sem isso, é impossível detectar deterioração gradual antes que se torne crise. Na gestão japonesa (TQM/SPC), análise de tendência é base do controle estatístico de processos — uma variação consistente em 3 ou mais períodos é sinal de mudança de processo, não ruído aleatório.

#### O que será feito

**Fase 1 — Serviço de cálculo:**
- Adicionar método `calcularTendencia(string $codIndicador, int $ultimosMeses = 3)` em `IndicadorCalculoService`
- Algoritmo: regressão linear simples sobre os últimos N valores de `vlr_realizado`
  - Coeficiente angular > +threshold → `'Crescente'`
  - Coeficiente angular < -threshold → `'Decrescente'`
  - Entre thresholds → `'Estável'`
- Considerar polaridade do indicador para determinar se tendência é favorável ou desfavorável

**Fase 2 — Model:**
- Adicionar método `tendenciaAtual(int $meses = 3)` em `Indicador`
- Retornar: `['direcao' => string, 'favoravel' => bool, 'variacao_pct' => float]`

**Fase 3 — UI:**
- Em `ListarIndicadores`: ícone de seta (↑ verde / ↓ vermelho / → amarelo) ao lado do valor atual
- Em `DetalharIndicador`: gráfico de tendência com linha de regressão projetada
- Em `MapaEstrategico`: indicar tendência desfavorável com ícone de alerta no objetivo

**Fase 4 — Alertas:**
- Criar alerta automático quando tendência desfavorável persistir por 2+ períodos consecutivos
- Alert via `NotificationService` → `StrategicAlert`

#### Arquivos envolvidos
- `app/Services/IndicadorCalculoService.php`
- `app/Models/PerformanceIndicators/Indicador.php`
- `app/Livewire/PerformanceIndicators/ListarIndicadores.php`
- `app/Livewire/PerformanceIndicators/DetalharIndicador.php`
- `app/Livewire/StrategicPlanning/MapaEstrategico.php`
- `app/Services/NotificationService.php`

#### Critério de conclusão
Cada indicador com 3+ evoluções registradas exibe sua tendência (direção + se é favorável). Tendências desfavoráveis persistentes geram alerta automático no sistema.

#### Valor agregado
Transforma dados históricos em inteligência antecipativa. A diferença entre gestão reativa e gestão proativa está em detectar o problema enquanto ainda é tendência, não quando já é resultado consumado. É o conceito de "early warning system" que as empresas japonesas (Toyota, Honda) aplicam no chão de fábrica — adaptado para a gestão estratégica pública.

---

## BLOCO 3 — Melhorias de Maturidade (P3)

### ROAD-007 — Matriz TOWS: da análise ambiental às estratégias
**Prioridade:** P3 — Melhoria  
**Status:** 🔴 Pendente  
**Impacto:** Médio  
**Referência:** TOWS Matrix (Weihrich, 1982); SWOT Analysis (Andrews, 1971)  

#### Problema identificado
O sistema coleta SWOT e PESTEL com qualidade, mas a análise ambiental permanece desconectada dos objetivos estratégicos. A Matriz TOWS (de Weihrich, 1982 — evolução da SWOT) cruza os quadrantes para derivar estratégias: SO (usar Forças para explorar Oportunidades), WO (superar Fraquezas com Oportunidades), ST (usar Forças para neutralizar Ameaças), WT (minimizar Fraquezas frente às Ameaças). Sem este cruzamento, a análise ambiental é diagnóstico sem prescrição.

#### O que será feito

**Fase 1 — Modelo de dados:**
- Criar tabela `strategic_planning.tab_estrategia_tows`:
  - `cod_estrategia` (PK UUID)
  - `cod_pei` (FK)
  - `cod_organizacao` (FK)
  - `dsc_tipo` (enum: 'SO', 'ST', 'WO', 'WT')
  - `dsc_estrategia` (text — descrição da estratégia derivada)
  - `txt_fundamentacao` (text nullable — quais forças/fraquezas e oportunidades/ameaças embasam)
  - `cod_objetivo_vinculado` (FK nullable → tab_objetivo — objetivo que operacionaliza a estratégia)
  - timestamps + soft delete

**Fase 2 — Componente:**
- Adicionar aba "Matriz TOWS" em `AnaliseSWOT`
- Exibir matriz 2×2 com os 4 quadrantes
- Permitir registrar estratégias por quadrante
- Vincular estratégia a objetivo existente ou sinalizar necessidade de criar novo objetivo

**Fase 3 — Rastreabilidade:**
- Em `ListarObjetivos`: badge indicando quais objetivos foram derivados de estratégia TOWS
- Em `AnaliseSWOT`: indicar quais itens SWOT já foram usados em estratégias TOWS

#### Arquivos envolvidos
- `database/migrations/StrategicPlanning/` (nova migration)
- `app/Models/StrategicPlanning/EstrategiaTows.php` (novo)
- `app/Livewire/StrategicPlanning/AnaliseSWOT.php`
- `resources/views/livewire/p-e-i/analise-s-w-o-t.blade.php`
- `app/Livewire/StrategicPlanning/ListarObjetivos.php`

#### Critério de conclusão
É possível registrar estratégias por quadrante TOWS e vinculá-las a objetivos estratégicos. O sistema indica quais objetivos têm fundamentação TOWS e quais ainda não têm.

#### Valor agregado
Fecha o círculo entre diagnóstico e estratégia. A análise ambiental deixa de ser um exercício acadêmico para se tornar o fundamento explícito dos objetivos estratégicos. Em auditorias (TCU/CGU), a pergunta "Por que este objetivo foi escolhido?" terá resposta rastreável e fundamentada. É o elo que transforma análise em decisão.

---

### ROAD-008 — Bloqueios e guardrails metodológicos no fluxo
**Prioridade:** P3 — Melhoria  
**Status:** 🔴 Pendente  
**Impacto:** Médio  
**Referência:** GPPEI/MGI 2025 (sequência metodológica obrigatória)  

#### Problema identificado
O `PeiGuidanceService` orienta o usuário com avisos e progressão, mas não impede que etapas sejam puladas. Um usuário pode criar Indicadores sem ter criado Perspectivas. Pode criar Planos sem Objetivos definidos. A metodologia GPPEI define uma sequência que não é opcional — cada fase depende da anterior para ter coerência.

#### O que será feito

**Fase 1 — Mapeamento de pré-requisitos:**
- Definir matriz de pré-requisitos no `PeiGuidanceService`:
  - Perspectivas requerem: Ciclo PEI ativo + Identidade salva
  - Objetivos requerem: ao menos 2 perspectivas cadastradas
  - Indicadores requerem: ao menos 1 objetivo cadastrado
  - Planos requerem: ao menos 1 objetivo cadastrado
  - Graus de Satisfação requerem: Ciclo PEI ativo

**Fase 2 — Guards nos componentes:**
- Em cada componente, antes de abrir modal de criação, verificar pré-requisitos via `PeiGuidanceService`
- Se pré-requisito não atendido: exibir modal explicativo com link para a etapa que falta
- Mensagem clara: "Para criar Indicadores, é necessário ter pelo menos 1 Objetivo Estratégico cadastrado. [Ir para Objetivos →]"

**Fase 3 — Sinalização visual:**
- Itens da sidebar com pré-requisitos não atendidos recebem ícone de cadeado
- Tooltip: "Complete [etapa X] para desbloquear"

#### Arquivos envolvidos
- `app/Services/PeiGuidanceService.php`
- `app/Livewire/PerformanceIndicators/ListarIndicadores.php`
- `app/Livewire/ActionPlan/ListarPlanos.php`
- `app/Livewire/StrategicPlanning/ListarPerspectivas.php`
- `app/Livewire/StrategicPlanning/ListarObjetivos.php`
- `resources/views/layouts/app.blade.php`

#### Critério de conclusão
Nenhum componente permite criar registros de domínio sem que os pré-requisitos metodológicos estejam atendidos. O usuário é redirecionado com mensagem clara quando tenta acessar uma etapa prematuramente.

#### Valor agregado
Protege a integridade metodológica do planejamento. Um sistema que guia mas não protege é como um GPS que avisa sobre o buraco mas não desacelera o carro. Os guardrails garantem que os dados inseridos no sistema tenham coerência metodológica, o que é fundamental para a credibilidade dos relatórios e auditorias.

---

### ROAD-009 — Análise de causa raiz na RAE (5 Porquês / Ishikawa)
**Prioridade:** P3 — Melhoria  
**Status:** 🔴 Pendente  
**Impacto:** Médio  
**Referência:** Método dos 5 Porquês (Toyota Production System, Taiichi Ohno); Diagrama de Ishikawa (1968)  

#### Problema identificado
A RAE registra "problemas identificados" como campo de texto livre. Não há estrutura para análise de causa raiz — técnica central da gestão japonesa (Toyota, Kaizen) para garantir que correções ataquem causas, não sintomas. Sem causa raiz estruturada, os encaminhamentos (ROAD-001) correm o risco de serem paliativos.

#### O que será feito

**Fase 1 — Modelo de dados:**
- Criar tabela `strategic_planning.tab_rae_causa_raiz`:
  - `cod_causa` (PK UUID)
  - `cod_rae` (FK)
  - `dsc_problema` (text — problema observado)
  - `json_cinco_porques` (jsonb — array de 5 strings representando cada "porquê")
  - `dsc_causa_raiz` (text — causa raiz identificada ao final dos 5 porquês)
  - `dsc_categoria_ishikawa` (enum nullable: 'Método', 'Máquina', 'Mão de Obra', 'Material', 'Medida', 'Meio Ambiente')
  - `cod_encaminhamento_vinculado` (FK nullable → tab_rae_encaminhamento)

**Fase 2 — Componente:**
- Adicionar aba "Análise de Causa Raiz" em `GerenciarRae`
- Formulário guiado dos 5 Porquês: campo "Por que?" repetido 5 vezes com seta visual de aprofundamento
- Seleção da categoria Ishikawa (6M)
- Vinculação automática a encaminhamento

**Fase 3 — IA:**
- Botão "Sugerir causa raiz" — envia problema + 5 porquês para o agente IA e retorna análise categorizando a causa raiz

#### Arquivos envolvidos
- `database/migrations/StrategicPlanning/` (nova migration)
- `app/Models/StrategicPlanning/RaeCausaRaiz.php` (novo)
- `app/Livewire/StrategicPlanning/GerenciarRae.php`
- `resources/views/livewire/p-e-i/gerenciar-rae.blade.php`

#### Critério de conclusão
A RAE pode registrar análises de causa raiz estruturadas (5 Porquês + categoria Ishikawa), vinculadas a encaminhamentos concretos.

#### Valor agregado
É a diferença entre "o indicador caiu porque houve falta de orçamento" (sintoma) e "o indicador caiu porque o processo de aquisição leva 90 dias, porque o sistema de compras exige 3 aprovações sequenciais, porque não há delegação de competência, porque..." (causa raiz). Organizações que tratam causas raiz eliminam problemas. Organizações que tratam sintomas os repetem em ciclos.

---

### ROAD-010 — Unificação conceitual da Identidade Estratégica
**Prioridade:** P3 — Melhoria  
**Status:** 🔴 Pendente  
**Impacto:** Baixo  
**Referência:** BSC (Kaplan & Norton — Missão, Visão, Valores como fundamento); GPPEI/MGI 2025  

#### Problema identificado
A identidade estratégica está dividida entre dois modelos:
- `MissaoVisaoValores` — armazena Missão e Visão (texto livre por PEI/organização)
- `Valor` — armazena valores institucionais (modelo separado, cadastro independente)

Na literatura (BSC, GPPEI), Missão, Visão e Valores são os 3 pilares inseparáveis da identidade estratégica. A separação em modelos distintos não é problema se for transparente para o usuário — mas atualmente o componente `MissaoVisao` mistura os dois contextos de forma confusa, e o modelo `MissaoVisaoValores` tem o nome enganoso (não armazena valores, armazena só missão e visão).

#### O que será feito

**Fase 1 — Renomear modelo (sem migration — sem alterar banco):**
- Renomear `App\Models\StrategicPlanning\MissaoVisaoValores` → `App\Models\StrategicPlanning\IdentidadeEstrategica`
- Manter a tabela `tab_missao_visao_valores` (não alterar banco)
- Atualizar todos os `use` e referências no código
- Atualizar `$table` para continuar apontando para `strategic_planning.tab_missao_visao_valores`

**Fase 2 — Componente:**
- Renomear componente para refletir "Identidade Estratégica" de forma clara
- Organizar seções explicitamente: "1. Missão", "2. Visão", "3. Valores"
- Adicionar campo de Negócio (`dsc_negocio`) — muitas metodologias incluem o Negócio antes da Missão
- Exibir os 3 pilares em cards lado a lado, não em seções separadas

**Fase 3 — Documentação interna:**
- Atualizar `CLAUDE.md` para refletir a renomeação
- Atualizar harness de documentação

#### Arquivos envolvidos
- `app/Models/StrategicPlanning/MissaoVisaoValores.php` (renomear)
- `app/Livewire/StrategicPlanning/MissaoVisao.php`
- `resources/views/livewire/p-e-i/missao-visao.blade.php`
- Todos os arquivos que referenciam `MissaoVisaoValores` (grep necessário)

#### Critério de conclusão
O modelo se chama `IdentidadeEstrategica`. O componente apresenta Missão, Visão e Valores como 3 pilares integrados, visualmente claros e conceitualmente corretos.

#### Valor agregado
Clareza conceitual para o usuário e para futuros desenvolvedores. Um sistema cujo código reflete corretamente os conceitos do domínio é mais fácil de manter, estender e auditar. É o princípio do "Ubiquitous Language" de Eric Evans (Domain-Driven Design) — o código deve falar a mesma língua que o especialista do domínio.

---

### ROAD-011 — Futuro Almejado com estrutura SMART por objetivo
**Prioridade:** P3 — Melhoria  
**Status:** 🔴 Pendente  
**Impacto:** Baixo  
**Referência:** GPPEI/MGI 2025 (Futuro Almejado); SMART Goals (Doran, 1981)  

#### Problema identificado
O modelo `FuturoAlmejado` contém apenas `dsc_futuro_almejado` — texto livre. O GPPEI orienta que o futuro almejado responda "Como será a organização ao final do ciclo?" de forma quantificável e verificável. Sem estrutura, o futuro almejado se torna declaração retórica sem conexão com as metas dos indicadores.

#### O que será feito

**Fase 1 — Modelo de dados:**
- Expandir `strategic_planning.tab_futuro_almejado_objetivo`:
  - `dsc_situacao_atual` (text nullable — linha de base qualitativa)
  - `dsc_futuro_almejado` (text — já existe, mantido)
  - `dsc_indicador_referencia` (text nullable — qual indicador mede o alcance)
  - `vlr_referencia_meta` (decimal nullable — valor quantitativo esperado)
  - `dte_horizonte` (date nullable — quando o futuro almejado deve ser realidade)

**Fase 2 — Componente:**
- Expandir `GerenciarFuturoAlmejado` com os novos campos
- Exibir lado a lado: "Situação atual" → "Futuro almejado"
- Vincular ao indicador de referência (seletor do indicador vinculado ao objetivo)

#### Arquivos envolvidos
- `database/migrations/StrategicPlanning/` (nova migration)
- `app/Models/StrategicPlanning/FuturoAlmejado.php`
- `app/Livewire/StrategicPlanning/GerenciarFuturoAlmejado.php`
- `resources/views/livewire/p-e-i/`

#### Critério de conclusão
O futuro almejado de cada objetivo tem: situação atual, descrição qualitativa do futuro, indicador de referência, valor meta e horizonte temporal.

#### Valor agregado
Conecta o discurso estratégico à realidade mensurável. Um "futuro almejado" sem número é desejo. Com número, prazo e indicador de referência, é compromisso verificável — e auditável. É a materialização do princípio japonês "o que não pode ser medido não pode ser gerenciado".

---

## Histórico de atualizações deste documento

| Data | Versão | Descrição | Autor |
|---|---|---|---|
| 2026-06-25 | 1.0 | Criação do roadmap com 11 itens da varredura metodológica | Sistema PEI |
| 2026-06-25 | 1.1 | ROAD-001 concluído — encaminhamentos formais na RAE | Sistema PEI |
| 2026-06-25 | 1.2 | ROAD-002 concluído — desdobramento em cascata de objetivos (Hoshin Kanri) | Sistema PEI |

---

## Painel de status consolidado

| ID | Título resumido | Prioridade | Status | Impacto |
|---|---|---|---|---|
| ROAD-001 | Ciclo PDCA completo — RAE gera ajustes formais | P1 | 🟢 Concluído | Alto |
| ROAD-002 | Desdobramento em cascata de objetivos | P1 | 🟢 Concluído | Alto |
| ROAD-003 | Estratégias de resposta a riscos (ISO 31000) | P2 | 🔴 Pendente | Médio |
| ROAD-004 | Índice consolidado de desempenho do PEI | P2 | 🔴 Pendente | Médio |
| ROAD-005 | Rastreabilidade bidirecional Plano ↔ Indicador | P2 | 🔴 Pendente | Médio |
| ROAD-006 | Análise de tendência temporal dos indicadores | P2 | 🔴 Pendente | Médio |
| ROAD-007 | Matriz TOWS: da análise ambiental às estratégias | P3 | 🔴 Pendente | Médio |
| ROAD-008 | Bloqueios e guardrails metodológicos no fluxo | P3 | 🔴 Pendente | Médio |
| ROAD-009 | Análise de causa raiz na RAE (5 Porquês / Ishikawa) | P3 | 🔴 Pendente | Médio |
| ROAD-010 | Unificação conceitual da Identidade Estratégica | P3 | 🔴 Pendente | Baixo |
| ROAD-011 | Futuro Almejado com estrutura SMART por objetivo | P3 | 🔴 Pendente | Baixo |
