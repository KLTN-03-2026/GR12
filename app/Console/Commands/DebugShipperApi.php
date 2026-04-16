<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Shipper;
use Illuminate\Console\Command;
use App\Http\Controllers\ShipperController;
use Illuminate\Support\Facades\Auth;

class DebugShipperApi extends Command
{
    protected $signature = 'debug:shipper-api {shipper_id}';
    protected $description = 'Debug shipper API response';

    public function handle()
    {
        $shipperId = $this->argument('shipper_id');
        $shipper = Shipper::find($shipperId);

        if (!$shipper) {
            $this->error("Shipper $shipperId not found");
            return;
        }

        $this->info("=== Testing API for Shipper ID: $shipperId ===");
        $this->line("Shipper User ID: {$shipper->user_id}");
        $this->line("Shipper Status: {$shipper->status}");
        $this->line("Location: ({$shipper->current_latitude}, {$shipper->current_longitude})");

        // Manually call the controller method
        $this->info("\n📊 Testing ShipperController@index()...");
        
        // Fake authentication
        Auth::loginUsingId($shipper->user_id);
        
        $controller = new ShipperController();
        $response = $controller->index();
        $data = json_decode($response->getContent(), true);

        $this->line("Current Orders: " . count($data['current_orders'] ?? []));
        $this->line("Available Orders: " . count($data['available_orders'] ?? []));

        if (count($data['available_orders'] ?? []) === 0) {
            $this->warn("\n⚠️ No available orders. Checking database...");
            $confirmed = Order::where('status', 'confirmed')->get();
            $assigned = Order::where('status', 'assigned')->get();
            $this->line("DB Confirmed: {$confirmed->count()}");
            $this->line("DB Assigned: {$assigned->count()}");

            foreach ($confirmed as $order) {
                $this->line("\n  Order #{$order->id} ({$order->order_code})");
                $this->line("    Items: {$order->items->count()}");
                if ($order->items->count() > 0) {
                    foreach ($order->items as $item) {
                        $this->line("      - Item: product_id={$item->product_id}, product=" . ($item->product?->name ?? 'NULL'));
                    }
                }
            }
        }

        dd($data);
    }
}
