<?php

namespace App\Providers;

use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\Organization;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\RiskManagement\Risco;
use App\Models\User;
use App\Observers\EntregaObserver;
use App\Policies\EntregaPolicy;
use App\Policies\IndicadorPolicy;
use App\Policies\OrganizationPolicy;
use App\Policies\PlanoDeAcaoPolicy;
use App\Policies\RiscoPolicy;
use App\Policies\UserPolicy;
use App\Services\Authorization\CapacidadeResolver;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Comandos de migrate que exigem que os schemas já existam antes de rodar.
     * O evento CommandStarting dispara antes de qualquer handle(), portanto
     * antes de o Migrator tentar criar a tabela "migrations".
     */
    private const MIGRATE_COMMANDS = [
        'migrate',
        'migrate:fresh',
        'migrate:refresh',
        'migrate:reset',
        'migrate:rollback',
        'migrate:install',
        'migrate:status',
        'migrate:run',
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auto-criação dos schemas PostgreSQL antes de qualquer comando migrate.
        // Resolve o erro "no schema has been selected to create in" que ocorre
        // no primeiro migrate de um projeto recém-clonado, quando o schema "pei"
        // (e os demais do search_path) ainda não existem no banco.
        Event::listen(CommandStarting::class, function (CommandStarting $event) {
            if (! in_array($event->command, self::MIGRATE_COMMANDS, true)) {
                return;
            }

            $connection = config('database.default');

            if (config("database.connections.{$connection}.driver") !== 'pgsql') {
                return;
            }

            $schemas = config("database.connections.{$connection}.search_path", []);

            foreach ((array) $schemas as $schema) {
                try {
                    DB::connection($connection)->statement("CREATE SCHEMA IF NOT EXISTS \"{$schema}\"");
                } catch (\Throwable) {
                    // Silencia erros de permissão ou conexão; o migrate dará
                    // mensagem de erro mais clara se o banco for inacessível.
                }
            }
        });

        Schema::defaultStringLength(191);

        $this->loadMigrationsFrom([
            database_path('migrations'),
            database_path('migrations/Organization'),
            database_path('migrations/ActionPlan'),
            database_path('migrations/StrategicPlanning'),
            database_path('migrations/PerformanceIndicators'),
            database_path('migrations/RiskManagement'),
        ]);

        // Register Observers for automatic indicator calculation
        Entrega::observe(EntregaObserver::class);

        // Register Policies
        Gate::policy(Organization::class, OrganizationPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(PlanoDeAcao::class, PlanoDeAcaoPolicy::class);
        Gate::policy(Indicador::class, IndicadorPolicy::class);
        Gate::policy(Risco::class, RiscoPolicy::class);
        Gate::policy(Entrega::class, EntregaPolicy::class);

        $this->registrarGatesDeAutorizacao();

        // Fix URL generation for subfolder deployment.
        // Nunca em testes: TestCase::get()/post() constroem a URI da requisição
        // via url(), que herdaria essa raiz forçada e quebraria o roteamento
        // (ex.: "/login" viraria "https://.../fs-v1/public/login", sem rota).
        $appUrl = config('app.url');
        if ($appUrl && ! $this->app->runningUnitTests()) {
            URL::forceRootUrl($appUrl);
        }

        // Configure Livewire update endpoint for subfolder deployment
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)
                ->middleware('web')
                ->name('livewire.update');
        });

        // Custom Blade Directives for Brazilian Formatting
        Blade::directive('brazil_number', function ($expression) {
            return "<?php echo number_format($expression, ',', '.'); ?>";
        });

        Blade::directive('brazil_percent', function ($expression) {
            return "<?php echo number_format($expression, ',', '.') . '%'; ?>";
        });
    }

    /**
     * Controle de Acesso: RBAC (Gates "modulo.*", fonte única no banco via
     * CapacidadeResolver) combinado com ABAC (hooks globais de estado do
     * usuário e auditoria de negação).
     *
     * Princípio inquebrável: a Session não é fonte de permissão. Toda
     * checagem de capacidade deriva de CapacidadeResolver::podeNoModulo(),
     * resolvido a partir do perfil vinculado ao usuário no banco.
     */
    private function registrarGatesDeAutorizacao(): void
    {
        foreach (['acessar', 'ver-sensivel', 'criar', 'editar', 'excluir', 'exportar'] as $ability) {
            Gate::define("modulo.{$ability}", function (User $user, string $nomPath) use ($ability): bool {
                return CapacidadeResolver::podeNoModulo($user, $nomPath, $ability);
            });
        }

        // ABAC — estado do usuário: veto total para conta inativa, antes de
        // qualquer outra checagem. Retornar null (não true/false) para os
        // demais casos deixa a decisão para os Gates/Policies normais.
        Gate::before(function (User $user, string $ability) {
            return $user->isAtivo() ? null : false;
        });

        // ABAC — auditoria: toda negação de acesso é registrada no canal
        // dedicado "auditoria", com rastro de quem/quando/o quê, sem
        // vazar o conteúdo do model envolvido (só classe + chave primária).
        Gate::after(function (User $user, string $ability, ?bool $result, array $arguments) {
            if ($result !== false) {
                return;
            }

            Log::channel('auditoria')->info('Acesso negado', [
                'user_id' => $user->id,
                'ability' => $ability,
                'arguments' => array_map(
                    fn ($argumento) => $argumento instanceof Model
                        ? [get_class($argumento), $argumento->getKey()]
                        : $argumento,
                    $arguments
                ),
            ]);
        });
    }
}
