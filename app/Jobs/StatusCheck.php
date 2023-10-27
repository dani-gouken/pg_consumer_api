<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Services\Payment\TransactionProcessorInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StatusCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Transaction $tx)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(TransactionProcessorInterface $processor): void
    {
        $processor->checkStatus($this->tx);
    }

    public function getTransaction(): Transaction
    {
        return $this->tx;
    }
}