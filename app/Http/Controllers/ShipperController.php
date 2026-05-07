<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipper;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShipperController extends Controller
{
    protected function getShipper(): Shipper
    {
        return Shipper::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'status' => 'available',
                'current_latitude' => 0,
                'current_longitude' => 0,
                'vehicle_type' => 'xe máy',
                'rating' => 0,
                'total_deliveries' => 0,
            ]
        );
    }

    public function index()
    {
        // Lấy shipper hiện tại
        $shipper = $this->getShipper();

        $shipperLat = $shipper->current_latitude;
        $shipperLng = $shipper->current_longitude;

        // Đơn hàng đang giao
        $currentOrders = Order::where('shipper_id', $shipper->id)
            ->whereIn('status', ['assigned', 'confirmed', 'shipping', 'arrived', 'picked_up', 'delivering'])
            ->with('items.product.user', 'user')
            ->get()
            ->filter(function ($order) {
                return $order->items->count() > 0;
            })
            ->map(function ($order) {
                $restaurantUser = $order->items->first()?->product?->user;

                return [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'address' => $order->address,
                    'phone' => $order->phone,
                    'status' => $order->status,
                    'is_food_ready' => $order->is_food_ready,
                    'total' => $order->total,
                    'shipping_fee' => $order->shipping_fee,
                    'shipper_tip' => $order->shipper_tip,
                    'distance' => $order->distance,
                    'user' => [
                        'name' => $order->user?->name,
                        'latitude' => $order->user?->latitude,
                        'longitude' => $order->user?->longitude,
                    ],
                    'restaurant' => [
                        'name' => $restaurantUser?->restaurant_name ?? $restaurantUser?->name,
                        'latitude' => $restaurantUser?->latitude,
                        'longitude' => $restaurantUser?->longitude,
                    ],
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product' => [
                                'name' => $item->product?->name,
                            ],
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                        ];
                    }),
                ];
            })->values();

        // Đơn hàng available (chưa nhận) - chỉ lấy confirmed và chưa có shipper gán
        $availableOrders = Order::whereNull('shipper_id')
            ->where('status', 'confirmed')
            ->with('items.product.user', 'user')
            ->get()
            ->filter(function ($order) {
                return $order->items->count() > 0;
            })
            ->map(function ($order) use ($shipperLat, $shipperLng) {
                $restaurantUser = $order->items->first()->product?->user;
                $restaurantLat = $restaurantUser?->latitude;
                $restaurantLng = $restaurantUser?->longitude;

                $distance = null;
                if ($shipperLat && $shipperLng && $restaurantLat && $restaurantLng) {
                    $distance = $this->distanceBetweenCoordinates(
                        $shipperLat,
                        $shipperLng,
                        $restaurantLat,
                        $restaurantLng,
                    );
                }

                return [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'address' => $order->address,
                    'phone' => $order->phone,
                    'status' => $order->status,
                    'is_food_ready' => $order->is_food_ready,
                    'total' => $order->total,
                    'shipping_fee' => $order->shipping_fee,
                    'shipper_tip' => $order->shipper_tip,
                    'distance' => $order->distance,
                    'distance_to_restaurant' => $distance,
                    'user' => [
                        'name' => $order->user?->name,
                        'latitude' => $order->user?->latitude,
                        'longitude' => $order->user?->longitude,
                    ],
                    'restaurant' => [
                        'name' => $restaurantUser?->restaurant_name ?? $restaurantUser?->name,
                        'latitude' => $restaurantLat,
                        'longitude' => $restaurantLng,
                    ],
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product' => [
                                'name' => $item->product?->name,
                            ],
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                        ];
                    }),
                ];
            })
            ->filter(function ($order) {
                // Nếu có distance, phải <= 2km. Nếu không có distance (chưa set vị trí), vẫn show để chọn
                return $order['distance_to_restaurant'] === null || $order['distance_to_restaurant'] <= 2;
            })
            ->values();

        // Đơn hàng đã gán cho shipper này (chờ accept)
        $assignedOrders = Order::where('shipper_id', $shipper->id)
            ->whereIn('status', ['assigned', 'confirmed'])
            ->with('items.product.user', 'user')
            ->get()
            ->filter(function ($order) {
                return $order->items->count() > 0;
            })
            ->map(function ($order) {
                $restaurantUser = $order->items->first()?->product?->user;

                return [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'address' => $order->address,
                    'phone' => $order->phone,
                    'status' => $order->status,
                    'is_food_ready' => $order->is_food_ready,
                    'total' => $order->total,
                    'shipping_fee' => $order->shipping_fee,
                    'shipper_tip' => $order->shipper_tip,
                    'distance' => $order->distance,
                    'user' => [
                        'name' => $order->user?->name,
                        'latitude' => $order->user?->latitude,
                        'longitude' => $order->user?->longitude,
                    ],
                    'restaurant' => [
                        'name' => $restaurantUser?->restaurant_name ?? $restaurantUser?->name,
                        'latitude' => $restaurantUser?->latitude,
                        'longitude' => $restaurantUser?->longitude,
                    ],
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product' => [
                                'name' => $item->product?->name,
                            ],
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                        ];
                    }),
                ];
            })->values();

        return response()->json([
            'current_orders' => $currentOrders,
            'available_orders' => $availableOrders,
            'assigned_orders' => $assignedOrders,
            'shipper' => [
                'id' => $shipper->id,
                'user_id' => $shipper->user_id,
                'status' => $shipper->status,
                'current_latitude' => $shipper->current_latitude,
                'current_longitude' => $shipper->current_longitude,
            ]
        ]);
    }

    public function findNearestShipper($restaurantLat, $restaurantLng, $maxDistance = 2, array $excludeShipperIds = [])
    {
        $shippers = Shipper::whereIn('status', ['available', 'online'])
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude');

        if (!empty($excludeShipperIds)) {
            $shippers->whereNotIn('id', $excludeShipperIds);
        }

        $shippers = $shippers->get();

        $nearestShipper = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($shippers as $shipper) {
            $distance = $this->distanceBetweenCoordinates(
                $restaurantLat,
                $restaurantLng,
                $shipper->current_latitude,
                $shipper->current_longitude
            );

            if ($distance <= $maxDistance && $distance < $minDistance) {
                $minDistance = $distance;
                $nearestShipper = $shipper;
            }
        }

        return $nearestShipper;
    }

    public function assignShipperToOrder($orderId, array $excludeShipperIds = []): bool
    {
        $order = Order::with('items.product.user')->lockForUpdate()->find($orderId);

        if ($order->shipper_id) {
            return false; // Already assigned to someone
        }

        $restaurantUser = $order->items->first()->product?->user;
        if (!$restaurantUser || !$restaurantUser->latitude || !$restaurantUser->longitude) {
            return false; // No restaurant location
        }

        $shipper = $this->findNearestShipper(
            $restaurantUser->latitude,
            $restaurantUser->longitude,
            2,
            $excludeShipperIds,
        );

        if ($shipper) {
            // Lock shipper để tránh race condition
            $lockedShipper = Shipper::where('id', $shipper->id)->lockForUpdate()->first();
            if ($lockedShipper && in_array($lockedShipper->status, ['available', 'online'])) {
                $order->update(['shipper_id' => $shipper->id, 'status' => 'assigned']);
                return true;
            }
        }

        return false;
    }

    public function acceptOrder(Request $request, $orderId)
    {
        $shipper = $this->getShipper();

        if (!in_array($shipper->status, ['available', 'online'], true)) {
            return response()->json(['error' => 'Shipper not available'], 400);
        }

        $order = Order::findOrFail($orderId);

        if ($order->shipper_id && $order->shipper_id !== $shipper->id) {
            return response()->json(['error' => 'Order not available'], 400);
        }

        if (! in_array($order->status, ['assigned', 'confirmed'], true)) {
            return response()->json(['error' => 'Order not available'], 400);
        }

        $oldStatus = $order->status;
        $order->update([
            'shipper_id' => $shipper->id,
            'status' => 'shipping',
            'delivering_at' => now() // Giai đoạn 4: Shipper bắt đầu đi tới quán
        ]);

        $order->user->notify(new \App\Notifications\OrderStatusUpdated($order, $oldStatus, 'shipping'));
        $restaurantUser = $order->items->first()?->product?->user;
        if ($restaurantUser) {
            $restaurantUser->notify(new \App\Notifications\OrderStatusUpdated($order, $oldStatus, 'shipping'));
        }
        
        event(new \App\Events\OrderStatusUpdated($order));

        $shipper->update(['status' => 'busy']);

        return response()->json(['message' => 'Order accepted']);
    }

    public function rejectOrder(Request $request, $orderId)
    {
        $shipper = $this->getShipper();

        $order = Order::where('id', $orderId)
            ->where('shipper_id', $shipper->id)
            ->firstOrFail();

        if (!in_array($order->status, ['assigned', 'confirmed'])) {
            return response()->json(['error' => 'Order không ở trạng thái assigned hoặc confirmed'], 400);
        }

        $order->update([
            'shipper_id' => null,
            'status' => 'confirmed', // Giai đoạn 3 (lại): Trở lại trạng thái chờ shipper khác
        ]);

        $this->assignShipperToOrder($orderId, [$shipper->id]);

        $order->refresh();
        event(new \App\Events\OrderStatusUpdated($order));

        return response()->json([
            'message' => 'Đã hoãn đơn. Hệ thống sẽ tìm shipper khác.',
            'status' => $order->status,
            'shipper_id' => $order->shipper_id,
        ]);
    }

    public function arriveAtRestaurant($orderId)
    {
        $shipper = $this->getShipper();
        $order = Order::where('id', $orderId)->where('shipper_id', $shipper->id)->firstOrFail();

        if (!in_array($order->status, ['shipping', 'confirmed'])) {
            return response()->json(['error' => 'Invalid order status'], 400);
        }

        $restaurantUser = $order->items->first()->product?->user;
        if (! $restaurantUser || ! $restaurantUser->latitude || ! $restaurantUser->longitude) {
            return response()->json(['error' => 'Restaurant location not available'], 400);
        }

        if (! $shipper->current_latitude || ! $shipper->current_longitude) {
            return response()->json(['error' => 'Shipper location not available'], 400);
        }

        $distanceToRestaurant = $this->distanceBetweenCoordinates(
            $shipper->current_latitude,
            $shipper->current_longitude,
            $restaurantUser->latitude,
            $restaurantUser->longitude,
        );

        if ($distanceToRestaurant > 0.3) {
            return response()->json(['error' => 'Bạn chưa đến gần quán, vui lòng đi đúng vị trí trước khi xác nhận.'], 400);
        }

        $oldStatus = $order->status;
        $order->update([
            'status' => 'arrived',
            'shipping_at' => now() // Giai đoạn 4: Shipper đã đến quán
        ]);

        $order->user->notify(new \App\Notifications\OrderStatusUpdated($order, $oldStatus, 'arrived'));
        if ($restaurantUser) {
            $restaurantUser->notify(new \App\Notifications\OrderStatusUpdated($order, $oldStatus, 'arrived'));
        }
        
        event(new \App\Events\OrderStatusUpdated($order));

        return response()->json(['message' => 'Arrived at restaurant']);
    }

    public function confirmPickup($orderId)
    {
        $shipper = $this->getShipper();
        $order = Order::where('id', $orderId)->where('shipper_id', $shipper->id)->firstOrFail();

        if (!in_array($order->status, ['arrived', 'shipping', 'confirmed'])) {
            return response()->json(['error' => 'Invalid order status'], 400);
        }

        $restaurantUser = $order->items->first()->product?->user;
        if (! $restaurantUser || ! $restaurantUser->latitude || ! $restaurantUser->longitude) {
            return response()->json(['error' => 'Restaurant location not available'], 400);
        }

        if (! $shipper->current_latitude || ! $shipper->current_longitude) {
            return response()->json(['error' => 'Shipper location not available'], 400);
        }

        $distanceToRestaurant = $this->distanceBetweenCoordinates(
            $shipper->current_latitude,
            $shipper->current_longitude,
            $restaurantUser->latitude,
            $restaurantUser->longitude,
        );

        if ($distanceToRestaurant > 0.3) {
            return response()->json(['error' => 'Bạn chưa đến gần quán, vui lòng đi đúng vị trí trước khi xác nhận lấy hàng.'], 400);
        }

        DB::beginTransaction();
        try {
            $oldStatus = $order->status;
            $order->update([
                'status' => 'picked_up',
                'picked_up_at' => now() // Giai đoạn 5: Shipper lấy hàng từ quán
            ]);

            $order->user->notify(new \App\Notifications\OrderStatusUpdated($order, $oldStatus, 'picked_up'));
            if ($restaurantUser) {
                $restaurantUser->notify(new \App\Notifications\OrderStatusUpdated($order, $oldStatus, 'picked_up'));
            }
            
            event(new \App\Events\OrderStatusUpdated($order));

            // Trừ tiền ví Shipper (Ứng trả quán)
            $shipperUser = $shipper->user;
            $balanceBeforeShipper = $shipperUser->wallet_balance;
            $shipperUser->wallet_balance -= $order->restaurant_revenue;
            $shipperUser->save();

            WalletTransaction::create([
                'user_id' => $shipperUser->id,
                'type' => 'order_payment',
                'amount' => -$order->restaurant_revenue,
                'balance_before' => $balanceBeforeShipper,
                'balance_after' => $shipperUser->wallet_balance,
                'description' => 'Trừ tiền ứng trả quán ăn cho đơn hàng #' . $order->order_code,
                'reference_id' => $order->id,
            ]);

            // Cộng tiền ví Quán
            $balanceBeforeRestaurant = $restaurantUser->wallet_balance;
            $restaurantUser->wallet_balance += $order->restaurant_revenue;
            $restaurantUser->save();

            WalletTransaction::create([
                'user_id' => $restaurantUser->id,
                'type' => 'order_revenue',
                'amount' => $order->restaurant_revenue,
                'balance_before' => $balanceBeforeRestaurant,
                'balance_after' => $restaurantUser->wallet_balance,
                'description' => 'Nhận doanh thu từ đơn hàng #' . $order->order_code,
                'reference_id' => $order->id,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'System error: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Pickup confirmed']);
    }

    public function completeOrder($orderId)
    {
        $shipper = $this->getShipper();
        $order = Order::where('id', $orderId)->where('shipper_id', $shipper->id)->firstOrFail();

        if ($order->status !== 'picked_up') {
            return response()->json(['error' => 'Invalid order status'], 400);
        }

        $customer = $order->user;
        if (! $customer || ! $customer->latitude || ! $customer->longitude) {
            return response()->json(['error' => 'Customer location not available'], 400);
        }

        if (! $shipper->current_latitude || ! $shipper->current_longitude) {
            return response()->json(['error' => 'Shipper location not available'], 400);
        }

        $distanceToCustomer = $this->distanceBetweenCoordinates(
            $shipper->current_latitude,
            $shipper->current_longitude,
            $customer->latitude,
            $customer->longitude,
        );

        if ($distanceToCustomer > 0.3) {
            return response()->json(['error' => 'Bạn đang ở sai vị trí giao hàng. Vui lòng di chuyển vào bán kính 300m của khách hàng.'], 400);
        }

        // Tính phí shipper dựa trên khoảng cách từ quán đến khách hàng
        $restaurantUser = $order->items->first()->product?->user;

        if ($restaurantUser && $customer && $restaurantUser->latitude && $restaurantUser->longitude && $customer->latitude && $customer->longitude) {
            $distance = $this->distanceBetweenCoordinates(
                $restaurantUser->latitude,
                $restaurantUser->longitude,
                $customer->latitude,
                $customer->longitude
            );

            $baseFee = 12000; // 12k
            $additionalFee = max(0, $distance - 2) * 3000; // 3k/km từ km thứ 3
            $shipperFee = $baseFee + $additionalFee;

            $order->update(['shipper_fee' => $shipperFee]);
        }

        DB::beginTransaction();
        try {
            $oldStatus = $order->status;
            $order->update([
                'status' => 'completed',
                'completed_at' => now() // Giai đoạn 3: Shipper hoàn tất giao hàng
            ]);
            
            $customer->notify(new \App\Notifications\OrderStatusUpdated($order, $oldStatus, 'completed'));
            if ($restaurantUser) {
                $restaurantUser->notify(new \App\Notifications\OrderStatusUpdated($order, $oldStatus, 'completed'));
            }
            event(new \App\Events\OrderStatusUpdated($order));
            
            $shipper->increment('total_deliveries');
            $shipper->update(['status' => 'available']);

            $shipperUser = $shipper->user;

            if ($order->payment_method === 'cod') {
                // Nếu COD: Shipper đã thu $order->total tiền mặt.
                // Lợi nhuận shipper mong muốn: $order->shipper_fee + $order->shipper_tip.
                // Trừ phí nền tảng và phần chênh lệch ship: 
                // total_deduction = total - (shipper_fee + tip) - restaurant_revenue (đã trừ lúc lấy hàng)
                $feeToDeduct = $order->total - ($order->shipper_fee + $order->shipper_tip) - $order->restaurant_revenue;
                
                $balanceBefore = $shipperUser->wallet_balance;
                $shipperUser->wallet_balance -= $feeToDeduct;
                $shipperUser->save();

                WalletTransaction::create([
                    'user_id' => $shipperUser->id,
                    'type' => 'admin_fee_deduction',
                    'amount' => -$feeToDeduct,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $shipperUser->wallet_balance,
                    'description' => 'Trừ phí nền tảng cho đơn COD #' . $order->order_code,
                    'reference_id' => $order->id,
                ]);
            } else {
                // Nếu VNPay: Nền tảng cầm tiền.
                // Cộng vào ví Shipper: hoàn ứng (restaurant_revenue) + lương (shipper_fee + tip)
                $amountToCredit = $order->restaurant_revenue + $order->shipper_fee + $order->shipper_tip;
                
                $balanceBefore = $shipperUser->wallet_balance;
                $shipperUser->wallet_balance += $amountToCredit;
                $shipperUser->save();

                WalletTransaction::create([
                    'user_id' => $shipperUser->id,
                    'type' => 'deposit',
                    'amount' => $amountToCredit,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $shipperUser->wallet_balance,
                    'description' => 'Hoàn ứng và thanh toán thu nhập cho đơn VNPay #' . $order->order_code,
                    'reference_id' => $order->id,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'System error: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Order completed']);
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        $shipper = $this->getShipper();

        $shipper->update([
            'current_latitude' => $request->latitude,
            'current_longitude' => $request->longitude
        ]);
        
        // Nếu shipper đang giao một đơn, broadcast tọa độ
        if ($request->has('order_id')) {
            event(new \App\Events\ShipperLocationUpdated($request->order_id, $request->latitude, $request->longitude));
        }

        return response()->json(['message' => 'Location updated']);
    }

    public function checkIn()
    {
        $shipper = $this->getShipper();

        if ($shipper->status === 'offline') {
            $shipper->update(['status' => 'available']);
        }

        return response()->json([
            'message' => 'Shipper is now online',
            'status' => $shipper->status,
        ]);
    }

    public function checkOut()
    {
        $shipper = $this->getShipper();

        if ($shipper->status === 'busy') {
            return response()->json(['error' => 'Cannot check out while delivering an order'], 400);
        }

        $shipper->update(['status' => 'offline']);

        return response()->json([
            'message' => 'Shipper is now offline',
            'status' => $shipper->status,
        ]);
    }

    public function income()
    {
        $shipper = $this->getShipper();
        
        $today = now()->startOfDay();
        $startOfWeek = now()->startOfWeek();
        $startOfMonth = now()->startOfMonth();

        $allOrders = \App\Models\Order::where('shipper_id', $shipper->id)
            ->where('status', 'completed')
            ->get();

        $todayIncome = $allOrders->where('updated_at', '>=', $today)->sum(function ($order) {
            return ($order->shipping_fee ?? 0) + ($order->shipper_tip ?? 0);
        });

        $weekIncome = $allOrders->where('updated_at', '>=', $startOfWeek)->sum(function ($order) {
            return ($order->shipping_fee ?? 0) + ($order->shipper_tip ?? 0);
        });

        $monthIncome = $allOrders->where('updated_at', '>=', $startOfMonth)->sum(function ($order) {
            return ($order->shipping_fee ?? 0) + ($order->shipper_tip ?? 0);
        });

        $todayOrdersCount = $allOrders->where('updated_at', '>=', $today)->count();

        // Chart data for last 7 days
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $nextDate = $date->copy()->addDay();
            
            $dayIncome = $allOrders->where('updated_at', '>=', $date)
                ->where('updated_at', '<', $nextDate)
                ->sum(function ($order) {
                    return ($order->shipping_fee ?? 0) + ($order->shipper_tip ?? 0);
                });
                
            $chartData[] = [
                'day' => $date->locale('vi')->isoFormat('ddd'),
                'date' => $date->format('d/m'),
                'income' => $dayIncome
            ];
        }

        $recentOrders = \App\Models\Order::where('shipper_id', $shipper->id)
            ->where('status', 'completed')
            ->with('items.product.user', 'user')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'address' => $order->address,
                    'total' => $order->total,
                    'shipping_fee' => $order->shipping_fee,
                    'shipper_tip' => $order->shipper_tip,
                    'distance' => $order->distance,
                    'completed_at' => $order->updated_at,
                    'restaurant' => [
                        'name' => $order->items->first()?->product?->user?->restaurant_name ?? $order->items->first()?->product?->user?->name ?? 'Quán',
                    ],
                    'user' => [
                        'name' => $order->user?->name,
                    ]
                ];
            });

        return \Inertia\Inertia::render('Shipper/Income', [
            'todayIncome' => $todayIncome,
            'weekIncome' => $weekIncome,
            'monthIncome' => $monthIncome,
            'todayOrdersCount' => $todayOrdersCount,
            'chartData' => $chartData,
            'recentOrders' => $recentOrders,
        ]);
    }

    public function show($orderId)
    {
        $shipper = $this->getShipper();
        $order = \App\Models\Order::where('id', $orderId)->where('shipper_id', $shipper->id)
            ->with('items.product', 'user', 'shipper.user')
            ->firstOrFail();

        return response()->json($order);
    }

    /**
     * Tính khoảng cách giữa hai điểm tọa độ bằng công thức Haversine (km)
     */
    private function distanceBetweenCoordinates($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadiusKm * $c;

        return $distance;
    }
}
