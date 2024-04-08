<?php

namespace App\Services\Payment;

use App\Models\Option;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class AmountService implements AmountServiceInterface
{
    /** @param iterable<Option>|Collection<int,Option> $options **/
    public function getAmount(
        Product $product,
        iterable|Collection $options = [],
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