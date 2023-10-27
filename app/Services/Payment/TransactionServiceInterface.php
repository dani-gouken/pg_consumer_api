<?php
namespace App\Services\Payment;
use App\Models\Transaction;

interface TransactionServiceInterface {
    public function initiate(Transaction $transaction): TransactionResult;
    public function checkStatus(Transaction $transaction): TransactionResult;
}