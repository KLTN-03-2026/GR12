<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminFinanceController;
use App\Http\Controllers\Admin\AdminLogController;
use App\Http\Controllers\Restaurant\ProductController as RestaurantProductController;
use App\Http\Controllers\Restaurant\ReviewController as RestaurantReviewController;
use App\Http\Controllers\Customer\RestaurantController as CustomerRestaurantController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Restaurant\RestaurantOrderController;
use App\Http\Controllers\RestaurantProfileController;
use App\Http\Controllers\AdminApprovalController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| 1. NHÓM CÔNG KHAI (PUBLIC ROUTES)
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/restaurant-menu/{id}', [CustomerRestaurantController::class, 'show'])->name('restaurant.menu');

// --- AI CHATBOT (Public) ---
Route::post('/api/chatbot', [\App\Http\Controllers\ChatbotController::class, 'handle'])->name('chatbot.handle');

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

    Route::get('/my-notifications', function () {
        $notifications = auth()->user()->notifications()->latest()->get();
        return Inertia::render('Notifications', [
            'notifications' => $notifications
        ]);
    })->name('my.notifications');

    Route::get('/wallet', [WalletController::class, 'customerWallet'])->name('customer.wallet');

    // --- DÀNH CHO NHÀ HÀNG (RESTAURANT) ---
    // Gom tất cả vào prefix 'restaurant' và name 'restaurant.'
    Route::prefix('restaurant')->name('restaurant.')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', function () {
            $user = auth()->user();

            // Lấy danh sách đơn hàng hoàn thành hôm nay
            $todayOrders = \App\Models\Order::whereHas('items.product', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('status', 'completed')
            ->whereDate('created_at', \Carbon\Carbon::today())
            ->get();
            $todayRevenue = $todayOrders->sum('restaurant_revenue');

            // Lấy số đơn đang xử lý
            $activeOrdersCount = \App\Models\Order::whereHas('items.product', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereIn('status', ['pending', 'processing', 'confirmed', 'shipping'])
            ->count();

            // Tính dữ liệu biểu đồ 7 ngày qua
            $chartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = \Carbon\Carbon::today()->subDays($i);
                $dailyRevenue = \App\Models\Order::whereHas('items.product', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('restaurant_revenue');
                
                $chartData[] = [
                    'date' => $date->format('d/m'),
                    'revenue' => $dailyRevenue,
                ];
            }

            $stats = [
                'total_products'  => \App\Models\Product::where('user_id', $user->id)->count(),
                'active_products' => \App\Models\Product::where('user_id', $user->id)->where('is_available', true)->count(),
                'active_orders'   => $activeOrdersCount,
                'today_revenue'   => number_format($todayRevenue, 0, ',', '.') . 'đ',
                'chart_data'      => $chartData,
            ];
            return Inertia::render('Restaurant/Dashboard', ['stats' => $stats]);
        })->name('dashboard');

        // Quản lý đơn hàng của quán
        Route::get('/orders', [RestaurantOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/history', [RestaurantOrderController::class, 'history'])->name('orders.history');
        Route::get('/orders/{id}', [RestaurantOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{id}/status', [RestaurantOrderController::class, 'updateStatus'])->name('orders.update');

        // Quản lý đánh giá và phản hồi
        Route::get('/reviews', [RestaurantReviewController::class, 'index'])->name('reviews.index');
        Route::post('/reviews/{review}/reply', [RestaurantReviewController::class, 'reply'])->name('reviews.reply');

        // Quản lý Vouchers của nhà hàng
        Route::get('/vouchers', [\App\Http\Controllers\Restaurant\VoucherController::class, 'index'])->name('vouchers.index');
        Route::post('/vouchers', [\App\Http\Controllers\Restaurant\VoucherController::class, 'store'])->name('vouchers.store');
        Route::delete('/vouchers/{voucher}', [\App\Http\Controllers\Restaurant\VoucherController::class, 'destroy'])->name('vouchers.destroy');

        // Quản lý món ăn (CRUD)
        Route::resource('products', RestaurantProductController::class);

        // Cài đặt giờ mở cửa và trạng thái nhận đơn
        Route::patch('/settings/operating-hours', function (\Illuminate\Http\Request $request) {
            $user = auth()->user();
            $request->validate([
                'opening_time' => 'required|date_format:H:i',
                'closing_time' => 'required|date_format:H:i',
                'is_accepting_orders' => 'required|boolean',
            ]);
            $user->update([
                'opening_time' => $request->opening_time,
                'closing_time' => $request->closing_time,
                'is_accepting_orders' => $request->is_accepting_orders,
            ]);
            return back()->with('success', 'Đã cập nhật cài đặt hoạt động.');
        })->name('settings.operating-hours');

        // Ví điện tử
        Route::get('/wallet', [WalletController::class, 'restaurantWallet'])->name('wallet');
    });

    // --- DÀNH CHO QUẢN TRỊ VIÊN (ADMIN) ---
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::controller(AdminController::class)->group(function () {
            Route::get('/users-pending', 'pendingUsers')->name('pending');
            Route::get('/users', 'users')->name('users.index');
            Route::patch('/users/{user}/toggle-block', 'toggleBlockUser')->name('users.toggle-block');
            Route::get('/products-pending', 'pendingProducts')->name('products.pending');
            Route::get('/products', 'allProducts')->name('products.index');
            Route::post('/users/{user}/approve', 'approve')->name('approve');
            Route::post('/users/{user}/reject', 'reject')->name('reject');
            Route::post('/products/{product}/approve', 'approveProduct')->name('products.approve');
            Route::post('/products/{product}/reject', 'rejectProduct')->name('products.reject');
            Route::get('/vouchers', 'vouchers')->name('vouchers.index');
            Route::post('/vouchers', 'storeVoucher')->name('vouchers.store');
            Route::delete('/vouchers/{voucher}', 'destroyVoucher')->name('vouchers.destroy');
            Route::get('/orders', 'orders')->name('orders.index');
            Route::get('/orders/{id}', 'showOrder')->name('orders.show');
            Route::post('/users/{user}/analyze-risk', 'analyzeUserRisk')->name('users.analyze-risk');
            Route::get('/live-map', 'liveMap')->name('live-map');
            Route::get('/live-map-data', 'liveMapData')->name('live-map-data');
        });

        // Notifications (Push Center)
        Route::controller(\App\Http\Controllers\Admin\AdminNotificationController::class)->group(function () {
            Route::get('/notifications', 'index')->name('notifications.index');
            Route::post('/notifications', 'store')->name('push-notifications.store');
        });

        // Logs
        Route::get('/logs', [AdminLogController::class, 'index'])->name('logs');
        Route::controller(AdminFinanceController::class)->group(function () {
            Route::get('/withdrawals', 'withdrawals')->name('withdrawals.index');
            Route::post('/withdrawals/{transaction}/approve', 'approveWithdrawal')->name('withdrawals.approve');
            Route::post('/withdrawals/{transaction}/reject', 'rejectWithdrawal')->name('withdrawals.reject');
            Route::get('/revenue', 'revenue')->name('revenue');
            Route::get('/wallets', 'wallets')->name('wallets.index');
            Route::post('/wallets/{user}/adjust', 'adjustWallet')->name('wallets.adjust');
        });

        // Settings
        Route::controller(\App\Http\Controllers\Admin\SettingController::class)->group(function () {
            Route::get('/settings', 'index')->name('settings.index');
            Route::post('/settings', 'update')->name('settings.update');
        });
    });

    // --- DÀNH CHO SHIPPER ---
    Route::prefix('shipper')->name('shipper.')->middleware(['auth', 'role:shipper'])->group(function () {
        Route::get('/dashboard', function () {
            return redirect()->route('shipper.tracking');
        })->name('dashboard');

        Route::get('/notifications', function () {
            $notifications = auth()->user()->notifications()->latest()->get();
            return Inertia::render('Shipper/Notifications', [
                'notifications' => $notifications
            ]);
        })->name('notifications');

        Route::get('/history', function () {
            $shipper = \App\Models\Shipper::where('user_id', auth()->id())->first();
            $orders = \App\Models\Order::where('shipper_id', $shipper?->id)
                ->whereIn('status', ['completed', 'cancelled'])
                ->with('items.product.user', 'user')
                ->latest('updated_at')
                ->get();
                
            return Inertia::render('Shipper/History', [
                'orders' => $orders
            ]);
        })->name('history');

        Route::get('/tracking', function () {
            return Inertia::render('Shipper/Tracking');
        })->name('tracking');

        Route::get('/income', [\App\Http\Controllers\ShipperController::class, 'income'])->name('income');

        Route::get('/menu/{item}', function ($item) {
            $allowedItems = ['destination', 'notifications', 'wallet', 'history', 'help', 'settings'];
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

        // Ví điện tử Shipper
        Route::get('/wallet', [WalletController::class, 'shipperWallet'])->name('wallet');
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

    // --- WALLET TOPUP / WITHDRAW ---
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::post('/deposit', [WalletController::class, 'deposit'])->name('deposit');
        Route::post('/withdraw', [WalletController::class, 'withdraw'])->name('withdraw');
    });

    // --- CHAT (Giao tiếp trực tiếp) ---
    Route::get('/api/orders/{order}/chat', [\App\Http\Controllers\ChatMessageController::class, 'index'])->name('chat.index');
    Route::post('/api/orders/{order}/chat', [\App\Http\Controllers\ChatMessageController::class, 'store'])->name('chat.store');

});

require __DIR__.'/auth.php';
    // Restaurant Profile Routes
    Route::middleware('role:restaurant')->group(function () {
        Route::get('/restaurant/profile', [RestaurantProfileController::class, 'edit'])->name('restaurant.profile.edit');
        Route::post('/restaurant/profile/update', [RestaurantProfileController::class, 'update'])->name('restaurant.profile.update');
    });

    // Admin Approval Routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/approvals', [AdminApprovalController::class, 'index'])->name('admin.approvals.index');
        Route::post('/admin/approvals/{approvalRequest}/approve', [AdminApprovalController::class, 'approve'])->name('admin.approvals.approve');
        Route::post('/admin/approvals/{approvalRequest}/reject', [AdminApprovalController::class, 'reject'])->name('admin.approvals.reject');
    });


// VNPay Routes
Route::get('/payment/vnpay-return', [App\Http\Controllers\PaymentController::class, 'vnpayReturn'])->name('payment.vnpay.return');
Route::post('/payment/vnpay-ipn', [App\Http\Controllers\PaymentController::class, 'vnpayIPN'])->name('payment.vnpay.ipn');
Route::get('/wallet/vnpay-return', [App\Http\Controllers\PaymentController::class, 'vnpayWalletReturn'])->name('wallet.vnpay-return');

Route::delete('/my-orders/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
