# ESTRUTURA DE PASTAS DO PROJETO
## Sistema de Planejamento Estratégico

**Versão:** 1.0
**Data:** 23/12/2025

---

## ESTRUTURA COMPLETA DO PROJETO

```
seae/
├── app/
│   ├── Console/
│   │   ├── Commands/
│   │   │   ├── CalcularAtingimentoIndicadores.php
│   │   │   ├── EnviarNotificacoesPendencias.php
│   │   │   └── GerarBackupDiario.php
│   │   └── Kernel.php
│   ├── Exceptions/
│   │   └── Handler.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   ├── DashboardController.php
│   │   │   ├── RelatorioController.php
│   │   │   └── ExportController.php
│   │   ├── Livewire/
│   │   │   ├── Dashboard.php
│   │   │   ├── Auth/
│   │   │   │   ├── Login.php
│   │   │   │   ├── ForgotPassword.php
│   │   │   │   ├── ResetPassword.php
│   │   │   │   └── TrocarSenha.php
│   │   │   ├── Organizacao/
│   │   │   │   ├── ListarOrganizacoes.php
│   │   │   │   ├── FormOrganizacao.php
│   │   │   │   └── SeletorOrganizacao.php
│   │   │   ├── Usuario/
│   │   │   │   ├── ListarUsuarios.php
│   │   │   │   ├── FormUsuario.php
│   │   │   │   └── PerfilUsuario.php
│   │   │   ├── PEI/
│   │   │   │   ├── Listarphp
│   │   │   │   └── Formphp
│   │   │   ├── Identidade/
│   │   │   │   ├── MissaoVisao.php
│   │   │   │   ├── ListarValores.php
│   │   │   │   └── FormValor.php
│   │   │   ├── BSC/
│   │   │   │   ├── ListarPerspectivas.php
│   │   │   │   ├── ListarObjetivos.php
│   │   │   │   ├── FormObjetivo.php
│   │   │   │   └── DetalheObjetivo.php
│   │   │   ├── PlanoAcao/
│   │   │   │   ├── ListarPlanos.php
│   │   │   │   ├── FormPlano.php
│   │   │   │   ├── DetalhePlano.php
│   │   │   │   ├── GerenciarEntregas.php
│   │   │   │   └── AtribuirResponsavel.php
│   │   │   ├── Indicador/
│   │   │   │   ├── ListarIndicadores.php
│   │   │   │   ├── FormIndicador.php
│   │   │   │   ├── DetalheIndicador.php
│   │   │   │   ├── LancarEvolucao.php
│   │   │   │   ├── GerenciarMetas.php
│   │   │   │   └── AnexarArquivo.php
│   │   │   ├── CadeiaValor/
│   │   │   │   ├── ListarAtividades.php
│   │   │   │   ├── FormAtividade.php
│   │   │   │   └── GerenciarProcessos.php
│   │   │   ├── Dashboard/
│   │   │   │   ├── DashboardPrincipal.php
│   │   │   │   ├── DashboardObjetivos.php
│   │   │   │   ├── DashboardIndicadores.php
│   │   │   │   └── MapaEstrategico.php
│   │   │   ├── Relatorio/
│   │   │   │   ├── RelatorioIdentidade.php
│   │   │   │   ├── RelatorioObjetivos.php
│   │   │   │   └── RelatorioIndicadores.php
│   │   │   ├── Auditoria/
│   │   │   │   ├── ListarLogs.php
│   │   │   │   └── DetalhesAuditoria.php
│   │   │   ├── Shared/
│   │   │   │   ├── Datatable.php
│   │   │   │   ├── Modal.php
│   │   │   │   ├── SeletorPeriodo.php
│   │   │   │   ├── GraficoLinha.php
│   │   │   │   └── CardKPI.php
│   │   │   └── Traits/
│   │   │       ├── WithNotification.php
│   │   │       ├── WithAuthorization.php
│   │   │       └── WithExport.php
│   │   ├── Middleware/
│   │   │   ├── Authenticate.php
│   │   │   ├── CheckSuperAdmin.php
│   │   │   ├── CheckOrganizationAccess.php
│   │   │   └── VerifyTrocarSenha.php
│   │   └── Kernel.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Organization.php
│   │   ├── PerfilAcesso.php
│   │   ├── Acao.php
│   │   ├── TabAudit.php
│   │   ├── TabStatus.php
│   │   ├── PEI/
│   │   │   ├── php
│   │   │   ├── MissaoVisaoValores.php
│   │   │   ├── Valor.php
│   │   │   ├── FuturoAlmejadoObjetivoEstrategico.php
│   │   │   ├── Perspectiva.php
│   │   │   ├── ObjetivoEstrategico.php
│   │   │   ├── TipoExecucao.php
│   │   │   ├── PlanoDeAcao.php
│   │   │   ├── Entrega.php
│   │   │   ├── Indicador.php
│   │   │   ├── EvolucaoIndicador.php
│   │   │   ├── LinhaBaseIndicador.php
│   │   │   ├── MetaPorAno.php
│   │   │   ├── GrauSatisfacao.php
│   │   │   ├── Arquivo.php
│   │   │   ├── AtividadeCadeiaValor.php
│   │   │   └── ProcessoAtividadeCadeiaValor.php
│   │   └── Scopes/
│   │       └── OrganizacaoScope.php
│   ├── Observers/
│   │   ├── PlanoDeAcaoObserver.php
│   │   └── IndicadorObserver.php
│   ├── Policies/
│   │   ├── OrganizationPolicy.php
│   │   ├── UserPolicy.php
│   │   ├── PlanoDeAcaoPolicy.php
│   │   └── IndicadorPolicy.php
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── AuthServiceProvider.php
│   │   ├── EventServiceProvider.php
│   │   ├── FortifyServiceProvider.php
│   │   └── JetstreamServiceProvider.php
│   ├── Services/
│   │   ├── CalculoIndicadorService.php
│   │   ├── RelatorioService.php
│   │   ├── ExportService.php
│   │   └── NotificacaoService.php
│   └── View/
│       └── Components/
│           ├── AppLayout.php
│           ├── GuestLayout.php
│           └── Icons/
├── bootstrap/
│   ├── app.php
│   └── cache/
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   ├── filesystems.php
│   ├── fortify.php
│   ├── jetstream.php
│   ├── livewire.php
│   └── ...
├── database/
│   ├── factories/
│   │   └── UserFactory.php
│   ├── migrations/
│   │   ├── 2014_08_09_230616_create_organizacaos_table.php
│   │   ├── 2014_10_11_080128_create_tab_perfil_acesso_table.php
│   │   ├── 2014_10_12_000000_create_users_table.php
│   │   ├── ... (todas as migrations do banco legado)
│   │   └── ... (novas migrations se necessário)
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── PerfilAcessoSeeder.php
│       └── TipoExecucaoSeeder.php
├── public/
│   ├── index.php
│   ├── css/
│   ├── js/
│   ├── images/
│   └── storage -> ../storage/app/public
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   ├── app.js
│   │   ├── bootstrap.js
│   │   └── components/
│   │       ├── chart-radar.js
│   │       ├── chart-line.js
│   │       └── toast.js
│   ├── lang/
│   │   └── pt_BR/
│   │       ├── auth.php
│   │       ├── validation.php
│   │       └── messages.php
│   └── views/
│       ├── components/
│       │   ├── app-layout.blade.php
│       │   ├── guest-layout.blade.php
│       │   ├── nav-link.blade.php
│       │   └── icons/
│       ├── livewire/
│       │   ├── dashboard.blade.php
│       │   ├── auth/
│       │   ├── organizacao/
│       │   ├── usuario/
│       │   ├── pei/
│       │   ├── identidade/
│       │   ├── bsc/
│       │   ├── plano-acao/
│       │   ├── indicador/
│       │   ├── cadeia-valor/
│       │   ├── dashboard/
│       │   ├── relatorio/
│       │   ├── auditoria/
│       │   └── shared/
│       ├── layouts/
│       │   ├── app.blade.php
│       │   └── guest.blade.php
│       ├── errors/
│       │   ├── 404.blade.php
│       │   ├── 403.blade.php
│       │   └── 500.blade.php
│       └── vendor/
│           └── livewire/
├── routes/
│   ├── web.php
│   ├── api.php (se necessário)
│   └── console.php
├── storage/
│   ├── app/
│   │   ├── public/
│   │   │   └── pei/
│   │   │       ├── evidencias/
│   │   │       └── relatorios/
│   │   └── private/
│   ├── framework/
│   │   ├── cache/
│   │   ├── sessions/
│   │   └── views/
│   └── logs/
│       └── laravel.log
├── tests/
│   ├── Feature/
│   │   ├── Auth/
│   │   │   └── LoginTest.php
│   │   ├── Organizacao/
│   │   │   └── OrganizacaoTest.php
│   │   └── Indicador/
│   │       └── IndicadorTest.php
│   └── Unit/
│       ├── Models/
│       │   ├── UserTest.php
│       │   └── IndicadorTest.php
│       └── Services/
│           └── CalculoIndicadorServiceTest.php
├── vendor/
├── .env
├── .env.example
├── .gitignore
├── artisan
├── composer.json
├── composer.lock
├── package.json
├── package-lock.json
├── phpunit.xml
├── README.md
└── vite.config.js
```

---

## DETALHAMENTO DAS PRINCIPAIS PASTAS

### app/Http/Livewire/

Todos os componentes Livewire do sistema, organizados por módulo.

**Convenções:**
- Cada módulo tem sua própria subpasta
- Componentes de listagem: `Listar*.php`
- Componentes de formulário: `Form*.php`
- Componentes de detalhes: `Detalhe*.php`
- Componentes compartilhados em `Shared/`

### app/Models/

Todos os models Eloquent.

**Organização:**
- Models do schema `public` ficam na raiz
- Models do schema `pei` ficam em `Models/PEI/`
- Scopes globais em `Models/Scopes/`

### app/Services/

Lógica de negócio complexa que não pertence a Models ou Controllers.

**Exemplos:**
- `CalculoIndicadorService`: Cálculos de atingimento, médias, projeções
- `RelatorioService`: Geração de relatórios complexos
- `ExportService`: Exportação de dados (PDF, Excel)
- `NotificacaoService`: Envio de notificações e alertas

### app/Policies/

Políticas de autorização (Laravel Policies).

**Exemplo:**
```php
// app/Policies/PlanoDeAcaoPolicy.php
public function update(User $user, PlanoDeAcao $plano): bool
{
    // Super Admin pode tudo
    if ($user->isSuperAdmin()) {
        return true;
    }

    // Admin Unidade pode editar planos da sua unidade
    if ($user->temPermissaoOrganizacao($plano->organizacao)) {
        return true;
    }

    // Gestor pode editar seus próprios planos
    return $user->isGestorResponsavel($plano->cod_plano_de_acao) ||
           $user->isGestorSubstituto($plano->cod_plano_de_acao);
}
```

### resources/views/livewire/

Views Blade para cada componente Livewire, espelhando a estrutura de `app/Http/Livewire/`.

### storage/app/public/pei/

Armazenamento de arquivos públicos do sistema.

**Subpastas:**
- `evidencias/`: Arquivos de evidência anexados às evoluções de indicadores
- `relatorios/`: PDFs gerados temporariamente

---

## PADRÕES DE NOMENCLATURA

### Arquivos PHP

| Tipo | Padrão | Exemplo |
|------|--------|---------|
| **Model** | PascalCase (singular) | `PlanoDeAcao.php` |
| **Controller** | PascalCase + Controller | `DashboardController.php` |
| **Livewire** | PascalCase | `ListarPlanos.php` |
| **Service** | PascalCase + Service | `CalculoIndicadorService.php` |
| **Policy** | PascalCase + Policy | `PlanoDeAcaoPolicy.php` |
| **Observer** | PascalCase + Observer | `PlanoDeAcaoObserver.php` |
| **Middleware** | PascalCase | `CheckSuperAdmin.php` |
| **Command** | PascalCase | `CalcularAtingimentoIndicadores.php` |
| **Test** | PascalCase + Test | `IndicadorTest.php` |

### Views Blade

| Tipo | Padrão | Exemplo |
|------|--------|---------|
| **Livewire** | kebab-case | `listar-planos.blade.php` |
| **Layout** | kebab-case | `app-layout.blade.php` |
| **Componente** | kebab-case | `card-kpi.blade.php` |

### Rotas

| Tipo | Padrão | Exemplo |
|------|--------|---------|
| **Resource** | plural, kebab-case | `planos-acao` |
| **Single** | kebab-case | `dashboard` |
| **Ação** | verbo-objeto | `lancar-evolucao` |

**Exemplo:**
```php
// routes/web.php
Route::get('/dashboard', Dashboard::class)->name('dashboard');
Route::get('/planos-acao', ListarPlanos::class)->name('planos-acao.index');
Route::get('/indicadores/{id}/lancar-evolucao', LancarEvolucao::class)->name('indicadores.lancar-evolucao');
```

---

## CONFIGURAÇÕES IMPORTANTES

### .env (exemplo)

```env
APP_NAME="Sistema de Planejamento Estratégico"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://planejamento.exemplo.gov.br

LOG_CHANNEL=stack
LOG_LEVEL=info

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=seae_db
DB_USERNAME=seae_user
DB_PASSWORD=senha_segura

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.exemplo.gov.br
MAIL_PORT=587
MAIL_USERNAME=noreply@exemplo.gov.br
MAIL_PASSWORD=senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@exemplo.gov.br"
MAIL_FROM_NAME="${APP_NAME}"
```

### composer.json (dependências principais)

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0",
        "laravel/jetstream": "^5.0",
        "livewire/livewire": "^3.0",
        "owen-it/laravel-auditing": "^13.0",
        "spatie/laravel-backup": "^8.0",
        "barryvdh/laravel-dompdf": "^2.0",
        "maatwebsite/excel": "^3.1"
    },
    "require-dev": {
        "phpunit/phpunit": "^11.0",
        "laravel/dusk": "^8.0",
        "laravel/pint": "^1.0",
        "nunomaduro/larastan": "^2.0"
    }
}
```

### package.json (dependências frontend)

```json
{
    "devDependencies": {
        "@popperjs/core": "^2.11.6",
        "autoprefixer": "^10.4.12",
        "bootstrap": "^5.3.0",
        "chart.js": "^4.0.0",
        "postcss": "^8.4.18",
        "vite": "^5.0.0",
        "laravel-vite-plugin": "^1.0.0"
    },
    "dependencies": {
        "alpinejs": "^3.13.0",
        "axios": "^1.6.0"
    }
}
```

---

## PRÓXIMOS PASSOS

1. **Clonar repositório base**
2. **Configurar .env**
3. **Instalar dependências:** `composer install && npm install`
4. **Conectar ao banco legado**
5. **Rodar migrations novas** (se houver)
6. **Build de assets:** `npm run build`
7. **Iniciar desenvolvimento** seguindo o roadmap

---

**Próximo Documento:** 07-ROADMAP-IMPLEMENTACAO.md
