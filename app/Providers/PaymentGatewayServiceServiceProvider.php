<?php

namespace App\Providers;

use App\Services\PaymentGatewayService;
use Illuminate\Support\ServiceProvider;

class PaymentGatewayServiceServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayService::class, fn() => new PaymentGatewayService(
            baseUrl: config("pg.baseUrl"),
            publicKey: config("pg.publicKey"),
            privatekey: config("pg.privateKey"),
            hash: config("pg.hash"),
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
