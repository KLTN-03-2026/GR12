<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shipper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderShipperFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_order_and_shipper_tracking_flow(): void
    {
        $restaurant = User::create([
            'name' => 'Restaurant Owner',
            'email' => 'restaurant@example.com',
            'password' => 'password1',
            'phone' => '0901234567',
            'gender' => 'Nam',
            'birthday' => '1985-05-10',
            'occupation' => 'Nhà hàng',
            'role' => 'restaurant',
            'status' => 'active',
            'restaurant_name' => 'Nhà hàng Test',
            'latitude' => 10.7765300,
            'longitude' => 106.7009810,
            'address' => '123 Đường Test, Quận 1',
        ]);

        $category = Category::create([ 'name' => 'Món ăn', 'slug' => 'mon-an' ]);

        $product = Product::create([
            'user_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Cơm gà',
            'slug' => 'com-ga',
            'description' => 'Cơm gà thơm ngon',
            'price' => 60000,
            'discount_price' => null,
            'image' => null,
            'stock_quantity' => 100,
            'is_available' => true,
        ]);

        $customer = User::create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'password' => 'password1',
            'phone' => '0912345678',
            'gender' => 'Nữ',
            'birthday' => '1995-01-01',
            'occupation' => 'Sinh viên',
            'role' => 'customer',
            'status' => 'active',
            'latitude' => 10.7800000,
            'longitude' => 106.7000000,
            'address' => '456 Đường Test, Quận 1',
        ]);

        $shipperUser = User::create([
            'name' => 'Shipper',
            'email' => 'shipper@example.com',
            'password' => 'password1',
            'phone' => '0987654321',
            'gender' => 'Nam',
            'birthday' => '1992-02-02',
            'occupation' => 'Shipper',
            'role' => 'shipper',
            'status' => 'active',
        ]);

        $shipper = Shipper::create([
            'user_id' => $shipperUser->id,
            'status' => 'offline',
            'current_latitude' => 10.7768000,
            'current_longitude' => 106.7005000,
            'vehicle_type' => 'xe máy',
            'rating' => 5.0,
            'total_deliveries' => 0,
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'order_code' => 'FXP-TEST-001',
            'address' => $customer->address,
            'phone' => $customer->phone,
            'note' => 'Giao nhanh',
            'subtotal' => 60000,
            'shipping_fee' => 15000,
            'total' => 75000,
            'payment_method' => 'cod',
            'status' => 'confirmed',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 60000,
            'options' => null,
        ]);

        $response = $this->actingAs($shipperUser, 'sanctum')
            ->postJson('/api/shipper/check-in');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Shipper is now online']);

        $dashboard = $this->actingAs($shipperUser, 'sanctum')
            ->getJson('/api/shipper/dashboard');

        $dashboard->assertStatus(200)
            ->assertJsonCount(0, 'current_orders')
            ->assertJsonCount(1, 'available_orders');

        $this->actingAs($shipperUser, 'sanctum')
            ->postJson("/api/shipper/orders/{$order->id}/accept")
            ->assertStatus(200)
            ->assertJson(['message' => 'Order accepted']);

        $order->refresh();
        $this->assertSame('shipping', $order->status);
        $this->assertSame($shipper->id, $order->shipper_id);

        $dashboard = $this->actingAs($shipperUser, 'sanctum')
            ->getJson('/api/shipper/dashboard');

        $dashboard->assertStatus(200)
            ->assertJsonCount(1, 'current_orders')
            ->assertJsonCount(0, 'available_orders');

        $this->actingAs($shipperUser, 'sanctum')
            ->postJson("/api/shipper/orders/{$order->id}/confirm-pickup")
            ->assertStatus(200)
            ->assertJson(['message' => 'Pickup confirmed']);

        $order->refresh();
        $this->assertSame('picked_up', $order->status);

        $this->actingAs($shipperUser, 'sanctum')
            ->postJson("/api/shipper/orders/{$order->id}/start-delivery")
            ->assertStatus(200)
            ->assertJson(['message' => 'Delivery started']);

        $order->refresh();
        $this->assertSame('delivering', $order->status);

        // Update shipper location to be near customer for completion
        $this->actingAs($shipperUser, 'sanctum')
            ->postJson('/api/shipper/location', [
                'latitude' => 10.7795000,
                'longitude' => 106.6995000,
            ]);

        $this->actingAs($shipperUser, 'sanctum')
            ->postJson("/api/shipper/orders/{$order->id}/complete")
            ->assertStatus(200)
            ->assertJson(['message' => 'Order completed']);

        $order->refresh();
        $this->assertSame('completed', $order->status);

        $shipper->refresh();
        $this->assertSame('available', $shipper->status);
        $this->assertSame(1, $shipper->total_deliveries);

        $details = $this->actingAs($customer, 'sanctum')
            ->getJson("/api/orders/{$order->id}/details");

        $details->assertStatus(200)
            ->assertJsonPath('status', 'completed')
            ->assertJsonPath('shipper.user.name', 'Shipper')
            ->assertJsonPath('restaurant.name', 'Nhà hàng Test');
    }
}
