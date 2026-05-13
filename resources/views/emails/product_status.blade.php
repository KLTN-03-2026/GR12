<div style="font-family: sans-serif; padding: 20px; border: 1px solid #eee;">
    <h2 style="color: #f97316;">Chào {{ $product->user->name }}!</h2>

    @if($status === 'approved')
        <p>Xin chúc mừng! Món ăn <strong>"{{ $product->name }}"</strong> đã được Admin duyệt.</p>
        <p>Bây giờ món ăn này sẽ xuất hiện trên hệ thống FoodXpress để khách hàng có thể đặt.</p>
    @else
        <p>Rất tiếc, món ăn <strong>"{{ $product->name }}"</strong> chưa được duyệt.</p>
        <p><strong>Lý do từ chối:</strong> <span style="color: #dc2626;">{{ $product->reject_reason ?? 'Không có lý do cụ thể.' }}</span></p>
        <p>Vui lòng kiểm tra lại thông tin, chỉnh sửa cho phù hợp với chính sách của FoodXpress và gửi lại để được xét duyệt.</p>
    @endif

    <p style="margin-top: 20px; color: #6b7280;">Cảm ơn bạn đã sử dụng FoodXpress.</p>
</div>
