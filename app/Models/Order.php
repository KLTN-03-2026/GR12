<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['user_id', 'order_code', 'address', 'phone', 'note', 'subtotal', 'shipping_fee', 'total', 'payment_method', 'status'];

    public function items() {
        return $this->hasMany(OrderItem::class);
    }
    public function user()
    {
        // Một đơn hàng thuộc về một người dùng (khách hàng)
        return $this->belongsTo(User::class);
    }
}
