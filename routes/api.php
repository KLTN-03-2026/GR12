<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\ShipperController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'API FoodXpress đã sẵn sàng!']);
});

Route::get('/debug/order/{orderId}', function ($orderId) {
    $order = \App\Models\Order::with('items.product.user')->find($orderId);
    if (!$order) {
        return response()->json(['error' => 'Order not found'], 404);
    }
    return response()->json([
        'order_id' => $order->id,
        'order_code' => $order->order_code,
        'items_count' => $order->items->count(),
        'items' => $order->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'restaurant' => $item->product->user->restaurant_name ?? $item->product->user->name,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ];
        }),
        'db_check' => [
            'raw_items' => \DB::table('order_items')->where('order_id', $orderId)->count(),
        ]
    ]);
});

Route::get('/debug/shipper-dashboard', function () {
    $user = \Illuminate\Support\Facades\Auth::user();
    if (!$user) {
        return response()->json(['error' => 'Not authenticated'], 401);
    }

    $shipper = \App\Models\Shipper::where('user_id', $user->id)->first();
    if (!$shipper) {
        return response()->json(['error' => 'Shipper not found for user ' . $user->id], 404);
    }

    $orders = \App\Models\Order::all();
    $confirmedOrders = \App\Models\Order::where('status', 'confirmed')->get();
    $assignedOrders = \App\Models\Order::where('status', 'assigned')->get();

    return response()->json([
        'authenticated_user' => $user->id,
        'shipper' => [
            'id' => $shipper->id,
            'user_id' => $shipper->user_id,
            'status' => $shipper->status,
        ],
        'total_orders' => $orders->count(),
        'confirmed_orders' => $confirmedOrders->map(fn($o) => [
            'id' => $o->id,
            'code' => $o->order_code,
            'status' => $o->status,
            'shipper_id' => $o->shipper_id,
            'items_count' => $o->items->count(),
        ]),
        'assigned_orders' => $assignedOrders->map(fn($o) => [
            'id' => $o->id,
            'code' => $o->order_code,
            'status' => $o->status,
            'shipper_id' => $o->shipper_id,
        ]),
    ]);
})->middleware('auth:sanctum');

// Route lấy danh sách món ăn: GET http://127.0.0.1:8000/api/products
Route::get('/products', [ProductController::class, 'index']);

// Route lấy chi tiết món ăn: GET http://127.0.0.1:8000/api/products/{id}
Route::get('/products/{id}', [ProductController::class, 'show']);

// Customer routes
Route::middleware('auth:sanctum')->prefix('orders')->group(function () {
    Route::get('/{id}/details', [App\Http\Controllers\Customer\OrderController::class, 'getOrderDetails']);
    Route::post('/{id}/confirm-delivery', [App\Http\Controllers\Customer\OrderController::class, 'confirmDelivery']);
});

// Shipper routes - chỉ shipper role mới được truy cập
Route::middleware(['auth:sanctum', 'role:shipper'])->prefix('shipper')->group(function () {
    Route::get('/dashboard', [ShipperController::class, 'index']);
    Route::post('/check-in', [ShipperController::class, 'checkIn']);
    Route::post('/check-out', [ShipperController::class, 'checkOut']);
    Route::post('/location', [ShipperController::class, 'updateLocation']);
    Route::post('/orders/{orderId}/accept', [ShipperController::class, 'acceptOrder']);
    Route::post('/orders/{orderId}/reject', [ShipperController::class, 'rejectOrder']);
    Route::post('/orders/{orderId}/confirm-pickup', [ShipperController::class, 'confirmPickup']);
    Route::post('/orders/{orderId}/start-delivery', [ShipperController::class, 'startDelivery']);
    Route::post('/orders/{orderId}/complete', [ShipperController::class, 'completeOrder']);
});

// Payment routes
Route::middleware('auth:sanctum')->prefix('payment')->group(function () {
    Route::post('/vnpay/{orderId}', [App\Http\Controllers\PaymentController::class, 'createVNPayPayment']);
});

    Route::get('/{id}/details', [App\Http\Controllers\Customer\OrderController::class, 'show']);
