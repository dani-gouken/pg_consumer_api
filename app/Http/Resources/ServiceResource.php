<?php

namespace App\Http\Resources;

use App\Models\Service;
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
        /** @var Service $service */
        $service = $this;
        return [
            "uuid" => $service->uuid,
            "image" => \Vite::asset($service->logo_url),
            "name" => $service->name,
            "description" => $service->description,
            "kind" => $service->kind,
            "enabled" => $service->enabled,
            "min_amount" => $service->min_amount,
            "form_input_label" => $service->form_input_label,
            "form_input_placeholder" => $service->form_input_placeholder,
            "form_input_regex" => $service->form_input_regex,
            "max_amount" => $service->max_amount,
            "products" => ProductResource::collection($service->whenLoaded("products")) // @phpstan-ignore-line
        ];
    }
}