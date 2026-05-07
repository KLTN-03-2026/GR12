<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

// Nhận dữ liệu từ AdminController
defineProps({
    stats: Object,
});

defineOptions({ layout: AdminLayout });
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="space-y-8 pb-10">
        <!-- Header Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 p-8 sm:p-10 shadow-2xl">
            <!-- Decorative blobs -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 rounded-full bg-gradient-to-br from-orange-500/30 to-red-500/30 blur-3xl mix-blend-screen"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 rounded-full bg-gradient-to-br from-blue-500/30 to-purple-500/30 blur-3xl mix-blend-screen"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div>
                    <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight mb-2">
                        Chào <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-red-400">{{ $page.props.auth.user.name }}</span>! 👋
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base font-medium max-w-xl">
                        Chào mừng bạn trở lại bảng điều khiển. Hôm nay hệ thống hoạt động rất tốt, hãy kiểm tra các chỉ số mới nhất bên dưới nhé!
                    </p>
                </div>
                <div class="shrink-0 flex gap-3">
                    <Link :href="route('admin.orders.index')" class="px-6 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold transition-all duration-300 backdrop-blur-sm border border-white/10 flex items-center gap-2">
                        <span>📦</span> Xem đơn hàng
                    </Link>
                    <Link :href="route('admin.pending')" class="px-6 py-3 rounded-xl bg-gradient-to-r from-orange-500 to-red-500 text-white font-bold shadow-lg shadow-orange-500/30 hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-2">
                        <span>⏳</span> Duyệt đối tác mới
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div>
            <h3 class="text-lg font-black text-slate-800 mb-4 uppercase tracking-widest">Tổng quan hệ thống</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card 1 -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center text-2xl group-hover:bg-blue-500 group-hover:text-white transition-colors duration-300">
                            👥
                        </div>
                        <span class="px-3 py-1 rounded-full bg-green-50 text-green-600 text-xs font-bold">+12%</span>
                    </div>
                    <div class="text-slate-500 text-sm font-bold uppercase tracking-widest mb-1">
                        Tổng khách hàng
                    </div>
                    <div class="text-4xl font-black text-slate-800">
                        {{ stats.total_customers }}
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center text-2xl group-hover:bg-orange-500 group-hover:text-white transition-colors duration-300">
                            🏪
                        </div>
                        <span v-if="(stats.pending_partners || stats.pending_restaurants) > 0" class="flex h-3 w-3 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-orange-500"></span>
                        </span>
                    </div>
                    <div class="text-slate-500 text-sm font-bold uppercase tracking-widest mb-1">
                        Đối tác chờ duyệt
                    </div>
                    <div class="text-4xl font-black text-slate-800">
                        {{ stats.pending_partners || stats.pending_restaurants || 0 }}
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-yellow-50 text-yellow-500 flex items-center justify-center text-2xl group-hover:bg-yellow-500 group-hover:text-white transition-colors duration-300">
                            🍔
                        </div>
                    </div>
                    <div class="text-slate-500 text-sm font-bold uppercase tracking-widest mb-1">
                        Món ăn chờ duyệt
                    </div>
                    <div class="text-4xl font-black text-slate-800">
                        {{ stats.pending_products || 0 }}
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-2xl group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300">
                            🛵
                        </div>
                    </div>
                    <div class="text-slate-500 text-sm font-bold uppercase tracking-widest mb-1">
                        Shipper đang chạy
                    </div>
                    <div class="text-4xl font-black text-slate-800">
                        {{ stats.active_shippers }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Placeholder for future 'Admin Power' features -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 opacity-60">
            <div class="bg-white border-2 border-dashed border-slate-200 rounded-3xl p-8 flex flex-col items-center justify-center text-center min-h-[200px]">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-2xl mb-4 grayscale">⚙️</div>
                <h4 class="font-bold text-slate-800">Cấu hình Hệ thống (Sắp ra mắt)</h4>
                <p class="text-sm text-slate-500 mt-2">Chỉnh sửa phí ship, % hoa hồng và các thông số vận hành khác.</p>
            </div>
            <div class="bg-white border-2 border-dashed border-slate-200 rounded-3xl p-8 flex flex-col items-center justify-center text-center min-h-[200px]">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-2xl mb-4 grayscale">💰</div>
                <h4 class="font-bold text-slate-800">Quản lý Tài chính nâng cao</h4>
                <p class="text-sm text-slate-500 mt-2">Trực tiếp can thiệp ví người dùng, hoàn tiền và cộng thưởng.</p>
            </div>
        </div>
    </div>
</template>

