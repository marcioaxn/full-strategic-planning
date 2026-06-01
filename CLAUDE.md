# Sistema de Planejamento Estratégico Institucional (PEI) — Claude Code Rules

## Identidade
Sistema de gestão estratégica para organizações públicas brasileiras.
Alinhado ao Guia Prático de Planejamento Estratégico Institucional (GPPEI/MGI 2025).
Referência metodológica: documentacao/pdf/Guia_PEI_VF.pdf
Documento mestre do projeto: documentacao/documento-mestre-evolucao-sistema-pei.md

## Stack (verificada em runtime)
- PHP 8.2.12 · Laravel 12.53.0 · Livewire 3.7.11 · Alpine.js 3.15.2
- PostgreSQL multi-schema (6 schemas de domínio) · Bootstrap 5.3.3 · Vite 7
- Auth: Fortify + Jetstream + Sanctum
- Queue: database · Cache: database · Session: database
- Testes: Pest 4.x (`vendor/bin/pest` ou `php artisan test`)
- Lint: Laravel Pint (`vendor/bin/pint`)

## Ambiente de desenvolvimento
- Windows 10 (XAMPP), PostgreSQL local

## Comandos essenciais
- Dev completo: `composer dev` (server + queue + Vite em paralelo)
- Testes: `php artisan test` ou `vendor/bin/pest`
- Testes filtrados: `php artisan test --filter=NomeTeste`
- Lint: `vendor/bin/pint --dirty`
- Cache clear: `php artisan optimize:clear`
- Rotas: `php artisan route:list`
- Validação syntax: `php -l app/Livewire/MeuComponente.php`

## User Model
- Arquivo: `app/Models/User.php`
- Tabela: `public.users`
- PK: `id` (UUID)
- Campos especiais: `adm` (bool admin), `ativo` (bool ativo), `trocarsenha` (bool força troca)
- Conexão: `pgsql`

## Stack de middleware (rotas protegidas)
`auth:sanctum` → `jetstream.auth_session` → `verified` → `CheckPasswordChange`

## Middleware customizado (app/Http/Middleware/)
- `CheckPasswordChange` — redireciona para troca de senha se `trocarsenha=true`

## Sessão
- Driver: database (tabela `public.sessions`)

## Schemas PostgreSQL (6 schemas — NUNCA assuma que todas as tabelas estão em public)
| Schema | Propósito |
|---|---|
| `strategic_planning` | Ciclos PEI, perspectivas BSC, objetivos, identidade, cadeia de valor |
| `action_plan` | Planos de ação, entregas, labels, comentários, histórico, anexos |
| `performance_indicators` | Indicadores/KPIs, metas, linha de base, evolução |
| `risk_management` | Riscos, mitigações, ocorrências |
| `organization` | Organizações hierárquicas, perfis de acesso |
| `public` | Users, auditoria, relatórios, alertas, configurações sistêmicas, análise ambiental |

## Estrutura de módulos
```
app/
  Livewire/            — Componentes Livewire por domínio:
    StrategicPlanning/ — PEI, Missão/Visão, Perspectivas, Objetivos, Mapa, SWOT, PESTEL
    ActionPlan/        — Planos de Ação
    Deliverables/      — Entregas (Kanban/Lista/Timeline)
    PerformanceIndicators/ — Indicadores, Evolução
    RiskManagement/    — Riscos, Mitigações
    Organization/      — Organizações
    Reports/           — Relatórios
    Audit/             — Auditoria
    Admin/             — Configurações
    Dashboard/         — Dashboard
  Models/              — Eloquent com schema qualificado explícito
  Services/            — PeiGuidanceService, IndicadorCalculoService, ReportGenerationService
  Policies/            — OrganizacaoPolicy, PlanoDeAcaoPolicy, IndicadorPolicy, RiscoPolicy
  Observers/           — EntregaObserver (recalculo automático de indicadores)

resources/views/
  livewire/            — Views Blade por domínio
  layouts/             — app.blade.php, public.blade.php
  components/          — Componentes Blade reutilizáveis

database/
  migrations/          — Organizadas por subpastas de domínio
  seeders/             — Seeders por domínio
```

## Módulos e rotas de entrada
- `/` → Mapa Estratégico (página inicial)
- `/dashboard` → Dashboard\Index
- `/pei` → MissaoVisao (identidade estratégica)
- `/pei/ciclos` → ListarPeis
- `/pei/mapa` → MapaEstrategico
- `/pei/perspectivas` → ListarPerspectivas
- `/pei/swot` → AnaliseSWOT
- `/pei/pestel` → AnalisePESTEL
- `/pei/valores` → ListarValores
- `/objetivos` → ListarObjetivos
- `/indicadores` → ListarIndicadores
- `/indicadores/{id}/evolucao` → LancarEvolucao ⚠️ Link invisível na UI — Fase 1 do roadmap
- `/planos` → ListarPlanos
- `/planos/{id}/entregas` → DeliverablesBoard ⚠️ Link pouco visível — Fase 1 do roadmap
- `/riscos` → Gestão de Riscos
- `/relatorios` → ListarRelatorios
- `/graus-satisfacao` → ListarGrausSatisfacao
- `/auditoria` → ListarLogs
- `/organizacoes` → ListarOrganizacoes
- `/configuracoes` → ConfiguracaoSistema

## Fluxo metodológico PEI (PeiGuidanceService)
O serviço `app/Services/PeiGuidanceService.php` guia o ciclo em fases sequenciais:
Ciclo PEI → Identidade (Missão/Visão) → Perspectivas → Objetivos → Graus de Satisfação → Indicadores → Planos de Ação → Dashboard

## Referência metodológica
- GPPEI (156 páginas): `documentacao/pdf/Guia_PEI_VF.pdf`
- Guia de Projetos: `documentacao/pdf/guia-pratico-de-projetos.pdf`
- Documento mestre com roadmap e histórias de usuário: `documentacao/documento-mestre-evolucao-sistema-pei.md`

## Convenções de código
- **Idioma**: variáveis, comentários e mensagens em Português do Brasil
- **Livewire**: componente PHP em `app/Livewire/`, view em `resources/views/livewire/`, kebab-case no Blade
- **Models**: declarar `$table` com prefixo de schema (ex: `strategic_planning.tab_pei`)
- **PKs**: UUID com `gen_random_uuid()` como default, `$incrementing = false`, `$keyType = 'string'`
- **Soft delete**: usar `deleted_at` nas tabelas de negócio
- **Novas migrations**: criar em subpasta do domínio em `database/migrations/`
- **Bootstrap 5**: usar classes Bootstrap + Bootstrap Icons na UI, seguindo padrão visual existente
- **Commits**: PT-BR, prefixo `feat|fix|refactor|chore|docs`

## Regras invioláveis
- ❌ NUNCA executar `php artisan migrate` sem confirmação explícita do usuário
- ❌ NUNCA executar `php artisan migrate:fresh` ou `migrate:rollback`
- ❌ NUNCA editar `.env` ou `config/database.php` diretamente
- ❌ NUNCA adicionar dependências Composer/NPM sem aprovação explícita
- ❌ NUNCA remover rotas ou Livewire components sem verificar dependências reais em disco
- ✅ Migrations: sempre NOVAS — jamais alterar migrations já aplicadas
- ✅ Antes de editar qualquer arquivo: lê-lo do disco no mesmo turno
- ✅ Após alterar PHP: executar `php -l {arquivo}` para validar sintaxe
- ✅ Reportar arquivos alterados e resultado da validação ao final de cada turno

## Protocolo pré-edição
1. Ler o arquivo-alvo do disco no mesmo turno
2. Confirmar dependências: rotas, Livewire components, includes Blade, Models referenciados
3. Executar `php -l {arquivo}` em cada PHP alterado antes de reportar conclusão
4. Reportar arquivos alterados e resultado da validação ao final
