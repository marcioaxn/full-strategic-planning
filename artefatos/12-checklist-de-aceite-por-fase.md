# Checklist de Aceite por Fase

> **Artefato extraído** do Documento Mestre de Evolução do Sistema PEI (`documentacao/documento-mestre-evolucao-sistema-pei.md`, v1.0 · 2026-05-30).

## Fase 1 — Aceite mínimo para uso real

- [ ] Usuário consegue lançar evolução de indicador em 3 cliques a partir da listagem
- [ ] Usuário consegue acessar as entregas de um plano em 2 cliques a partir da listagem de planos
- [ ] Página inicial mostra claramente os 3 módulos e o status de cada um
- [ ] Administrador Geral consegue ver e gerenciar perfis de acesso
- [ ] Todos os módulos principais estão acessíveis no menu de navegação

## Fase 2 — Aceite da harmonização visual

- [ ] Cores de cada módulo seguem a paleta do GPPEI
- [ ] Links "Ver no GPPEI" estão visíveis em todas as telas principais
- [ ] Viewer PDF do GPPEI está acessível e funcional
- [ ] Stepper de progresso do ciclo PEI é visível no layout

## Fase 3-5 — Aceite dos novos módulos

- [x] Módulo Inaugurar e Integrar aparece no `PeiGuidanceService`
- [x] Cadeia de Valor tem diagrama visual editável
- [x] RAE pode ser registrada e gera relatório PDF
- [x] Dashboard executivo exibe indicadores consolidados por perspectiva

## Fase 6 — Aceite do Guia de Projetos

- [x] Viewer PDF do Guia de Projetos acessível em `/documentos/projetos`
- [x] Links `<x-projetos-link>` em Planos, Riscos, Indicadores, RACI, Lições Aprendidas
- [x] Módulo Lições Aprendidas operacional em `/licoes-aprendidas`
- [x] Plano de Comunicação com CRUD em `AtribuirResponsaveis`
- [x] RACI visualizado em `detalhar-plano`

## Fase 7 — Aceite Landing Page + Perfis

- [x] Rota `/` exibe landing page pública (não o Mapa Estratégico) — `App\Livewire\LandingPage`
- [x] Usuários autenticados redirecionados de `/` para `/dashboard` — `LandingPage::mount()`
- [x] Tela `/admin/perfis` funcional com tabela de permissões — `App\Livewire\Admin\GestaoPerfis`
- [x] Função de impersonate operacional para `adm=true` — `App\Http\Controllers\ImpersonateController`

## Fase 8 — Aceite Complementos

- [x] RACI pode ser criado/editado via UI (em `AtribuirResponsaveis`)
- [x] Cadeia de Valor exporta PDF
- [x] Alertas de prazos vencidos visíveis no dashboard
- [x] Relatório de Comunicação do PEI em PDF (`relatorios.comunicacao`)

## Fase 9 — Aceite Harmonização Visual

- [x] Componente `<x-module-header>` com paleta de cores por módulo (seção 9.1)
- [x] Headers coloridos aplicados aos módulos (Cadeia de Valor, Inaugurar, RAE, Lições)
- [x] Viewer PDF do GPPEI com menu lateral de seções (`/guia-gppei`)
- [x] Análise de Cenários Prospectivos (aba na Análise Ambiental)
