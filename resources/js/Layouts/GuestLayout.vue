<script setup>
import { Link } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import ToastList from "@/Components/ToastList.vue"; // 1. Import component Toast
</script>

<template>
    <div class="min-h-screen bg-gray-50 text-gray-900 flex flex-col">
        <ToastList />

        <nav
            class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100 shadow-sm"
        >
            <div
                class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center"
            >
                <div class="flex items-center gap-2">
                    <Link :href="route('home')" class="flex items-center">
                        <span
                            class="text-2xl font-black text-orange-500 italic tracking-tighter"
                            >FoodXpress</span
                        >
                    </Link>
                </div>

                <div class="hidden md:flex items-center gap-8">
                    <Link
                        :href="route('home')"
                        :class="
                            route().current('home')
                                ? 'text-orange-500'
                                : 'text-gray-600'
                        "
                        class="font-bold hover:text-orange-500 transition-colors"
                    >
                        Khám phá
                    </Link>

                    <Link
                        :href="route('cart.index')"
                        class="relative group p-2"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="text-2xl group-hover:scale-110 transition-transform duration-200"
                                >🛒</span
                            >
                            <span
                                class="hidden lg:inline font-bold text-gray-600 group-hover:text-orange-500"
                                >Giỏ hàng</span
                            >
                        </div>
                        <div
                            v-if="$page.props.cartCount > 0"
                            class="absolute -top-1 -right-1 bg-orange-600 text-white text-[10px] font-black w-5 h-5 flex items-center justify-center rounded-full shadow-md animate-bounce"
                        >
                            {{ $page.props.cartCount }}
                        </div>
                    </Link>

                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="route('orders.index')"
                            class="font-bold text-gray-600 hover:text-orange-500 transition-colors"
                        >
                            Đơn hàng của tôi
                        </Link>

                        <div class="h-6 w-[1px] bg-gray-200"></div>

                        <div class="relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button
                                        class="flex items-center gap-2 font-bold text-gray-700 hover:text-orange-500 transition-all focus:outline-none"
                                    >
                                        <div
                                            class="w-8 h-8 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center text-xs font-black"
                                        >
                                            {{
                                                $page.props.auth.user.name
                                                    .charAt(0)
                                                    .toUpperCase()
                                            }}
                                        </div>
                                        <span>{{
                                            $page.props.auth.user.name
                                        }}</span>
                                        <svg
                                            class="w-4 h-4 fill-current"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">
                                        Hồ sơ cá nhân
                                    </DropdownLink>
                                    <div class="border-t border-gray-100"></div>
                                    <DropdownLink
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        class="text-red-500 font-bold"
                                    >
                                        Đăng xuất
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </template>

                    <template v-else>
                        <div class="flex items-center gap-4">
                            <Link
                                :href="route('login')"
                                class="font-bold text-gray-600 hover:text-orange-500 px-4 py-2 transition-colors"
                            >
                                Đăng nhập
                            </Link>
                            <Link
                                :href="route('register')"
                                class="bg-orange-500 text-white px-6 py-2.5 rounded-full font-bold hover:bg-orange-600 shadow-lg transition-all text-sm uppercase tracking-widest"
                            >
                                Đăng ký
                            </Link>
                        </div>
                    </template>
                </div>

                <div class="md:hidden flex items-center gap-4">
                    <Link :href="route('cart.index')" class="relative p-2">
                        <span class="text-2xl">🛒</span>
                        <div
                            v-if="$page.props.cartCount > 0"
                            class="absolute top-0 right-0 bg-orange-600 text-white text-[10px] w-4 h-4 flex items-center justify-center rounded-full"
                        >
                            {{ $page.props.cartCount }}
                        </div>
                    </Link>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto px-6 py-10 flex-grow animate-fade-in">
            <slot />
        </main>

        <footer class="bg-white border-t border-gray-100 py-10 mt-auto">
            <div
                class="max-w-7xl mx-auto px-6 text-center text-gray-400 text-xs font-bold uppercase tracking-widest"
            >
                &copy; 2026 FoodXpress - Dự án của Hoàng Anh • Đà Nẵng
            </div>
        </footer>
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
