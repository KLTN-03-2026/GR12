<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'uuid',
        'discount_type',
        'discount_value',
        'expires_at',
        'max_uses',
        'used_count',
        'minimum_product_price',
    ];

    protected $casts = [
        'discount_value' => 'float',
        'expires_at' => 'datetime',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'minimum_product_price' => 'float',
    ];

    protected static function booted()
    {
        static::creating(function (Voucher $voucher) {
            if (empty($voucher->uuid)) {
                $voucher->uuid = (string) Str::uuid();
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now())
            ->where(function ($query) {
                $query->where('max_uses', 0)
                    ->orWhereColumn('used_count', '<', 'max_uses');
            });
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at ? $this->expires_at->isPast() : false;
    }

    public function getUsesRemainingAttribute()
    {
        return $this->max_uses === 0 ? null : max(0, $this->max_uses - $this->used_count);
    }

    public function isAvailable()
    {
        return !$this->is_expired
            && ($this->max_uses === 0 || $this->used_count < $this->max_uses);
    }
}
