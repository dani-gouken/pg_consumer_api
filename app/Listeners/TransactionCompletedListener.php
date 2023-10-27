<?php

namespace App\Listeners;

use App\Events\TransactionCompleted;
use App\Models\Transaction;
use App\Services\Payment\ServicePaymentProcessorInterface;

class TransactionCompletedListener
{
    /**
     * Create the event listener.
     */
    public function __construct(private ServicePaymentProcessorInterface $service)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TransactionCompleted $event): void
    {
        $tx = $event->getTransaction();
        $tx->isCredit() ? $this->handleCreditTransaction($tx) : $this->handleDebitTransaction($tx);
    }

    public function handleCreditTransaction(Transaction $tx): void
    {
        \Log::info('handling credit transaction update', compact('tx'));
        if ($tx->status->error()) {
            $this->service->onCreditError($tx);
        }
        if ($tx->status->success()) {
            $this->service->onCreditSuccess($tx);
        }
    }

    public function handleDebitTransaction(Transaction $tx): void
    {
        \Log::info('handling debit transaction update', compact('tx'));
        if ($tx->status->error()) {
            $this->service->onDebitError($tx);
        }
        if ($tx->status->success()) {
            $this->service->onDebitSuccess($tx);
        }
    }
}