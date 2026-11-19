<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'sku',
    'slug',
    'name',
    'short_description',
    'description',
    'size_label',
    'image_url',
    'retail_price',
    'stock_quantity',
    'minimum_order_quantity',
    'active',
    'sort_order',
    'announced_at',
])]
class Product extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'retail_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'minimum_order_quantity' => 'integer',
            'active' => 'boolean',
            'announced_at' => 'datetime',
        ];
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function subscriberPrice(): ?float
    {
        if ($this->retail_price === null) {
            return null;
        }

        $percent = (int) config('features.subscriber_discount_percent');

        return round((float) $this->retail_price * (100 - $percent) / 100, 2);
    }

    public function priceFor(?User $user): ?float
    {
        if ($this->retail_price === null) {
            return null;
        }

        return $user?->isSubscriber() ? $this->subscriberPrice() : (float) $this->retail_price;
    }

    /** @return HasMany<WholesalePrice, $this> */
    public function wholesalePrices(): HasMany
    {
        return $this->hasMany(WholesalePrice::class);
    }

    public function basketItems(): HasMany
    {
        return $this->hasMany(BasketItem::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }
}
