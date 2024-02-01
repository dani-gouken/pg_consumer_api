<?php

namespace App\Http\Resources;

use App\Models\ServicePayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ServicePayment */
        $payment = $this;
        return [
            "uuid" => $payment->uuid,
            "status" => $payment->status,
            "product" => new ProductResource($payment->product),
            "service" => new ServiceResource($payment->service),
            "paymentService" => new ServiceResource($payment->paymentService),
            "code" => $payment->code,
            "debit_destination" => $payment->debit_destination,
            "credit_destination" => $payment->credit_destination,
            "amount" => $payment->amount,
            "created_at" => $payment->created_at,
            "updated_at" => $payment->updated_at,
        ];
    }
}
