<?php

namespace App\Providers;

use App\Services\Smobilpay\SmobilpayService;
use Illuminate\Support\ServiceProvider;

class SmobilpayServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmobilpayService::class, fn() => new SmobilpayService(
            username: config("smobilpay.username"),
            password: config("smobilpay.password"),
            baseUrl: config("smobilpay.baseUrl"),
            cachePrefix: env("APP_SMOBILPAY_SERVICE_CACHE_PREFIX", "app")
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
