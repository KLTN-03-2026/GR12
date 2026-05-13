<script setup>
import { ref } from 'vue';
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { Line, Doughnut } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  Filler
} from 'chart.js';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  Filler
);

const props = defineProps({
    stats: Object,
});

defineOptions({ layout: AdminLayout });

// --- Cấu hình Biểu đồ Line (Doanh thu) ---
const lineChartData = {
    labels: props.stats.revenue_chart.labels,
    datasets: [
        {
            label: 'Tổng Doanh Thu Sàn',
            backgroundColor: 'rgba(249, 115, 22, 0.1)',
            borderColor: '#f97316', // orange-500
            data: props.stats.revenue_chart.system_revenue,
            fill: true,
            tension: 0.4,
            borderWidth: 2,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#f97316',
            pointRadius: 4,
            pointHoverRadius: 6
        },
        {
            label: 'Doanh Thu Admin (Hoa hồng)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderColor: '#3b82f6', // blue-500
            data: props.stats.revenue_chart.admin_revenue,
            fill: true,
            tension: 0.4,
            borderWidth: 2,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#3b82f6',
            pointRadius: 4,
            pointHoverRadius: 6
        }
    ]
};

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
            labels: { font: { family: "'Inter', sans-serif", weight: 'bold' } }
        },
        tooltip: {
            mode: 'index',
            intersect: false,
            callbacks: {
                label: function(context) {
                    let label = context.dataset.label || '';
                    if (label) {
                        label += ': ';
                    }
                    if (context.parsed.y !== null) {
                        label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed.y);
                    }
                    return label;
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: function(value) {
                    return value >= 1000000 ? (value / 1000000) + 'M' : value >= 1000 ? (value / 1000) + 'K' : value;
                }
            }
        }
    }
};

// --- Cấu hình Biểu đồ Doughnut (Trạng thái đơn) ---
const doughnutChartData = {
    labels: ['Thành công', 'Đã hủy', 'Chờ xử lý', 'Đang giao', 'Đã nhận đơn'],
    datasets: [
        {
            backgroundColor: [
                '#10b981', // green-500
                '#ef4444', // red-500
                '#f59e0b', // amber-500
                '#3b82f6', // blue-500
                '#8b5cf6'  // violet-500
            ],
            data: [
                props.stats.order_status_chart.completed,
                props.stats.order_status_chart.cancelled,
                props.stats.order_status_chart.pending,
                props.stats.order_status_chart.delivering,
                props.stats.order_status_chart.accepted,
            ],
            borderWidth: 0,
            hoverOffset: 4
        }
    ]
};

const doughnutChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: { font: { family: "'Inter', sans-serif", weight: 'bold' }, padding: 20 }
        }
    },
    cutout: '70%'
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
};
</script>

<template>
    <Head title="Admin Analytics Dashboard" />

    <div class="space-y-8 pb-10">
        <!-- Header Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 p-8 sm:p-10 shadow-2xl">
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 rounded-full bg-gradient-to-br from-orange-500/30 to-red-500/30 blur-3xl mix-blend-screen"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 rounded-full bg-gradient-to-br from-blue-500/30 to-purple-500/30 blur-3xl mix-blend-screen"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div>
                    <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight mb-2">
                        Báo cáo Thống kê & Doanh thu 📈
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base font-medium max-w-xl">
                        Chào <span class="text-white">{{ $page.props.auth.user.name }}</span>! Dưới đây là bức tranh toàn cảnh về sức khỏe tài chính và hoạt động của nền tảng FoodXpress.
                    </p>
                </div>
                <div class="shrink-0 flex gap-3">
                    <Link :href="route('admin.orders.index')" class="px-6 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold transition-all duration-300 backdrop-blur-sm border border-white/10 flex items-center gap-2">
                        <span>📦</span> Quản lý đơn
                    </Link>
                    <Link :href="route('admin.pending')" class="px-6 py-3 rounded-xl bg-gradient-to-r from-orange-500 to-red-500 text-white font-bold shadow-lg shadow-orange-500/30 hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-2">
                        <span>⏳</span> Duyệt đối tác
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stats Grid (Tài chính & Đơn hàng) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Card 1: Tổng Doanh thu Sàn -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-orange-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-orange-100 text-orange-500 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-300">
                            💰
                        </div>
                    </div>
                    <div class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">
                        Tổng Doanh thu Hệ thống
                    </div>
                    <div class="text-3xl font-black text-slate-800 tracking-tight">
                        {{ formatCurrency(stats.total_system_revenue) }}
                    </div>
                </div>
            </div>

            <!-- Card 2: Doanh thu Admin -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-500 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-300">
                            🛡️
                        </div>
                    </div>
                    <div class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">
                        Lợi nhuận Admin (Hoa hồng)
                    </div>
                    <div class="text-3xl font-black text-slate-800 tracking-tight text-blue-600">
                        {{ formatCurrency(stats.total_admin_revenue) }}
                    </div>
                </div>
            </div>

            <!-- Card 3: Tổng Đơn Hàng -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-500 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-300">
                            🧾
                        </div>
                    </div>
                    <div class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">
                        Tổng số đơn hàng
                    </div>
                    <div class="text-3xl font-black text-slate-800 tracking-tight">
                        {{ new Intl.NumberFormat('vi-VN').format(stats.total_orders) }}
                    </div>
                </div>
            </div>

            <!-- Card 4: Tỷ lệ thành công -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-500 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-300">
                            🎯
                        </div>
                    </div>
                    <div class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">
                        Tỷ lệ đơn thành công
                    </div>
                    <div class="flex items-baseline gap-2">
                        <div class="text-3xl font-black text-slate-800 tracking-tight" :class="stats.success_rate >= 80 ? 'text-emerald-600' : (stats.success_rate >= 50 ? 'text-orange-500' : 'text-red-600')">
                            {{ stats.success_rate }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ phân tích -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Line Chart: Doanh thu 7 ngày -->
            <div class="lg:col-span-2 bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-black text-slate-800">Biến động Doanh thu</h3>
                        <p class="text-sm text-slate-500 font-medium">7 ngày gần nhất</p>
                    </div>
                </div>
                <div class="h-[300px] w-full">
                    <Line :data="lineChartData" :options="lineChartOptions" />
                </div>
            </div>

            <!-- Doughnut Chart: Trạng thái đơn -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <div class="mb-6">
                    <h3 class="text-lg font-black text-slate-800">Phân bổ Đơn hàng</h3>
                    <p class="text-sm text-slate-500 font-medium">Trạng thái tổng thể</p>
                </div>
                <div class="h-[250px] w-full flex items-center justify-center relative">
                    <Doughnut :data="doughnutChartData" :options="doughnutChartOptions" />
                    <!-- Tỷ lệ thành công ở giữa -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none pb-8">
                        <span class="text-3xl font-black text-slate-800">{{ stats.success_rate }}%</span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Thành công</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bảng xếp hạng Top Quán ăn & User Stats -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Top Restaurants -->
            <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="text-lg font-black text-slate-800">🏆 Top 5 Đối tác Xuất sắc</h3>
                        <p class="text-sm text-slate-500 font-medium">Nhà hàng có doanh thu cao nhất</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white border-b border-slate-100 text-[10px] uppercase tracking-widest text-slate-400 font-black">
                                <th class="p-4 pl-6 w-16 text-center">Top</th>
                                <th class="p-4">Nhà hàng</th>
                                <th class="p-4 text-center">Đơn thành công</th>
                                <th class="p-4 text-right pr-6">Tổng Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(restaurant, index) in stats.top_restaurants" :key="restaurant.id" class="hover:bg-slate-50 transition-colors group">
                                <td class="p-4 pl-6 text-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mx-auto font-black text-sm shadow-sm"
                                        :class="index === 0 ? 'bg-yellow-100 text-yellow-600 border border-yellow-200' : (index === 1 ? 'bg-slate-200 text-slate-600 border border-slate-300' : (index === 2 ? 'bg-orange-100 text-orange-600 border border-orange-200' : 'bg-slate-50 text-slate-400'))">
                                        {{ index + 1 }}
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center font-black text-lg border border-orange-100">
                                            {{ (restaurant.restaurant_name || restaurant.name).charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ restaurant.restaurant_name || restaurant.name }}</p>
                                            <p class="text-xs text-slate-500">{{ restaurant.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 px-3 py-1 rounded-lg text-xs font-black">
                                        {{ new Intl.NumberFormat('vi-VN').format(restaurant.completed_orders || 0) }} đơn
                                    </span>
                                </td>
                                <td class="p-4 pr-6 text-right font-black text-orange-500">
                                    {{ formatCurrency(restaurant.total_revenue || 0) }}
                                </td>
                            </tr>
                            <tr v-if="stats.top_restaurants.length === 0">
                                <td colspan="4" class="p-8 text-center text-slate-400 font-medium">Chưa có dữ liệu giao dịch</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Nguồn lực hệ thống & Admin Powers -->
            <div class="space-y-6">
                <!-- User Stats -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-black text-slate-800 mb-6">Nguồn lực Hệ thống</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-2xl border border-blue-100">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-xl shadow-sm">👥</span>
                                <span class="font-bold text-slate-700">Khách hàng</span>
                            </div>
                            <span class="font-black text-blue-600 text-lg">{{ new Intl.NumberFormat('vi-VN').format(stats.total_customers) }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-2xl border border-emerald-100">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-xl shadow-sm">🛵</span>
                                <span class="font-bold text-slate-700">Tài xế (Shipper)</span>
                            </div>
                            <span class="font-black text-emerald-600 text-lg">{{ new Intl.NumberFormat('vi-VN').format(stats.active_shippers) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Admin Powers -->
                <Link :href="route('admin.settings.index')" class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-3xl p-6 flex items-center gap-4 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group block">
                    <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-300 border border-white/5 shadow-sm">⚙️</div>
                    <div>
                        <h4 class="font-black text-white text-base">Cấu hình Cốt lõi</h4>
                        <p class="text-xs text-slate-400 font-medium mt-1 line-clamp-2">Sửa phí giao hàng, hoa hồng nền tảng và cài đặt quan trọng.</p>
                    </div>
                </Link>

                <Link :href="route('admin.wallets.index')" class="bg-gradient-to-br from-emerald-500 to-teal-600 border border-emerald-400 rounded-3xl p-6 flex items-center gap-4 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group block">
                    <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-300 shadow-sm">💳</div>
                    <div>
                        <h4 class="font-black text-white text-base">Ví điện tử Admin</h4>
                        <p class="text-xs text-emerald-100 font-medium mt-1 line-clamp-2">Kiểm soát số dư, nạp/rút tiền thủ công cho người dùng.</p>
                    </div>
                </Link>
            </div>
        </div>
    </div>
</template>
