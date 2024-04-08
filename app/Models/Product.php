<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Product
 *
 * @property int $id
 * @property int $service_id
 * @property string $uuid
 * @property string $color
 * @property string $name
 * @property string $tag
 * @property bool $promoted
 * @property bool $featured
 * @property string $name
 * @property string $description
 * @property string $slug
 * @property string|null $provider_id_1
 * @property string|null $provider_id_2
 * @property bool $fixed_price
 * @property int|null $price
 * @property bool $enabled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Service|null $service
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereFixedPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereProviderId1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereProviderId2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUuid($value)
 * @property-read mixed $formatted_price
 * @property bool $default
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDefault($value)
 * @mixin \Eloquent
 */
class Product extends Model
{
    use HasFactory;

    protected $casts = [
        "default" => "boolean",
        "fixed_price" => "boolean",
        "promoted" => "boolean",
        "featured" => "boolean",
        "enabled" => "boolean",
    ];

    /**
     * @return BelongsTo<Service, self>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return HasMany<Transaction>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, ",", " ");
    }

    public static function findByEnabledIdOrFail(int $id): self
    {
        return self::where("id", "=", $id)
            ->where("enabled", "=", true)
            ->firstOrFail();
    }
    public static function findByEnabledSlugOrFail(string $slug): self
    {
        return self::where("slug", "=", $slug)
            ->where("enabled", "=", true)
            ->firstOrFail();
    }
    public static function findByEnabledUuidOrFail(string $uuid): self
    {
        return self::where("uuid", "=", $uuid)
            ->where("enabled", "=", true)
            ->firstOrFail();
    }

    /**
     * @return BelongsToMany<Option>
     */
    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class);
    }
}