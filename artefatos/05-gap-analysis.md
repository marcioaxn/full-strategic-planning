# Gap Analysis: Sistema Atual vs. GPPEI

> **Artefato extraído** do Documento Mestre de Evolução do Sistema PEI (`documentacao/documento-mestre-evolucao-sistema-pei.md`, v1.0 · 2026-05-30).

## Módulo 01 — Inaugurar e Integrar

| Funcionalidade GPPEI | Status no sistema | Ação necessária |
|---|---|---|
| Tela de boas-vindas / portal de módulos | **Ausente** | Construir portal/home com mapa de módulos |
| Planejar o Planejamento (cronograma, equipe, validação) | **Ausente** | Novo módulo `inaugurar` |
| Integração com PPA / LOA / ODS | **Ausente** | Novo sub-módulo `integracao-instrumentos` |
| Plano de Comunicação do PEI | **Ausente** | Novo sub-módulo `comunicacao-pei` |
| Calendário de eventos do PEI | **Ausente** | Componente de calendário |

## Módulo 02 — Planejar

| Funcionalidade GPPEI | Status no sistema | Ação necessária |
|---|---|---|
| Cadeia de Valor (diagrama visual interativo) | **Parcial** (tabelas existem, UX incompleta) | Redesenhar interface da Cadeia de Valor |
| Análise Ambiental (SWOT) | Existe (`/pei/swot`) | Melhorar UX e conexão com fluxo |
| Análise Ambiental (PESTEL) | Existe (`/pei/pestel`) | Melhorar UX e conexão com fluxo |
| Análise de Partes Interessadas | **Ausente** | Novo componente |
| Análise de Cenários Prospectivos | **Ausente** | Novo componente |
| Matriz GUT | **Ausente** | Novo componente na Análise Ambiental |
| Referencial Estratégico (Missão/Visão) | Existe (`/pei`) | Melhorar UX |
| Temas Norteadores | Tabela existe, UX a verificar | Verificar e conectar ao fluxo |
| Mapa Estratégico (BSC visual) | Existe (`/pei/mapa`) | Ajustar para seguir identidade GPPEI |
| Metas SMART por indicador | Existe (MetaPorAno) | Adicionar validação SMART na UI |
| OKR por objetivo | **Ausente** | Novo componente opcional |
| Carteira de Projetos (Modelo Lógico) | **Parcial** (Planos de Ação existem) | Adicionar template de Modelo Lógico / Canvas |
| Termo de Abertura de Projeto | **Ausente** | Novo template no módulo de Planos |
| 5W2H por entrega | **Ausente** | Novo componente nas Entregas |
| EAP (Estrutura Analítica do Projeto) | **Ausente** | Novo componente no módulo de Planos |
| Gráfico de Gantt | **Ausente** (timeline existe) | Melhorar timeline para Gantt formal |
| Matriz RACI | **Ausente** | Novo componente nos Planos de Ação |

## Módulo 03 — Monitorar e Avaliar

| Funcionalidade GPPEI | Status no sistema | Ação necessária |
|---|---|---|
| Painel de monitoramento (Dashboard) | Existe (`/dashboard`) | Melhorar com visão de progresso por módulo |
| Lançar Evolução de Indicadores | Existe (rota), **UX invisível** | Corrigir UX — botão/link visível |
| Ficha de Indicadores (modelo estruturado) | **Parcial** | Adicionar campos de ficha GPPEI |
| RAE — Revisão e Avaliação da Estratégia | **Ausente** | Novo módulo `rae` |
| Relatório de RAE | **Ausente** | Novo relatório de ciclo |
| Estratégia de Comunicação | **Ausente** | Novo sub-módulo |
| Sistema de alertas estratégicos | Existe (`strategic_alerts`) | Expandir para notificações do ciclo |

## UX Críticas (independente do GPPEI)

| Problema | Módulo | Solução |
|---|---|---|
| Usuário não encontra onde lançar evolução | Indicadores | Botão "Lançar Evolução" visível na listagem e no detalhe |
| Usuário não encontra entregas do plano | Planos de Ação | Link "Ver Entregas" visível no card/detalhe do plano |
| Módulos isolados, sem fio condutor | Todo o sistema | Portal de módulos com progressão visual (stepper) |
| Perfis de acesso não visíveis para o usuário | Admin / Perfis | Tela de gestão de perfis clara |
| Página inicial é o Mapa Estratégico sem contexto | Home | Criar portal com menu dos módulos |
