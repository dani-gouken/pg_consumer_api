<?php

namespace App\Services\Payment;

use App\Events\TransactionCompleted;
use App\Jobs\StatusCheck;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionKind;
use App\Services\Payment\Exceptions\TransactionInitFailureException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use Ramsey\Uuid\Uuid;

class TransactionProcessor implements TransactionProcessorInterface
{

    public function __construct(
        private TransactionServiceResolverInterface $resolver,
        private int $delayBetweenStatusCheck = 10,
        private int $maximumStatusCheck = 25,
    ) {

    }

    public function createTransaction(
        Product $product,
        string $destination,
        TransactionKind $kind,
        ?int $amount = null,
    ): Transaction {
        $transaction = new Transaction;
        $transaction->uuid = Uuid::uuid4()->toString();
        $transaction->product_id = $product->id;
        $transaction->service_id = $product->service_id;
        $transaction->status_check_count = 0;
        $transaction->kind = $kind->value;
        $transaction->max_status_check = $this->maximumStatusCheck;
        if (!$product->fixed_price && is_null($amount)) {
            throw new TransactionInitFailureException("Amount is required for a product without a fixed price");
        }
        $service = $product->service;
        $transaction->amount = $product->fixed_price ? $product->price : $amount;
        if ($service->min_amount && ($transaction->amount < $service->min_amount)) {
            throw new TransactionInitFailureException("Minimum amount allowed by the service is [{$service->min_amount}");
        }
        if ($service->max_amount && ($transaction->amount > $service->max_amount)) {
            throw new TransactionInitFailureException("Maximum amount allowed by the service is [{$service->min_amount}");
        }
        $transaction->destination = $destination;
        $transaction->status = Status::PENDING;
        return $transaction;
    }



    public function process(Transaction $tx): Transaction
    {
        try {
            $provider = $this->getProviderByTx($tx);
            $result = $provider->initiate($tx);
            $tx = $this->handlePaymentResult($tx, $result);
            Log::info("payment initiated", ["result" => $result, "tx" => $tx]);
            return $tx;
        } catch (\throwable $ex) {
            Log::error("failed to initiate payment", [
                "message" => $ex->getMessage(),
                "class" => $ex::class,
                "trace" => $ex->getTraceAsString()
            ]);
            return $this->handlePaymentResult(
                $tx,
                new TransactionResult(
                    Status::ERROR,
                    "",
                    "Payment initiation failed"
                )
            );
        }

    }

    public function checkStatus(Transaction $tx): Transaction
    {
        try {
            Log::info("Checking payment status", ["tx" => $tx]);
            if ($tx->expired()) {
                Log::error("Status check failed. payment timeout", [
                    "tx" => $tx
                ]);
                $this->handlePaymentResult($tx, new TransactionResult(Status::ERROR, "", "Payment timeout"), isStatusCheck: true);
                return $tx;
            }
            if ($tx->status->isFinal()) {
                Log::error("Status check cancelled. Transaction is in a final status", [
                    "tx" => $tx
                ]);
                return $tx;
            }
            $provider = $this->getProviderByTx($tx);
            $result = $provider->checkStatus($tx);
            $tx = $this->handlePaymentResult($tx, $result, isStatusCheck: true);
            Log::info("payment checked", ["result" => $result, "tx" => $tx]);
            return $tx;
        } catch (\throwable $ex) {
            Log::error("failed to check payment status", [
                "message" => $ex->getMessage(),
                "class" => $ex::class,
                "trace" => $ex->getTraceAsString()
            ]);
            $this->handlePaymentResult($tx, new TransactionResult(
                Status::PENDING,
                "",
                "Status verification failed"
            ), isStatusCheck: true);
            return $tx;
        }
    }


    protected function handlePaymentResult(Transaction $transaction, TransactionResult $result, bool $isStatusCheck = false): Transaction
    {
        if (!empty($result->externalReference)) {
            $transaction->external_reference = $result->externalReference;
        }
        $tx = match ($result->status) {
            Status::PENDING => $transaction->pending($isStatusCheck),
            Status::ERROR => $transaction->error($result->error, $result->providerError),
            Status::SUCCESS => $transaction->success(),
        };
        $tx->save();
        if ($tx->status->pending()) {
            dispatch(new StatusCheck($tx))->delay($this->delayBetweenStatusCheck)
                ->onQueue('status');
        } else if ($tx->status->isFinal()) {
            event(new TransactionCompleted($tx));
        }
        return $tx;
    }

    protected function getProviderByTx(Transaction $tx): TransactionServiceInterface
    {
        $service = $tx->service;
        $provider = $this->resolver->resolve(
            $service->provider
        );
        return $provider;
    }

    public function handleCallback(Request $request): Response
    {
        Log::warning("callback: request received", [
            "body" => $request->getContent(),
            "headers" => $request->headers->all()
        ]);
        $nothing = response("");
        $serviceName = $request->query->getString('__service');
        if (empty($serviceName)) {
            Log::info('callback: service not found in request');
            return $nothing;
        }
        try {
            $service = $this->resolver->resolve($serviceName);
        } catch (\Exception $e) {
            Log::warning("callback: service not found", ["message" => $e->getMessage(), "serviceName" => $serviceName]);
            return $nothing;
        }
        if (!($service instanceof HandlesCallback)) {
            Log::warning("callback: tx service does not support callback", ["service" => $service]);
        }
        /** @var HandlesCallback|TransactionServiceInterface $service */
        if (!$service->isValidCallback($request)) {
            Log::warning("callback: request not valid");
            return $nothing;
        }
        $tx = $service->getCallbackTransaction($request);
        if (is_null($tx)) {
            Log::warning("callback: transaction not found");
            return $nothing;
        }
        $result = $service->getCallbackResult($request);
        if (is_null($result)) {
            Log::warning("callback: unable to retrieve tx result");
            return $nothing;
        }
        if(!$result->status->isFinal()) {
            Log::warning("callback: non-final status received");
            return $nothing;
        }
        $this->handlePaymentResult($tx, $result,isStatusCheck: true);
        return $nothing;
    }

}