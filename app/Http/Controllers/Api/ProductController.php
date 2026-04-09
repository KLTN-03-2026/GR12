<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Lấy danh sách tất cả món ăn (kèm theo danh mục)
    public function index()
    {
        $products = Product::with('category')->where('is_available', true)->get();
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    // Lấy chi tiết một món ăn (kèm theo các lựa chọn Topping/Size)
    public function show($id)
    {
        $product = Product::with(['category', 'options'])->find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy món ăn'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }
}