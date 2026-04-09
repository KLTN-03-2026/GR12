<script setup>
import { Link } from "@inertiajs/vue3";
import ToastList from "@/Components/ToastList.vue"; // 1. Import hệ thống Toast
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex">
        <ToastList />

        <aside class="w-64 bg-slate-900 text-white hidden md:block shrink-0">
            <div class="p-6 text-2xl font-bold border-b border-slate-800">
                FoodXpress <span class="text-orange-500 text-sm">Admin</span>
            </div>
            <nav class="p-4 space-y-2">
                <Link
                    :href="route('admin.dashboard')"
                    class="block p-3 rounded-lg hover:bg-slate-800 transition"
                    :class="{
                        'bg-orange-600 shadow-lg shadow-orange-900/50':
                            $page.component === 'Admin/Dashboard',
                    }"
                >
                    📊 Tổng quan
                </Link>
                <Link
                    :href="route('admin.pending')"
                    class="block p-3 rounded-lg hover:bg-slate-800 transition"
                    :class="{
                        'bg-orange-600 shadow-lg shadow-orange-900/50':
                            $page.component === 'Admin/PendingUsers',
                    }"
                >
                    ⏳ Duyệt tài khoản
                </Link>
                <Link
                    href="#"
                    class="block p-3 rounded-lg hover:bg-slate-800 transition text-gray-500 cursor-not-allowed"
                >
                    🍔 Quản lý món ăn
                </Link>

                <div class="pt-10">
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="w-full text-left p-3 rounded-lg bg-red-500/10 text-red-500 hover:bg-red-600 hover:text-white transition flex items-center gap-2 font-bold"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        Đăng xuất
                    </Link>
                </div>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header
                class="bg-white shadow-sm p-4 flex justify-between items-center sticky top-0 z-10"
            >
                <h1
                    class="text-xl font-black text-gray-800 uppercase tracking-tight"
                >
                    Hệ thống Quản trị
                </h1>
                <div class="flex items-center gap-4">
                    <div class="flex flex-col text-right hidden sm:block">
                        <span
                            class="text-xs font-black text-orange-500 uppercase tracking-widest"
                            >Administrator</span
                        >
                        <span class="text-sm font-bold text-gray-700">{{
                            $page.props.auth.user.name
                        }}</span>
                    </div>
                    <div
                        class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-black border-2 border-orange-200"
                    >
                        {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                    </div>
                </div>
            </header>

            <main class="p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Đồng bộ font chữ cho toàn bộ hệ thống Admin */
* {
    font-family: "Inter", sans-serif;
}
</style>
