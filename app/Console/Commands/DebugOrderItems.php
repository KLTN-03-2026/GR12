<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;

class DebugOrderItems extends Command
{
    protected $signature = 'debug:order-items {orderId}';
    protected $description = 'Debug order items for a specific order';

    public function handle()
    {
        $orderId = $this->argument('orderId');
        $order = Order::with('items.product.user')->find($orderId);

        if (!$order) {
            $this->error("Order not found");
            return 1;
        }

        $this->info("=== Order #$orderId ===");
        $this->info("Order Code: " . $order->order_code);
        $this->info("Total Items in Database: " . $order->items()->count());
        $this->newLine();

        $this->table(
            ['Item ID', 'Product ID', 'Product Name', 'Quantity', 'Price'],
            $order->items->map(fn($item) => [
                $item->id,
                $item->product_id,
                $item->product->name ?? 'N/A',
                $item->quantity,
                $item->price,
            ])->toArray()
        );

        // Check for duplicates
        $itemIds = $order->items->pluck('id')->toArray();
        $uniqueIds = array_unique($itemIds);
        
        if (count($itemIds) !== count($uniqueIds)) {
            $this->warn("WARNING: Duplicate item IDs detected!");
        } else {
            $this->info("✓ No duplicate items found");
        }

        return 0;
    }
}
