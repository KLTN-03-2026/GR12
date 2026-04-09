<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // 1. Phân quyền (Giữ nguyên logic của bạn - Rất tốt)
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->status !== 'active') return redirect()->route('wait.approval');
            if ($user->role === 'admin') return redirect()->route('admin.dashboard');
            if ($user->role === 'restaurant') return redirect()->route('restaurant.dashboard');
        }

        $search = $request->input('search');
        $categoryId = $request->input('category_id');

        // 2. Lấy danh mục cho Filter Bar
        $categories = Category::where('is_active', true)->get();

        // 3. Lấy sản phẩm (Bản fix logic lọc)
        $products = Product::with(['user', 'category', 'options'])
            ->where(function($query) use ($search) {
                if ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                }
            })
            ->when($categoryId, function($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->latest()
            ->get();

        // 4. Lấy danh sách quán ăn
        $restaurants = User::where('role', 'restaurant')
            ->where('status', 'active')
            ->when($search, function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->get();

        return Inertia::render('Welcome', [
            'restaurants' => $restaurants,
            'products'    => $products,
            'categories'  => $categories,
            'filters'     => $request->only(['search', 'category_id']),
            // Đảm bảo cartCount được handle qua Middleware HandleInertiaRequests như mình đã sửa
        ]);
    }
}