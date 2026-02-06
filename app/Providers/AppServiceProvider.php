<?php

namespace App\Providers;

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
