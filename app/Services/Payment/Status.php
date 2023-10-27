<?php

namespace App\Services\Payment;

enum Status: string
{
    case SUCCESS = "SUCCESS";
    case ERROR = "ERROR";
    case PENDING = "PENDING";

    public function isFinal(): bool
    {
        return $this != Status::PENDING;
    }

    public function error(): bool
    {
        return $this === Status::ERROR;
    }

    public function success(): bool
    {
        return $this === Status::SUCCESS;
    }

    public function pending(): bool
    {
        return $this === Status::PENDING;
    }
}