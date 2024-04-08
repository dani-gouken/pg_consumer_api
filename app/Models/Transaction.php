<?php

namespace App\Models;

use App\Services\Payment\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Transaction
 *
 * @property int $id
 * @property int $product_id
 * @property int $service_id
 * @property string $uuid
 * @property int $status_check_count
 * @property int $max_status_check
 * @property int $amount
 * @property Status|string $status
 * @property string|null $external_reference
 * @property string|null $secret
 * @property string|null $error
 * @property string|null $provider_error
 * @property string $destination
 * @property string|null|\DatetimeInterface $last_status_check_at
 * @property string|null|\DatetimeInterface $processed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Service|null $service
 * @property-read \App\Models\ServicePayment|null $servicePayment
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereDestination($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereExternalReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereLastStatusCheckAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereMaxStatusCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereProviderError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereStatusCheckCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereUuid($value)
 * @property int|null $service_payment_id
 * @property string|TransactionKind $kind
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereKind($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereServicePaymentId($value)
 * @mixin \Eloquent
 */
class Transaction extends Model
{
    use HasFactory;

    protected $casts = [
        "status" => Status::class,
        "kind" => TransactionKind::class,
    ];

    public function getStatus(): Status
    {
        return $this->status;
    }

    /**
     * @return BelongsTo<Service,self>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
 
    /**
     * @return BelongsTo<Product,self>
     */
   public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<ServicePayment,self>
     */
    public function servicePayment(): BelongsTo
    {
        return $this->belongsTo(ServicePayment::class);
    }

    public function success(): Transaction
    {
        $this->status = Status::SUCCESS;
        $this->processed_at = new \DateTime();
        return $this;
    }

    public function error(string $error, string $providerError): Transaction
    {
        $this->status = Status::ERROR;
        $this->error = $error;
        $this->provider_error = $providerError;
        $this->processed_at = new \DateTimeImmutable();
        return $this;
    }
    public function pending(bool $incrementStatusCheckCount = false): Transaction
    {
        $this->status = Status::PENDING;
        if ($incrementStatusCheckCount) {
            $this->status_check_count = $this->status_check_count + 1;
        }
        $this->last_status_check_at = new \DateTimeImmutable();
        return $this;
    }

    public function expired(): bool
    {
        return $this->status_check_count >= $this->max_status_check;
    }

    public function isDebit(): bool
    {
        return $this->kind === TransactionKind::debit;
    }
    public function isCredit(): bool
    {
        return $this->kind === TransactionKind::credit;
    }
}