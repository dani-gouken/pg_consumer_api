<?php
namespace App\Services\Payment;

use App\Models\Option;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServicePayment;
use App\Models\Transaction;

interface ServicePaymentProcessorInterface
{
    public function init(ServicePayment $servicePayment, ?int $amount = null): void;
    public function onCreditSuccess(Transaction $transaction): void;
    public function onDebitSuccess(Transaction $transaction): void;
    public function onCreditError(Transaction $transaction): void;
    public function onDebitError(Transaction $transaction): void;
    public function findSuitablePaymentServiceByDestination(string $destination): ?Service;
    
    /** @param array<Option> $options **/
    public function createServicePayment(
        Product $product,
        Service $service,
        Service $paymentService,
        string $debitDestination,
        string $creditDestination,
        array $options = [],
        string $customerName = "",
        string $notificationEmail = "",
        string $notificationPhoneNumber = "",
        ?int $amount = null,
    ): ServicePayment;
}