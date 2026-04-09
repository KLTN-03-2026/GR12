<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
   public function handle(Request $request, Closure $next)
    {
        // 1. Kiểm tra xem người dùng đã đăng nhập chưa
        if (auth()->check()) {
            $user = auth()->user();

            // 2. Nếu status KHÔNG PHẢI 'active' và vai trò KHÔNG PHẢI 'customer'
            if ($user->status !== 'active' && $user->role !== 'customer') {
                
                // 3. Quan trọng: Nếu họ đang ở trang chờ rồi thì cho qua (để tránh vòng lặp)
                // Hoặc nếu họ đang nhấn nút Đăng xuất (Logout) thì cũng phải cho qua
                if ($request->routeIs('wait.approval') || $request->routeIs('logout')) {
                    return $next($request);
                }

                // 4. Đẩy họ về trang chờ (Dùng redirect thay vì render)
                return redirect()->route('wait.approval');
            }
        }

        return $next($request);
    }
}
