<script setup>
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div class="min-h-screen bg-slate-100 flex">
        <aside class="w-64 bg-slate-900 text-white flex flex-col">
            <div class="p-6 text-2xl font-black text-blue-400">AdminPanel</div>

            <nav class="flex-1 px-4 space-y-1">
                <Link
                    :href="route('admin.dashboard')"
                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-800 transition-all"
                >
                    <span>📊 Thống kê hệ thống</span>
                </Link>
                <Link
                    :href="route('admin.pending')"
                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-800"
                >
                    <span>🔔 Duyệt quán ăn</span>
                    <span
                        class="bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full"
                        >New</span
                    >
                </Link>
                <Link
                    href="#"
                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-800 text-slate-400"
                >
                    <span>👥 Quản lý User</span>
                </Link>
            </nav>

            <div class="p-4 bg-slate-800">
                <div class="text-xs text-slate-400 mb-2 uppercase">
                    Quản trị viên
                </div>
                <div class="text-sm font-bold mb-3">
                    {{ $page.props.auth.user.name }}
                </div>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-sm text-red-400 hover:text-red-300 font-bold"
                >
                    Đăng xuất hệ thống
                </Link>
            </div>
        </aside>

        <main class="flex-1 p-10">
            <slot />
        </main>
    </div>
</template>
