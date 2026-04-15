<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Review; // THÊM DÒNG NÀY
use App\Models\CartItem; // THÊM DÒNG NÀY
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class RestaurantController extends Controller
{
    public function show($id)
    {
        // 1. Lấy thông tin quán
        $restaurant = User::findOrFail($id);
        
        // 2. Lấy Menu và nhóm theo danh mục
        $menu = Product::where('user_id', $id)
            ->with(['category', 'options', 'gallery'])
            ->get()
            ->groupBy(fn($item) => $item->category->name);

        // 3. Lấy TẤT CẢ ĐÁNH GIÁ của quán (Thông qua các sản phẩm của quán đó)
        // Chúng ta lấy review của các product mà product đó thuộc về user_id (quán) này
        $reviews = Review::whereHas('product', function ($query) use ($id) {
                $query->where('user_id', $id);
            })
            ->with(['user:id,name', 'product:id,name']) // Lấy tên người dùng và tên món được đánh giá
            ->latest() // Đánh giá mới nhất lên đầu
            ->get();

        // 4. Lấy giỏ hàng thực tế từ Database
        $cartItems = [];
        if (auth()->check()) {
            $cartItems = CartItem::where('user_id', auth()->id())
                ->with('product')
                ->get();
        }

        return Inertia::render('Customer/RestaurantMenu', [
            'restaurant' => $restaurant,
            'menu' => $menu,
            'reviews' => $reviews, // TRUYỀN BIẾN NÀY XUỐNG VUE
            'cartItems' => $cartItems,
            'currentTime' => now()->format('H:i')
        ]);
    }
}