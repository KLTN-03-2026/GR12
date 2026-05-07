<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use App\Mail\PartnerAccountStatus;
use App\Mail\ProductApprovalStatus;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    // Trang tổng quan Dashboard
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_customers' => User::where('role', 'customer')->count(),
                'pending_partners' => User::where('status', 'pending')->count(),
                'pending_products' => Product::where('status', 'pending')->count(),
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

    public function pendingProducts()
    {
        $products = Product::with(['user', 'category'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return Inertia::render('Admin/PendingProducts', [
            'products' => $products,
        ]);
    }

    public function approveProduct(Product $product)
    {
        $product->update(['status' => 'approved', 'is_available' => true]);
        Mail::to($product->user->email)->send(new ProductApprovalStatus($product, 'approved'));

        return back()->with('message', "Đã duyệt món ăn '{$product->name}' thành công.");
    }

    public function rejectProduct(Product $product)
    {
        $product->update(['status' => 'rejected', 'is_available' => false]);
        Mail::to($product->user->email)->send(new ProductApprovalStatus($product, 'rejected'));

        return back()->with('message', "Đã từ chối món ăn '{$product->name}'.");
    }

    public function vouchers()
    {
        $vouchers = Voucher::orderBy('expires_at', 'asc')->get();

        return Inertia::render('Admin/Vouchers', [
            'vouchers' => $vouchers,
        ]);
    }

    public function storeVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:vouchers,code|max:50',
            'discount_type' => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric|min:1',
            'expires_at' => 'required|date|after:now',
            'max_uses' => 'nullable|integer|min:1',
            'minimum_product_price' => 'nullable|numeric|min:0',
        ], [
            'code.required' => 'Mã voucher không được để trống.',
            'code.unique' => 'Mã voucher đã tồn tại. Vui lòng sử dụng mã khác.',
            'expires_at.after' => 'Ngày hết hạn phải lớn hơn thời điểm hiện tại.',
        ]);

        Voucher::create([
            'code' => strtoupper(trim($request->code)),
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'expires_at' => $request->expires_at,
            'uuid' => (string) Str::uuid(),
            'max_uses' => $request->input('max_uses', 0) ?: 0,
            'used_count' => 0,
            'minimum_product_price' => $request->input('minimum_product_price') ?: null,
        ]);

        return back()->with('success', 'Đã tạo voucher thành công.');
    }

    public function destroyVoucher(Voucher $voucher)
    {
        $voucher->delete();
        return back()->with('success', 'Đã thu hồi voucher thành công.');
    }

    // Quản lý đơn hàng cho admin
    public function orders(Request $request)
    {
        $query = \App\Models\Order::with(['user', 'shipper.user', 'items.product.user']);

        // Tìm kiếm theo mã đơn hoặc số điện thoại
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Lọc theo trạng thái
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Sắp xếp theo thời gian đặt giảm dần
        $orders = $query->orderBy('created_at', 'desc')
                        ->paginate($request->get('pageSize', 10))
                        ->appends($request->query());

        // Thống kê nhanh
        $stats = [
            'total' => \App\Models\Order::count(),
            'pending' => \App\Models\Order::where('status', 'pending')->count(),
            'delivering' => \App\Models\Order::where('status', 'delivering')->count(),
            'completed_today' => \App\Models\Order::where('status', 'completed')->whereDate('updated_at', today())->count(),
        ];

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function showOrder($id)
    {
        $order = \App\Models\Order::with([
            'user', 
            'shipper.user', 
            'items.product.user'
        ])->findOrFail($id);

        return response()->json($order);
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

    // --- QUẢN LÝ NGƯỜI DÙNG ---
    public function users(Request $request)
    {
        $query = User::query();

        // Lọc theo Role
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        // Lọc theo Status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Tìm kiếm (Tên, SĐT, Email)
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')
                       ->paginate($request->get('pageSize', 15))
                       ->appends($request->query());

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'status'])
        ]);
    }

    public function toggleBlockUser(User $user)
    {
        // Không cho phép Admin tự khóa chính mình
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Bạn không thể tự khóa tài khoản của mình.']);
        }

        // Nếu đang active thì block, nếu đang block thì active
        if ($user->status === 'blocked') {
            $user->update(['status' => 'active']);
            $message = "Đã MỞ KHÓA tài khoản {$user->name}.";
        } else {
            $user->update(['status' => 'blocked']);
            $message = "Đã KHÓA tài khoản {$user->name}.";
        }

        return back()->with('message', $message);
    }

    // --- QUẢN LÝ THÔNG BÁO ---
    public function notificationsIndex()
    {
        return Inertia::render('Admin/Notifications/Index');
    }

    public function sendNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target_audience' => 'required|in:all,customer,restaurant,shipper',
            'type' => 'required|in:info,success,warning'
        ]);

        $query = User::where('status', 'active');
        if ($request->target_audience !== 'all') {
            $query->where('role', $request->target_audience);
        }

        $users = $query->get();

        \Illuminate\Support\Facades\Notification::send($users, new \App\Notifications\SystemNotification(
            $request->title,
            $request->message,
            $request->type
        ));

        return back()->with('success', "Đã gửi thông báo thành công tới {$users->count()} người dùng.");
    }
}