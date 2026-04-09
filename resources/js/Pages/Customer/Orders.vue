<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

defineOptions({ layout: GuestLayout });

const props = defineProps({
    orders: Array,
});

const formatPrice = (p) =>
    new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(p);

// Hàm định dạng màu sắc cho từng trạng thái
const getStatusStyle = (status) => {
    switch (status) {
        case "pending":
            return "bg-amber-100 text-amber-600 border-amber-200";
        case "processing":
            return "bg-blue-100 text-blue-600 border-blue-200";
        case "shipping":
            return "bg-purple-100 text-purple-600 border-purple-200";
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
        shipping: "🛵 Đang giao hàng",
        completed: "✅ Đã giao thành công",
        cancelled: "❌ Đã hủy",
    };
    return statuses[status] || status;
};
</script>

<template>
    <Head title="Đơn hàng của tôi - FoodXpress" />

    <div class="min-h-screen bg-[#f8f9fb] py-12 px-4">
        <div class="max-w-4xl mx-auto">
            <h1
                class="text-4xl font-black text-gray-900 mb-10 tracking-tighter italic flex items-center gap-4"
            >
                Lịch sử đặt hàng 📋
            </h1>

            <div v-if="orders.length > 0" class="space-y-8">
                <div
                    v-for="order in orders"
                    :key="order.id"
                    class="bg-white rounded-[2.5rem] overflow-hidden shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-500 group"
                >
                    <div
                        class="p-6 md:p-8 flex flex-wrap justify-between items-center gap-4 border-b border-gray-50 bg-gray-50/30"
                    >
                        <div>
                            <p
                                class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1"
                            >
                                Mã đơn hàng
                            </p>
                            <h2
                                class="text-xl font-black text-gray-800 tracking-tight"
                            >
                                #{{ order.order_code }}
                            </h2>
                        </div>
                        <div
                            :class="getStatusStyle(order.status)"
                            class="px-5 py-2 rounded-2xl text-xs font-black uppercase tracking-widest border shadow-sm"
                        >
                            {{ getStatusText(order.status) }}
                        </div>
                    </div>

                    <div class="p-6 md:p-8 space-y-6">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex items-center gap-4"
                        >
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
                            <div class="text-right font-black text-gray-700">
                                {{ formatPrice(item.price * item.quantity) }}
                            </div>
                        </div>
                    </div>

                    <div
                        class="p-6 md:p-8 bg-gray-50/50 border-t border-gray-50 flex flex-wrap justify-between items-end gap-6"
                    >
                        <div class="space-y-2">
                            <div
                                class="flex items-center gap-2 text-gray-500 text-sm"
                            >
                                <span class="text-lg">📍</span>
                                <span class="font-bold">{{
                                    order.address
                                }}</span>
                            </div>
                            <div
                                class="flex items-center gap-2 text-gray-400 text-[10px] font-black uppercase"
                            >
                                📅
                                {{
                                    new Date(order.created_at).toLocaleString(
                                        "vi-VN",
                                    )
                                }}
                            </div>
                        </div>
                        <div class="text-right">
                            <p
                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1"
                            >
                                Tổng thanh toán
                            </p>
                            <p
                                class="text-3xl font-black text-orange-600 tracking-tighter italic"
                            >
                                {{ formatPrice(order.total) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="text-center py-32 bg-white rounded-[4rem] border-4 border-dashed border-gray-100"
            >
                <p class="text-7xl mb-6">📦</p>
                <h3
                    class="text-2xl font-black text-gray-400 uppercase italic tracking-widest"
                >
                    Bạn chưa có đơn hàng nào!
                </h3>
                <Link
                    :href="route('home')"
                    class="mt-8 inline-block bg-orange-500 text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-orange-100 hover:scale-105 transition"
                >
                    Đặt món ngay thôi
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
* {
    font-family: "Inter", sans-serif;
}
</style>
