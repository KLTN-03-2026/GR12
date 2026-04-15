<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ShipperController;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RestaurantOrderController extends Controller
{
    /**
     * Hiển thị danh sách đơn hàng dành cho chủ nhà hàng
     */
    public function index()
    {
        $restaurantId = Auth::id(); // ID của tài khoản chủ quán đang đăng nhập

        // Lấy các đơn hàng có chứa sản phẩm thuộc về nhà hàng này
        $orders = Order::whereHas('items.product', function ($query) use ($restaurantId) {
            $query->where('user_id', $restaurantId);
        })
        ->with(['user', 'items' => function($query) use ($restaurantId) {
            // Chỉ lấy các chi tiết món ăn (items) thuộc về nhà hàng này
            $query->whereHas('product', function($q) use ($restaurantId) {
                $q->where('user_id', $restaurantId);
            })->with('product');
        }])
        ->latest()
        ->paginate(10);

        return Inertia::render('Restaurant/Orders/Index', [
            'orders' => $orders
        ]);
    }

    /**
     * Cập nhật trạng thái đơn hàng (Xác nhận, Giao hàng, Hoàn tất)
     */
    public function updateStatus(Request $request, $id)
    {
        // 1. Validate dữ liệu đầu vào
        $request->validate([
            'status' => 'required|in:pending,processing,shipping,completed,cancelled'
        ]);

        $restaurantId = Auth::id();

        // 2. Tìm đơn hàng và kiểm tra quyền sở hữu
        // Chỉ cho phép cập nhật nếu đơn hàng này có món ăn của quán mình
        $order = Order::whereHas('items.product', function ($query) use ($restaurantId) {
            $query->where('user_id', $restaurantId);
        })->findOrFail($id);

        $oldStatus = $order->status;

        // 3. Cập nhật trạng thái
        $order->update([
            'status' => $request->status
        ]);

        // 4. Nếu chuyển sang processing, tự động gán shipper
        if ($oldStatus !== 'processing' && $request->status === 'processing') {
            $shipperController = new ShipperController();
            $shipperController->assignShipperToOrder($id);
        }

        // 5. Trả về thông báo thành công cho Toast
        $statusMap = [
            'processing' => 'đã được xác nhận và đang chuẩn bị',
            'shipping'   => 'đang được giao đến khách hàng',
            'completed'  => 'đã hoàn thành',
            'cancelled'  => 'đã bị hủy',
        ];

        $msg = "Đơn hàng #{$order->order_code} " . ($statusMap[$request->status] ?? 'đã cập nhật');

        return redirect()->back()->with('success', $msg);
    }
}