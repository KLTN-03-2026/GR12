<script setup>
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div class="min-h-screen bg-orange-50 flex">
        <aside class="w-64 bg-white shadow-xl flex flex-col">
            <div class="p-6 text-2xl font-black text-orange-500 italic">
                FoodXpress
                <span class="text-xs block text-gray-400">RESTAURANT</span>
            </div>

            <nav class="flex-1 px-4 space-y-2">
                <Link
                    :href="route('restaurant.dashboard')"
                    :class="{
                        'bg-orange-500 text-white':
                            $page.component === 'Restaurant/Dashboard',
                    }"
                    class="flex items-center gap-3 p-3 rounded-xl font-bold hover:bg-orange-100 transition-all"
                >
                    <span>🏠 Tổng quan</span>
                </Link>
                <Link
                    :href="route('restaurant.products.index')"
                    class="flex items-center gap-3 p-3 rounded-xl font-bold text-gray-600 hover:bg-orange-100"
                >
                    <span>🍔 Món ăn của tôi</span>
                </Link>
                <Link
                    href="#"
                    class="flex items-center gap-3 p-3 rounded-xl font-bold text-gray-600 hover:bg-orange-100"
                >
                    <span>📜 Đơn hàng</span>
                </Link>
            </nav>

            <div class="p-4 border-t">
                <div class="mb-4 px-2 text-sm font-medium text-gray-500">
                    Đang bán: {{ $page.props.auth.user.name }}
                </div>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="w-full flex items-center gap-3 p-3 rounded-xl font-bold text-red-500 hover:bg-red-50 transition-all"
                >
                    <span>🚪 Đăng xuất</span>
                </Link>
            </div>
        </aside>

        <main class="flex-1 p-8">
            <slot />
        </main>
    </div>
</template>
