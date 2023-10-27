<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiPaymentRequest;
use App\Http\Requests\PaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Product;
use App\Services\Payment\ServicePaymentProcessor;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function pay(ApiPaymentRequest $request, string $uuid, ServicePaymentProcessor $paymentProcessor)
    {
        $product = Product::findByEnabledUuidOrFail($uuid);
        $service = $product->service;
        $this->authorize("viewProducts", $service);
        if (!$service->fixed_amount) {
            $request->validate([
                "amount" => ["required", "min:{$service->min_amount}", "max:{$service->max_amount}", "integer"]
            ]);
        }
        $paymentService = $paymentProcessor->findSuitablePaymentServiceByDestination(
            $request->get("debit_destination")
        );
        if (!$paymentService) {
            throw ValidationException::withMessages([
                'debit_destination' => [
                    "The payee phone number is not supported"
                ]
            ]);
        }
        $payment = $paymentProcessor->createServicePayment(
            $product,
            $service,
            $paymentService,
            debitDestination: $request->get("debit_destination"),
            creditDestination: $request->get("credit_destination"),
            amount: $request->get("amount")
        );
        $payment->save();
        $paymentProcessor->init($payment, $request->get("amount"));
        return new PaymentResource($payment);
    }
}