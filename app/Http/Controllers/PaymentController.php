<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function createVNPayPayment(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->payment_status !== 'pending') {
            return response()->json(['error' => 'Order payment already processed'], 400);
        }

        $vnp_TmnCode = config('vnpay.tmn_code'); // VNPay TMN Code
        $vnp_HashSecret = config('vnpay.hash_secret'); // VNPay Hash Secret
        $vnp_Url = config('vnpay.url'); // VNPay URL
        $vnp_Returnurl = config('vnpay.return_url'); // Return URL
        $vnp_IpnUrl = config('vnpay.ipn_url'); // IPN URL

        $vnp_TxnRef = $order->order_code; // Mã giao dịch
        $vnp_OrderInfo = 'Thanh toan don hang ' . $order->order_code;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $order->total * 100; // VNPay yêu cầu số tiền * 100
        $vnp_Locale = 'vn';
        $vnp_BankCode = '';
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

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
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

    public function vnpayReturn(Request $request)
    {
        $vnp_HashSecret = config('vnpay.hash_secret');

        $inputData = array();
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $orderId = $inputData['vnp_TxnRef'];

        if ($secureHash == $request->vnp_SecureHash) {
            $order = Order::where('order_code', $orderId)->first();
            if ($request->vnp_ResponseCode == '00') {
                $order->update(['payment_status' => 'paid']);
                return redirect()->route('home')->with('success', 'Thanh toán thành công!');
            } else {
                $order->update(['payment_status' => 'failed']);
                return redirect()->route('home')->with('error', 'Thanh toán thất bại!');
            }
        } else {
            return redirect()->route('home')->with('error', 'Chữ ký không hợp lệ!');
        }
    }

    public function vnpayIPN(Request $request)
    {
        $vnp_HashSecret = config('vnpay.hash_secret');

        $inputData = array();
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $orderId = $inputData['vnp_TxnRef'];

        if ($secureHash == $request->vnp_SecureHash) {
            $order = Order::where('order_code', $orderId)->first();
            if ($request->vnp_ResponseCode == '00') {
                $order->update(['payment_status' => 'paid']);
                Log::info('VNPay IPN: Payment successful for order ' . $orderId);
            } else {
                $order->update(['payment_status' => 'failed']);
                Log::info('VNPay IPN: Payment failed for order ' . $orderId);
            }
            return response('OK', 200);
        } else {
            return response('Invalid signature', 400);
        }
    }
}
