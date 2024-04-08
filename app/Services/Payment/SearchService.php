<?php
namespace App\Services\Payment;

use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Facades\Cache;
use Throwable;

class SearchService implements SearchServiceInterface
{

    public function __construct(private TransactionServiceResolver $resolver)
    {
    }

    /**
     * @return array<SearchResult>
     */
    public function search(
        Service $service,
        Product $product,
        string $query,
    ): array|Throwable {
        if (!$service->searchable) {
            return new \InvalidArgumentException("The service is not searchable");
        }

        $transactionService = $this->resolver->resolve(
            $service->provider
        );

        if (!($transactionService instanceof HandlesSearch)) {
            return new \InvalidArgumentException("The service provider does not support search");
        }
        $results = [];
        try {
            $results = $transactionService->search($service, $product, $query);
            Cache::putMany(
                collect($results)->mapWithKeys(fn(SearchResult $r) => [$r->id => $r->toArray()])
                    ->toArray(),
                20 * 60 // 1 hour
            );
            return $results;
        } catch (Throwable $e) {
            \Log::error('search failed', [
                "message" => $e->getMessage(),
                "trace" => $e->getTraceAsString()
            ]);
            return $e;
        }
    }

    public function getSearchResultFromCache(string $id): ?SearchResult
    {
        if (!Cache::has($id)) {
            return null;
        }
        $serialized = Cache::get($id);

        if (!is_array($serialized)) {
            return null;
        }

        return SearchResult::fromArray($serialized);

    }

}