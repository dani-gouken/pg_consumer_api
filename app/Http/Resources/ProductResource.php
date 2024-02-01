<?php

namespace App\Http\Resources;

use App\Models\Product;
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
        /** @var Product */
        $product = $this;
        return [
            "uuid" => $product->uuid,
            "color" => $product->color,
            "name" => $product->name,
            "description" => $product->description,
            "default" => $product->default,
            "fixed_price" => $product->fixed_price,
            "price" => $product->price,
        ];
    }
}
