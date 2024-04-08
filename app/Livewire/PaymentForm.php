<?php

namespace App\Livewire;

use App\Http\Requests\SearchRequest;
use App\Models\Product;
use App\Models\Service;
use App\Services\Payment\AmountServiceInterface;
use App\Services\Payment\SearchResult;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Attributes\Url;

class PaymentForm extends Component
{
    /**
     * @var Product
     */
    public $product;

    /**
     * @var Service
     */
    public $service;
    
    /**
     * @var SearchResult|null
     */
    public $searchResult;


    /**
     * @var array<int>
     */
    #[Url]
    public $selectedOptions = [];

    /**
     * @var string
     */
    #[Url( as: "credit_destination")]
    public $creditDestination = "";

    public function render(
        AmountServiceInterface $amountService
    ): View {
        $options = $this->product->options()->findMany($this->selectedOptions);
        $amount = $amountService->getAmount($this->product, $options);
        $editable = empty($this->creditDestination);

        return view('livewire.payment-form', compact('amount', 'editable'));
    }
}
