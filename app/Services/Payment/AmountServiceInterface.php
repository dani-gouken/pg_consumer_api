<?php

namespace App\Services\Payment;

use App\Models\Option;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface AmountServiceInterface
{
    /** @param iterable<Option>|Collection<int,Option> $options **/
    public function getAmount(
        Product $product,
        iterable|Collection $options = [],
        ?int $amount = null,
    ): ?int;
}