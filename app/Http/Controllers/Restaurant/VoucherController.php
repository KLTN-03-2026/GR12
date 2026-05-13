<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    /**
     * Hiển thị danh sách voucher của quán
     */
    public function index()
    {
        $vouchers = Voucher::where('restaurant_id', Auth::id())
            ->orderBy('expires_at', 'asc')
            ->get();

        return Inertia::render('Restaurant/Vouchers', [
            'vouchers' => $vouchers,
        ]);
    }

    /**
     * Tạo mới voucher
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:vouchers',
            'discount_type' => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric|min:0',
            'expires_at' => 'required|date|after:today',
            'max_uses' => 'required|integer|min:0',
            'minimum_product_price' => 'nullable|numeric|min:0',
        ]);

        Voucher::create([
            'code' => strtoupper($request->code),
            'restaurant_id' => Auth::id(),
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'expires_at' => \Carbon\Carbon::parse($request->expires_at)->endOfDay(),
            'max_uses' => $request->max_uses,
            'minimum_product_price' => $request->minimum_product_price,
            'used_count' => 0,
        ]);

        return redirect()->back()->with('success', 'Mã giảm giá đã được tạo thành công.');
    }

    /**
     * Xóa voucher
     */
    public function destroy(Voucher $voucher)
    {
        if ($voucher->restaurant_id !== Auth::id()) {
            abort(403);
        }

        $voucher->delete();
        return redirect()->back()->with('success', 'Đã xóa mã giảm giá.');
    }
}
