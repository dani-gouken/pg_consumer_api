<?php

namespace App\Http\Resources;

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
        return [
            "uuid" => $this->uuid,
            "status" => $this->status,
            "product" => new ProductResource($this->product),
            "service" => new ServiceResource($this->service),
            "paymentService" => new ServiceResource($this->paymentService),
            "code" => $this->code,
            "debit_destination" => $this->debit_destination,
            "credit_destination" => $this->credit_destination,
            "amount" => $this->amount,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
