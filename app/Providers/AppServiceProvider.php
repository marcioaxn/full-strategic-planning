<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        // Register Policies
        Gate::policy(\App\Models\Organization::class, \App\Policies\OrganizationPolicy::class);
        Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);
        Gate::policy(\App\Models\PEI\PlanoDeAcao::class, \App\Policies\PlanoDeAcaoPolicy::class);
        Gate::policy(\App\Models\PEI\Indicador::class, \App\Policies\IndicadorPolicy::class);
        Gate::policy(\App\Models\Risco::class, \App\Policies\RiscoPolicy::class);

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
    }
}
