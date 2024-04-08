<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Jobs\ProcessTransaction;
use App\Models\Option;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceKindEnum;
use App\Models\ServicePayment;
use App\Services\Payment\SearchServiceInterface;
use App\Services\Payment\ServicePaymentProcessorInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Random\Engine\Secure;
use Random\Randomizer;

class PaymentController extends Controller
{
    public function __construct(
        private SearchServiceInterface $searchService,
    ) {

    }

    public function show(ServicePayment $payment): View
    {
        $service = $payment->service;
        $product = $payment->product;
        return view("payment.show", compact("payment", "service", "product"));
    }

    public function create(Request $request, Service $service, Product $product): View|RedirectResponse
    {
        if ($service->kind == ServiceKindEnum::bill && !request()->has('item')) {
            return redirect()->route('search.index', [$service->slug, $product->slug]);
        }

        $searchResult = null;
        if ($service->kind == ServiceKindEnum::bill) {
            $searchResult = $this->searchService->getSearchResultFromCache($request->get('item'));
            if (is_null($searchResult)) {
                return redirect()->route('search.index', [$service->slug, $product->slug]);
            }
        }

        $paymentServices = Service::ofKindQuery(ServiceKindEnum::payment)->get();
        return view("payment.create", compact("product", "service", "paymentServices", "searchResult"));
    }

    public function store(
        PaymentRequest $request,
        ServicePaymentProcessorInterface $paymentProcessor,
    ): RedirectResponse {
        $product = Product::findByEnabledIdOrFail($request->get("product_id"));
        $service = $product->service;
        if (!$product->fixed_price) {
            $request->validate([
                "amount" => ["required", "min:{$service->min_amount}", "max:{$service->max_amount}", "integer"]
            ]);
        }
        $paymentService = $paymentProcessor->findSuitablePaymentServiceByDestination(
            $request->get("debit_destination")
        );

        if (!$paymentService) {
            return redirect()->back()->with("warning", "Le numéro payeur n'est pas supporté");
        }

        $options = [];
        if ($request->options && !empty($request->options)) {
            $options = $service->options()->find($request->options)->all();
        }

        $payment = $paymentProcessor->createServicePayment(
            $product,
            $service,
            $paymentService,
            options: $options,
            debitDestination: $request->get("debit_destination"),
            creditDestination: $request->get("credit_destination"),
            amount: $request->get("amount"),
        );
        $payment->save();
        $payment->options()->sync(collect($options)->pluck('id')->toArray());
        $paymentProcessor->init($payment, $request->get("amount"));
        return redirect()->route("payment.show", compact("payment"));
    }

}