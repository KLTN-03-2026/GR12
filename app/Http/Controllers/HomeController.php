<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // 1. Phân quyền nếu đã đăng nhập
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->status !== 'active') return redirect()->route('wait.approval');
            if ($user->role === 'admin') return redirect()->route('admin.dashboard');
            if ($user->role === 'restaurant') return redirect()->route('restaurant.dashboard');
        }

        $search = $request->input('search');
        $categoryId = $request->input('category_id'); // Lấy thêm ID danh mục

        // 1. Lấy danh sách danh mục để hiện lên thanh lọc (Filter bar)
        $categories = \App\Models\Category::where('is_active', true)->get();

        // 2. Lấy danh sách món ăn với logic lọc nâng cao
        $products = Product::with(['user', 'category', 'options'])
            ->when($search, function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($categoryId, function($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->latest()
            ->get();

        // 3. Quán ăn (chỉ hiện khi tìm kiếm chung hoặc không lọc danh mục cụ thể)
        $restaurants = User::where('role', 'restaurant')
            ->where('status', 'active')
            ->when($search, function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->get();

        return Inertia::render('Welcome', [
            'restaurants' => $restaurants,
            'products'    => $products,
            'categories'  => $categories, // Truyền sang FE
            'filters'     => $request->only(['search', 'category_id']),
        ]);
    }
}