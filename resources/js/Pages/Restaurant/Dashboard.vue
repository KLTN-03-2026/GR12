<script setup>
import RestaurantLayout from "@/Layouts/RestaurantLayout.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";

const props = defineProps({
    stats: Object,
});

const user = usePage().props.auth.user;

const form = useForm({
    opening_time: user.opening_time ? user.opening_time.substring(0,5) : '00:00',
    closing_time: user.closing_time ? user.closing_time.substring(0,5) : '23:59',
    is_accepting_orders: user.is_accepting_orders == 1,
});

const submitSettings = () => {
    form.patch(route('restaurant.settings.operating-hours'), {
        preserveScroll: true,
    });
};

// Cách này là chuẩn để Inertia tự bọc Layout mà không bị lặp
defineOptions({ layout: RestaurantLayout });
</script>

<template>
    <Head title="Quản lý quán ăn" />

    <div class="space-y-8 animate-fade-in-up pb-10">
        <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-6 mb-2">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">
                    Tổng quan cửa hàng
                </h2>
                <p class="text-slate-500 font-medium mt-1">Theo dõi hoạt động kinh doanh hôm nay của bạn.</p>
            </div>
            
            <!-- SETTINGS PANEL -->
            <div class="bg-white rounded-[2rem] p-4 lg:p-5 border border-slate-200 shadow-lg shadow-slate-200/50 flex flex-col md:flex-row items-center gap-6">
                <!-- Toggle Switch -->
                <div class="flex items-center gap-3">
                    <div class="flex flex-col text-right">
                        <span class="text-sm font-black text-slate-800">Nhận đơn</span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Trạng thái quán</span>
                    </div>
                    <button 
                        @click="form.is_accepting_orders = !form.is_accepting_orders; submitSettings()"
                        class="w-16 h-8 rounded-full relative transition-colors duration-300 shadow-inner"
                        :class="form.is_accepting_orders ? 'bg-gradient-to-r from-emerald-400 to-emerald-500' : 'bg-slate-300'"
                    >
                        <div class="absolute top-1 w-6 h-6 rounded-full bg-white shadow-md transition-transform duration-300"
                             :class="form.is_accepting_orders ? 'left-9' : 'left-1'"></div>
                    </button>
                </div>
                
                <div class="hidden md:block w-px h-10 bg-slate-200"></div>
                
                <!-- Time Inputs -->
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 uppercase">Mở:</span>
                        <input @change="submitSettings" type="time" v-model="form.opening_time" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm font-black text-slate-700 outline-none focus:border-orange-500">
                    </div>
                    <span class="text-slate-300 font-bold">-</span>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 uppercase">Đóng:</span>
                        <input @change="submitSettings" type="time" v-model="form.closing_time" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm font-black text-slate-700 outline-none focus:border-orange-500">
                    </div>
                </div>
                
                <!-- Status Indicator -->
                <div class="flex items-center gap-2 bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-100">
                    <span class="w-2.5 h-2.5 rounded-full animate-pulse" :class="form.is_accepting_orders ? 'bg-emerald-500' : 'bg-red-500'"></span>
                    <span class="text-xs font-black uppercase tracking-widest" :class="form.is_accepting_orders ? 'text-emerald-600' : 'text-red-500'">
                        {{ form.is_accepting_orders ? 'Mở cửa' : 'Đóng cửa' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Thẻ 1: Thực đơn -->
            <div
                class="bg-white rounded-[2rem] p-6 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgb(249,115,22,0.1)] transition-all duration-300 transform hover:-translate-y-2 group relative overflow-hidden"
            >
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-orange-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-out z-0"></div>
                <div class="relative z-10 flex flex-col h-full justify-between">
                    <div class="flex items-start justify-between mb-4">
                        <div class="bg-gradient-to-br from-orange-400 to-orange-600 p-3.5 rounded-2xl text-white shadow-lg shadow-orange-500/30 group-hover:rotate-12 transition-transform duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">
                            Sản phẩm
                        </p>
                        <p class="text-4xl font-black text-slate-900 tracking-tighter">
                            {{ stats.total_products }} <span class="text-lg text-slate-400 font-bold ml-1 tracking-normal">món</span>
                        </p>
                    </div>
                    <Link
                        :href="route('restaurant.products.index')"
                        class="mt-6 flex items-center justify-between text-xs font-bold text-orange-600 bg-orange-50 px-4 py-2.5 rounded-xl group-hover:bg-orange-100 transition-colors"
                    >
                        Quản lý thực đơn
                        <span class="transform group-hover:translate-x-1 transition-transform">→</span>
                    </Link>
                </div>
            </div>

            <!-- Thẻ 2: Đơn hàng mới -->
            <div
                class="bg-white rounded-[2rem] p-6 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgb(59,130,246,0.1)] transition-all duration-300 transform hover:-translate-y-2 group relative overflow-hidden"
            >
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-out z-0"></div>
                <div class="relative z-10 flex flex-col h-full justify-between">
                    <div class="flex items-start justify-between mb-4">
                        <div class="bg-gradient-to-br from-blue-400 to-blue-600 p-3.5 rounded-2xl text-white shadow-lg shadow-blue-500/30 group-hover:rotate-12 transition-transform duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <span v-if="stats.active_orders > 0" class="bg-red-100 text-red-600 text-xs font-black px-3 py-1 rounded-full animate-bounce">
                            HOT
                        </span>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">
                            Đơn đang xử lý
                        </p>
                        <p class="text-4xl font-black text-slate-900 tracking-tighter">
                            {{ stats.active_orders }} <span class="text-lg text-slate-400 font-bold ml-1 tracking-normal">đơn</span>
                        </p>
                    </div>
                    <Link
                        :href="route('restaurant.orders.index')"
                        class="mt-6 flex items-center justify-between text-xs font-bold text-blue-600 bg-blue-50 px-4 py-2.5 rounded-xl group-hover:bg-blue-100 transition-colors"
                    >
                        Xem danh sách đơn
                        <span class="transform group-hover:translate-x-1 transition-transform">→</span>
                    </Link>
                </div>
            </div>

            <!-- Thẻ 3: Doanh thu -->
            <div
                class="bg-white rounded-[2rem] p-6 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgb(16,185,129,0.1)] transition-all duration-300 transform hover:-translate-y-2 group relative overflow-hidden"
            >
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-out z-0"></div>
                <div class="relative z-10 flex flex-col h-full justify-between">
                    <div class="flex items-start justify-between mb-4">
                        <div class="bg-gradient-to-br from-emerald-400 to-emerald-600 p-3.5 rounded-2xl text-white shadow-lg shadow-emerald-500/30 group-hover:rotate-12 transition-transform duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-black text-emerald-600 bg-emerald-100 px-2 py-1 rounded-lg">
                            +15%
                        </span>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">
                            Doanh thu hôm nay
                        </p>
                        <p class="text-3xl font-black text-slate-900 tracking-tighter truncate" :title="stats.today_revenue">
                            {{ stats.today_revenue }}
                        </p>
                    </div>
                    <div class="mt-6 flex items-center gap-2">
                        <div class="h-1.5 flex-1 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 w-[70%] rounded-full"></div>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400">Đạt 70% KPI</span>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-[3rem] p-8 md:p-12 text-white relative overflow-hidden shadow-2xl shadow-slate-900/20 group"
        >
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 w-[40rem] h-[40rem] bg-gradient-to-bl from-orange-500/20 to-transparent rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 group-hover:translate-x-1/4 transition-transform duration-1000"></div>
            <div class="absolute bottom-0 left-0 w-[30rem] h-[30rem] bg-gradient-to-tr from-blue-500/20 to-transparent rounded-full blur-3xl translate-y-1/2 -translate-x-1/4 group-hover:-translate-x-1/3 transition-transform duration-1000"></div>
            
            <!-- Grid pattern overlay -->
            <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: linear-gradient(to right, #ffffff 1px, transparent 1px), linear-gradient(to bottom, #ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>

            <div class="relative z-10 md:w-2/3">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/10 mb-6">
                    <span class="text-orange-400">✨</span>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-white/90">Phiên bản mới</span>
                </div>
                
                <h3 class="text-4xl md:text-5xl font-black mb-4 tracking-tight leading-tight">
                    Chào mừng trở lại,<br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-yellow-300">
                        {{ $page.props.auth.user.restaurant_name || "Quản lý Cửa Hàng" }}!
                    </span>
                </h3>
                
                <p class="text-slate-300 text-base leading-relaxed font-medium mb-8 max-w-xl">
                    Hôm nay là một ngày tuyệt vời để phục vụ những món ăn ngon nhất đến khách hàng của bạn. Hãy cập nhật thực đơn và chuẩn bị đón các đơn hàng mới nào.
                </p>
                
                <div class="flex flex-wrap gap-4">
                    <Link
                        :href="route('restaurant.products.create')"
                        class="bg-gradient-to-r from-orange-500 to-orange-600 text-white px-8 py-3.5 rounded-2xl font-black text-sm transition-all hover:scale-105 hover:shadow-[0_0_20px_rgba(249,115,22,0.4)] active:scale-95 flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        Thêm món mới ngay
                    </Link>
                    <Link
                        :href="route('restaurant.orders.index')"
                        class="bg-white/10 backdrop-blur-md text-white border border-white/20 px-8 py-3.5 rounded-2xl font-black text-sm transition-all hover:bg-white/20 hover:scale-105 active:scale-95"
                    >
                        Kiểm tra đơn hàng
                    </Link>
                </div>
            </div>
            
            <div class="absolute right-0 bottom-0 pointer-events-none opacity-20 transform translate-x-1/4 translate-y-1/4">
                <svg width="400" height="400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z" />
                </svg>
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
</style>
