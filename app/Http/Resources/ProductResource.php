<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            "color" => $this->color,
            "name" => $this->name,
            "description" => $this->description,
            "default" => $this->default,
            "fixed_price" => $this->fixed_price,
            "price" => $this->price,
        ];
    }
}
