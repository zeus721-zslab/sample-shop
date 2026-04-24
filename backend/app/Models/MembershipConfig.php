<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipConfig extends Model
{
    protected $fillable = ['grade', 'min_amount', 'point_rate', 'description'];

    protected function casts(): array
    {
        return [
            'min_amount' => 'integer',
            'point_rate' => 'float',
        ];
    }
}
