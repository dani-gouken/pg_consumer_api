<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Option extends Model
{
    use HasFactory;

    public static function findByCode(string $code): ?Option
    {
        return self::where('code', $code)->first();
    }

    /**
     * @return BelongsTo<Service,self>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0, ",", " ");
    }

}
