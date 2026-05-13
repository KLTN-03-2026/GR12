<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Xem danh sách giỏ hàng
     */
    public function index()
    {
        // Lấy giỏ hàng kèm thông tin món ăn và quán sở hữu món đó
        $cartItems = CartItem::where('user_id', Auth::id())
            ->with(['product.user']) 
            ->latest()
            ->get();

        $settings = [
            'base_shipping_fee' => \App\Models\Setting::getValue('base_shipping_fee', 15000),
        ];

        return Inertia::render('Customer/Cart', [
            'cartItems' => $cartItems,
            'settings' => $settings,
        ]);
    }

    /**
     * Thêm vào giỏ hàng
     */
    public function add(Request $request)
    {
        // 1. Kiểm tra dữ liệu đầu vào
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'options'    => 'nullable|array',
            'note'       => 'nullable|string',
        ]);

        $userId = Auth::id();
        $productId = $request->product_id;
        
        // 2. Lấy thông tin sản phẩm và quán sở hữu
        $product = Product::with('user')->find($productId);
        $restaurantId = $product->user_id;
        
        // 3. Kiểm tra giỏ hàng hiện tại - chỉ cho phép mua từ 1 quán
        $existingCartItems = CartItem::where('user_id', $userId)
            ->with('product.user')
            ->get();
            
        if ($existingCartItems->isNotEmpty()) {
            $currentRestaurantId = $existingCartItems->first()->product->user_id;
            if ($currentRestaurantId !== $restaurantId) {
                return redirect()->back()->with('error', 'Bạn chỉ có thể đặt hàng từ một quán trong một lần. Vui lòng xóa giỏ hàng hiện tại hoặc hoàn tất đơn hàng trước khi đặt từ quán khác.');
            }
        }
        
        // 4. Chuẩn hóa Options: Sắp xếp theo ID để so sánh mảng cho chính xác
        // Nếu không có options, ta để mảng trống [] thay vì null để dễ so sánh trong DB
        $options = $request->options 
            ? collect($request->options)->sortBy('id')->values()->toArray() 
            : [];

        /**
         * 5. Tìm món đã tồn tại với CÙNG tùy chọn (options)
         * Chúng ta lấy toàn bộ món cùng product_id trong giỏ ra rồi dùng filter của Collection
         * để so sánh mảng options, tránh việc so sánh JSON thô bị lệch thứ tự.
         */
        $existingItem = CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->get()
            ->filter(function ($item) use ($options) {
                // Đảm bảo cả hai đều là mảng khi so sánh
                $itemOptions = $item->options ?? [];
                return $itemOptions === $options;
            })
            ->first();

        if ($existingItem) {
            // Nếu đã có món y hệt (cùng topping), chỉ cần tăng số lượng
            $existingItem->increment('quantity', $request->quantity);
        } else {
            // Nếu chưa có, tạo mới
            CartItem::create([
                'user_id'    => $userId,
                'product_id' => $productId,
                'quantity'   => $request->quantity,
                'options'    => $options,
                'note'       => $request->note,
            ]);
        }

        // Trả về kèm thông báo thành công
        return redirect()->back()->with('success', 'Ngon lành! Món ăn đã nằm trong giỏ.');
    }

    /**
     * Cập nhật số lượng trong giỏ
     */
    public function update(Request $request, CartItem $cartItem)
    {
        // Đảm bảo chỉ người sở hữu giỏ hàng mới cập nhật được
        if ($cartItem->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate(['quantity' => 'required|integer|min:1']);
        
        $cartItem->update(['quantity' => $request->quantity]);

        return redirect()->back();
    }

    /**
     * Xóa món khỏi giỏ
     */
    public function destroy(CartItem $cartItem)
    {
        if ($cartItem->user_id !== Auth::id()) {
            abort(403);
        }

        $cartItem->delete();

        return redirect()->back()->with('success', 'Đã xóa món khỏi giỏ hàng.');
    }
}