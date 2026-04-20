<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_number', 'status', 'total_amount', 'discount_amount',
        'shipping_fee', 'paid_amount', 'payment_method', 'payment_id',
        'shipping_address', 'memo', 'paid_at', 'shipped_at', 'delivered_at',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'paid_at'          => 'datetime',
        'shipped_at'       => 'datetime',
        'delivered_at'     => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
