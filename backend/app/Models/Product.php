<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'seller_id', 'name', 'slug', 'description', 'detail',
        'price', 'sale_price', 'stock', 'status', 'images', 'options',
        'view_count', 'order_count', 'rating_avg',
    ];

    protected $casts = [
        'images'      => 'array',
        'options'     => 'array',
        'price'       => 'integer',
        'sale_price'  => 'integer',
        'rating_avg'  => 'float',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getDiscountRateAttribute(): int
    {
        if ($this->sale_price && $this->price > 0) {
            return (int) round((1 - $this->sale_price / $this->price) * 100);
        }
        return 0;
    }

    public function getEffectivePriceAttribute(): int
    {
        return $this->sale_price ?? $this->price;
    }
}
