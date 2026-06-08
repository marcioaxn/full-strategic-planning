<?php

namespace App\Providers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Models\ActionPlan\Entrega;
use App\Observers\EntregaObserver;

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
        'migrate:install',
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

        \Illuminate\Support\Facades\Schema::defaultStringLength(191);
    
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
        Gate::policy(\App\Models\Organization::class, \App\Policies\OrganizationPolicy::class);
        Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);
        Gate::policy(\App\Models\ActionPlan\PlanoDeAcao::class, \App\Policies\PlanoDeAcaoPolicy::class);
        Gate::policy(\App\Models\PerformanceIndicators\Indicador::class, \App\Policies\IndicadorPolicy::class);
        Gate::policy(\App\Models\RiskManagement\Risco::class, \App\Policies\RiscoPolicy::class);

        // Fix URL generation for subfolder deployment
        $appUrl = config('app.url');
        if ($appUrl) {
            URL::forceRootUrl($appUrl);
        }

        // Configure Livewire update endpoint for subfolder deployment
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)
                ->middleware('web')
                ->name('livewire.update');
        });

        // Custom Blade Directives for Brazilian Formatting
        \Illuminate\Support\Facades\Blade::directive('brazil_number', function ($expression) {
            return "<?php echo number_format($expression, ',', '.'); ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('brazil_percent', function ($expression) {
            return "<?php echo number_format($expression, ',', '.') . '%'; ?>";
        });
    }
}
