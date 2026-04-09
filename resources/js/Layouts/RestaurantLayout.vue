<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import ToastList from "@/Components/ToastList.vue"; // Thêm Toast để báo nhận đơn thành công
import { computed } from "vue";

const page = usePage();

// Kiểm tra component hiện tại để active menu chính xác hơn
const currentComponent = computed(() => page.component);
</script>

<template>
    <div class="min-h-screen bg-[#fdf2f0] flex font-sans">
        <ToastList />

        <aside
            class="w-72 bg-white shadow-2xl flex flex-col sticky top-0 h-screen z-50"
        >
            <div class="p-8">
                <Link
                    :href="route('restaurant.dashboard')"
                    class="flex flex-col"
                >
                    <span
                        class="text-3xl font-black text-orange-500 italic tracking-tighter"
                        >FoodXpress</span
                    >
                    <span
                        class="text-[10px] font-black text-gray-400 tracking-[0.3em] uppercase mt-1"
                        >Restaurant Hub</span
                    >
                </Link>
            </div>

            <nav class="flex-1 px-4 space-y-3">
                <Link
                    :href="route('restaurant.dashboard')"
                    :class="
                        currentComponent === 'Restaurant/Dashboard'
                            ? 'bg-orange-500 text-white shadow-lg shadow-orange-200'
                            : 'text-gray-500 hover:bg-orange-50 hover:text-orange-600'
                    "
                    class="flex items-center gap-4 p-4 rounded-2xl font-black text-sm transition-all duration-300 group"
                >
                    <span class="text-xl group-hover:scale-110 transition"
                        >🏠</span
                    >
                    <span>TỔNG QUAN</span>
                </Link>

                <Link
                    :href="route('restaurant.products.index')"
                    :class="
                        currentComponent.startsWith('Restaurant/Products')
                            ? 'bg-orange-500 text-white shadow-lg shadow-orange-200'
                            : 'text-gray-500 hover:bg-orange-50 hover:text-orange-600'
                    "
                    class="flex items-center gap-4 p-4 rounded-2xl font-black text-sm transition-all duration-300 group"
                >
                    <span class="text-xl group-hover:scale-110 transition"
                        >🍔</span
                    >
                    <span>MÓN ĂN CỦA TÔI</span>
                </Link>

                <Link
                    :href="route('restaurant.orders.index')"
                    :class="
                        currentComponent === 'Restaurant/Orders/Index'
                            ? 'bg-orange-500 text-white shadow-lg shadow-orange-200'
                            : 'text-gray-500 hover:bg-orange-50 hover:text-orange-600'
                    "
                    class="flex items-center gap-4 p-4 rounded-2xl font-black text-sm transition-all duration-300 group"
                >
                    <span class="text-xl group-hover:scale-110 transition"
                        >📜</span
                    >
                    <span>ĐƠN HÀNG MỚI</span>
                    <div
                        v-if="$page.props.newOrdersCount > 0"
                        class="ml-auto bg-red-500 text-white text-[10px] w-5 h-5 flex items-center justify-center rounded-full animate-pulse"
                    >
                        {{ $page.props.newOrdersCount }}
                    </div>
                </Link>
            </nav>

            <div class="p-6 border-t border-gray-50 bg-gray-50/50">
                <div class="flex items-center gap-3 mb-6">
                    <div
                        class="w-10 h-10 bg-orange-500 rounded-xl flex items-center justify-center text-white font-black"
                    >
                        {{ $page.props.auth.user.name.charAt(0) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-black text-gray-800 truncate">
                            {{
                                $page.props.auth.user.restaurant_name ||
                                "Chủ quán"
                            }}
                        </p>
                        <p class="text-[10px] font-bold text-gray-400 truncate">
                            {{ $page.props.auth.user.email }}
                        </p>
                    </div>
                </div>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="w-full flex items-center justify-center gap-3 p-4 rounded-2xl font-black text-xs text-red-500 bg-white border border-red-50 hover:bg-red-500 hover:text-white transition-all duration-300 shadow-sm"
                >
                    🚪 ĐĂNG XUẤT
                </Link>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto">
            <div class="p-8 md:p-12">
                <slot />
            </div>
        </main>
    </div>
</template>

<style scoped>
* {
    font-family: "Inter", sans-serif;
}
/* Tùy chỉnh thanh cuộn cho sidebar nếu menu quá dài */
aside::-webkit-scrollbar {
    width: 0px;
}
</style>
