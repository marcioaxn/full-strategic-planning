# Roadmap Inicial

> **Artefato extraído** do Documento Mestre de Evolução do Sistema PEI (`documentacao/documento-mestre-evolucao-sistema-pei.md`, v1.0 · 2026-05-30).

## Fase 1 — Correções UX Críticas (Sprint 1, ~2 semanas)
**Objetivo:** Tornar o sistema utilizável antes de novas funcionalidades.

| Item | Módulo | Prioridade |
|---|---|---|
| Botão "Lançar Evolução" visível na listagem e detalhe de indicadores | Indicadores | CRÍTICA |
| Link "Ver Entregas" visível no card e detalhe de Planos de Ação | Planos | CRÍTICA |
| Ajustar página inicial para exibir portal de módulos + Mapa Estratégico | Home | ALTA |
| Verificar e corrigir rota de Riscos na navegação principal | Riscos | ALTA |
| Tela de Gestão de Perfis de Acesso | Admin | ALTA |

## Fase 2 — Portal de Módulos e Harmonização Visual (Sprint 2, ~3 semanas)
**Objetivo:** Alinhar a experiência visual ao GPPEI e criar o fio condutor metodológico.

| Item | Módulo | Prioridade |
|---|---|---|
| Portal/Home com os 3 módulos GPPEI e status de progresso | Home | ALTA |
| Stepper de progresso do ciclo PEI no layout | Layout global | ALTA |
| Aplicar paleta de cores por módulo conforme GPPEI | Visual | ALTA |
| Viewer PDF do GPPEI embutido (`/documentos/gppei`) | Referência | ALTA |
| Links "Ver no GPPEI" em cada tela principal | Todas | MÉDIA |
| Cards de módulo com ícone e número no estilo GPPEI | Visual | MÉDIA |

## Fase 3 — Módulo 01: Inaugurar e Integrar (Sprint 3, ~2 semanas)
**Objetivo:** Implementar o módulo de inauguração do ciclo PEI.

| Item | Módulo | Prioridade |
|---|---|---|
| Tela "Planejar o Planejamento" com migração e formulário | Inaugurar | ALTA |
| Tela de Integração com Instrumentos (PPA/LOA/ODS) | Inaugurar | ALTA |
| Calendário de Eventos do PEI | Inaugurar | MÉDIA |
| Checklist do Módulo 01 no `PeiGuidanceService` | Inaugurar | MÉDIA |

## Fase 4 — Módulo 02: Planejar — Extensões (Sprint 4-5, ~4 semanas)
**Objetivo:** Completar e melhorar os módulos de planejamento.

| Item | Módulo | Prioridade |
|---|---|---|
| Redesenhar interface da Cadeia de Valor (diagrama interativo) | Cadeia de Valor | ALTA |
| Adicionar Classificação GUT ao SWOT | Análise Ambiental | ALTA |
| Análise de Partes Interessadas | Análise Ambiental | MÉDIA |
| Validação SMART nos indicadores | Indicadores | ALTA |
| Aba Modelo Lógico no Plano de Ação | Planos | ALTA |
| 5W2H nas Entregas | Entregas | ALTA |
| Matriz RACI nos Planos | Planos | MÉDIA |
| Tela "Minhas Entregas" | Entregas | ALTA |

## Fase 5 — Módulo 03: Monitorar — RAE e Dashboard Executivo (Sprint 6, ~3 semanas)
**Objetivo:** Completar o ciclo de monitoramento.

| Item | Módulo | Prioridade |
|---|---|---|
| Módulo RAE com CRUD e relatório PDF | Monitoramento | ALTA |
| Dashboard executivo redesenhado | Dashboard | ALTA |
| Alertas de prazos vencidos e indicadores críticos | Notificações | MÉDIA |
| Relatório de Comunicação do PEI | Comunicação | BAIXA |

## Fase 6 — Segundo PDF: Guia Prático de Projetos (Sprint 7-8, ~4 semanas)
**Objetivo:** Incorporar o segundo guia (Projetos) ao sistema.

| Item | Módulo | Prioridade | Status |
|---|---|---|---|
| Analisar estrutura do guia-pratico-de-projetos.pdf | Análise | ALTA | ✅ CONCLUÍDO |
| Identificar gap entre módulo de Planos e o Guia de Projetos | Gap | ALTA | ✅ CONCLUÍDO |
| Viewer do PDF de Projetos + links por módulo | Referência | ALTA | ✅ CONCLUÍDO |
| Lições Aprendidas (Domínio 7) | Projetos | ALTA | ✅ CONCLUÍDO |
| Plano de Comunicação do plano (Domínio 5) | Projetos | ALTA | ✅ CONCLUÍDO |
| RACI UI no DetalharPlano (Domínio 3) | Projetos | ALTA | ✅ CONCLUÍDO |

---

## Fase 7 — Landing Page Pública + Gestão de Perfis (Sprint 9, ~2 semanas)
**Objetivo:** Corrigir a página pública inicial e implementar a gestão completa de perfis de acesso — lacunas críticas identificadas na auditoria pós-Fase 6.

**Problema identificado:** A rota `/` exibe o Mapa Estratégico sem autenticação, expondo dados sensíveis e oferecendo UX inadequada para visitantes. Contradiz H00 (adicionado ao documento nesta revisão).

| Item | Módulo | Prioridade |
|---|---|---|
| Landing page pública moderna e humanizada na rota `/` | Home Pública | CRÍTICA |
| Redirecionar usuários autenticados de `/` para `/dashboard` | Routing | CRÍTICA |
| Tela de Gestão de Perfis de Acesso (`/admin/perfis`) | Admin | ALTA |
| Tabela de permissões por perfil × funcionalidade | Admin | ALTA |
| Função de impersonate para Administrador Geral | Admin | ALTA |
| Log de ações em modo impersonate no `tab_audit` | Admin | ALTA |

## Fase 8 — Completar RACI + Export PDF + Alertas (Sprint 10, ~2 semanas)
**Objetivo:** Fechar funcionalidades implementadas parcialmente nas fases anteriores.

| Item | Módulo | Prioridade |
|---|---|---|
| Formulário de criação/edição de entradas RACI na UI | Planos | ALTA |
| Exportação da Cadeia de Valor como PDF (RF13) | Cadeia de Valor | MÉDIA |
| Alertas expandidos: prazos vencidos de entregas | Notificações | MÉDIA |
| Alertas expandidos: indicadores abaixo da meta crítica | Notificações | MÉDIA |
| Relatório de Comunicação do PEI | Relatórios | BAIXA |

## Fase 9 — Harmonização Visual GPPEI Completa (Sprint 11, ~1 semana)
**Objetivo:** Aplicar integralmente a paleta de cores e identidade visual do GPPEI (seção 9.1) a todos os módulos.

| Item | Módulo | Prioridade |
|---|---|---|
| CSS: variáveis de cor por módulo (seção 9.1 deste documento) | Visual | MÉDIA |
| Headers de tela com banner colorido por módulo GPPEI | Visual | MÉDIA |
| Cards de módulo numerados (01/02/03) no estilo GPPEI no dashboard | Visual | MÉDIA |
| Viewer PDF com menu lateral de seções (H19) | Referência | BAIXA |
| Análise de Cenários Prospectivos (item do gap analysis) | Planejamento | BAIXA |
