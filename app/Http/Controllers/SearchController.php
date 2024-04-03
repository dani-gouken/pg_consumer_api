<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Service;
use App\Services\Payment\HandlesSearch;
use App\Services\Payment\TransactionServiceResolverInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Throwable;

class SearchController extends Controller
{
    public function __construct(
        private TransactionServiceResolverInterface $transactionServiceResolver
    ) {
    }
    public function create(string $serviceSlug, string $productSlug): View
    {
        $service = Service::findPubliclyUsableBySlugOrFail($serviceSlug);
        $product = $service->enabledProductsQuery()->where("slug", "=", $productSlug)->firstOrFail();
        abort_if($product->fixed_price || !$service->searchable, 404);

        return view('search.create', compact('service', 'product'));
    }
    public function store(Request $request, string $serviceSlug, string $productSlug)
    {
        $service = Service::findPubliclyUsableBySlugOrFail($serviceSlug);
        $product = $service->enabledProductsQuery()->where("slug", "=", $productSlug)->firstOrFail();


        if ($product->fixed_price || !$service->searchable) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Service unavailable');
        }

        $transactionService = $this->transactionServiceResolver->resolve(
            $service->provider
        );

        if (!($transactionService instanceof HandlesSearch)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Service unavailable');
        }

        $result = null;
        try {
            $result = $transactionService->search($service, $request->q);
        } catch (Throwable $e) {
            \Log::error('search failed', [
                "message" => $e->getMessage(),
                "trace" => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Search failed');
        }

        if ($result == null) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Aucun resultat trouvé');
        }

        return redirect()->route('payment.create', [
            'product' => $result->product->slug,
            'service' => $serviceSlug,
            'credit_destination' => $request->q,
        ]);
    }
}
