<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class FixOrderSubtotal extends Command
{
    protected $signature = 'fix:order-subtotal {--dry-run}';
    protected $description = 'Fix subtotal for all orders by recalculating from order_items';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 Running in DRY-RUN mode (no changes will be made)');
        }

        $orders = Order::all();
        $fixed = 0;
        $errors = 0;

        $this->output->progressStart($orders->count());

        foreach ($orders as $order) {
            try {
                // Calculate correct subtotal from order items
                $correctSubtotal = DB::table('order_items')
                    ->where('order_id', $order->id)
                    ->selectRaw('SUM(price * quantity) as total')
                    ->value('total') ?? 0;

                $correctSubtotal = (float) $correctSubtotal;

                // Check if it differs
                if (abs($correctSubtotal - $order->subtotal) > 0.01) {
                    if (!$dryRun) {
                        $order->update(['subtotal' => $correctSubtotal]);
                    }
                    
                    $this->warn("\n  Order #{$order->id} ({$order->order_code}): {$order->subtotal}đ → {$correctSubtotal}đ");
                    $fixed++;
                }

                $this->output->progressAdvance();
            } catch (\Exception $e) {
                $this->error("\n  Error processing Order #{$order->id}: " . $e->getMessage());
                $errors++;
                $this->output->progressAdvance();
            }
        }

        $this->output->progressFinish();
        $this->newLine(2);

        $this->info("✅ Fixed: {$fixed} orders");
        $this->info("❌ Errors: {$errors} orders");

        if ($dryRun) {
            $this->info("💡 Tip: Run without --dry-run to apply changes");
        }

        return 0;
    }
}
