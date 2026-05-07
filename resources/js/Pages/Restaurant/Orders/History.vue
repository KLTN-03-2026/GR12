<script setup>
import { Head, router, Link } from "@inertiajs/vue3";
import RestaurantLayout from "@/Layouts/RestaurantLayout.vue"; // Đảm bảo dùng đúng Layout

defineOptions({ layout: RestaurantLayout });

const props = defineProps({ orders: Object });

const formatPrice = (p) =>
    new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(p);

// Định nghĩa Text hiển thị tiếng Việt cho trạng thái
const statusLabel = {
    pending: "📋 Đơn mới",
    assigned: "🛵 Đã gán shipper",
    confirmed: "✅ Quán xác nhận",
    shipping: "🚗 Shipper tới quán",
    picked_up: "📦 Đã lấy hàng",
    completed: "🎉 Hoàn tất",
    cancelled: "❌ Đã hủy",
};

const getStatusStyle = (status) => {
    const base =
        "px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-tighter border shadow-sm ";
    switch (status) {
        case "pending":
            return base + "bg-amber-50 text-amber-600 border-amber-200";
        case "assigned":
            return base + "bg-yellow-50 text-yellow-700 border-yellow-200";
        case "confirmed":
            return base + "bg-orange-50 text-orange-600 border-orange-200";
        case "shipping":
            return base + "bg-purple-50 text-purple-600 border-purple-200";
        case "picked_up":
            return base + "bg-indigo-50 text-indigo-600 border-indigo-200";
        case "completed":
            return base + "bg-green-50 text-green-600 border-green-200";
        default:
            return base + "bg-red-50 text-red-600 border-red-200";
    }
};
</script>

<template>
    <Head title="Quản lý đơn hàng - FoodXpress" />

    <div class="space-y-10 pb-20 animate-fade-in-up">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="space-y-2">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                    Lịch sử đơn hàng <span class="animate-pulse">📜</span>
                </h1>
                <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.25em] bg-slate-100/50 w-fit px-4 py-1.5 rounded-xl border border-slate-200/50">
                    Tất cả đơn đã hoàn tất
                </p>
            </div>

            <div class="flex gap-4">
                <div class="bg-white p-5 rounded-[1.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 flex items-center gap-4 hover:shadow-md transition-all">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-400 to-indigo-500 rounded-xl flex items-center justify-center text-xl shadow-lg shadow-indigo-500/30 text-white">
                        📊
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase block tracking-widest">Thống kê</span>
                        <span class="text-sm font-black text-slate-800 tracking-tight">Lịch sử giao dịch</span>
                    </div>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 gap-8">
            <div
                v-for="order in orders.data"
                :key="order.id"
                class="bg-white rounded-[2.5rem] p-3 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 hover:shadow-[0_20px_40px_rgb(249,115,22,0.1)] transition-all duration-500 group flex flex-col lg:flex-row relative overflow-hidden"
            >
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/50 to-orange-50/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>

                <div class="p-8 lg:w-1/4 bg-slate-50 rounded-[2rem] border border-slate-100 flex flex-col justify-center text-center lg:text-left relative z-10">
                    <span class="text-[10px] font-black text-orange-500 uppercase tracking-widest mb-2 block">
                        #{{ order.order_code }}
                    </span>
                    <h3 class="font-black text-slate-800 text-2xl leading-tight mb-3 line-clamp-1">
                        {{ order.user.name }}
                    </h3>
                    <p class="text-sm font-bold text-slate-500 flex items-center justify-center lg:justify-start gap-2 bg-white px-3 py-1.5 rounded-xl shadow-sm w-fit mx-auto lg:mx-0">
                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        {{ order.phone }}
                    </p>
                    <p v-if="order.note" class="text-xs font-bold text-orange-600 bg-orange-100/50 px-3 py-2.5 rounded-xl mt-4 flex items-start gap-2 text-left border border-orange-200/50">
                        <span class="text-sm">📝</span> {{ order.note }}
                    </p>
                    <div class="mt-6 flex flex-col items-center lg:items-start gap-4">
                        <span :class="getStatusStyle(order.status)">
                            {{ statusLabel[order.status] }}
                        </span>
                        
                        <Link
                            :href="route('restaurant.orders.show', order.id)"
                            class="inline-flex items-center gap-1.5 text-[11px] font-black text-slate-400 hover:text-orange-500 uppercase tracking-widest transition-colors"
                        >
                            Chi tiết <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                        </Link>
                    </div>
                </div>

                <div class="p-8 lg:w-2/4 flex flex-col justify-center relative z-10">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="h-px flex-1 bg-slate-100"></div>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-3 py-1 rounded-full">Danh sách món</span>
                        <div class="h-px flex-1 bg-slate-100"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex items-center gap-4 bg-white p-3 rounded-2xl border border-slate-100 shadow-sm hover:border-orange-200 hover:shadow-md transition-all group/item"
                        >
                            <div class="w-14 h-14 rounded-xl overflow-hidden shrink-0 shadow-sm relative">
                                <img :src="'/storage/' + item.product.image" class="w-full h-full object-cover group-hover/item:scale-110 transition-transform duration-500" />
                                <div class="absolute inset-0 bg-slate-900/10 pointer-events-none"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-800 truncate mb-1">
                                    {{ item.product.name }}
                                </p>
                                <p class="text-[10px] font-black text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md w-fit uppercase tracking-widest">
                                    SL: {{ item.quantity }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-8 lg:w-1/4 flex flex-col justify-center items-center lg:items-end gap-6 bg-gradient-to-br from-white to-slate-50/80 rounded-[2rem] border border-slate-50 relative z-10">
                    <div class="text-center lg:text-right w-full">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 bg-slate-100 px-3 py-1 rounded-full w-fit mx-auto lg:ml-auto lg:mr-0">
                            Thanh toán
                        </p>
                        <p class="text-3xl font-black text-orange-600 tracking-tight leading-none drop-shadow-sm">
                            {{ formatPrice(order.total) }}
                        </p>
                    </div>

                    <div class="w-full space-y-3">
                        <div
                            v-if="order.status === 'completed'"
                            class="w-full rounded-2xl bg-emerald-50 text-emerald-600 font-black py-4 px-6 text-center border border-emerald-200/60 mt-3 uppercase text-[10px] tracking-widest flex items-center justify-center gap-2 shadow-sm"
                        >
                            <span class="text-sm">🎉</span> Đã hoàn tất lúc {{ new Date(order.updated_at).toLocaleTimeString('vi-VN') }}
                        </div>

                        <div
                            v-if="order.status === 'cancelled'"
                            class="w-full rounded-2xl bg-rose-50 text-rose-600 font-black py-4 px-6 text-center border border-rose-200/60 mt-3 uppercase text-[10px] tracking-widest flex items-center justify-center gap-2 shadow-sm"
                        >
                            <span class="text-sm">❌</span> Đã hủy lúc {{ new Date(order.updated_at).toLocaleTimeString('vi-VN') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="orders.data.length === 0" class="bg-white rounded-[3rem] p-16 md:p-24 border border-slate-100 flex flex-col items-center justify-center text-center shadow-sm relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-slate-50/50 to-transparent"></div>
            <div class="relative z-10 flex flex-col items-center">
                <div class="w-24 h-24 bg-slate-50 rounded-[2rem] flex items-center justify-center mb-6 shadow-inner border border-slate-100">
                    <span class="text-5xl">🏜️</span>
                </div>
                <h3 class="text-2xl font-black text-slate-800 tracking-tight mb-2">
                    Lịch sử trống
                </h3>
                <p class="text-slate-500 font-medium text-sm">
                    Bạn chưa có đơn hàng nào được hoàn tất.
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

button {
    backface-visibility: hidden;
    transform: translateZ(0);
}
</style>
