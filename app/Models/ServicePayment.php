<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\ServicePayment
 *
 * @property int $id
 * @property int|null $credit_transaction_id
 * @property int|null $debit_transaction_id
 * @property ServicePaymentStatusEnum|string $status
 * @property int $product_id
 * @property int $service_id
 * @property string|null $notification_email
 * @property string|null $notification_phone_number
 * @property string $customer_name
 * @property string $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment query()
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment whereCreditTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment whereDebitTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment whereNotificationEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment whereNotificationPhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment whereUpdatedAt($value)
 * @property-read \App\Models\Service|null $service
 * @property-read \App\Models\Product|null $product
 * @property string $credit_destination
 * @property string $debit_destination
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment whereCreditDestination($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment whereDebitDestination($value)
 * @property int $payment_service_id
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment wherePaymentServiceId($value)
 * @property string $uuid
 * @property string $code
 * @property-read string $formatted_amount
 * @property-read \App\Models\Service|null $paymentService
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicePayment whereUuid($value)
 * @mixin \Eloquent
 */
class ServicePayment extends Model
{
    use HasFactory;

    protected $attributes = [
        "status" => ServicePaymentStatusEnum::draft->value
    ];

    protected $casts = [
        "status" => ServicePaymentStatusEnum::class
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
    public function paymentService(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'payment_service_id');
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format((int) $this->amount, 0, ",", " ") . " FCFA";
    }

    public static function findByCodeOrFail(string $code)
    {
        return self::where("code", $code)->firstOrFail();
    }

}