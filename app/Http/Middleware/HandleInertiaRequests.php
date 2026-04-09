<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Session;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        // Lấy giỏ hàng từ Session
        $cart = Session::get('cart', []);
        
        // Tính tổng số lượng món ăn trong giỏ
        $totalQuantity = 0;
        foreach ($cart as $item) {
            $totalQuantity += $item['quantity'] ?? 0;
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            
            // Chia sẻ dữ liệu giỏ hàng
            'cart' => $cart,
            'cartCount' => $totalQuantity,
            
            // CHỈNH SỬA Ở ĐÂY: Dùng fn () => để Inertia lấy dữ liệu flash mới nhất
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'message' => fn () => $request->session()->get('message'),
            ],
        ];
    }
}