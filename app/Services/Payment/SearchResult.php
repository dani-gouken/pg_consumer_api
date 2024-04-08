<?php
namespace App\Services\Payment;

use App\Models\Option;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use JsonSerializable;
use Livewire\Wireable;
use Serializable;

readonly class SearchResult implements Wireable
{
    public function __construct(
        public string $id,
        public string $billNumber,
        public string $customerName,
        public string $customerNumber,
        public ?int $productId = null,
        /** @var array<int> */
        public array $options = [],
        public string $description = "",
    ) {
    }

    public function getProduct(): ?Product
    {
        if (!$this->productId) {
            return null;
        }
        return Product::findByEnabledIdOrFail($this->productId);
    }
    public function hasProduct(): bool
    {
        return $this->productId != null;
    }

    /** @return Collection<int,Option> */
    public function getOptions(): Collection
    {
        return Option::find($this->options);
    }

    /**
     * @return array<string,string|array<int>|int>
     */
    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "billNumber" => $this->billNumber,
            "customerName" => $this->customerName,
            "customerNumber" => $this->customerNumber,
            "productId" => $this->productId,
            "options" => $this->options,
            "description" => $this->description,
        ];
    }

    /**
     * @param array<string,string|array<int>|int> $data
     */
    public static function fromArray(array $data): self
    {
        return new SearchResult(
            $data["id"] ?? 0,
            $data["billNumber"] ?? "",
            $data["customerName"] ?? "",
            $data["customerNumber"] ?? "",
            $data["productId"] ?? null,
            $data["options"] ?? [],
            $data["description"] ?? "",
        );
    }

    public function paymentUrl(Service $service, Product $product): string
    {
        if ($this->hasProduct()) {
            $product = Product::findByEnabledIdOrFail($this->productId);
        }

        return route('payment.create', [
            $service->slug,
            $product->slug,
            "item" => $this->id
        ]);
    }
    
    /**
     * @return array<string,string|array<int>|int>
     */
    public function toLivewire(): array
    {
        return $this->toArray();
    }
 
    /**
     * @return SearchResult
     * @param array<string,string|array<int>|int> $value
     */
    public static function fromLivewire($value)
    {
       
        return static::fromArray($value);
    }
}