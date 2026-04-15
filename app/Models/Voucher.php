<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'expires_at',
    ];

    protected $casts = [
        'discount_value' => 'float',
        'expires_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at ? $this->expires_at->isPast() : false;
    }
}
