<?php

namespace App\Services\Payment;

use App\Services\PaymentGatewayService;
use App\Services\Smobilpay\SmobilpayScrapingService;

class TransactionServiceResolver implements TransactionServiceResolverInterface
{
    public function resolve(string $name): TransactionServiceInterface
    {
        return match ($name) {
            "smobilpay_scraping" => app()->get(SmobilpayScrapingService::class),
            "pg" => app()->get(PaymentGatewayService::class),
            default => throw new \Exception("failed to resolve service [$name]"),
        };
    }
}