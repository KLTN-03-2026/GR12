<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('restaurant.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $order = \App\Models\Order::find($orderId);
    if (!$order) return false;
    
    // Admin có quyền theo dõi mọi đơn hàng trên Live Map
    if ($user->role === 'admin') return true;
    
    // Khách hàng của đơn hàng
    if ($user->id === $order->user_id) return true;
    
    // Shipper của đơn hàng
    $shipper = \App\Models\Shipper::where('user_id', $user->id)->first();
    if ($shipper && $order->shipper_id === $shipper->id) return true;
    
    return false;
});
