<?php

namespace App\Providers;

use App\Services\Payment\ServicePaymentProcessor;
use App\Services\Payment\ServicePaymentProcessorInterface;
use App\Services\Payment\TransactionProcessor;
use App\Services\Payment\TransactionProcessorInterface;
use App\Services\Payment\TransactionServiceResolver;
use App\Services\Payment\TransactionServiceResolverInterface;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TransactionServiceResolverInterface::class, fn() => new TransactionServiceResolver());
        $this->app->bind(TransactionProcessorInterface::class, fn(Container $container) => new TransactionProcessor(
            resolver: $container->get(TransactionServiceResolverInterface::class),
            delayBetweenStatusCheck: config("payment.status_check.delay", 10),
            maximumStatusCheck: config("payment.status_check.max", 60),
        ));
        $this->app->bind(ServicePaymentProcessorInterface::class, ServicePaymentProcessor::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}