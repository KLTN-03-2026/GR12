<template>
    <ShipperLayout>
        <template #default>
            <div class="pb-24">
                <!-- Header Card -->
                <section
                    class="rounded-[2rem] bg-gradient-to-br from-emerald-600 to-teal-800 p-6 text-white shadow-[0_10px_30px_-10px_rgba(20,184,166,0.5)] relative overflow-hidden ring-1 ring-white/10 mb-6"
                >
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-emerald-400/20 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-teal-400/20 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="flex items-center justify-between relative z-10 mb-4">
                        <button
                            @click="goBack"
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white shadow-sm ring-1 ring-white/20 backdrop-blur-sm active:scale-95 transition-transform"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        </button>
                        <div class="px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-sm text-xs font-bold ring-1 ring-white/20 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                            Hôm nay: {{ todayOrdersCount }} đơn
                        </div>
                    </div>

                    <div class="flex flex-col items-center justify-center text-center relative z-10 mt-2 mb-2">
                        <p class="text-[10px] uppercase font-bold tracking-[0.2em] text-emerald-100 mb-1">Thu nhập hôm nay</p>
                        <h1 class="text-4xl font-black text-white tracking-tight drop-shadow-md">
                            {{ formatCurrency(todayIncome) }}
                        </h1>
                    </div>
                </section>

                <div class="space-y-4">
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-[2rem] bg-white border border-slate-100 p-5 shadow-sm">
                            <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center text-sm mb-3">📅</div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Tuần này</p>
                            <p class="text-xl font-black text-slate-800">{{ formatCurrency(weekIncome) }}</p>
                        </div>
                        <div class="rounded-[2rem] bg-white border border-slate-100 p-5 shadow-sm">
                            <div class="w-8 h-8 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center text-sm mb-3">🗓️</div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Tháng này</p>
                            <p class="text-xl font-black text-slate-800">{{ formatCurrency(monthIncome) }}</p>
                        </div>
                    </div>

                    <!-- Chart Section -->
                    <div class="rounded-[2rem] bg-white border border-slate-100 p-5 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-sm font-black text-slate-800">Biểu đồ thu nhập</h3>
                                <p class="text-[10px] text-slate-500 font-semibold mt-0.5">7 ngày gần nhất</p>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400">📊</div>
                        </div>

                        <div class="h-40 flex items-end justify-between gap-1.5 pt-4 border-t border-slate-50">
                            <div v-for="(day, index) in chartData" :key="index" class="flex flex-col items-center flex-1">
                                <div class="w-full relative flex flex-col justify-end h-32 group">
                                    <div class="absolute -top-7 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-slate-800 text-white text-[9px] font-bold px-1.5 py-1 rounded shadow-lg pointer-events-none whitespace-nowrap z-10">
                                        {{ formatCurrency(day.income) }}
                                        <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 border-l-[4px] border-l-transparent border-r-[4px] border-r-transparent border-t-[4px] border-t-slate-800"></div>
                                    </div>
                                    <div 
                                        class="w-full rounded-md transition-all duration-700 ease-out"
                                        :class="index === chartData.length - 1 ? 'bg-emerald-500 shadow-sm shadow-emerald-500/30' : 'bg-indigo-100 group-hover:bg-indigo-200'"
                                        :style="{ height: getBarHeight(day.income) }"
                                    ></div>
                                </div>
                                <span class="text-[9px] font-bold mt-2" :class="index === chartData.length - 1 ? 'text-emerald-600' : 'text-slate-400'">{{ day.day }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="rounded-[2rem] bg-white border border-slate-100 p-5 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-black text-slate-800">Giao dịch gần đây</h3>
                            <Link href="/shipper/history" class="text-xs font-bold text-emerald-600">Xem tất cả</Link>
                        </div>

                        <div v-if="recentOrders.length === 0" class="py-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                            <span class="text-3xl block mb-2 opacity-50">💸</span>
                            <p class="text-xs font-semibold text-slate-500">Chưa có thu nhập nào</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div 
                                v-for="order in recentOrders" 
                                :key="order.id"
                                class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100/50"
                            >
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg shrink-0 shadow-sm">
                                        ✓
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-900 truncate">{{ order.restaurant.name }}</p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[9px] font-black text-slate-500 uppercase">#{{ order.order_code }}</span>
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span class="text-[9px] text-slate-400">{{ formatTime(order.completed_at) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0 pl-2">
                                    <p class="text-sm font-black text-emerald-600">
                                        +{{ formatCurrency((parseFloat(order.shipping_fee) || 0) + (parseFloat(order.shipper_tip) || 0)) }}
                                    </p>
                                    <p class="text-[9px] text-slate-400 font-semibold mt-0.5">{{ order.distance ? order.distance + 'km' : '' }}</p>
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
import { router, Link } from "@inertiajs/vue3";
import ShipperLayout from "@/Layouts/ShipperLayout.vue";

const props = defineProps({
    todayIncome: {
        type: Number,
        default: 0
    },
    weekIncome: {
        type: Number,
        default: 0
    },
    monthIncome: {
        type: Number,
        default: 0
    },
    todayOrdersCount: {
        type: Number,
        default: 0
    },
    chartData: {
        type: Array,
        default: () => []
    },
    recentOrders: {
        type: Array,
        default: () => []
    }
});

const maxIncome = computed(() => {
    if (!props.chartData || props.chartData.length === 0) return 10000;
    const max = Math.max(...props.chartData.map(d => parseFloat(d.income) || 0));
    return max > 0 ? max : 10000;
});

const getBarHeight = (income) => {
    const val = parseFloat(income) || 0;
    // Tối thiểu là 4% để bar luôn hiện ra 1 chút, tối đa 100%
    const percentage = Math.max(4, (val / maxIncome.value) * 100);
    return `${percentage}%`;
};

const goBack = () => {
    router.visit("/shipper/dashboard");
};

const formatCurrency = (amount) => {
    const val = parseFloat(amount) || 0;
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
        maximumFractionDigits: 0
    }).format(val);
};

const formatTime = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('vi-VN', {
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
};
</script>
