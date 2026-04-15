<template>
    <ShipperLayout>
        <template #title>Chi tiết đơn hàng: {{ order.order_code }}</template>
        <template #subtitle>Quản lý giao hàng</template>

        <div class="flex justify-between items-center mb-6">
            <div></div>
            <Link
                href="/shipper/dashboard"
                class="text-blue-600 hover:text-blue-800"
                >← Quay lại Dashboard</Link
            >
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Order Info -->
            <div>
                <h2 class="text-xl font-semibold mb-4">Thông tin đơn hàng</h2>
                <div class="space-y-2">
                    <p>
                        <strong>Khách hàng:</strong>
                        {{ order.user.name }}
                    </p>
                    <p>
                        <strong>Địa chỉ:</strong>
                        {{ order.address }}
                    </p>
                    <p>
                        <strong>Số điện thoại:</strong>
                        {{ order.phone }}
                    </p>
                    <p>
                        <strong>Ghi chú:</strong>
                        {{ order.note || "Không có" }}
                    </p>
                    <p>
                        <strong>Trạng thái:</strong>
                        {{ getStatusText(order.status) }}
                    </p>
                    <p>
                        <strong>Tổng tiền:</strong>
                        {{ formatCurrency(order.total) }}
                    </p>
                    <p v-if="order.shipper_fee">
                        <strong>Phí shipper:</strong>
                        {{ formatCurrency(order.shipper_fee) }}
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 space-y-3">
                    <button
                        v-if="order.status === 'confirmed'"
                        @click="confirmPickup"
                        class="w-full bg-blue-500 text-white py-3 rounded-lg hover:bg-blue-600"
                    >
                        Xác nhận đã lấy hàng
                    </button>
                    <button
                        v-if="order.status === 'picked_up'"
                        @click="startDelivery"
                        class="w-full bg-green-500 text-white py-3 rounded-lg hover:bg-green-600"
                    >
                        Bắt đầu giao hàng
                    </button>
                    <button
                        v-if="order.status === 'shipping'"
                        @click="completeOrder"
                        class="w-full bg-purple-500 text-white py-3 rounded-lg hover:bg-purple-600"
                    >
                        Hoàn thành giao hàng
                    </button>
                </div>
            </div>

            <!-- Map/Location -->
            <div>
                <h2 class="text-xl font-semibold mb-4">Vị trí giao hàng</h2>
                <div
                    id="map"
                    class="h-64 bg-gray-200 rounded-lg flex items-center justify-center"
                >
                    <p class="text-gray-500">Bản đồ sẽ hiển thị ở đây</p>
                    <!-- In real app, integrate Google Maps or similar -->
                </div>
            </div>
        </div>

        <!-- Order Timeline -->
        <div class="mt-8">
            <h2 class="text-xl font-semibold mb-4">Quy trình giao hàng</h2>
            <div class="space-y-4">
                <div class="flex items-start gap-4">
                    <div
                        :class="[
                            'w-8 h-8 rounded-full flex items-center justify-center text-sm font-black',
                            order.status !== 'pending'
                                ? 'bg-green-100 text-green-600'
                                : 'bg-gray-100 text-gray-400',
                        ]"
                    >
                        📋
                    </div>
                    <div class="flex-1">
                        <p class="font-black text-gray-800">Đã nhận đơn</p>
                        <p class="text-sm text-gray-500">
                            Shipper đã nhận đơn hàng này
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div
                        :class="[
                            'w-8 h-8 rounded-full flex items-center justify-center text-sm font-black',
                            [
                                'confirmed',
                                'picked_up',
                                'shipping',
                                'completed',
                            ].includes(order.status)
                                ? 'bg-green-100 text-green-600'
                                : 'bg-gray-100 text-gray-400',
                        ]"
                    >
                        🏪
                    </div>
                    <div class="flex-1">
                        <p class="font-black text-gray-800">
                            Đến quán lấy hàng
                        </p>
                        <p class="text-sm text-gray-500">
                            Shipper đang đến quán để lấy hàng
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div
                        :class="[
                            'w-8 h-8 rounded-full flex items-center justify-center text-sm font-black',
                            ['picked_up', 'shipping', 'completed'].includes(
                                order.status,
                            )
                                ? 'bg-green-100 text-green-600'
                                : 'bg-gray-100 text-gray-400',
                        ]"
                    >
                        📦
                    </div>
                    <div class="flex-1">
                        <p class="font-black text-gray-800">Đã lấy hàng</p>
                        <p class="text-sm text-gray-500">
                            Shipper đã lấy hàng từ quán
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div
                        :class="[
                            'w-8 h-8 rounded-full flex items-center justify-center text-sm font-black',
                            ['shipping', 'completed'].includes(order.status)
                                ? 'bg-green-100 text-green-600'
                                : 'bg-gray-100 text-gray-400',
                        ]"
                    >
                        🚚
                    </div>
                    <div class="flex-1">
                        <p class="font-black text-gray-800">Đang giao hàng</p>
                        <p class="text-sm text-gray-500">
                            Shipper đang giao hàng đến khách hàng
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div
                        :class="[
                            'w-8 h-8 rounded-full flex items-center justify-center text-sm font-black',
                            order.status === 'completed'
                                ? 'bg-green-100 text-green-600'
                                : 'bg-gray-100 text-gray-400',
                        ]"
                    >
                        ✅
                    </div>
                    <div class="flex-1">
                        <p class="font-black text-gray-800">Hoàn thành</p>
                        <p class="text-sm text-gray-500">
                            Đơn hàng đã được giao thành công
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="mt-8">
            <h2 class="text-xl font-semibold mb-4">Chi tiết món ăn</h2>
            <div class="space-y-4">
                <div
                    v-for="item in order.items"
                    :key="item.id"
                    class="flex justify-between items-center border-b pb-2"
                >
                    <div>
                        <h3 class="font-medium">
                            {{ item.product.name }}
                        </h3>
                        <p class="text-sm text-gray-600">
                            Số lượng: {{ item.quantity }}
                        </p>
                    </div>
                    <p class="font-semibold">
                        {{ formatCurrency(item.price * item.quantity) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex gap-4">
            <Link
                href="/shipper/dashboard"
                class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600"
            >
                Quay lại Dashboard
            </Link>
        </div>
    </ShipperLayout>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import ShipperLayout from "@/Layouts/ShipperLayout.vue";

const props = defineProps({
    order: Object,
});

const order = ref(props.order);

const confirmPickup = async () => {
    try {
        const response = await fetch(
            `/api/shipper/orders/${order.value.id}/confirm-pickup`,
            {
                method: "POST",
                headers: {
                    Accept: "application/json",
                },
                credentials: "include",
            },
        );
        if (response.ok) {
            order.value.status = "picked_up";
        }
    } catch (error) {
        console.error("Error confirming pickup:", error);
    }
};

const completeOrder = async () => {
    try {
        const response = await fetch(
            `/api/shipper/orders/${order.value.id}/complete`,
            {
                method: "POST",
                headers: {
                    Accept: "application/json",
                },
                credentials: "include",
            },
        );
        if (response.ok) {
            router.visit("/shipper/dashboard");
        }
    } catch (error) {
        console.error("Error completing order:", error);
    }
};

const startDelivery = async () => {
    try {
        // Update order status to shipping
        order.value.status = "shipping";
        // Start real-time location tracking
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(
                updateLocation,
                handleLocationError,
                {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0,
                },
            );
        }
    } catch (error) {
        console.error("Error starting delivery:", error);
    }
};

const updateLocation = async (position) => {
    try {
        await window.csrfFetch("/api/shipper/location", {
            method: "POST",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
            }),
        });
    } catch (error) {
        console.error("Error updating location:", error);
    }
};

const handleLocationError = (error) => {
    console.error("Location error:", error);
};

const getStatusText = (status) => {
    const statuses = {
        pending: "Chờ xác nhận",
        confirmed: "Đã xác nhận",
        shipping: "Đang giao",
        picked_up: "Đã lấy hàng",
        completed: "Hoàn thành",
        cancelled: "Đã hủy",
    };
    return statuses[status] || status;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(amount);
};
</script>
