<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Models\Product;
use App\Models\Service;
use App\Services\Payment\HandlesSearch;
use App\Services\Payment\SearchServiceInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Throwable;

class SearchController extends Controller
{
    public function __construct(
        private SearchServiceInterface $searchService
    ) {
    }
    public function index(Service $service, Product $product): View
    {
        abort_if(!$service->searchable, 404);
        $query = "";
        return view('search.index', compact('service', 'product', 'query'));
    }

    public function search(SearchRequest $request, Service $service, Product $product): View|RedirectResponse
    {
        if (!$service->searchable) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Service unavailable');
        }
        
        $query = $request->q;

        $results = $this->searchService->search(
            $service,
            $product,
            $query,
        );
        

        if ($results instanceof Throwable) {
            \Log::error('search failed', [
                "message" => $results->getMessage(),
                "trace" => $results->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une érreur est survenu, veuillez réessayer');
        }

        if (empty($results)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Aucun resultat trouvé');
        }
        
        $results = collect($results);

        return view('search.index', compact('service', 'product', 'results', 'query'));
    }
}
