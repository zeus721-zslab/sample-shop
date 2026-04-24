<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'category', 'is_pinned'];

    protected $casts = ['is_pinned' => 'boolean'];
}
