<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Zslab\Search\Contracts\Searchable;
use Zslab\Search\Utils\JamoConverter;

class Product extends Model implements Searchable
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

    public function toSearchArray(): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'name_suggest'  => $this->name,
            'name_jamo'     => JamoConverter::convert($this->name),
            'slug'          => $this->slug,
            'description'   => $this->description ?? '',
            'category_name' => $this->category?->name ?? '',
            'price'         => $this->price,
            'sale_price'    => $this->sale_price,
            'status'        => $this->status,
            'order_count'   => $this->order_count,
            'rating_avg'    => $this->rating_avg,
            'images'        => $this->images ?? [],
        ];
    }

    public static function getSearchIndex(): string
    {
        return 'zslab_products';
    }

    public static function getSearchFields(): array
    {
        return ['name^3', 'description^1', 'category_name^2'];
    }
}
