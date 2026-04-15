<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\ShipperController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'API FoodXpress đã sẵn sàng!']);
});

// Route lấy danh sách món ăn: GET http://127.0.0.1:8000/api/products
Route::get('/products', [ProductController::class, 'index']);

// Route lấy chi tiết món ăn: GET http://127.0.0.1:8000/api/products/{id}
Route::get('/products/{id}', [ProductController::class, 'show']);

// Customer routes
Route::middleware('auth:sanctum')->prefix('orders')->group(function () {
    Route::post('/{id}/confirm-delivery', [App\Http\Controllers\Customer\OrderController::class, 'confirmDelivery']);
});

// Shipper routes - sử dụng sanctum middleware với session cookie support
Route::middleware(['auth:sanctum'])->prefix('shipper')->group(function () {
    Route::get('/dashboard', [ShipperController::class, 'index']);
    Route::post('/check-in', [ShipperController::class, 'checkIn']);
    Route::post('/check-out', [ShipperController::class, 'checkOut']);
    Route::post('/location', [ShipperController::class, 'updateLocation']);
    Route::post('/orders/{orderId}/accept', [ShipperController::class, 'acceptOrder']);
    Route::post('/orders/{orderId}/confirm-pickup', [ShipperController::class, 'confirmPickup']);
    Route::post('/orders/{orderId}/complete', [ShipperController::class, 'completeOrder']);
});
