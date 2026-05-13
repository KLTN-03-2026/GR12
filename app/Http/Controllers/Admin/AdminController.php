<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use App\Models\Order;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\PartnerAccountStatus;
use App\Mail\ProductApprovalStatus;
use App\Notifications\ProductStatusUpdated;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminController extends Controller
{
    // Trang tổng quan Dashboard
    public function index()
    {
        $now = Carbon::now();
        
        // --- 1. Tổng quan Tài chính & Đơn hàng ---
        $completedOrders = Order::where('status', 'completed');
        
        $totalSystemRevenue = $completedOrders->sum('total');
        $totalAdminRevenue = $completedOrders->sum('admin_revenue');
        
        $totalOrdersCount = Order::count();
        $completedOrdersCount = $completedOrders->count();
        
        // Tính tỷ lệ thành công
        $successRate = $totalOrdersCount > 0 ? round(($completedOrdersCount / $totalOrdersCount) * 100, 1) : 0;

        // --- 2. Dữ liệu Biểu đồ (7 ngày gần nhất) ---
        $sevenDaysAgo = Carbon::now()->subDays(6)->startOfDay();
        
        // Nhóm doanh thu theo ngày
        $dailyRevenue = Order::where('status', 'completed')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('SUM(admin_revenue) as admin_revenue')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Định dạng dữ liệu cho Line Chart
        $chartLabels = [];
        $chartSystemRevenue = [];
        $chartAdminRevenue = [];
        
        // Khởi tạo mảng 7 ngày để tránh bị thiếu ngày không có đơn
        for ($i = 0; $i < 7; $i++) {
            $dateStr = Carbon::now()->subDays(6 - $i)->format('Y-m-d');
            $chartLabels[] = Carbon::now()->subDays(6 - $i)->format('d/m');
            
            $dayData = $dailyRevenue->firstWhere('date', $dateStr);
            $chartSystemRevenue[] = $dayData ? (float) $dayData->total_revenue : 0;
            $chartAdminRevenue[] = $dayData ? (float) $dayData->admin_revenue : 0;
        }

        // --- 3. Dữ liệu Trạng thái Đơn hàng (Doughnut Chart) ---
        $orderStatusStats = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // --- 4. Top 5 Nhà hàng Doanh thu cao nhất ---
        $topRestaurants = User::where('users.role', 'restaurant')
            ->select('users.id', 'users.name', 'users.restaurant_name', 'users.email', 'users.avatar')
            ->join('products', 'products.user_id', '=', 'users.id')
            ->join('order_items', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
            ->selectRaw('SUM(order_items.price * order_items.quantity) as total_revenue')
            ->selectRaw('COUNT(DISTINCT orders.id) as completed_orders')
            ->groupBy('users.id', 'users.name', 'users.restaurant_name', 'users.email', 'users.avatar')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_system_revenue' => $totalSystemRevenue,
                'total_admin_revenue' => $totalAdminRevenue,
                'total_orders' => $totalOrdersCount,
                'success_rate' => $successRate,
                'total_customers' => User::where('role', 'customer')->count(),
                'active_shippers' => User::where('role', 'shipper')->where('status', 'active')->count(),
                
                // Chart Data
                'revenue_chart' => [
                    'labels' => $chartLabels,
                    'system_revenue' => $chartSystemRevenue,
                    'admin_revenue' => $chartAdminRevenue,
                ],
                'order_status_chart' => [
                    'completed' => $orderStatusStats['completed'] ?? 0,
                    'cancelled' => $orderStatusStats['cancelled'] ?? 0,
                    'pending' => $orderStatusStats['pending'] ?? 0,
                    'delivering' => $orderStatusStats['delivering'] ?? 0,
                    'accepted' => $orderStatusStats['accepted'] ?? 0,
                ],
                
                // Top Restaurants
                'top_restaurants' => $topRestaurants
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
        $products = Product::with(['user', 'category', 'options'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return Inertia::render('Admin/PendingProducts', [
            'products' => $products,
        ]);
    }

    public function allProducts(Request $request)
    {
        $query = Product::with(['user', 'category', 'options', 'gallery']);

        // Search by product name or restaurant name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('restaurant_name', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->orderBy('created_at', 'desc')
                          ->paginate($request->get('pageSize', 12))
                          ->appends($request->query());

        $stats = [
            'total' => Product::count(),
            'approved' => Product::where('status', 'approved')->count(),
            'pending' => Product::where('status', 'pending')->count(),
            'rejected' => Product::where('status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function liveMap()
    {
        return Inertia::render('Admin/LiveMap');
    }

    public function liveMapData()
    {
        // Get orders that are currently active
        $activeOrders = \App\Models\Order::whereNotIn('status', ['completed', 'cancelled', 'failed'])
            ->with([
                'user:id,name,phone,latitude,longitude,address',
                'shipper.user:id,name,phone,latitude,longitude',
                'items.product.user:id,name,restaurant_name,phone,latitude,longitude,address'
            ])
            ->get()
            ->map(function ($order) {
                // Determine restaurant from first item
                $restaurant = null;
                if ($order->items->isNotEmpty() && $order->items->first()->product) {
                    $restaurant = $order->items->first()->product->user;
                }
                
                // Fallback cho Khách hàng (nếu không có toạ độ trong DB)
                $customerLat = $order->user->latitude ?? (16.0544 + (crc32($order->user->id . 'lat') % 200 - 100) / 10000);
                $customerLng = $order->user->longitude ?? (108.2022 + (crc32($order->user->id . 'lng') % 200 - 100) / 10000);
                
                $orderCustomer = $order->user->toArray();
                $orderCustomer['latitude'] = $customerLat;
                $orderCustomer['longitude'] = $customerLng;

                // Fallback cho Nhà hàng
                $orderRestaurant = $restaurant ? $restaurant->toArray() : null;
                if ($orderRestaurant) {
                    $orderRestaurant['latitude'] = $restaurant->latitude ?? (16.0544 + (crc32($restaurant->id . 'lat') % 200 - 100) / 10000);
                    $orderRestaurant['longitude'] = $restaurant->longitude ?? (108.2022 + (crc32($restaurant->id . 'lng') % 200 - 100) / 10000);
                }

                // Fallback cho Shipper
                $orderShipper = null;
                if ($order->shipper && $order->shipper->user) {
                    $orderShipper = $order->shipper->user->toArray();
                    $orderShipper['latitude'] = $order->shipper->current_latitude ?? $order->shipper->user->latitude ?? (16.0544 + (crc32($order->shipper->id . 'lat') % 200 - 100) / 10000);
                    $orderShipper['longitude'] = $order->shipper->current_longitude ?? $order->shipper->user->longitude ?? (108.2022 + (crc32($order->shipper->id . 'lng') % 200 - 100) / 10000);
                }

                return [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'status' => $order->status,
                    'customer' => $orderCustomer,
                    'restaurant' => $orderRestaurant,
                    'shipper' => $orderShipper,
                ];
            });

        return response()->json($activeOrders);
    }

    public function approveProduct(Product $product)
    {
        $product->update(['status' => 'approved', 'is_available' => true]);
        
        // Notify the restaurant
        $product->user->notify(new ProductStatusUpdated($product));
        
        Mail::to($product->user->email)->queue(new ProductApprovalStatus($product, 'approved'));

        return back()->with('message', "Đã duyệt món ăn '{$product->name}' thành công.");
    }

    public function rejectProduct(Request $request, Product $product)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ], [
            'reason.required' => 'Vui lòng cung cấp lý do từ chối để nhà hàng có thể cải thiện.'
        ]);

        $product->update([
            'status' => 'rejected', 
            'is_available' => false,
            'reject_reason' => $request->reason
        ]);
        
        // Notify the restaurant
        $product->user->notify(new ProductStatusUpdated($product));
        
        Mail::to($product->user->email)->queue(new ProductApprovalStatus($product, 'rejected'));

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
        Mail::to($user->email)->queue(new PartnerAccountStatus($user, 'active'));
        return back()->with('message', "Đã duyệt tài khoản cho {$user->name}");
    }
    public function reject(Request $request, $id)
        {
            $request->validate([
                'reason' => 'required|string|max:500'
            ], [
                'reason.required' => 'Vui lòng cung cấp lý do từ chối để đối tác có thể cải thiện hồ sơ.'
            ]);

            // Tìm người dùng theo ID
            $user = \App\Models\User::findOrFail($id);

            // Đổi status thành 'rejected' và lưu lại lý do
            $user->update([
                'status' => 'rejected',
                'reject_reason' => $request->reason
            ]);

            Mail::to($user->email)->queue(new PartnerAccountStatus($user, 'rejected', $request->reason));

            return back()->with('message', 'Đã từ chối và gửi phản hồi cho đối tác.');
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

        // Tổng hợp thống kê
        $stats = [
            'total' => User::count(),
            'customers' => User::where('role', 'customer')->count(),
            'restaurants' => User::where('role', 'restaurant')->count(),
            'shippers' => User::where('role', 'shipper')->count(),
            'blocked' => User::where('status', 'blocked')->count(),
        ];

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'stats' => $stats,
            'filters' => $request->only(['search', 'role', 'status'])
        ]);
    }

    public function toggleBlockUser(Request $request, User $user)
    {
        // Không cho phép Admin tự khóa chính mình
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Bạn không thể tự khóa tài khoản của mình.']);
        }

        // Nếu đang active thì block, nếu đang block thì active
        if ($user->status === 'blocked') {
            $user->update(['status' => 'active', 'block_reason' => null]);
            $message = "Đã MỞ KHÓA tài khoản {$user->name}.";
        } else {
            $request->validate([
                'reason' => 'required|string|max:500'
            ], [
                'reason.required' => 'Vui lòng nhập lý do khóa tài khoản.'
            ]);
            $user->update(['status' => 'blocked', 'block_reason' => $request->reason]);
            $message = "Đã KHÓA tài khoản {$user->name}.";
        }

        return back()->with('message', $message);
    }

    public function analyzeUserRisk(User $user)
    {
        $apiKeyEnv = env('GEMINI_API_KEY');
        if (!$apiKeyEnv) {
            return response()->json(['success' => false, 'message' => 'Hệ thống AI chưa được cấu hình (Thiếu API Key).']);
        }

        // Hỗ trợ nhiều key cách nhau bởi dấu phẩy
        $apiKeys = array_filter(array_map('trim', explode(',', $apiKeyEnv)));
        if (empty($apiKeys)) {
            return response()->json(['success' => false, 'message' => 'Định dạng GEMINI_API_KEY không hợp lệ.']);
        }
        $apiKey = $apiKeys[array_rand($apiKeys)];

        // Gom dữ liệu của người dùng
        $userData = "Tên: {$user->name}, Vai trò: {$user->role}\n";
        
        if ($user->role === 'customer') {
            $totalOrders = \App\Models\Order::where('user_id', $user->id)->count();
            $cancelledOrders = \App\Models\Order::where('user_id', $user->id)->where('status', 'cancelled')->count();
            $userData .= "Tổng số đơn đã đặt: {$totalOrders}\nSố đơn đã hủy: {$cancelledOrders}\n";
        } elseif ($user->role === 'restaurant') {
            $products = \App\Models\Product::where('user_id', $user->id)->count();
            $totalOrders = \App\Models\Order::whereHas('items.product', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count();
            $cancelledOrders = \App\Models\Order::whereHas('items.product', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('status', 'cancelled')->count();
            $userData .= "Tổng số món ăn đang bán: {$products}\nTổng số đơn nhận được: {$totalOrders}\nSố đơn bị hủy: {$cancelledOrders}\n";
        } elseif ($user->role === 'shipper') {
            $shipper = \App\Models\Shipper::where('user_id', $user->id)->first();
            if ($shipper) {
                $totalOrders = \App\Models\Order::where('shipper_id', $shipper->id)->count();
                $cancelledOrders = \App\Models\Order::where('shipper_id', $shipper->id)->where('status', 'cancelled')->count();
                $userData .= "Tổng số đơn đã nhận giao: {$totalOrders}\nSố đơn giao thất bại/hủy: {$cancelledOrders}\n";
            }
        }

        $prompt = "Đóng vai trò là một chuyên gia phân tích rủi ro hệ thống của ứng dụng đặt đồ ăn FoodXpress. Hãy phân tích cực kỳ ngắn gọn (khoảng 2-3 câu) dữ liệu người dùng sau và đưa ra kết luận mức độ rủi ro (Cao/Trung bình/Thấp) và lời khuyên cho Admin xem có nên khóa tài khoản này không. Sử dụng ngôn từ chuyên nghiệp. KHÔNG dùng markdown định dạng in đậm in nghiêng phức tạp, chỉ trả về text thuần.\n\nDữ liệu: \n{$userData}";

        try {
            $response = \Illuminate\Support\Facades\Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? "Không thể đưa ra đánh giá rủi ro lúc này.";
                return response()->json(['success' => true, 'analysis' => $reply]);
            }
            $errorMsg = $response->json()['error']['message'] ?? 'Lỗi kết nối tới AI.';
            return response()->json(['success' => false, 'message' => 'Lỗi từ Gemini: ' . $errorMsg]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
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