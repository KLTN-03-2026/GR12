<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $activeOrders = \App\Models\Order::whereIn('status', ['preparing', 'ready', 'delivering'])
        ->with([
            'user:id,name,phone,latitude,longitude,address',
            'shipper.user:id,name,phone,latitude,longitude',
            'items.product.user:id,name,restaurant_name,phone,latitude,longitude,address'
        ])
        ->get()
        ->map(function ($order) {
            $restaurant = null;
            if ($order->items->isNotEmpty() && $order->items->first()->product) {
                $restaurant = $order->items->first()->product->user;
            }

            return [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'status' => $order->status,
                'customer' => $order->user,
                'restaurant' => $restaurant,
                'shipper' => $order->shipper ? $order->shipper->user : null,
            ];
        });

    file_put_contents('test_output.json', json_encode(['success' => true, 'data' => $activeOrders], JSON_PRETTY_PRINT));
    echo "Done";
} catch (\Exception $e) {
    file_put_contents('test_output.json', json_encode(['success' => false, 'error' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile()]));
    echo "Error";
}
