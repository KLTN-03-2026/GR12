<div style="font-family: sans-serif; padding: 20px; border: 1px solid #eee;">
    <h2 style="color: #f97316;">Chào {{ $user->name }}!</h2>
    @if($status === 'active')
        <p>Chúc mừng bạn! Tài khoản của bạn đã được Admin phê duyệt.</p>
        <p>Bây giờ bạn đã có thể đăng nhập và bắt đầu kinh doanh trên <b>FoodXpress</b>.</p>
        <a href="{{ url('/login') }}" style="background: #f97316; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Đăng nhập ngay</a>
    @else
        <p>Rất tiếc, hồ sơ đăng ký của bạn không phù hợp với tiêu chí của chúng tôi tại thời điểm này.</p>
        <p>Cảm ơn bạn đã quan tâm đến FoodXpress.</p>
    @endif
</div>