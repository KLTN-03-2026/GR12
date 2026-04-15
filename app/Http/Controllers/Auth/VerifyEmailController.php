<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        // 1. Nếu người dùng đã xác nhận rồi, chuyển về trang chủ
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home', absolute: false).'?verified=1');
        }

        // 2. Thực hiện đánh dấu đã xác nhận trong Database
        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // 3. Sau khi xác nhận thành công, chuyển về trang chủ
        // Mình đổi 'dashboard' thành 'home' để khớp với web.php của bạn
        return redirect()->intended(route('home', absolute: false).'?verified=1');
    }
}