<?php

namespace App\Services\Payment;

use App\Services\PaymentGatewayService;
use App\Services\Smobilpay\SmobilpayService;

class TransactionServiceResolver implements TransactionServiceResolverInterface
{
    public function resolve(string $name): TransactionServiceInterface
    {
        return match ($name) {
            "smobilpay" => app()->get(SmobilpayService::class),
            "pg" => app()->get(PaymentGatewayService::class),
            default => throw new \Exception("failed to resolve service [$name]"),
        };
    }
}