<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Jobs\ProcessTransaction;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceKindEnum;
use App\Models\ServicePayment;
use App\Services\Payment\ServicePaymentProcessorInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Random\Engine\Secure;
use Random\Randomizer;

class PaymentController extends Controller
{
    public function show(ServicePayment $payment): View
    {
        $service = $payment->service;
        $product = $payment->product;
        return view("payment.show", compact("payment", "service", "product"));
    }

    public function create(Request $request, string $serviceSlug, string $productSlug)
    {
        $service = Service::findPubliclyUsableBySlugOrFail($serviceSlug);
        $product = $service->enabledProductsQuery()->where("slug", "=", $productSlug)->firstOrFail();

        if ($service->kind == ServiceKindEnum::bill && !$product->fixed_price) {
            return redirect()->route('search.create', [$service->slug, $product->slug]);
        }

        $paymentServices = Service::ofKindQuery(ServiceKindEnum::payment)->get();
        return view("payment.create", compact("product", "service", "paymentServices"));
    }

    public function store(
        PaymentRequest $request,
        ServicePaymentProcessorInterface $paymentProcessor,
    ) {
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
        $payment = $paymentProcessor->createServicePayment(
            $product,
            $service,
            $paymentService,
            debitDestination: $request->get("debit_destination"),
            creditDestination: $request->get("credit_destination"),
            amount: $request->get("amount"),
        );
        $payment->save();
        $paymentProcessor->init($payment, $request->get("amount"));
        return redirect()->route("payment.show", compact("payment"));
    }

}