<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Restaurant\ProductController as RestaurantProductController;
use App\Http\Controllers\Restaurant\ReviewController as RestaurantReviewController;
use App\Http\Controllers\Customer\RestaurantController as CustomerRestaurantController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Restaurant\RestaurantOrderController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| 1. NHÓM CÔNG KHAI (PUBLIC ROUTES)
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/restaurant-menu/{id}', [CustomerRestaurantController::class, 'show'])->name('restaurant.menu');

// Trang chờ phê duyệt
Route::get('/waiting-for-approval', function () { 
    return Inertia::render('Auth/WaitForApproval'); 
})->name('wait.approval');


/*
|--------------------------------------------------------------------------
| 2. NHÓM YÊU CẦU ĐĂNG NHẬP (AUTH ROUTES)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {


    // Sửa lại route này trong web.php
        Route::post('/update-location', function (\Illuminate\Http\Request $request) {
            // 1. Lưu tọa độ vào session (Giữ nguyên)
            session([
                'user_latitude' => $request->latitude,
                'user_longitude' => $request->longitude,
            ]);

            // 2. QUAN TRỌNG: Trả về redirect back để Inertia không báo lỗi
            return back(); 
        })->name('update-location');

    // --- DÀNH CHO KHÁCH HÀNG (CUSTOMER) ---
    Route::get('/my-orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/my-orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // --- DÀNH CHO NHÀ HÀNG (RESTAURANT) ---
    // Gom tất cả vào prefix 'restaurant' và name 'restaurant.'
    Route::prefix('restaurant')->name('restaurant.')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', function () {
            $user = auth()->user();
            $stats = [
                'total_products'  => \App\Models\Product::where('user_id', $user->id)->count(),
                'active_products' => \App\Models\Product::where('user_id', $user->id)->where('is_available', true)->count(),
                'active_orders'   => 0,
                'today_revenue'   => '0đ',
            ];
            return Inertia::render('Restaurant/Dashboard', ['stats' => $stats]);
        })->name('dashboard');

        // Quản lý đơn hàng của quán
        Route::get('/orders', [RestaurantOrderController::class, 'index'])->name('orders.index');
        Route::patch('/orders/{id}/status', [RestaurantOrderController::class, 'updateStatus'])->name('orders.update');

        // Quản lý đánh giá và phản hồi
        Route::get('/reviews', [RestaurantReviewController::class, 'index'])->name('reviews.index');

        // Quản lý món ăn (CRUD)
        Route::resource('products', RestaurantProductController::class);
    });

    // --- DÀNH CHO QUẢN TRỊ VIÊN (ADMIN) ---
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::controller(AdminController::class)->group(function () {
            Route::get('/users-pending', 'pendingUsers')->name('pending');
            Route::post('/users/{user}/approve', 'approve')->name('approve');
            Route::post('/users/{user}/reject', 'reject')->name('reject');
            Route::get('/vouchers', 'vouchers')->name('vouchers.index');
            Route::post('/vouchers', 'storeVoucher')->name('vouchers.store');
        });
    });

    // --- DÀNH CHO SHIPPER ---
    Route::prefix('shipper')->name('shipper.')->middleware(['auth', 'role:shipper'])->group(function () {
        Route::get('/dashboard', function () {
            $shipper = \App\Models\Shipper::where('user_id', auth()->id())->first();
            if ($shipper && $shipper->status === 'offline') {
                return redirect()->route('shipper.notifications');
            }
            return Inertia::render('Shipper/MenuPage');
        })->name('dashboard');

        Route::get('/notifications', function () {
            return Inertia::render('Shipper/Notifications');
        })->name('notifications');

        Route::get('/tracking', function () {
            return Inertia::render('Shipper/Tracking');
        })->name('tracking');

        Route::get('/menu/{item}', function ($item) {
            $allowedItems = ['destination', 'notifications', 'income', 'wallet', 'history', 'help', 'settings'];
            if (!in_array($item, $allowedItems, true)) {
                abort(404);
            }
            return Inertia::render('Shipper/MenuDetail', ['item' => $item]);
        })->name('menu.item');

        Route::get('/orders/{order}/detail', function (\App\Models\Order $order) {
            // Kiểm tra xem đơn hàng này có thuộc shipper hiện tại không
            $shipper = \App\Models\Shipper::where('user_id', auth()->id())->first();
            if (!$shipper || $order->shipper_id !== $shipper->id) {
                abort(403, 'Unauthorized access to this order');
            }
            return Inertia::render('Shipper/OrderDetail', [
                'order' => $order->load('items.product.user', 'user', 'shipper.user')
            ]);
        })->name('order.detail');
    });

    // --- GIỎ HÀNG ---
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::patch('/{cartItem}', [CartController::class, 'update'])->name('update');
        Route::delete('/{cartItem}', [CartController::class, 'destroy'])->name('destroy');
    });

    // --- HỒ SƠ CÁ NHÂN ---
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });
});

require __DIR__.'/auth.php';