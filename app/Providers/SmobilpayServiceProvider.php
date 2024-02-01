<?php

namespace App\Providers;

use App\Services\Smobilpay\SmobilpayScrapingService;
use App\Services\Smobilpay\SmobilpayService;
use Illuminate\Support\ServiceProvider;

class SmobilpayServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmobilpayScrapingService::class, fn() => new SmobilpayScrapingService(
            username: config("smobilpay.scraping.username"),
            password: config("smobilpay.scraping.password"),
            baseUrl: config("smobilpay.scraping.baseUrl"),
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
