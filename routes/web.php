<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Restaurant\ProductController as RestaurantProductController;
use App\Http\Controllers\Customer\RestaurantController as CustomerRestaurantController;
use App\Http\Controllers\Customer\CartController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Restaurant\RestaurantOrderController;


/*
|--------------------------------------------------------------------------
| 1. NHÓM CÔNG KHAI (PUBLIC ROUTES)
|--------------------------------------------------------------------------
*/

// Trang chủ: Đã tách logic sang HomeController
Route::get('/', [HomeController::class, 'index'])->name('home');

// Xem Menu chi tiết của một quán ăn
Route::get('/restaurant-menu/{id}', [CustomerRestaurantController::class, 'show'])->name('restaurant.menu');

// Trang chờ phê duyệt tài khoản (cho Restaurant mới đăng ký)
Route::get('/waiting-for-approval', function () { 
    return Inertia::render('Auth/WaitForApproval'); 
})->name('wait.approval');


/*
|--------------------------------------------------------------------------
| 2. NHÓM YÊU CẦU ĐĂNG NHẬP (PRIVATE ROUTES)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    Route::middleware(['auth'])->group(function () {
        Route::get('/my-orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    });

    Route::prefix('restaurant')->name('restaurant.')->middleware(['auth'])->group(function () {
        Route::get('/orders', [RestaurantOrderController::class, 'index'])->name('orders.index');
        Route::patch('/orders/{id}/status', [RestaurantOrderController::class, 'updateStatus'])->name('orders.update');
    });
    // --- NHÓM QUÁN ĂN (RESTAURANT) ---
    Route::prefix('shop')->name('restaurant.')->group(function () {
        
        Route::get('/dashboard', function () {
            $user = auth()->user();
            $stats = [
                'total_products'  => \App\Models\Product::where('user_id', $user->id)->count(),
                'active_products' => \App\Models\Product::where('user_id', $user->id)->where('is_available', true)->count(),
                'active_orders'   => 0,
                'today_revenue'   => number_format(0) . 'đ',
            ];

            return Inertia::render('Restaurant/Dashboard', ['stats' => $stats]);
        })->name('dashboard');

        // Quản lý món ăn (CRUD)
        Route::resource('products', RestaurantProductController::class);
    });

    // --- NHÓM QUẢN TRỊ (ADMIN) ---
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        
        Route::controller(AdminController::class)->group(function () {
            Route::get('/users-pending', 'pendingUsers')->name('pending');
            Route::post('/users/{user}/approve', 'approve')->name('approve');
            Route::post('/users/{user}/reject', 'reject')->name('reject');
        });
    });

    // Giỏ hàng
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::patch('/{cartItem}', [CartController::class, 'update'])->name('update');
        Route::delete('/{cartItem}', [CartController::class, 'destroy'])->name('destroy');
    });

    // --- HỒ SƠ CÁ NHÂN (PROFILE) ---
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });
});

require __DIR__.'/auth.php';