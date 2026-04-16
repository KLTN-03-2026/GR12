<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Shipper;
use Illuminate\Console\Command;

class DebugOrderDetails extends Command
{
    protected $signature = 'debug:order {orderId}';
    protected $description = 'Debug detailed order data';

    public function handle()
    {
        $orderId = $this->argument('orderId');
        $order = Order::with('items.product.user', 'user', 'shipper.user')->find($orderId);

        if (!$order) {
            $this->error("Order #{$orderId} not found");
            return;
        }

        $this->info("=== Order #{$orderId} Details ===\n");
        $this->line("Order Code: {$order->order_code}");
        $this->line("Status: {$order->status}");
        $this->line("Shipper ID: {$order->shipper_id}");
        $this->line("Address: {$order->address}");
        $this->line("Total: {$order->total}");

        $this->info("\n🧑 Customer:");
        if ($order->user) {
            $this->line("  Name: {$order->user->name}");
            $this->line("  Location: ({$order->user->latitude}, {$order->user->longitude})");
        } else {
            $this->error("  No customer found!");
        }

        $this->info("\n📦 Items:");
        if ($order->items->count() === 0) {
            $this->error("  No items in this order!");
        } else {
            foreach ($order->items as $item) {
                $this->line("  Item ID: {$item->id}, Qty: {$item->quantity}, Price: {$item->price}");
                if ($item->product) {
                    $this->line("    Product: {$item->product->name}");
                    if ($item->product->user) {
                        $this->line("    Restaurant: {$item->product->user->restaurant_name ?? $item->product->user->name}");
                        $this->line("    Restaurant Location: ({$item->product->user->latitude}, {$item->product->user->longitude})");
                    } else {
                        $this->error("    Product has no user (no restaurant)!");
                    }
                } else {
                    $this->error("    Product not found!");
                }
            }
        }

        $this->info("\n🚚 Shipper:");
        if ($order->shipper) {
            $this->line("  ID: {$order->shipper->id}");
            $this->line("  User: {$order->shipper->user->name}");
            $this->line("  Status: {$order->shipper->status}");
            $this->line("  Location: ({$order->shipper->current_latitude}, {$order->shipper->current_longitude})");
        } else {
            $this->line("  Not assigned");
        }
    }
}
