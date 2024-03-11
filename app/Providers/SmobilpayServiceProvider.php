<?php

namespace App\Providers;

use App\Services\Smobilpay\SmobilpayScrapingService;
use App\Services\Smobilpay\SmobilpayService;
use Illuminate\Support\ServiceProvider;
use Maviance\S3PApiClient\ApiClient;
use Maviance\S3PApiClient\Configuration;
use Maviance\S3PApiClient\Service\ConfirmApi;
use Maviance\S3PApiClient\Service\InitiateApi;
use Maviance\S3PApiClient\Service\VerifyApi;

class SmobilpayServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            SmobilpayScrapingService::class,
            fn() => new SmobilpayScrapingService(
                username: config("smobilpay.scraping.username"),
                password: config("smobilpay.scraping.password"),
                baseUrl: config("smobilpay.scraping.baseUrl"),
                cachePrefix: env("APP_SMOBILPAY_SERVICE_CACHE_PREFIX", "app")
            )
        );

        $this->app->bind(Configuration::class, function () {
            $config = new Configuration();
            $config->setHost(config('smobilpay.api.url'));
            return $config;
        });
        $this->app->bind(SmobilpayService::class, function () {
            return new SmobilpayService(
                initiateApi: app(InitiateApi::class),
                confirmApi: app(ConfirmApi::class),
                verifyApi: app(VerifyApi::class),
                paymentEmail: config('smobilpay.api.payment_email'),
            );
        });

        $this->app->bind(ApiClient::class, function () {
            return new ApiClient(config('smobilpay.api.token'), config('smobilpay.api.secret'), ['verify' => true]);
        });
        $this->app->bind(Configuration::class, function () {
            $config = new Configuration();
            $config->setHost(config('smobilpay.api.url'));
            return $config;
        });
        $this->app->bind(InitiateApi::class, function () {
            return new InitiateApi(
                app(ApiClient::class),
                app(Configuration::class)
            );
        });
        $this->app->bind(ConfirmApi::class, function () {
            return new ConfirmApi(
                app(ApiClient::class),
                app(Configuration::class)
            );
        });
        $this->app->bind(VerifyApi::class, function () {
            return new VerifyApi(
                app(ApiClient::class),
                app(Configuration::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
