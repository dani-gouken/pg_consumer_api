<?php
namespace App\Services\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;

interface HandlesCallback {
    public function isValidCallback(Request $request): bool;
    public function getCallbackTransaction(Request $request): ?Transaction;
    public function getCallbackResult(Request $request): ?TransactionResult;
}