<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'API FoodXpress đã sẵn sàng!']);
});

// Route lấy danh sách món ăn: GET http://127.0.0.1:8000/api/products
Route::get('/products', [ProductController::class, 'index']);

// Route lấy chi tiết món ăn: GET http://127.0.0.1:8000/api/products/{id}
Route::get('/products/{id}', [ProductController::class, 'show']);
