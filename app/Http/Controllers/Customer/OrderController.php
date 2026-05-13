<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ShipperController;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class OrderController extends Controller
{
    // app/Http/Controllers/Customer/OrderController.php

    public function index()
    {
        $userId = auth()->id();

        // Đơn đang đặt (active)
        $activeOrders = Order::where('user_id', $userId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with(['items.product.reviews.user'])
            ->latest()
            ->get();

        // Đơn đã đặt (past)
        $pastOrders = Order::where('user_id', $userId)
            ->whereIn('status', ['completed', 'cancelled'])
            ->with(['items.product.reviews.user'])
            ->latest()
            ->paginate(5);

        return Inertia::render('Customer/Orders', [
            'activeOrders' => $activeOrders,
            'pastOrders' => $pastOrders
        ]);
    }

    public function show($id)
    {
        $order = Order::where('user_id', auth()->id())
            ->with(['items.product.user', 'shipper.user', 'user'])
            ->findOrFail($id);

        return Inertia::render('Customer/OrderDetail', [
            'order' => $order
        ]);
    }

    public function confirmDelivery($id)
    {
        $order = Order::where('user_id', auth()->id())
            ->whereIn('status', ['shipping', 'picked_up'])
            ->findOrFail($id);

        $order->update(['status' => 'completed']);

        return response()->json(['message' => 'Delivery confirmed']);
    }

    /**
     * API Endpoint: Lấy chi tiết đơn hàng real-time (dành cho front-end polling)
     */
    public function getOrderDetails($id)
    {
        $order = Order::where('user_id', auth()->id())
            ->with([
                'items.product.user',
                'shipper.user',
                'user'
            ])
            ->findOrFail($id);

        $restaurantUser = $order->items->first()?->product?->user;

        return response()->json([
            'id' => $order->id,
            'order_code' => $order->order_code,
            'address' => $order->address,
            'phone' => $order->phone,
            'note' => $order->note,
            'status' => $order->status,
            'subtotal' => $order->subtotal,
            'shipping_fee' => $order->shipping_fee,
            'service_fee' => $order->service_fee,
            'packaging_fee' => $order->packaging_fee,
            'shipper_tip' => $order->shipper_tip,
            'discount_amount' => $order->discount_amount,
            'total' => $order->total,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'created_at' => $order->created_at,
            'restaurant_revenue' => $order->restaurant_revenue,
            'restaurant_tax_fee' => $order->restaurant_tax_fee,
            'restaurant_commission_rate' => $order->restaurant_commission_rate,
            'restaurant' => [
                'name' => $restaurantUser?->restaurant_name ?? $restaurantUser?->name ?? 'Nhà hàng',
                'address' => $restaurantUser?->address ?? '',
            ],
            'items' => $order->items->map(fn($item) => [
                'id' => $item->id,
                'product' => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'image' => $item->product->image,
                    'price' => $item->product->price,
                ],
                'quantity' => $item->quantity,
                'price' => $item->price,
            ]),
            'shipper' => $order->shipper ? [
                'id' => $order->shipper->id,
                'status' => $order->shipper->status,
                'current_latitude' => $order->shipper->current_latitude,
                'current_longitude' => $order->shipper->current_longitude,
                'user' => [
                    'name' => $order->shipper->user->name,
                    'phone' => $order->shipper->user->phone,
                    'profile_photo_path' => $order->shipper->user->profile_photo_path,
                ]
            ] : null,
            'user' => [
                'name' => $order->user->name,
                'latitude' => $order->user->latitude,
                'longitude' => $order->user->longitude,
            ]
        ]);
    }

    /**
     * Hiển thị trang thanh toán (Checkout)
     */
    public function checkout()
    {
        $user = Auth::user();
        
        // Lấy giỏ hàng kèm thông tin món và quán ăn
        $cartItems = CartItem::where('user_id', $user->id)
            ->with(['product.user'])
            ->get();

        // Nếu giỏ hàng trống thì không cho vào trang checkout
        if ($cartItems->isEmpty()) {
            return redirect()->route('home')->with('error', 'Giỏ hàng của bạn đang trống, hãy chọn món trước nhé!');
        }

        // Lấy thông tin quán ăn từ sản phẩm đầu tiên trong giỏ hàng
        $restaurant = $cartItems->first()->product->user;

        $vouchers = Voucher::active()
            ->orderBy('expires_at', 'asc')
            ->get();

        $settings = [
            'base_shipping_fee' => \App\Models\Setting::getValue('base_shipping_fee', 15000),
            'base_radius_km' => \App\Models\Setting::getValue('base_radius_km', 2),
            'extra_shipping_fee_per_km' => \App\Models\Setting::getValue('extra_shipping_fee_per_km', 3000),
        ];

        return Inertia::render('Customer/Checkout', [
            'cartItems' => $cartItems,
            'user' => $user,
            'restaurant' => $restaurant,
            'vouchers' => $vouchers,
            'settings' => $settings,
        ]);
    }

    /**
     * Xử lý lưu đơn hàng vào Database
     */
    public function store(Request $request)
    {
        \Log::info('Order store request', $request->all());
        // 1. Validate thông tin khách hàng nhập
            $request->validate([
            'address' => 'required|string|min:10',
            'phone' => 'required|numeric|digits_between:10,11',
            'note' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cod,cash,vnpay,wallet',
            'vnpay_bank_code' => 'nullable|string',
            'voucher_code' => 'nullable|string|exists:vouchers,code',
            'shipper_tip' => 'nullable|numeric|min:0',
        ], [
            'address.required' => 'Bạn quên nhập địa chỉ rồi!',
            'address.min' => 'Địa chỉ cần chi tiết hơn một chút nhé.',
            'phone.required' => 'Số điện thoại là bắt buộc để shipper gọi bạn.',
            'phone.numeric' => 'Số điện thoại chỉ được chứa chữ số thôi.',
            'phone.digits_between' => 'Số điện thoại phải từ 10-11 số.',
        ]);

        $user = Auth::user();
        
        // Lấy lại giỏ hàng để tính toán giá tiền (tránh bị hack giá từ client)
        $cartItems = CartItem::where('user_id', $user->id)
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('home')->with('error', 'Giỏ hàng đã hết hạn hoặc trống!');
        }

        // 2. Tính toán tổng tiền
        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });
        
        // Validate subtotal > 0
        if ($subtotal <= 0) {
            \Log::error('Invalid subtotal calculation', [
                'user_id' => $user->id,
                'cart_items_count' => $cartItems->count(),
                'subtotal' => $subtotal,
            ]);
            return redirect()->back()->with('error', 'Lỗi tính toán giá, vui lòng thử lại.');
        }
        
        // Tính phí ship dựa trên khoảng cách từ quán đến khách hàng
        $restaurant = $cartItems->first()->product->user;
        $restaurantId = $restaurant->id;
        $customerLat = $request->latitude;
        $customerLng = $request->longitude;
        $restaurantLat = $restaurant->latitude;
        $restaurantLng = $restaurant->longitude;

        $distance = 0;

        if ($customerLat && $customerLng && $restaurantLat && $restaurantLng) {
            $distance = $this->distanceBetweenCoordinates($restaurantLat, $restaurantLng, $customerLat, $customerLng);
            
            $baseFee = \App\Models\Setting::getValue('base_shipping_fee', 15000);
            $baseRadiusKm = \App\Models\Setting::getValue('base_radius_km', 2);
            $extraFeePerKm = \App\Models\Setting::getValue('extra_shipping_fee_per_km', 3000);
            
            $additionalFee = max(0, $distance - $baseRadiusKm) * $extraFeePerKm;
            $shippingFee = $baseFee + $additionalFee;
        } else {
            $shippingFee = \App\Models\Setting::getValue('base_shipping_fee', 15000); // Fallback nếu không có vị trí
        }

        $voucherCode = $request->voucher_code;
        $discountAmount = 0;

        if ($voucherCode) {
            $voucher = Voucher::where('code', $voucherCode)
                ->where('expires_at', '>', now())
                ->first();

            if (!$voucher || !$voucher->isAvailable()) {
                return redirect()->back()->withErrors([
                    'voucher_code' => 'Mã voucher không hợp lệ, đã hết lượt sử dụng hoặc đã hết hạn.',
                ])->withInput();
            }

            if ($voucher->minimum_product_price && !$cartItems->contains(function ($item) use ($voucher) {
                return $item->product->price >= $voucher->minimum_product_price;
            })) {
                return redirect()->back()->withErrors([
                    'voucher_code' => 'Voucher chỉ áp dụng cho sản phẩm có giá từ ' . number_format($voucher->minimum_product_price, 0, ',', '.') . ' trở lên.',
                ])->withInput();
            }

            if ($voucher->restaurant_id && $voucher->restaurant_id !== $restaurantId) {
                return redirect()->back()->withErrors([
                    'voucher_code' => 'Mã giảm giá này không áp dụng cho quán ăn bạn đang chọn.',
                ])->withInput();
            }

            if ($voucher->discount_type === 'percent') {
                $discountAmount = round($subtotal * ($voucher->discount_value / 100), 2);
            } else {
                $discountAmount = min($subtotal, $voucher->discount_value);
            }
        }

        $serviceFee = 3000;
        $packagingFee = 0; // Flat packaging fee for now, can be updated later
        $shipperTip = $request->shipper_tip ? floatval($request->shipper_tip) : 0;

        $total = $subtotal + $shippingFee + $serviceFee + $packagingFee + $shipperTip - $discountAmount;

        // Tính toán doanh thu
        $platformCommissionPercent = \App\Models\Setting::getValue('platform_commission', 20);
        $restaurantCommissionRate = $platformCommissionPercent / 100;
        $restaurantTaxRate = 0.045; // Fixed tax rate for now or can be added to settings later
        
        $commissionFee = $subtotal * $restaurantCommissionRate;
        $restaurantTaxFee = $subtotal * $restaurantTaxRate;
        
        // Phân bổ phần giảm giá (Voucher của hệ thống thì sàn chịu, voucher của quán thì quán chịu)
        $restaurantVoucherDiscount = 0;
        $adminVoucherDiscount = 0;
        if ($discountAmount > 0 && isset($voucher)) {
            if ($voucher->restaurant_id) {
                $restaurantVoucherDiscount = $discountAmount;
            } else {
                $adminVoucherDiscount = $discountAmount;
            }
        }

        // Quán nhận: Tiền món + Phí đóng gói - Chiết khấu - Thuế - Giảm giá của quán (nếu có)
        $restaurantRevenue = $subtotal + $packagingFee - $commissionFee - $restaurantTaxFee - $restaurantVoucherDiscount;
        
        // Admin nhận: Chiết khấu + Thuế (giữ hộ) + Phí dịch vụ - Giảm giá của hệ thống
        $adminRevenue = $commissionFee + $restaurantTaxFee + $serviceFee - $adminVoucherDiscount;

        try {
            // 3. Sử dụng Transaction để đảm bảo tính toàn vẹn dữ liệu
            DB::beginTransaction();

            // Cập nhật địa chỉ người dùng vào profile
            $user->update([
                'address' => $request->address,
                'phone' => $request->phone,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            // Tạo đơn hàng chính
            $order = Order::create([
                'user_id' => $user->id,
                'order_code' => 'FXP-' . strtoupper(Str::random(8)),
                'address' => $request->address,
                'phone' => $request->phone,
                'note' => $request->note,
                'voucher_code' => $voucherCode,
                'subtotal' => $subtotal,
                'distance' => $distance,
                'shipping_fee' => $shippingFee,
                'service_fee' => $serviceFee,
                'packaging_fee' => $packagingFee,
                'shipper_tip' => $shipperTip,
                'discount_amount' => $discountAmount,
                'total' => $total,
                'restaurant_commission_rate' => $restaurantCommissionRate,
                'restaurant_tax_fee' => $restaurantTaxFee,
                'restaurant_revenue' => $restaurantRevenue,
                'admin_revenue' => $adminRevenue,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'vnpay' ? 'pending' : 'paid',
                'status' => 'pending', // Giai đoạn 1: Đặt hàng & Tiếp nhận
            ]);


            if (isset($voucher)) {
                $lockedVoucher = Voucher::where('id', $voucher->id)->lockForUpdate()->first();
                if ($lockedVoucher && $lockedVoucher->isAvailable()) {
                    $lockedVoucher->increment('used_count');
                }
            }

            // Lưu chi tiết từng món trong đơn hàng
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                    'options' => $cartItem->options, // Lưu Topping khách đã chọn
                ]);
            }

            // Reload items to ensure they are available for the notification
            $order->load('items.product');

            // Gửi thông báo cho chủ quán khi có đơn hàng mới
            $restaurant = $cartItems->first()->product->user;
            $restaurant->notify(new \App\Notifications\NewOrderReceived($order));
            event(new \App\Events\NewOrderReceived($order, $restaurant->id));


            // 🔄 HỆ THỐNG TỰ ĐỘNG GÁN SHIPPER khi khách đặt hàng
            // Gọi sau khi đã lưu OrderItem để relation có thể load được
            $shipperController = new ShipperController();
            $assigned = $shipperController->assignShipperToOrder($order->id);

            if ($assigned) {
                $order->refresh();
                // Nếu tìm được shipper, đơn sẽ ở trạng thái assigned
                $order->update(['status' => 'assigned']);
                $user->notify(new \App\Notifications\OrderStatusUpdated($order, 'pending', 'assigned'));
                
                if ($order->shipper && $order->shipper->user) {
                    $order->shipper->user->notify(new \App\Notifications\OrderStatusUpdated($order, 'pending', 'assigned'));
                }
            } else {
                // Nếu không tìm được shipper, đơn vẫn ở trạng thái pending
                $user->notify(new \App\Notifications\OrderStatusUpdated($order, 'new', 'pending'));
            }

            // 4. Xóa sạch giỏ hàng của user sau khi đặt thành công
            // PHẢI nằm TRONG transaction để đảm bảo nếu có lỗi, cart items vẫn được giữ lại
            CartItem::where('user_id', $user->id)->delete();

            // 4.5. Xử lý thanh toán qua Ví FoodXpress
            if ($request->payment_method === 'wallet') {
                if ($user->wallet_balance < $total) {
                    throw new \Exception('Số dư ví không đủ để thanh toán.');
                }
                
                $balanceBefore = $user->wallet_balance;
                $user->wallet_balance -= $total;
                $user->save();
                
                \App\Models\WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdraw',
                    'amount' => -$total,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $user->wallet_balance,
                    'status' => 'completed',
                    'description' => 'Thanh toán đơn hàng ' . $order->order_code,
                ]);
            }

            DB::commit();

            // 5. Xử lý thanh toán
            \Log::info('Order created', ['order_id' => $order->id, 'payment_method' => $order->payment_method]);
            if ($order->payment_method === 'vnpay') {
                \Log::info('Processing VNPay for order ' . $order->id);
                try {
                    $paymentUrl = $this->createVNPayUrl($order, $request->vnpay_bank_code);
                    \Log::info('VNPay URL generated: ' . $paymentUrl);
                    return \Inertia\Inertia::location($paymentUrl);
                } catch (\Exception $e) {
                    \Log::error('VNPay error: ' . $e->getMessage());
                    return redirect()->back()->with('error', 'Lỗi tạo thanh toán VNPay. Vui lòng thử lại.');
                }
            } else {
                // 5. Điều hướng về trang chi tiết đơn hàng
                return redirect()->route('orders.show', $order->id)->with('success', 'Đơn hàng ' . $order->order_code . ' đã được đặt thành công! Chờ shipper nhé 🛵');
            }

        } catch (\Exception $e) {
            // Nếu có bất kỳ lỗi nào, hủy bỏ toàn bộ quá trình lưu
            DB::rollBack();
            \Log::error('Order creation failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Rất tiếc, đã có lỗi xảy ra khi tạo đơn hàng. Vui lòng thử lại!');
        }
    }

    /**
     * Tính khoảng cách giữa hai điểm tọa độ bằng công thức Haversine (km)
     */
    private function distanceBetweenCoordinates($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadiusKm * $c;

        return $distance;
    }

    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', auth()->id())->findOrFail($id);

        // Chỉ cho phép hủy nếu chưa giao
        if (in_array($order->status, ['shipping', 'picked_up', 'delivering', 'completed'])) {
            return response()->json(['error' => 'Không thể hủy đơn hàng đã giao.'], 400);
        }

        $oldStatus = $order->status;
        $order->update(['status' => 'cancelled']);

        // Gửi thông báo
        $order->user->notify(new \App\Notifications\OrderStatusUpdated($order, $oldStatus, 'cancelled'));

        // Nếu đã thanh toán qua VNPay hoặc Ví, tự động hoàn tiền vào Ví FoodXpress
        if ($order->payment_status === 'paid' && !in_array($order->payment_method, ['cash', 'cod'])) {
            $user = $order->user;
            $refundAmount = $order->total;
            $balanceBefore = $user->wallet_balance;

            $user->wallet_balance += $refundAmount;
            $user->save();

            \App\Models\WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $refundAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $user->wallet_balance,
                'status' => 'completed',
                'description' => 'Hoàn tiền vào ví do đơn hàng ' . $order->order_code . ' bị hủy.',
            ]);

            $order->update(['payment_status' => 'refunded']);
            
            // Gửi thông báo hoàn tiền
            $user->notify(new \App\Notifications\SystemNotification(
                'Hoàn tiền thành công',
                'Đơn hàng ' . $order->order_code . ' đã bị hủy. Số tiền ' . number_format($refundAmount) . 'đ đã được hoàn vào Ví FoodXpress của bạn.',
                'success'
            ));
        }

        return response()->json(['message' => 'Đơn hàng đã được hủy.']);
    }

    private function createVNPayUrl($order, $bankCode = '')
    {
        $vnp_TmnCode = config('vnpay.tmn_code');
        $vnp_HashSecret = config('vnpay.hash_secret');
        $vnp_Url = config('vnpay.url');
        $vnp_Returnurl = config('vnpay.return_url');
        $vnp_IpnUrl = config('vnpay.ipn_url');

        \Log::info('VNPay config', [
            'tmn_code' => $vnp_TmnCode,
            'hash_secret' => $vnp_HashSecret ? 'set' : 'not set',
            'url' => $vnp_Url,
            'return_url' => $vnp_Returnurl,
        ]);

        if (!$vnp_TmnCode || !$vnp_HashSecret) {
            throw new \Exception('VNPay config missing');
        }

        $vnp_TxnRef = $order->order_code;
        $vnp_OrderInfo = 'Thanh toan don hang ' . $order->order_code;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $order->total * 100;
        $vnp_Locale = 'vn';
        $vnp_BankCode = $bankCode ?? '';
        $vnp_IpAddr = request()->ip();

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => date('YmdHis', strtotime('+15 minutes')),
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }
}
