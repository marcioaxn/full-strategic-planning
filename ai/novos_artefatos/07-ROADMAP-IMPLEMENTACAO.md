# ROADMAP DE IMPLEMENTAÇÃO
## Sistema de Planejamento Estratégico

**Versão:** 1.1
**Data de Criação:** 23/12/2025
**Última Atualização:** 24/12/2025 20:30
**Desenvolvedor:** Solo (com assistência de Claude AI)
**Prazo Total Estimado:** 14-16 semanas

---

## 📊 STATUS ATUAL DO PROJETO

**Última atualização:** 24/12/2025 às 20:30

### ✅ CONCLUÍDO

#### Migrations (100% - 43/43 executadas)
- ✅ Todas migrations do starter kit (users, sessions, tokens, etc.)
- ✅ Todas migrations do banco legado (30 tabelas schema PUBLIC e PEI)
- ✅ 4 migrations de Gestão de Riscos (novas)
- ✅ Migration do Laravel Auditing (audits table)
- ✅ Seed de dados iniciais (Unidade Central, 4 Perfis, 3 Tipos Execução, 100 Níveis)

#### Models Eloquent (100% - 26/26 criados)
- ✅ **PUBLIC Schema (6 models)**
  - User.php (atualizado com campos legados e relacionamentos)
  - Organization.php (hierarquia organizacional)
  - PerfilAcesso.php (4 perfis com UUIDs constantes)
  - Acao.php (log simples)
  - TabAudit.php (auditoria customizada)
  - TabStatus.php (lookup de status)

- ✅ **PEI Schema (17 models)**
  - PEI.php, MissaoVisaoValores.php, Valor.php
  - Perspectiva.php, ObjetivoEstrategico.php, FuturoAlmejadoObjetivoEstrategico.php
  - TipoExecucao.php, PlanoDeAcao.php, Entrega.php
  - Indicador.php, EvolucaoIndicador.php, LinhaBaseIndicador.php, MetaPorAno.php
  - GrauSatisfacao.php, Arquivo.php
  - AtividadeCadeiaValor.php, ProcessoAtividadeCadeiaValor.php

- ✅ **Gestão de Riscos (4 models)**
  - Risco.php (matriz 5x5, auto-cálculo de nível)
  - RiscoObjetivo.php (relacionamento com objetivos)
  - RiscoMitigacao.php (planos de mitigação)
  - RiscoOcorrencia.php (registro de materializações)

#### Packages Instalados
- ✅ Laravel 12.38.1
- ✅ Laravel Jetstream (com Livewire 3)
- ✅ Laravel Fortify (autenticação)
- ✅ Laravel Sanctum (tokens API)
- ✅ Livewire 3.6.4
- ✅ Laravel Auditing (owen-it/laravel-auditing) - instalado e configurado
- ✅ Composer autoloader atualizado (7702 classes)

### ⚠️ PENDENTE / EM ANDAMENTO

#### FASE 0 - Fundação (100% concluída)
- ✅ Laravel 12 instalado
- ✅ Jetstream com Livewire 3 instalado
- ✅ PostgreSQL configurado e funcionando
- ✅ Bootstrap 5.3.3 INSTALADO e configurado (Vite compilando SCSS)
- ✅ Layout base com Bootstrap 5 - COMPLETO (sidebar, topbar, theme system)
- ✅ Menu superior e sidebar - CRIADOS (com Alpine.js, dark mode, session timer)
- ✅ Dashboard moderno - CRIADO (com stats, cards, tables, gradientes)
- ✅ Autenticação adaptada para campos legados (ativo, trocarsenha) - CONCLUÍDO
- ✅ Tela de troca de senha obrigatória - CRIADA
- ✅ Navegação do sidebar adaptada para contexto de planejamento estratégico - CONCLUÍDO

#### FASE 1 - Core Básico (Em Andamento - 70%)
- ✅ Componentes Livewire de Organizações (CRUD completo) - CONCLUÍDO
- ✅ Componentes Livewire de Usuários (CRUD completo com vínculos) - CONCLUÍDO
- ✅ Policies (OrganizationPolicy, UserPolicy) - CONCLUÍDO
- ❌ Seletor de Organização - PENDENTE

#### Demais Fases (2-7)
- ❌ Todas as fases seguintes estão pendentes (0%)

### 🎯 PRÓXIMOS PASSOS SUGERIDOS

1. **Iniciar FASE 1** (2-3 dias):
   - Criar componente Livewire de Listagem de Organizações (CRUD)
   - Criar componente Livewire de Listagem de Usuários (CRUD)
   - Implementar Policies de autorização (OrganizationPolicy, UserPolicy)
   - Implementar Seletor de Organização no menu superior

2. **Planejar FASE 2**:
   - Revisar models de identidade estratégica
   - Preparar visualização de mapa estratégico

---

## 📋 DIRETRIZES COMPLETAS PARA OUTRAS IAs

**IMPORTANTE:** Esta seção é OBRIGATÓRIA para qualquer IA (Gemini, Codex, GPT-4, etc.) que for continuar este projeto. Claude AI estabeleceu padrões rigorosos que DEVEM ser seguidos para manter a qualidade e consistência do código.

### 1. 🏗️ ARQUITETURA DO SISTEMA

#### 1.1 Stack Tecnológico
```
Backend:
- Laravel 12.38.1 (PHP 8.3+)
- PostgreSQL 16+ (com extensão uuid-ossp)
- Redis (cache e sessões)

Frontend:
- Livewire 3.6.4 (componentes reativos full-stack)
- Alpine.js 3.x (JavaScript mínimo)
- Bootstrap 5.3.3 (framework CSS)
- Bootstrap Icons 1.11+
- Vite 5.x (build tool)

Packages Instalados:
- Laravel Jetstream (autenticação com Livewire)
- Laravel Fortify (autenticação backend)
- Laravel Sanctum (API tokens)
- owen-it/laravel-auditing (auditoria de mudanças)
```

#### 1.2 Estrutura de Diretórios
```
app/
├── Http/
│   ├── Controllers/          # EVITAR - Usar Livewire sempre que possível
│   ├── Middleware/           # Middlewares customizados
│   └── Livewire/             # PRINCIPAL - Todos os componentes Livewire aqui
│       ├── Auth/             # Componentes de autenticação
│       ├── Organizacao/      # CRUD de organizações
│       ├── Usuario/          # CRUD de usuários
│       ├── PEI/              # Componentes de PEI
│       ├── PlanoAcao/        # Componentes de planos de ação
│       ├── Indicador/        # Componentes de indicadores
│       ├── Risco/            # Componentes de gestão de riscos
│       ├── Dashboard/        # Dashboards e visualizações
│       └── Shared/           # Componentes reutilizáveis
├── Models/
│   ├── PEI/                  # Models do schema PEI
│   ├── User.php              # Model principal de usuários
│   ├── Organization.php      # Hierarquia organizacional
│   ├── PerfilAcesso.php      # Perfis de acesso (com UUIDs constantes)
│   ├── Risco.php             # Gestão de riscos
│   └── ...
├── Policies/                 # Autorização (Laravel Policies)
└── Services/                 # Lógica de negócio complexa

database/
├── migrations/               # 43 migrations já executadas (NÃO MODIFICAR)
└── seeders/                  # Seeds de dados iniciais

resources/
├── views/
│   ├── layouts/
│   │   ├── app.blade.php     # Layout principal (CRÍTICO - já completo)
│   │   └── partials/
│   │       ├── sidebar.blade.php
│   │       └── topbar.blade.php
│   ├── livewire/             # Views dos componentes Livewire
│   ├── navigation-menu.blade.php
│   └── dashboard.blade.php   # Dashboard moderno (já criado)
├── js/
│   └── app.js                # Bootstrap + Alpine + Theme system (já configurado)
└── scss/
    └── app.scss              # Estilos Bootstrap customizados (já configurado)
```

#### 1.3 Banco de Dados - ARQUITETURA LEGADA (CRÍTICO!)

**⚠️ ATENÇÃO MÁXIMA:** Este é um banco de dados LEGADO com dados reais em produção!

**Regras INVIOLÁVEIS:**
1. ❌ **NUNCA** criar novas migrations para tabelas que já existem
2. ❌ **NUNCA** alterar migrations que já foram executadas (43 migrations)
3. ❌ **NUNCA** executar `php artisan migrate:fresh` ou `migrate:refresh`
4. ✅ **SEMPRE** usar os Models Eloquent existentes (26 models já criados)
5. ✅ **SEMPRE** preservar 100% de compatibilidade com dados legados

**Schemas PostgreSQL:**
```sql
-- Schema PUBLIC (dados de usuários e organizações)
PUBLIC.users
PUBLIC.tab_organizacao
PUBLIC.tab_perfil_acesso
PUBLIC.tab_status
PUBLIC.tab_audit
PUBLIC.rel_users_tab_organizacoes_tab_perfil_acesso (many-to-many)

-- Schema PEI (planejamento estratégico institucional)
pei.tab_pei
pei.tab_missao_visao_valores
pei.tab_valores
pei.tab_perspectiva
pei.tab_objetivo_estrategico
pei.tab_futuro_almejado_objetivo_estrategico
pei.tab_tipo_execucao
pei.tab_plano_de_acao
pei.tab_entregas
pei.tab_indicador
pei.tab_evolucao_indicador
pei.tab_linha_base_indicador
pei.tab_meta_por_ano
pei.tab_grau_satisfacao
pei.tab_arquivos
pei.tab_atividade_cadeia_valor
pei.tab_processo_atividade_cadeia_valor
pei.tab_risco                              (NOVO - criado por Claude)
pei.tab_risco_objetivo                     (NOVO - criado por Claude)
pei.tab_risco_mitigacao                    (NOVO - criado por Claude)
pei.tab_risco_ocorrencia                   (NOVO - criado por Claude)
```

**Características Especiais do Banco:**
- ✅ Todas as tabelas usam **UUID como PRIMARY KEY** (`gen_random_uuid()`)
- ✅ Convenção de nomenclatura:
  - PK sempre: `cod_<nome_tabela>`
  - FK sempre: `cod_<tabela_relacionada>`
  - Exemplo: `pei.tab_plano_de_acao.cod_plano` (PK)
  - Exemplo: `pei.tab_plano_de_acao.cod_objetivo_estrategico` (FK)
- ✅ Soft Deletes implementado em tabelas principais (`deleted_at`)
- ✅ Timestamps: `created_at`, `updated_at` em todas as tabelas
- ✅ Auditoria via `owen-it/laravel-auditing` nas tabelas principais

**Campos Legados CRÍTICOS na tabela `users`:**
```php
'ativo' => 'boolean',        // true = usuário ativo, false = inativo
'trocarsenha' => 'integer',  // 0 = não precisa trocar, 1 = precisa trocar obrigatoriamente, 2 = já trocou
```

**⚠️ IMPORTANTE:** A autenticação DEVE verificar:
1. Se `ativo = true` (caso contrário, negar login)
2. Se `trocarsenha = 1` (caso contrário, redirecionar para tela de troca de senha ANTES de acessar sistema)

---

### 2. 🎨 PADRÕES DE UI/UX (Bootstrap 5)

#### 2.1 Sistema de Cores e Tema

**⚠️ NÃO usar Tailwind CSS!** O projeto usa **Bootstrap 5.3.3**.

**Cores Principais (definidas em `resources/scss/app.scss`):**
```scss
// Tema Light
--bs-primary: #0d6efd;
--bs-success: #198754;
--bs-danger: #dc3545;
--bs-warning: #ffc107;
--bs-info: #0dcaf0;

// Sistema de Dark Mode
// O sistema possui dark mode automático via Alpine.js
// Classes: .light-mode, .dark-mode, .system-mode
```

**Como usar:**
```html
<!-- Botões -->
<button class="btn btn-primary">Primário</button>
<button class="btn btn-outline-secondary">Secundário</button>

<!-- Cards -->
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">Título</div>
    <div class="card-body">Conteúdo</div>
</div>

<!-- Badges -->
<span class="badge bg-success">Ativo</span>
<span class="badge bg-danger">Crítico</span>

<!-- Alerts -->
<div class="alert alert-info" role="alert">Mensagem informativa</div>
```

#### 2.2 Layout Padrão (CRÍTICO - Já Implementado)

O arquivo `resources/views/layouts/app.blade.php` é o **layout principal** e **JÁ ESTÁ COMPLETO**.

**Estrutura:**
```blade
<div class="app-shell d-flex min-vh-100" x-data="appLayout()">
    <!-- Sidebar (colapsável) -->
    @include('layouts.partials.sidebar', ['items' => $appNavigation])

    <!-- Main content -->
    <div class="app-main flex-grow-1 d-flex flex-column">
        <!-- Topbar -->
        @livewire('navigation-menu')

        <!-- Page content -->
        <main class="flex-grow-1 p-4">
            {{ $slot }}
        </main>
    </div>
</div>
```

**Features já implementadas:**
- ✅ Sidebar responsivo (colapsa em mobile)
- ✅ Topbar com menu de usuário
- ✅ Dark mode toggle (light/dark/system)
- ✅ Session timer (timeout warning)
- ✅ Alpine.js state management
- ✅ Gradientes modernos

**⚠️ NÃO recriar este layout!** Apenas adaptar a navegação (`$appNavigation`).

#### 2.3 Componentes Bootstrap Usados

**Tabelas:**
```html
<div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead class="table-light">
            <tr>
                <th>Coluna 1</th>
                <th>Coluna 2</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <!-- Dados aqui -->
        </tbody>
    </table>
</div>
```

**Formulários:**
```html
<div class="mb-3">
    <label for="campo" class="form-label">Label</label>
    <input type="text" class="form-control" id="campo" wire:model="campo">
    @error('campo') <span class="text-danger small">{{ $message }}</span> @enderror
</div>
```

**Modais:**
```html
<div class="modal fade" id="meuModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Título</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Conteúdo</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary">Salvar</button>
            </div>
        </div>
    </div>
</div>
```

---

### 3. 💻 PADRÕES DE CÓDIGO LARAVEL

#### 3.1 Convenções de Nomenclatura (PSR-12)

**Classes:**
```php
// PascalCase para classes
class PlanoDeAcao extends Model {}
class ListarPlanos extends Component {}
class PlanoDeAcaoPolicy {}
```

**Métodos:**
```php
// camelCase para métodos
public function calcularPercentualAtingimento() {}
public function isAtrasado() {}
public function getStatusLabel() {}
```

**Variáveis:**
```php
// camelCase para variáveis
$planoDeAcao = PlanoDeAcao::find($id);
$percentualAtingimento = $this->calcular();
```

**Constantes:**
```php
// SCREAMING_SNAKE_CASE para constantes
const SUPER_ADMIN = 'c00b9ebc-7014-4d37-97dc-7875e55fff2a';
const STATUS_ATIVO = 'Ativo';
```

**Propriedades Livewire:**
```php
// camelCase para propriedades públicas
public $planoId;
public $descricaoPlano;
public $dataInicio;
```

#### 3.2 Models Eloquent (26 models já criados)

**⚠️ IMPORTANTE:** Todos os 26 models já foram criados por Claude AI e estão **COMPLETOS E FUNCIONAIS**.

**Localização:**
- `app/Models/` - Models do schema PUBLIC (User, Organization, PerfilAcesso, etc.)
- `app/Models/PEI/` - Models do schema PEI (PEI, ObjetivoEstrategico, PlanoDeAcao, etc.)

**Estrutura padrão de um Model:**
```php
<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PlanoDeAcao extends Model implements Auditable
{
    use HasFactory, HasUuids, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $table = 'pei.tab_plano_de_acao';
    protected $primaryKey = 'cod_plano';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'cod_pei',
        'cod_organizacao',
        'dsc_plano',
        // ... todos os campos
    ];

    protected $casts = [
        'dte_inicio' => 'date',
        'dte_fim' => 'date',
        'vlr_orcamento' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // === RELACIONAMENTOS ===

    public function pei()
    {
        return $this->belongsTo(PEI::class, 'cod_pei', 'cod_pei');
    }

    // === SCOPES ===

    public function scopeAtivos($query)
    {
        return $query->whereNotIn('dsc_status', ['Cancelado', 'Concluído']);
    }

    // === MÉTODOS AUXILIARES ===

    public function calcularPercentual()
    {
        // Lógica de cálculo
    }
}
```

**Traits OBRIGATÓRIOS:**
- `HasUuids` - Para primary keys UUID
- `SoftDeletes` - Para exclusão lógica (tabelas principais)
- `\OwenIt\Auditing\Auditable` - Para rastreamento de mudanças

**⚠️ NUNCA criar um model sem esses padrões!**

#### 3.3 Componentes Livewire

**Estrutura padrão de componente:**
```php
<?php

namespace App\Http\Livewire\PlanoAcao;

use App\Models\PEI\PlanoDeAcao;
use Livewire\Component;
use Livewire\WithPagination;

class ListarPlanos extends Component
{
    use WithPagination;

    // Propriedades públicas (wire:model)
    public $busca = '';
    public $status = '';
    public $organizacaoId = '';

    // Propriedades protected
    protected $queryString = ['busca', 'status'];

    // Listeners
    protected $listeners = ['planoSalvo' => '$refresh'];

    // Métodos de ciclo de vida
    public function mount()
    {
        // Inicialização
    }

    public function updating($name, $value)
    {
        // Antes de atualizar propriedade
        if ($name === 'busca') {
            $this->resetPage();
        }
    }

    // Métodos públicos (chamados do front-end)
    public function excluir($id)
    {
        $plano = PlanoDeAcao::findOrFail($id);

        // Autorização
        $this->authorize('delete', $plano);

        // Lógica
        $plano->delete();

        // Feedback
        session()->flash('mensagem', 'Plano excluído com sucesso!');
        session()->flash('tipo', 'success');
    }

    // Render
    public function render()
    {
        $planos = PlanoDeAcao::query()
            ->when($this->busca, fn($q) =>
                $q->where('dsc_plano', 'ilike', "%{$this->busca}%")
            )
            ->when($this->status, fn($q) =>
                $q->where('dsc_status', $this->status)
            )
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.plano-acao.listar-planos', [
            'planos' => $planos,
        ]);
    }
}
```

**View correspondente (`resources/views/livewire/plano-acao/listar-planos.blade.php`):**
```blade
<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Planos de Ação</h2>
        <button class="btn btn-primary" wire:click="$emit('abrirModal')">
            <i class="bi bi-plus-lg"></i> Novo Plano
        </button>
    </div>

    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control"
                           placeholder="Buscar..."
                           wire:model.debounce.300ms="busca">
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model="status">
                        <option value="">Todos os status</option>
                        <option value="Em Andamento">Em Andamento</option>
                        <option value="Concluído">Concluído</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th>Período</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($planos as $plano)
                        <tr>
                            <td>{{ $plano->dsc_plano }}</td>
                            <td>
                                <span class="badge {{ $plano->getStatusBadgeClass() }}">
                                    {{ $plano->dsc_status }}
                                </span>
                            </td>
                            <td>{{ $plano->dte_inicio->format('d/m/Y') }} a {{ $plano->dte_fim->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary"
                                        wire:click="$emit('editar', '{{ $plano->cod_plano }}')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger"
                                        wire:click="excluir('{{ $plano->cod_plano }}')"
                                        wire:confirm="Confirma exclusão?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Nenhum plano encontrado</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginação -->
    <div class="mt-3">
        {{ $planos->links() }}
    </div>
</div>
```

**Criação de componente (comando):**
```bash
php artisan make:livewire PlanoAcao/ListarPlanos
# Cria: app/Http/Livewire/PlanoAcao/ListarPlanos.php
# Cria: resources/views/livewire/plano-acao/listar-planos.blade.php
```

#### 3.4 Validação

**Sempre validar no componente Livewire:**
```php
protected $rules = [
    'descricao' => 'required|min:5|max:500',
    'dataInicio' => 'required|date',
    'dataFim' => 'required|date|after:dataInicio',
    'organizacaoId' => 'required|exists:PUBLIC.tab_organizacao,cod_organizacao',
];

protected $messages = [
    'descricao.required' => 'A descrição é obrigatória.',
    'descricao.min' => 'A descrição deve ter no mínimo 5 caracteres.',
    'dataFim.after' => 'A data fim deve ser posterior à data início.',
];

public function salvar()
{
    $this->validate();

    PlanoDeAcao::create([
        'dsc_plano' => $this->descricao,
        'dte_inicio' => $this->dataInicio,
        'dte_fim' => $this->dataFim,
        // ...
    ]);

    session()->flash('mensagem', 'Plano salvo com sucesso!');
    session()->flash('tipo', 'success');

    $this->emit('planoSalvo');
}
```

#### 3.5 Autorização (Policies)

**Criar Policy:**
```bash
php artisan make:policy PlanoDeAcaoPolicy --model=PlanoDeAcao
```

**Exemplo de Policy:**
```php
<?php

namespace App\Policies;

use App\Models\PEI\PlanoDeAcao;
use App\Models\User;
use App\Models\PerfilAcesso;

class PlanoDeAcaoPolicy
{
    // Super Admin pode tudo
    public function before(User $user, $ability)
    {
        if ($user->perfis->contains('cod_perfil_acesso', PerfilAcesso::SUPER_ADMIN)) {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        return true; // Todos podem listar
    }

    public function view(User $user, PlanoDeAcao $plano)
    {
        // Pode visualizar se for da mesma organização
        return $user->organizacoes->contains('cod_organizacao', $plano->cod_organizacao);
    }

    public function create(User $user)
    {
        // Admin Unidade e Gestor Responsável podem criar
        return $user->perfis->contains(function ($perfil) {
            return in_array($perfil->cod_perfil_acesso, [
                PerfilAcesso::ADMIN_UNIDADE,
                PerfilAcesso::GESTOR_RESPONSAVEL,
            ]);
        });
    }

    public function update(User $user, PlanoDeAcao $plano)
    {
        // Gestor responsável do plano pode editar
        return $plano->gestoresResponsaveis->contains('id', $user->id);
    }

    public function delete(User $user, PlanoDeAcao $plano)
    {
        // Apenas Admin Unidade e Super Admin podem excluir
        return $user->perfis->contains(function ($perfil) {
            return in_array($perfil->cod_perfil_acesso, [
                PerfilAcesso::SUPER_ADMIN,
                PerfilAcesso::ADMIN_UNIDADE,
            ]);
        });
    }
}
```

**Registrar Policy em `AuthServiceProvider`:**
```php
protected $policies = [
    PlanoDeAcao::class => PlanoDeAcaoPolicy::class,
];
```

**Usar no componente:**
```php
public function excluir($id)
{
    $plano = PlanoDeAcao::findOrFail($id);
    $this->authorize('delete', $plano);

    $plano->delete();
}
```

---

### 4. 🔒 AUTENTICAÇÃO E CAMPOS LEGADOS

**⚠️ ATENÇÃO CRÍTICA:** O sistema usa campos legados que DEVEM ser validados!

#### 4.1 Campo `ativo` (boolean)

**Validação obrigatória no login:**
```php
// Em app/Http/Livewire/Auth/Login.php ou middleware

if (!$user->ativo) {
    throw ValidationException::withMessages([
        'email' => ['Sua conta está inativa. Entre em contato com o administrador.'],
    ]);
}
```

#### 4.2 Campo `trocarsenha` (integer)

**Valores possíveis:**
- `0` = Usuário NÃO precisa trocar senha (normal)
- `1` = Usuário DEVE trocar senha obrigatoriamente (primeira vez ou reset)
- `2` = Usuário já trocou a senha

**Fluxo obrigatório:**
1. Após autenticação bem-sucedida, verificar `trocarsenha`
2. Se `trocarsenha == 1`, redirecionar IMEDIATAMENTE para tela de troca de senha
3. Bloquear acesso a qualquer outra rota até que a senha seja trocada
4. Após troca bem-sucedida, atualizar `trocarsenha = 2`

**Implementação sugerida:**

**Middleware `app/Http/Middleware/CheckPasswordChange.php`:**
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->trocarsenha == 1) {
            if (!$request->routeIs('auth.trocar-senha')) {
                return redirect()->route('auth.trocar-senha');
            }
        }

        return $next($request);
    }
}
```

**Componente Livewire `app/Http/Livewire/Auth/TrocarSenha.php`:**
```php
<?php

namespace App\Http\Livewire\Auth;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class TrocarSenha extends Component
{
    public $senhaAtual;
    public $novaSenha;
    public $novaSenhaConfirmacao;

    protected $rules = [
        'senhaAtual' => 'required',
        'novaSenha' => 'required|min:8|confirmed',
    ];

    public function trocarSenha()
    {
        $this->validate();

        $user = auth()->user();

        // Validar senha atual
        if (!Hash::check($this->senhaAtual, $user->password)) {
            $this->addError('senhaAtual', 'Senha atual incorreta.');
            return;
        }

        // Atualizar senha
        $user->update([
            'password' => Hash::make($this->novaSenha),
            'trocarsenha' => 2, // Marca como trocada
        ]);

        session()->flash('mensagem', 'Senha alterada com sucesso!');
        session()->flash('tipo', 'success');

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.auth.trocar-senha')
            ->layout('layouts.guest'); // Layout sem sidebar
    }
}
```

**Registrar middleware em `app/Http/Kernel.php`:**
```php
protected $middlewareGroups = [
    'web' => [
        // ... outros middlewares
        \App\Http\Middleware\CheckPasswordChange::class,
    ],
];
```

---

### 5. 🎯 PADRÕES DE COMMITS E GIT

**Formato de commit (Conventional Commits):**
```
tipo(escopo): descrição curta

Descrição detalhada (opcional)

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: [Nome da IA] <email>
```

**Tipos permitidos:**
- `feat:` Nova funcionalidade
- `fix:` Correção de bug
- `refactor:` Refatoração sem mudança funcional
- `docs:` Alteração em documentação
- `test:` Adição ou correção de testes
- `chore:` Tarefas de manutenção

**Exemplos:**
```
feat(planos): adicionar CRUD de planos de ação

Criado componente Livewire para listagem e formulário de planos.
Inclui validação, autorização via Policy e soft deletes.

🤖 Generated with Gemini Pro

Co-Authored-By: Gemini Pro <gemini@google.com>
```

```
fix(auth): corrigir validação de campo trocarsenha

Implementado middleware CheckPasswordChange e componente TrocarSenha.
Agora força troca de senha quando trocarsenha = 1.

🤖 Generated with Codex

Co-Authored-By: GitHub Codex <codex@github.com>
```

---

### 6. 📊 PADRÕES DE RELACIONAMENTOS

#### 6.1 Relacionamentos Comuns

**BelongsTo (Muitos para Um):**
```php
// PlanoDeAcao pertence a um ObjetivoEstrategico
public function objetivoEstrategico()
{
    return $this->belongsTo(ObjetivoEstrategico::class, 'cod_objetivo_estrategico', 'cod_objetivo_estrategico');
}
```

**HasMany (Um para Muitos):**
```php
// ObjetivoEstrategico tem muitos PlanoDeAcao
public function planos()
{
    return $this->hasMany(PlanoDeAcao::class, 'cod_objetivo_estrategico', 'cod_objetivo_estrategico');
}
```

**BelongsToMany (Muitos para Muitos):**
```php
// User pertence a muitas Organizations (e vice-versa)
public function organizacoes()
{
    return $this->belongsToMany(
        Organization::class,
        'rel_users_tab_organizacoes_tab_perfil_acesso',
        'user_id',
        'cod_organizacao'
    )->withPivot('cod_perfil_acesso')
     ->withTimestamps();
}
```

#### 6.2 Eager Loading (OBRIGATÓRIO para performance)

**❌ Ruim (N+1 queries):**
```php
$planos = PlanoDeAcao::all();
foreach ($planos as $plano) {
    echo $plano->objetivoEstrategico->dsc_objetivo; // Nova query a cada iteração!
}
```

**✅ Bom:**
```php
$planos = PlanoDeAcao::with('objetivoEstrategico', 'organizacao', 'gestoresResponsaveis')->get();
foreach ($planos as $plano) {
    echo $plano->objetivoEstrategico->dsc_objetivo; // Sem queries adicionais
}
```

---

### 7. 🧪 PADRÕES DE TESTES

**Criar teste:**
```bash
php artisan make:test PlanoDeAcaoTest
```

**Exemplo de teste:**
```php
<?php

namespace Tests\Feature;

use App\Models\PEI\PlanoDeAcao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanoDeAcaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_pode_criar_plano()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/planos', [
                'dsc_plano' => 'Novo Plano',
                'dte_inicio' => '2025-01-01',
                'dte_fim' => '2025-12-31',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pei.tab_plano_de_acao', [
            'dsc_plano' => 'Novo Plano',
        ]);
    }
}
```

---

### 8. 🚨 ERROS COMUNS A EVITAR

#### ❌ Erros Críticos

1. **NUNCA alterar migrations executadas:**
   ```bash
   # ❌ PROIBIDO
   php artisan migrate:fresh
   php artisan migrate:refresh

   # ✅ Permitido
   php artisan migrate
   ```

2. **NUNCA usar `id` como primary key em tabelas do schema PEI:**
   ```php
   // ❌ ERRADO
   Schema::create('pei.tab_nova_tabela', function (Blueprint $table) {
       $table->id(); // NUNCA!
   });

   // ✅ CORRETO
   Schema::create('pei.tab_nova_tabela', function (Blueprint $table) {
       $table->uuid('cod_nova_tabela')->primary()->default(DB::raw('gen_random_uuid()'));
   });
   ```

3. **NUNCA usar Tailwind CSS:**
   ```blade
   <!-- ❌ ERRADO -->
   <div class="flex items-center justify-between bg-blue-500">

   <!-- ✅ CORRETO -->
   <div class="d-flex align-items-center justify-content-between bg-primary">
   ```

4. **NUNCA esquecer de verificar `ativo` e `trocarsenha`:**
   ```php
   // ❌ ERRADO - Login sem validação
   Auth::attempt($credentials);

   // ✅ CORRETO
   if (Auth::attempt($credentials)) {
       if (!auth()->user()->ativo) {
           Auth::logout();
           throw ValidationException::withMessages(['email' => ['Conta inativa.']]);
       }

       if (auth()->user()->trocarsenha == 1) {
           return redirect()->route('auth.trocar-senha');
       }
   }
   ```

5. **NUNCA criar controllers quando puder usar Livewire:**
   ```php
   // ❌ EVITAR
   class PlanoController extends Controller {
       public function index() { /* ... */ }
   }

   // ✅ PREFERIR
   class ListarPlanos extends Component {
       public function render() { /* ... */ }
   }
   ```

---

### 9. 📚 DOCUMENTAÇÃO E RECURSOS

**Documentação Obrigatória:**
- Laravel 12: https://laravel.com/docs/12.x
- Livewire 3: https://livewire.laravel.com/docs/3.x
- Bootstrap 5.3: https://getbootstrap.com/docs/5.3
- Alpine.js 3: https://alpinejs.dev
- Laravel Auditing: https://laravel-auditing.com/

**Arquivos de Referência no Projeto:**
- `ai/novos_artefatos/04-MODELOS-ELOQUENT.md` - Especificação completa dos 26 models
- `ai/novos_artefatos/05-COMPONENTES-LIVEWIRE.md` - Lista de componentes a criar
- `ai/novos_artefatos/06-ROTAS-E-NAVEGACAO.md` - Estrutura de rotas
- `app/Models/` - Todos os models prontos e comentados

---

### 10. ✅ CHECKLIST ANTES DE COMMITAR

Antes de fazer qualquer commit, verificar:

- [ ] Código segue PSR-12
- [ ] Models usam `HasUuids`, `SoftDeletes` e `Auditable` quando apropriado
- [ ] Componentes Livewire têm validação adequada
- [ ] Policies de autorização estão implementadas
- [ ] Eager loading está sendo usado (evitar N+1)
- [ ] UI usa Bootstrap 5 (NUNCA Tailwind)
- [ ] Nomes de variáveis/métodos seguem convenções
- [ ] Não há `dd()`, `dump()` ou `var_dump()` no código
- [ ] Mensagens de sucesso/erro estão implementadas
- [ ] Código está comentado em partes complexas
- [ ] Testes foram executados (`php artisan test`)
- [ ] Assets foram compilados (`npm run build`)
- [ ] Commit message segue Conventional Commits

---

### 📝 NOTAS PARA CONTINUIDADE (Gemini/Codex)

**Onde Claude parou:**
- ✅ Todas migrations executadas com sucesso (43 migrations)
- ✅ Todos 26 models Eloquent criados e funcionais
- ✅ Laravel Auditing instalado (migration duplicada foi removida)
- ✅ Banco de dados 100% estruturado e pronto
- ⚠️ Bootstrap 5 NÃO foi instalado/configurado (ainda usa Tailwind)
- ⚠️ Nenhum componente Livewire foi criado ainda
- ⚠️ Layout base ainda não foi adaptado para Bootstrap
- ⚠️ Autenticação não valida campos legados (ativo, trocarsenha)

**Comandos importantes executados:**
```bash
composer dump-autoload  # Executado e concluído (7702 classes)
php artisan migrate     # Executado com sucesso (43 migrations)
```

**Arquivos importantes:**
- Models: `app/Models/` e `app/Models/PEI/`
- Migrations: `database/migrations/`
- Documentação: `ai/novos_artefatos/04-MODELOS-ELOQUENT.md`

---

## VISÃO GERAL

Este roadmap está organizado em **7 fases incrementais**, cada uma entregando valor funcional ao cliente. O desenvolvimento seguirá metodologia ágil com entregas semanais.

**Princípios:**
- ✅ **Entregas incrementais** - Cada fase entrega funcionalidade utilizável
- ✅ **Foco no banco legado** - Aproveitar 100% dos dados existentes
- ✅ **Qualidade sobre velocidade** - Código limpo, testado e documentado
- ✅ **Feedback contínuo** - Validação com cliente ao final de cada fase

---

## FASE 0: FUNDAÇÃO (Semana 1)
**Objetivo:** Configurar ambiente de desenvolvimento e estrutura base do projeto

### Tarefas

#### 0.1 Configuração do Ambiente ⏱️ 1 dia
- [ ] Instalar Laravel 12
- [ ] Instalar Jetstream com Livewire 3
- [ ] Configurar Bootstrap 5 (substituir Tailwind)
- [ ] Configurar conexão com PostgreSQL (banco legado)
- [ ] Configurar Redis (cache e sessões)
- [ ] Configurar variáveis de ambiente (`.env`)

**Comando:**
```bash
composer create-project laravel/laravel seae
cd seae
composer require laravel/jetstream
php artisan jetstream:install livewire
npm install bootstrap @popperjs/core
```

---

#### 0.2 Estrutura de Autenticação ⏱️ 2 dias
- [x] Adaptar login do Jetstream para tabela `users` (UUID)
- [x] Implementar verificação de campo `ativo`
- [x] Implementar redirecionamento se `trocarsenha = 1`
- [x] Criar tela de troca de senha obrigatória
- [x] Configurar reset de senha por email
- [x] Testar fluxo completo de autenticação

**Arquivos principais:**
- `app/Models/User.php` - Ajustar para UUID
- `app/Http/Livewire/Auth/TrocarSenha.php`
- `resources/views/livewire/auth/trocar-senha.blade.php`

---

#### 0.3 Layout Base e Navegação ⏱️ 2 dias
- [x] Criar layout principal com Bootstrap 5
- [x] Implementar menu superior (logo, seletor de organização, perfil)
- [x] Implementar menu lateral (sidebar) com navegação
- [x] Implementar breadcrumbs
- [x] Implementar sistema de toasts (notificações)
- [x] Criar página de dashboard vazia (placeholder)

**Arquivos principais:**
- `resources/views/layouts/app.blade.php`
- `resources/views/components/app-layout.blade.php`
- `app/Http/Livewire/Dashboard/Index.php`

---

**Entrega Fase 0:**
- ✅ Sistema instalado e rodando
- ✅ Usuário consegue fazer login
- ✅ Layout responsivo funcionando
- ✅ Navegação básica implementada

**Critério de Aceitação:**
- Usuário `adm@adm.gov.br` (do banco legado) consegue logar
- Interface Bootstrap renderiza corretamente

---

## FASE 1: CORE BÁSICO (Semanas 2-3)
**Objetivo:** Implementar gestão de organizações e usuários

### Módulo: Gestão de Organizações ⏱️ 3 dias

#### 1.1 Listar Organizações
- [ ] Criar componente `ListarOrganizacoes`
- [ ] Exibir hierarquia em TreeView
- [ ] Implementar busca por nome/sigla
- [ ] Implementar filtro de organizações excluídas
- [ ] Testar com dados reais do banco

**Componente:** `app/Http/Livewire/Organizacao/ListarOrganizacoes.php`

---

#### 1.2 CRUD de Organizações
- [ ] Criar formulário de criação/edição
- [ ] Validar campos (sigla única, nome obrigatório)
- [ ] Implementar seletor de organização pai
- [ ] Implementar exclusão lógica (soft delete)
- [ ] Implementar restauração
- [ ] Criar Policy para autorização
- [ ] Testar criação de hierarquia

**Componentes:**
- `FormOrganizacao.php`
- `app/Policies/OrganizationPolicy.php`

---

### Módulo: Gestão de Usuários ⏱️ 4 dias

#### 1.3 Listar Usuários
- [ ] Criar componente `ListarUsuarios`
- [ ] Exibir: Nome, Email, Organizações, Perfis, Status
- [ ] Implementar filtros (status, organização, perfil)
- [ ] Implementar busca por nome/email
- [ ] Implementar paginação
- [ ] Respeitar permissões (Super Admin vê todos, Admin Unidade vê sua unidade)

---

#### 1.4 CRUD de Usuários
- [ ] Criar formulário de criação/edição
- [ ] Implementar multi-select de organizações
- [ ] Implementar atribuição de perfis por organização
- [ ] Implementar reset de senha
- [ ] Implementar ativação/inativação
- [ ] Criar Policy de autorização
- [ ] Testar permissões granulares

---

#### 1.5 Componente Seletor de Organização
- [ ] Criar dropdown de organizações no menu superior
- [ ] Armazenar seleção em sessão
- [ ] Filtrar dados com base na organização selecionada
- [ ] Testar troca de organização e atualização de dados

**Componente:** `app/Http/Livewire/Shared/SeletorOrganizacao.php`

---

**Entrega Fase 1:**
- ✅ CRUD completo de Organizações
- ✅ CRUD completo de Usuários
- ✅ Seletor de Organização funcionando
- ✅ Permissões implementadas

**Critério de Aceitação:**
- Admin pode criar organizações e usuários
- Usuário comum vê apenas sua organização
- Troca de organização reflete nos dados exibidos

---

## FASE 2: IDENTIDADE E BSC (Semanas 4-5)
**Objetivo:** Implementar Identidade Estratégica e Balanced Scorecard

### Módulo: Identidade Estratégica ⏱️ 3 dias

#### 2.1 Missão e Visão
- [ ] Criar componente `MissaoVisao`
- [ ] Exibir missão e visão da organização selecionada
- [ ] Implementar modo de edição inline
- [ ] Validar textos (obrigatório, máximo 2000 caracteres)
- [ ] Salvar alterações com auditoria
- [ ] Testar versionamento

**Tabela:** `pei.tab_missao_visao_valores`

---

#### 2.2 Valores Organizacionais
- [ ] Criar componente `ListarValores`
- [ ] Exibir lista de valores
- [ ] Implementar CRUD de valores
- [ ] Ordenação drag-and-drop
- [ ] Teste de criação/edição/exclusão

**Tabela:** `pei.tab_valores`

---

### Módulo: Balanced Scorecard ⏱️ 5 dias

#### 2.3 Gestão de Perspectivas
- [ ] Criar componente `ListarPerspectivas`
- [ ] Exibir 4 perspectivas padrão do BSC
- [ ] Implementar ordenação (drag-and-drop)
- [ ] Permitir edição de nomes (Super Admin)
- [ ] Testar com dados do banco

**Tabela:** `pei.tab_perspectiva`

---

#### 2.4 Objetivos Estratégicos
- [ ] Criar componente `ListarObjetivos`
- [ ] Agrupar por perspectiva (abas ou accordion)
- [ ] Exibir: Nome, Descrição, KPIs vinculados, % Atingimento
- [ ] Implementar busca e filtros
- [ ] Criar formulário de CRUD
- [ ] Implementar ordenação hierárquica
- [ ] Calcular % de atingimento (baseado em indicadores)
- [ ] Testar cálculos

**Tabelas:** `pei.tab_objetivo_estrategico`, `pei.tab_indicador`

---

#### 2.5 Futuro Almejado
- [ ] Criar componente para gerenciar futuros almejados
- [ ] Vincular a objetivos estratégicos
- [ ] CRUD simples (textarea)
- [ ] Testar vinculação

**Tabela:** `pei.tab_futuro_almejado_objetivo_estrategico`

---

#### 2.6 Visualização de Mapa Estratégico (básico)
- [ ] Criar visualização gráfica das 4 perspectivas
- [ ] Exibir objetivos em cada perspectiva
- [ ] Colorir por % de atingimento (verde/amarelo/vermelho)
- [ ] Implementar modal de detalhes ao clicar
- [ ] Testar navegação

**Componente:** `app/Http/Livewire/Dashboard/MapaEstrategico.php`

---

**Entrega Fase 2:**
- ✅ Missão, Visão e Valores gerenciáveis
- ✅ Objetivos Estratégicos por perspectiva
- ✅ Mapa Estratégico visual

**Critério de Aceitação:**
- Usuário visualiza mapa estratégico da organização
- Objetivos exibem % de atingimento correto
- Edição de identidade é auditada

---

## FASE 3: PLANOS DE AÇÃO (Semanas 6-7)
**Objetivo:** Implementar gestão completa de Planos de Ação

### Módulo: Planos de Ação ⏱️ 5 dias

#### 3.1 Listar Planos
- [ ] Criar componente `ListarPlanos`
- [ ] Exibir: Tipo, Descrição, Objetivo, Organização, Datas, Status
- [ ] Implementar filtros (tipo, status, organização, período)
- [ ] Implementar busca
- [ ] Implementar paginação
- [ ] Badge de status e indicador de atraso
- [ ] Testar com dados reais

---

#### 3.2 CRUD de Planos
- [ ] Criar formulário completo
- [ ] Campos: Descrição, Tipo, Objetivo, Organização, Datas, Orçamento, Status, PPA, LOA
- [ ] Validação de datas (fim > início)
- [ ] Dropdown de objetivos (filtrado por PEI)
- [ ] Dropdown de tipo de execução (Ação/Iniciativa/Projeto)
- [ ] Implementar auditoria
- [ ] Criar Policy (permissões por gestor)
- [ ] Testar criação e edição

---

#### 3.3 Gestão de Entregas
- [ ] Criar componente `GerenciarEntregas`
- [ ] Listar entregas do plano
- [ ] CRUD de entregas
- [ ] Campos: Descrição, Status, Período de Medição
- [ ] Ordenação drag-and-drop
- [ ] Calcular % de progresso do plano (baseado em entregas)
- [ ] Testar vínculo

**Tabela:** `pei.tab_entregas`

---

#### 3.4 Atribuição de Responsáveis
- [ ] Criar componente `AtribuirResponsavel`
- [ ] Selecionar usuários como Gestor Responsável
- [ ] Selecionar usuários como Gestor Substituto
- [ ] Permitir múltiplos substitutos
- [ ] Validar não duplicação
- [ ] Testar permissões após atribuição

**Tabela:** `rel_users_tab_organizacoes_tab_perfil_acesso`

---

#### 3.5 Detalhes de Plano
- [ ] Criar página/modal de detalhes
- [ ] Exibir todas as informações
- [ ] Exibir responsáveis
- [ ] Exibir entregas
- [ ] Exibir indicadores vinculados
- [ ] Timeline de alterações (auditoria)
- [ ] Testar navegação

---

**Entrega Fase 3:**
- ✅ CRUD completo de Planos de Ação
- ✅ Gestão de Entregas
- ✅ Atribuição de Responsáveis

**Critério de Aceitação:**
- Gestor consegue criar e gerenciar seus planos
- Admin consegue atribuir responsáveis
- % de progresso é calculado corretamente

---

## FASE 4: INDICADORES (Semanas 8-10)
**Objetivo:** Implementar gestão completa de Indicadores (KPIs)

### Módulo: Indicadores ⏱️ 7 dias

#### 4.1 Listar Indicadores
- [ ] Criar componente `ListarIndicadores`
- [ ] Exibir: Nome, Tipo, Unidade, Vinculação, Status, Farol
- [ ] Implementar filtros (tipo, objetivo, plano, organização)
- [ ] Implementar busca
- [ ] Badge de farol (verde/amarelo/vermelho)
- [ ] Indicador de status (Em dia/Atrasado/Sem dados)
- [ ] Paginação
- [ ] Testar com indicadores reais

---

#### 4.2 CRUD de Indicadores
- [ ] Criar formulário extenso
- [ ] Campos: Nome, Descrição, Tipo, Vinculação, Unidade, Meta, Fórmula, Fonte, etc.
- [ ] Validação: Deve estar vinculado a Objetivo OU Plano (não ambos)
- [ ] Dropdown condicional (se tipo = Objetivo, mostra objetivos; se tipo = Plano, mostra planos)
- [ ] Implementar todos os 15 campos da tabela
- [ ] Criar Policy
- [ ] Testar criação e edição

**Tabela:** `pei.tab_indicador`

---

#### 4.3 Linha de Base
- [ ] Criar formulário de linha de base
- [ ] Campos: Ano, Valor
- [ ] Permitir edição
- [ ] Validar ano único
- [ ] Testar cadastro

**Tabela:** `pei.tab_linha_base_indicador`

---

#### 4.4 Metas Anuais
- [ ] Criar componente `GerenciarMetas`
- [ ] Formulário: Ano, Meta
- [ ] Permitir cadastrar múltiplos anos
- [ ] Validar ano único
- [ ] Listar metas cadastradas
- [ ] Editar/Excluir metas
- [ ] Testar com diferentes períodos

**Tabela:** `pei.tab_meta_por_ano`

---

#### 4.5 Lançar Evolução Mensal
- [ ] Criar componente `LancarEvolucao`
- [ ] Formulário: Ano, Mês, Valor Previsto, Valor Realizado, Avaliação
- [ ] Carregar evolução existente se houver
- [ ] Calcular desvio (realizado - previsto)
- [ ] Calcular % atingimento vs. meta
- [ ] Checkbox "Atualizado"
- [ ] Salvar com auditoria
- [ ] Testar lançamentos mensais

**Tabela:** `pei.tab_evolucao_indicador`

---

#### 4.6 Anexar Arquivos de Evidência
- [ ] Criar componente `AnexarArquivo`
- [ ] Upload de arquivo (PDF, Excel, Word, Imagem)
- [ ] Campos: Assunto, Data
- [ ] Validar tamanho (máximo 10 MB)
- [ ] Armazenar em `storage/app/pei/evidencias/`
- [ ] Listar arquivos anexados
- [ ] Permitir download
- [ ] Permitir exclusão
- [ ] Testar upload e download

**Tabela:** `pei.tab_arquivos`

---

#### 4.7 Farol de Desempenho
- [ ] Implementar cálculo de % atingimento
- [ ] Buscar faixa correspondente em `pei.tab_grau_satisfacao`
- [ ] Exibir cor e descrição
- [ ] Mostrar em cards e listagens
- [ ] Testar com diferentes percentuais

**Tabela:** `pei.tab_grau_satisfacao`

---

#### 4.8 Detalhes de Indicador
- [ ] Criar página/modal de detalhes
- [ ] Exibir ficha técnica completa
- [ ] Gráfico de evolução (line chart)
- [ ] Tabela de evolução mensal
- [ ] Lista de arquivos
- [ ] Timeline de alterações
- [ ] Testar navegação

---

**Entrega Fase 4:**
- ✅ CRUD completo de Indicadores
- ✅ Lançamento de evolução mensal
- ✅ Gestão de metas e linha de base
- ✅ Anexo de evidências
- ✅ Farol de desempenho funcionando

**Critério de Aceitação:**
- Gestor consegue lançar evolução mensal
- Gráficos exibem dados corretamente
- Farol calcula cor correta

---

## FASE 5: DASHBOARDS E RELATÓRIOS (Semanas 11-12)
**Objetivo:** Implementar painéis executivos e relatórios

### Módulo: Dashboards ⏱️ 5 dias

#### 5.1 Dashboard Principal
- [ ] Criar componente `DashboardPrincipal`
- [ ] KPIs principais (cards): Total Objetivos, Total Planos, Total Indicadores, % Médio
- [ ] Gráfico radar (% por perspectiva) - Chart.js
- [ ] Gráfico de evolução mensal dos indicadores críticos
- [ ] Lista de alertas (planos atrasados, indicadores sem lançamento)
- [ ] Últimas atualizações (timeline)
- [ ] Testar com dados reais

---

#### 5.2 Dashboard de Objetivos
- [ ] Filtros: Organização, PEI, Perspectiva
- [ ] Cards de objetivos com % atingimento
- [ ] Gráfico de distribuição por perspectiva
- [ ] Gráfico de % atingimento por objetivo (bar chart)
- [ ] Drill-down para detalhes
- [ ] Testar navegação

---

#### 5.3 Dashboard de Indicadores
- [ ] Filtros: Organização, PEI, Objetivo, Plano, Período
- [ ] Tabela de indicadores com última medição
- [ ] Gráfico de evolução temporal (line chart)
- [ ] Comparativo Previsto vs. Realizado
- [ ] Distribuição de faróis
- [ ] Alertas de indicadores sem lançamento
- [ ] Testar com múltiplos indicadores

---

#### 5.4 Dashboard de Planos de Ação
- [ ] Filtros múltiplos
- [ ] Cards de totais por tipo
- [ ] Gráfico de Gantt simplificado (timeline)
- [ ] Lista de planos com status visual
- [ ] Alertas de vencimento
- [ ] Gráfico de orçamento
- [ ] Testar visualizações

---

### Módulo: Relatórios ⏱️ 3 dias

#### 5.5 Relatório de Identidade Estratégica (PDF)
- [ ] Criar service `RelatorioService`
- [ ] Gerar PDF com Missão, Visão, Valores
- [ ] Cabeçalho com logo
- [ ] Rodapé com data de geração
- [ ] Botão de exportação
- [ ] Testar geração

**Biblioteca:** `barryvdh/laravel-dompdf`

---

#### 5.6 Relatório de Objetivos (PDF e Excel)
- [ ] Filtros: Organização, PEI, Perspectiva
- [ ] Tabela com objetivos e KPIs
- [ ] Agrupamento por perspectiva
- [ ] Totalizadores
- [ ] Exportação em PDF
- [ ] Exportação em Excel
- [ ] Testar ambos formatos

**Biblioteca:** `maatwebsite/excel`

---

#### 5.7 Relatório de Indicadores (PDF e Excel)
- [ ] Filtros múltiplos
- [ ] Tabela detalhada
- [ ] Gráficos de evolução
- [ ] Comparativos
- [ ] Análise de desvios
- [ ] Exportação em PDF e Excel
- [ ] Testar geração

---

#### 5.8 Relatório Executivo Consolidado (PDF)
- [ ] Sumário executivo
- [ ] Identidade estratégica
- [ ] Mapa estratégico (imagem)
- [ ] Objetivos por perspectiva
- [ ] TOP 10 indicadores
- [ ] Planos em andamento
- [ ] Alertas e desvios
- [ ] Análise de performance
- [ ] Testar documento completo

---

**Entrega Fase 5:**
- ✅ Dashboards executivos funcionais
- ✅ Relatórios em PDF e Excel
- ✅ Gráficos interativos (Chart.js)

**Critério de Aceitação:**
- CEO visualiza dashboard e entende situação estratégica
- Relatórios são gerados sem erros
- Gráficos refletem dados corretos

---

## FASE 6: GESTÃO DE RISCOS (Semanas 13-14)
**Objetivo:** Implementar módulo completo de identificação, avaliação e mitigação de riscos estratégicos

**STATUS: 30% CONCLUÍDO** (Migrations e Models prontos)

### ✅ Criar Tabelas de Riscos ⏱️ 1 dia - CONCLUÍDO

**Migrations:**
- [x] `create_pei_tab_risco_table` - Riscos estratégicos ✅
- [x] `create_pei_tab_risco_objetivo_table` - Vinculação com objetivos ✅
- [x] `create_pei_tab_risco_mitigacao_table` - Planos de mitigação ✅
- [x] `create_pei_tab_risco_ocorrencia_table` - Ocorrências registradas ✅

**Arquivos criados:**
- `database/migrations/2025_12_24_100000_create_pei_tab_risco_table.php`
- `database/migrations/2025_12_24_100001_create_pei_tab_risco_objetivo_table.php`
- `database/migrations/2025_12_24_100002_create_pei_tab_risco_mitigacao_table.php`
- `database/migrations/2025_12_24_100003_create_pei_tab_risco_ocorrencia_table.php`

---

### ✅ Models de Riscos ⏱️ 1 dia - CONCLUÍDO

- [x] `Risco` - Com scopes (ativos, críticos, porCategoria, porNivel) ✅
  - Auto-cálculo de nível (probabilidade × impacto)
  - Auto-incremento de código do risco por PEI
  - Métodos auxiliares: getNivelRiscoLabel(), getNivelRiscoCor(), isCritico()
  - Trait Auditable implementado

- [x] `RiscoObjetivo` - Pivot entre riscos e objetivos ✅
  - Tabela: `pei.tab_risco_objetivo`
  - Relacionamentos bidirecionais

- [x] `RiscoMitigacao` - Planos de mitigação com status ✅
  - Scopes: atrasados, porStatus, porTipo
  - Métodos: isAtrasado(), isConcluido(), getDiasRestantes()
  - Trait Auditable implementado

- [x] `RiscoOcorrencia` - Registro de materializações ✅
  - Scopes: recentes, porPeriodo
  - Métodos: getImpactoRealLabel(), getImpactoRealCor(), isRecente()
  - Trait Auditable implementado

**Arquivos criados:**
- `app/Models/Risco.php`
- `app/Models/RiscoObjetivo.php`
- `app/Models/RiscoMitigacao.php`
- `app/Models/RiscoOcorrencia.php`

---

### CRUD de Riscos ⏱️ 2 dias

**Componentes:**
- [ ] `RiscoIndex` - Listagem com filtros (categoria, nível, status)
- [ ] `RiscoForm` - Criação/edição com cálculo dinâmico de nível
- [ ] `RiscoShow` - Visualização detalhada com histórico

---

### Planos de Mitigação ⏱️ 2 dias

**Componentes:**
- [ ] `MitigacaoForm` - Formulário de plano de mitigação
- [ ] `MitigacaoList` - Lista de mitigações por risco

---

### Registro de Ocorrências ⏱️ 1 dia

**Componentes:**
- [ ] `OcorrenciaForm` - Registro de risco materializado
- [ ] `OcorrenciaTimeline` - Timeline de ocorrências

---

### Matriz de Riscos ⏱️ 2 dias

**Componentes:**
- [ ] `MatrizRiscos` - Grid 5x5 Probabilidade × Impacto

---

### Dashboard de Riscos ⏱️ 2 dias

**Componentes:**
- [ ] `DashboardRiscos` - Painel executivo com KPIs e alertas

---

### Relatório de Riscos ⏱️ 1 dia

- [ ] Relatório PDF consolidado
- [ ] Exportação Excel

**Entregáveis da Fase 6:**
- ✅ Módulo completo de Gestão de Riscos funcionando
- ✅ 4 novas tabelas criadas com padrão UUID
- ✅ Matriz de riscos interativa
- ✅ Dashboard executivo com alertas

**Critério de Aceitação:**
- CEO visualiza matriz de riscos e identifica criticidades
- Gestores conseguem cadastrar riscos e planos de mitigação
- Dashboard exibe alertas de riscos críticos sem mitigação

---

## FASE 7: REFINAMENTOS E AUDITORIA (Semana 15)
**Objetivo:** Ajustes finais e implementação de auditoria

### Auditoria e Logs ⏱️ 3 dias

#### 6.1 Visualização de Logs
- [ ] Criar componente `ListarLogs`
- [ ] Filtros: Usuário, Tabela, Ação, Período, IP
- [ ] Tabela com colunas principais
- [ ] Ordenação por data (decrescente)
- [ ] Paginação
- [ ] Exportação Excel
- [ ] Testar com dados de auditoria

**Tabelas:** `tab_audit`, `audits`

---

#### 6.2 Detalhes de Auditoria
- [ ] Criar modal/página de detalhes
- [ ] Exibir valor antes/depois
- [ ] Diff visual (destacar alterações)
- [ ] Informações do usuário e IP
- [ ] Testar visualização

---

#### 6.3 Timeline de Alterações
- [ ] Timeline visual por registro
- [ ] Cada item mostra data, usuário, ação
- [ ] Link para detalhes
- [ ] Filtro por período
- [ ] Testar com múltiplas alterações

---

### Performance e Otimização ⏱️ 2 dias

#### 6.4 Otimização de Queries
- [ ] Revisar queries com Laravel Debugbar
- [ ] Adicionar Eager Loading onde necessário
- [ ] Criar índices no banco (se necessário)
- [ ] Implementar cache (Redis) para dados frequentes
- [ ] Testar performance com grande volume de dados

---

#### 6.5 Otimização de Frontend
- [ ] Minificar CSS e JS (Vite)
- [ ] Otimizar imagens
- [ ] Implementar lazy loading de componentes pesados
- [ ] Testar velocidade de carregamento

---

**Entrega Fase 6:**
- ✅ Auditoria completa e visualizável
- ✅ Performance otimizada
- ✅ Logs detalhados

---

## FASE 8: TESTES E DOCUMENTAÇÃO (Semana 16)
**Objetivo:** Garantir qualidade e documentar sistema

### Testes ⏱️ 3 dias

#### 8.1 Testes de Feature
- [ ] Testar fluxo de login
- [ ] Testar CRUD de organizações
- [ ] Testar CRUD de planos de ação
- [ ] Testar lançamento de indicadores
- [ ] Cobertura mínima 60%

**Ferramenta:** PHPUnit

---

#### 8.2 Testes de Browser (E2E)
- [ ] Testar navegação completa
- [ ] Testar criação de plano de ação
- [ ] Testar lançamento de evolução
- [ ] Testar exportação de relatórios

**Ferramenta:** Laravel Dusk

---

### Documentação ⏱️ 2 dias

#### 8.3 Manual do Usuário
- [ ] Criar PDF com capturas de tela
- [ ] Passo-a-passo de cada funcionalidade
- [ ] FAQs
- [ ] Glossário

---

#### 8.4 Manual Técnico
- [ ] Arquitetura do sistema
- [ ] Diagramas de fluxo
- [ ] Instruções de deploy
- [ ] Troubleshooting

---

#### 8.5 README.md
- [ ] Instruções de instalação
- [ ] Configuração inicial
- [ ] Comandos úteis
- [ ] Contribuição

---

**Entrega Fase 7:**
- ✅ Sistema testado e validado
- ✅ Documentação completa
- ✅ Pronto para produção

---

## RESUMO DE ENTREGAS POR FASE

| Fase | Semanas | Entregas Principais |
|------|---------|---------------------|
| **0 - Fundação** | 1 | Ambiente configurado, Login funcionando |
| **1 - Core Básico** | 2-3 | Organizações, Usuários, Permissões |
| **2 - Identidade e BSC** | 4-5 | Missão/Visão/Valores, Objetivos, Mapa Estratégico |
| **3 - Planos de Ação** | 6-7 | CRUD Planos, Entregas, Responsáveis |
| **4 - Indicadores** | 8-10 | CRUD Indicadores, Evolução, Metas, Evidências, Farol |
| **5 - Dashboards e Relatórios** | 11-12 | Dashboards, Relatórios PDF/Excel, Gráficos |
| **6 - Refinamentos** | 13 | Auditoria, Performance, Otimização |
| **7 - Testes e Docs** | 14 | Testes, Documentação, Deploy |

---

## COMANDOS ÚTEIS DURANTE O DESENVOLVIMENTO

```bash
# Gerar componente Livewire
php artisan make:livewire PlanoAcao/ListarPlanos

# Gerar model com migration
php artisan make:model Models/PEI/PlanoDeAcao -m

# Gerar policy
php artisan make:policy PlanoDeAcaoPolicy --model=PlanoDeAcao

# Rodar migrations
php artisan migrate

# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Build de assets
npm run build

# Rodar testes
php artisan test
php artisan dusk

# Gerar documentação de rotas
php artisan route:list
```

---

## CRITÉRIOS DE SUCESSO DO PROJETO

✅ **Funcional:**
- Todos os requisitos funcionais implementados
- 100% compatível com banco legado
- Sem perda de dados históricos

✅ **Técnico:**
- Código seguindo PSR-12
- Cobertura de testes ≥ 60%
- Performance adequada (páginas ≤ 2s)

✅ **Negócio:**
- CEO aprova interface e usabilidade
- Usuários conseguem executar tarefas sem treinamento extenso
- Sistema estável em produção

---

**Conclusão:** Este roadmap fornece um caminho claro e estruturado para implementar o sistema completo em 14-16 semanas (incluindo módulo de Gestão de Riscos). Cada fase entrega valor incremental, permitindo validação contínua com o cliente.

---

## 📈 RESUMO EXECUTIVO DE PROGRESSO

### Trabalho Realizado Até Agora (24/12/2025)

**Tempo investido:** ~1 dia de desenvolvimento (com Claude AI)

**Entregas:**
1. ✅ **43 migrations** executadas com sucesso
   - Schema PUBLIC: 9 tabelas (users, organizations, perfis, etc.)
   - Schema PEI: 30 tabelas (planejamento estratégico completo)
   - 4 tabelas de Gestão de Riscos (novo módulo)
   - Tabela de auditoria (Laravel Auditing)

2. ✅ **26 Models Eloquent** criados com:
   - Relacionamentos completos tipados
   - Scopes úteis para queries
   - Métodos auxiliares de negócio
   - Trait Auditable nos models principais
   - Casts apropriados
   - Soft Deletes configurado

3. ✅ **Banco de dados** 100% estruturado e funcional
   - PostgreSQL com UUID primary keys
   - Schemas separados (PUBLIC e PEI)
   - Foreign keys com CASCADE
   - Seed de dados iniciais
   - Indexes para performance

4. ✅ **Laravel Auditing** instalado e configurado
   - Pacote `owen-it/laravel-auditing` instalado
   - Migration duplicada corrigida
   - Pronto para rastreamento de alterações

### Estimativa de Progresso Geral do Projeto

**Progresso Total:** ~8% concluído (2 de 25 tarefas do roadmap completo)

**Breakdown por fase:**
- FASE 0 (Fundação): 40% - Migrations e Models prontos, falta UI
- FASE 1 (Core Básico): 0% - Não iniciada
- FASE 2-5: 0% - Não iniciadas
- FASE 6 (Gestão Riscos): 30% - Migrations e Models prontos, falta UI
- FASE 7: 0% - Não iniciada

### Próxima IA a Continuar (Gemini/Codex)

**Deve começar por:** Concluir FASE 0 - Fundação

**Prioridades:**
1. Remover Tailwind CSS e instalar Bootstrap 5
2. Criar layout base (menu, sidebar, breadcrumbs)
3. Adaptar autenticação para validar campos legados (ativo, trocarsenha)
4. Criar tela de troca de senha obrigatória
5. Criar dashboard placeholder

**Arquivos importantes para revisar antes de iniciar:**
- `ai/novos_artefatos/04-MODELOS-ELOQUENT.md` - Especificação completa dos models
- `ai/novos_artefatos/05-COMPONENTES-LIVEWIRE.md` - Componentes a serem criados
- `ai/novos_artefatos/06-ROTAS-E-NAVEGACAO.md` - Estrutura de rotas
- `app/Models/` - Todos os models já criados e funcionais

---

**Versão do Roadmap:** 1.1
**Atualizado por:** Claude AI (Sonnet 4.5)
**Data:** 24/12/2025 20:35
