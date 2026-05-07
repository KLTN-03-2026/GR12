<script setup>
import { Link } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import ToastList from "@/Components/ToastList.vue"; // 1. Import component Toast
import UserAvatar from "@/Components/UserAvatar.vue";
import AiChatWidget from "@/Components/AiChatWidget.vue";
</script>

<template>
    <div
        class="min-h-screen bg-gradient-to-br from-neutral-50 to-neutral-100 text-neutral-900 flex flex-col"
    >
        <ToastList />

        <nav
            class="sticky top-0 z-50 bg-white/95 backdrop-blur-xl border-b border-neutral-200/50 shadow-soft"
        >
            <div
                class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center"
            >
                <div class="flex items-center gap-3">
                    <Link :href="route('home')" class="flex items-center group">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-brand-500 to-brand-600 rounded-2xl flex items-center justify-center shadow-glow group-hover:shadow-glow-lg transition-all duration-300"
                        >
                            <span class="text-white font-black text-lg">F</span>
                        </div>
                        <span
                            class="ml-3 text-2xl font-black text-gradient italic tracking-tighter group-hover:scale-105 transition-transform duration-300"
                            >FoodXpress</span
                        >
                    </Link>
                </div>

                <div class="hidden md:flex items-center gap-8">
                    <Link
                        :href="route('home')"
                        :class="
                            route().current('home')
                                ? 'text-brand-600 font-bold'
                                : 'text-neutral-600 hover:text-brand-500'
                        "
                        class="font-semibold hover:scale-105 transition-all duration-300 relative"
                    >
                        Khám phá
                        <span
                            v-if="route().current('home')"
                            class="absolute -bottom-1 left-0 w-full h-0.5 bg-gradient-to-r from-brand-500 to-brand-600 rounded-full"
                        ></span>
                    </Link>

                    <Link
                        :href="route('cart.index')"
                        class="relative group p-3 rounded-2xl hover:bg-brand-50 transition-all duration-300"
                    >
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <span
                                    class="text-2xl group-hover:scale-110 transition-transform duration-300"
                                    >🛒</span
                                >
                                <div
                                    v-if="$page.props.cartCount > 0"
                                    class="absolute -top-2 -right-2 bg-gradient-to-r from-red-500 to-red-600 text-white text-xs font-black w-6 h-6 flex items-center justify-center rounded-full shadow-lg animate-pulse"
                                >
                                    {{ $page.props.cartCount }}
                                </div>
                            </div>
                            <span
                                class="hidden lg:inline font-semibold text-neutral-700 group-hover:text-brand-600 transition-colors"
                                >Giỏ hàng</span
                            >
                        </div>
                    </Link>

                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="route('orders.index')"
                            class="font-semibold text-neutral-600 hover:text-brand-500 hover:scale-105 transition-all duration-300"
                        >
                            Đơn hàng
                        </Link>

                        <Link
                            :href="route('my.notifications')"
                            class="font-semibold text-neutral-600 hover:text-brand-500 hover:scale-105 transition-all duration-300"
                        >
                            Thông báo
                        </Link>

                        <div class="h-6 w-[1px] bg-neutral-300"></div>

                        <div class="relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button
                                        class="flex items-center gap-3 font-semibold text-neutral-700 hover:text-brand-600 transition-all focus:outline-none p-2 rounded-xl hover:bg-brand-50"
                                    >
                                        <UserAvatar
                                            :user="$page.props.auth.user"
                                            size="md"
                                            rounded="lg"
                                        />
                                        <span class="hidden lg:inline">{{
                                            $page.props.auth.user.name
                                        }}</span>
                                        <svg
                                            class="w-4 h-4 fill-current transition-transform group-hover:rotate-180"
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
                                    <div
                                        class="border-t border-neutral-200"
                                    ></div>
                                    <DropdownLink
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        class="text-red-500 font-semibold hover:bg-red-50"
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
                                class="font-semibold text-neutral-600 hover:text-brand-500 px-4 py-2 rounded-xl hover:bg-brand-50 transition-all duration-300"
                            >
                                Đăng nhập
                            </Link>
                            <Link
                                :href="route('register')"
                                class="btn-primary text-sm uppercase tracking-widest"
                            >
                                Đăng ký
                            </Link>
                        </div>
                    </template>
                </div>

                <div class="md:hidden flex items-center gap-4">
                    <Link
                        :href="route('cart.index')"
                        class="relative p-3 rounded-xl hover:bg-brand-50 transition-all duration-300"
                    >
                        <span class="text-2xl">🛒</span>
                        <div
                            v-if="$page.props.cartCount > 0"
                            class="absolute top-1 right-1 bg-gradient-to-r from-red-500 to-red-600 text-white text-xs font-black w-5 h-5 flex items-center justify-center rounded-full shadow-lg animate-pulse"
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

        <footer
            class="bg-white border-t border-neutral-200/50 py-12 mt-auto shadow-soft"
        >
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-brand-500 to-brand-600 rounded-2xl flex items-center justify-center shadow-glow"
                            >
                                <span class="text-white font-black text-lg"
                                    >F</span
                                >
                            </div>
                            <span class="text-xl font-black text-gradient"
                                >FoodXpress</span
                            >
                        </div>
                        <p class="text-neutral-600 text-sm leading-relaxed">
                            Giao thức ăn nóng hổi tận cửa trong 20 phút. Trải
                            nghiệm ẩm thực tuyệt vời tại Đà Nẵng.
                        </p>
                    </div>
                    <div>
                        <h4 class="font-bold text-neutral-800 mb-4">
                            Khám phá
                        </h4>
                        <ul class="space-y-2 text-sm text-neutral-600">
                            <li>
                                <Link
                                    href="/"
                                    class="hover:text-brand-500 transition-colors"
                                    >Trang chủ</Link
                                >
                            </li>
                            <li>
                                <Link
                                    href="/cart"
                                    class="hover:text-brand-500 transition-colors"
                                    >Giỏ hàng</Link
                                >
                            </li>
                            <li>
                                <Link
                                    href="/orders"
                                    class="hover:text-brand-500 transition-colors"
                                    >Đơn hàng</Link
                                >
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold text-neutral-800 mb-4">Hỗ trợ</h4>
                        <ul class="space-y-2 text-sm text-neutral-600">
                            <li>
                                <a
                                    href="#"
                                    class="hover:text-brand-500 transition-colors"
                                    >Liên hệ</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="hover:text-brand-500 transition-colors"
                                    >FAQ</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="hover:text-brand-500 transition-colors"
                                    >Điều khoản</a
                                >
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold text-neutral-800 mb-4">
                            Theo dõi
                        </h4>
                        <div class="flex gap-4">
                            <a
                                href="#"
                                class="w-10 h-10 bg-brand-100 text-brand-600 rounded-xl flex items-center justify-center hover:bg-brand-200 transition-colors"
                            >
                                <span class="text-lg">📘</span>
                            </a>
                            <a
                                href="#"
                                class="w-10 h-10 bg-brand-100 text-brand-600 rounded-xl flex items-center justify-center hover:bg-brand-200 transition-colors"
                            >
                                <span class="text-lg">📷</span>
                            </a>
                            <a
                                href="#"
                                class="w-10 h-10 bg-brand-100 text-brand-600 rounded-xl flex items-center justify-center hover:bg-brand-200 transition-colors"
                            >
                                <span class="text-lg">🐦</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="border-t border-neutral-200 pt-8 text-center">
                    <p class="text-neutral-500 text-sm font-medium">
                        &copy; 2026 FoodXpress - Dự án của Hoàng Anh • Đà Nẵng
                    </p>
                </div>
            </div>
        </footer>
        
        <!-- Khung chat AI -->
        <AiChatWidget />
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.6s ease-out;
}
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
