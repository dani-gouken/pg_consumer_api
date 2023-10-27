<?php

namespace App\Services\Payment;

use App\Services\Payment\Status;

readonly class TransactionResult {
    public function __construct(
        public Status $status,
        public String $externalReference = "",
        public string $error = "",
        public string $providerError = "",
    ){
    }
}