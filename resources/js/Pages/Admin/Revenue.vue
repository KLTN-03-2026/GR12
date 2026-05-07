<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    stats: Object,
    recent_orders: Array,
    chartData: Array
});

const maxRevenue = computed(() => {
    if (!props.chartData || props.chartData.length === 0) return 10000;
    const max = Math.max(...props.chartData.map(d => parseFloat(d.revenue) || 0));
    return max > 0 ? max : 10000;
});

const getBarHeight = (revenue) => {
    const val = parseFloat(revenue) || 0;
    const percentage = Math.max(4, (val / maxRevenue.value) * 100);
    return `${percentage}%`;
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN').format(value || 0) + 'đ';
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('vi-VN');
};
</script>

<template>
    <Head title="Báo cáo Doanh thu - Admin" />

    <AdminLayout>
        <div class="max-w-7xl mx-auto">
            <div class="mb-8">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Doanh thu hệ thống</h1>
                <p class="text-sm text-slate-500 mt-1 font-medium">Theo dõi sự tăng trưởng và dòng tiền từ dịch vụ.</p>
            </div>

            <!-- Stats & Chart Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Left: Stats Column -->
                <div class="flex flex-col gap-6">
                    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl p-6 text-white shadow-xl shadow-emerald-500/20 relative overflow-hidden flex-1 flex flex-col justify-center">
                        <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10 pointer-events-none"></div>
                        <p class="text-xs font-bold text-emerald-100 uppercase tracking-widest mb-2">Doanh thu hôm nay</p>
                        <p class="text-4xl sm:text-5xl font-black tracking-tight drop-shadow-sm">{{ formatCurrency(stats.today) }}</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white rounded-[2rem] p-5 shadow-sm border border-slate-100 flex flex-col items-center text-center justify-center">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Tháng này</p>
                            <p class="text-lg font-black text-slate-800">{{ formatCurrency(stats.month) }}</p>
                        </div>
                        <div class="bg-white rounded-[2rem] p-5 shadow-sm border border-slate-100 flex flex-col items-center text-center justify-center">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Tổng doanh thu</p>
                            <p class="text-lg font-black text-slate-800">{{ formatCurrency(stats.total) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Right: Chart Section -->
                <div class="lg:col-span-2 bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-black text-slate-800">Biểu đồ Doanh thu</h3>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">7 ngày gần nhất</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-lg">📈</div>
                    </div>

                    <div class="flex-1 min-h-[200px] flex items-end justify-between gap-2 pt-8 border-t border-slate-50">
                        <div v-for="(day, index) in chartData" :key="index" class="flex flex-col items-center flex-1 h-full">
                            <div class="w-full relative flex flex-col justify-end h-full group">
                                <div class="absolute -top-8 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-slate-800 text-white text-xs font-bold px-2 py-1 rounded shadow-lg pointer-events-none whitespace-nowrap z-10">
                                    {{ formatCurrency(day.revenue) }}
                                    <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 border-l-[4px] border-l-transparent border-r-[4px] border-r-transparent border-t-[4px] border-t-slate-800"></div>
                                </div>
                                <div 
                                    class="w-full max-w-[48px] mx-auto rounded-t-lg transition-all duration-700 ease-out"
                                    :class="index === chartData.length - 1 ? 'bg-emerald-500 shadow-lg shadow-emerald-500/30' : 'bg-slate-100 group-hover:bg-blue-400'"
                                    :style="{ height: getBarHeight(day.revenue) }"
                                ></div>
                            </div>
                            <div class="mt-3 text-center">
                                <p class="text-[10px] font-bold" :class="index === chartData.length - 1 ? 'text-emerald-600' : 'text-slate-500'">{{ day.day }}</p>
                                <p class="text-[9px] text-slate-400 mt-0.5">{{ day.date }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest">Giao dịch sinh lời gần đây</h2>
                </div>
                
                <div v-if="recent_orders.length === 0" class="text-center py-12 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                    <span class="text-4xl block mb-3 opacity-40">🧾</span>
                    <p class="text-sm text-slate-500 font-bold">Chưa có giao dịch doanh thu nào gần đây.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b-2 border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                <th class="py-4 px-4 whitespace-nowrap">Đơn hàng</th>
                                <th class="py-4 px-4 whitespace-nowrap">Khách hàng & Shipper</th>
                                <th class="py-4 px-4 whitespace-nowrap text-right">Tổng đơn</th>
                                <th class="py-4 px-4 whitespace-nowrap text-right">Phí DV</th>
                                <th class="py-4 px-4 whitespace-nowrap text-right">Hoa hồng</th>
                                <th class="py-4 px-4 whitespace-nowrap text-right text-emerald-600">Thực thu Admin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="order in recent_orders" :key="order.id" class="hover:bg-slate-50 transition-colors group">
                                <td class="py-4 px-4">
                                    <span class="text-sm font-black text-blue-600">#{{ order.order_code }}</span>
                                    <span class="block text-[10px] text-slate-500 mt-0.5 font-medium">{{ formatDate(order.updated_at) }}</span>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-1.5 mb-1 text-xs">
                                        <span class="w-4 text-center">👤</span>
                                        <span class="font-bold text-slate-800">{{ order.user?.name || 'Unknown' }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-[10px]">
                                        <span class="w-4 text-center">🛵</span>
                                        <span class="font-medium text-slate-500">{{ order.shipper?.user?.name || 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <span class="text-sm font-bold text-slate-700">{{ formatCurrency(order.total) }}</span>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <span class="text-sm font-bold text-slate-700">{{ formatCurrency(order.service_fee) }}</span>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <span class="text-xs font-black text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">{{ order.restaurant_commission_rate * 100 }}%</span>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <span class="text-base font-black text-emerald-600 group-hover:scale-105 inline-block transition-transform duration-300">
                                        +{{ formatCurrency(order.admin_revenue) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
