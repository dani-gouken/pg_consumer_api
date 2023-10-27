<?php

namespace Tests\Services\Payment;

use App\Models\Transaction;
use App\Services\Payment\TransactionResult;
use App\Services\Payment\TransactionServiceInterface;

class DummyTransactionService implements TransactionServiceInterface
{
    public function __construct(private TransactionResult $result)
    {
    }
    public function initiate(Transaction $transaction): TransactionResult
    {
        return $this->result;
    }
    public function checkStatus(Transaction $transaction): TransactionResult
    {
        return $this->result;
    }
}