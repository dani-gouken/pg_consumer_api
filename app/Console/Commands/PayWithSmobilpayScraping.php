<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Transaction;
use App\Services\Payment\Status;
use App\Services\Smobilpay\SmobilpayScrapingService;
use Illuminate\Console\Command;

class PayWithSmobilpayScraping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pay-with-smobilpay-scraping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(SmobilpayScrapingService $smobilpayService): void
    {
        $start = microtime(true);
        $product = Product::where("name", "Crédit Blue/Camtel")->first();

        $transaction = new Transaction;

        $transaction->amount = 100;
        $transaction->product()->associate($product);
        $transaction->destination = "650675795";
        $transaction->status = Status::PENDING;

        $result = $smobilpayService->initiate($transaction);
        $end = microtime(true);
        $this->line("Duration: " . round($end - $start, 2) . " Seconds");
        $this->line(json_encode($result));
    }
}
