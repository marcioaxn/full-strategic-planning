<?php

namespace App\Providers;

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
