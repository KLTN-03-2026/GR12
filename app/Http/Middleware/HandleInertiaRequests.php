<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\CartItem; // Nhớ import Model này vào nhé
use App\Models\Order;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            
            // Chia sẻ dữ liệu giỏ hàng từ DATABASE thay vì Session
            'cart' => function () use ($user) {
                return $user 
                    ? CartItem::where('user_id', $user->id)->with('product')->get() 
                    : [];
            },

            'cartCount' => function () use ($user) {
                return $user 
                    ? CartItem::where('user_id', $user->id)->sum('quantity') 
                    : 0;
            },
            
            'newOrdersCount' => function () use ($user) {
                if (! $user || $user->role !== 'restaurant') {
                    return 0;
                }

                return Order::whereHas('items.product', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->whereIn('status', ['pending', 'processing'])
                ->count();
            },
            
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'message' => fn () => $request->session()->get('message'),
            ],
        ];
    }
}