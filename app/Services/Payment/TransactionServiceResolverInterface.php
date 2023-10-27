<?php
namespace App\Services\Payment;

interface TransactionServiceResolverInterface {
    public function resolve(string $providerName): TransactionServiceInterface;
}