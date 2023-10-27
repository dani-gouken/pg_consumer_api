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
use Illuminate\Queue\Worker;
use Log;
class ProcessTransaction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Transaction $transaction)
    {

    }

    /**
     * Execute the job.
     */
    public function handle(TransactionProcessorInterface $processor): void
    {
        Log::info("Processing transaction", ["tx" => $this->transaction]);
        $processor->process($this->transaction);
        Log::info("Transaction processed", ["tx" => $this->transaction]);
    }
}