<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReviewController extends Controller
{
    /**
     * Hiển thị danh sách đánh giá dành cho chủ nhà hàng.
     */
    public function index()
    {
        $restaurantId = Auth::id();

        $reviews = Review::whereHas('product', function ($query) use ($restaurantId) {
            $query->where('user_id', $restaurantId);
        })
        ->with(['product', 'user'])
        ->latest()
        ->paginate(12);

        $averageRating = Review::whereHas('product', function ($query) use ($restaurantId) {
            $query->where('user_id', $restaurantId);
        })->avg('rating');

        return Inertia::render('Restaurant/Reviews/Index', [
            'reviews' => $reviews,
            'averageRating' => round($averageRating ?: 0, 1),
        ]);
    }
}
