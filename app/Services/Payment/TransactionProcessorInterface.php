<?php
namespace App\Services\Payment;

use App\Models\Option;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionKind;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

interface TransactionProcessorInterface
{
    public function createTransaction(
        Product $product,
        string $destination,
        TransactionKind $kind,
        int $amount = null,
    ): Transaction;

    public function process(Transaction $transaction): Transaction;

    public function checkStatus(Transaction $transaction): Transaction;
    public function handleCallback(Request $request): Response;
}