<?php
namespace App\Services\Payment;

use App\Jobs\ProcessTransaction;
use App\Models\Option;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceKindEnum;
use App\Models\ServicePayment;
use App\Models\ServicePaymentStatusEnum;
use App\Models\Transaction;
use App\Models\TransactionKind;
use App\Services\Payment\Exceptions\TransactionInitFailureException;
use Illuminate\Database\Eloquent\Collection;
use Ramsey\Uuid\Uuid;
use Random\Randomizer;

class ServicePaymentProcessor implements ServicePaymentProcessorInterface
{
    public function __construct(
        private TransactionProcessorInterface $processor,
        private AmountServiceInterface $amountService,
    ) {
    }

    public function init(ServicePayment $servicePayment, ?int $amount = null): void
    {
        $servicePayment->status = ServicePaymentStatusEnum::debitPending;
        $servicePayment->save();
        $product = $servicePayment->paymentService->defaultProduct();

        try {
            $debitTx = $this->processor->createTransaction(
                $product,
                $servicePayment->debit_destination,
                TransactionKind::debit,
                (int) $servicePayment->amount,
            );
            $debitTx->service_payment_id = $servicePayment->id;
            $debitTx->save();
            ProcessTransaction::dispatch($debitTx)->onQueue("payments");
        } catch (TransactionInitFailureException $e) {
            \Log::error("payment init failed", [
                "servicePayment" => $servicePayment,
                "ex" => ["message" => $e->getMessage(), "trace" => $e->getTraceAsString()]
            ]);
            $servicePayment->status = ServicePaymentStatusEnum::debitError;
            $servicePayment->save();
        }
    }

    public function onDebitSuccess(Transaction $tx): void
    {
        /** @var ServicePayment  */
        $servicePayment = $tx->servicePayment;
        $servicePayment->status = ServicePaymentStatusEnum::creditPending;
        $servicePayment->save();
        $product = $servicePayment->product;

        try {
            $creditTx = $this->processor->createTransaction(
                $product,
                $servicePayment->credit_destination,
                TransactionKind::credit,
                $tx->amount,
            );
            $creditTx->service_payment_id = $servicePayment->id;
            $creditTx->save();
            ProcessTransaction::dispatch($creditTx)->onQueue("payments");
        } catch (TransactionInitFailureException $e) {
            \Log::error("payment init failed", [
                "servicePayment" => $servicePayment,
                "ex" => ["message" => $e->getMessage(), "trace" => $e->getTraceAsString()]
            ]);
            $this->onCreditError($tx);
        }
    }


    public function onCreditError(Transaction $tx): void
    {
        $servicePayment = $tx->servicePayment;
        $servicePayment->status = ServicePaymentStatusEnum::creditError;
        $servicePayment->save();
    }

    public function onCreditSuccess(Transaction $tx): void
    {
        $servicePayment = $tx->servicePayment;
        $servicePayment->status = ServicePaymentStatusEnum::success;
        $servicePayment->save();
    }

    public function onDebitError(Transaction $tx): void
    {
        $servicePayment = $tx->servicePayment;
        $servicePayment->status = ServicePaymentStatusEnum::debitError;
        $servicePayment->save();
    }

    public function findSuitablePaymentServiceByDestination(string $destination): ?Service
    {
        /** @var Collection<int,Service> $paymentServices */
        $paymentServices = Service::ofKindQuery(ServiceKindEnum::payment)->get();
        foreach ($paymentServices as $paymentService) {
            if (preg_match("/{$paymentService->form_input_regex}/", $destination)) {
                return $paymentService;
            }
        }
        return null;
    }

    /** @param array<Option> $options **/
    public function createServicePayment(
        Product $product,
        Service $service,
        Service $paymentService,
        string $debitDestination,
        string $creditDestination,
        array $options = [],
        string $customerName = "",
        string $notificationEmail = "",
        string $notificationPhoneNumber = "",
        ?int $amount = null,
    ): ServicePayment {
        if (!$product->fixed_price && is_null($amount)) {
            throw new TransactionInitFailureException("Amount is required for a product without a fixed price");
        }
        $price = $this->amountService->getAmount($product, $options);
        if ($price == null) {
            throw new TransactionInitFailureException("A valid price is required");
        }
        $random = new Randomizer;
        $servicePayment = new ServicePayment;
        $servicePayment->uuid = Uuid::uuid4()->toString();
        $servicePayment->code = strtoupper(bin2hex($random->getBytes(5)));
        $servicePayment->product_id = $product->id;
        $servicePayment->service_id = $product->service->id;
        $servicePayment->payment_service_id = $paymentService->id;
        $servicePayment->credit_destination = $creditDestination;
        $servicePayment->debit_destination = $debitDestination;
        $servicePayment->amount = (string) $price;
        $servicePayment->customer_name = $customerName;
        $servicePayment->notification_email = $notificationEmail;
        $servicePayment->notification_email = $notificationPhoneNumber;

        return $servicePayment;
    }

}