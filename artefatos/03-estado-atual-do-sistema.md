# Estado Atual do Sistema

> **Artefato extraído** do Documento Mestre de Evolução do Sistema PEI (`documentacao/documento-mestre-evolucao-sistema-pei.md`, v1.0 · 2026-05-30).

## Stack tecnológica verificada

| Camada | Tecnologia |
|---|---|
| Framework | Laravel 12.53.0 |
| PHP | 8.2.12 |
| Frontend stateful | Livewire 3.7.11 |
| Autenticação | Jetstream + Fortify + Sanctum |
| Banco | PostgreSQL multi-schema (6 schemas) |
| Frontend base | Bootstrap 5.3.3 + Alpine.js 3 + Vite 7 |
| Relatórios | DomPDF + Maatwebsite/Excel |
| Auditoria | owen-it/laravel-auditing |

## Rotas e módulos existentes (verificados em runtime)

| Rota | Módulo | Status UX |
|---|---|---|
| `/` | Mapa Estratégico (página inicial) | Funcional, mas sem contexto de boas-vindas |
| `/dashboard` | Dashboard | Existe, conteúdo a verificar |
| `/pei/ciclos` | Ciclos PEI | Funcional |
| `/pei` | Identidade (Missão/Visão) | Funcional |
| `/pei/perspectivas` | Perspectivas BSC | Funcional |
| `/pei/valores` | Valores Institucionais | Funcional |
| `/pei/swot` | Análise SWOT | **Existe mas desconectado do fluxo** |
| `/pei/pestel` | Análise PESTEL | **Existe mas desconectado do fluxo** |
| `/objetivos` | Objetivos Estratégicos | Funcional |
| `/indicadores` | Indicadores/KPIs | **UX crítica: evolução inacessível** |
| `/indicadores/{id}/evolucao` | Lançar Evolução | Rota existe, link invisível |
| `/planos` | Planos de Ação | Funcional |
| `/planos/{id}/entregas` | Entregas/Deliverables | **UX crítica: acesso não obvio** |
| `/riscos` | Gestão de Riscos | A verificar na UI |
| `/relatorios` | Relatórios (PDF/Excel) | Funcional |
| `/graus-satisfacao` | Graus de Satisfação | Funcional |
| `/auditoria` | Auditoria | Funcional |
| `/organizacoes` | Organizações | Funcional |
| `/configuracoes` | Configurações | Funcional |

## Módulos com tabelas no banco mas sem rota clara

| Tabela | Conteúdo | Status |
|---|---|---|
| `strategic_planning.tab_atividade_cadeia_valor` | Atividades da Cadeia de Valor | Tabela existe, UX incompleta |
| `strategic_planning.tab_processos_atividade_cadeia_valor` | Processos da Cadeia de Valor | Tabela existe, UX incompleta |
| `strategic_planning.tab_tema_norteador` | Temas Norteadores | Tabela existe, rota não identificada |
| `strategic_planning.tab_futuro_almejado_objetivo` | Futuro Almejado | Rota `objetivos.futuro` existe |
| `public.tab_analise_ambiental` | Análise Ambiental estruturada (SWOT/PESTEL) | Tabela com 90 registros |
