<?php
namespace App\Services\Smobilpay;

use App\Models\Transaction;
use App\Services\Payment\Status;
use App\Services\Payment\TransactionResult;
use App\Services\Payment\TransactionServiceInterface;
use Maviance\S3PApiClient\ApiException;
use Maviance\S3PApiClient\Model\CollectionRequest;
use Maviance\S3PApiClient\Model\QuoteRequest;
use Maviance\S3PApiClient\Service\ConfirmApi;
use Maviance\S3PApiClient\Service\InitiateApi;
use Maviance\S3PApiClient\Service\VerifyApi;
use Log;

class SmobilpayService implements TransactionServiceInterface
{
    use MapSmobilpayErrorCodes;
    private string $apiVersion;
    public function __construct(
        private InitiateApi $initiateApi,
        private ConfirmApi $confirmApi,
        private VerifyApi $verifyApi,
        private string $paymentEmail,
    ) {
        $this->apiVersion = config('smobilpay.api.version');
    }

    public function initiate(Transaction $transaction): TransactionResult
    {
        try {

            $quoteRequest = new QuoteRequest;
            $quoteRequest->setAmount($transaction->amount);
            $quoteRequest->setPayItemId($transaction->product->provider_id_1);
            Log::info('[SMOBILPAY] sending quote request', compact('quoteRequest'));
            $quote = $this->initiateApi->quotestdPost($this->apiVersion, $quoteRequest);
            Log::info('[SMOBILPAY] quote received', compact('quote', 'transaction'));

            $collectionRequest = new CollectionRequest;
            $collectionRequest->setQuoteId($quote->getQuoteId());
            $collectionRequest->setServiceNumber($transaction->destination);
            $collectionRequest->setTrid($transaction->secret);
            $collectionRequest->setCustomerPhonenumber(
                $transaction->destination,
            );
            $collectionRequest->setCustomerEmailaddress(
                $this->paymentEmail,
            );

            Log::info('[SMOBILPAY] sending collection request', compact('collectionRequest'));
            $collectionResponse = $this->confirmApi->collectstdPost($this->apiVersion, $collectionRequest);
            Log::info('[SMOBILPAY] collection received', compact('collectionResponse', 'transaction'));

            return new TransactionResult(
                Status::PENDING,
                $collectionResponse->getPtn(),
            );
        } catch (ApiException $e) {
            Log::info('[SMOBILPAY] failed to initiate payment', [
                'exception' => [
                    'message' => $e->getMessage(),
                    'body' => $e->getResponseBody(),
                    'statusCode' => $e->getCode(),
                    "trace" => $e->getTraceAsString()
                ],
                "transaction" => $transaction,
            ]);
            return new TransactionResult(
                Status::ERROR,
                '',
                'failed to initiate the payment'
            );
        } catch (\Throwable $e) {
            Log::warning("[SMOBILPAY] unexpected error during payment initiation", [
                'exception' => [
                    'message' => $e->getMessage(),
                    "trace" => $e->getTraceAsString()
                ],
                "transaction" => $transaction,
            ]);
            return new TransactionResult(
                Status::ERROR,
                error: 'an unpextected error occured during the payment initiation'
            );
        }

    }

    public function checkStatus(Transaction $transaction): TransactionResult
    {
        try {
            $payments = $this->verifyApi->verifytxGet(
                $this->apiVersion,
                trid: $transaction->secret,
            );
            if (count($payments) !== 1) {
                Log::warning("[SMOBILPAY] Found more than one payment or no payment", [
                    "transaction" => $transaction,
                ]);
                return new TransactionResult(
                    Status::ERROR,
                    externalReference: $transaction->external_reference,
                    error: "Payment not found",
                );
            }
            $payment = $payments[0];
            Log::info("[SMOBILPAY] status check successful", [
                "payment" => $payment,
            ]);
            switch ($payment->getStatus()) {
                case "SUCCESS":
                    return new TransactionResult(
                        Status::SUCCESS,
                        externalReference: $transaction->external_reference,
                    );
                case "PENDING":
                    return new TransactionResult(
                        Status::PENDING,
                        externalReference: $transaction->external_reference,
                    );
                default:
                    return new TransactionResult(
                        Status::ERROR,
                        externalReference: $transaction->external_reference,
                        providerError: $payment->getErrorCode(),
                        error: $this->mapErrorCode($payment->getErrorCode())
                    );

            }
        } catch (ApiException $e) {
            if (($e instanceof ApiException) && ($e->getCode() == 500)) {
                return new TransactionResult(
                    Status::PENDING,
                    $transaction->external_reference
                );
            }
            Log::warning("[SMOBILPAY] unexpected error during payment status check", [
                'exception' => [
                    'message' => $e->getMessage(),
                    'body' => $e->getResponseBody(),
                    'statusCode' => $e->getCode(),
                    "trace" => $e->getTraceAsString()
                ],
                "transaction" => $transaction,
            ]);
            return new TransactionResult(
                Status::ERROR,
                $transaction->external_reference,
                providerError: $e->getCode(),
                error: 'an unpextected error occured during the status check'
            );
        } catch (\Throwable $e) {
            Log::warning("[SMOBILPAY] unexpected error during payment status check", [
                'exception' => [
                    'message' => $e->getMessage(),
                    "trace" => $e->getTraceAsString()
                ],
                "transaction" => $transaction,
            ]);
            return new TransactionResult(
                Status::ERROR,
                $transaction->external_reference,
                error: 'an unpextected error occured during the status check'
            );
        }
    }
}