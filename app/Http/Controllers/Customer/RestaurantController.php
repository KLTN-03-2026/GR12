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
    public function show($id)
    {
        // Lấy thông tin quán ăn
        $restaurant = User::where('role', 'restaurant')
                        ->where('status', 'active')
                        ->findOrFail($id);

        // Lấy danh sách món ăn của quán đó, phân nhóm theo danh mục
        $products = Product::where('user_id', $id)
                        ->where('is_available', true)
                        ->with('category', 'options') // Lấy luôn thông tin danh mục và tùy chọn
                        ->get()
                        ->groupBy('category.name');

        return Inertia::render('Customer/RestaurantMenu', [
            'restaurant' => $restaurant,
            'menu' => $products,
            'currentTime' => now()->format('H:i') // Gửi giờ hiện tại để check ẩn/hiện
        ]);
    }
}
