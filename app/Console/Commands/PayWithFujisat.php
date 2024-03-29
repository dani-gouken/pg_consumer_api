<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Transaction;
use App\Services\Payment\Status;
use FujisatService;
use Illuminate\Console\Command;

class PayWithFujisat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pay-with-fujisat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\FujisatService $fujisatService): void
    {
        $start = microtime(true);
        $product = Product::where("name", "Canal+")->first();

        $transaction = new Transaction;

        $transaction->amount = 100;
        $transaction->product()->associate($product);
        $transaction->destination = "696163373";
        $transaction->status = Status::PENDING;

        $result = $fujisatService->initiate($transaction);
        $end = microtime(true);
        $this->line("Duration: " . round($end - $start, 2) . " Seconds");
        $this->line(json_encode($result));
    }
}
