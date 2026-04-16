<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Shipper;
use Illuminate\Console\Command;

class DebugOrder extends Command
{
    protected $signature = 'debug:order {id}';
    protected $description = 'Debug specific order with all relationships';

    public function handle()
    {
        $id = $this->argument('id');
        $order = Order::with('items.product.user', 'user', 'shipper.user')->find($id);

        if (!$order) {
            $this->error("Order #$id not found");
            return;
        }

        $this->info("=== Order #$id Debug ===");
        $this->line("Order Code: {$order->order_code}");
        $this->line("Status: {$order->status}");
        $this->line("Shipper ID: " . ($order->shipper_id ?? 'NULL'));
        $this->line("User ID: {$order->user_id} ({$order->user->name ?? 'N/A'})");
        $this->line("Total: {$order->total}");
        $this->line("Address: {$order->address}");

        $this->info("\n📦 Items Count: {$order->items->count()}");
        foreach ($order->items as $item) {
            $this->line("  Item #{$item->id}");
            $this->line("    - Product ID: {$item->product_id}");
            $this->line("    - Product: " . ($item->product?->name ?? 'NOT FOUND'));
            $this->line("    - Product User: " . ($item->product?->user?->name ?? 'NOT FOUND'));
            $this->line("    - Quantity: {$item->quantity}");
            $this->line("    - Price: {$item->price}");
        }

        $this->info("\n👥 Shipper Info:");
        if ($order->shipper_id) {
            $shipper = Shipper::find($order->shipper_id);
            $this->line("  - Shipper ID: {$shipper->id}");
            $this->line("  - User ID: {$shipper->user_id}");
            $this->line("  - Status: {$shipper->status}");
            $this->line("  - Location: ({$shipper->current_latitude}, {$shipper->current_longitude})");
        } else {
            $this->line("  - No shipper assigned");
        }

        // Test distance calculation
        if ($order->items->count() > 0 && $order->shipper_id) {
            $restaurantUser = $order->items->first()->product?->user;
            if ($restaurantUser?->latitude && $restaurantUser?->longitude) {
                $this->info("\n📍 Distance Calculation:");
                $this->line("  Restaurant: ({$restaurantUser->latitude}, {$restaurantUser->longitude})");
                $this->line("  Shipper: ({$order->shipper->user->latitude ?? 'N/A'}, {$order->shipper->user->longitude ?? 'N/A'})");
            }
        }
    }
}
