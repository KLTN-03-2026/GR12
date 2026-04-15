<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShipperController extends Controller
{
    public function index()
    {
        // Lấy shipper hiện tại
        $shipper = Shipper::where('user_id', Auth::id())->first();

        if (!$shipper) {
            return response()->json(['error' => 'Shipper not found'], 404);
        }

        $shipperLat = $shipper->current_latitude;
        $shipperLng = $shipper->current_longitude;

        // Đơn hàng đang giao
        $currentOrders = Order::where('shipper_id', $shipper->id)
            ->whereIn('status', ['shipping', 'picked_up'])
            ->with('items.product.user', 'user')
            ->get()
            ->map(function ($order) {
                $restaurantUser = $order->items->first()->product?->user;

                return [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'address' => $order->address,
                    'phone' => $order->phone,
                    'status' => $order->status,
                    'total' => $order->total,
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
            });

        // Đơn hàng available (chưa có shipper) và nằm trong bán kính 2km từ shipper đến quán
        $availableOrders = Order::whereNull('shipper_id')
            ->where('status', 'confirmed')
            ->with('items.product.user', 'user')
            ->get()
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
                    'total' => $order->total,
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
                return $order['distance_to_restaurant'] !== null && $order['distance_to_restaurant'] <= 2;
            })
            ->values();

        return response()->json([
            'current_orders' => $currentOrders,
            'available_orders' => $availableOrders,
            'shipper' => $shipper
        ]);
    }

    public function findNearestShipper($restaurantLat, $restaurantLng, $maxDistance = 2)
    {
        $shippers = Shipper::where('status', 'available')
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->get();

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

    public function assignShipperToOrder($orderId)
    {
        $order = Order::with('items.product.user')->findOrFail($orderId);

        if ($order->shipper_id) {
            return; // Already assigned
        }

        $restaurantUser = $order->items->first()->product?->user;
        if (!$restaurantUser || !$restaurantUser->latitude || !$restaurantUser->longitude) {
            return; // No restaurant location
        }

        $shipper = $this->findNearestShipper($restaurantUser->latitude, $restaurantUser->longitude);

        if ($shipper) {
            $order->update(['shipper_id' => $shipper->id, 'status' => 'confirmed']);
            $shipper->update(['status' => 'busy']);
        }
    }

    public function acceptOrder(Request $request, $orderId)
    {
        $shipper = Shipper::where('user_id', Auth::id())->first();

        if (!$shipper || $shipper->status !== 'available') {
            return response()->json(['error' => 'Shipper not available'], 400);
        }

        $order = Order::findOrFail($orderId);

        if ($order->shipper_id || $order->status !== 'confirmed') {
            return response()->json(['error' => 'Order not available'], 400);
        }

        $order->update([
            'shipper_id' => $shipper->id,
            'status' => 'shipping'
        ]);

        $shipper->update(['status' => 'busy']);

        return response()->json(['message' => 'Order accepted']);
    }

    public function confirmPickup($orderId)
    {
        $shipper = Shipper::where('user_id', Auth::id())->first();
        $order = Order::where('id', $orderId)->where('shipper_id', $shipper->id)->firstOrFail();

        if ($order->status !== 'shipping') {
            return response()->json(['error' => 'Invalid order status'], 400);
        }

        $order->update(['status' => 'picked_up']);

        return response()->json(['message' => 'Pickup confirmed']);
    }

    public function startDelivery($orderId)
    {
        $shipper = Shipper::where('user_id', Auth::id())->first();
        $order = Order::where('id', $orderId)->where('shipper_id', $shipper->id)->firstOrFail();

        if ($order->status !== 'picked_up') {
            return response()->json(['error' => 'Invalid order status'], 400);
        }

        $order->update(['status' => 'shipping']);

        return response()->json(['message' => 'Delivery started']);
    }

    public function completeOrder($orderId)
    {
        $shipper = Shipper::where('user_id', Auth::id())->first();
        $order = Order::where('id', $orderId)->where('shipper_id', $shipper->id)->firstOrFail();

        if ($order->status !== 'picked_up') {
            return response()->json(['error' => 'Invalid order status'], 400);
        }

        // Tính phí shipper dựa trên khoảng cách từ quán đến khách hàng
        $restaurantUser = $order->items->first()->product?->user;
        $customer = $order->user;

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

        $order->update(['status' => 'completed']);
        $shipper->increment('total_deliveries');
        $shipper->update(['status' => 'available']);

        return response()->json(['message' => 'Order completed']);
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        $shipper = Shipper::where('user_id', Auth::id())->firstOrFail();

        $shipper->update([
            'current_latitude' => $request->latitude,
            'current_longitude' => $request->longitude
        ]);

        return response()->json(['message' => 'Location updated']);
    }

    public function checkIn()
    {
        $shipper = Shipper::where('user_id', Auth::id())->firstOrFail();

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
        $shipper = Shipper::where('user_id', Auth::id())->firstOrFail();

        if ($shipper->status === 'busy') {
            return response()->json(['error' => 'Cannot check out while delivering an order'], 400);
        }

        $shipper->update(['status' => 'offline']);

        return response()->json([
            'message' => 'Shipper is now offline',
            'status' => $shipper->status,
        ]);
    }

    public function show($orderId)
    {
        $shipper = Shipper::where('user_id', Auth::id())->first();
        $order = Order::where('id', $orderId)->where('shipper_id', $shipper->id)
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
