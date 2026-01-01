# 🤖 PROMPT PARA OUTRAS FERRAMENTAS DE IA
## Continuação do Projeto SEAE - Sistema de Planejamento Estratégico

**Versão:** 1.0
**Data de Criação:** 25/12/2025
**Preparado por:** Claude AI (Sonnet 4.5)
**Para:** Gemini, Codex, GPT-4, ou qualquer outra ferramenta de IA

---

## 📋 CONTEXTO GERAL

Você foi designado para continuar o desenvolvimento de um **Sistema de Planejamento Estratégico** para órgãos públicos brasileiros. Este sistema está sendo construído do **zero**, mas **100% dos dados já existem** em um banco de dados PostgreSQL legado em produção.

**Situação Atual:**
- Claude AI iniciou o projeto e completou ~8% do roadmap total
- **43 migrations** foram executadas com sucesso
- **26 Models Eloquent** foram criados e estão 100% funcionais
- **Bootstrap 5** está instalado e configurado (layout completo já existe)
- **Nenhum componente Livewire** foi criado ainda (esta é sua primeira prioridade)
- Banco de dados está 100% estruturado e pronto para uso

**Sua Missão:**
Continuar o desenvolvimento a partir do ponto onde Claude AI parou, seguindo RIGOROSAMENTE os padrões estabelecidos, criando componentes Livewire para as funcionalidades do sistema.

**⚠️ ATENÇÃO CRÍTICA:**
1. Este é um banco de dados LEGADO com dados REAIS em produção
2. **NUNCA** altere migrations existentes ou execute `migrate:fresh`/`refresh`
3. **SEMPRE** use Bootstrap 5 (NUNCA Tailwind)
4. **SEMPRE** siga os padrões de código estabelecidos por Claude AI

---

## 🎯 O QUE VOCÊ DEVE FAZER AGORA

### Objetivo Imediato: Concluir FASE 0 (Fundação)

**Status Atual da FASE 0:** 70% concluída

**O que já foi feito:**
- ✅ Laravel 12.38.1 instalado
- ✅ Jetstream com Livewire 3 instalado
- ✅ Bootstrap 5.3.3 configurado e compilando via Vite
- ✅ Layout principal completo (sidebar, topbar, dark mode, session timer)
- ✅ Dashboard moderno criado (com stats, cards, tabelas)
- ✅ 43 migrations executadas
- ✅ 26 models criados com relacionamentos, scopes, métodos auxiliares

**O que você precisa fazer:**

#### 1. Adaptar Autenticação para Campos Legados (PRIORIDADE MÁXIMA)

O sistema usa campos legados na tabela `users` que **DEVEM** ser validados:

**Campo `ativo` (boolean):**
- `true` = usuário pode logar
- `false` = usuário NÃO pode logar (negar acesso)

**Campo `trocarsenha` (integer):**
- `0` = usuário não precisa trocar senha
- `1` = usuário DEVE trocar senha obrigatoriamente (primeira vez ou após reset)
- `2` = usuário já trocou a senha

**Tarefas específicas:**

**1.1. Criar Middleware `CheckPasswordChange`**
- Localização: `app/Http/Middleware/CheckPasswordChange.php`
- Função: Verificar se `trocarsenha == 1` e redirecionar para tela de troca de senha
- Bloquear acesso a QUALQUER rota exceto `auth.trocar-senha` enquanto senha não for trocada
- Referência: Veja seção "4.2 Campo trocarsenha" no roadmap

**1.2. Criar Componente Livewire de Troca de Senha**
- Localização: `app/Http/Livewire/Auth/TrocarSenha.php`
- View: `resources/views/livewire/auth/trocar-senha.blade.php`
- Campos: senha atual, nova senha, confirmação
- Validação: senha atual deve estar correta, nova senha mínimo 8 caracteres
- Após troca bem-sucedida: atualizar `trocarsenha = 2`
- Layout: usar `layouts.guest` (sem sidebar)

**1.3. Validar Campo `ativo` no Login**
- Localização: Verificar onde o login é processado (provavelmente em Fortify ou componente Livewire do Jetstream)
- Adicionar validação: se `ativo == false`, negar login com mensagem apropriada

**1.4. Registrar Middleware**
- Arquivo: `app/Http/Kernel.php`
- Adicionar `CheckPasswordChange::class` ao grupo de middlewares `web`

**1.5. Criar Rota**
- Arquivo: `routes/web.php`
- Criar rota nomeada `auth.trocar-senha` que chama o componente `TrocarSenha`

#### 2. Adaptar Navegação do Sidebar

**Arquivo:** `resources/views/layouts/app.blade.php`

**O que fazer:**
- Localizar a variável `$appNavigation` (provavelmente definida em um Service Provider ou no próprio layout)
- Atualmente tem items genéricos (Home, Dashboard, Leads)
- Substituir por navegação de planejamento estratégico:

```php
$appNavigation = [
    [
        'label' => __('Dashboard'),
        'route' => 'dashboard',
        'icon' => 'speedometer2'
    ],
    [
        'label' => __('Organizações'),
        'route' => 'organizacoes.index',
        'icon' => 'building'
    ],
    [
        'label' => __('Usuários'),
        'route' => 'usuarios.index',
        'icon' => 'people'
    ],
    [
        'label' => __('PEI'),
        'route' => 'pei.index',
        'icon' => 'clipboard-data'
    ],
    [
        'label' => __('Objetivos Estratégicos'),
        'route' => 'objetivos.index',
        'icon' => 'bullseye'
    ],
    [
        'label' => __('Planos de Ação'),
        'route' => 'planos.index',
        'icon' => 'list-task'
    ],
    [
        'label' => __('Indicadores'),
        'route' => 'indicadores.index',
        'icon' => 'graph-up'
    ],
    [
        'label' => __('Riscos'),
        'route' => 'riscos.index',
        'icon' => 'exclamation-triangle'
    ],
];
```

**Nota:** As rotas ainda não existem (você criará na FASE 1), mas a estrutura do menu já estará pronta.

#### 3. Atualizar Dashboard para Contexto de Planejamento

**Arquivo:** `resources/views/dashboard.blade.php`

**O que fazer:**
- O dashboard atual é genérico (criado como demo)
- Adaptar os cards de estatísticas para métricas reais:
  - Total de Organizações
  - Total de Objetivos Estratégicos
  - Total de Planos de Ação
  - Total de Indicadores
  - Planos Atrasados (alerta)
  - Indicadores sem Lançamento Recente (alerta)
  - Riscos Críticos (alerta)

**Sugestão:** Criar um componente Livewire `Dashboard` que busque esses dados dos models.

#### 4. Atualizar Roadmap

Após completar cada tarefa, **SEMPRE** atualizar o arquivo `ai/novos_artefatos/07-ROADMAP-IMPLEMENTACAO.md`:
- Marcar tarefas como concluídas (✅)
- Atualizar percentuais de progresso
- Adicionar notas sobre o que foi feito
- Registrar onde você parou (para a próxima IA continuar)

---

## 📚 ESTRUTURA DO PROJETO

### Diretórios Importantes

```
D:\Apache24\htdocs\seae\
├── app/
│   ├── Http/
│   │   ├── Livewire/          # AQUI você criará componentes
│   │   │   ├── Auth/          # Autenticação (TrocarSenha vai aqui)
│   │   │   ├── Dashboard/     # Dashboards
│   │   │   ├── Organizacao/   # FASE 1 - CRUD de organizações
│   │   │   ├── Usuario/       # FASE 1 - CRUD de usuários
│   │   │   ├── PEI/           # FASE 2 - PEI, missão, visão, valores
│   │   │   ├── PlanoAcao/     # FASE 3 - Planos de ação
│   │   │   ├── Indicador/     # FASE 4 - Indicadores
│   │   │   └── Risco/         # FASE 6 - Gestão de riscos
│   │   ├── Middleware/        # CheckPasswordChange vai aqui
│   │   └── Controllers/       # EVITAR - preferir Livewire
│   ├── Models/
│   │   ├── PEI/               # Models do schema PEI (17 models)
│   │   ├── User.php           # ⚠️ TEM CAMPOS LEGADOS (ativo, trocarsenha)
│   │   ├── Organization.php   # Hierarquia organizacional
│   │   ├── PerfilAcesso.php   # 4 perfis com UUIDs constantes
│   │   └── Risco*.php         # 4 models de gestão de riscos
│   ├── Policies/              # Autorização - você criará aqui
│   └── Providers/
│       └── AuthServiceProvider.php  # Registrar policies aqui
├── database/
│   ├── migrations/            # ⚠️ NÃO MODIFICAR - 43 migrations já executadas
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   └── app.blade.php  # ⚠️ Layout principal - JÁ COMPLETO
│   │   ├── livewire/          # Views dos componentes que você criar
│   │   └── dashboard.blade.php # Adaptar para contexto
│   ├── js/
│   │   └── app.js             # ⚠️ Bootstrap + Alpine - JÁ CONFIGURADO
│   └── scss/
│       └── app.scss           # ⚠️ Estilos Bootstrap - JÁ CONFIGURADO
├── routes/
│   └── web.php                # Adicionar rotas aqui
└── ai/
    ├── novos_artefatos/
    │   ├── 04-MODELOS-ELOQUENT.md         # ⚠️ LEIA ISTO - Especificação dos 26 models
    │   ├── 05-COMPONENTES-LIVEWIRE.md     # Lista de componentes a criar
    │   ├── 06-ROTAS-E-NAVEGACAO.md        # Estrutura de rotas
    │   └── 07-ROADMAP-IMPLEMENTACAO.md    # ⚠️ LEIA ISTO - Roadmap completo com diretrizes
    └── PROMPT-PARA-OUTRAS-IAS.md          # Este arquivo
```

---

## 🗄️ BANCO DE DADOS

### Informações Críticas

**Tipo:** PostgreSQL 16+
**Schemas:** `PUBLIC` e `pei`
**Primary Keys:** TODAS as tabelas usam UUID (não auto-incremento)
**Status:** 100% estruturado e pronto (43 migrations executadas)

### Convenção de Nomenclatura

```sql
-- Primary Key sempre: cod_<nome_tabela>
pei.tab_plano_de_acao.cod_plano (UUID, PK)

-- Foreign Key sempre: cod_<tabela_relacionada>
pei.tab_plano_de_acao.cod_objetivo_estrategico (UUID, FK)
pei.tab_plano_de_acao.cod_organizacao (UUID, FK)
pei.tab_plano_de_acao.cod_pei (UUID, FK)
```

### Tabelas Principais

**Schema PUBLIC (usuários e organizações):**
- `users` - Usuários do sistema (⚠️ campos legados: `ativo`, `trocarsenha`)
- `tab_organizacao` - Hierarquia de organizações
- `tab_perfil_acesso` - 4 perfis (Super Admin, Admin Unidade, Gestor Responsável, Gestor Substituto)
- `rel_users_tab_organizacoes_tab_perfil_acesso` - Tabela pivot (usuário x organização x perfil)

**Schema PEI (planejamento estratégico):**
- `tab_pei` - PEI (Plano Estratégico Institucional)
- `tab_missao_visao_valores` - Identidade estratégica
- `tab_valores` - Valores organizacionais
- `tab_perspectiva` - 4 perspectivas do BSC
- `tab_objetivo_estrategico` - Objetivos estratégicos
- `tab_plano_de_acao` - Planos de ação
- `tab_indicador` - Indicadores (KPIs)
- `tab_evolucao_indicador` - Evolução mensal dos indicadores
- `tab_risco` - Riscos estratégicos (NOVO - criado por Claude)
- `tab_risco_mitigacao` - Planos de mitigação (NOVO)
- `tab_risco_ocorrencia` - Ocorrências de riscos (NOVO)

**⚠️ NUNCA:**
- Executar `php artisan migrate:fresh` ou `migrate:refresh`
- Modificar migrations já executadas
- Criar migrations para tabelas que já existem

**✅ SEMPRE:**
- Usar `php artisan migrate` (apenas para novas migrations)
- Consultar os Models Eloquent existentes
- Preservar 100% de compatibilidade com dados legados

---

## 🏗️ STACK TECNOLÓGICO

### Backend
- **Laravel 12.38.1** (PHP 8.3+)
- **PostgreSQL 16+** (com extensão uuid-ossp)
- **Redis** (cache e sessões)

### Frontend
- **Livewire 3.6.4** (componentes reativos full-stack) - **PRINCIPAL**
- **Alpine.js 3.x** (JavaScript mínimo)
- **Bootstrap 5.3.3** (framework CSS) - **NUNCA use Tailwind!**
- **Bootstrap Icons 1.11+**
- **Vite 5.x** (build tool)

### Packages Instalados
- Laravel Jetstream (autenticação)
- Laravel Fortify (autenticação backend)
- Laravel Sanctum (API tokens)
- owen-it/laravel-auditing (auditoria de mudanças)

---

## 📖 PADRÕES DE CÓDIGO (OBRIGATÓRIOS!)

### 1. Models Eloquent

**Todos os 26 models já foram criados por Claude AI.**

**Localização:**
- `app/Models/` - Models do schema PUBLIC
- `app/Models/PEI/` - Models do schema PEI

**Estrutura padrão (SEMPRE seguir):**

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

    public function organizacao()
    {
        return $this->belongsTo(Organization::class, 'cod_organizacao', 'cod_organizacao');
    }

    // === SCOPES ===

    public function scopeAtivos($query)
    {
        return $query->whereNotIn('dsc_status', ['Cancelado', 'Concluído']);
    }

    // === MÉTODOS AUXILIARES ===

    public function isAtrasado()
    {
        return $this->dte_fim < now() && $this->dsc_status !== 'Concluído';
    }
}
```

**Traits OBRIGATÓRIOS:**
- `HasUuids` - Para PKs UUID
- `SoftDeletes` - Para exclusão lógica
- `\OwenIt\Auditing\Auditable` - Para auditoria

### 2. Componentes Livewire

**Criar componente:**
```bash
php artisan make:livewire Auth/TrocarSenha
# Cria: app/Http/Livewire/Auth/TrocarSenha.php
# Cria: resources/views/livewire/auth/trocar-senha.blade.php
```

**Estrutura padrão:**

```php
<?php

namespace App\Http\Livewire\Auth;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class TrocarSenha extends Component
{
    // Propriedades públicas (vinculadas ao front-end)
    public $senhaAtual;
    public $novaSenha;
    public $novaSenhaConfirmacao;

    // Regras de validação
    protected $rules = [
        'senhaAtual' => 'required',
        'novaSenha' => 'required|min:8|confirmed',
    ];

    // Mensagens personalizadas
    protected $messages = [
        'senhaAtual.required' => 'A senha atual é obrigatória.',
        'novaSenha.required' => 'A nova senha é obrigatória.',
        'novaSenha.min' => 'A nova senha deve ter no mínimo 8 caracteres.',
        'novaSenha.confirmed' => 'As senhas não conferem.',
    ];

    // Método público (chamado do front-end)
    public function trocarSenha()
    {
        $this->validate();

        $user = auth()->user();

        // Validar senha atual
        if (!Hash::check($this->senhaAtual, $user->password)) {
            $this->addError('senhaAtual', 'Senha atual incorreta.');
            return;
        }

        // Atualizar
        $user->update([
            'password' => Hash::make($this->novaSenha),
            'trocarsenha' => 2, // ⚠️ IMPORTANTE - marcar como trocada
        ]);

        // Feedback
        session()->flash('mensagem', 'Senha alterada com sucesso!');
        session()->flash('tipo', 'success');

        // Redirecionar
        return redirect()->route('dashboard');
    }

    // Render
    public function render()
    {
        return view('livewire.auth.trocar-senha')
            ->layout('layouts.guest'); // Layout sem sidebar
    }
}
```

**View correspondente:**

```blade
<div>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Troca de Senha Obrigatória</h4>
        </div>
        <div class="card-body">
            <p class="text-muted mb-4">
                Por segurança, você precisa alterar sua senha antes de continuar.
            </p>

            <form wire:submit.prevent="trocarSenha">
                <div class="mb-3">
                    <label for="senhaAtual" class="form-label">Senha Atual</label>
                    <input type="password"
                           class="form-control @error('senhaAtual') is-invalid @enderror"
                           id="senhaAtual"
                           wire:model="senhaAtual">
                    @error('senhaAtual')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="novaSenha" class="form-label">Nova Senha</label>
                    <input type="password"
                           class="form-control @error('novaSenha') is-invalid @enderror"
                           id="novaSenha"
                           wire:model="novaSenha">
                    @error('novaSenha')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <div class="form-text">Mínimo 8 caracteres</div>
                </div>

                <div class="mb-3">
                    <label for="novaSenhaConfirmacao" class="form-label">Confirmar Nova Senha</label>
                    <input type="password"
                           class="form-control"
                           id="novaSenhaConfirmacao"
                           wire:model="novaSenhaConfirmacao">
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Alterar Senha
                </button>
            </form>
        </div>
    </div>
</div>
```

### 3. Autorização (Policies)

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

**Registrar em `app/Providers/AuthServiceProvider.php`:**

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
    $this->authorize('delete', $plano); // ⚠️ Autorização

    $plano->delete();

    session()->flash('mensagem', 'Plano excluído!');
}
```

### 4. UI com Bootstrap 5

**⚠️ NUNCA use Tailwind CSS! Sempre Bootstrap 5!**

**Botões:**
```html
<button class="btn btn-primary">Primário</button>
<button class="btn btn-outline-secondary">Secundário</button>
```

**Cards:**
```html
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">Título</div>
    <div class="card-body">Conteúdo</div>
    <div class="card-footer">Rodapé</div>
</div>
```

**Tabelas:**
```html
<div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead class="table-light">
            <tr>
                <th>Coluna 1</th>
                <th>Coluna 2</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Dado 1</td>
                <td>Dado 2</td>
            </tr>
        </tbody>
    </table>
</div>
```

**Formulários:**
```html
<div class="mb-3">
    <label for="campo" class="form-label">Label</label>
    <input type="text" class="form-control" id="campo" wire:model="campo">
    @error('campo')
        <span class="invalid-feedback d-block">{{ $message }}</span>
    @enderror
</div>
```

**Badges:**
```html
<span class="badge bg-success">Ativo</span>
<span class="badge bg-danger">Crítico</span>
<span class="badge bg-warning text-dark">Alerta</span>
```

### 5. Nomenclatura (PSR-12)

**Classes:**
```php
class PlanoDeAcao extends Model {}      // PascalCase
class ListarPlanos extends Component {} // PascalCase
```

**Métodos:**
```php
public function calcularPercentual() {} // camelCase
public function isAtrasado() {}         // camelCase
```

**Variáveis:**
```php
$planoDeAcao = PlanoDeAcao::find($id);  // camelCase
$percentual = 50;                        // camelCase
```

**Constantes:**
```php
const SUPER_ADMIN = 'uuid...';           // SCREAMING_SNAKE_CASE
const STATUS_ATIVO = 'Ativo';           // SCREAMING_SNAKE_CASE
```

---

## 🚨 ERROS CRÍTICOS A EVITAR

### ❌ NUNCA FAÇA ISSO:

1. **Alterar migrations executadas:**
   ```bash
   # ❌ PROIBIDO
   php artisan migrate:fresh
   php artisan migrate:refresh
   ```

2. **Usar Tailwind CSS:**
   ```html
   <!-- ❌ ERRADO -->
   <div class="flex items-center bg-blue-500">

   <!-- ✅ CORRETO -->
   <div class="d-flex align-items-center bg-primary">
   ```

3. **Esquecer validação de campos legados:**
   ```php
   // ❌ ERRADO - Login sem validar ativo/trocarsenha
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

4. **Criar controllers ao invés de Livewire:**
   ```php
   // ❌ EVITAR
   class PlanoController extends Controller {}

   // ✅ PREFERIR
   class ListarPlanos extends Component {}
   ```

5. **Esquecer Eager Loading (problema N+1):**
   ```php
   // ❌ RUIM - N+1 queries
   $planos = PlanoDeAcao::all();
   foreach ($planos as $plano) {
       echo $plano->objetivo->nome; // Nova query!
   }

   // ✅ BOM
   $planos = PlanoDeAcao::with('objetivo', 'organizacao')->get();
   foreach ($planos as $plano) {
       echo $plano->objetivo->nome; // Sem nova query
   }
   ```

---

## 📝 COMMITS

**Formato (Conventional Commits):**

```
tipo(escopo): descrição curta

Descrição detalhada (opcional)

🤖 Generated with [Sua IA] <email>

Co-Authored-By: [Seu Nome/IA] <email>
```

**Tipos:**
- `feat:` Nova funcionalidade
- `fix:` Correção de bug
- `refactor:` Refatoração
- `docs:` Documentação
- `test:` Testes
- `chore:` Manutenção

**Exemplos:**

```
feat(auth): implementar troca de senha obrigatória

Criado middleware CheckPasswordChange e componente TrocarSenha.
Valida campo trocarsenha e força redirecionamento quando necessário.

🤖 Generated with Gemini Pro

Co-Authored-By: Gemini Pro <gemini@google.com>
```

```
feat(navigation): adaptar sidebar para planejamento estratégico

Substituído menu genérico por navegação específica do sistema.
Incluídos links para: Organizações, PEI, Objetivos, Planos, Indicadores, Riscos.

🤖 Generated with GitHub Codex

Co-Authored-By: GitHub Codex <codex@github.com>
```

---

## ✅ CHECKLIST ANTES DE COMMITAR

Antes de fazer **QUALQUER** commit, verificar:

- [ ] Código segue PSR-12
- [ ] Models usam `HasUuids`, `SoftDeletes`, `Auditable` quando apropriado
- [ ] Componentes Livewire têm validação adequada
- [ ] Policies de autorização implementadas (se aplicável)
- [ ] Eager loading usado (evitar N+1)
- [ ] UI usa Bootstrap 5 (NUNCA Tailwind)
- [ ] Nomes seguem convenções (camelCase, PascalCase, etc.)
- [ ] Sem `dd()`, `dump()`, `var_dump()` no código
- [ ] Mensagens de sucesso/erro implementadas
- [ ] Código comentado em partes complexas
- [ ] Testes executados: `php artisan test`
- [ ] Assets compilados: `npm run build`
- [ ] Commit message segue Conventional Commits
- [ ] Roadmap atualizado com progresso

---

## 📚 DOCUMENTAÇÃO DE REFERÊNCIA

### Obrigatória (Leia antes de começar):

1. **`ai/novos_artefatos/07-ROADMAP-IMPLEMENTACAO.md`**
   - Roadmap completo do projeto
   - Diretrizes completas para outras IAs (seção extensa)
   - Status atual de cada fase
   - Próximos passos sugeridos

2. **`ai/novos_artefatos/04-MODELOS-ELOQUENT.md`**
   - Especificação completa dos 26 models
   - Relacionamentos, scopes, métodos auxiliares
   - Campos de cada tabela

3. **`ai/novos_artefatos/05-COMPONENTES-LIVEWIRE.md`**
   - Lista de todos os componentes a serem criados
   - Especificação de cada componente

4. **`ai/novos_artefatos/06-ROTAS-E-NAVEGACAO.md`**
   - Estrutura de rotas do sistema
   - Navegação e breadcrumbs

### Documentação Oficial:

- Laravel 12: https://laravel.com/docs/12.x
- Livewire 3: https://livewire.laravel.com/docs/3.x
- Bootstrap 5.3: https://getbootstrap.com/docs/5.3
- Alpine.js 3: https://alpinejs.dev
- Laravel Auditing: https://laravel-auditing.com/

### Models Existentes:

Explore os models em:
- `app/Models/` - Models do schema PUBLIC
- `app/Models/PEI/` - Models do schema PEI

Todos estão 100% completos e prontos para uso!

---

## 🎓 EXEMPLO COMPLETO: Criando um Componente Livewire

Vou mostrar um exemplo completo de como criar um componente do zero, seguindo todos os padrões.

**Tarefa:** Criar listagem de Organizações

### 1. Criar o Componente

```bash
php artisan make:livewire Organizacao/ListarOrganizacoes
```

### 2. Código do Componente

**`app/Http/Livewire/Organizacao/ListarOrganizacoes.php`:**

```php
<?php

namespace App\Http\Livewire\Organizacao;

use App\Models\Organization;
use Livewire\Component;
use Livewire\WithPagination;

class ListarOrganizacoes extends Component
{
    use WithPagination;

    public $busca = '';
    public $incluirExcluidas = false;

    protected $queryString = ['busca'];

    protected $listeners = ['organizacaoSalva' => '$refresh'];

    public function updatingBusca()
    {
        $this->resetPage();
    }

    public function excluir($id)
    {
        $organizacao = Organization::findOrFail($id);

        // Autorização
        $this->authorize('delete', $organizacao);

        // Soft delete
        $organizacao->delete();

        session()->flash('mensagem', 'Organização excluída com sucesso!');
        session()->flash('tipo', 'success');
    }

    public function restaurar($id)
    {
        $organizacao = Organization::withTrashed()->findOrFail($id);

        $this->authorize('restore', $organizacao);

        $organizacao->restore();

        session()->flash('mensagem', 'Organização restaurada com sucesso!');
        session()->flash('tipo', 'success');
    }

    public function render()
    {
        $query = Organization::query();

        // Incluir excluídas se solicitado
        if ($this->incluirExcluidas) {
            $query->withTrashed();
        }

        // Busca
        if ($this->busca) {
            $query->where(function ($q) {
                $q->where('dsc_organizacao', 'ilike', "%{$this->busca}%")
                  ->orWhere('sgl_organizacao', 'ilike', "%{$this->busca}%");
            });
        }

        // Eager loading (evitar N+1)
        $query->with('organizacaoPai');

        $organizacoes = $query->orderBy('dsc_organizacao')->paginate(15);

        return view('livewire.organizacao.listar-organizacoes', [
            'organizacoes' => $organizacoes,
        ]);
    }
}
```

### 3. View do Componente

**`resources/views/livewire/organizacao/listar-organizacoes.blade.php`:**

```blade
<div>
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Organizações</h2>
        @can('create', App\Models\Organization::class)
            <button class="btn btn-primary" wire:click="$emit('abrirModalCriar')">
                <i class="bi bi-plus-lg"></i> Nova Organização
            </button>
        @endcan
    </div>

    {{-- Alertas --}}
    @if (session()->has('mensagem'))
        <div class="alert alert-{{ session('tipo') }} alert-dismissible fade show" role="alert">
            {{ session('mensagem') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <input type="text"
                           class="form-control"
                           placeholder="Buscar por nome ou sigla..."
                           wire:model.debounce.300ms="busca">
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input"
                               type="checkbox"
                               id="incluirExcluidas"
                               wire:model="incluirExcluidas">
                        <label class="form-check-label" for="incluirExcluidas">
                            Incluir excluídas
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Sigla</th>
                        <th>Nome</th>
                        <th>Organização Pai</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organizacoes as $org)
                        <tr class="{{ $org->trashed() ? 'table-secondary' : '' }}">
                            <td><strong>{{ $org->sgl_organizacao }}</strong></td>
                            <td>{{ $org->dsc_organizacao }}</td>
                            <td>
                                @if($org->organizacaoPai)
                                    {{ $org->organizacaoPai->sgl_organizacao }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($org->trashed())
                                    <span class="badge bg-secondary">Excluída</span>
                                @else
                                    <span class="badge bg-success">Ativa</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($org->trashed())
                                    @can('restore', $org)
                                        <button class="btn btn-sm btn-outline-success"
                                                wire:click="restaurar('{{ $org->cod_organizacao }}')"
                                                wire:confirm="Confirma restauração?">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    @endcan
                                @else
                                    @can('update', $org)
                                        <button class="btn btn-sm btn-outline-primary"
                                                wire:click="$emit('editar', '{{ $org->cod_organizacao }}')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endcan

                                    @can('delete', $org)
                                        <button class="btn btn-sm btn-outline-danger"
                                                wire:click="excluir('{{ $org->cod_organizacao }}')"
                                                wire:confirm="Confirma exclusão?">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Nenhuma organização encontrada
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginação --}}
    <div class="mt-3">
        {{ $organizacoes->links() }}
    </div>
</div>
```

### 4. Criar Rota

**`routes/web.php`:**

```php
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/organizacoes', App\Http\Livewire\Organizacao\ListarOrganizacoes::class)
        ->name('organizacoes.index');
});
```

### 5. Testar

```bash
# Compilar assets
npm run build

# Acessar no navegador
# http://localhost/organizacoes
```

---

## 🎯 RESUMO: O QUE FAZER AGORA

1. **Leia a documentação:**
   - `ai/novos_artefatos/07-ROADMAP-IMPLEMENTACAO.md` (seção "DIRETRIZES COMPLETAS")
   - `ai/novos_artefatos/04-MODELOS-ELOQUENT.md`

2. **Execute as tarefas da FASE 0:**
   - Criar middleware `CheckPasswordChange`
   - Criar componente Livewire `TrocarSenha`
   - Validar campo `ativo` no login
   - Adaptar navegação do sidebar
   - Atualizar dashboard

3. **Sempre:**
   - Seguir padrões de código estabelecidos
   - Usar Bootstrap 5 (NUNCA Tailwind)
   - Validar com Policies
   - Usar Eager Loading
   - Atualizar roadmap após cada tarefa

4. **Nunca:**
   - Alterar migrations executadas
   - Executar `migrate:fresh` ou `migrate:refresh`
   - Usar Tailwind CSS
   - Esquecer validação de `ativo` e `trocarsenha`

---

## 🆘 PRECISA DE AJUDA?

Se tiver dúvidas durante o desenvolvimento:

1. **Consulte os models existentes** em `app/Models/`
2. **Leia o roadmap** em `ai/novos_artefatos/07-ROADMAP-IMPLEMENTACAO.md`
3. **Veja a especificação dos models** em `ai/novos_artefatos/04-MODELOS-ELOQUENT.md`
4. **Consulte a documentação oficial** do Laravel 12 e Livewire 3

---

## 🎉 BOA SORTE!

Você tem todas as ferramentas necessárias para continuar este projeto com excelência. Claude AI construiu uma base sólida - agora é sua vez de expandir sobre ela!

**Lembre-se:**
- Qualidade > Velocidade
- Sempre siga os padrões
- Sempre atualize o roadmap
- Sempre teste antes de commitar

**Você consegue!** 💪

---

**Preparado por:** Claude AI (Sonnet 4.5)
**Data:** 25/12/2025
**Versão:** 1.0

**Feedback para Claude AI:**
Quando você (outra IA) terminar suas tarefas, atualize este arquivo com:
- O que você fez
- Onde você parou
- Próximos passos sugeridos
- Problemas encontrados (se houver)

Isso garantirá continuidade para a próxima IA que assumir o projeto.

---

**FIM DO PROMPT**
