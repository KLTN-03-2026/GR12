<template>
    <GuestLayout>
        <Head :title="`Đơn hàng ${orderData.order_code} - FoodXpress`" />

        <div
            class="min-h-screen bg-[#f4f7f9] py-8 md:py-12 px-4 font-sans text-slate-900"
        >
            <div class="max-w-5xl mx-auto space-y-6">
                <div
                    class="bg-white rounded-[2.5rem] shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-white overflow-hidden p-8 md:p-12 transition-all"
                >
                    <div
                        class="flex flex-col md:flex-row justify-between items-start gap-6 mb-12"
                    >
                        <div class="space-y-2">
                            <p
                                class="text-[11px] uppercase tracking-[0.3em] text-orange-500 font-black flex items-center gap-2"
                            >
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"
                                    ></span>
                                    <span
                                        class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"
                                    ></span>
                                </span>
                                Thời gian giao dự kiến
                            </p>
                            <h2
                                class="text-4xl md:text-5xl font-black tracking-tight italic"
                            >
                                {{ estimatedDeliveryTime }}
                            </h2>
                            <p class="text-slate-400 text-sm font-medium">
                                FoodXpress sẽ cập nhật thông báo khi Shipper di
                                chuyển.
                            </p>
                        </div>
                        <div
                            class="px-8 py-4 bg-orange-50 text-orange-600 rounded-full font-black uppercase tracking-widest text-sm shadow-sm border border-orange-100"
                        >
                            {{ getStatusHeadline(orderData.status) }}
                        </div>
                    </div>

                    <div class="relative px-4 pb-4">
                        <div
                            class="absolute top-7 left-0 w-full h-[4px] bg-slate-100 rounded-full z-0"
                        >
                            <div
                                class="h-full bg-orange-500 transition-all duration-1000 shadow-[0_0_15px_rgba(249,115,22,0.4)]"
                                :style="{ width: `${progressPercent}%` }"
                            ></div>
                        </div>

                        <div
                            class="relative z-10 flex justify-between items-start"
                        >
                            <div
                                v-for="step in progressSteps"
                                :key="step.key"
                                class="flex flex-col items-center group flex-1"
                            >
                                <div
                                    :class="[
                                        'w-14 h-14 rounded-2xl flex items-center justify-center text-2xl transition-all duration-500 border-4 border-white shadow-md',
                                        step.active
                                            ? 'bg-orange-500 text-white scale-110 shadow-orange-200'
                                            : 'bg-slate-100 text-slate-400',
                                    ]"
                                >
                                    {{ step.icon }}
                                </div>
                                <span
                                    :class="[
                                        'mt-4 text-[10px] md:text-[11px] uppercase tracking-[0.15em] font-black text-center px-2',
                                        step.active
                                            ? 'text-slate-900'
                                            : 'text-slate-300',
                                    ]"
                                >
                                    {{ step.title }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-[1.6fr_1fr]">
                    <div class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div
                                class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-50"
                            >
                                <p
                                    class="text-[10px] uppercase tracking-[0.2em] text-slate-400 mb-4 font-black"
                                >
                                    Từ nhà hàng
                                </p>
                                <h3 class="font-black text-lg mb-2">
                                    {{ restaurant.name }}
                                </h3>
                                <p
                                    class="text-sm text-slate-500 leading-relaxed"
                                >
                                    {{ restaurant.address }}
                                </p>
                            </div>
                            <div
                                class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-50 relative"
                            >
                                <span
                                    class="absolute top-4 right-6 text-[9px] font-black px-2 py-1 bg-slate-100 rounded-lg uppercase tracking-tighter"
                                    >Bạn</span
                                >
                                <p
                                    class="text-[10px] uppercase tracking-[0.2em] text-slate-400 mb-4 font-black"
                                >
                                    Giao đến
                                </p>
                                <h3
                                    class="font-black text-lg mb-2 line-clamp-1"
                                >
                                    {{ orderData.address }}
                                </h3>
                                <p
                                    class="text-sm text-slate-500 font-bold tracking-widest italic"
                                >
                                    {{ orderData.phone }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden"
                        >
                            <div
                                class="p-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/20"
                            >
                                <h3
                                    class="font-black text-slate-800 flex items-center gap-2 text-xs uppercase tracking-[0.2em]"
                                >
                                    📍 Live Tracking
                                </h3>
                                <span
                                    v-if="showTrackingMap"
                                    class="flex h-2 w-2 rounded-full bg-green-500 animate-pulse"
                                ></span>
                            </div>
                            <div
                                v-if="showTrackingMap"
                                id="order-map"
                                class="h-[400px] w-full"
                            ></div>
                            <div
                                v-else
                                class="h-[250px] flex flex-col items-center justify-center bg-slate-50 text-slate-400 p-8 text-center italic"
                            >
                                <p class="font-bold text-sm">
                                    Shipper chưa bắt đầu di chuyển hoặc đang
                                    chuẩn bị hàng.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div
                            class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 p-8"
                        >
                            <h3 class="text-xl font-black mb-8 italic">
                                Đơn hàng của bạn
                            </h3>
                            <div class="space-y-6 mb-8">
                                <div
                                    v-for="item in orderData.items"
                                    :key="item.id"
                                    class="flex items-center gap-4 group"
                                >
                                    <div
                                        class="w-16 h-16 rounded-2xl overflow-hidden shadow-sm border border-slate-50"
                                    >
                                        <img
                                            :src="
                                                '/storage/' + item.product.image
                                            "
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                        />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="font-black text-slate-900 text-sm truncate"
                                        >
                                            {{ item.product.name }}
                                        </p>
                                        <p
                                            class="text-xs text-slate-400 font-bold mt-1 uppercase"
                                        >
                                            {{ item.quantity }} phần •
                                            {{ formatPrice(item.price) }}
                                        </p>
                                    </div>
                                    <p
                                        class="font-black text-slate-900 text-sm italic"
                                    >
                                        {{
                                            formatPrice(
                                                item.price * item.quantity,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="space-y-4 pt-6 border-t border-slate-50"
                            >
                                <div
                                    class="flex justify-between text-sm font-medium text-slate-500"
                                >
                                    <span>Tạm tính</span>
                                    <span class="font-bold text-slate-900">{{
                                        formatPrice(orderData.subtotal)
                                    }}</span>
                                </div>
                                <div
                                    class="flex justify-between text-sm font-medium text-slate-500"
                                >
                                    <span>Phí giao hàng</span>
                                    <span class="font-bold text-slate-900">{{
                                        formatPrice(orderData.shipping_fee)
                                    }}</span>
                                </div>
                                <div
                                    class="flex justify-between text-sm font-bold text-green-600"
                                >
                                    <span>Giảm giá</span>
                                    <span
                                        >-{{
                                            formatPrice(
                                                orderData.discount_amount,
                                            )
                                        }}</span
                                    >
                                </div>

                                <div
                                    class="flex justify-between items-center py-4 px-5 bg-slate-50 rounded-2xl my-6 border border-slate-100"
                                >
                                    <span
                                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest"
                                        >Thanh toán bằng</span
                                    >
                                    <p
                                        class="font-black text-slate-900 text-sm italic"
                                    >
                                        {{
                                            orderData.payment_method === "cod"
                                                ? "💵 Tiền mặt"
                                                : "💳 VNPay"
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="flex justify-between text-2xl font-black text-slate-900 pt-2 border-t border-slate-100 italic"
                                >
                                    <span>Tổng cộng</span>
                                    <span class="text-orange-600">{{
                                        formatPrice(orderData.total)
                                    }}</span>
                                </div>
                            </div>

                            <div
                                v-if="
                                    ['picked_up', 'shipping'].includes(
                                        orderData.status,
                                    )
                                "
                                class="mt-8"
                            >
                                <button
                                    @click="confirmDelivery"
                                    class="w-full py-4 bg-green-600 hover:bg-green-700 text-white rounded-2xl font-black text-sm transition-all shadow-xl shadow-green-100 flex items-center justify-center gap-2"
                                >
                                    ✅ ĐÃ NHẬN ĐƯỢC HÀNG
                                </button>
                            </div>

                            <div v-if="canCancelOrder" class="mt-4">
                                <button
                                    @click="cancelOrder"
                                    class="w-full py-3 text-red-400 hover:text-red-600 font-black text-xs uppercase tracking-widest transition-all"
                                >
                                    Hủy đơn hàng
                                </button>
                            </div>
                        </div>

                        <div
                            v-if="orderData.shipper"
                            class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-xl shadow-slate-200"
                        >
                            <div class="flex items-center gap-5">
                                <UserAvatar
                                    :user="orderData.shipper.user"
                                    class="w-16 h-16 ring-4 ring-slate-800"
                                />
                                <div class="flex-1">
                                    <p
                                        class="text-[10px] font-black uppercase text-orange-400 tracking-widest mb-1"
                                    >
                                        Tài xế đang giao
                                    </p>
                                    <p class="font-black text-lg">
                                        {{ orderData.shipper.user.name }}
                                    </p>
                                    <p class="text-slate-400 text-xs italic">
                                        {{ orderData.shipper.user.phone }}
                                    </p>
                                </div>
                                <a
                                    :href="
                                        'tel:' + orderData.shipper.user.phone
                                    "
                                    class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center hover:bg-orange-500 transition-all"
                                    >📞</a
                                >
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
import Swal from "sweetalert2";

const props = defineProps({ order: Object });

const map = ref(null);
const orderData = ref(props.order);
const shipperMarker = ref(null);
const refreshInterval = ref(null);

const restaurant = computed(() => {
    if (orderData.value.restaurant) return orderData.value.restaurant;
    const productUser = orderData.value.items?.[0]?.product?.user;
    return {
        name: productUser?.restaurant_name ?? productUser?.name ?? "Nhà hàng",
        address: productUser?.address ?? "Đang cập nhật địa chỉ...",
    };
});

const estimatedDeliveryTime = computed(() => {
    if (!orderData.value.created_at) return "--:--";
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
        active: [
            "pending",
            "processing",
            "confirmed",
            "assigned",
            "shipping",
            "picked_up",
            "completed",
        ].includes(orderData.value.status),
    },
    {
        key: "processing",
        icon: "👨‍🍳",
        title: "Chuẩn bị",
        active: [
            "processing",
            "confirmed",
            "assigned",
            "shipping",
            "picked_up",
            "completed",
        ].includes(orderData.value.status),
    },
    {
        key: "confirmed",
        icon: "🍱",
        title: "Sẵn sàng",
        active: [
            "confirmed",
            "assigned",
            "shipping",
            "picked_up",
            "completed",
        ].includes(orderData.value.status),
    },
    {
        key: "assigned",
        icon: "🛵",
        title: "Gán Shipper",
        active: ["assigned", "shipping", "picked_up", "completed"].includes(
            orderData.value.status,
        ),
    },
    {
        key: "shipping",
        icon: "🚚",
        title: "Giao hàng",
        active: ["shipping", "picked_up", "completed"].includes(
            orderData.value.status,
        ),
    },
]);

const progressPercent = computed(() => {
    const s = orderData.value.status;
    const stepWidth = 100 / (progressSteps.value.length - 1);
    if (s === "pending") return 0;
    if (s === "processing") return stepWidth * 1;
    if (s === "confirmed") return stepWidth * 2;
    if (s === "assigned") return stepWidth * 3;
    if (s === "shipping") return stepWidth * 4;
    if (s === "picked_up") return stepWidth * 4.5;
    if (s === "completed") return 100;
    return 0;
});

const showTrackingMap = computed(() => {
    return (
        ["shipping", "picked_up"].includes(orderData.value.status) &&
        orderData.value.shipper?.current_latitude
    );
});

const getStatusHeadline = (status) => {
    const headlines = {
        pending: "Chờ xác nhận",
        processing: "Quán đang chuẩn bị",
        confirmed: "Đã sẵn sàng",
        assigned: "Đã tìm thấy shipper",
        shipping: "Đang giao đến bạn",
        picked_up: "Đã lấy hàng",
        completed: "Giao thành công",
        cancelled: "Đã hủy",
    };
    return headlines[status] || "Đang xử lý";
};

// Polling & API
const startOrderPolling = () => {
    if (!refreshInterval.value)
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

const updateOrderData = async () => {
    try {
        const response = await fetch(
            `/api/orders/${orderData.value.id}/details`,
            { headers: { Accept: "application/json" }, credentials: "include" },
        );
        if (response.ok) orderData.value = await response.json();
    } catch (e) {
        console.error(e);
    }
};

const confirmDelivery = async () => {
    const result = await Swal.fire({
        title: "Bạn đã nhận đủ món?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#10b981",
        confirmButtonText: "Đúng, đã nhận!",
        cancelButtonText: "Chưa",
        customClass: { popup: "rounded-[2rem]" },
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(
                `/api/orders/${orderData.value.id}/confirm-delivery`,
                {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    credentials: "include",
                },
            );
            if (response.ok)
                Swal.fire(
                    "Thành công!",
                    "Chúc bạn ngon miệng!",
                    "success",
                ).then(() => window.location.reload());
        } catch (e) {
            Swal.fire("Lỗi", "Vui lòng thử lại", "error");
        }
    }
};

const cancelOrder = async () => {
    const result = await Swal.fire({
        title: "Hủy đơn hàng?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        confirmButtonText: "Hủy đơn",
        cancelButtonText: "Quay lại",
    });
    if (result.isConfirmed) {
        try {
            const response = await fetch(
                `/my-orders/${orderData.value.id}/cancel`,
                {
                    method: "DELETE",
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    credentials: "include",
                },
            );
            if (response.ok)
                Swal.fire(
                    "Đã hủy!",
                    "Đơn hàng đã hủy thành công.",
                    "success",
                ).then(() => window.location.reload());
            else {
                const err = await response.json();
                Swal.fire("Thất bại", err.error, "error");
            }
        } catch (e) {
            console.error(e);
        }
    }
};

// Map
const initMap = () => {
    if (!orderData.value.shipper?.current_latitude) return;
    if (map.value) map.value.remove();
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
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(
        map.value,
    );

    if (orderData.value.user.latitude) {
        L.circleMarker(
            [orderData.value.user.latitude, orderData.value.user.longitude],
            {
                radius: 10,
                fillColor: "#2563eb",
                color: "#fff",
                weight: 4,
                fillOpacity: 1,
            },
        )
            .addTo(map.value)
            .bindPopup("Bạn");
    }
    shipperMarker.value = L.circleMarker(
        [
            orderData.value.shipper.current_latitude,
            orderData.value.shipper.current_longitude,
        ],
        {
            radius: 12,
            fillColor: "#f97316",
            color: "#fff",
            weight: 4,
            fillOpacity: 1,
        },
    )
        .addTo(map.value)
        .bindPopup("Shipper");
};

watch(
    () => [
        orderData.value.shipper?.current_latitude,
        orderData.value.shipper?.current_longitude,
    ],
    ([lat, lng]) => {
        if (shipperMarker.value && lat && lng) {
            shipperMarker.value.setLatLng([lat, lng]);
            map.value?.panTo([lat, lng]);
        }
    },
);

watch(showTrackingMap, (v) => {
    if (v) setTimeout(initMap, 500);
});

onMounted(() => {
    if (showTrackingMap.value) setTimeout(initMap, 500);
    if (["shipping", "picked_up"].includes(orderData.value.status))
        startOrderPolling();
});

onUnmounted(() => {
    stopOrderPolling();
    if (map.value) map.value.remove();
});

const canCancelOrder = computed(() => {
    return ![
        "shipping",
        "picked_up",
        "delivering",
        "completed",
        "cancelled",
    ].includes(orderData.value.status);
});
</script>

<style scoped>
:deep(.leaflet-container) {
    border-radius: 0;
    font-family: inherit;
}
/* Đảm bảo chữ ko bị vỡ trên mobile */
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
