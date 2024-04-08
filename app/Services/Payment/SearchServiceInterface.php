<?php
namespace App\Services\Payment;

use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Facades\Cache;
use Throwable;

interface SearchServiceInterface
{

    /**
     * @return array<SearchResult>
     */
    public function search(
        Service $service,
        Product $product,
        string $query,
    ): array|Throwable;

    public function getSearchResultFromCache(string $id): ?SearchResult;

}