# Arquitetura do Projeto - Laravel Jetstream Livewire Bootstrap

## ⚠️ LEIA ANTES DE QUALQUER TROUBLESHOOTING

**OBRIGATÓRIO:** Antes de resolver qualquer problema neste projeto, leia:
- [METODOLOGIA-CIENTIFICA.md](./METODOLOGIA-CIENTIFICA.md) - Método científico obrigatório
- [COMANDOS-DIAGNOSTICO.md](./COMANDOS-DIAGNOSTICO.md) - Comandos para diagnóstico

**Regra de ouro:** Compare com código funcional PRIMEIRO, modifique DEPOIS.

---

## 1. Visão Geral

| Item | Valor |
|------|-------|
| **Framework** | Laravel 12.x |
| **PHP** | 8.2+ |
| **Autenticação** | Laravel Jetstream 5.3 + Fortify |
| **Frontend Reativo** | Livewire 3.6.4 |
| **CSS Framework** | Bootstrap 5.3.3 |
| **API Auth** | Laravel Sanctum 4.0 |
| **Build Tool** | Vite 7.0.7 |

---

## 2. Estrutura de Diretórios

```
starter-kit-laravel-jetstream-livewire-bootstrap/
├── app/
│   ├── Actions/                    # Classes de ação (Fortify & Jetstream)
│   │   ├── Fortify/
│   │   │   ├── CreateNewUser.php
│   │   │   ├── UpdateUserProfileInformation.php
│   │   │   ├── UpdateUserPassword.php
│   │   │   ├── ResetUserPassword.php
│   │   │   └── PasswordValidationRules.php (trait)
│   │   └── Jetstream/
│   │       └── DeleteUser.php
│   ├── Http/Controllers/           # Controllers HTTP
│   ├── Livewire/
│   │   └── LeadsTable.php          # Componente CRUD de Leads
│   ├── Models/
│   │   ├── User.php                # Model com UUID
│   │   └── Lead.php                # Model de CRM
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── JetstreamServiceProvider.php
│   │   └── FortifyServiceProvider.php
│   └── View/Components/
│       ├── AppLayout.php
│       └── GuestLayout.php
│
├── database/
│   ├── migrations/                 # 6 arquivos de migração
│   ├── factories/
│   │   ├── UserFactory.php
│   │   └── LeadFactory.php
│   └── seeders/
│       └── DatabaseSeeder.php
│
├── resources/
│   ├── scss/
│   │   └── app.scss                # Bootstrap customizado
│   ├── js/
│   │   ├── app.js                  # Tema, sidebar, tooltips
│   │   └── bootstrap.js            # Axios + Bootstrap
│   └── views/
│       ├── layouts/                # Layouts principais
│       ├── auth/                   # 7 views de autenticação
│       ├── profile/                # 6 views de perfil
│       ├── components/             # 32 componentes Blade
│       ├── livewire/               # Views dos componentes Livewire
│       ├── dashboard.blade.php
│       └── welcome.blade.php
│
├── routes/
│   ├── web.php                     # Rotas web
│   └── api.php                     # Rotas API
│
└── config/                         # Arquivos de configuração
```

---

## 3. Dependências

### PHP (composer.json)
```json
{
  "laravel/framework": "^12.0",
  "laravel/jetstream": "^5.3",
  "laravel/sanctum": "^4.0",
  "livewire/livewire": "^3.6.4",
  "spatie/laravel-html": "^3.12"
}
```

### JavaScript (package.json)
```json
{
  "bootstrap": "^5.3.3",
  "bootstrap-icons": "^1.11.3",
  "alpinejs": "^3.15.2",
  "@alpinejs/mask": "^3.15.2",
  "@alpinejs/focus": "^3.15.2",
  "axios": "^1.11.0",
  "sortablejs": "^1.15.6"
}
```

---

## 4. Banco de Dados

### Tabela Users (UUID)
| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | UUID | Chave primária |
| name | string | Nome do usuário |
| email | string | Email único |
| password | string | Senha hash |
| two_factor_secret | text | Segredo 2FA |
| two_factor_recovery_codes | text | Códigos de recuperação |
| profile_photo_path | string | Caminho da foto |
| timestamps | - | created_at, updated_at |

### Tabela Leads
| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint | Chave primária auto |
| name | string | Nome obrigatório |
| entity | string | Departamento/equipe |
| status | string(30) | Status (new, in_progress, won, lost) |
| email | string | Email do lead |
| phone | string(30) | Telefone |
| notes | text | Observações |
| timestamps | - | created_at, updated_at |

### Status do Lead (constantes)
```php
const STATUSES = [
    'new' => 'Novo',
    'in_progress' => 'Em andamento',
    'won' => 'Ganho',
    'lost' => 'Perdido',
];
```

---

## 5. Autenticação

### Stack de Autenticação
- **Login**: Email + senha via Fortify
- **2FA**: TOTP com códigos de recuperação
- **Reset de senha**: Via email
- **Deleção de conta**: Com limpeza de dados
- **Sessões**: Gerenciamento multi-dispositivo

### Configurações Jetstream (config/jetstream.php)
- Stack: `livewire`
- Guard: `sanctum`
- Features habilitadas: `accountDeletion()`
- Features desabilitadas: teams, API tokens, profile photos

### Middlewares de Proteção
```php
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Rotas protegidas
});
```

---

## 6. Frontend

### Assets (Vite)
- **SCSS**: `resources/scss/app.scss` (Bootstrap customizado)
- **JS**: `resources/js/app.js` (tema, sidebar, Alpine.js)

### Funcionalidades JavaScript
- Alternância de tema (Light/Dark/System)
- Estado do sidebar (colapsado/expandido)
- Tooltips Bootstrap
- Toast notifications
- Integração Livewire

### Componentes Blade (32 componentes)
| Categoria | Componentes |
|-----------|-------------|
| Layout | app-layout, guest-layout |
| Form | form-section, input, label, checkbox, input-error |
| Buttons | button, secondary-button, danger-button, action-button |
| Modal | modal, dialog-modal, confirmation-modal, confirms-password |
| UI | banner, alert, toast, dropdown, nav-link |
| Data | data-table, section-border, section-title |

---

## 7. Rotas

### Rotas Web (routes/web.php)
| Método | URI | Nome | Descrição |
|--------|-----|------|-----------|
| GET | / | - | Página inicial |
| GET | /dashboard | dashboard | Dashboard (auth) |
| GET | /leads | leads.index | CRUD de Leads (auth) |

### Rotas Automáticas (Fortify/Jetstream)
- `/login`, `/register`, `/forgot-password`, `/reset-password`
- `/two-factor-challenge`, `/confirm-password`
- `/profile`, `/user/api-tokens`

### Rotas API (routes/api.php)
```php
Route::get('/user', ...)->middleware('auth:sanctum');
```

---

## 8. Padrões de Design

### Action Pattern
Classes de ação para lógica de negócio:
- `CreateNewUser` - Criação de usuário
- `UpdateUserProfileInformation` - Atualização de perfil
- `UpdateUserPassword` - Alteração de senha
- `ResetUserPassword` - Reset de senha
- `DeleteUser` - Deleção com limpeza

### Livewire Components
Componentes reativos com:
- Propriedades reativas
- Validação em tempo real
- Query string state
- Paginação Bootstrap

### Service Providers
- `JetstreamServiceProvider` - Configuração Jetstream
- `FortifyServiceProvider` - Rate limiting, handlers
- `AppServiceProvider` - Serviços customizados

---

## 9. Implementações Customizadas

### Sistema de Leads (app/Livewire/LeadsTable.php)
- Busca multi-coluna (database-agnostic)
- Filtro por status
- Paginação com Bootstrap
- Export CSV streaming
- Modais de criação/edição/deleção

### Busca Database-Agnostic
```php
if ($driver === 'pgsql') {
    // ILIKE para PostgreSQL
} else {
    // LOWER() + LIKE para MySQL/SQLite
}
```

### Sistema de Temas
- Light/Dark/System modes
- Persistência em localStorage
- Detecção de preferência do sistema
- Atributo `data-bs-theme` no Bootstrap

### UUIDs para Users
```php
DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto";');
$table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
```

---

## 10. Seeder Padrão

### Usuário Admin
- **Email**: `user_adm@user_adm.com`
- **Senha**: `1352@765@1452`

### Dados de Demonstração
- 25 registros de Lead gerados via factory

---

## 11. Extensibilidade

O projeto está preparado para adicionar:
- Novos componentes Livewire CRUD
- Endpoints API com Sanctum
- Sistema de Teams (já scaffolded)
- Jobs em background (queue system)
- Webhooks e notificações
- Suporte multi-database

---

## 12. Arquivos Principais

| Arquivo | Localização | Descrição |
|---------|-------------|-----------|
| User Model | `app/Models/User.php` | Model com UUID e traits Jetstream |
| Lead Model | `app/Models/Lead.php` | Model CRM com accessors |
| LeadsTable | `app/Livewire/LeadsTable.php` | Componente CRUD completo |
| Layout Principal | `resources/views/layouts/app.blade.php` | Layout autenticado |
| Configuração Jetstream | `config/jetstream.php` | Features e stack |
| Configuração Fortify | `config/fortify.php` | Autenticação |

---

*Documento gerado para análise de arquitetura do projeto.*
