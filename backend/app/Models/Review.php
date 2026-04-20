<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'order_item_id', 'rating',
        'title', 'content', 'images', 'is_verified', 'is_best',
    ];

    protected $casts = [
        'images'      => 'array',
        'is_verified' => 'boolean',
        'is_best'     => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
