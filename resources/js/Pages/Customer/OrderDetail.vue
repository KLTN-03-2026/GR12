<template>
    <GuestLayout>
        <Head :title="`Đơn hàng ${order.order_code} - FoodXpress`" />

        <div class="min-h-screen bg-[#f8f9fb] py-12 px-4">
            <div class="max-w-4xl mx-auto">
                <div
                    class="bg-white rounded-[2.5rem] overflow-hidden shadow-sm border border-gray-100 mb-8"
                >
                    <div
                        class="p-6 md:p-8 border-b border-gray-50 bg-gray-50/30"
                    >
                        <div
                            class="flex flex-wrap justify-between items-center gap-4"
                        >
                            <div>
                                <p
                                    class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1"
                                >
                                    Mã đơn hàng
                                </p>
                                <h1
                                    class="text-2xl font-black text-gray-800 tracking-tight"
                                >
                                    #{{ order.order_code }}
                                </h1>
                            </div>
                            <div
                                :class="getStatusStyle(order.status)"
                                class="px-5 py-2 rounded-2xl text-xs font-black uppercase tracking-widest border shadow-sm"
                            >
                                {{ getStatusText(order.status) }}
                            </div>
                        </div>
                    </div>

                    <div class="p-6 md:p-8 space-y-6">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex items-center justify-between gap-4"
                        >
                            <div class="flex items-center gap-4 flex-1">
                                <div
                                    class="w-16 h-16 rounded-2xl overflow-hidden shadow-md shrink-0 border border-gray-100"
                                >
                                    <img
                                        :src="'/storage/' + item.product.image"
                                        class="w-full h-full object-cover"
                                    />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4
                                        class="font-black text-gray-800 line-clamp-1 italic"
                                    >
                                        {{ item.product.name }}
                                    </h4>
                                    <p
                                        class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1"
                                    >
                                        Số lượng: {{ item.quantity }} •
                                        {{ formatPrice(item.price) }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right font-black text-gray-700">
                                {{ formatPrice(item.price * item.quantity) }}
                            </div>
                        </div>
                    </div>

                    <div
                        class="p-6 md:p-8 bg-gray-50/50 border-t border-gray-50"
                    >
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="font-black text-gray-800 mb-4">
                                    Thông tin giao hàng
                                </h3>
                                <div class="space-y-2 text-sm">
                                    <p>
                                        <span class="font-bold">Địa chỉ:</span>
                                        {{ order.address }}
                                    </p>
                                    <p>
                                        <span class="font-bold"
                                            >Số điện thoại:</span
                                        >
                                        {{ order.phone }}
                                    </p>
                                    <p v-if="order.note">
                                        <span class="font-bold">Ghi chú:</span>
                                        {{ order.note }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-black text-gray-800 mb-4">
                                    Tóm tắt đơn hàng
                                </h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span>Tạm tính:</span>
                                        <span>{{
                                            formatPrice(order.subtotal)
                                        }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Phí giao hàng:</span>
                                        <span>{{
                                            formatPrice(order.shipping_fee)
                                        }}</span>
                                    </div>
                                    <div
                                        class="flex justify-between font-black text-lg border-t pt-2"
                                    >
                                        <span>Tổng cộng:</span>
                                        <span>{{
                                            formatPrice(order.total)
                                        }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipper Information -->
                <div
                    v-if="order.shipper"
                    class="bg-white rounded-[2.5rem] overflow-hidden shadow-sm border border-gray-100 mb-8"
                >
                    <div
                        class="p-6 md:p-8 border-b border-gray-50 bg-blue-50/30"
                    >
                        <h3 class="font-black text-gray-800 mb-4">
                            Thông tin shipper
                        </h3>
                        <div class="flex items-center gap-4">
                            <UserAvatar
                                :user="order.shipper.user"
                                class="w-12 h-12"
                            />
                            <div>
                                <p class="font-black text-gray-800">
                                    {{ order.shipper.user.name }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ order.shipper.user.phone }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 md:p-8">
                        <div
                            id="order-map"
                            class="h-64 w-full bg-gray-200 rounded-2xl mb-4"
                        ></div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="bg-gray-50 p-4 rounded-2xl">
                                <p class="text-gray-400 font-bold">
                                    Vị trí hiện tại
                                </p>
                                <p class="mt-1 font-black text-gray-800">
                                    {{
                                        order.shipper.current_latitude
                                            ? `${order.shipper.current_latitude.toFixed(4)}, ${order.shipper.current_longitude.toFixed(4)}`
                                            : "Chưa cập nhật"
                                    }}
                                </p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-2xl">
                                <p class="text-gray-400 font-bold">
                                    Trạng thái
                                </p>
                                <p class="mt-1 font-black text-gray-800">
                                    {{ getShipperStatus(order.shipper.status) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Timeline -->
                <div
                    class="bg-white rounded-[2.5rem] overflow-hidden shadow-sm border border-gray-100"
                >
                    <div
                        class="p-6 md:p-8 border-b border-gray-50 bg-gray-50/30"
                    >
                        <h3 class="font-black text-gray-800">
                            Quy trình giao hàng
                        </h3>
                    </div>

                    <div class="p-6 md:p-8">
                        <div class="space-y-6">
                            <div class="flex items-start gap-4">
                                <div
                                    :class="[
                                        'w-8 h-8 rounded-full flex items-center justify-center text-sm font-black',
                                        order.status === 'pending'
                                            ? 'bg-amber-100 text-amber-600'
                                            : 'bg-green-100 text-green-600',
                                    ]"
                                >
                                    📋
                                </div>
                                <div class="flex-1">
                                    <p class="font-black text-gray-800">
                                        Đơn hàng đã đặt
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Đơn hàng của bạn đã được tạo thành công
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ formatDate(order.created_at) }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div
                                    :class="[
                                        'w-8 h-8 rounded-full flex items-center justify-center text-sm font-black',
                                        [
                                            'processing',
                                            'confirmed',
                                            'shipping',
                                            'picked_up',
                                            'completed',
                                        ].includes(order.status)
                                            ? 'bg-green-100 text-green-600'
                                            : 'bg-gray-100 text-gray-400',
                                    ]"
                                >
                                    👨‍🍳
                                </div>
                                <div class="flex-1">
                                    <p class="font-black text-gray-800">
                                        Quán đang chuẩn bị
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Nhà hàng đang chuẩn bị món ăn cho bạn
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div
                                    :class="[
                                        'w-8 h-8 rounded-full flex items-center justify-center text-sm font-black',
                                        [
                                            'confirmed',
                                            'shipping',
                                            'picked_up',
                                            'completed',
                                        ].includes(order.status)
                                            ? 'bg-green-100 text-green-600'
                                            : 'bg-gray-100 text-gray-400',
                                    ]"
                                >
                                    🛵
                                </div>
                                <div class="flex-1">
                                    <p class="font-black text-gray-800">
                                        Shipper đã nhận đơn
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Shipper đang trên đường đến quán lấy
                                        hàng
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div
                                    :class="[
                                        'w-8 h-8 rounded-full flex items-center justify-center text-sm font-black',
                                        [
                                            'shipping',
                                            'picked_up',
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
                                        Đã lấy hàng
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Shipper đã lấy hàng và đang giao đến bạn
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div
                                    :class="[
                                        'w-8 h-8 rounded-full flex items-center justify-center text-sm font-black',
                                        [
                                            'picked_up',
                                            'shipping',
                                            'completed',
                                        ].includes(order.status)
                                            ? 'bg-green-100 text-green-600'
                                            : 'bg-gray-100 text-gray-400',
                                    ]"
                                >
                                    🚚
                                </div>
                                <div class="flex-1">
                                    <p class="font-black text-gray-800">
                                        Đang giao hàng
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Món ăn đang trên đường đến bạn
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
                                    <p class="font-black text-gray-800">
                                        Đã giao thành công
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Món ăn đã được giao đến bạn thành công
                                    </p>
                                    <button
                                        v-if="
                                            order.status === 'picked_up' ||
                                            order.status === 'shipping'
                                        "
                                        @click="confirmDelivery"
                                        class="mt-3 px-6 py-2 bg-green-600 text-white rounded-2xl font-black text-sm hover:bg-green-700 transition"
                                    >
                                        Xác nhận đã nhận hàng
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { Head } from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

const props = defineProps({
    order: Object,
});

const map = ref(null);

const formatPrice = (p) =>
    new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(p);

const formatDate = (date) => new Date(date).toLocaleString("vi-VN");

const getStatusStyle = (status) => {
    switch (status) {
        case "pending":
            return "bg-amber-100 text-amber-600 border-amber-200";
        case "processing":
            return "bg-blue-100 text-blue-600 border-blue-200";
        case "confirmed":
            return "bg-purple-100 text-purple-600 border-purple-200";
        case "shipping":
            return "bg-indigo-100 text-indigo-600 border-indigo-200";
        case "picked_up":
            return "bg-orange-100 text-orange-600 border-orange-200";
        case "completed":
            return "bg-green-100 text-green-600 border-green-200";
        case "cancelled":
            return "bg-red-100 text-red-600 border-red-200";
        default:
            return "bg-gray-100 text-gray-600";
    }
};

const getStatusText = (status) => {
    const statuses = {
        pending: "⏳ Đang chờ xác nhận",
        processing: "👨‍🍳 Đang chuẩn bị món",
        confirmed: "🛵 Đã gán shipper",
        shipping: "🚚 Đang giao hàng",
        picked_up: "📦 Đã lấy hàng",
        completed: "✅ Đã giao thành công",
        cancelled: "❌ Đã hủy",
    };
    return statuses[status] || status;
};

const getShipperStatus = (status) => {
    const statuses = {
        available: "Sẵn sàng",
        busy: "Đang giao hàng",
        offline: "Offline",
    };
    return statuses[status] || status;
};

const initMap = () => {
    if (
        !order.shipper ||
        !order.shipper.current_latitude ||
        !order.shipper.current_longitude
    )
        return;

    map.value = L.map("order-map", {
        zoomControl: false,
        attributionControl: false,
    }).setView(
        [order.shipper.current_latitude, order.shipper.current_longitude],
        15,
    );

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 18,
        minZoom: 10,
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map.value);

    // Add shipper marker
    L.circleMarker(
        [order.shipper.current_latitude, order.shipper.current_longitude],
        {
            radius: 10,
            fillColor: "#ef4444",
            color: "#ffffff",
            weight: 2,
            fillOpacity: 0.95,
        },
    )
        .addTo(map.value)
        .bindPopup("Vị trí shipper hiện tại");
};

const confirmDelivery = async () => {
    if (!confirm("Bạn xác nhận đã nhận được hàng?")) return;

    try {
        const response = await fetch(
            `/api/orders/${order.id}/confirm-delivery`,
            {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
            },
        );

        if (response.ok) {
            alert("Cảm ơn bạn đã xác nhận!");
            window.location.reload();
        } else {
            alert("Có lỗi xảy ra, vui lòng thử lại.");
        }
    } catch (error) {
        console.error("Error confirming delivery:", error);
        alert("Có lỗi xảy ra, vui lòng thử lại.");
    }
};

onMounted(() => {
    initMap();
});
</script>
