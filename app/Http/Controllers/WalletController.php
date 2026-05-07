<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function restaurantWallet()
    {
        $user = Auth::user();
        $transactions = WalletTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Restaurant/Wallet', [
            'transactions' => $transactions
        ]);
    }

    public function shipperWallet()
    {
        $user = Auth::user();
        $transactions = WalletTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Shipper/Wallet', [
            'transactions' => $transactions
        ]);
    }

    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
            'method' => 'required|in:vnpay,momo' // Momo có thể fallback sau
        ]);

        $user = Auth::user();
        $amount = $request->amount;

        if ($request->method === 'vnpay') {
            $vnp_TmnCode = config('vnpay.tmn_code');
            $vnp_HashSecret = config('vnpay.hash_secret');
            $vnp_Url = config('vnpay.url');
            $vnp_Returnurl = route('wallet.vnpay-return'); // New route

            $vnp_TxnRef = 'WALLET_DEPOSIT_' . $user->id . '_' . time();
            $vnp_OrderInfo = 'Nap tien vao vi FoodXpress user ' . $user->id;
            $vnp_OrderType = 'billpayment';
            $vnp_Amount = $amount * 100;
            $vnp_Locale = 'vn';
            $vnp_BankCode = $request->bank_code ?? '';
            $vnp_IpAddr = $request->ip();

            $inputData = array(
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef,
                "vnp_ExpireDate" => date('YmdHis', strtotime('+15 minutes')),
            );

            if (!empty($vnp_BankCode)) {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            }

            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }

            return response()->json(['payment_url' => $vnp_Url]);
        }

        return response()->json(['error' => 'Unsupported payment method'], 400);
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:50000',
            'bank_account' => 'required|string',
            'bank_name' => 'required|string',
            'account_name' => 'required|string'
        ]);

        $user = Auth::user();
        $amount = $request->amount;

        if ($user->wallet_balance < $amount) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'amount' => 'Số dư không đủ để rút số tiền này.'
            ]);
        }

        DB::beginTransaction();
        try {
            // Lưu thông tin ngân hàng cho lần rút tiếp theo
            $user->update([
                'bank_name' => $request->bank_name,
                'bank_account' => $request->bank_account,
                'bank_account_name' => $request->account_name,
            ]);

            $balanceBefore = $user->wallet_balance;
            $user->wallet_balance -= $amount;
            $user->save();

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'withdraw',
                'amount' => -$amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $user->wallet_balance,
                'status' => 'pending',
                'description' => 'Yêu cầu rút tiền về ngân hàng ' . $request->bank_name . ' (' . $request->bank_account . ')',
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Yêu cầu rút tiền thành công. Vui lòng chờ kế toán xử lý trong 24h.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
