<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();

        // 1. KIỂM TRA TRẠNG THÁI TÀI KHOẢN
        if ($user->role !== 'customer' && $user->status !== 'active') {
            return redirect()->route('wait.approval');
        }

        // 2. ĐIỀU HƯỚNG DỰA TRÊN VAI TRÒ (ROLE)
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'restaurant') {
            return redirect()->route('restaurant.dashboard');
        }

        if ($user->role === 'shipper') {
            // Tạm thời để home hoặc dashboard shipper nếu có
            return redirect()->route('home'); 
        }

        // 3. MẶC ĐỊNH CHO KHÁCH HÀNG (CUSTOMER)
        // QUAN TRỌNG: Sửa từ route('dashboard') thành route('home') 
        // để đưa khách về trang Welcome sau khi đăng nhập thành công.
        return redirect()->intended(route('home'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Đăng xuất xong đẩy về trang chủ công cộng
        return redirect('/');
    }
}