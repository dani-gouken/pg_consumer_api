<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "image" => \Vite::asset($this->logo_url),
            "name" => $this->name,
            "description" => $this->description,
            "kind" => $this->kind,
            "enabled" => $this->enabled,
            "min_amount" => $this->min_amount,
            "form_input_label" => $this->form_input_label,
            "form_input_placeholder" => $this->form_input_placeholder,
            "form_input_regex" => $this->form_input_regex,
            "max_amount" => $this->max_amount,
            "products" => ProductResource::collection($this->whenLoaded("products"))
        ];
    }
}