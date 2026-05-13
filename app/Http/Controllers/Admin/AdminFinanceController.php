<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Models\Order;
use App\Models\User;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AdminFinanceController extends Controller
{
    // Quản lý Yêu cầu rút tiền
    public function withdrawals(Request $request)
    {
        $query = WalletTransaction::with('user')
            ->where('type', 'withdraw');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('pageSize', 10))
            ->appends($request->query());

        // Thống kê nhanh
        $stats = [
            'pending_count' => WalletTransaction::where('type', 'withdraw')->where('status', 'pending')->count(),
            'pending_amount' => abs(WalletTransaction::where('type', 'withdraw')->where('status', 'pending')->sum('amount')),
            'completed_amount' => abs(WalletTransaction::where('type', 'withdraw')->where('status', 'completed')->sum('amount')),
            'total_completed' => WalletTransaction::where('type', 'withdraw')->where('status', 'completed')->count(),
        ];

        return Inertia::render('Admin/Withdrawals', [
            'withdrawals' => $withdrawals,
            'stats' => $stats,
            'filters' => $request->only(['status']),
        ]);
    }

    // Duyệt (Xác nhận đã chuyển tiền)
    public function approveWithdrawal(WalletTransaction $transaction)
    {
        if ($transaction->type !== 'withdraw' || $transaction->status !== 'pending') {
            return back()->withErrors(['error' => 'Giao dịch không hợp lệ.']);
        }

        $transaction->update(['status' => 'completed']);

        AdminActivityLog::log('approve_withdrawal', "Duyệt chuyển tiền: " . number_format(abs($transaction->amount)) . "đ cho {$transaction->user->name}");

        // Nếu muốn, có thể gửi mail cho User báo đã chuyển tiền thành công ở đây

        return back()->with('message', 'Đã xác nhận chuyển tiền thành công.');
    }

    // Từ chối (Hoàn tiền vào ví)
    public function rejectWithdrawal(Request $request, WalletTransaction $transaction)
    {
        if ($transaction->type !== 'withdraw' || $transaction->status !== 'pending') {
            return back()->withErrors(['error' => 'Giao dịch không hợp lệ.']);
        }

        DB::beginTransaction();
        try {
            // Đổi trạng thái giao dịch cũ thành rejected
            $transaction->update(['status' => 'rejected']);

            // Hoàn lại tiền cho user
            $user = $transaction->user;
            // Số tiền trong giao dịch là số âm, nên ta dùng abs() hoặc trừ đi số âm
            $refundAmount = abs($transaction->amount);
            $balanceBefore = $user->wallet_balance;
            
            $user->wallet_balance += $refundAmount;
            $user->save();

            // Tạo giao dịch hoàn tiền
            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'deposit', // Hoặc type mới 'refund'
                'amount' => $refundAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $user->wallet_balance,
                'status' => 'completed',
                'description' => 'Hoàn tiền do yêu cầu rút tiền bị từ chối. Lý do: ' . $request->input('reason', 'Không hợp lệ'),
            ]);

            AdminActivityLog::log('reject_withdrawal', "Từ chối rút tiền: " . number_format(abs($transaction->amount)) . "đ của {$transaction->user->name}. Lý do: " . $request->input('reason'));

            DB::commit();
            return back()->with('message', 'Đã từ chối yêu cầu và hoàn tiền thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Đã xảy ra lỗi hệ thống: ' . $e->getMessage()]);
        }
    }

    // Thống kê doanh thu
    public function revenue(Request $request)
    {
        $now = \Carbon\Carbon::now();

        // Lấy tất cả đơn hàng đã giao thành công (completed/delivered)
        $completedOrders = Order::whereIn('status', ['completed', 'delivered'])->get();

        $todayRevenue = $completedOrders
            ->where('updated_at', '>=', $now->copy()->startOfDay())
            ->sum('admin_revenue');

        $monthRevenue = $completedOrders
            ->where('updated_at', '>=', $now->copy()->startOfMonth())
            ->sum('admin_revenue');

        $totalRevenue = $completedOrders->sum('admin_revenue');

        // Dữ liệu biểu đồ 7 ngày gần nhất
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $nextDate = $date->copy()->addDay();
            
            $dayRevenue = $completedOrders->where('updated_at', '>=', $date)
                ->where('updated_at', '<', $nextDate)
                ->sum('admin_revenue');
                
            $chartData[] = [
                'day' => $date->locale('vi')->isoFormat('ddd'),
                'date' => $date->format('d/m'),
                'revenue' => $dayRevenue
            ];
        }

        $recentRevenueOrders = Order::with(['user', 'shipper.user'])
            ->whereIn('status', ['completed', 'delivered'])
            ->where('admin_revenue', '>', 0)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Revenue', [
            'stats' => [
                'today' => $todayRevenue,
                'month' => $monthRevenue,
                'total' => $totalRevenue,
            ],
            'chartData' => $chartData,
            'recent_orders' => $recentRevenueOrders
        ]);
    }

    // --- QUẢN LÝ VÍ ĐIỆN TỬ NÂNG CAO ---
    public function wallets(Request $request)
    {
        $query = User::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('wallet_balance', 'desc')
                       ->paginate($request->get('pageSize', 15))
                       ->appends($request->query());

        // Tổng hợp thống kê ví
        $stats = [
            'total_balance' => User::sum('wallet_balance'),
            'total_customers' => User::where('role', 'customer')->sum('wallet_balance'),
            'total_restaurants' => User::where('role', 'restaurant')->sum('wallet_balance'),
            'total_shippers' => User::where('role', 'shipper')->sum('wallet_balance'),
        ];

        return Inertia::render('Admin/Wallets', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
            'stats' => $stats
        ]);
    }

    public function adjustWallet(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|not_in:0',
            'reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $amount = (float) $request->amount;
            $balanceBefore = $user->wallet_balance;
            
            // Không cho trừ quá số dư
            if ($amount < 0 && abs($amount) > $balanceBefore) {
                return back()->withErrors(['error' => 'Số tiền trừ không được lớn hơn số dư hiện tại.']);
            }

            $user->wallet_balance += $amount;
            $user->save();

            // Ghi nhận lịch sử
            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => $amount > 0 ? 'deposit' : 'withdraw', // Admin deposit/withdraw
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $user->wallet_balance,
                'status' => 'completed',
                'description' => '[Admin Điều Chỉnh] ' . $request->reason,
            ]);

            DB::commit();
            return back()->with('message', 'Đã điều chỉnh số dư ví của ' . $user->name . ' thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }
}
