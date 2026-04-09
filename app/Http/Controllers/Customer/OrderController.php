<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
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
        $orders = Order::where('user_id', auth()->id())
            ->with(['items.product']) // Lấy kèm chi tiết món ăn
            ->latest() // Đơn hàng mới nhất lên đầu
            ->get();

        return Inertia::render('Customer/Orders', [
            'orders' => $orders
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

        return Inertia::render('Customer/Checkout', [
            'cartItems' => $cartItems,
            'user' => $user
        ]);
    }

    /**
     * Xử lý lưu đơn hàng vào Database
     */
    public function store(Request $request)
    {
        // 1. Validate thông tin khách hàng nhập
            $request->validate([
            'address' => 'required|string|min:10',
            'phone' => 'required|numeric|digits_between:10,11',
            'payment_method' => 'required',
        ], [
            'address.required' => 'Hoàng Anh ơi, quên nhập địa chỉ rồi!',
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
        $shippingFee = 15000; // Phí ship cố định (Hoàng Anh có thể chỉnh lại)
        $total = $subtotal + $shippingFee;

        try {
            // 3. Sử dụng Transaction để đảm bảo tính toàn vẹn dữ liệu
            DB::beginTransaction();

            // Tạo đơn hàng chính
            $order = Order::create([
                'user_id' => $user->id,
                'order_code' => 'FXP-' . strtoupper(Str::random(8)),
                'address' => $request->address,
                'phone' => $request->phone,
                'note' => $request->note,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'status' => 'pending', // Trạng thái chờ xử lý
            ]);

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

            // 4. Xóa sạch giỏ hàng của user sau khi đặt thành công
            CartItem::where('user_id', $user->id)->delete();

            DB::commit();

            // 5. Điều hướng về trang chủ và hiện Toast "hàng hiệu"
            return redirect()->route('home')->with('success', 'Đơn hàng ' . $order->order_code . ' đã được đặt thành công! Chờ shipper nhé 🛵');

        } catch (\Exception $e) {
            // Nếu có bất kỳ lỗi nào, hủy bỏ toàn bộ quá trình lưu
            DB::rollBack();
            
            return redirect()->back()->with('error', 'Rất tiếc, đã có lỗi xảy ra khi tạo đơn hàng. Vui lòng thử lại!');
        }
    }
}