<?php

namespace App\Providers;

use FujisatService;
use Illuminate\Support\ServiceProvider;

class FujisatServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            FujisatService::class,
            fn() => new FujisatService(
                config('fujisat.base_url'),
                config('fujisat.username'),
                config('fujisat.password'),
            )
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
