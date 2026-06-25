# Roadmap de EvoluÃ§Ã£o â€” Sistema PEI
# Planejamento EstratÃ©gico Integrado

**VersÃ£o:** 1.0  
**Data de criaÃ§Ã£o:** 2026-06-25  
**Baseado em:** Varredura metodolÃ³gica completa realizada em 2026-06-25  
**Calibragem:** BSC (Kaplan & Norton), Hoshin Kanri, PDCA (Deming), ISO 31000:2018, GPPEI/MGI 2025, TOWS Matrix (Weihrich), GestÃ£o EstratÃ©gica Japonesa  

---

## Como ler este documento

- **Status:** `ðŸ”´ Pendente` | `ðŸŸ¡ Em andamento` | `ðŸŸ¢ ConcluÃ­do` | `â¸ï¸ Suspenso` | `âŒ Cancelado`
- **Prioridade:** `P1 â€” CrÃ­tico` | `P2 â€” Importante` | `P3 â€” Melhoria`
- **Impacto:** `Alto` | `MÃ©dio` | `Baixo`
- Cada item registra: o que serÃ¡ feito, arquivos envolvidos, critÃ©rio de conclusÃ£o e valor agregado.

---

## Contexto da varredura

A varredura realizada em 2026-06-25 avaliou o sistema contra a literatura e melhores prÃ¡ticas mundiais de planejamento estratÃ©gico. O sistema demonstrou solidez em ~70% da implementaÃ§Ã£o. Os gaps identificados concentram-se em trÃªs eixos:

1. **IntegraÃ§Ã£o entre mÃ³dulos** â€” dados coletados em silos sem rastreabilidade cruzada.
2. **FormalizaÃ§Ã£o de ciclos** â€” ferramentas existem mas nÃ£o formam ciclo completo (PDCA).
3. **RestriÃ§Ãµes metodolÃ³gicas** â€” o sistema orienta mas nÃ£o impede desvios da metodologia.

---

## BLOCO 1 â€” CorreÃ§Ãµes de Alta Prioridade (P1)

### ROAD-001 â€” Ciclo PDCA completo: RAE deve gerar ajustes formais
**Prioridade:** P1 â€” CrÃ­tico  
**Status:** ðŸŸ¢ ConcluÃ­do â€” 2026-06-25  
**Impacto:** Alto  
**ReferÃªncia:** PDCA (Deming, 1950); Hoshin Kanri; GPPEI/MGI 2025 MÃ³dulo 03  

#### Problema identificado
A RAE (RevisÃ£o e AvaliaÃ§Ã£o da EstratÃ©gia) implementa o "Check" do ciclo PDCA, mas o "Act" â€” os ajustes estratÃ©gicos que a revisÃ£o gera â€” nÃ£o retroalimenta o sistema de forma estruturada. Um problema identificado em reuniÃ£o de RAE nÃ£o gera novo plano de aÃ§Ã£o, nÃ£o altera meta de indicador e nÃ£o modifica objetivo de forma rastreÃ¡vel. O ciclo PDCA estÃ¡ aberto: Plan âœ… â†’ Do âœ… â†’ Check âœ… â†’ **Act âŒ**.

#### O que serÃ¡ feito

**Fase 1 â€” Modelo de dados:**
- Criar migration para tabela `strategic_planning.tab_rae_encaminhamento`:
  - `cod_encaminhamento` (PK UUID)
  - `cod_rae` (FK â†’ `tab_rae`)
  - `dsc_tipo` (enum: 'Novo Plano', 'RevisÃ£o de Meta', 'RevisÃ£o de Objetivo', 'RevisÃ£o de Risco', 'Outro')
  - `txt_descricao` (text â€” descriÃ§Ã£o da aÃ§Ã£o de ajuste)
  - `dsc_status` (enum: 'Pendente', 'Em ExecuÃ§Ã£o', 'ConcluÃ­do')
  - `cod_responsavel` (FK â†’ `pei.users`)
  - `dte_prazo` (date)
  - `cod_plano_vinculado` (FK nullable â†’ `action_plan.tab_plano_de_acao`)
  - timestamps + soft delete

**Fase 2 â€” Model e Component:**
- Criar `App\Models\StrategicPlanning\RaeEncaminhamento`
- Expandir `App\Livewire\StrategicPlanning\GerenciarRae` para incluir aba "Encaminhamentos"
- Cada encaminhamento do tipo "Novo Plano" deve permitir criar um plano de aÃ§Ã£o diretamente da RAE com link bidirecional

**Fase 3 â€” Dashboard:**
- Widget no Dashboard mostrando encaminhamentos pendentes de RAEs anteriores
- Alerta quando encaminhamento estiver atrasado (prazo vencido sem conclusÃ£o)

**Fase 4 â€” RelatÃ³rio:**
- Incluir encaminhamentos no relatÃ³rio executivo com status de execuÃ§Ã£o

#### Arquivos envolvidos
- `database/migrations/StrategicPlanning/` (nova migration)
- `app/Models/StrategicPlanning/RaeEncaminhamento.php` (novo)
- `app/Models/StrategicPlanning/Rae.php` (adicionar relacionamento)
- `app/Livewire/StrategicPlanning/GerenciarRae.php`
- `resources/views/livewire/p-e-i/gerenciar-rae.blade.php`
- `app/Livewire/Dashboard/Index.php`

#### CritÃ©rio de conclusÃ£o
Um problema identificado em reuniÃ£o de RAE pode gerar encaminhamento que: tem responsÃ¡vel, prazo, status e â€” quando do tipo "Novo Plano" â€” cria um plano de aÃ§Ã£o vinculado ao objetivo que originou o problema.

#### Valor agregado
Fecha o ciclo PDCA. O sistema deixa de ser um repositÃ³rio de diagnÃ³sticos para se tornar um motor de melhoria contÃ­nua. Sem este fechamento, a RAE Ã© apenas uma reuniÃ£o registrada â€” com ele, cada revisÃ£o produz aÃ§Ãµes rastreÃ¡veis que alimentam o prÃ³ximo ciclo. Ã‰ a diferenÃ§a entre uma organizaÃ§Ã£o que avalia e uma que aprende.

---

### ROAD-002 â€” Desdobramento em cascata de objetivos (Hoshin Kanri)
**Prioridade:** P1 â€” CrÃ­tico  
**Status:** ðŸŸ¢ ConcluÃ­do â€” 2026-06-25  
**Impacto:** Alto  
**ReferÃªncia:** Hoshin Kanri (Toyota, 1965); BSC (Kaplan & Norton, 2004 â€” Strategy Maps)  

#### Problema identificado
NÃ£o existe vÃ­nculo objetivo-pai â†’ objetivo-filho. Um objetivo estratÃ©gico de nÃ­vel corporativo nÃ£o se desdobra em objetivos tÃ¡ticos ou operacionais. Isso impede rastrear se as equipes e unidades estÃ£o alinhadas com a direÃ§Ã£o estratÃ©gica definida pela lideranÃ§a. No Hoshin Kanri, este processo de desdobramento e negociaÃ§Ã£o Ã© chamado de "catchball" â€” a estratÃ©gia desce e sobe em iteraÃ§Ãµes atÃ© haver consenso e alinhamento.

#### O que serÃ¡ feito

**Fase 1 â€” Modelo de dados:**
- Adicionar coluna `cod_objetivo_pai` (FK nullable self-referencing) em `strategic_planning.tab_objetivo`
- Adicionar coluna `num_nivel_desdobramento` (smallint, default 1) para identificar a profundidade da cascata
- Nova migration (nunca alterar migration existente)

**Fase 2 â€” Model:**
- Adicionar em `App\Models\StrategicPlanning\Objetivo`:
  - Relacionamento `objetivoPai()` (BelongsTo self)
  - Relacionamento `objetivosFilhos()` (HasMany self)
  - MÃ©todo `calcularAtingimentoCascata()` â€” considera atingimento dos filhos no cÃ¡lculo do pai
  - Scope `raiz()` â€” objetivos sem pai (nÃ­vel 1)

**Fase 3 â€” Componente Livewire:**
- Expandir `ListarObjetivos` para exibir Ã¡rvore de desdobramento
- Ao criar objetivo, permitir selecionar objetivo-pai (opcional)
- VisualizaÃ§Ã£o em modo Ã¡rvore (accordion por perspectiva â†’ objetivo raiz â†’ filhos)

**Fase 4 â€” Mapa EstratÃ©gico:**
- Exibir indicaÃ§Ã£o visual de objetivos com filhos desdobrados
- Link "Ver desdobramento" abrindo Ã¡rvore completa

#### Arquivos envolvidos
- `database/migrations/StrategicPlanning/` (nova migration)
- `app/Models/StrategicPlanning/Objetivo.php`
- `app/Livewire/StrategicPlanning/ListarObjetivos.php`
- `resources/views/livewire/p-e-i/listar-objetivos.blade.php`
- `app/Livewire/StrategicPlanning/MapaEstrategico.php`

#### CritÃ©rio de conclusÃ£o
Um objetivo estratÃ©gico de nÃ­vel 1 pode ter N objetivos filhos. O atingimento do objetivo pai considera os filhos. O mapa estratÃ©gico identifica visualmente quais objetivos possuem desdobramento.

#### Valor agregado
Implementa o conceito central do Hoshin Kanri: alinhamento vertical. A estratÃ©gia da alta direÃ§Ã£o desce formalmente atÃ© as unidades executoras, e o progresso sobe rastreÃ¡vel. Elimina o gap entre "o que foi planejado no topo" e "o que estÃ¡ sendo executado na base" â€” causa nÃºmero 1 de fracasso em planejamento estratÃ©gico segundo Kaplan & Norton (2004).

---

## BLOCO 2 â€” Melhorias Importantes (P2)

### ROAD-003 â€” EstratÃ©gias de resposta a riscos (ISO 31000:2018)
**Prioridade:** P2 â€” Importante  
**Status:** ðŸŸ¢ ConcluÃ­do  
**Impacto:** MÃ©dio  
**ReferÃªncia:** ISO 31000:2018 â€” GestÃ£o de Riscos; ERM (COSO, 2017)  

#### Problema identificado
O sistema implementa identificaÃ§Ã£o, anÃ¡lise (probabilidade Ã— impacto) e avaliaÃ§Ã£o de riscos corretamente. PorÃ©m faltam as 4 estratÃ©gias canÃ´nicas de resposta definidas pela ISO 31000 e pelo COSO ERM: **Mitigar** (reduzir probabilidade/impacto), **Evitar** (eliminar a causa), **Transferir** (seguro, terceirizaÃ§Ã£o), **Aceitar** (registrar e monitorar). O campo `dsc_status` atual ('Identificado', 'Em Monitoramento', 'Mitigado', 'Encerrado') mistura estado com estratÃ©gia, gerando ambiguidade.

#### O que serÃ¡ feito

**Fase 1 â€” Modelo de dados:**
- Adicionar coluna `dsc_estrategia_resposta` (enum: 'Mitigar', 'Evitar', 'Transferir', 'Aceitar') em `risk_management.tab_risco`
- Adicionar coluna `txt_justificativa_estrategia` (text nullable) â€” obrigatÃ³ria quando estratÃ©gia = 'Aceitar'
- Adicionar coluna `dte_proxima_revisao` (date nullable) â€” prazo para reavaliar o risco
- Nova migration

**Fase 2 â€” Model e validaÃ§Ã£o:**
- Atualizar `App\Models\RiskManagement\Risco` com constantes de estratÃ©gia
- Atualizar `ListarRiscos` â€” validaÃ§Ã£o: quando `dsc_estrategia_resposta = 'Aceitar'`, `txt_justificativa_estrategia` Ã© obrigatÃ³ria
- Adicionar mÃ©todo `estaVencido()` â€” retorna true quando `dte_proxima_revisao` < hoje

**Fase 3 â€” IntegraÃ§Ã£o com RAE:**
- Na RAE, exibir riscos com revisÃ£o vencida como alerta automÃ¡tico
- Widget no Dashboard: "Riscos com revisÃ£o pendente"

**Fase 4 â€” Matriz de riscos:**
- Atualizar `MatrizRiscos` para filtrar por estratÃ©gia de resposta
- Exibir badge com estratÃ©gia em cada cÃ©lula da matriz

#### Arquivos envolvidos
- `database/migrations/RiskManagement/` (nova migration)
- `app/Models/RiskManagement/Risco.php`
- `app/Livewire/RiskManagement/ListarRiscos.php`
- `app/Livewire/RiskManagement/MatrizRiscos.php`
- `resources/views/livewire/risco/`
- `app/Livewire/StrategicPlanning/GerenciarRae.php`

#### CritÃ©rio de conclusÃ£o
Todo risco tem estratÃ©gia de resposta declarada. Riscos com estratÃ©gia 'Aceitar' exigem justificativa. Riscos com `dte_proxima_revisao` vencida aparecem como alerta na RAE e no Dashboard.

#### Valor agregado
Alinha a gestÃ£o de riscos ao padrÃ£o ISO 31000:2018 e COSO ERM â€” os dois frameworks mais adotados globalmente. A distinÃ§Ã£o entre "aceitar conscientemente" e "mitigar" Ã© a diferenÃ§a entre gestÃ£o de riscos madura e registro passivo de problemas. OrganizaÃ§Ãµes auditadas (TCU, CGU) sÃ£o cobradas exatamente nesta distinÃ§Ã£o.

---

### ROAD-004 â€” Ãndice consolidado de desempenho do PEI
**Prioridade:** P2 â€” Importante  
**Status:** ðŸŸ¢ ConcluÃ­do  
**Impacto:** MÃ©dio  
**ReferÃªncia:** BSC (Kaplan & Norton â€” Scorecard Consolidado); IQG (Ãndice de Qualidade de GestÃ£o)  

#### Problema identificado
O cÃ¡lculo de desempenho estÃ¡ implementado ao nÃ­vel de indicador e perspectiva, mas nÃ£o existe um Ã­ndice Ãºnico que represente a saÃºde geral do ciclo PEI. O gestor executivo precisa de um nÃºmero â€” como o Ãndice de Qualidade de GestÃ£o (IQG) usado em empresas japonesas e no modelo de excelÃªncia da FNQ â€” para ter visÃ£o imediata sem navegar por cada perspectiva.

#### O que serÃ¡ feito

**Fase 1 â€” ServiÃ§o de cÃ¡lculo:**
- Criar mÃ©todo `calcularIQG(string $codPei, int $ano, int $mes)` em `IndicadorCalculoService` (ou novo `PeiDesempenhoService`)
- FÃ³rmula: mÃ©dia ponderada dos desempenhos das perspectivas pelo peso configurado em cada uma
- Considerar apenas perspectivas com pelo menos 1 indicador com evoluÃ§Ã£o no perÃ­odo
- Retornar: `['valor' => float, 'grau' => GrauSatisfacao, 'perspectivas' => array, 'tendencia' => string]`

**Fase 2 â€” Dashboard:**
- Widget principal: Ãndice do PEI em destaque (grande, colorido pelo grau de satisfaÃ§Ã£o)
- Gauge visual (semicÃ­rculo) representando 0â€“100%
- HistÃ³rico dos Ãºltimos 6 meses em sparkline

**Fase 3 â€” RelatÃ³rios:**
- Incluir IQG na capa de todos os relatÃ³rios executivos
- SeÃ§Ã£o de evoluÃ§Ã£o do IQG por perÃ­odo

**Fase 4 â€” Mapa EstratÃ©gico:**
- Exibir IQG como indicador-sÃ­ntese no topo do mapa

#### Arquivos envolvidos
- `app/Services/IndicadorCalculoService.php` (ou novo PeiDesempenhoService)
- `app/Livewire/Dashboard/Index.php`
- `resources/views/livewire/dashboard/index.blade.php`
- `resources/views/relatorios/executivo.blade.php`
- `app/Livewire/StrategicPlanning/MapaEstrategico.php`

#### CritÃ©rio de conclusÃ£o
O Dashboard exibe um Ã­ndice Ãºnico de desempenho do PEI, colorido pelo grau de satisfaÃ§Ã£o correspondente, com evoluÃ§Ã£o histÃ³rica dos Ãºltimos 6 meses.

#### Valor agregado
Um executivo que entra no sistema deve saber em 3 segundos se a organizaÃ§Ã£o estÃ¡ no caminho certo. O IQG entrega isso. Ã‰ o conceito japonÃªs de "kanban visual" aplicado Ã  gestÃ£o estratÃ©gica â€” a informaÃ§Ã£o mais crÃ­tica deve ser a mais visÃ­vel. Elimina a necessidade de navegar por perspectivas para ter a visÃ£o executiva.

---

### ROAD-005 â€” Rastreabilidade bidirecional: Plano de AÃ§Ã£o â†” Indicador
**Prioridade:** P2 â€” Importante  
**Status:** ðŸŸ¢ ConcluÃ­do  
**Impacto:** MÃ©dio  
**ReferÃªncia:** BSC (teoria da causalidade estratÃ©gica); GestÃ£o por Resultados  

#### Problema identificado
Um Plano de AÃ§Ã£o Ã© vinculado a um Objetivo, e Indicadores tambÃ©m sÃ£o vinculados a Objetivos. Mas nÃ£o existe relaÃ§Ã£o direta "este plano de aÃ§Ã£o foi criado para mover este indicador especÃ­fico". ImpossÃ­vel responder: "Quais indicadores ficam sem plano de aÃ§Ã£o que os suporte?" ou "Este plano contribui para melhorar o indicador X?". No BSC, a causalidade entre aÃ§Ãµes e resultados medidos Ã© explÃ­cita e fundamental.

#### O que serÃ¡ feito

**Fase 1 â€” Modelo de dados:**
- Criar tabela pivot `performance_indicators.rel_indicador_plano_de_acao`:
  - `cod_indicador` (FK)
  - `cod_plano_de_acao` (FK)
  - `txt_contribuicao` (text nullable â€” descriÃ§Ã£o de como o plano move o indicador)
  - PK composta

**Fase 2 â€” Model:**
- Adicionar em `Indicador`: relacionamento `planosDeAcao()` (BelongsToMany)
- Adicionar em `PlanoDeAcao`: relacionamento `indicadores()` (BelongsToMany)
- MÃ©todo em `Indicador`: `temCoberturaDePlano()` â€” retorna bool

**Fase 3 â€” Componentes:**
- Em `DetalharIndicador`: seÃ§Ã£o "Planos que suportam este indicador" com link para cada plano e % de progresso
- Em `DetalharPlano`: seÃ§Ã£o "Indicadores que este plano visa mover" com valor atual e meta
- Em `ListarIndicadores`: badge/alerta para indicadores sem nenhum plano vinculado
- Em `PeiGuidanceService`: aviso quando hÃ¡ indicadores sem cobertura de plano

#### Arquivos envolvidos
- `database/migrations/PerformanceIndicators/` (nova migration)
- `app/Models/PerformanceIndicators/Indicador.php`
- `app/Models/ActionPlan/PlanoDeAcao.php`
- `app/Livewire/PerformanceIndicators/DetalharIndicador.php`
- `app/Livewire/ActionPlan/DetalharPlano.php`
- `app/Livewire/PerformanceIndicators/ListarIndicadores.php`
- `app/Services/PeiGuidanceService.php`

#### CritÃ©rio de conclusÃ£o
Ã‰ possÃ­vel visualizar, a partir de um indicador, todos os planos que o suportam. Ã‰ possÃ­vel visualizar, a partir de um plano, todos os indicadores que ele visa impactar. Indicadores sem cobertura de plano sÃ£o sinalizados.

#### Valor agregado
Implementa a espinha dorsal do BSC: a cadeia de causalidade entre aÃ§Ã£o e resultado. Responde a pergunta que todo gestor pÃºblico enfrenta em auditoria: "Como vocÃª sabe que este plano estÃ¡ contribuindo para este resultado?". Transforma o sistema de um repositÃ³rio de dados em um sistema de gestÃ£o por causalidade.

---

### ROAD-006 â€” AnÃ¡lise de tendÃªncia temporal dos indicadores
**Prioridade:** P2 â€” Importante  
**Status:** ðŸ”´ Pendente  
**Impacto:** MÃ©dio  
**ReferÃªncia:** Statistical Process Control (SPC â€” Shewhart/Deming); GestÃ£o da Qualidade Total (TQM)  

#### Problema identificado
O sistema registra histÃ³rico de evoluÃ§Ã£o dos indicadores (`tab_evolucao_indicador`), mas nÃ£o calcula tendÃªncia (crescente/decrescente/estÃ¡vel). Sem isso, Ã© impossÃ­vel detectar deterioraÃ§Ã£o gradual antes que se torne crise. Na gestÃ£o japonesa (TQM/SPC), anÃ¡lise de tendÃªncia Ã© base do controle estatÃ­stico de processos â€” uma variaÃ§Ã£o consistente em 3 ou mais perÃ­odos Ã© sinal de mudanÃ§a de processo, nÃ£o ruÃ­do aleatÃ³rio.

#### O que serÃ¡ feito

**Fase 1 â€” ServiÃ§o de cÃ¡lculo:**
- Adicionar mÃ©todo `calcularTendencia(string $codIndicador, int $ultimosMeses = 3)` em `IndicadorCalculoService`
- Algoritmo: regressÃ£o linear simples sobre os Ãºltimos N valores de `vlr_realizado`
  - Coeficiente angular > +threshold â†’ `'Crescente'`
  - Coeficiente angular < -threshold â†’ `'Decrescente'`
  - Entre thresholds â†’ `'EstÃ¡vel'`
- Considerar polaridade do indicador para determinar se tendÃªncia Ã© favorÃ¡vel ou desfavorÃ¡vel

**Fase 2 â€” Model:**
- Adicionar mÃ©todo `tendenciaAtual(int $meses = 3)` em `Indicador`
- Retornar: `['direcao' => string, 'favoravel' => bool, 'variacao_pct' => float]`

**Fase 3 â€” UI:**
- Em `ListarIndicadores`: Ã­cone de seta (â†‘ verde / â†“ vermelho / â†’ amarelo) ao lado do valor atual
- Em `DetalharIndicador`: grÃ¡fico de tendÃªncia com linha de regressÃ£o projetada
- Em `MapaEstrategico`: indicar tendÃªncia desfavorÃ¡vel com Ã­cone de alerta no objetivo

**Fase 4 â€” Alertas:**
- Criar alerta automÃ¡tico quando tendÃªncia desfavorÃ¡vel persistir por 2+ perÃ­odos consecutivos
- Alert via `NotificationService` â†’ `StrategicAlert`

#### Arquivos envolvidos
- `app/Services/IndicadorCalculoService.php`
- `app/Models/PerformanceIndicators/Indicador.php`
- `app/Livewire/PerformanceIndicators/ListarIndicadores.php`
- `app/Livewire/PerformanceIndicators/DetalharIndicador.php`
- `app/Livewire/StrategicPlanning/MapaEstrategico.php`
- `app/Services/NotificationService.php`

#### CritÃ©rio de conclusÃ£o
Cada indicador com 3+ evoluÃ§Ãµes registradas exibe sua tendÃªncia (direÃ§Ã£o + se Ã© favorÃ¡vel). TendÃªncias desfavorÃ¡veis persistentes geram alerta automÃ¡tico no sistema.

#### Valor agregado
Transforma dados histÃ³ricos em inteligÃªncia antecipativa. A diferenÃ§a entre gestÃ£o reativa e gestÃ£o proativa estÃ¡ em detectar o problema enquanto ainda Ã© tendÃªncia, nÃ£o quando jÃ¡ Ã© resultado consumado. Ã‰ o conceito de "early warning system" que as empresas japonesas (Toyota, Honda) aplicam no chÃ£o de fÃ¡brica â€” adaptado para a gestÃ£o estratÃ©gica pÃºblica.

---

## BLOCO 3 â€” Melhorias de Maturidade (P3)

### ROAD-007 â€” Matriz TOWS: da anÃ¡lise ambiental Ã s estratÃ©gias
**Prioridade:** P3 â€” Melhoria  
**Status:** ðŸ”´ Pendente  
**Impacto:** MÃ©dio  
**ReferÃªncia:** TOWS Matrix (Weihrich, 1982); SWOT Analysis (Andrews, 1971)  

#### Problema identificado
O sistema coleta SWOT e PESTEL com qualidade, mas a anÃ¡lise ambiental permanece desconectada dos objetivos estratÃ©gicos. A Matriz TOWS (de Weihrich, 1982 â€” evoluÃ§Ã£o da SWOT) cruza os quadrantes para derivar estratÃ©gias: SO (usar ForÃ§as para explorar Oportunidades), WO (superar Fraquezas com Oportunidades), ST (usar ForÃ§as para neutralizar AmeaÃ§as), WT (minimizar Fraquezas frente Ã s AmeaÃ§as). Sem este cruzamento, a anÃ¡lise ambiental Ã© diagnÃ³stico sem prescriÃ§Ã£o.

#### O que serÃ¡ feito

**Fase 1 â€” Modelo de dados:**
- Criar tabela `strategic_planning.tab_estrategia_tows`:
  - `cod_estrategia` (PK UUID)
  - `cod_pei` (FK)
  - `cod_organizacao` (FK)
  - `dsc_tipo` (enum: 'SO', 'ST', 'WO', 'WT')
  - `dsc_estrategia` (text â€” descriÃ§Ã£o da estratÃ©gia derivada)
  - `txt_fundamentacao` (text nullable â€” quais forÃ§as/fraquezas e oportunidades/ameaÃ§as embasam)
  - `cod_objetivo_vinculado` (FK nullable â†’ tab_objetivo â€” objetivo que operacionaliza a estratÃ©gia)
  - timestamps + soft delete

**Fase 2 â€” Componente:**
- Adicionar aba "Matriz TOWS" em `AnaliseSWOT`
- Exibir matriz 2Ã—2 com os 4 quadrantes
- Permitir registrar estratÃ©gias por quadrante
- Vincular estratÃ©gia a objetivo existente ou sinalizar necessidade de criar novo objetivo

**Fase 3 â€” Rastreabilidade:**
- Em `ListarObjetivos`: badge indicando quais objetivos foram derivados de estratÃ©gia TOWS
- Em `AnaliseSWOT`: indicar quais itens SWOT jÃ¡ foram usados em estratÃ©gias TOWS

#### Arquivos envolvidos
- `database/migrations/StrategicPlanning/` (nova migration)
- `app/Models/StrategicPlanning/EstrategiaTows.php` (novo)
- `app/Livewire/StrategicPlanning/AnaliseSWOT.php`
- `resources/views/livewire/p-e-i/analise-s-w-o-t.blade.php`
- `app/Livewire/StrategicPlanning/ListarObjetivos.php`

#### CritÃ©rio de conclusÃ£o
Ã‰ possÃ­vel registrar estratÃ©gias por quadrante TOWS e vinculÃ¡-las a objetivos estratÃ©gicos. O sistema indica quais objetivos tÃªm fundamentaÃ§Ã£o TOWS e quais ainda nÃ£o tÃªm.

#### Valor agregado
Fecha o cÃ­rculo entre diagnÃ³stico e estratÃ©gia. A anÃ¡lise ambiental deixa de ser um exercÃ­cio acadÃªmico para se tornar o fundamento explÃ­cito dos objetivos estratÃ©gicos. Em auditorias (TCU/CGU), a pergunta "Por que este objetivo foi escolhido?" terÃ¡ resposta rastreÃ¡vel e fundamentada. Ã‰ o elo que transforma anÃ¡lise em decisÃ£o.

---

### ROAD-008 â€” Bloqueios e guardrails metodolÃ³gicos no fluxo
**Prioridade:** P3 â€” Melhoria  
**Status:** ðŸ”´ Pendente  
**Impacto:** MÃ©dio  
**ReferÃªncia:** GPPEI/MGI 2025 (sequÃªncia metodolÃ³gica obrigatÃ³ria)  

#### Problema identificado
O `PeiGuidanceService` orienta o usuÃ¡rio com avisos e progressÃ£o, mas nÃ£o impede que etapas sejam puladas. Um usuÃ¡rio pode criar Indicadores sem ter criado Perspectivas. Pode criar Planos sem Objetivos definidos. A metodologia GPPEI define uma sequÃªncia que nÃ£o Ã© opcional â€” cada fase depende da anterior para ter coerÃªncia.

#### O que serÃ¡ feito

**Fase 1 â€” Mapeamento de prÃ©-requisitos:**
- Definir matriz de prÃ©-requisitos no `PeiGuidanceService`:
  - Perspectivas requerem: Ciclo PEI ativo + Identidade salva
  - Objetivos requerem: ao menos 2 perspectivas cadastradas
  - Indicadores requerem: ao menos 1 objetivo cadastrado
  - Planos requerem: ao menos 1 objetivo cadastrado
  - Graus de SatisfaÃ§Ã£o requerem: Ciclo PEI ativo

**Fase 2 â€” Guards nos componentes:**
- Em cada componente, antes de abrir modal de criaÃ§Ã£o, verificar prÃ©-requisitos via `PeiGuidanceService`
- Se prÃ©-requisito nÃ£o atendido: exibir modal explicativo com link para a etapa que falta
- Mensagem clara: "Para criar Indicadores, Ã© necessÃ¡rio ter pelo menos 1 Objetivo EstratÃ©gico cadastrado. [Ir para Objetivos â†’]"

**Fase 3 â€” SinalizaÃ§Ã£o visual:**
- Itens da sidebar com prÃ©-requisitos nÃ£o atendidos recebem Ã­cone de cadeado
- Tooltip: "Complete [etapa X] para desbloquear"

#### Arquivos envolvidos
- `app/Services/PeiGuidanceService.php`
- `app/Livewire/PerformanceIndicators/ListarIndicadores.php`
- `app/Livewire/ActionPlan/ListarPlanos.php`
- `app/Livewire/StrategicPlanning/ListarPerspectivas.php`
- `app/Livewire/StrategicPlanning/ListarObjetivos.php`
- `resources/views/layouts/app.blade.php`

#### CritÃ©rio de conclusÃ£o
Nenhum componente permite criar registros de domÃ­nio sem que os prÃ©-requisitos metodolÃ³gicos estejam atendidos. O usuÃ¡rio Ã© redirecionado com mensagem clara quando tenta acessar uma etapa prematuramente.

#### Valor agregado
Protege a integridade metodolÃ³gica do planejamento. Um sistema que guia mas nÃ£o protege Ã© como um GPS que avisa sobre o buraco mas nÃ£o desacelera o carro. Os guardrails garantem que os dados inseridos no sistema tenham coerÃªncia metodolÃ³gica, o que Ã© fundamental para a credibilidade dos relatÃ³rios e auditorias.

---

### ROAD-009 â€” AnÃ¡lise de causa raiz na RAE (5 PorquÃªs / Ishikawa)
**Prioridade:** P3 â€” Melhoria  
**Status:** ðŸ”´ Pendente  
**Impacto:** MÃ©dio  
**ReferÃªncia:** MÃ©todo dos 5 PorquÃªs (Toyota Production System, Taiichi Ohno); Diagrama de Ishikawa (1968)  

#### Problema identificado
A RAE registra "problemas identificados" como campo de texto livre. NÃ£o hÃ¡ estrutura para anÃ¡lise de causa raiz â€” tÃ©cnica central da gestÃ£o japonesa (Toyota, Kaizen) para garantir que correÃ§Ãµes ataquem causas, nÃ£o sintomas. Sem causa raiz estruturada, os encaminhamentos (ROAD-001) correm o risco de serem paliativos.

#### O que serÃ¡ feito

**Fase 1 â€” Modelo de dados:**
- Criar tabela `strategic_planning.tab_rae_causa_raiz`:
  - `cod_causa` (PK UUID)
  - `cod_rae` (FK)
  - `dsc_problema` (text â€” problema observado)
  - `json_cinco_porques` (jsonb â€” array de 5 strings representando cada "porquÃª")
  - `dsc_causa_raiz` (text â€” causa raiz identificada ao final dos 5 porquÃªs)
  - `dsc_categoria_ishikawa` (enum nullable: 'MÃ©todo', 'MÃ¡quina', 'MÃ£o de Obra', 'Material', 'Medida', 'Meio Ambiente')
  - `cod_encaminhamento_vinculado` (FK nullable â†’ tab_rae_encaminhamento)

**Fase 2 â€” Componente:**
- Adicionar aba "AnÃ¡lise de Causa Raiz" em `GerenciarRae`
- FormulÃ¡rio guiado dos 5 PorquÃªs: campo "Por que?" repetido 5 vezes com seta visual de aprofundamento
- SeleÃ§Ã£o da categoria Ishikawa (6M)
- VinculaÃ§Ã£o automÃ¡tica a encaminhamento

**Fase 3 â€” IA:**
- BotÃ£o "Sugerir causa raiz" â€” envia problema + 5 porquÃªs para o agente IA e retorna anÃ¡lise categorizando a causa raiz

#### Arquivos envolvidos
- `database/migrations/StrategicPlanning/` (nova migration)
- `app/Models/StrategicPlanning/RaeCausaRaiz.php` (novo)
- `app/Livewire/StrategicPlanning/GerenciarRae.php`
- `resources/views/livewire/p-e-i/gerenciar-rae.blade.php`

#### CritÃ©rio de conclusÃ£o
A RAE pode registrar anÃ¡lises de causa raiz estruturadas (5 PorquÃªs + categoria Ishikawa), vinculadas a encaminhamentos concretos.

#### Valor agregado
Ã‰ a diferenÃ§a entre "o indicador caiu porque houve falta de orÃ§amento" (sintoma) e "o indicador caiu porque o processo de aquisiÃ§Ã£o leva 90 dias, porque o sistema de compras exige 3 aprovaÃ§Ãµes sequenciais, porque nÃ£o hÃ¡ delegaÃ§Ã£o de competÃªncia, porque..." (causa raiz). OrganizaÃ§Ãµes que tratam causas raiz eliminam problemas. OrganizaÃ§Ãµes que tratam sintomas os repetem em ciclos.

---

### ROAD-010 â€” UnificaÃ§Ã£o conceitual da Identidade EstratÃ©gica
**Prioridade:** P3 â€” Melhoria  
**Status:** ðŸ”´ Pendente  
**Impacto:** Baixo  
**ReferÃªncia:** BSC (Kaplan & Norton â€” MissÃ£o, VisÃ£o, Valores como fundamento); GPPEI/MGI 2025  

#### Problema identificado
A identidade estratÃ©gica estÃ¡ dividida entre dois modelos:
- `MissaoVisaoValores` â€” armazena MissÃ£o e VisÃ£o (texto livre por PEI/organizaÃ§Ã£o)
- `Valor` â€” armazena valores institucionais (modelo separado, cadastro independente)

Na literatura (BSC, GPPEI), MissÃ£o, VisÃ£o e Valores sÃ£o os 3 pilares inseparÃ¡veis da identidade estratÃ©gica. A separaÃ§Ã£o em modelos distintos nÃ£o Ã© problema se for transparente para o usuÃ¡rio â€” mas atualmente o componente `MissaoVisao` mistura os dois contextos de forma confusa, e o modelo `MissaoVisaoValores` tem o nome enganoso (nÃ£o armazena valores, armazena sÃ³ missÃ£o e visÃ£o).

#### O que serÃ¡ feito

**Fase 1 â€” Renomear modelo (sem migration â€” sem alterar banco):**
- Renomear `App\Models\StrategicPlanning\MissaoVisaoValores` â†’ `App\Models\StrategicPlanning\IdentidadeEstrategica`
- Manter a tabela `tab_missao_visao_valores` (nÃ£o alterar banco)
- Atualizar todos os `use` e referÃªncias no cÃ³digo
- Atualizar `$table` para continuar apontando para `strategic_planning.tab_missao_visao_valores`

**Fase 2 â€” Componente:**
- Renomear componente para refletir "Identidade EstratÃ©gica" de forma clara
- Organizar seÃ§Ãµes explicitamente: "1. MissÃ£o", "2. VisÃ£o", "3. Valores"
- Adicionar campo de NegÃ³cio (`dsc_negocio`) â€” muitas metodologias incluem o NegÃ³cio antes da MissÃ£o
- Exibir os 3 pilares em cards lado a lado, nÃ£o em seÃ§Ãµes separadas

**Fase 3 â€” DocumentaÃ§Ã£o interna:**
- Atualizar `CLAUDE.md` para refletir a renomeaÃ§Ã£o
- Atualizar harness de documentaÃ§Ã£o

#### Arquivos envolvidos
- `app/Models/StrategicPlanning/MissaoVisaoValores.php` (renomear)
- `app/Livewire/StrategicPlanning/MissaoVisao.php`
- `resources/views/livewire/p-e-i/missao-visao.blade.php`
- Todos os arquivos que referenciam `MissaoVisaoValores` (grep necessÃ¡rio)

#### CritÃ©rio de conclusÃ£o
O modelo se chama `IdentidadeEstrategica`. O componente apresenta MissÃ£o, VisÃ£o e Valores como 3 pilares integrados, visualmente claros e conceitualmente corretos.

#### Valor agregado
Clareza conceitual para o usuÃ¡rio e para futuros desenvolvedores. Um sistema cujo cÃ³digo reflete corretamente os conceitos do domÃ­nio Ã© mais fÃ¡cil de manter, estender e auditar. Ã‰ o princÃ­pio do "Ubiquitous Language" de Eric Evans (Domain-Driven Design) â€” o cÃ³digo deve falar a mesma lÃ­ngua que o especialista do domÃ­nio.

---

### ROAD-011 â€” Futuro Almejado com estrutura SMART por objetivo
**Prioridade:** P3 â€” Melhoria  
**Status:** ðŸ”´ Pendente  
**Impacto:** Baixo  
**ReferÃªncia:** GPPEI/MGI 2025 (Futuro Almejado); SMART Goals (Doran, 1981)  

#### Problema identificado
O modelo `FuturoAlmejado` contÃ©m apenas `dsc_futuro_almejado` â€” texto livre. O GPPEI orienta que o futuro almejado responda "Como serÃ¡ a organizaÃ§Ã£o ao final do ciclo?" de forma quantificÃ¡vel e verificÃ¡vel. Sem estrutura, o futuro almejado se torna declaraÃ§Ã£o retÃ³rica sem conexÃ£o com as metas dos indicadores.

#### O que serÃ¡ feito

**Fase 1 â€” Modelo de dados:**
- Expandir `strategic_planning.tab_futuro_almejado_objetivo`:
  - `dsc_situacao_atual` (text nullable â€” linha de base qualitativa)
  - `dsc_futuro_almejado` (text â€” jÃ¡ existe, mantido)
  - `dsc_indicador_referencia` (text nullable â€” qual indicador mede o alcance)
  - `vlr_referencia_meta` (decimal nullable â€” valor quantitativo esperado)
  - `dte_horizonte` (date nullable â€” quando o futuro almejado deve ser realidade)

**Fase 2 â€” Componente:**
- Expandir `GerenciarFuturoAlmejado` com os novos campos
- Exibir lado a lado: "SituaÃ§Ã£o atual" â†’ "Futuro almejado"
- Vincular ao indicador de referÃªncia (seletor do indicador vinculado ao objetivo)

#### Arquivos envolvidos
- `database/migrations/StrategicPlanning/` (nova migration)
- `app/Models/StrategicPlanning/FuturoAlmejado.php`
- `app/Livewire/StrategicPlanning/GerenciarFuturoAlmejado.php`
- `resources/views/livewire/p-e-i/`

#### CritÃ©rio de conclusÃ£o
O futuro almejado de cada objetivo tem: situaÃ§Ã£o atual, descriÃ§Ã£o qualitativa do futuro, indicador de referÃªncia, valor meta e horizonte temporal.

#### Valor agregado
Conecta o discurso estratÃ©gico Ã  realidade mensurÃ¡vel. Um "futuro almejado" sem nÃºmero Ã© desejo. Com nÃºmero, prazo e indicador de referÃªncia, Ã© compromisso verificÃ¡vel â€” e auditÃ¡vel. Ã‰ a materializaÃ§Ã£o do princÃ­pio japonÃªs "o que nÃ£o pode ser medido nÃ£o pode ser gerenciado".

---

## HistÃ³rico de atualizaÃ§Ãµes deste documento

| Data | VersÃ£o | DescriÃ§Ã£o | Autor |
|---|---|---|---|
| 2026-06-25 | 1.0 | CriaÃ§Ã£o do roadmap com 11 itens da varredura metodolÃ³gica | Sistema PEI |
| 2026-06-25 | 1.1 | ROAD-001 concluÃ­do â€” encaminhamentos formais na RAE | Sistema PEI |
| 2026-06-25 | 1.2 | ROAD-002 concluÃ­do â€” desdobramento em cascata de objetivos (Hoshin Kanri) | Sistema PEI |

---

## Painel de status consolidado

| ID | TÃ­tulo resumido | Prioridade | Status | Impacto |
|---|---|---|---|---|
| ROAD-001 | Ciclo PDCA completo â€” RAE gera ajustes formais | P1 | ðŸŸ¢ ConcluÃ­do | Alto |
| ROAD-002 | Desdobramento em cascata de objetivos | P1 | ðŸŸ¢ ConcluÃ­do | Alto |
| ROAD-003 | EstratÃ©gias de resposta a riscos (ISO 31000) | P2 | ðŸŸ¢ ConcluÃ­do | MÃ©dio |
| ROAD-004 | Ãndice consolidado de desempenho do PEI | P2 | ðŸŸ¢ ConcluÃ­do | MÃ©dio |
| ROAD-005 | Rastreabilidade bidirecional Plano â†” Indicador | P2 | ðŸŸ¢ ConcluÃ­do | MÃ©dio |
| ROAD-006 | AnÃ¡lise de tendÃªncia temporal dos indicadores | P2 | ðŸŸ¢ ConcluÃ­do | MÃ©dio |
| ROAD-007 | Matriz TOWS: da anÃ¡lise ambiental Ã s estratÃ©gias | P3 | ðŸŸ¢ ConcluÃ­do | MÃ©dio |
| ROAD-008 | Bloqueios e guardrails metodolÃ³gicos no fluxo | P3 | ðŸŸ¢ ConcluÃ­do | MÃ©dio |
| ROAD-009 | AnÃ¡lise de causa raiz na RAE (5 PorquÃªs / Ishikawa) | P3 | ðŸŸ¢ ConcluÃ­do | MÃ©dio |
| ROAD-010 | UnificaÃ§Ã£o conceitual da Identidade EstratÃ©gica | P3 | ðŸŸ¢ ConcluÃ­do | Baixo |
| ROAD-011 | Futuro Almejado com estrutura SMART por objetivo | P3 | ðŸŸ¢ ConcluÃ­do | Baixo |


