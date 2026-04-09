<script setup>
import RestaurantLayout from "@/Layouts/RestaurantLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    stats: Object,
});

// Cách này là chuẩn để Inertia tự bọc Layout mà không bị lặp
defineOptions({ layout: RestaurantLayout });
</script>

<template>
    <Head title="Quản lý quán ăn" />

    <div class="p-4 sm:p-8 space-y-8">
        <div class="mb-6">
            <h2 class="text-2xl font-black text-gray-800 tracking-tight">
                Tổng quan cửa hàng
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div
                class="bg-white p-6 rounded-[2rem] border border-orange-100 shadow-sm hover:shadow-md transition-shadow"
            >
                <div class="flex items-center gap-4">
                    <div class="bg-orange-100 p-3 rounded-2xl text-orange-600">
                        <svg
                            class="w-8 h-8"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"
                                stroke-width="2"
                            />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-black uppercase">
                            Thực đơn
                        </p>
                        <p class="text-2xl font-black text-gray-800">
                            {{ stats.total_products }} món
                        </p>
                    </div>
                </div>
                <Link
                    :href="route('restaurant.products.index')"
                    class="mt-4 block text-center text-xs font-bold text-orange-500 hover:underline"
                    >Xem chi tiết →</Link
                >
            </div>

            <div
                class="bg-white p-6 rounded-[2rem] border border-blue-100 shadow-sm"
            >
                <div class="flex items-center gap-4">
                    <div class="bg-blue-100 p-3 rounded-2xl text-blue-600">
                        <svg
                            class="w-8 h-8"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                                stroke-width="2"
                            />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-black uppercase">
                            Đơn hàng mới
                        </p>
                        <p class="text-2xl font-black text-gray-800">
                            {{ stats.active_orders }} đơn
                        </p>
                    </div>
                </div>
                <p
                    class="mt-4 text-center text-[10px] font-bold text-blue-400 italic"
                >
                    Đang xử lý...
                </p>
            </div>

            <div
                class="bg-white p-6 rounded-[2rem] border border-green-100 shadow-sm"
            >
                <div class="flex items-center gap-4">
                    <div class="bg-green-100 p-3 rounded-2xl text-green-600">
                        <svg
                            class="w-8 h-8"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                stroke-width="2"
                            />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-black uppercase">
                            Doanh thu ngày
                        </p>
                        <p class="text-2xl font-black text-gray-800">
                            {{ stats.today_revenue }}
                        </p>
                    </div>
                </div>
                <p
                    class="mt-4 text-center text-[10px] text-green-500 font-bold italic"
                >
                    +15% so với hôm qua
                </p>
            </div>
        </div>

        <div
            class="bg-gradient-to-br from-orange-500 to-orange-400 rounded-[3rem] p-10 text-white relative overflow-hidden shadow-xl shadow-orange-100"
        >
            <div class="relative z-10 max-w-xl">
                <h3 class="text-3xl font-black mb-3 italic">
                    Chào mừng Hoàng Anh Quán!
                </h3>
                <p
                    class="text-orange-50 opacity-90 leading-relaxed font-medium"
                >
                    Hôm nay là một ngày tuyệt vời để phục vụ những món ăn ngon
                    nhất đến khách hàng của bạn.
                </p>
                <div class="mt-8">
                    <Link
                        :href="route('restaurant.products.create')"
                        class="bg-white text-orange-500 px-8 py-3 rounded-2xl font-black text-sm transition hover:bg-orange-50 active:scale-95 shadow-lg"
                    >
                        Thêm món mới ngay
                    </Link>
                </div>
            </div>
            <div
                class="absolute right-[-20px] bottom-[-40px] opacity-10 rotate-12"
            >
                <svg class="w-72 h-72" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z"
                    />
                </svg>
            </div>
        </div>
    </div>
</template>
