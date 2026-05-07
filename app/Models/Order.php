<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_code',
        'address',
        'phone',
        'note',
        'voucher_code',
        'subtotal',
        'distance',
        'shipping_fee',
        'service_fee',
        'packaging_fee',
        'shipper_tip',
        'discount_amount',
        'total',
        'restaurant_commission_rate',
        'restaurant_tax_fee',
        'restaurant_revenue',
        'admin_revenue',
        'payment_method',
        'payment_status',
        'status',
        'is_food_ready',
        'shipper_id',
        'shipper_fee',
        'confirmed_at',
        'picked_up_at',
        'delivering_at',
        'completed_at'
    ];

    protected $casts = [
        'is_food_ready' => 'boolean',
    ];

    public function items() {
        return $this->hasMany(OrderItem::class);
    }
    public function user()
    {
        // Một đơn hàng thuộc về một người dùng (khách hàng)
        return $this->belongsTo(User::class);
    }

    public function shipper()
    {
        return $this->belongsTo(Shipper::class);
    }
}
