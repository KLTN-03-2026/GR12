<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['user_id', 'order_code', 'address', 'phone', 'note', 'voucher_code', 'subtotal', 'shipping_fee', 'discount_amount', 'total', 'payment_method', 'payment_status', 'status', 'shipper_id', 'shipper_fee'];

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
