<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    /**
     * Lưu đánh giá mới từ khách hàng.
     */
    // app/Http/Controllers/Customer/ReviewController.php

    public function store(Request $request)
    {
        // 1. Tìm món ăn để xác định chủ quán (restaurant)
        $product = \App\Models\Product::findOrFail($request->product_id);
        
        // ID của chủ quán chính là user_id trong bảng products
        $restaurantId = $product->user_id; 

        // 2. Kiểm tra điều kiện mua hàng
        // Tìm trong bảng order_items: đã có đơn hàng hoàn thành chứa sản phẩm này chưa
        $hasPurchased = \App\Models\OrderItem::where('product_id', $request->product_id)
            ->whereHas('order', function ($query) {
                $query->where('user_id', auth()->id())
                    ->where('status', 'completed');
            })
            ->exists();

        if (!$hasPurchased) {
            return back()->with('error', 'Bạn chỉ có thể đánh giá sau khi đã mua và hoàn thành đơn hàng sản phẩm này!');
        }

        // 3. Tiến hành Validate và Lưu (Giống như cũ)
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'rating'     => ['required', 'integer', 'min:1', 'max:5'],
            'comment'    => ['nullable', 'string', 'max:1000'],
            'image'      => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('reviews', 'public');
        }

        \App\Models\Review::create([
            'user_id'    => auth()->id(),
            'product_id' => $request->product_id,
            'rating'     => $request->rating,
            'comment'    => $request->comment,
            'image'      => $imagePath,
        ]);

        return back()->with('success', 'Cảm ơn bạn đã để lại đánh giá thực tế! ⭐');
    }
        /**
         * Xóa đánh giá (Nếu bạn muốn cho phép khách hàng xóa bình luận của chính họ)
         */
        public function destroy(Review $review): RedirectResponse
        {
            // Kiểm tra quyền: Chỉ người tạo hoặc Admin mới được xóa
            if ($review->user_id !== auth()->id()) {
                abort(403);
            }

            // Xóa ảnh vật lý trong thư mục storage để tránh rác server
            if ($review->image) {
                Storage::disk('public')->delete($review->image);
            }

            $review->delete();

            return back()->with('toast', [
                'type' => 'info',
                'message' => 'Đã xóa đánh giá thành công.'
            ]);
        }
    }