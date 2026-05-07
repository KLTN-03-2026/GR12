<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = App\Models\Order::latest()->first();
if (!$order) {
    echo "No order found\n";
    exit;
}
echo "Order ID: " . $order->id . " Status: " . $order->status . "\n";
if ($order->shipper_id) {
    echo "Already assigned to shipper " . $order->shipper_id . "\n";
    exit;
}

$sc = new App\Http\Controllers\ShipperController();
$assigned = $sc->assignShipperToOrder($order->id);
echo "Assigned: " . ($assigned ? 'true' : 'false') . "\n";
$order->refresh();
echo "New status: " . $order->status . " Shipper ID: " . $order->shipper_id . "\n";
