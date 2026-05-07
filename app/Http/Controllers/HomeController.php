<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\Voucher;
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
            if ($user->role === 'shipper') return redirect()->route('shipper.dashboard');
        }

        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $activeTab = $request->input('active_tab');
        $priceRange = $request->input('price_range');
        $deliveryType = $request->input('delivery_type');

        // 2. Lấy danh mục cho Filter Bar
        $categories = Category::where('is_active', true)->get();

        $currentTime = now()->format('H:i:s');

        // 3. Lấy sản phẩm (Bản fix logic lọc)
        $products = Product::with(['user', 'category', 'options'])
            ->visible()
            ->whereHas('user', function($query) use ($currentTime) {
                $query->where('is_accepting_orders', true)
                      ->whereTime('opening_time', '<=', $currentTime)
                      ->whereTime('closing_time', '>=', $currentTime);
            })
            ->when($search, function($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, function($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($activeTab && $activeTab !== 'Tất cả', function($query) use ($activeTab) {
                $query->whereHas('category', function ($query) use ($activeTab) {
                    $query->where('name', $activeTab);
                });
            })
            ->when($priceRange, function($query) use ($priceRange) {
                if ($priceRange === '0-50000') {
                    $query->where('price', '<', 50000);
                } elseif ($priceRange === '50000-100000') {
                    $query->whereBetween('price', [50000, 100000]);
                } elseif ($priceRange === '100000+') {
                    $query->where('price', '>', 100000);
                }
            })
            ->latest()
            ->get();

        // 4. Lấy danh sách quán ăn
        $restaurants = User::where('role', 'restaurant')
            ->where('status', 'active')
            ->where('is_accepting_orders', true)
            ->whereTime('opening_time', '<=', $currentTime)
            ->whereTime('closing_time', '>=', $currentTime)
            ->when($search, function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->get();

        // 5. Lấy danh sách Voucher còn hạn
        $vouchers = Voucher::where('expires_at', '>', now())->orderBy('created_at', 'desc')->get();

        return Inertia::render('Welcome', [
            'restaurants' => $restaurants,
            'products'    => $products,
            'categories'  => $categories,
            'vouchers'    => $vouchers,
            'filters'     => $request->only(['search', 'category_id', 'active_tab', 'price_range', 'delivery_type']),
            // Đảm bảo cartCount được handle qua Middleware HandleInertiaRequests như mình đã sửa
        ]);
    }
}