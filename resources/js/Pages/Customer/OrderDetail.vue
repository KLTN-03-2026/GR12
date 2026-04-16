<template>
    <GuestLayout>
        <Head :title="`Đơn hàng ${orderData.order_code} - FoodXpress`" />

        <div class="min-h-screen bg-[#f8f9fb] py-12 px-4">
            <div class="max-w-4xl mx-auto space-y-8">
                <div
                    class="bg-white rounded-[3rem] overflow-hidden shadow-sm border border-gray-100"
                >
                    <div class="p-6 md:p-8">
                        <div
                            class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between"
                        >
                            <div class="max-w-2xl">
                                <p
                                    class="text-[10px] uppercase tracking-[0.3em] text-gray-400 mb-2"
                                >
                                    Thời gian giao dự kiến
                                </p>
                                <h2
                                    class="text-3xl sm:text-4xl font-black text-gray-900"
                                >
                                    {{ estimatedDeliveryTime }}
                                </h2>
                                <p class="mt-3 text-sm text-gray-500">
                                    ShopeeFood sẽ thông báo khi đơn bắt đầu được
                                    giao đến bạn.
                                </p>
                            </div>
                            <div
                                class="rounded-full bg-orange-50 border border-orange-100 px-5 py-3 text-orange-700 font-black uppercase tracking-[0.18em] text-sm text-center"
                            >
                                {{ getStatusHeadline(orderData.status) }}
                            </div>
                        </div>

                        <div class="mt-8 space-y-5">
                            <div class="grid grid-cols-2 gap-4">
                                <div
                                    v-for="step in progressSteps"
                                    :key="step.key"
                                    class="rounded-[2rem] border p-4 text-center transition"
                                >
                                    <div
                                        :class="
                                            step.active
                                                ? 'bg-orange-500 text-white'
                                                : 'bg-gray-100 text-gray-500'
                                        "
                                        class="mx-auto flex h-10 w-10 items-center justify-center rounded-full text-lg"
                                    >
                                        {{ step.icon }}
                                    </div>
                                    <p
                                        class="mt-3 text-xs uppercase tracking-[0.2em] font-black"
                                    >
                                        {{ step.title }}
                                    </p>
                                    <p
                                        class="mt-1 text-[11px] text-gray-500 leading-tight"
                                    >
                                        {{ step.label }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="h-2 rounded-full bg-slate-100 overflow-hidden"
                            >
                                <div
                                    class="h-full rounded-full bg-orange-500 transition-all duration-500"
                                    :style="{ width: `${progressPercent}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-[1.8fr_1fr]">
                    <div class="space-y-6">
                        <div
                            class="bg-white rounded-[3rem] shadow-sm border border-gray-100 p-6 md:p-8"
                        >
                            <div
                                class="flex items-center justify-between gap-4 mb-5"
                            >
                                <div>
                                    <p
                                        class="text-xs uppercase tracking-[0.25em] text-gray-400 mb-2"
                                    >
                                        Từ
                                    </p>
                                    <h3
                                        class="text-lg font-black text-gray-900"
                                    >
                                        {{ restaurant.name }}
                                    </h3>
                                    <p class="mt-2 text-sm text-gray-500">
                                        {{
                                            restaurant.address ||
                                            "Địa chỉ nhà hàng chưa có"
                                        }}
                                    </p>
                                </div>
                                <span
                                    class="inline-flex items-center rounded-full bg-orange-50 px-3 py-2 text-xs font-black uppercase tracking-[0.2em] text-orange-700"
                                >
                                    {{ orderData.items?.length || 0 }} món
                                </span>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-[3rem] shadow-sm border border-gray-100 p-6 md:p-8"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p
                                        class="text-xs uppercase tracking-[0.25em] text-gray-400 mb-2"
                                    >
                                        Đến
                                    </p>
                                    <h3
                                        class="text-lg font-black text-gray-900"
                                    >
                                        {{ orderData.address }}
                                    </h3>
                                    <p class="mt-2 text-sm text-gray-500">
                                        {{ orderData.phone }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-full bg-slate-100 px-3 py-2 text-xs font-black uppercase tracking-[0.18em] text-slate-600"
                                >
                                    Khách hàng
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="showTrackingMap"
                            class="bg-white rounded-[3rem] overflow-hidden shadow-sm border border-gray-100"
                        >
                            <div class="p-6 md:p-8">
                                <div
                                    class="flex items-center justify-between gap-4 mb-4"
                                >
                                    <div>
                                        <p
                                            class="text-xs uppercase tracking-[0.25em] text-gray-400"
                                        >
                                            Bản đồ theo dõi
                                        </p>
                                        <h3
                                            class="text-lg font-black text-gray-900"
                                        >
                                            Shipper đang giao đến bạn
                                        </h3>
                                    </div>
                                </div>
                                <div
                                    id="order-map"
                                    class="h-72 w-full rounded-3xl border border-gray-100"
                                ></div>
                            </div>
                        </div>

                        <div
                            v-else-if="orderData.shipper"
                            class="bg-white rounded-[3rem] shadow-sm border border-gray-100 p-6 md:p-8 text-center text-gray-500"
                        >
                            Bản đồ sẽ hiển thị khi shipper bắt đầu giao đơn.
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div
                            class="bg-white rounded-[3rem] shadow-sm border border-gray-100 p-6 md:p-8"
                        >
                            <h3 class="text-lg font-black text-gray-900 mb-5">
                                Tóm tắt đơn hàng
                            </h3>
                            <div class="space-y-4">
                                <div
                                    v-for="item in orderData.items"
                                    :key="item.id"
                                    class="flex items-center gap-4"
                                >
                                    <div
                                        class="w-16 h-16 rounded-2xl overflow-hidden border border-gray-100"
                                    >
                                        <img
                                            :src="
                                                '/storage/' + item.product.image
                                            "
                                            alt=""
                                            class="w-full h-full object-cover"
                                        />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="font-black text-gray-900 line-clamp-1"
                                        >
                                            {{ item.product.name }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            {{ item.quantity }} x
                                            {{ formatPrice(item.price) }}
                                        </p>
                                    </div>
                                    <div
                                        class="text-right font-black text-gray-900"
                                    >
                                        {{
                                            formatPrice(
                                                item.price * item.quantity,
                                            )
                                        }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 space-y-3 text-sm text-gray-600">
                                <div class="flex justify-between">
                                    <span>Giá món</span>
                                    <span class="font-black text-gray-900">{{
                                        formatPrice(orderData.subtotal)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Phí giao hàng</span>
                                    <span class="font-black text-gray-900">{{
                                        formatPrice(orderData.shipping_fee)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Phí áp dụng</span>
                                    <span class="font-black text-gray-900">{{
                                        formatPrice(orderData.discount_amount)
                                    }}</span>
                                </div>
                                <div
                                    class="border-t border-gray-100 pt-4 flex justify-between text-lg font-black"
                                >
                                    <span>Tổng cộng</span>
                                    <span>{{
                                        formatPrice(orderData.total)
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="orderData.shipper"
                            class="bg-white rounded-[3rem] shadow-sm border border-gray-100 p-6 md:p-8"
                        >
                            <h3 class="text-lg font-black text-gray-900 mb-5">
                                Thông tin shipper
                            </h3>
                            <div class="flex items-center gap-4">
                                <UserAvatar
                                    :user="orderData.shipper.user"
                                    class="w-16 h-16"
                                />
                                <div class="flex-1 min-w-0">
                                    <p class="font-black text-gray-900">
                                        {{ orderData.shipper.user.name }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ orderData.shipper.user.phone }}
                                    </p>
                                    <p class="text-sm text-gray-500 mt-2">
                                        {{
                                            orderData.shipper.user
                                                .license_plate ||
                                            "Chưa có biển số"
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-[3rem] overflow-hidden shadow-sm border border-gray-100"
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
                                        orderData.status === 'pending'
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
                                        {{ formatDate(orderData.created_at) }}
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
                                        ].includes(orderData.status)
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
                                        ].includes(orderData.status)
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
                                        ].includes(orderData.status)
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
                                        ].includes(orderData.status)
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
                                        orderData.status === 'completed'
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
                                            ['picked_up', 'shipping'].includes(
                                                orderData.status,
                                            )
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
import { computed, ref, onMounted, watch, onUnmounted } from "vue";
import { Head } from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

const props = defineProps({
    order: Object,
});

const map = ref(null);
const orderData = ref(props.order);
const shipperMarker = ref(null);
const refreshInterval = ref(null);

const restaurant = computed(() => {
    if (orderData.value.restaurant) return orderData.value.restaurant;
    const productUser = orderData.value.items?.[0]?.product?.user;
    return {
        name: productUser?.restaurant_name ?? productUser?.name ?? "Nhà hàng",
        address: productUser?.address ?? "",
    };
});

const estimatedDeliveryTime = computed(() => {
    if (!orderData.value.created_at) return "Đang cập nhật";
    const created = new Date(orderData.value.created_at);
    const start = new Date(created.getTime() + 20 * 60000);
    const end = new Date(created.getTime() + 40 * 60000);
    const formatTime = (date) =>
        new Intl.DateTimeFormat("vi-VN", {
            hour: "2-digit",
            minute: "2-digit",
        }).format(date);
    return `${formatTime(start)} - ${formatTime(end)}`;
});

const progressSteps = computed(() => [
    {
        key: "pending",
        icon: "📋",
        title: "Đặt đơn",
        label: "Đã đặt",
        active: [
            "pending",
            "processing",
            "confirmed",
            "shipping",
            "picked_up",
            "completed",
        ].includes(orderData.value.status),
    },
    {
        key: "processing",
        icon: "👨‍🍳",
        title: "Chuẩn bị",
        label: "Nhà hàng đang làm đồ",
        active: [
            "processing",
            "confirmed",
            "shipping",
            "picked_up",
            "completed",
        ].includes(orderData.value.status),
    },
    {
        key: "confirmed",
        icon: "🛵",
        title: "Gán shipper",
        label: "Tài xế đang nhận đơn",
        active: ["confirmed", "shipping", "picked_up", "completed"].includes(
            orderData.value.status,
        ),
    },
    {
        key: "shipping",
        icon: "🚚",
        title: "Giao hàng",
        label: "Shipper đang đến",
        active: ["shipping", "picked_up", "completed"].includes(
            orderData.value.status,
        ),
    },
]);

const progressPercent = computed(() => {
    switch (orderData.value.status) {
        case "pending":
            return 12;
        case "processing":
            return 35;
        case "confirmed":
            return 60;
        case "shipping":
            return 80;
        case "picked_up":
            return 92;
        case "completed":
            return 100;
        default:
            return 0;
    }
});

const showTrackingMap = computed(() => {
    return (
        ["shipping", "picked_up"].includes(orderData.value.status) &&
        orderData.value.shipper?.current_latitude &&
        orderData.value.shipper?.current_longitude &&
        orderData.value.user?.latitude &&
        orderData.value.user?.longitude
    );
});

const getStatusHeadline = (status) => {
    const headlines = {
        pending: "Đang chờ xác nhận",
        processing: "Đơn đang được chuẩn bị",
        confirmed: "Tài xế đang lấy đơn…",
        shipping: "Đơn đang được giao…",
        picked_up: "Shipper đã lấy hàng",
        completed: "Đã giao thành công",
        cancelled: "Đã hủy đơn",
    };
    return headlines[status] || "Đang cập nhật";
};

const startOrderPolling = () => {
    if (refreshInterval.value) return;
    refreshInterval.value = setInterval(updateOrderData, 5000);
};

const stopOrderPolling = () => {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
        refreshInterval.value = null;
    }
};

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
        !orderData.value.shipper ||
        !orderData.value.shipper.current_latitude ||
        !orderData.value.shipper.current_longitude
    )
        return;

    if (map.value) {
        map.value.remove();
    }

    map.value = L.map("order-map", {
        zoomControl: false,
        attributionControl: false,
    }).setView(
        [
            orderData.value.shipper.current_latitude,
            orderData.value.shipper.current_longitude,
        ],
        15,
    );

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 18,
        minZoom: 10,
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map.value);

    // Add customer marker
    if (orderData.value.user.latitude && orderData.value.user.longitude) {
        L.circleMarker(
            [orderData.value.user.latitude, orderData.value.user.longitude],
            {
                radius: 8,
                fillColor: "#2563eb",
                color: "#ffffff",
                weight: 2,
                fillOpacity: 0.9,
            },
        )
            .addTo(map.value)
            .bindPopup("Vị trí khách hàng");
    }

    // Add shipper marker
    shipperMarker.value = L.circleMarker(
        [
            orderData.value.shipper.current_latitude,
            orderData.value.shipper.current_longitude,
        ],
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

const updateOrderData = async () => {
    try {
        const response = await fetch(
            `/api/orders/${orderData.value.id}/details`,
            {
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
                credentials: "include",
            },
        );

        if (response.ok) {
            const freshData = await response.json();
            orderData.value = freshData;
        }
    } catch (error) {
        console.error("Error fetching order details:", error);
    }
};

// Watch shipper location changes to update map
watch(
    () => [
        orderData.value.shipper?.current_latitude,
        orderData.value.shipper?.current_longitude,
    ],
    () => {
        if (orderData.value.shipper && shipperMarker.value) {
            const newLat = orderData.value.shipper.current_latitude;
            const newLng = orderData.value.shipper.current_longitude;

            if (newLat && newLng) {
                shipperMarker.value.setLatLng([newLat, newLng]);
                map.value?.panTo([newLat, newLng]);
            }
        }
    },
);

watch(
    () => orderData.value.status,
    (status) => {
        if (["shipping", "picked_up"].includes(status)) {
            startOrderPolling();
        } else {
            stopOrderPolling();
        }
    },
);

watch(showTrackingMap, (visible) => {
    if (visible) {
        initMap();
    }
});

const confirmDelivery = async () => {
    if (!confirm("Bạn xác nhận đã nhận được hàng?")) return;

    try {
        const response = await fetch(
            `/api/orders/${orderData.value.id}/confirm-delivery`,
            {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
                credentials: "include",
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
    if (showTrackingMap.value) {
        initMap();
    }

    if (["shipping", "picked_up"].includes(orderData.value.status)) {
        startOrderPolling();
    }
});

onUnmounted(() => {
    stopOrderPolling();
    if (map.value) {
        map.value.remove();
    }
});
</script>
