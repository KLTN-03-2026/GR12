<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Shipper;
use Illuminate\Console\Command;

class DebugShipperOrders extends Command
{
    protected $signature = 'debug:shipper-orders';
    protected $description = 'Debug shipper orders data';

    public function handle()
    {
        $this->info('=== Shipper Orders Debug ===');
        
        // Check shippers
        $shippers = Shipper::all();
        $this->info("\n👥 Total Shippers: {$shippers->count()}");
        foreach ($shippers as $shipper) {
            $this->line("  - ID: {$shipper->id}, User: {$shipper->user_id}, Status: {$shipper->status}");
            $this->line("    Location: ({$shipper->current_latitude}, {$shipper->current_longitude})");
        }

        // Check orders
        $this->info("\n📦 Total Orders: " . Order::count());
        
        $confirmedOrders = Order::where('status', 'confirmed')->get();
        $this->info("\n✅ Confirmed Orders (available): {$confirmedOrders->count()}");
        foreach ($confirmedOrders as $order) {
            $this->line("  - Order #{$order->id} ({$order->order_code}): shipper_id={$order->shipper_id}");
        }

        $assignedOrders = Order::where('status', 'assigned')->get();
        $this->info("\n🔔 Assigned Orders: {$assignedOrders->count()}");
        foreach ($assignedOrders as $order) {
            $this->line("  - Order #{$order->id} ({$order->order_code}): shipper_id={$order->shipper_id}");
        }

        $allOrdersByStatus = Order::select('status')->distinct()->pluck('status');
        $this->info("\n📊 All Orders by Status:");
        foreach ($allOrdersByStatus as $status) {
            $count = Order::where('status', $status)->count();
            $this->line("  - {$status}: {$count}");
        }
    }
}
