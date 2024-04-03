<?php

namespace App\Services\Payment;

use App\Models\Option;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface AmountServiceInterface
{
    public function getAmount(
        Product $product,
        /** @param iterable<Option> */
        iterable $options = [],
        ?int $amount = null,
    ): ?int;
}