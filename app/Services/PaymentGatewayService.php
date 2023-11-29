<?php
namespace App\Services;

use App\Models\ServiceKindEnum;
use App\Models\Transaction;
use App\Services\Payment\HandlesCallback;
use App\Services\Payment\Status;
use App\Services\Payment\TransactionResult;
use App\Services\Payment\TransactionServiceInterface;
use Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService implements TransactionServiceInterface, HandlesCallback
{
    public function __construct(
        protected string $baseUrl,
        protected string $publicKey,
        protected string $privatekey,
        protected string $hash,
    ) {
    }

    protected function makeRequest()
    {
        return Http::withOptions([
            "base_uri" => $this->baseUrl,
            "headers" => [
                "Content-Type" => "application/json",
                "Accept" => "application/json",
                'Authorization' => "Bearer {$this->privatekey}"
            ]
        ]);
    }
    public function initiate(Transaction $transaction): TransactionResult
    {
        $payload =  [
            "publicKey" => $this->publicKey,
            "amount" => $transaction->amount,
            "reference" => $transaction->uuid,
            "country" => "cm",
            "recipient" => "237{$transaction->destination}",
            "channel" => $transaction->product->provider_id_1,
            "type" => $transaction->service->kind == ServiceKindEnum::payment ? "cash_collect" : "payout"
        ];
        if(!empty($transaction->product->provider_id_2)) {
            $payload["service"] = $transaction->product->provider_id_2 ?? ""; 
        }
        $initiatedTx = $this->makeRequest()->post("/api/payment",$payload);
        if ($initiatedTx->failed()) {
            Log::error("failed to initiate a payment", $initiatedTx->json());
            return new TransactionResult(
                Status::ERROR,
                '',
                'failed to initiate the payment',
                $initiatedTx->status()
            );
        }
        $initStatusCode = $initiatedTx->status();
        $initiatedTx = $initiatedTx->json();
        Log::info("Tx initiated", $initiatedTx);

        $uuid = $initiatedTx["data"]['transaction']['uuid'] ?? '';
        $status = $initiatedTx["data"]['transaction']['status'] ?? '';
        if (empty($uuid) || ($status != "INITIATED")) {
            Log::error("failed to initiate a payment, missing uuid or invalid status", $initiatedTx);
            return new TransactionResult(
                Status::ERROR,
                '',
                'failed to initiate the payment, missing uuid or invalid status',
                $initStatusCode
            );
        }

        $executedTx = $this->makeRequest()->put("/api/payment/{$uuid}", [
            "publicKey" => $this->publicKey,
            "schema_type" => $transaction->service->kind == ServiceKindEnum::payment ? "CM_MOBILE_MONEY_SCHEMA" : "PHONE_NUMBER",
            "schema" => [
                "phoneNumber" => "237{$transaction->destination}"
            ]
        ]);

        if ($executedTx->failed()) {
            Log::error("Failed to execute the payment", $executedTx->json());
            return new TransactionResult(
                Status::ERROR,
                $uuid,
                'failed to execute the payment',
                $executedTx->status()
            );
        }

        $executedTx = $executedTx->json();
        Log::error("payment executed", $executedTx);

        $status = $executedTx["data"]['transaction']['status'] ?? "FAILED";
        $error = $executedTx["data"]['transaction']['provider_error_code'] ?? "";
        $errorDescription = $executedTx["data"]['transaction']['error_message'] ?? "";

        return match ($status) {
            "SUCCESS" => new TransactionResult(Status::SUCCESS, $uuid),
            "PENDING", "WAITING_FOR_PAYMENT" => new TransactionResult(Status::PENDING, $uuid),
            "FAILED", "CANCELLED", "ERROR" => new TransactionResult(
                Status::ERROR,
                $uuid,
                $error,
                $errorDescription,
            ),
            default => new TransactionResult(
                Status::ERROR,
                $uuid,
                sprintf("Unexpected status [%s]", $status)
            ),
        };
    }


    public function checkStatus(Transaction $transaction): TransactionResult
    {
        $externalReference = $transaction->external_reference;
        $checkedTx = $this->makeRequest()->get("/api/payment/{$externalReference}?publicKey={$this->publicKey}");
        if ($checkedTx->failed()) {
            Log::error("Failed find transaction in remote system", $checkedTx->json());
            return new TransactionResult(
                Status::ERROR,
                $externalReference,
                'Transaction not found',
                $checkedTx->status()
            );
        }
        $checkedTx = $checkedTx->json();
        $uuid = $checkedTx["data"]['transaction']['uuid'] ?? "";
        $status = $checkedTx["data"]['transaction']['status'] ?? "FAILED";
        $error = $checkedTx["data"]['transaction']['provider_error_code'] ?? "";
        $errorDescription = $checkedTx["data"]['transaction']['error_message'] ?? "";

        return match ($status) {
            "SUCCESS" => new TransactionResult(Status::SUCCESS, $uuid),
            "PENDING", "WAITING_FOR_PAYMENT" => new TransactionResult(Status::PENDING, $uuid),
            "FAILED", "CANCELLED", "ERROR" => new TransactionResult(
                Status::ERROR,
                $uuid,
                $error,
                $errorDescription,
            ),
            default => new TransactionResult(
                Status::ERROR,
                $uuid,
                sprintf("Unexpected status [%s]", $status)
            ),
        };
    }
    public function isValidCallback(Request $request): bool
    {
        if (!($request->header("x-hash") === $this->hash)) {
            Log::info("pgService: invalid callback hash");
            return false;
        }
        $data = $request->all();
        $status = $data['transaction']['status'] ?? "";
        $uuid = $data['transaction']['user_reference'] ?? "";
        if (empty($status) || !in_array($status, ["SUCCESS", "CANCELLED", "FAILED"])) {
            Log::info("pgService: invalid status", compact("status", "data"));
            return false;
        }
        if (empty($uuid)) {
            Log::info("pgService: missing uuid", compact("uuid", "data"));
            return false;
        }
        return true;
    }

    public function getCallbackTransaction(Request $request): ?Transaction
    {
        $data = $request->all();
        $uuid = $data['transaction']['user_reference'] ?? "";
        return Transaction::firstWhere("uuid", $uuid);

    }
    public function getCallbackResult(Request $request): ?TransactionResult
    {
        $data = $request->all();
        $status = $data['transaction']['status'] ?? "";
        $uuid = $data['transaction']['uuid'] ?? "";
        $status = $data['transaction']['status'] ?? "FAILED";
        $error = $data['transaction']['provider_error_code'] ?? "";
        $errorDescription = $data['transaction']['error_message'] ?? "";

        return match ($status) {
            "SUCCESS" => new TransactionResult(Status::SUCCESS, $uuid),
            "FAILED", "CANCELLED" => new TransactionResult(
                Status::ERROR,
                $uuid,
                $error,
                $errorDescription,
            ),
            default => null,
        };
    }
}