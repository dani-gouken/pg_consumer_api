<?php
namespace App\Services\Payment;
use App\Models\Product;
use App\Models\Service;

interface HandlesSearch
{
    /**
     * @return array<SearchResult>
     */
    public function search(Service $service, Product $product, string $query): array;
}