<template>
    <ShipperLayout>
        <template #default>
            <div class="pb-24">
                <!-- Header Card -->
                <section
                    class="rounded-[2rem] bg-gradient-to-br from-purple-900 to-slate-800 p-6 text-white shadow-[0_10px_30px_-10px_rgba(15,23,42,0.6)] relative overflow-hidden ring-1 ring-white/10 mb-6"
                >
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="flex items-center justify-between relative z-10 mb-4">
                        <button
                            @click="goBack"
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white shadow-sm ring-1 ring-white/20 backdrop-blur-sm active:scale-95 transition-transform"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        </button>
                    </div>

                    <div class="flex items-center gap-5 relative z-10">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-[1.25rem] bg-white/10 text-3xl shadow-xl ring-1 ring-white/20 shrink-0 backdrop-blur-sm"
                        >
                            📜
                        </div>
                        <div>
                            <p
                                class="text-[10px] uppercase font-bold tracking-[0.2em] text-purple-200 mb-1"
                            >
                                Tổng quan
                            </p>
                            <h1 class="text-2xl font-black text-white tracking-tight">
                                Lịch sử đơn hàng
                            </h1>
                        </div>
                    </div>
                </section>

                <div class="space-y-4">
                    <!-- Stats summary -->
                    <div class="grid grid-cols-2 gap-4 mb-2">
                        <div class="rounded-[2rem] bg-white border border-slate-100 p-5 shadow-sm">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Tổng đơn</p>
                            <p class="text-2xl font-black text-slate-800">{{ orders.length }}</p>
                        </div>
                        <div class="rounded-[2rem] bg-emerald-50 border border-emerald-100 p-5 shadow-sm">
                            <p class="text-[10px] font-bold text-emerald-600/70 uppercase tracking-widest mb-1">Hoàn thành</p>
                            <p class="text-2xl font-black text-emerald-600">{{ completedCount }}</p>
                        </div>
                    </div>

                    <div v-if="orders.length === 0" class="rounded-[2rem] border-2 border-dashed border-slate-200 p-10 text-center bg-white/50">
                        <span class="text-5xl block mb-4 opacity-50">🏜️</span>
                        <h3 class="text-lg font-black text-slate-800 mb-1">Chưa có lịch sử</h3>
                        <p class="text-xs font-semibold text-slate-500">Bạn chưa hoàn thành đơn hàng nào.</p>
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="order in orders"
                            :key="order.id"
                            class="rounded-[2rem] bg-white border border-slate-100 p-5 shadow-sm relative overflow-hidden group"
                        >
                            <div v-if="order.status === 'cancelled'" class="absolute right-0 top-0 w-12 h-12 bg-rose-50 flex items-center justify-center rounded-bl-3xl">
                                <span class="text-rose-500 font-bold text-xs">Hủy</span>
                            </div>
                            <div v-if="order.status === 'completed'" class="absolute right-0 top-0 w-12 h-12 bg-emerald-50 flex items-center justify-center rounded-bl-3xl">
                                <span class="text-emerald-500 font-bold text-xs">Xong</span>
                            </div>

                            <div class="flex items-center gap-2 mb-3 pr-10">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-[10px] font-black text-slate-600 tracking-widest uppercase">
                                    #{{ order.order_code }}
                                </span>
                                <span class="text-[10px] font-semibold text-slate-400">
                                    {{ formatDate(order.updated_at) }}
                                </span>
                            </div>

                            <div class="space-y-3 mb-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center shrink-0 mt-0.5">🏪</div>
                                    <div>
                                        <p class="text-xs font-black text-slate-800 line-clamp-1">
                                            {{ order.items?.[0]?.product?.user?.restaurant_name || order.items?.[0]?.product?.user?.name || 'Quán ăn' }}
                                        </p>
                                        <p class="text-[10px] text-slate-500 line-clamp-1 mt-0.5">
                                            {{ order.items?.[0]?.product?.user?.address || 'Địa chỉ quán' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="w-0.5 h-4 bg-slate-100 ml-4"></div>
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center shrink-0 mt-0.5">👤</div>
                                    <div>
                                        <p class="text-xs font-black text-slate-800 line-clamp-1">
                                            {{ order.user?.name || 'Khách hàng' }}
                                        </p>
                                        <p class="text-[10px] text-slate-500 line-clamp-2 mt-0.5">
                                            {{ order.address }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Thu nhập</p>
                                    <p class="text-sm font-black" :class="order.status === 'completed' ? 'text-emerald-600' : 'text-slate-500 line-through'">
                                        +{{ formatCurrency((parseFloat(order.shipping_fee) || 0) + (parseFloat(order.shipper_tip) || 0)) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Tổng giá trị</p>
                                    <p class="text-sm font-black text-slate-800">
                                        {{ formatCurrency(order.total) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </ShipperLayout>
</template>

<script setup>
import { computed } from "vue";
import { router } from "@inertiajs/vue3";
import ShipperLayout from "@/Layouts/ShipperLayout.vue";

const props = defineProps({
    orders: {
        type: Array,
        default: () => []
    }
});

const completedCount = computed(() => {
    return props.orders.filter(o => o.status === 'completed').length;
});

const goBack = () => {
    router.visit("/shipper/dashboard");
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(amount);
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
};
</script>
