# SISTEMA DE PLANEJAMENTO ESTRATÉGICO EMPRESARIAL - ARTEFATOS COMPLETOS

**Versão:** 2.0  
**Data:** Janeiro 2025  
**Status:** Pronto para Desenvolvimento  
**Desenvolvedor:** Solo (Analista de Sistema)  
**Stack:** Laravel 12 + Livewire 3 + Bootstrap 5 + Alpine.js + PostgreSQL

---

## ÍNDICE

1. [Visão Geral e Contexto](#1-visão-geral-e-contexto)
2. [Personas e Usuários](#2-personas-e-usuários)
3. [Histórias de Usuário](#3-histórias-de-usuário)
4. [Requisitos Funcionais](#4-requisitos-funcionais)
5. [Requisitos Não-Funcionais](#5-requisitos-não-funcionais)
6. [Arquitetura de Dados](#6-arquitetura-de-dados)
7. [Modelos Eloquent e Relacionamentos](#7-modelos-eloquent-e-relacionamentos)
8. [Matriz de Relacionamentos](#8-matriz-de-relacionamentos)
9. [Estrutura de Pastas do Projeto](#9-estrutura-de-pastas-do-projeto)
10. [Componentes e Interface](#10-componentes-e-interface)
11. [Fluxos de Negócio](#11-fluxos-de-negócio)
12. [Cronograma Detalhado](#12-cronograma-detalhado)
13. [Riscos e Mitigações](#13-riscos-e-mitigações)
14. [Métricas de Sucesso](#14-métricas-de-sucesso)

---

## 1. VISÃO GERAL E CONTEXTO

### 1.1 Problema e Oportunidade

**Contexto:**
O cliente possui um sistema legado de planejamento estratégico com estrutura de banco de dados sólida, porém com interface obsoleta e funcionalidades limitadas. A organização necessita modernizar a plataforma mantendo a integridade dos dados históricos e expandindo as capacidades analíticas.

**Objetivos do Projeto:**
1. Modernizar a interface com arquitetura reativa (Livewire 3)
2. Manter compatibilidade 100% com banco de dados legado
3. Expandir funcionalidades analíticas (SWOT, PESTEL, Canvas, Porter, BCG)
4. Implementar dashboards executivos interativos
5. Aprimorar usabilidade e performance
6. Estabelecer base para futuras expansões (APIs, Mobile, IA)

### 1.2 Escopo do Sistema

**Módulos Principais:**
1. **Gestão Organizacional** - Estrutura hierárquica de organizações
2. **Identidade Estratégica** - Missão, Visão, Valores, Futuro Almejado
3. **Análises Estratégicas** - SWOT, PESTEL, Canvas, Porter, BCG, Cadeia de Valor
4. **Objetivos e Perspectivas** - BSC com 4 perspectivas
5. **KPIs e Indicadores** - Métricas com histórico, linhas base e metas
6. **Planos de Ação** - Ações, Iniciativas e Projetos com indicadores
7. **Execução e Monitoramento** - Acompanhamento de progresso em tempo real
8. **Relatórios e Análises** - Exportação, comparativos, projeções
9. **Auditoria e Segurança** - Logs completos de alterações
10. **Gestão de Usuários** - Controle de acesso por perfil

### 1.3 Stack Tecnológico

| Camada | Tecnologia | Versão |
|--------|-----------|--------|
| **Backend** | Laravel | 12.x |
| **Frontend** | Blade Templates | - |
| **Reatividade** | Livewire | 3.x |
| **Componentes UI** | Bootstrap | 5.x |
| **Interatividade** | Alpine.js | 3.x |
| **Banco de Dados** | PostgreSQL | 14+ |
| **ORM** | Eloquent | 12.x |
| **Gráficos** | Chart.js | 3.x |
| **Tabelas** | DataTables | 1.13+ |
| **Deploy** | Docker/Linux | - |

---

## 2. PERSONAS E USUÁRIOS

### 2.1 Diretoria Executiva

**Nome:** Carlos Silva  
**Idade:** 52 anos  
**Cargo:** Diretor Geral  

**Características:**
- Tomador de decisão estratégica
- Precisa de informações consolidadas rapidamente
- Acesso a todos os dados da organização
- Não tem tempo para navegação complexa

**Necessidades:**
- Dashboard executivo com KPIs críticos em primeiro plano
- Visualização de progresso vs. meta em tempo real
- Alertas de desvios críticos
- Relatórios por período (semanal, mensal, trimestral)
- Exportação em PDF para apresentações

**Permissões:**
- Visualizar tudo
- Criar ciclos de planejamento
- Aprovar estratégia de topo

---

### 2.2 Administrador de Unidade

**Nome:** Maria Santos  
**Idade:** 38 anos  
**Cargo:** Gerente de Planejamento

**Características:**
- Responsável por uma ou mais unidades organizacionais
- Gerencia objetivos e indicadores da unidade
- Coordena com gestores de ação
- Precisa de histórico e comparativos

**Necessidades:**
- Visualizar objetivos e KPIs da sua unidade
- Criar e editar planos de ação
- Aprovar atualizações de indicadores
- Gerar relatórios da unidade
- Delegar responsabilidades

**Permissões:**
- Gerenciar dados da sua unidade
- Visualizar unidades subordinadas (se houver)
- Criar analistas
- Não pode ver unidades de outras estruturas

---

### 2.3 Gestor Responsável de Ação

**Nome:** João Oliveira  
**Idade:** 45 anos  
**Cargo:** Gestor de Projetos

**Características:**
- Responsável pela execução de planos de ação
- Atualiza indicadores regularmente
- Gerencia equipe de executores
- Foco em entregas práticas

**Necessidades:**
- Visualizar ações pelas quais é responsável
- Lançar valores de indicadores
- Adicionar comentários e documentos
- Acompanhar progresso vs. meta
- Criar dependências entre ações

**Permissões:**
- Editar apenas suas ações
- Visualizar ações relacionadas
- Lançar valores de indicadores
- Não pode excluir ações

---

### 2.4 Gestor Substituto

**Nome:** Paula Costa  
**Idade:** 35 anos  
**Cargo:** Coordenadora de Projetos

**Características:**
- Substitui gestor responsável em ausências
- Mesmo acesso do responsável enquanto substituindo
- Temporário ou permanente

**Necessidades:**
- Acesso completo às ações que substitui
- Visibilidade de quem é o responsável principal
- Auditoria clara de alterações feitas

**Permissões:**
- Idênticas ao gestor responsável (por ação)
- Acesso temporário/condicional

---

### 2.5 Super Administrador

**Nome:** Roberto Machado  
**Idade:** 40 anos  
**Cargo:** Administrador de TI

**Características:**
- Cuida da infraestrutura e integridade do sistema
- Responsável por backups e performance
- Gerencia acessos de usuários
- Visualiza toda auditoria

**Necessidades:**
- Acesso total a todas as funcionalidades
- Visualizar logs de auditoria completos
- Gerenciar usuários e perfis
- Configurar períodos de planejamento
- Validar integridade de dados

**Permissões:**
- Acesso absoluto
- Bypass de todas as restrições
- Acesso a logs de sistema

---

## 3. HISTÓRIAS DE USUÁRIO

### 3.1 Autenticação e Acesso

**US-001: Realizar Login no Sistema**
\`\`\`
Como: Qualquer usuário
Preciso: Autenticar no sistema com email e senha
Para: Acessar minhas funcionalidades permitidas

Critérios de Aceitação:
- Campo de email com validação
- Campo de senha mascarado
- Botão "Entrar" desabilitado se campos vazios
- Mensagem de erro se credenciais inválidas
- Redirecionamento para dashboard após sucesso
- Manter sessão por 12 horas de inatividade
- Opção "Lembrar-me" por 30 dias

Dados de Teste:
- Email: adm@adm.gov.br
- Senha: teste123
\`\`\`

**US-002: Redefinir Senha**
\`\`\`
Como: Usuário que esqueceu a senha
Preciso: Redefinir minha senha de forma segura
Para: Recuperar acesso ao sistema

Critérios de Aceitação:
- Link "Esqueci minha senha" na tela de login
- Solicitar email
- Enviar link com token válido por 2 horas
- Tela de nova senha com confirmação
- Validar força da senha (mínimo 8 caracteres, 1 maiúscula, 1 número, 1 caractere especial)
- Após resetar, notificar por email
- Invalidar token após uso
\`\`\`

**US-003: Mudar Senha no Primeiro Acesso**
\`\`\`
Como: Novo usuário ou administrador resetou minha senha
Preciso: Obrigatoriamente mudar minha senha no primeiro login
Para: Garantir que tenho uma senha segura pessoal

Critérios de Aceitação:
- Após login com senha padrão, redirecionar para tela obrigatória de mudança
- Não permitir continuar sem mudar
- Aplicar mesmas regras de força de senha
- Bloquear uso de senhas anteriores (últimas 5)
- Após mudança, ir para dashboard
\`\`\`

### 3.2 Identidade Organizacional

**US-010: Visualizar Identidade Estratégica**
\`\`\`
Como: Qualquer usuário autenticado
Preciso: Ver a Missão, Visão e Valores da minha organização
Para: Entender o contexto estratégico

Critérios de Aceitação:
- Mostrar missão, visão e valores em cards destacados
- Mostrar versão ativa (data de vigência)
- Mostrar histórico de versões anteriores
- Permitir expansão para ver versão completa
- Mostrar "Futuro Almejado" se disponível
- Indicar data de próxima revisão
\`\`\`

**US-011: Criar/Editar Identidade Estratégica**
\`\`\`
Como: Administrador da Unidade
Preciso: Criar ou editar Missão, Visão, Valores e Futuro Almejado
Para: Manter atualizada a identidade da unidade

Critérios de Aceitação:
- Formulário com 4 campos principais (Missão, Visão, Valores, Futuro Almejado)
- Cada campo com editor de texto rico (até 2000 caracteres)
- Validação obrigatória de preenchimento
- Opção de ativar nova versão imediatamente ou agendar
- Versão anterior fica como histórico (soft delete)
- Log de quem criou/editou e quando
- Preview antes de salvar
\`\`\`

### 3.3 Análises Estratégicas - SWOT

**US-020: Visualizar Análise SWOT**
\`\`\`
Como: Qualquer usuário
Preciso: Ver a análise SWOT da organização em formato matricial
Para: Entender forças, fraquezas, oportunidades e ameaças

Critérios de Aceitação:
- Matriz 2x2 com cores distintas (Strengths: verde, Weaknesses: vermelho, Opportunities: azul, Threats: laranja)
- Cada quadrante mostra lista de itens
- Itens agrupáveis por categoria
- Itens ordenáveis por data ou impacto
- Filtro por período de análise
- Filtro por categoria (Recursos Humanos, Financeiro, Tecnologia, etc.)
- Mostrar quem criou e quando
\`\`\`

**US-021: Registrar Item SWOT**
\`\`\`
Como: Administrador da Unidade ou acima
Preciso: Adicionar itens de Strengths, Weaknesses, Opportunities ou Threats
Para: Documentar análise estratégica

Critérios de Aceitação:
- Modal com select para tipo (SWOT)
- Campo de descrição (até 500 caracteres)
- Campo de categoria (dropdown)
- Campo de nível de impacto (1-5 ou Baixo/Alto)
- Campo de probabilidade (para Ameaças e Oportunidades)
- Opção de vinculação com Objetivo
- Salvar e abrir novo item ou fechar
- Validação de campos obrigatórios
\`\`\`

**US-022: Priorizar Itens SWOT**
\`\`\`
Como: Gestor de Planejamento
Preciso: Ordenar itens SWOT por importância/impacto
Para: Focar nas análises mais relevantes

Critérios de Aceitação:
- Drag-and-drop para reordenar itens
- Salvar ordem automaticamente
- Mostrar score de priorização
- Filtrar por nível de impacto
- Destacar top 5 itens de cada quadrante
\`\`\`

### 3.4 Análises Estratégicas - PESTEL

**US-030: Visualizar Análise PESTEL**
\`\`\`
Como: Qualquer usuário
Preciso: Ver análise PESTEL (Político, Econômico, Social, Tecnológico, Ecológico, Legal)
Para: Entender fatores externos que impactam a estratégia

Critérios de Aceitação:
- Visualização em abas (6 abas, uma para cada fator)
- Cada aba com lista de itens
- Indicador de impacto (baixo/médio/alto)
- Indicador de tendência (melhorando/piorando/estável)
- Filtro por período
- Timeline de evolução dos fatores
\`\`\`

**US-031: Registrar Fator PESTEL**
\`\`\`
Como: Administrador da Unidade
Preciso: Adicionar análise de cada fator PESTEL
Para: Documentar análise do ambiente externo

Critérios de Aceitação:
- Formulário por tipo de fator (P/E/S/T/E/L)
- Descrição do fator
- Nível de impacto (1-5)
- Tendência (melhoria/piora/estável)
- Ações mitigation (se ameaçador)
- Oportunidades derivadas
\`\`\`

### 3.5 Canvas de Modelo de Negócio

**US-040: Visualizar Canvas**
\`\`\`
Como: Qualquer usuário
Preciso: Ver o Canvas de Modelo de Negócio em layout visual 2x5
Para: Entender a proposta de valor

Critérios de Aceitação:
- Visualização canvas 2x5 com cores
- 9 blocos: Parceiros-chave, Atividades-chave, Proposição de Valor, Relacionamento com Cliente, Segmentos de Clientes, Recursos-chave, Canais, Estrutura de Custos, Fluxos de Receita
- Cada bloco editável via click
- Suporte a múltiplas versões (versionamento)
- Comparação entre versões
- Timeline de evolução
\`\`\`

**US-041: Editar Canvas**
\`\`\`
Como: Gestor de Estratégia
Preciso: Editar cada bloco do Canvas
Para: Manter o modelo de negócio atualizado

Critérios de Aceitação:
- Click em qualquer bloco abre editor
- Editor com campo de texto rico
- Possibilidade de adicionar comentários
- Histórico de versões com author e data
- Validação de preenchimento mínimo
- Notificação de alterações para stakeholders
\`\`\`

### 3.6 Matriz de Análise Competitiva - 5 Forças de Porter

**US-050: Visualizar 5 Forças de Porter**
\`\`\`
Como: Qualquer usuário
Preciso: Ver análise das 5 forças competitivas em visualização radial
Para: Entender intensidade da competição

Critérios de Aceitação:
- Gráfico radial/spider com 5 eixos
- 5 forças: Ameaça de novos concorrentes, Poder dos fornecedores, Rivalidade entre concorrentes, Poder dos clientes, Ameaça de produtos/serviços substitutos
- Score de 1-5 para cada força
- Cor vermelha para ameaças altas (4-5)
- Cor amarela para médias (2-3)
- Cor verde para baixas (1)
- Histórico de evolução
\`\`\`

**US-051: Avaliar Força Competitiva**
\`\`\`
Como: Analista Estratégico
Preciso: Avaliar intensidade de cada força competitiva
Para: Quantificar o ambiente competitivo

Critérios de Aceitação:
- Formulário com descrição para cada força
- Score de 1-5 (Baixo a Alto)
- Evidências/Justificativa
- Ações estratégicas derivadas
- Data da análise
\`\`\`

### 3.7 Matriz de Crescimento - BCG (Boston Consulting Group)

**US-060: Visualizar Matriz BCG**
\`\`\`
Como: Qualquer usuário
Preciso: Ver posicionamento de produtos/unidades na matriz crescimento vs. market share
Para: Entender dinâmica de portfólio

Critérios de Aceitação:
- Matriz 2x2: Crescimento (Y) vs. Market Share (X)
- 4 quadrantes: Estrelas (Alto/Alto), Vacas de Ouro (Alto/Baixo), Pontos de Interrogação (Baixo/Alto), Cães (Baixo/Baixo)
- Bolhas representando produtos (tamanho = receita)
- Cores por quadrante
- Hover mostra detalhes
- Filtro por categoria
\`\`\`

**US-061: Adicionar Produto/Unidade à BCG**
\`\`\`
Como: Gestor de Portfólio
Preciso: Posicionar produtos ou unidades na matriz BCG
Para: Acompanhar evolução do portfólio

Critérios de Aceitação:
- Formulário com: Nome, Taxa crescimento mercado (%), Market share (%), Receita
- Cálculo automático do quadrante
- Histórico de posicionamento ao longo do tempo
- Sugestões de ações por quadrante (Estrela: Investir, Vaca: Extrair, Interrogação: Analisar, Cão: Colher/Encerrar)
\`\`\`

### 3.8 Objetivos Estratégicos

**US-070: Visualizar Objetivos por Perspectiva BSC**
\`\`\`
Como: Qualquer usuário
Preciso: Ver objetivos estratégicos organizados por perspectiva Balanced Scorecard
Para: Entender direcionamento da estratégia

Critérios de Aceitação:
- 4 abas para perspectivas: Financeira, Clientes, Processos Internos, Aprendizado e Crescimento
- Cada aba mostra objetivos em cards ou lista
- Card mostra: Nome, Descrição resumida, KPIs vinculados, Status, Progress bar
- Filtro por responsável
- Filtro por status
- Ordenação por prioridade
\`\`\`

**US-071: Criar Objetivo**
\`\`\`
Como: Administrador da Unidade
Preciso: Criar novo objetivo vinculado a uma perspectiva
Para: Estruturar a estratégia

Critérios de Aceitação:
- Seleção de perspectiva (obrigatória)
- Nome do objetivo (até 200 caracteres, obrigatório)
- Descrição detalhada (até 1000 caracteres)
- Vinculação com análises (SWOT, PESTEL, Canvas - opcional)
- Nível hierárquico (Corporativo, Departamental, Operacional)
- Período de vigência (Data início / Data fim)
- Responsável principal
- Status (Planejado, Em Execução, Concluído, Cancelado)
- Validação de duplicatas
\`\`\`

**US-072: Vincular KPI a Objetivo**
\`\`\`
Como: Gestor de Indicadores
Preciso: Associar um KPI/Indicador a um objetivo
Para: Medir o sucesso do objetivo

Critérios de Aceitação:
- Seleção de objetivo
- Seleção ou criação de indicador
- Peso do indicador para o objetivo (em %)
- Validação que peso total do objetivo = 100%
- Indicador pode ter múltiplos objetivos (relacionamento many-to-many)
\`\`\`

### 3.9 KPIs e Indicadores

**US-080: Visualizar Indicadores de Objetivo**
\`\`\`
Como: Qualquer usuário
Preciso: Ver todos os indicadores (KPIs) de um objetivo
Para: Acompanhar performance

Critérios de Aceitação:
- Lista ou cards de indicadores
- Cada indicador mostra: Nome, Meta, Atual, % Atingimento, Tendência
- Status visual (Verde: >80%, Amarelo: 60-80%, Vermelho: <60%)
- Gráfico de evolução mensal/trimestral
- Filtro por período
- Ordenação por performance
\`\`\`

**US-081: Lançar Valor de Indicador**
\`\`\`
Como: Gestor Responsável
Preciso: Registrar valor realizado de um indicador
Para: Atualizar o acompanhamento

Critérios de Aceitação:
- Modal com: Indicador (pré-selecionado), Período (mês/trimestre), Valor Realizado, Observações
- Validação de tipo de dado (decimal, inteiro, %)
- Comparação visual com meta
- Cálculo automático de % atingimento
- Opção de anexar arquivo (justificativa/evidência)
- Histórico de valores anteriores visível
- Notificação de desvios críticos (se <60%)
\`\`\`

**US-082: Visualizar Histórico do Indicador**
\`\`\`
Como: Qualquer usuário
Preciso: Ver evolução de um indicador ao longo do tempo
Para: Entender tendências e padrões

Critérios de Aceitação:
- Gráfico de linha: Período (X) vs. Valor (Y)
- Linha de meta
- Linha de linha base
- Período personalizável (últimas 12 semanas, último ano, período custom)
- Tabela de valores mensais/trimestrais
- Exportação em CSV/PDF
- Análise de tendência (crescente/decrescente/estável)
- Projeção simples (se tendência continuar)
\`\`\`

**US-083: Criar Indicador (KPI)**
\`\`\`
Como: Administrador da Unidade
Preciso: Criar novo indicador/KPI
Para: Medir performance de objetivo

Critérios de Aceitação:
- Nome (obrigatório)
- Descrição
- Tipo (Quantitativo, Qualitativo, Percentual)
- Unidade de Medida (obrigatória)
- Meta por ano (múltiplas linhas)
- Linha Base (valor inicial)
- Periodicidade de Medição (Mensal, Trimestral, Anual)
- Fórmula de cálculo (se aplicável)
- Fonte de dados
- Responsável pela medição
- Validação de campos obrigatórios
- Versioning (histórico de modificações)
\`\`\`

### 3.10 Planos de Ação

**US-090: Visualizar Planos de Ação**
\`\`\`
Como: Qualquer usuário
Preciso: Ver lista de planos de ação (Ações, Iniciativas, Projetos)
Para: Acompanhar execução

Critérios de Aceitação:
- 3 abas: Ações, Iniciativas, Projetos
- Cada aba com lista/cards mostrando: Nome, Período, Responsável, Status, % Conclusão, KPIs
- Filtro por: Responsável, Status, Período, Organização
- Ordenação por prioridade ou data
- Cor de fundo por status (Planejado: cinza, Em Execução: azul, Concluído: verde, Cancelado: vermelho)
- Link para detalhes
\`\`\`

**US-091: Criar Plano de Ação**
\`\`\`
Como: Gestor de Planejamento
Preciso: Criar ação, iniciativa ou projeto
Para: Executar estratégia

Critérios de Aceitação:
- Seleção de tipo (Ação/Iniciativa/Projeto)
- Objetivo vinculado (obrigatório)
- Título (obrigatório)
- Descrição detalhada
- Data de Início e Data de Fim (obrigatórias)
- Responsável principal (obrigatório)
- Responsável substituto (opcional)
- Orçamento previsto
- Indicadores vinculados
- Entregas principais (repetível)
- Periodicidade de atualização (Mensal, Semanal, Quinzenal)
- Prioridade (Alta, Média, Baixa)
- Status inicial (Planejado)
\`\`\`

**US-092: Atualizar Progresso de Ação**
\`\`\`
Como: Gestor Responsável
Preciso: Atualizar progresso da ação
Para: Manter informação atual

Critérios de Aceitação:
- % de conclusão (0-100%)
- Status (Planejado, Em Execução, Concluído, Cancelado)
- Observações/Comentários
- Arquivo(s) de evidência
- Data da atualização
- Calcular automaticamente % com base em entregas concluídas
- Histórico de atualizações
- Notificação de atraso se data fim passou
\`\`\`

**US-093: Vincular Entregas à Ação**
\`\`\`
Como: Gestor de Projetos
Preciso: Definir entregas (deliverables) da ação
Para: Detalhar o que precisa ser entregue

Critérios de Aceitação:
- Adicionar múltiplas entregas
- Cada entrega com: Nome, Descrição, Data Prevista, Status
- Marcar entrega como concluída
- Anexar arquivo da entrega
- Cálculo automático: % ação = média % entregas
- Histórico de modificações em entrega
\`\`\`

### 3.11 Dashboard Executivo

**US-100: Visualizar Dashboard Executivo**
\`\`\`
Como: Diretor/Gestor
Preciso: Ver visão consolidada da estratégia em um dashboard
Para: Tomar decisões rapidamente

Critérios de Aceitação:
- Widget de Objetivos: Quantitativo (Total, Em Execução, Concluído, Atrasado)
- Widget de KPIs: Top 5 com melhor e pior performance
- Widget de Saúde por Perspectiva: 4 gauges (% atingimento por perspectiva)
- Widget de Ações: Status (Planejado/Execução/Concluído/Cancelado)
- Widget de Variação de KPIs: Comparativo período anterior
- Gráfico de Heatmap: Objetivos vs. Perspectivas (cores por performance)
- Timeline de eventos: Próximas datas importantes
- Filtro por período (Mês, Trimestre, Ano)
- Filtro por organização (se multi-unidade)
\`\`\`

**US-101: Personalizar Dashboard**
\`\`\`
Como: Qualquer usuário
Preciso: Customizar widgets do dashboard
Para: Ver informação relevante para mim

Critérios de Aceitação:
- Drag-and-drop para reordenar widgets
- Ocultar/Mostrar widgets
- Configurar tamanho de widget (1 coluna, 2 colunas)
- Salvar configuração por usuário
- Opção de resetar para padrão
\`\`\`

### 3.12 Relatórios

**US-110: Gerar Relatório de Planejamento**
\`\`\`
Como: Gestor
Preciso: Exportar relatório completo de planejamento
Para: Apresentar e arquivar

Critérios de Aceitação:
- Seleção de período
- Seleção de elementos (Identidade, SWOT, Objetivos, KPIs, Ações, etc.)
- Formato de saída (PDF, XLSX, HTML)
- Incluir históricos e comparativos
- Capa com logo da organização
- Índice automático
- Paginação e cabeçalho/rodapé
\`\`\`

**US-111: Comparar Períodos**
\`\`\`
Como: Analista
Preciso: Comparar performance entre períodos
Para: Identificar tendências

Critérios de Aceitação:
- Seleção de 2 períodos
- Tabela comparativa de KPIs
- Cálculo de variação (%, pontos)
- Indicador de melhora/piora
- Gráfico de comparação
\`\`\`

### 3.13 Cadeia de Valor

**US-120: Visualizar Cadeia de Valor**
\`\`\`
Como: Qualquer usuário
Preciso: Ver a cadeia de valor da organização
Para: Entender processos e atividades

Critérios de Aceitação:
- Visualização de atividades por perspectiva
- Para cada atividade: Entrada → Transformação → Saída
- Agrupamento por perspectiva BSC
- Filtro por nível de hierarquia
\`\`\`

**US-121: Registrar Atividade de Cadeia de Valor**
\`\`\`
Como: Gestor de Processos
Preciso: Documentar atividades da cadeia de valor
Para: Manter mapa de processos

Critérios de Aceitação:
- Seleção de perspectiva
- Nome da atividade
- Descrição
- Entrada (insumos)
- Transformação (processo)
- Saída (resultado)
- Responsável
\`\`\`

### 3.14 Segurança e Auditoria

**US-130: Visualizar Log de Auditoria**
\`\`\`
Como: Administrador
Preciso: Ver histórico de todas as alterações no sistema
Para: Rastrear mudanças e detectar anomalias

Critérios de Aceitação:
- Tabela com: Data/Hora, Usuário, Ação, Tabela, Antes, Depois, IP
- Filtro por: Usuário, Tabela, Período, Tipo de ação (Create, Update, Delete)
- Busca por valor
- Expansão para ver antes/depois completo
- Exportação em CSV
- Retenção mínima: 2 anos
\`\`\`

**US-131: Gerenciar Usuários e Perfis**
\`\`\`
Como: Super Administrador
Preciso: Criar, editar e deletar usuários, e atribuir perfis/permissões
Para: Controlar acesso ao sistema

Critérios de Aceitação:
- CRUD de usuários
- Atribuição de perfil por organização
- Ativação/Desativação de usuário
- Reset de senha (enviar link)
- Visualizar último acesso
- Histórico de mudanças de perfil
\`\`\`

### 3.15 Integrações Futuras

**US-140: Exportar para API**
\`\`\`
Como: Administrador
Preciso: Exportar dados para sistemas externos via API REST
Para: Integrar com BI, ERP, etc.

Critérios de Aceitação:
- Endpoints RESTful para dados principais
- Autenticação por token (API key)
- Rate limiting
- Documentação Swagger
- Exemplos de uso
\`\`\`

---

## 4. REQUISITOS FUNCIONAIS

### 4.1 Gestão Organizacional

| ID | Requisito | Descrição | Prioridade |
|----|-----------|-----------|-----------|
| RF-001 | Estrutura Hierárquica | Suportar organizações aninhadas (matriz organizacional) | CRÍTICA |
| RF-002 | Multi-Organização | Mesmo usuário pode ter acesso a múltiplas organizações | CRÍTICA |
| RF-003 | Isolamento de Dados | Dados isolados por organização (não há vazamento) | CRÍTICA |
| RF-004 | Hierarquia Visualizável | Mostrar organograma visual | ALTA |

### 4.2 Identidade Estratégica

| ID | Requisito | Descrição | Prioridade |
|----|-----------|-----------|-----------|
| RF-010 | Missão/Visão/Valores | Gerenciar identidade organizacional | CRÍTICA |
| RF-011 | Versionamento | Histórico de versões com data de ativação | ALTA |
| RF-012 | Futuro Almejado | Campo adicional para visão de futuro | MÉDIA |
| RF-013 | Valores Corporativos | Gerenciar valores isoladamente | MÉDIA |

### 4.3 Análises Estratégicas - SWOT

| ID | Requisito | Descrição | Prioridade |
|----|-----------|-----------|-----------|
| RF-020 | Matriz SWOT | Criar e visualizar análise SWOT | CRÍTICA |
| RF-021 | Categorização SWOT | Itens SWOT categorizáveis | ALTA |
| RF-022 | Impacto/Probabilidade | Mensuração de impacto para cada item | ALTA |
| RF-023 | Histórico SWOT | Manter histórico de análises anteriores | MÉDIA |
| RF-024 | Vinculação SWOT-Objetivo | Linkar itens SWOT a objetivos estratégicos | MÉDIA |

### 4.4 Análises Estratégicas - PESTEL

| ID | Requisito | Descrição | Prioridade |
|----|-----------|-----------|-----------|
| RF-030 | Análise PESTEL | Seis abas (P/E/S/T/E/L) | ALTA |
| RF-031 | Tendências | Indicar melhora/piora de fatores | ALTA |
| RF-032 | Timeline PESTEL | Visualizar evolução ao longo do tempo | MÉDIA |

### 4.5 Análises Estratégicas - Canvas

| ID | Requisito | Descrição | Prioridade |
|----|-----------|-----------|-----------|
| RF-040 | Canvas 2x5 | Visualização de modelo de negócio | ALTA |
| RF-041 | Edição Canvas | Editar cada bloco individualmente | ALTA |
| RF-042 | Versionamento Canvas | Histórico de versões | MÉDIA |
| RF-043 | Comparação Canvas | Comparar versões lado a lado | MÉDIA |

### 4.6 Análises Estratégicas - 5 Forças de Porter

| ID | Requisito | Descrição | Prioridade |
|----|-----------|-----------|-----------|
| RF-050 | Matriz Porter | Visualização radial de 5 forças | MÉDIA |
| RF-051 | Scoring Forças | Score de 1-5 para cada força | MÉDIA |
| RF-052 | Histórico Porter | Acompanhar evolução | BAIXA |

### 4.7 Análises Estratégicas - Matriz BCG

| ID | Requisito | Descrição | Prioridade |
|----|-----------|-----------|-----------|
| RF-060 | Matriz BCG | Visualização 2x2 com bolhas | MÉDIA |
| RF-061 | Posicionamento BCG | Adicionar produtos/unidades | MÉDIA |
| RF-062 | Histórico BCG | Evolução de posicionamento | BAIXA |

### 4.8 Balanced Scorecard

| ID | Requisito | Descrição | Prioridade |
|----|-----------|-----------|-----------|
| RF-070 | 4 Perspectivas | Financeira, Clientes, Processos, Aprendizado | CRÍTICA |
| RF-071 | Objetivos por Perspectiva | Organizar objetivos em perspectivas | CRÍTICA |
| RF-072 | Relacionamento Objetivo-Perspectiva | Cada objetivo linkado a uma perspectiva | CRÍTICA |

### 4.9 Objetivos Estratégicos

| ID | Requisito | Descrição | Prioridade |
|----|-----------|-----------|-----------|
| RF-080 | CRUD Objetivo | Criar, ler, editar, deletar objetivos | CRÍTICA |
| RF-081 | Nível Hierárquico | Corporativo, Departamental, Operacional | ALTA |
| RF-082 | Período de Vigência | Data de início e fim do objetivo | ALTA |
| RF-083 | Status Objetivo | Planejado, Execução, Concluído, Cancelado | ALTA |
| RF-084 | Histórico Objetivo | Versioning de modificações | MÉDIA |

### 4.10 KPIs e Indicadores

| ID | Requisito | Descrição | Prioridade |
|----|-----------|-----------|-----------|
| RF-090 | CRUD Indicador | Criar, ler, editar, deletar KPIs | CRÍTICA |
| RF-091 | Histórico Valores | Manter histórico de valores lançados | CRÍTICA |
| RF-092 | Linha Base | Valor de referência inicial | ALTA |
| RF-093 | Metas por Ano | Metas podem variar por ano | ALTA |
| RF-094 | Cálculo Atingimento | % Atingimento calculado automaticamente | ALTA |
| RF-095 | Fórmulas Customizadas | Suportar fórmulas de cálculo | MÉDIA |
| RF-096 | Tendência | Indicar se está melhorando/piorando | ALTA |

### 4.11 Planos de Ação

| ID | Requisito | Descrição | Prioridade |
|----|-----------|-----------|-----------|
| RF-100 | CRUD Plano Ação | Criar, ler, editar, deletar | CRÍTICA |
| RF-101 | 3 Tipos de Execução | Ação, Iniciativa, Projeto | ALTA |
| RF-102 | Responsáveis | Principal + Substituto | ALTA |
| RF-103 | Entregas (Deliverables) | Desdobrar em entregas | ALTA |
| RF-104 | % Progresso | Cálculo automático com base em entregas | ALTA |
| RF-105 | Orçamento | Campo de orçamento previsto | MÉDIA |
| RF-106 | Periodicidade Atualização | Definir frequência esperada | ALTA |

### 4.12 Dashboard e Visualizações

| ID | Requisito | Descrição | Prioridade |
|----|-----------|-----------|-----------|
| RF-110 | Dashboard Executivo | Visão consolidada da estratégia | CRÍTICA |
| RF-111 | Widgets Customizáveis | Reordenar e ocultar/mostrar widgets | ALTA |
| RF-112 | Filtros Dashboard | Por período, organização | ALTA |
| RF-113 | Gráficos Interativos | Chart.js com interatividade | ALTA |

### 4.13 Relatórios

| ID | Requisito | Descrição | Prioridade |
|----|-----------|-----------|-----------|
| RF-120 | Relatório Estratégia | Export em PDF/XLSX | ALTA |
| RF-121 | Comparativo Períodos | Comparação de períodos | ALTA |
| RF-122 | Gráficos em Relatório | Inclusão de gráficos em exports | ALTA |

### 4.14 Cadeia de Valor

| ID | Requisito | Descrição | Prioridade |
|----|-----------|-----------|-----------|
| RF-130 | Visualização Cadeia Valor | Mostrar atividades por perspectiva | MÉDIA |
| RF-131 | Entrada-Transformação-Saída | Modelo ETS para processos | MÉDIA |

### 4.15 Segurança

| ID | Requisito | Descrição | Prioridade |
|----|-----------|-----------|-----------|
| RF-140 | Autenticação Segura | Login com email/senha | CRÍTICA |
| RF-141 | Controle Acesso por Perfil | 4 níveis de perfil | CRÍTICA |
| RF-142 | Row Level Security | Dados isolados por organização/unidade | CRÍTICA |
| RF-143 | Auditoria Completa | Log de todas as alterações | CRÍTICA |
| RF-144 | CSRF Protection | Proteção contra CSRF | CRÍTICA |
| RF-145 | XSS Prevention | Validação e sanitização | ALTA |
| RF-146 | Rate Limiting | Limite de tentativas de login | ALTA |

---

## 5. REQUISITOS NÃO-FUNCIONAIS

### 5.1 Performance

| ID | Requisito | Critério | Prioridade |
|----|-----------|----------|-----------|
| RNF-001 | Tempo de Carregamento | Dashboard < 2s em conexão 3G | CRÍTICA |
| RNF-002 | Responsividade | Interface < 100ms para interações | ALTA |
| RNF-003 | Busca | Busca em 100k+ registros < 500ms | ALTA |
| RNF-004 | Cache | Cachear dados frequentes (24h) | ALTA |
| RNF-005 | Índices BD | Índices em colunas de filtro/busca | CRÍTICA |

### 5.2 Escalabilidade

| ID | Requisito | Critério | Prioridade |
|----|-----------|----------|-----------|
| RNF-010 | Usuários Simultâneos | Suportar 500 usuários simultâneos | ALTA |
| RNF-011 | Crescimento Dados | Suportar 5 anos de histórico | ALTA |
| RNF-012 | Multi-Tenant | Suportar 10+ organizações | ALTA |

### 5.3 Disponibilidade

| ID | Requisito | Critério | Prioridade |
|----|-----------|----------|-----------|
| RNF-020 | Uptime | Mínimo 99% de disponibilidade | ALTA |
| RNF-021 | Backup | Backup diário com retenção 30 dias | CRÍTICA |
| RNF-022 | Disaster Recovery | RTO: 4h, RPO: 1h | ALTA |

### 5.4 Segurança

| ID | Requisito | Critério | Prioridade |
|----|-----------|----------|-----------|
| RNF-030 | SSL/TLS | Conexão HTTPS obrigatória | CRÍTICA |
| RNF-031 | Senhas | Criptografia bcrypt com salt | CRÍTICA |
| RNF-032 | Dados em BD | Criptografia de dados sensíveis (AES-256) | ALTA |
| RNF-033 | Logs | Logs por mínimo 2 anos | ALTA |
| RNF-034 | Rate Limiting | 5 tentativas login / 5 min | ALTA |

### 5.5 Usabilidade

| ID | Requisito | Critério | Prioridade |
|----|-----------|----------|-----------|
| RNF-040 | Responsividade | Mobile-first, funciona em tablets | ALTA |
| RNF-041 | Acessibilidade | WCAG 2.1 Level AA | MÉDIA |
| RNF-042 | Internacionalização | Pronto para múltiplos idiomas | MÉDIA |
| RNF-043 | Temas | Modo claro/escuro | BAIXA |

### 5.6 Manutenibilidade

| ID | Requisito | Critério | Prioridade |
|----|-----------|----------|-----------|
| RNF-050 | Cobertura Testes | ≥60% de cobertura | ALTA |
| RNF-051 | Documentação | Código documentado (phpdoc) | ALTA |
| RNF-052 | Padrões Código | PSR-12 + Laravel conventions | ALTA |

---

## 6. ARQUITETURA DE DADOS

### 6.1 Modelos do Banco Legado

O banco legado utiliza **PostgreSQL com dois schemas**:
- **Schema `public`**: Gestão de usuários, organizações, auditoria
- **Schema `pei`**: Dados estratégicos (Planejamento Estratégico Integrado)

### 6.2 Estrutura de Tabelas

#### Schema PUBLIC (Gestão de Acesso)

\`\`\`
tab_organizacoes
├─ cod_organizacao (UUID, PK)
├─ sgl_organizacao (VARCHAR)
├─ nom_organizacao (TEXT)
├─ rel_cod_organizacao (UUID, FK - self reference)
└─ timestamps (created_at, updated_at, deleted_at)

tab_perfil_acesso
├─ cod_perfil (UUID, PK)
├─ dsc_perfil (TEXT)
├─ dsc_permissao (TEXT)
└─ timestamps

users
├─ id (UUID, PK)
├─ name (VARCHAR)
├─ email (VARCHAR, UNIQUE)
├─ ativo (SMALLINT)
├─ adm (SMALLINT)
├─ password (VARCHAR)
├─ trocarsenha (SMALLINT)
└─ timestamps

rel_users_tab_organizacoes
├─ id (UUID, PK)
├─ user_id (UUID, FK)
├─ cod_organizacao (UUID, FK)
└─ timestamps

rel_users_tab_organizacoes_tab_perfil_acesso
├─ id (UUID, PK)
├─ user_id (UUID, FK)
├─ cod_organizacao (UUID, FK)
├─ cod_plano_de_acao (UUID, FK)
├─ cod_perfil (UUID, FK)
└─ timestamps

tab_perfil_acesso (Valores Pré-definidos)
├─ c00b9ebc-7014-4d37-97dc-7875e55fff2a: Super Administrador
├─ c00b9ebc-7014-4d37-97dc-7875e55fff3b: Administrador da Unidade
├─ c00b9ebc-7014-4d37-97dc-7875e55fff4c: Gestor(a) Responsável
└─ c00b9ebc-7014-4d37-97dc-7875e55fff5d: Gestor(a) Substituto(a)

acoes (Auditoria Simples)
├─ id (UUID, PK)
├─ table_id (VARCHAR)
├─ user_id (UUID, FK)
├─ table (VARCHAR)
├─ acao (TEXT)
└─ timestamps

tab_audit (Auditoria Detalhada)
├─ id (UUID, PK)
├─ acao (VARCHAR)
├─ antes (TEXT)
├─ depois (TEXT)
├─ table (VARCHAR)
├─ column_name (VARCHAR)
├─ data_type (VARCHAR)
├─ table_id (VARCHAR)
├─ ip (VARCHAR)
├─ user_id (UUID, FK)
├─ dte_expired_at (TIMESTAMP)
└─ timestamps

audits (Audit Model Laravel)
├─ id (BIGSERIAL, PK)
├─ user_type (VARCHAR)
├─ user_id (UUID)
├─ event (VARCHAR)
├─ auditable_type (VARCHAR)
├─ auditable_id (UUID)
├─ old_values (TEXT)
├─ new_values (TEXT)
├─ url (TEXT)
├─ ip_address (INET)
├─ user_agent (VARCHAR)
├─ tags (VARCHAR)
└─ timestamps
\`\`\`

#### Schema PEI (Planejamento Estratégico Integrado)

\`\`\`
tab_pei
├─ cod_pei (UUID, PK)
├─ dsc_pei (TEXT)
├─ num_ano_inicio_pei (SMALLINT)
├─ num_ano_fim_pei (SMALLINT)
└─ timestamps

tab_missao_visao_valores
├─ cod_missao_visao_valores (UUID, PK)
├─ dsc_missao (TEXT)
├─ dsc_visao (TEXT)
├─ cod_pei (UUID, FK)
├─ cod_organizacao (UUID, FK)
└─ timestamps

tab_valores
├─ cod_valor (UUID, PK)
├─ nom_valor (TEXT)
├─ dsc_valor (TEXT)
├─ cod_pei (UUID, FK)
├─ cod_organizacao (UUID, FK)
└─ timestamps

tab_futuro_almejado_objetivo_estrategico
├─ cod_futuro_almejado (UUID, PK)
├─ dsc_futuro_almejado (TEXT)
├─ cod_objetivo_estrategico (UUID, FK)
└─ timestamps

tab_perspectiva
├─ cod_perspectiva (UUID, PK)
├─ dsc_perspectiva (TEXT)
├─ num_nivel_hierarquico_apresentacao (SMALLINT)
├─ cod_pei (UUID, FK)
└─ timestamps

tab_nivel_hierarquico
└─ num_nivel_hierarquico_apresentacao (SMALLINT, PK)

tab_objetivo_estrategico
├─ cod_objetivo_estrategico (UUID, PK)
├─ nom_objetivo_estrategico (TEXT)
├─ dsc_objetivo_estrategico (TEXT)
├─ num_nivel_hierarquico_apresentacao (SMALLINT)
├─ cod_perspectiva (UUID, FK)
└─ timestamps

tab_tipo_execucao (Valores Pré-definidos)
├─ c00b9ebc-7014-4d37-97dc-7875e55fff1b: Ação
├─ ecef6a50-c010-4cda-afc3-cbda245b55b0: Iniciativa
└─ 57518c30-3bc5-4305-a998-8ce8b11550ed: Projeto

tab_plano_de_acao
├─ cod_plano_de_acao (UUID, PK)
├─ cod_objetivo_estrategico (UUID, FK)
├─ cod_tipo_execucao (UUID, FK)
├─ cod_organizacao (UUID, FK)
├─ num_nivel_hierarquico_apresentacao (SMALLINT)
├─ dsc_plano_de_acao (TEXT)
├─ dte_inicio (DATE)
├─ dte_fim (DATE)
├─ vlr_orcamento_previsto (DECIMAL)
├─ bln_status (VARCHAR)
├─ cod_ppa (VARCHAR)
├─ cod_loa (VARCHAR)
└─ timestamps

tab_entregas
├─ cod_entrega (UUID, PK)
├─ cod_plano_de_acao (UUID, FK)
├─ dsc_entrega (TEXT)
├─ bln_status (VARCHAR)
├─ dsc_periodo_medicao (VARCHAR)
├─ num_nivel_hierarquico_apresentacao (SMALLINT)
└─ timestamps

tab_indicador
├─ cod_indicador (UUID, PK)
├─ cod_plano_de_acao (UUID, FK)
├─ cod_objetivo_estrategico (UUID, FK)
├─ dsc_tipo (TEXT)
├─ nom_indicador (TEXT)
├─ dsc_indicador (TEXT)
├─ txt_observacao (TEXT)
├─ dsc_meta (TEXT)
├─ dsc_atributos (TEXT)
├─ dsc_referencial_comparativo (TEXT)
├─ dsc_unidade_medida (TEXT)
├─ num_peso (SMALLINT)
├─ bln_acumulado (VARCHAR)
├─ dsc_formula (TEXT)
├─ dsc_fonte (VARCHAR)
├─ dsc_periodo_medicao (VARCHAR)
└─ timestamps

tab_evolucao_indicador
├─ cod_evolucao_indicador (UUID, PK)
├─ cod_indicador (UUID, FK)
├─ num_ano (SMALLINT)
├─ num_mes (SMALLINT)
├─ vlr_previsto (DECIMAL)
├─ vlr_realizado (DECIMAL)
├─ txt_avaliacao (TEXT)
├─ bln_atualizado (VARCHAR)
└─ timestamps

tab_linha_base_indicador
├─ cod_linha_base (UUID, PK)
├─ cod_indicador (UUID, FK)
├─ num_linha_base (DECIMAL)
├─ num_ano (SMALLINT)
└─ timestamps

tab_meta_por_ano
├─ cod_meta_por_ano (UUID, PK)
├─ cod_indicador (UUID, FK)
├─ num_ano (SMALLINT)
├─ meta (DECIMAL)
└─ timestamps

rel_indicador_objetivo_estrategico_organizacao
├─ cod_indicador (UUID, FK)
├─ cod_organizacao (UUID, FK)
└─ timestamps

tab_grau_satisfacao
├─ cod_grau_satisfacao (UUID, PK)
├─ dsc_grau_satisfacao (TEXT)
├─ cor (VARCHAR)
├─ vlr_minimo (DECIMAL)
├─ vlr_maximo (DECIMAL)
└─ timestamps

tab_arquivos
├─ cod_arquivo (UUID, PK)
├─ cod_evolucao_indicador (UUID, FK)
├─ txt_assunto (TEXT)
├─ data (TEXT)
├─ dsc_nome_arquivo (TEXT)
├─ dsc_tipo (VARCHAR)
└─ timestamps

tab_atividade_cadeia_valor
├─ cod_atividade_cadeia_valor (UUID, PK)
├─ cod_pei (UUID, FK)
├─ cod_perspectiva (UUID, FK)
├─ dsc_atividade (TEXT)
└─ timestamps

tab_processos_atividade_cadeia_valor
├─ cod_processo_atividade_cadeia_valor (UUID, PK)
├─ cod_atividade_cadeia_valor (UUID, FK)
├─ dsc_entrada (TEXT)
├─ dsc_transformacao (TEXT)
├─ dsc_saida (TEXT)
└─ timestamps
\`\`\`

### 6.3 Novas Tabelas a Criar

Para as funcionalidades que não existem no banco legado:

#### Análise SWOT

\`\`\`sql
CREATE TABLE tab_analise_swot (
    cod_analise_swot UUID PRIMARY KEY,
    cod_pei UUID NOT NULL,
    cod_organizacao UUID NOT NULL,
    dsc_tipo VARCHAR(20) NOT NULL, -- 'strength', 'weakness', 'opportunity', 'threat'
    dsc_categoria VARCHAR(255),
    dsc_item TEXT NOT NULL,
    num_impacto SMALLINT, -- 1-5
    num_probabilidade SMALLINT, -- 1-5 (para threats e opportunities)
    cod_objetivo_estrategico UUID, -- opcional, vinculação
    num_prioridade SMALLINT, -- ordem de visualização
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    FOREIGN KEY (cod_pei) REFERENCES tab_pei(cod_pei),
    FOREIGN KEY (cod_organizacao) REFERENCES tab_organizacoes(cod_organizacao),
    FOREIGN KEY (cod_objetivo_estrategico) REFERENCES tab_objetivo_estrategico(cod_objetivo_estrategico)
);
\`\`\`

#### Análise PESTEL

\`\`\`sql
CREATE TABLE tab_analise_pestel (
    cod_analise_pestel UUID PRIMARY KEY,
    cod_pei UUID NOT NULL,
    cod_organizacao UUID NOT NULL,
    dsc_fator VARCHAR(20) NOT NULL, -- 'politico', 'economico', 'social', 'tecnologico', 'ecologico', 'legal'
    dsc_descricao TEXT NOT NULL,
    num_impacto SMALLINT, -- 1-5
    dsc_tendencia VARCHAR(20), -- 'melhora', 'piora', 'estavel'
    dsc_acao_mitigacao TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    FOREIGN KEY (cod_pei) REFERENCES tab_pei(cod_pei),
    FOREIGN KEY (cod_organizacao) REFERENCES tab_organizacoes(cod_organizacao)
);
\`\`\`

#### Canvas de Modelo de Negócio

\`\`\`sql
CREATE TABLE tab_canvas_modelo_negocio (
    cod_canvas UUID PRIMARY KEY,
    cod_pei UUID NOT NULL,
    cod_organizacao UUID NOT NULL,
    num_versao SMALLINT DEFAULT 1,
    txt_parceiros_chave TEXT,
    txt_atividades_chave TEXT,
    txt_proposicao_valor TEXT,
    txt_relacionamento_cliente TEXT,
    txt_segmentos_clientes TEXT,
    txt_recursos_chave TEXT,
    txt_canais TEXT,
    txt_estrutura_custos TEXT,
    txt_fluxos_receita TEXT,
    dte_criacao DATE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    FOREIGN KEY (cod_pei) REFERENCES tab_pei(cod_pei),
    FOREIGN KEY (cod_organizacao) REFERENCES tab_organizacoes(cod_organizacao)
);
\`\`\`

#### 5 Forças de Porter

\`\`\`sql
CREATE TABLE tab_forças_porter (
    cod_forca_porter UUID PRIMARY KEY,
    cod_pei UUID NOT NULL,
    cod_organizacao UUID NOT NULL,
    dsc_ameaca_novos_concorrentes TEXT,
    num_ameaca_novos_concorrentes SMALLINT, -- 1-5
    dsc_poder_fornecedores TEXT,
    num_poder_fornecedores SMALLINT,
    dsc_rivalidade_concorrentes TEXT,
    num_rivalidade_concorrentes SMALLINT,
    dsc_poder_clientes TEXT,
    num_poder_clientes SMALLINT,
    dsc_produtos_substitutos TEXT,
    num_produtos_substitutos SMALLINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    FOREIGN KEY (cod_pei) REFERENCES tab_pei(cod_pei),
    FOREIGN KEY (cod_organizacao) REFERENCES tab_organizacoes(cod_organizacao)
);
\`\`\`

#### Matriz BCG (Boston Consulting Group)

\`\`\`sql
CREATE TABLE tab_matriz_bcg (
    cod_bcg UUID PRIMARY KEY,
    cod_pei UUID NOT NULL,
    cod_organizacao UUID NOT NULL,
    nom_produto_unidade VARCHAR(255) NOT NULL,
    num_taxa_crescimento_mercado DECIMAL(5, 2), -- percentual
    num_market_share DECIMAL(5, 2), -- percentual
    vlr_receita DECIMAL(15, 2),
    dsc_quadrante VARCHAR(50), -- 'estrela', 'vaca_ouro', 'interrogacao', 'cao'
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    FOREIGN KEY (cod_pei) REFERENCES tab_pei(cod_pei),
    FOREIGN KEY (cod_organizacao) REFERENCES tab_organizacoes(cod_organizacao)
);
\`\`\`

#### Comentários e Discussões

\`\`\`sql
CREATE TABLE tab_comentarios (
    cod_comentario UUID PRIMARY KEY,
    table_name VARCHAR(255) NOT NULL,
    table_id UUID NOT NULL,
    user_id UUID NOT NULL,
    txt_conteudo TEXT NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
\`\`\`

---

## 7. MODELOS ELOQUENT E RELACIONAMENTOS

### 7.1 Models do Schema PUBLIC

#### User.php

\`\`\`php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['name', 'email', 'password', 'ativo', 'adm', 'trocarsenha'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'ativo' => 'boolean',
        'adm' => 'boolean',
        'trocarsenha' => 'boolean',
    ];

    // Relacionamentos
    public function organizacoes(): BelongsToMany
    {
        return $this->belongsToMany(
            Organization::class,
            'rel_users_tab_organizacoes',
            'user_id',
            'cod_organizacao',
            'id',
            'cod_organizacao'
        );
    }

    public function perfisAcesso(): BelongsToMany
    {
        return $this->belongsToMany(
            PerfilAcesso::class,
            'rel_users_tab_organizacoes_tab_perfil_acesso',
            'user_id',
            'cod_perfil',
            'id',
            'cod_perfil'
        )->withPivot('cod_organizacao', 'cod_plano_de_acao');
    }

    public function acoes(): HasMany
    {
        return $this->hasMany(Acao::class, 'user_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(Audit::class, 'user_id');
    }

    // Métodos auxiliares
    public function isSuperAdmin(): bool
    {
        return $this->adm;
    }

    public function temPermissaoOrganizacao(Organization $org): bool
    {
        return $this->organizacoes()->where('cod_organizacao', $org->cod_organizacao)->exists();
    }

    public function perfisNaOrganizacao(Organization $org): Collection
    {
        return $this->perfisAcesso()
            ->wherePivot('cod_organizacao', $org->cod_organizacao)
            ->get();
    }
}
\`\`\`

#### Organization.php

\`\`\`php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'tab_organizacoes';
    protected $primaryKey = 'cod_organizacao';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['sgl_organizacao', 'nom_organizacao', 'rel_cod_organizacao'];

    // Relacionamentos
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'rel_users_tab_organizacoes',
            'cod_organizacao',
            'user_id',
            'cod_organizacao',
            'id'
        );
    }

    public function pai(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'rel_cod_organizacao', 'cod_organizacao');
    }

    public function filhas(): HasMany
    {
        return $this->hasMany(Organization::class, 'rel_cod_organizacao', 'cod_organizacao');
    }

    public function planosAcao(): HasMany
    {
        return $this->hasMany(PlanoDeAcao::class, 'cod_organizacao');
    }

    public function peis(): HasMany
    {
        return $this->hasMany(PEI::class, 'cod_organizacao');
    }

    public function identidadeEstrategica(): HasMany
    {
        return $this->hasMany(MissaoVisaoValores::class, 'cod_organizacao');
    }

    // Métodos auxiliares
    public function obterHierarquia(): Collection
    {
        return collect([$this])->merge($this->filhas()->with('filhas')->get()->flatMap(fn($f) => $f->obterHierarquia()));
    }

    public function obterTodosOsUsuarios(): Collection
    {
        return $this->usuarios()->union(
            $this->filhas()->get()->flatMap(fn($f) => $f->obterTodosOsUsuarios())
        )->unique();
    }
}
\`\`\`

#### PerfilAcesso.php

\`\`\`php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PerfilAcesso extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'tab_perfil_acesso';
    protected $primaryKey = 'cod_perfil';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['dsc_perfil', 'dsc_permissao'];

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'rel_users_tab_organizacoes_tab_perfil_acesso',
            'cod_perfil',
            'user_id',
            'cod_perfil',
            'id'
        )->withPivot('cod_organizacao', 'cod_plano_de_acao');
    }

    // Perfis pré-definidos
    const SUPER_ADMIN = 'c00b9ebc-7014-4d37-97dc-7875e55fff2a';
    const ADMIN_UNIDADE = 'c00b9ebc-7014-4d37-97dc-7875e55fff3b';
    const GESTOR_RESPONSAVEL = 'c00b9ebc-7014-4d37-97dc-7875e55fff4c';
    const GESTOR_SUBSTITUTO = 'c00b9ebc-7014-4d37-97dc-7875e55fff5d';
}
\`\`\`

### 7.2 Models do Schema PEI

#### php

\`\`\`php
<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PEI extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'tab_pei';
    protected $primaryKey = 'cod_pei';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['dsc_pei', 'num_ano_inicio_pei', 'num_ano_fim_pei', 'cod_organizacao'];

    public function perspectivas(): HasMany
    {
        return $this->hasMany(Perspectiva::class, 'cod_pei');
    }

    public function identidadeEstrategica(): HasMany
    {
        return $this->hasMany(MissaoVisaoValores::class, 'cod_pei');
    }

    public function valores(): HasMany
    {
        return $this->hasMany(Valor::class, 'cod_pei');
    }

    public function cadeiaValor(): HasMany
    {
        return $this->hasMany(AtividadeCadeiaValor::class, 'cod_pei');
    }
}
\`\`\`

#### MissaoVisaoValores.php

\`\`\`php
<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissaoVisaoValores extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'tab_missao_visao_valores';
    protected $primaryKey = 'cod_missao_visao_valores';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['dsc_missao', 'dsc_visao', 'cod_pei', 'cod_organizacao'];

    public function pei(): BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei');
    }

    public function organizacao(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Organization::class, 'cod_organizacao');
    }

    public function futuroAlmejado(): HasMany
    {
        return $this->hasMany(FuturoAlmejadoObjetivoEstrategico::class);
    }
}
\`\`\`

#### Perspectiva.php

\`\`\`php
<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Perspectiva extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'tab_perspectiva';
    protected $primaryKey = 'cod_perspectiva';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['dsc_perspectiva', 'num_nivel_hierarquico_apresentacao', 'cod_pei'];

    protected $casts = [
        'num_nivel_hierarquico_apresentacao' => 'integer',
    ];

    // Perspectivas padrão BSC
    const FINANCEIRA = 'Financeira';
    const CLIENTES = 'Clientes';
    const PROCESSOS = 'Processos Internos';
    const APRENDIZADO = 'Aprendizado e Crescimento';

    public function pei(): BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei');
    }

    public function objetivos(): HasMany
    {
        return $this->hasMany(ObjetivoEstrategico::class, 'cod_perspectiva');
    }

    public function atividades(): HasMany
    {
        return $this->hasMany(AtividadeCadeiaValor::class, 'cod_perspectiva');
    }
}
\`\`\`

#### ObjetivoEstrategico.php

\`\`\`php
<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObjetivoEstrategico extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'tab_objetivo_estrategico';
    protected $primaryKey = 'cod_objetivo_estrategico';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nom_objetivo_estrategico',
        'dsc_objetivo_estrategico',
        'num_nivel_hierarquico_apresentacao',
        'cod_perspectiva',
    ];

    protected $casts = [
        'num_nivel_hierarquico_apresentacao' => 'integer',
    ];

    public function perspectiva(): BelongsTo
    {
        return $this->belongsTo(Perspectiva::class, 'cod_perspectiva');
    }

    public function planosAcao(): HasMany
    {
        return $this->hasMany(PlanoDeAcao::class, 'cod_objetivo_estrategico');
    }

    public function indicadores(): HasMany
    {
        return $this->hasMany(Indicador::class, 'cod_objetivo_estrategico');
    }

    public function futuroAlmejado(): HasMany
    {
        return $this->hasMany(FuturoAlmejadoObjetivoEstrategico::class, 'cod_objetivo_estrategico');
    }

    // Método auxiliar - calcular % atingimento
    public function calcularAtingimento(): float
    {
        $indicadores = $this->indicadores()->get();
        if ($indicadores->isEmpty()) {
            return 0;
        }
        return $indicadores->avg('percentualAtingimento');
    }
}
\`\`\`

#### PlanoDeAcao.php

\`\`\`php
<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanoDeAcao extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'tab_plano_de_acao';
    protected $primaryKey = 'cod_plano_de_acao';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'cod_objetivo_estrategico',
        'cod_tipo_execucao',
        'cod_organizacao',
        'num_nivel_hierarquico_apresentacao',
        'dsc_plano_de_acao',
        'dte_inicio',
        'dte_fim',
        'vlr_orcamento_previsto',
        'bln_status',
    ];

    protected $casts = [
        'dte_inicio' => 'date',
        'dte_fim' => 'date',
        'vlr_orcamento_previsto' => 'decimal:2',
        'num_nivel_hierarquico_apresentacao' => 'integer',
    ];

    // Status constantes
    const PLANEJADO = 'Planejado';
    const EXECUCAO = 'Em Execução';
    const CONCLUIDO = 'Concluído';
    const CANCELADO = 'Cancelado';

    public function objetivo(): BelongsTo
    {
        return $this->belongsTo(ObjetivoEstrategico::class, 'cod_objetivo_estrategico');
    }

    public function tipoExecucao(): BelongsTo
    {
        return $this->belongsTo(TipoExecucao::class, 'cod_tipo_execucao');
    }

    public function organizacao(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Organization::class, 'cod_organizacao');
    }

    public function indicadores(): HasMany
    {
        return $this->hasMany(Indicador::class, 'cod_plano_de_acao');
    }

    public function entregas(): HasMany
    {
        return $this->hasMany(Entrega::class, 'cod_plano_de_acao');
    }

    // Método auxiliar - calcular % progresso
    public function calcularProgresso(): float
    {
        $entregas = $this->entregas()->get();
        if ($entregas->isEmpty()) {
            return 0;
        }
        // Calcular baseado em status das entregas (simplificado)
        $concluidas = $entregas->where('bln_status', 'Concluída')->count();
        return ($concluidas / $entregas->count()) * 100;
    }
}
\`\`\`

#### Indicador.php

\`\`\`php
<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Indicador extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'tab_indicador';
    protected $primaryKey = 'cod_indicador';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'cod_plano_de_acao',
        'cod_objetivo_estrategico',
        'dsc_tipo',
        'nom_indicador',
        'dsc_indicador',
        'txt_observacao',
        'dsc_meta',
        'dsc_atributos',
        'dsc_referencial_comparativo',
        'dsc_unidade_medida',
        'num_peso',
        'bln_acumulado',
        'dsc_formula',
        'dsc_fonte',
        'dsc_periodo_medicao',
    ];

    protected $casts = [
        'num_peso' => 'integer',
    ];

    public function planoAcao(): BelongsTo
    {
        return $this->belongsTo(PlanoDeAcao::class, 'cod_plano_de_acao');
    }

    public function objetivo(): BelongsTo
    {
        return $this->belongsTo(ObjetivoEstrategico::class, 'cod_objetivo_estrategico');
    }

    public function evolucoes(): HasMany
    {
        return $this->hasMany(EvolucaoIndicador::class, 'cod_indicador');
    }

    public function linhasBase(): HasMany
    {
        return $this->hasMany(LinhaBaseIndicador::class, 'cod_indicador');
    }

    public function metasPorAno(): HasMany
    {
        return $this->hasMany(MetaPorAno::class, 'cod_indicador');
    }

    // Método auxiliar - obter último valor
    public function obterUltimoValor(): ?EvolucaoIndicador
    {
        return $this->evolucoes()
            ->orderByDesc('num_ano')
            ->orderByDesc('num_mes')
            ->first();
    }

    // Método auxiliar - calcular % atingimento
    public function calcularAtingimento(): float
    {
        $ultimoValor = $this->obterUltimoValor();
        if (!$ultimoValor || !$ultimoValor->vlr_realizado) {
            return 0;
        }

        $meta = $this->metasPorAno()
            ->where('num_ano', $ultimoValor->num_ano)
            ->first()?->meta;

        if (!$meta || $meta == 0) {
            return 0;
        }

        return ($ultimoValor->vlr_realizado / $meta) * 100;
    }
}
\`\`\`

#### EvolucaoIndicador.php

\`\`\`php
<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvolucaoIndicador extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'tab_evolucao_indicador';
    protected $primaryKey = 'cod_evolucao_indicador';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'cod_indicador',
        'num_ano',
        'num_mes',
        'vlr_previsto',
        'vlr_realizado',
        'txt_avaliacao',
        'bln_atualizado',
    ];

    protected $casts = [
        'num_ano' => 'integer',
        'num_mes' => 'integer',
        'vlr_previsto' => 'decimal:2',
        'vlr_realizado' => 'decimal:2',
    ];

    public function indicador(): BelongsTo
    {
        return $this->belongsTo(Indicador::class, 'cod_indicador');
    }

    public function arquivos(): HasMany
    {
        return $this->hasMany(Arquivo::class, 'cod_evolucao_indicador');
    }

    public function obterPeriodo(): string
    {
        $meses = [
            1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr', 5 => 'Mai', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ago', 9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez',
        ];
        return "{$meses[$this->num_mes]}/{$this->num_ano}";
    }
}
\`\`\`

#### Entrega.php

\`\`\`php
<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entrega extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'tab_entregas';
    protected $primaryKey = 'cod_entrega';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'cod_plano_de_acao',
        'dsc_entrega',
        'bln_status',
        'dsc_periodo_medicao',
        'num_nivel_hierarquico_apresentacao',
    ];

    const PLANEJADA = 'Planejada';
    const ENTREGUE = 'Entregue';
    const ATRASADA = 'Atrasada';
    const CANCELADA = 'Cancelada';

    public function planoAcao(): BelongsTo
    {
        return $this->belongsTo(PlanoDeAcao::class, 'cod_plano_de_acao');
    }
}
\`\`\`

### 7.3 Models para Novas Funcionalidades

#### AnaliseSwot.php

\`\`\`php
<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnaliseSwot extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'tab_analise_swot';
    protected $primaryKey = 'cod_analise_swot';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'cod_pei',
        'cod_organizacao',
        'dsc_tipo',
        'dsc_categoria',
        'dsc_item',
        'num_impacto',
        'num_probabilidade',
        'cod_objetivo_estrategico',
        'num_prioridade',
    ];

    const STRENGTH = 'strength';
    const WEAKNESS = 'weakness';
    const OPPORTUNITY = 'opportunity';
    const THREAT = 'threat';

    protected $casts = [
        'num_impacto' => 'integer',
        'num_probabilidade' => 'integer',
        'num_prioridade' => 'integer',
    ];

    public function pei(): BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei');
    }

    public function organizacao(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Organization::class, 'cod_organizacao');
    }

    public function objetivo(): BelongsTo
    {
        return $this->belongsTo(ObjetivoEstrategico::class, 'cod_objetivo_estrategico');
    }

    public function getTipoLabel(): string
    {
        return match ($this->dsc_tipo) {
            self::STRENGTH => 'Força',
            self::WEAKNESS => 'Fraqueza',
            self::OPPORTUNITY => 'Oportunidade',
            self::THREAT => 'Ameaça',
        };
    }
}
\`\`\`

---

## 8. MATRIZ DE RELACIONAMENTOS

### 8.1 Matriz Completa de Entidades

| Entity | Organiz | Users | Objetivo | Plano | KPI | Entrega | SWOT | PESTEL | Canvas | Porter | BCG |
|--------|---------|-------|----------|-------|-----|---------|------|--------|--------|--------|-----|
| **Organization** | 1:N | M:N | - | 1:N | - | - | 1:N | 1:N | 1:N | 1:N | 1:N |
| **Users** | M:N | - | - | - | - | - | - | - | - | - | - |
| **Objetivo** | - | - | - | 1:N | 1:N | - | M:N | - | - | - | - |
| **Plano** | 1:1 | 1:N | N:1 | - | 1:N | 1:N | - | - | - | - | - |
| **KPI** | - | - | 1:1 | 1:1 | - | - | - | - | - | - | - |
| **Entrega** | - | - | - | 1:1 | - | - | - | - | - | - | - |
| **SWOT** | - | - | M:N | - | - | - | - | - | - | - | - |
| **PESTEL** | - | - | - | - | - | - | - | - | - | - | - |
| **Canvas** | 1:1 | - | - | - | - | - | - | - | - | - | - |
| **Porter** | 1:1 | - | - | - | - | - | - | - | - | - | - |
| **BCG** | 1:1| - | - | - | - | - | - | - | - | - | - |

### 8.2 Diagram de Fluxo de Dados

\`\`\`
Organization (Topo)
    ├─ Users (M:N)
    ├─ Identidade (1:1)
    │   ├─ Valores
    │   └─ Futuro Almejado
    ├─ PEI (1:N)
    │   ├─ Perspectivas (1:N) [4 padrão: Financeira, Clientes, Processos, Aprendizado]
    │   │   └─ Objetivos (1:N)
    │   │       ├─ Planos de Ação (1:N)
    │   │       │   ├─ Entregas (1:N)
    │   │       │   └─ Indicadores (1:N)
    │   │       │       └─ Evolução (1:N)
    │   │       │           └─ Arquivos (1:N)
    │   │       ├─ Indicadores (1:N)
    │   │       └─ Vinculações SWOT (M:N)
    │   ├─ Cadeia de Valor (1:N)
    │   │   └─ Processos (1:N)
    │   ├─ SWOT (1:N)
    │   ├─ PESTEL (1:N)
    │   ├─ Canvas (1:N)
    │   ├─ 5 Forças Porter (1:N)
    │   └─ Matriz BCG (1:N)
    └─ Auditoria
        ├─ Acoes
        ├─ Audits
        └─ Comentários

Perfis de Acesso (4 tipos)
    └─ Usuários (M:N com contexto de Org + Plano)
\`\`\`

---

## 9. ESTRUTURA DE PASTAS DO PROJETO

\`\`\`
strategic-planning-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php
│   │   │   ├── OrganizationController.php
│   │   │   ├── IdentidadeEstrategicaController.php
│   │   │   ├── ObjetivoEstrategicoController.php
│   │   │   ├── PlanoDeAcaoController.php
│   │   │   ├── IndicadorController.php
│   │   │   ├── AnalisesController.php (SWOT, PESTEL, Canvas, Porter, BCG)
│   │   │   ├── RelatoriosController.php
│   │   │   └── AdminController.php
│   │   ├── Livewire/
│   │   │   ├── Dashboard/
│   │   │   │   ├── ExecutiveDashboard.php
│   │   │   │   ├── KpiWidget.php
│   │   │   │   ├── ObjectivePerformance.php
│   │   │   │   └── ActionStatus.php
│   │   │   ├── Estrategia/
│   │   │   │   ├── SwotAnalysis.php
│   │   │   │   ├── PestelAnalysis.php
│   │   │   │   ├── CanvasModeloNegocio.php
│   │   │   │   ├── ForçasPorter.php
│   │   │   │   └── MatrizBcg.php
│   │   │   ├── Objetivo/
│   │   │   │   ├── ListaObjetivos.php
│   │   │   │   ├── CriarObjetivo.php
│   │   │   │   └── DetalheObjetivo.php
│   │   │   ├── PlanoAcao/
│   │   │   │   ├── ListaAcoes.php
│   │   │   │   ├── CriarAcao.php
│   │   │   │   └── DetalheAcao.php
│   │   │   ├── KPI/
│   │   │   │   ├── ListaKpis.php
│   │   │   │   ├── LancarValor.php
│   │   │   │   └── GraficoEvoucao.php
│   │   │   └── Admin/
│   │   │       ├── GerenciarUsuarios.php
│   │   │       ├── GerenciarPerfis.php
│   │   │       └── LogAuditoria.php
│   │   └── Requests/ (FormRequest para validação)
│   │       ├── StoreObjetivoRequest.php
│   │       ├── StorePlanoAcaoRequest.php
│   │       ├── LancarIndicadorRequest.php
│   │       └── ...
│   ├── Models/
│   │   ├── User.php
│   │   ├── Organization.php
│   │   ├── PerfilAcesso.php
│   │   ├── Acao.php
│   │   ├── Audit.php
│   │   ├── PEI/
│   │   │   ├── php
│   │   │   ├── Perspectiva.php
│   │   │   ├── ObjetivoEstrategico.php
│   │   │   ├── PlanoDeAcao.php
│   │   │   ├── Indicador.php
│   │   │   ├── EvolucaoIndicador.php
│   │   │   ├── Entrega.php
│   │   │   ├── MissaoVisaoValores.php
│   │   │   ├── Valor.php
│   │   │   ├── LinhaBaseIndicador.php
│   │   │   ├── MetaPorAno.php
│   │   │   ├── GrauSatisfacao.php
│   │   │   ├── Arquivo.php
│   │   │   ├── AtividadeCadeiaValor.php
│   │   │   ├── ProcessoAtividadeCadeiaValor.php
│   │   │   ├── FuturoAlmejadoObjetivoEstrategico.php
│   │   │   ├── AnaliseSwot.php
│   │   │   ├── AnalisePestel.php
│   │   │   ├── CanvasModeloNegocio.php
│   │   │   ├── ForcasPorter.php
│   │   │   └── MatrizBcg.php
│   ├── Services/
│   │   ├── OrganizationService.php
│   │   ├── ObjetivoService.php
│   │   ├── IndicadorService.php
│   │   │   └── CalculadorAtingimento.php
│   │   ├── PlanoAcaoService.php
│   │   ├── AnalisesService.php
│   │   ├── DashboardService.php
│   │   ├── RelatorioService.php
│   │   ├── ExcelService.php
│   │   ├── PdfService.php
│   │   └── AuditoriaService.php
│   ├── Policies/
│   │   ├── OrganizationPolicy.php
│   │   ├── ObjetivoPolicy.php
│   │   ├── PlanoAcaoPolicy.php
│   │   ├── IndicadorPolicy.php
│   │   └── AnalisesPolicy.php
│   ├── Traits/
│   │   ├── HasAuditTrait.php (Auto-auditoria em Models)
│   │   ├── HasOrganizationTrait.php
│   │   └── HasPermissionsTrait.php
│   └── Observers/
│       ├── PlanoAcaoObserver.php (Para triggers automáticos)
│       ├── IndicadorObserver.php
│       └── EntregaObserver.php
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php
│   │   │   └── auth.blade.php
│   │   ├── dashboard/
│   │   │   ├── index.blade.php
│   │   │   └── widgets/
│   │   ├── estrategia/
│   │   │   ├── swot/
│   │   │   ├── pestel/
│   │   │   ├── canvas/
│   │   │   ├── porter/
│   │   │   └── bcg/
│   │   ├── objetivos/
│   │   ├── planosacao/
│   │   ├── kpis/
│   │   ├── relatorios/
│   │   ├── admin/
│   │   │   ├── usuarios.blade.php
│   │   │   ├── organizacoes.blade.php
│   │   │   └── auditoria.blade.php
│   │   └── components/
│   │       ├── sidebar.blade.php
│   │       ├── navbar.blade.php
│   │       ├── charts/
│   │       ├── tables/
│   │       └── modals/
│   ├── css/
│   │   ├── app.css (Tailwind/Bootstrap customizado)
│   │   └── themes/
│   └── js/
│       ├── app.js
│       ├── charts.js
│       └── utils.js
├── database/
│   ├── migrations/
│   │   ├── 2025_01_15_000000_create_swot_table.php
│   │   ├── 2025_01_15_000001_create_pestel_table.php
│   │   ├── 2025_01_15_000002_create_canvas_table.php
│   │   ├── 2025_01_15_000003_create_porter_table.php
│   │   ├── 2025_01_15_000004_create_bcg_table.php
│   │   └── 2025_01_15_000005_create_comentarios_table.php
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── OrganizationSeeder.php
│   │   ├── UserSeeder.php
│   │   ├── PerspectivSeeder.php
│   │   └── GrauSatisfacaoSeeder.php
│   └── factories/
│       ├── OrganizationFactory.php
│       ├── UserFactory.php
│       ├── ObjetivoEstrategicoFactory.php
│       └── ...
├── routes/
│   ├── web.php (Rotas públicas)
│   ├── auth.php (Rotas autenticadas)
│   ├── admin.php (Rotas administrativas)
│   └── api.php (APIs - futuro)
├── tests/
│   ├── Unit/
│   │   ├── Models/
│   │   ├── Services/
│   │   └── ...
│   ├── Feature/
│   │   ├── Livewire/
│   │   ├── Controllers/
│   │   └── ...
│   └── TestCase.php
├── bootstrap/
├── config/
│   ├── app.php
│   ├── database.php
│   ├── auth.php
│   ├── audit.php (Config auditoria customizada)
│   └── php (Config específica do PEI)
├── storage/
├── .env.example
├── .gitignore
├── README.md
├── composer.json
├── artisan
└── package.json
\`\`\`

---

## 10. COMPONENTES E INTERFACE

### 10.1 Principais Páginas

#### 1. Dashboard Executivo (`/dashboard`)
- Widgets de KPIs críticos
- Saúde por perspectiva (gauges)
- Timeline de eventos
- Gráfico de heatmap (Objetivos vs. Perspectivas)
- Filtro por período e organização

#### 2. Análise SWOT (`/estrategia/swot`)
- Matriz SWOT 2x2 com cores
- Cards de itens com impacto
- Modal para criar/editar item
- Filtros por categoria e impacto
- Reordenação por drag-and-drop

#### 3. Análise PESTEL (`/estrategia/pestel`)
- 6 abas (P/E/S/T/E/L)
- Cada aba com lista de fatores
- Indicadores de impacto e tendência
- Timeline de evolução
- Modal para adicionar fator

#### 4. Canvas de Modelo de Negócio (`/estrategia/canvas`)
- Visualização 2x5 dos 9 blocos
- Click em bloco abre editor
- Histórico de versões
- Comparação lado a lado

#### 5. 5 Forças de Porter (`/estrategia/porter`)
- Gráfico radial/spider
- Score visual (cores: vermelho/amarelo/verde)
- Histórico de evolução
- Análise textual

#### 6. Matriz BCG (`/estrategia/bcg`)
- Matriz 2x2 com bolhas
- Hover mostra detalhes
- Sugestões de ações por quadrante
- Timeline de movimento

#### 7. Objetivos Estratégicos (`/objetivos`)
- 4 abas para perspectivas BSC
- Cards de objetivos com KPIs
- Filtros por responsável e status
- Link para detalhes

#### 8. Planos de Ação (`/planosacao`)
- 3 abas: Ações, Iniciativas, Projetos
- Status visual (cores)
- Filtros e buscas
- Botão para criar novo

#### 9. KPIs (`/kpis`)
- Lista de indicadores
- Gráficos de evolução
- Lançamento de valores
- Análise de tendências

#### 10. Relatórios (`/relatorios`)
- Seletor de período
- Seletor de módulos (SWOT, Objetivos, KPIs, etc.)
- Preview antes de exportar
- Opções: PDF, XLSX, HTML

#### 11. Admin - Auditoria (`/admin/auditoria`)
- Tabela de logs com filtros
- Visualização antes/depois
- Exportação em CSV
- Busca por usuário/tabela

#### 12. Admin - Usuários (`/admin/usuarios`)
- CRUD de usuários
- Atribuição de perfis
- Histórico de acesso
- Reset de senha

### 10.2 Componentes Livewire

Exemplo de estrutura de um componente:

\`\`\`php
namespace App\Http\Livewire\Estrategia;

use Livewire\Component;
use App\Models\PEI\AnaliseSwot;
use App\Models\Organization;

class SwotAnalysis extends Component
{
    public Organization $organization;
    public $filterCategory = null;
    public $filterImpact = null;
    public $sortBy = 'impact';
    public $showModal = false;

    protected $listeners = ['itemCreated' => 'refreshItems'];

    public function render()
    {
        $swotItems = AnaliseSwot::query()
            ->where('cod_organizacao', $this->organization->cod_organizacao)
            ->when($this->filterCategory, fn($q) => $q->where('dsc_categoria', $this->filterCategory))
            ->when($this->filterImpact, fn($q) => $q->where('num_impacto', '>=', $this->filterImpact))
            ->orderBy($this->sortBy === 'priority' ? 'num_prioridade' : 'num_impacto', 'desc')
            ->get()
            ->groupBy('dsc_tipo');

        return view('livewire.estrategia.swot-analysis', [
            'swotItems' => $swotItems,
            'categories' => AnaliseSwot::distinct()->pluck('dsc_categoria'),
        ]);
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['formData']);
    }

    public function refreshItems()
    {
        // Recarregar lista
    }
}
\`\`\`

---

## 11. FLUXOS DE NEGÓCIO

### 11.1 Fluxo: Criar Objetivo

\`\`\`
1. Usuário clica em "Novo Objetivo"
   └─> Validação: Usuário tem permissão? (Policy)
2. Abre form com:
   ├─ Seletor de Perspectiva (obrigatório)
   ├─ Nome (até 200 chars)
   ├─ Descrição (até 1000 chars)
   ├─ Período (Data Início / Data Fim)
   ├─ Responsável
   ├─ Status (padrão: Planejado)
   └─ Período de Vigência
3. Validação de campos
4. Salva no BD (creates cod_objetivo_estrategico)
   └─> Auditoria automática
5. Redireciona para detalhe do objetivo
6. Evento disparado: `objectiveCreated`
   └─> Atualiza dashboard
\`\`\`

### 11.2 Fluxo: Lançar Valor de KPI

\`\`\`
1. Usuário acessa detalhe do Indicador
2. Clica em "Lançar Novo Valor"
3. Modal abre com:
   ├─ Indicador (pré-selecionado)
   ├─ Período (Mês/Trimestre)
   ├─ Valor Realizado (input numérico)
   ├─ Observações (textarea)
   └─ Arquivo (opcional)
4. Sistema calcula:
   ├─ Diferença vs. meta
   ├─ % Atingimento
   └─ Tendência
5. Validação de tipo de dado
6. Salva EvolucaoIndicador no BD
   └─> Auditoria automática
7. Se % Atingimento < 60%:
   └─> Dispara notificação de desvio
8. Atualiza gráficos em tempo real (Livewire)
\`\`\`

### 11.3 Fluxo: Gerar Relatório

\`\`\`
1. Usuário acessa "/relatorios"
2. Seleciona:
   ├─ Período (Data Início / Data Fim)
   ├─ Módulos (checkboxes):
   │  ├─ Identidade Estratégica
   │  ├─ SWOT
   │  ├─ PESTEL
   │  ├─ Objetivos
   │  ├─ KPIs
   │  └─ Ações
   ├─ Formato: PDF, XLSX, HTML
   └─ Organização (se multi-tenant)
3. Clica em "Gerar Preview"
4. Sistema coleta dados:
   ├─ Queries dos módulos selecionados
   ├─ Gráficos renderizados
   └─ Cálculos de comparativos
5. Exibe preview em modal
6. Usuário clica "Exportar"
7. Service exporta:
   ├─ Se PDF: Usa mPDF/DomPDF
   ├─ Se XLSX: Usa PhpSpreadsheet
   └─ Se HTML: Retorna file
8. Browser faz download
\`\`\`

### 11.4 Fluxo: Aprovar/Rejeitar Acesso

\`\`\`
1. Super Admin acessa "/admin/usuarios"
2. Lista usuários
3. Clica em usuário para editar
4. Atribui:
   ├─ Organização
   ├─ Perfil (Super Admin / Admin Unidade / Gestor / Substituto)
   └─ Plano de Ação (se Gestor/Substituto)
5. Clica em "Salvar"
6. Sistema atualiza rel_users_tab_organizacoes_tab_perfil_acesso
7. Email de confirmação enviado ao usuário
8. Log de auditoria registrado
\`\`\`

---

## 12. CRONOGRAMA DETALHADO

### 12.1 Estrutura dos Sprints

**Duração:** 6 meses (280 horas)  
**Carga:** 10h/semana (noites e fins de semana)  
**Sprints:** 6 ciclos de 4 semanas (40h cada)

---

### 12.2 SPRINT 1: Fundações (Semanas 1-4, 40h)

**Objetivo:** Infraestrutura, autenticação, base da aplicação

#### Semana 1 (10h)
- Setup Laravel 12 com Livewire 3
- Configuração Bootstrap 5 + Alpine.js
- Setup PostgreSQL local/remoto
- Seed de dados iniciais (Organizations, Users, Perfis)
- Primeira execução e verificação

#### Semana 2 (10h)
- Implementar autenticação (Laravel Breeze adaptado)
- Tela de login com validações
- Tela de change password (força de senha)
- Middleware de autenticação
- Testes de segurança básicos

#### Semana 3 (10h)
- Layout base (navbar, sidebar, footer)
- Componentes de layout reutilizáveis
- Navegação entre módulos
- Tema Bootstrap 5 personalizado
- Responsividade mobile/tablet

#### Semana 4 (10h)
- CRUD de Organizations
- Relacionamentos Organization-Users-Perfis
- Dashboard inicial (placeholder)
- Deploy em staging
- Documentação de setup

**Entregável:** MVP funcional com login e gestão básica

---

### 12.3 SPRINT 2: Identidade e Análises Básicas (Semanas 5-8, 40h)

**Objetivo:** Missão/Visão/Valores e SWOT

#### Semana 1 (10h)
- Módulo Identidade Estratégica
- Criar/editar Missão, Visão, Valores
- Versionamento com histórico
- Interface com cards

#### Semana 2 (10h)
- SWOT - Criar tabelas de suporte
- Model AnaliseSwot com relacionamentos
- Livrewire component SwotAnalysis
- Matriz visual 2x2 com cores

#### Semana 3 (10h)
- SWOT - Criar itens (modal)
- Editar/deletar itens
- Filtros (categoria, impacto)
- Drag-and-drop para priorização
- Tests

#### Semana 4 (10h)
- Histórico de versões SWOT
- Export SWOT simples (PDF)
- Dashboard com widgets básicos
- Deployment e ajustes

**Entregável:** Sistema completo de Identidade e SWOT

---

### 12.4 SPRINT 3: Análises Avançadas (Semanas 9-12, 40h)

**Objetivo:** PESTEL, Canvas, Porter, BCG

#### Semana 1 (10h)
- PESTEL - Tabelas e Models
- Interface com 6 abas
- Registrar fatores
- Indicadores de tendência

#### Semana 2 (10h)
- Canvas - Tabela de suporte
- Visualização 2x5 interativa
- Editar blocos (click para abrir editor)
- Versionamento de canvas

#### Semana 3 (10h)
- 5 Forças de Porter
- Gráfico radial/spider
- Scoring de forças
- Histórico de evolução

#### Semana 4 (10h)
- Matriz BCG
- Gráfico 2x2 com bolhas
- Posicionar produtos
- Tests e ajustes

**Entregável:** Todas as análises estratégicas funcionais

---

### 12.5 SPRINT 4: Objetivos e KPIs (Semanas 13-16, 40h)

**Objetivo:** BSC, Objetivos, Indicadores

#### Semana 1 (10h)
- Tabelas de Perspectivas (4 padrão)
- Modelo Perspectiva com relacionamentos
- Interface listar perspectivas
- Tests

#### Semana 2 (10h)
- Objetivos Estratégicos CRUD
- Vincular a Perspectivas
- Nível hierárquico
- Status (Planejado/Execução/Concluído/Cancelado)

#### Semana 3 (10h)
- Indicadores (KPIs) CRUD
- Linha base, metas por ano
- Evolução de indicadores
- Lançamento de valores

#### Semana 4 (10h)
- Cálculos de atingimento
- Gráficos de evolução (Chart.js)
- Análise de tendências
- Dashboard com KPIs

**Entregável:** BSC completo operacional

---

### 12.6 SPRINT 5: Planos de Ação e Execução (Semanas 17-20, 40h)

**Objetivo:** Planos de ação, entregas, acompanhamento

#### Semana 1 (10h)
- Planos de Ação CRUD
- 3 tipos (Ação, Iniciativa, Projeto)
- Relacionar com Objetivos
- Responsáveis e substitutos

#### Semana 2 (10h)
- Entregas (Deliverables)
- Cálculo de progresso (%)
- Status de entrega
- Atualizar progresso (modal)

#### Semana 3 (10h)
- Dashboard de Acompanhamento
- Timeline de ações
- Filtros por status e responsável
- Alertas de atraso

#### Semana 4 (10h)
- Cadeia de Valor
- Entrada → Transformação → Saída
- Visualização por perspectiva
- Testes e ajustes

**Entregável:** Execução e monitoramento completos

---

### 12.7 SPRINT 6: Dashboard, Relatórios, Finalização (Semanas 21-24, 40h)

**Objetivo:** Dashboard executivo, relatórios, segurança, deploy

#### Semana 1 (10h)
- Dashboard Executivo completo
- Widgets: KPIs críticos, saúde por perspectiva, timeline
- Gráfico heatmap (Objetivos vs. Perspectivas)
- Filtros por período/organização

#### Semana 2 (10h)
- Módulo de Relatórios
- Export em PDF/XLSX/HTML
- Comparativo entre períodos
- Agendamento de relatórios (futuro)

#### Semana 3 (10h)
- Auditoria e Segurança
- Log de todas as alterações
- Admin - Gestão de usuários
- Admin - Visualizar logs

#### Semana 4 (10h)
- Testes finais completos
- Performance optimization
- Documentação de usuário
- Deployment em produção

**Entregável:** Sistema completo em produção

---

### 12.8 Checklist por Sprint

#### Sprint 1 Checklist
- [ ] Laravel 12 + Livewire 3 + Bootstrap 5 setup
- [ ] Banco PostgreSQL conectado
- [ ] Migração de dados legados (tabelas públicas)
- [ ] Autenticação funcional
- [ ] Layout responsivo
- [ ] CRUD Organizations
- [ ] Testes básicos
- [ ] Deploy staging

#### Sprint 2 Checklist
- [ ] Identidade Estratégica CRUD
- [ ] SWOT criação/edição/deleção
- [ ] SWOT matriz visual
- [ ] Histórico de versões
- [ ] Widgets dashboard básicos
- [ ] Tests
- [ ] Deploy staging

#### Sprint 3 Checklist
- [ ] PESTEL completo
- [ ] Canvas modelo negócio
- [ ] 5 Forças Porter
- [ ] Matriz BCG
- [ ] Gráficos interativos
- [ ] Tests
- [ ] Deploy staging

#### Sprint 4 Checklist
- [ ] 4 Perspectivas BSC
- [ ] Objetivos CRUD
- [ ] Indicadores CRUD
- [ ] Cálculo de atingimento
- [ ] Lançamento de valores
- [ ] Gráficos de evolução
- [ ] Tests
- [ ] Deploy staging

#### Sprint 5 Checklist
- [ ] Planos de Ação CRUD
- [ ] 3 tipos de execução
- [ ] Entregas e progresso
- [ ] Cadeia de Valor
- [ ] Dashboard de acompanhamento
- [ ] Alertas de desvio
- [ ] Tests
- [ ] Deploy staging

#### Sprint 6 Checklist
- [ ] Dashboard Executivo completo
- [ ] Relatórios PDF/XLSX
- [ ] Auditoria completa
- [ ] Gestão de usuários/perfis
- [ ] Performance optimization
- [ ] Testes finais
- [ ] Documentação
- [ ] Deploy produção

---

## 13. RISCOS E MITIGAÇÕES

### 13.1 Matriz de Riscos

| # | Risco | Prob. | Impacto | Severidade | Mitigação |
|----|-------|-------|--------|-----------|-----------|
| R1 | Falta de tempo disponível | Alta | Alto | CRÍTICA | Buffer 20%, priorizar MVPs, reduzir escopo se necessário |
| R2 | Complexidade subestimada | Média | Alto | CRÍTICA | Pesquisa prévia, POC rápido antes de cada módulo |
| R3 | Problemas performance BD | Média | Médio | ALTA | Índices preventivos, queries otimizadas, testes de carga |
| R4 | Dados legados inconsistentes | Média | Médio | ALTA | Limpeza de dados, validações rigorosas |
| R5 | Dificuldade Livewire 3 | Baixa | Médio | MÉDIA | Estudo prévio, comunidade ativa, fallback para AJAX |
| R6 | Requisitos mudam durante projeto | Média | Médio | ALTA | Documentação clara, validação com cliente a cada sprint |
| R7 | Segurança não adequada | Baixa | CRÍTICA | CRÍTICA | Testes de segurança, penetration test antes de deploy |
| R8 | Deploy com problemas | Baixa | Médio | MÉDIA | Testes em staging, playbook de rollback |

### 13.2 Ações Preventivas

**Performance:**
\`\`\`sql
-- Índices críticos
CREATE INDEX idx_plano_acao_objetivo ON tab_plano_de_acao(cod_objetivo_estrategico);
CREATE INDEX idx_plano_acao_organizacao ON tab_plano_de_acao(cod_organizacao);
CREATE INDEX idx_indicador_plano ON tab_indicador(cod_plano_de_acao);
CREATE INDEX idx_indicador_objetivo ON tab_indicador(cod_objetivo_estrategico);
CREATE INDEX idx_evolucao_ano_mes ON tab_evolucao_indicador(num_ano, num_mes);
CREATE INDEX idx_swot_org ON tab_analise_swot(cod_organizacao);
CREATE INDEX idx_user_org ON rel_users_tab_organizacoes(user_id, cod_organizacao);
\`\`\`

**Segurança:**
- Rate limiting em login (5 tentativas/5 min)
- CSRF tokens em todos os forms
- SQL injection prevention (Eloquent ORM)
- XSS prevention (Blade escaping)
- Row Level Security (RLS) por organização
- Audit trail completo

---

## 14. MÉTRICAS DE SUCESSO

### 14.1 KPIs do Projeto

| Métrica | Meta | Frequência | Responsável |
|---------|------|-----------|-------------|
| Cobertura Testes | ≥60% | Sprint | Desenvolvedor |
| Bugs em Produção | 0 críticos | Contínuo | Desenvolvedor |
| Uptime Sistema | ≥99% | Mensal | Ops |
| Tempo Carregamento Dashboard | <2s | Semanal | Desenvolvedor |
| Satisfação Usuários | ≥4/5 | Final projeto | Stakeholders |
| Funcionalidades Entregues | 100% | Final projeto | PM |
| Documentação Completude | 100% | Final projeto | Desenvolvedor |

### 14.2 Critérios de Sucesso

✅ **Técnico:**
- Sistema funcional em produção
- Dados integrados com banco legado
- ≥60% cobertura de testes
- 0 bugs críticos
- ≥99% uptime

✅ **Funcional:**
- Todas as features do escopo implementadas
- Todos os módulos (SWOT, PESTEL, Canvas, Porter, BCG) operacionais
- Dashboard executivo com visualizações completas
- Relatórios exportáveis (PDF/XLSX)

✅ **Usuário:**
- Interface intuitiva e responsiva
- Tempo de resposta <2s
- Satisfação ≥4/5
- Taxa de adoção ≥80%

✅ **Negócio:**
- ROI positivo em 6 meses
- Redução de 50% no tempo de elaboração de planejamento
- Baseado para futuras expansões (APIs, Mobile, IA)

---

## 15. DEFINIÇÕES E GLOSSÁRIO

| Termo | Definição |
|-------|-----------|
| **BSC** | Balanced Scorecard - Framework de medição de estratégia com 4 perspectivas |
| **KPI** | Key Performance Indicator - Indicador-chave de desempenho |
| **SWOT** | Strength, Weakness, Opportunity, Threat - Análise estratégica |
| **PESTEL** | Político, Econômico, Social, Tecnológico, Ecológico, Legal |
| **Canvas** | Business Model Canvas - Visualização do modelo de negócio |
| **Porter** | 5 Forças de Michael Porter - Análise de competição |
| **BCG** | Boston Consulting Group - Matriz de crescimento vs. market share |
| **PEI** | Planejamento Estratégico Integrado (nome do schema) |
| **MVP** | Minimum Viable Product - Versão mínima funcional |
| **RLS** | Row Level Security - Segurança de nível de linha no BD |
| **RTO** | Recovery Time Objective - Tempo máximo de recuperação |
| **RPO** | Recovery Point Objective - Perda máxima aceitável de dados |

---

## CONCLUSÃO

Este documento define completamente o sistema de Planejamento Estratégico que:

✅ Moderniza o sistema legado mantendo compatibilidade 100%
✅ Expande funcionalidades com análises estratégicas avançadas
✅ Implementa em 6 meses com desenvolvimento solo
✅ Utiliza stack moderno (Laravel 12 + Livewire 3 + Bootstrap 5)
✅ Prioriza segurança, performance e usabilidade
✅ Estabelece base para futuras inovações

**Status:** Pronto para iniciar desenvolvimento  
**Próximo Passo:** Setup inicial (Sprint 1, Semana 1)

---

**Versão:** 2.0  
**Data:** Janeiro 2025  
**Aprovação:** [Pendente]
