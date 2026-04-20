<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'name', 'type', 'value', 'min_order_amount', 'max_discount_amount',
        'max_uses', 'used_count', 'is_active', 'starts_at', 'expires_at',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'starts_at'  => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function isValid(): bool
    {
        if (! $this->is_active) return false;
        if ($this->max_uses && $this->used_count >= $this->max_uses) return false;
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) return false;
        if ($this->expires_at && $now->gt($this->expires_at)) return false;
        return true;
    }
}
