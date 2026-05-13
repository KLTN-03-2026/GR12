<template>
    <ShipperLayout>
        <template #default>
            <div class="space-y-6 pb-24">

                <!-- Menu Items Section -->
                <section>
                    <h2 class="text-sm font-black uppercase tracking-widest text-slate-400 px-2 mb-3">Tính năng</h2>
                    <div class="bg-white rounded-[2rem] shadow-sm ring-1 ring-slate-100 overflow-hidden divide-y divide-slate-50">
                        <Link
                            href="/shipper/menu/destination"
                            class="flex items-center justify-between p-5 hover:bg-slate-50 active:bg-slate-100 transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-[1.25rem] bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">📍</div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">Điểm đến của tôi</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Lộ trình & điểm giao</p>
                                </div>
                            </div>
                            <span class="text-slate-300 text-xl font-light">›</span>
                        </Link>
                        
                        <Link
                            href="/shipper/notifications"
                            class="flex items-center justify-between p-5 hover:bg-slate-50 active:bg-slate-100 transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-[1.25rem] bg-orange-50 text-orange-600 flex items-center justify-center text-xl shrink-0">🔔</div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">Thông báo</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Tin tức hệ thống</p>
                                </div>
                            </div>
                            <span class="text-slate-300 text-xl font-light">›</span>
                        </Link>
                        
                        <Link
                            href="/shipper/income"
                            class="flex items-center justify-between p-5 hover:bg-slate-50 active:bg-slate-100 transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-[1.25rem] bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">💰</div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">Thu nhập</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Thống kê doanh thu</p>
                                </div>
                            </div>
                            <span class="text-slate-300 text-xl font-light">›</span>
                        </Link>
                        
                        <Link
                            href="/shipper/wallet"
                            class="flex items-center justify-between p-5 hover:bg-slate-50 active:bg-slate-100 transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-[1.25rem] bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">💳</div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">Ví thanh toán</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Số dư & Rút tiền</p>
                                </div>
                            </div>
                            <span class="text-slate-300 text-xl font-light">›</span>
                        </Link>
                        
                        <Link
                            href="/shipper/history"
                            class="flex items-center justify-between p-5 hover:bg-slate-50 active:bg-slate-100 transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-[1.25rem] bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">📋</div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">Lịch sử đơn hàng</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Các đơn đã giao</p>
                                </div>
                            </div>
                            <span class="text-slate-300 text-xl font-light">›</span>
                        </Link>
                    </div>
                </section>

                <section>
                    <h2 class="text-sm font-black uppercase tracking-widest text-slate-400 px-2 mb-3">Hỗ trợ & Cài đặt</h2>
                    <div class="bg-white rounded-[2rem] shadow-sm ring-1 ring-slate-100 overflow-hidden divide-y divide-slate-50">
                        <Link
                            href="/shipper/menu/help"
                            class="flex items-center justify-between p-5 hover:bg-slate-50 active:bg-slate-100 transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-[1.25rem] bg-sky-50 text-sky-600 flex items-center justify-center text-xl shrink-0">🎧</div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">Trung tâm trợ giúp</p>
                                </div>
                            </div>
                            <span class="text-slate-300 text-xl font-light">›</span>
                        </Link>
                        
                        <Link
                            href="/shipper/menu/settings"
                            class="flex items-center justify-between p-5 hover:bg-slate-50 active:bg-slate-100 transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-[1.25rem] bg-slate-50 text-slate-600 flex items-center justify-center text-xl shrink-0">⚙️</div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">Cài đặt ứng dụng</p>
                                </div>
                            </div>
                            <span class="text-slate-300 text-xl font-light">›</span>
                        </Link>
                    </div>
                </section>
            </div>


        </template>
    </ShipperLayout>
</template>

<script setup>
import { onMounted } from "vue";
import { Link } from "@inertiajs/vue3";
import ShipperLayout from "@/Layouts/ShipperLayout.vue";



onMounted(() => {
    // Basic dashboard mount tracking if needed
});
</script>
