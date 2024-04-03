<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Service;
use App\Services\Payment\AmountServiceInterface;
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
     * @var array<string>
     */
    #[Url]
    public $selectedOptions = [];

    /**
     * @var array<string>
     */
    #[Url(as: "credit_destination")]
    public $creditDestination = "";

    public function render(
        AmountServiceInterface $amountService
    ) {
        $options = $this->product->options()->findMany($this->selectedOptions);
        $amount = $amountService->getAmount($this->product, $options);
        $editable = empty($this->creditDestination);
        
        return view('livewire.payment-form', compact('amount', 'editable'));
    }
}
