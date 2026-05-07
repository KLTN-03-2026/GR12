<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import RestaurantLayout from "@/Layouts/RestaurantLayout.vue";

defineOptions({ layout: RestaurantLayout });

const props = defineProps({ order: Object });

const formatPrice = (p) =>
    new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(p);

const formatDate = (dateString) => {
    if (!dateString) return "--";
    const d = new Date(dateString);
    return d.toLocaleString("vi-VN", {
        hour: "2-digit",
        minute: "2-digit",
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
};

const changeStatus = (id, newStatus, isFoodReady = false) => {
    const payload = isFoodReady ? { is_food_ready: true } : { status: newStatus };
    router.patch(route("restaurant.orders.update", id), payload, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Chi tiết đơn hàng" />

    <div class="max-w-5xl mx-auto space-y-8 pb-20 animate-fade-in-up">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
            <div class="flex items-center gap-5">
                <Link
                    :href="route('restaurant.orders.index')"
                    class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-900 hover:text-white hover:border-slate-900 hover:shadow-lg transition-all group"
                >
                    <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                        Chi tiết đơn hàng
                    </h1>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mt-1">
                        <span class="text-orange-500">#{{ order.order_code }}</span> <span class="mx-2 text-slate-300">•</span> {{ formatDate(order.created_at) }}
                    </p>
                </div>
            </div>
            
            <div class="flex items-center">
                <div v-if="order.status === 'completed'" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 text-emerald-600 font-bold text-sm border border-emerald-200/50 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Hoàn tất
                </div>
                <div v-else-if="order.status === 'cancelled'" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-50 text-rose-600 font-bold text-sm border border-rose-200/50 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Đã hủy
                </div>
                <div v-else class="flex items-center gap-2 px-4 py-2 rounded-xl bg-orange-50 text-orange-600 font-bold text-sm border border-orange-200/50 shadow-sm">
                    <svg class="w-4 h-4 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Đang xử lý
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left Column: Details -->
            <div class="lg:col-span-7 xl:col-span-8 space-y-8">
                <!-- Grid Khách hàng & Shipper -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Customer Info -->
                    <div class="bg-white rounded-[2rem] p-7 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 hover:shadow-md transition-shadow">
                        <h2 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-5 flex items-center gap-2 bg-slate-50 w-fit px-3 py-1.5 rounded-lg">
                            <span class="text-base">👤</span> Khách hàng
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Người nhận</p>
                                <p class="font-black text-slate-800 text-lg">{{ order.user?.name || 'Khách vãng lai' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Điện thoại</p>
                                <a :href="'tel:' + order.phone" class="font-bold text-blue-600 hover:text-blue-700 flex items-center gap-2 w-fit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    {{ order.phone }}
                                </a>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Địa chỉ</p>
                                <p class="font-medium text-slate-700 text-sm leading-relaxed">{{ order.address }}</p>
                            </div>
                            <div v-if="order.note" class="pt-3 border-t border-slate-100">
                                <p class="text-[10px] font-bold text-orange-400 uppercase tracking-widest mb-1 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg> Ghi chú</p>
                                <p class="font-bold text-orange-600 text-sm italic">{{ order.note }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Shipper Info -->
                    <div class="bg-white rounded-[2rem] p-7 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 hover:shadow-md transition-shadow relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-gradient-to-br from-purple-100 to-purple-50 rounded-full blur-2xl opacity-50"></div>
                        
                        <h2 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-5 flex items-center gap-2 bg-slate-50 w-fit px-3 py-1.5 rounded-lg relative z-10">
                            <span class="text-base">🛵</span> Đối tác Shipper
                        </h2>
                        
                        <div v-if="order.shipper" class="relative z-10">
                            <div class="flex items-center gap-4 mb-5">
                                <div class="w-14 h-14 rounded-[1rem] bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-xl font-black text-white shadow-md">
                                    {{ order.shipper.user?.name?.charAt(0) || 'S' }}
                                </div>
                                <div class="flex-1">
                                    <p class="font-black text-lg text-slate-800 tracking-tight">{{ order.shipper.user?.name }}</p>
                                    <a :href="'tel:' + order.shipper.user?.phone" class="text-sm text-slate-500 font-medium hover:text-purple-600 flex items-center gap-1 w-fit">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                        {{ order.shipper.user?.phone }}
                                    </a>
                                </div>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 flex justify-between items-center">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Phương tiện</p>
                                <p class="font-bold text-purple-700">{{ order.shipper.vehicle_type || 'Xe máy' }}</p>
                            </div>
                        </div>
                        <div v-else class="h-[120px] flex flex-col items-center justify-center relative z-10 text-center">
                            <svg class="w-10 h-10 text-slate-300 animate-spin-slow mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Đang tìm Shipper...</p>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="bg-white rounded-[2rem] p-7 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
                    <h2 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2 bg-slate-50 w-fit px-3 py-1.5 rounded-lg">
                        <span class="text-base">📋</span> Danh sách món ăn
                    </h2>
                    <div class="space-y-4">
                        <div v-for="item in order.items" :key="item.id" class="group flex gap-4 p-4 rounded-[1.5rem] bg-white border border-slate-100 hover:border-orange-200 hover:shadow-md transition-all">
                            <div class="w-20 h-20 rounded-xl overflow-hidden shadow-sm flex-shrink-0 relative">
                                <img
                                    v-if="item.product?.image"
                                    :src="'/storage/' + item.product.image"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                />
                                <div class="absolute inset-0 bg-slate-900/10 pointer-events-none"></div>
                            </div>
                            <div class="flex-1 min-w-0 flex flex-col justify-center">
                                <p class="text-base font-bold text-slate-900 line-clamp-1 group-hover:text-orange-600 transition-colors">{{ item.product?.name }}</p>
                                <div class="flex items-center justify-between mt-2">
                                    <p class="text-[10px] font-black text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md uppercase tracking-widest">
                                        SL: {{ item.quantity }}
                                    </p>
                                    <p class="font-bold text-slate-500 text-sm">{{ formatPrice(item.price) }}</p>
                                </div>
                                <div class="mt-2 pt-2 border-t border-slate-50 flex justify-between items-center">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Thành tiền</p>
                                    <p class="text-sm font-black text-slate-900">{{ formatPrice(item.price * item.quantity) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Summary & Actions -->
            <div class="lg:col-span-5 xl:col-span-4 space-y-6">
                <!-- Payment Summary (Receipt Style) -->
                <div class="bg-slate-900 rounded-[2rem] p-7 shadow-xl text-white relative overflow-hidden">
                    <!-- Receipt Zig-zag top & bottom simulation via background pattern -->
                    <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 16px 16px;"></div>
                    
                    <div class="relative z-10">
                        <h2 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"></path></svg>
                            Hóa đơn thanh toán
                        </h2>
                        
                        <div class="space-y-4 text-sm font-medium text-slate-300">
                            <div class="flex justify-between items-center">
                                <span>Doanh thu món</span>
                                <span class="text-white">{{ formatPrice(order.subtotal) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-rose-400">
                                <span>Chiết khấu nền tảng ({{ (Number(order.restaurant_commission_rate) ? order.restaurant_commission_rate : 0.25) * 100 }}%)</span>
                                <span>-{{ formatPrice(order.subtotal * (Number(order.restaurant_commission_rate) ? order.restaurant_commission_rate : 0.25)) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-rose-400">
                                <span>Thuế GTGT & TNCN</span>
                                <span>-{{ formatPrice(Number(order.restaurant_tax_fee) ? order.restaurant_tax_fee : (order.subtotal * 0.045)) }}</span>
                            </div>
                            <div v-if="order.discount_amount > 0" class="flex justify-between items-center text-emerald-400 bg-emerald-400/10 p-2 rounded-lg -mx-2 mt-2">
                                <span>Khách hàng áp dụng Voucher</span>
                                <span class="font-bold">-{{ formatPrice(order.discount_amount) }} (Nền tảng chịu)</span>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-dashed border-slate-700">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-emerald-400">Thực nhận của Quán</p>
                            <p class="text-4xl font-black text-emerald-400 tracking-tight drop-shadow-md">
                                {{ formatPrice(Number(order.restaurant_revenue) ? order.restaurant_revenue : (order.subtotal - (order.subtotal * (Number(order.restaurant_commission_rate) ? order.restaurant_commission_rate : 0.25)) - (Number(order.restaurant_tax_fee) ? order.restaurant_tax_fee : (order.subtotal * 0.045)))) }}
                            </p>
                        </div>
                        
                        <div class="mt-6 flex items-center justify-center gap-2 text-[10px] font-bold uppercase tracking-widest bg-slate-800/50 py-3 rounded-xl border border-slate-700/50">
                            <span class="w-2 h-2 rounded-full shadow-[0_0_8px_currentColor]" :class="order.payment_status === 'paid' ? 'bg-emerald-400 text-emerald-400' : 'bg-amber-400 text-amber-400'"></span>
                            <span :class="order.payment_status === 'paid' ? 'text-emerald-400' : 'text-amber-400'">
                                {{ order.payment_status === 'paid' ? 'Đã thanh toán Online' : 'Thanh toán tiền mặt' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Panel -->
                <div class="bg-white rounded-[2rem] p-7 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 sticky top-6">
                    <h2 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-5 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Hành động / Trạng thái
                    </h2>
                    
                    <div class="space-y-4">
                        <!-- Action Button -->
                        <button
                            v-if="!order.is_food_ready && (order.status === 'assigned' || order.status === 'shipping' || order.status === 'arrived')"
                            @click="changeStatus(order.id, null, true)"
                            class="w-full bg-gradient-to-r from-orange-500 to-orange-600 text-white font-black py-4 rounded-2xl shadow-[0_8px_20px_rgba(249,115,22,0.3)] hover:shadow-[0_12px_25px_rgba(249,115,22,0.4)] hover:-translate-y-1 transition-all uppercase text-[11px] tracking-widest flex items-center justify-center gap-2 group"
                        >
                            <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            Xác nhận làm xong món
                        </button>

                        <!-- Status Indicators -->
                        <div
                            v-if="order.is_food_ready && (order.status === 'assigned' || order.status === 'confirmed' || order.status === 'shipping' || order.status === 'arrived')"
                            class="w-full rounded-2xl bg-emerald-50 text-emerald-600 font-black py-4 px-6 text-center border border-emerald-200/60 uppercase text-[10px] tracking-widest flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Món đã sẵn sàng tại quán
                        </div>

                        <div
                            v-if="order.status === 'pending'"
                            class="w-full rounded-2xl bg-amber-50 text-amber-600 font-black py-4 px-6 text-center border border-amber-200/60 animate-pulse mt-3 uppercase text-[10px] tracking-widest flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Hệ thống đang tìm shipper...
                        </div>

                        <div
                            v-if="order.status === 'shipping'"
                            class="w-full rounded-2xl bg-purple-50 text-purple-600 font-black py-4 px-6 text-center border border-purple-200/60 mt-3 uppercase text-[10px] tracking-widest flex items-center justify-center gap-2"
                        >
                            <span class="text-sm">🚗</span> Shipper đang tới quán
                        </div>

                        <div
                            v-if="order.status === 'arrived'"
                            class="w-full rounded-2xl bg-pink-50 text-pink-600 font-black py-4 px-6 text-center border border-pink-200/60 mt-3 uppercase text-[10px] tracking-widest flex items-center justify-center gap-2"
                        >
                            <span class="text-sm">📍</span> Shipper đã đến quán chờ món
                        </div>

                        <div
                            v-if="order.status === 'picked_up'"
                            class="w-full rounded-2xl bg-indigo-50 text-indigo-600 font-black py-4 px-6 text-center border border-indigo-200/60 mt-3 uppercase text-[10px] tracking-widest flex items-center justify-center gap-2"
                        >
                            <span class="text-sm">📦</span> Shipper đã lấy hàng
                        </div>

                        <div
                            v-if="order.status === 'completed'"
                            class="w-full rounded-2xl bg-emerald-50 text-emerald-600 font-black py-4 px-6 text-center border border-emerald-200/60 mt-3 uppercase text-[10px] tracking-widest flex items-center justify-center gap-2"
                        >
                            <span class="text-sm">🎉</span> Đã giao thành công
                        </div>
                    </div>
                </div>
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
    animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.animate-spin-slow {
    animation: spin 3s linear infinite;
}

button {
    backface-visibility: hidden;
    transform: translateZ(0);
}
</style>
