<?php
namespace App\Services\Payment;

use App\Models\Option;
use App\Models\Product;

readonly class SearchResult
{
    public function __construct(
        public Product $product,
        /** @var array<Option> */
        public array $options = []
    ) {
    }
}