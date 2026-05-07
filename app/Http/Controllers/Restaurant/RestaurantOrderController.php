<?php

namespace App\Http\Controllers\Restaurant;

    use App\Http\Controllers\Controller;
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

            // Lấy các đơn hàng có chứa sản phẩm thuộc về nhà hàng này và chưa hoàn tất/hủy
            $orders = Order::whereHas('items.product', function ($query) use ($restaurantId) {
                $query->where('user_id', $restaurantId);
            })
            ->whereNotIn('status', ['completed', 'cancelled'])
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
         * Hiển thị lịch sử đơn hàng đã kết thúc
         */
        public function history()
        {
            $restaurantId = Auth::id();

            // Lấy các đơn hàng đã hoàn tất hoặc bị hủy
            $orders = Order::whereHas('items.product', function ($query) use ($restaurantId) {
                $query->where('user_id', $restaurantId);
            })
            ->whereIn('status', ['completed', 'cancelled'])
            ->with(['user', 'items' => function($query) use ($restaurantId) {
                $query->whereHas('product', function($q) use ($restaurantId) {
                    $q->where('user_id', $restaurantId);
                })->with('product');
            }])
            ->latest('updated_at')
            ->paginate(15);

            return Inertia::render('Restaurant/Orders/History', [
                'orders' => $orders
            ]);
        }

        /**
         * Chi tiết đơn hàng dành cho chủ nhà hàng
         */
        public function show($id)
        {
            $restaurantId = Auth::id();

            $order = Order::whereHas('items.product', function ($query) use ($restaurantId) {
                $query->where('user_id', $restaurantId);
            })
            ->with(['user', 'shipper.user', 'items' => function($query) use ($restaurantId) {
                $query->whereHas('product', function($q) use ($restaurantId) {
                    $q->where('user_id', $restaurantId);
                })->with('product');
            }])
            ->findOrFail($id);

            return Inertia::render('Restaurant/Orders/Show', [
                'order' => $order
            ]);
        }

        /**
         * Cập nhật trạng thái đơn hàng theo quy trình mới
         *
         * Quy trình:
         * - pending: Khách đặt đơn (chờ gán shipper)
         * - assigned: Hệ thống TỰ ĐỘNG gán shipper khi khách đặt (không chờ quán)
         * - confirmed: Quán xác nhận đã làm xong món ăn (CHỜ SHIPPER NHẬN)
         * - shipping: Shipper đã nhận và đang đi tới quán
         * - picked_up: Shipper đã lấy hàng
         * - completed: Hoàn tất
         * - cancelled: Hủy
         */
        public function updateStatus(Request $request, $id)
        {
            // 1. Validate dữ liệu đầu vào
            $request->validate([
                'status' => 'sometimes|in:assigned,confirmed,cancelled',
                'is_food_ready' => 'sometimes|boolean'
            ]);

            $restaurantId = Auth::id();

            // 2. Tìm đơn hàng và kiểm tra quyền sở hữu
            $order = Order::whereHas('items.product', function ($query) use ($restaurantId) {
                $query->where('user_id', $restaurantId);
            })->findOrFail($id);

            $oldStatus = $order->status;

            if ($request->has('is_food_ready') && $request->is_food_ready) {
                $updateData = [
                    'is_food_ready' => true,
                    'confirmed_at' => now()
                ];
                
                // Chỉ đổi status sang confirmed nếu đang ở trạng thái pending/assigned. Nếu đã shipping thì giữ nguyên.
                if (in_array($oldStatus, ['pending', 'assigned'])) {
                    $updateData['status'] = 'confirmed';
                }
                
                $order->update($updateData);

                // Gửi thông báo
                if ($oldStatus !== $order->status) {
                    $order->user->notify(new \App\Notifications\OrderStatusUpdated($order, $oldStatus, $order->status));
                    if ($order->shipper) {
                        $order->shipper->user->notify(new \App\Notifications\OrderStatusUpdated($order, $oldStatus, $order->status));
                    }
                    event(new \App\Events\OrderStatusUpdated($order));
                }

                return redirect()->back()->with('success', "Đơn hàng #{$order->order_code} đã sẵn sàng, đang chờ shipper tới nhận.");
            }

            $newStatus = $request->status;
            if (!$newStatus) {
                return redirect()->back();
            }

            // 3. Cập nhật trạng thái
            $updateData = ['status' => $newStatus];

            // Cập nhật timestamps theo giai đoạn
            if ($newStatus === 'confirmed' && $oldStatus !== 'confirmed') {
                $updateData['confirmed_at'] = now();
            }

            if ($newStatus === 'picked_up' && $oldStatus !== 'picked_up') {
                $updateData['picked_up_at'] = now();
            }

            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                $updateData['completed_at'] = now();
            }

            $order->update($updateData);

            // 5. Gửi thông báo cho khách hàng và shipper nếu status thay đổi
            if ($oldStatus !== $newStatus) {
                $order->user->notify(new \App\Notifications\OrderStatusUpdated($order, $oldStatus, $newStatus));
                
                // Gửi thông báo cho shipper nếu có
                if ($order->shipper) {
                    $order->shipper->user->notify(new \App\Notifications\OrderStatusUpdated($order, $oldStatus, $newStatus));
                }
                event(new \App\Events\OrderStatusUpdated($order));
            }

            // 6. Trả về thông báo thành công cho Toast
            $statusMap = [
                'pending'      => 'đơn hàng vừa được đặt, chờ hệ thống gán shipper',
                'assigned'     => 'đã gán shipper, chờ quán xác nhận đã làm xong',
                'confirmed'    => 'quán đã xác nhận đã làm xong, đang chờ shipper nhận đơn',
                'shipping'     => 'shipper đã nhận đơn và đang đi tới quán',
                'picked_up'    => 'shipper đã lấy hàng, đang giao đến khách',
                'completed'    => 'đơn hàng đã hoàn thành',
                'cancelled'    => 'đơn hàng đã bị hủy',
            ];

            $msg = "Đơn hàng #{$order->order_code} " . ($statusMap[$newStatus] ?? 'đã cập nhật');

            return redirect()->back()->with('success', $msg);
        }
    }
    