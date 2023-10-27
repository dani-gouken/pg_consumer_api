<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Service
 *
 * @property int $id
 * @property string $uuid
 * @property string $image
 * @property string $name
 * @property string $slug
 * @property string|null $provider_id_1
 * @property string|null $provider_id_2
 * @property string $kind
 * @property int $enabled
 * @property int $public
 * @property string $provider
 * @property int|null $min_amount
 * @property int|null $max_amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $product
 * @property-read int|null $product_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Service newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service query()
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereKind($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereMaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereMinAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereProviderId1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereProviderId2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service wherePublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereUuid($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property string $description
 * @property string $form_input_label
 * @property string $form_input_placeholder
 * @property string $form_input_regex
 * @property-read Collection<int, \App\Models\Product> $enabledProductsQuery
 * @property-read int|null $enabled_products_query_count
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereFormInputLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereFormInputPlaceholder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereFormInputRegex($value)
 * @mixin \Eloquent
 */
class Service extends Model
{
    use HasFactory;

    protected $casts = [
        "enabled" => "boolean",
        "public" => "boolean",
        "kind" => ServiceKindEnum::class,
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function enabledProductsQuery(): HasMany
    {
        return $this->products()->where("enabled", "=", true);
    }

    public function defaultProduct(): ?Product
    {
        return $this->products()->where('default', '=', true)->first();
    }

    /**
     * @return Collection<Product>
     */
    public function enabledProducts(): Collection
    {
        return $this->enabledProductsQuery()->get();
    }

    /**
     * @return Collection<self>
     */
    public static function publicEnabled(): Collection
    {
        return Service::where("enabled", "=", true)
            ->where("public", "=", true)
            ->get();
    }

    /**
     * @return Collection<self>
     */
    public static function publicEnabledQuery(): Builder
    {
        return Service::where("enabled", "=", true)
            ->where("public", "=", true);
    }

    public static function ofKindQuery(ServiceKindEnum $kind)
    {
        return self::where("enabled", "=", true)
            ->where("kind", $kind->value);
    }

    public static function findPubliclyUsableBySlugOrFail(string $slug)
    {
        return Service::where("slug", "=", $slug)
            ->where("public", "=", true)
            ->where("enabled", "=", true)
            ->firstOrFail();
    }

    public static function findPubliclyUsableByIdOrFail(int $id)
    {
        return Service::where("id", "=", $id)
            ->where("public", "=", true)
            ->where("enabled", "=", true)
            ->firstOrFail();
    }

    public static function findOfKindById(ServiceKindEnum $kind, int $id)
    {
        return Service::ofKindQuery($kind)
            ->where("id", "=", $id)
            ->firstOrFail();
    }

    public function getLogoUrlAttribute(): string
    {
        return "resources/images/logos/{$this->image}";
    }
}