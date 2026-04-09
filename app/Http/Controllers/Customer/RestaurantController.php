<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;      // THÊM DÒNG NÀY
use App\Models\Product;   // THÊM DÒNG NÀY
use Illuminate\Support\Carbon; // THÊM DÒNG NÀY
use Inertia\Inertia;

class RestaurantController extends Controller
{
    //
    // app/Http/Controllers/Customer/RestaurantController.php

    public function show($id)
    {
        // Lấy thông tin quán
        $restaurant = \App\Models\User::findOrFail($id);
        
        // Lấy Menu và nhóm theo danh mục
        $menu = \App\Models\Product::where('user_id', $id)
            ->with(['category', 'options', 'gallery'])
            ->get()
            ->groupBy(fn($item) => $item->category->name);

        // Lấy giỏ hàng thực tế từ Database
        $cartItems = [];
        if (auth()->check()) {
            $cartItems = \App\Models\CartItem::where('user_id', auth()->id())
                ->with('product')
                ->get();
        }

        return \Inertia\Inertia::render('Customer/RestaurantMenu', [
            'restaurant' => $restaurant,
            'menu' => $menu,
            'cartItems' => $cartItems, // Quan trọng để đồng bộ nút giỏ hàng
            'currentTime' => now()->format('H:i')
        ]);
    }
}
