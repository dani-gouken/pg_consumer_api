<?php

namespace App\Models;

enum ServicePaymentStatusEnum: string
{
    case draft = 'draft';
    case creditPending = "creditPending";
    case creditError = "creditError";
    case initError = "initError";
    case success = "success";
    case debitPending = "debitPending";
    case debitError = "debitError";
    
    public function isInitializing(): bool
    {
        return self::draft == $this;
    }
    public function initialized(): bool
    {
        return !$this->isInitializing();
    }

    public function isAwaitingPayment(): bool
    {
        return self::creditPending == $this;
    }


    public function isError(): bool
    {
        return match ($this) {
            self::debitError, self::creditError => true,
            default => false,
        };
    }

    public function isPending(): bool
    {
        return match ($this) {
            self::debitPending, self::creditPending => true,
            default => false,
        };
    }
}