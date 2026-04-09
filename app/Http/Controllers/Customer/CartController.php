<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    // Lấy nội dung giỏ hàng hiện tại
    public function index()
    {
        $cart = Session::get('cart', []);
        return response()->json($cart); // Trả về JSON để Vue dễ xử lý
    }

    // Thêm món vào giỏ
    public function add(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $cart = Session::get('cart', []);

        // Nếu món đã có thì tăng số lượng, chưa có thì thêm mới
        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += 1;
        } else {
            $cart[$product->id] = [
                "id" => $product->id,
                "name" => $product->name,
                "quantity" => 1,
                "price" => $product->price,
                "image" => $product->image
            ];
        }

        Session::put('cart', $cart);
        return back()->with('success', 'Đã thêm món vào giỏ hàng!');
    }
}