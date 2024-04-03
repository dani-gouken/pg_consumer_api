<?php

namespace App\Services\Payment;

use App\Models\Option;
use App\Models\Product;

class AmountService implements AmountServiceInterface
{
    public function getAmount(
        Product $product,
        /** @param iterable<Option> */
        iterable $options = [],
        ?int $amount = null,
    ): ?int {
        $amount = $product->fixed_price ? $product->price : $amount;
        if (!$product->fixed_price) {
            return $amount;
        }

        foreach ($options as $option) {
            $amount += $option->amount;
        }

        return $amount;
    }
}