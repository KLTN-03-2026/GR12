<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail; // <--- THÊM DÒNG NÀY
use App\Mail\PartnerAccountStatus;  // <--- THÊM DÒNG NÀY

class AdminController extends Controller
{
    // Trang tổng quan Dashboard
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_customers' => User::where('role', 'customer')->count(),
                'pending_partners' => User::where('status', 'pending')->count(),
                'active_restaurants' => User::where('role', 'restaurant')->where('status', 'active')->count(),
                'active_shippers' => User::where('role', 'shipper')->where('status', 'active')->count(),
            ]
        ]);
    }

    // Danh sách chờ duyệt
    public function pendingUsers()
    {
        $users = User::where('status', 'pending')
                    ->whereIn('role', ['restaurant', 'shipper'])
                    ->get();

        return Inertia::render('Admin/PendingUsers', [
            'users' => $users
        ]);
    }

    // Hàm duyệt tài khoản
    public function approve(User $user)
    {
        $user->update(['status' => 'active']);
        Mail::to($user->email)->send(new PartnerAccountStatus($user, 'active'));
        return back()->with('message', "Đã duyệt tài khoản cho {$user->name}");
    }
    public function reject($id)
        {
            // Tìm người dùng theo ID
            $user = \App\Models\User::findOrFail($id);

            // Cách 1: Xóa luôn tài khoản đó khỏi hệ thống (Sạch sẽ database)
            Mail::to($user->email)->send(new PartnerAccountStatus($user, 'rejected'));
            $user->delete();

            // Cách 2: Hoặc chỉ đổi status thành 'rejected' (Nếu bạn muốn lưu vết)
            // $user->update(['status' => 'rejected']);

            return back()->with('message', 'Đã từ chối và gỡ bỏ yêu cầu của đối tác.');
        }
}