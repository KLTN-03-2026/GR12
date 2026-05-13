<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Events\ShipperLocationUpdated;

class SimulateShipper extends Command
{
    protected $signature = 'app:simulate-shipper {order_id}';
    protected $description = 'Giả lập vị trí Shipper di chuyển từ Quán đến Khách hàng (dành cho mục đích Demo/Test)';

    public function handle()
    {
        $orderId = $this->argument('order_id');
        $order = Order::with(['items.product.user', 'user'])->find($orderId);

        if (!$order) {
            $this->error("Không tìm thấy đơn hàng #{$orderId}");
            return;
        }

        if ($order->status !== 'shipping' && $order->status !== 'picked_up') {
            $this->warn("Lưu ý: Đơn hàng đang ở trạng thái {$order->status}. Tốt nhất nên test ở trạng thái 'shipping'.");
            $order->update(['status' => 'shipping']);
        }

        // Lấy tọa độ khách hàng (Điểm đến)
        $customerLat = $order->user ? $order->user->latitude : null;
        $customerLng = $order->user ? $order->user->longitude : null;

        if (!$customerLat || !$customerLng) {
            $this->error("Đơn hàng không có tọa độ giao hàng.");
            return;
        }

        $this->info("Đang bắt đầu giả lập Shipper cho Đơn hàng #{$orderId}");
        
        // Điểm xuất phát (Tạo khoảng cách giả để Test)
        // Cộng thêm 0.02 độ (khoảng 2km) để thấy Shipper chạy từ xa
        $startLat = $customerLat + 0.02;
        $startLng = $customerLng + 0.02;

        $steps = 30; // Chia nhỏ đoạn đường thành 30 bước
        
        for ($i = 0; $i <= $steps; $i++) {
            $progress = $i / $steps;
            
            // Tính toán tọa độ trung gian
            $currentLat = $startLat + ($customerLat - $startLat) * $progress;
            $currentLng = $startLng + ($customerLng - $startLng) * $progress;

            // Cập nhật vị trí vào Database cho API liveMapData lấy ra đúng dữ liệu mới nhất
            if ($order->shipper) {
                $order->shipper->update([
                    'current_latitude' => $currentLat,
                    'current_longitude' => $currentLng,
                ]);
            }

            // Bắn event qua Reverb
            event(new ShipperLocationUpdated($orderId, $currentLat, $currentLng));

            $this->info("Bước {$i}/{$steps}: Đã cập nhật tọa độ Shipper ({$currentLat}, {$currentLng})");
            
            // Nghỉ 1 giây rồi chạy tiếp
            sleep(1);
        }

        $this->info("Đã giả lập xong! Shipper đã tới nơi.");
    }
}
