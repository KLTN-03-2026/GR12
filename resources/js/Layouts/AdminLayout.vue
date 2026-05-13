<script setup>
import { Link } from "@inertiajs/vue3";
import ToastList from "@/Components/ToastList.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import { ref } from "vue";

const isSidebarOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-[#f8fafc] font-sans flex overflow-hidden">
        <ToastList />

        <!-- Mobile Overlay -->
        <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40 md:hidden"></div>

        <!-- Sidebar -->
        <aside 
            :class="[
                'w-72 bg-gradient-to-b from-gray-900 via-slate-800 to-gray-900 text-gray-300 flex flex-col shrink-0 fixed inset-y-0 left-0 z-50 md:static transform transition-transform duration-300 ease-in-out',
                isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
            ]"
        >
            <div class="p-6 flex items-center justify-center gap-3 border-b border-white/10 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-r from-orange-500/20 to-red-500/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center shadow-lg shadow-orange-500/30 text-white font-black text-xl z-10">
                    FX
                </div>
                <div class="font-black text-2xl tracking-tight text-white z-10 flex flex-col leading-tight">
                    FoodXpress
                    <span class="text-orange-500 text-[10px] tracking-widest uppercase -mt-1 font-bold">Admin Panel</span>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-6">
                <!-- Quản lý chung -->
                <div>
                    <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 px-3">Quản lý chung</div>
                    <div class="space-y-1">
                        <Link
                            :href="route('admin.dashboard')"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 font-medium"
                            :class="[
                                $page.component === 'Admin/Dashboard'
                                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-500/25'
                                    : 'hover:bg-white/5 hover:text-white'
                            ]"
                        >
                            <span class="text-xl">📊</span> Tổng quan
                        </Link>
                        
                        <Link
                            :href="route('admin.users.index')"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 font-medium"
                            :class="[
                                $page.component === 'Admin/Users/Index'
                                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-500/25'
                                    : 'hover:bg-white/5 hover:text-white'
                            ]"
                        >
                            <span class="text-xl">👥</span> Quản lý người dùng
                        </Link>

                        <Link
                            :href="route('admin.pending')"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 font-medium"
                            :class="[
                                $page.component === 'Admin/PendingUsers'
                                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-500/25'
                                    : 'hover:bg-white/5 hover:text-white'
                            ]"
                        >
                            <span class="text-xl">⏳</span> Duyệt đối tác
                        </Link>
                    </div>
                </div>

                <!-- Vận hành -->
                <div>
                    <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 px-3">Vận hành</div>
                    <div class="space-y-1">
                        <Link
                            :href="route('admin.live-map')"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 font-medium"
                            :class="[
                                $page.component === 'Admin/LiveMap'
                                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-500/25'
                                    : 'hover:bg-white/5 hover:text-white'
                            ]"
                        >
                            <span class="text-xl">🗺️</span> Live Tracking Map
                        </Link>
                        <Link
                            :href="route('admin.products.pending')"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 font-medium"
                            :class="[
                                $page.component === 'Admin/PendingProducts'
                                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-500/25'
                                    : 'hover:bg-white/5 hover:text-white'
                            ]"
                        >
                            <span class="text-xl">🍔</span> Duyệt món ăn
                        </Link>
                        
                        <Link
                            :href="route('admin.products.index')"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 font-medium"
                            :class="[
                                $page.component === 'Admin/Products/Index'
                                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-500/25'
                                    : 'hover:bg-white/5 hover:text-white'
                            ]"
                        >
                            <span class="text-xl">📋</span> Danh sách món ăn
                        </Link>

                        <Link
                            :href="route('admin.orders.index')"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 font-medium relative"
                            :class="[
                                $page.component === 'Admin/Orders/Index'
                                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-500/25'
                                    : 'hover:bg-white/5 hover:text-white'
                            ]"
                        >
                            <span class="text-xl">📦</span> Đơn hàng
                            <span v-if="$page.props.auth.user.unread_notifications_count" class="absolute right-3 top-1/2 -translate-y-1/2 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                        </Link>
                    </div>
                </div>

                <!-- Marketing & Tài chính -->
                <div>
                    <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 px-3">Marketing & Tài chính</div>
                    <div class="space-y-1">
                        <Link
                            :href="route('admin.vouchers.index')"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 font-medium"
                            :class="[
                                $page.component === 'Admin/Vouchers'
                                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-500/25'
                                    : 'hover:bg-white/5 hover:text-white'
                            ]"
                        >
                            <span class="text-xl">🎟️</span> Mã giảm giá
                        </Link>
                        
                        <Link
                            :href="route('admin.notifications.index')"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 font-medium"
                            :class="[
                                $page.component === 'Admin/PushNotifications'
                                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-500/25'
                                    : 'hover:bg-white/5 hover:text-white'
                            ]"
                        >
                            <span class="text-xl">📢</span> Thông báo hệ thống
                        </Link>

                        <Link
                            :href="route('admin.withdrawals.index')"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 font-medium"
                            :class="[
                                $page.component === 'Admin/Withdrawals'
                                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-500/25'
                                    : 'hover:bg-white/5 hover:text-white'
                            ]"
                        >
                            <span class="text-xl">💸</span> Duyệt rút tiền
                        </Link>
                        
                        <Link
                            :href="route('admin.wallets.index')"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 font-medium"
                            :class="[
                                $page.component === 'Admin/Wallets'
                                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-500/25'
                                    : 'hover:bg-white/5 hover:text-white'
                            ]"
                        >
                            <span class="text-xl">💳</span> Quản lý Ví điện tử
                        </Link>

                        <Link
                            :href="route('admin.revenue')"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 font-medium"
                            :class="[
                                $page.component === 'Admin/Revenue'
                                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-500/25'
                                    : 'hover:bg-white/5 hover:text-white'
                            ]"
                        >
                            <span class="text-xl">📈</span> Báo cáo doanh thu
                        </Link>
                    </div>
                </div>

                <!-- Hệ thống -->
                <div>
                    <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 px-3">Hệ thống</div>
                    <div class="space-y-1">
                        <Link
                            :href="route('admin.logs')"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 font-medium"
                            :class="[
                                $page.component === 'Admin/AuditLogs'
                                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-500/25'
                                    : 'hover:bg-white/5 hover:text-white'
                            ]"
                        >
                            <span class="text-xl">📜</span> Nhật ký Hệ thống
                        </Link>
                        <Link
                            :href="route('admin.settings.index')"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 font-medium"
                            :class="[
                                $page.component === 'Admin/Settings'
                                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-500/25'
                                    : 'hover:bg-white/5 hover:text-white'
                            ]"
                        >
                            <span class="text-xl">⚙️</span> Cấu hình Hệ thống
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Footer Sidebar -->
            <div class="p-4 border-t border-white/10">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white transition-all duration-300 font-bold"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Đăng xuất
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
            <!-- Header -->
            <header class="bg-white/80 backdrop-blur-lg shadow-sm border-b border-gray-100 z-30 sticky top-0">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center gap-4">
                        <button @click="isSidebarOpen = true" class="p-2 -ml-2 rounded-lg text-gray-500 hover:bg-gray-100 md:hidden">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <h1 class="text-xl font-black text-gray-800 tracking-tight hidden sm:block">
                            Hệ thống Quản trị FoodXpress
                        </h1>
                    </div>
                    
                    <div class="flex items-center gap-5 relative group cursor-pointer">
                        <div class="flex flex-col text-right">
                            <span class="text-[10px] font-black text-orange-500 uppercase tracking-widest leading-none mb-1">Administrator</span>
                            <span class="text-sm font-bold text-gray-800 leading-none">{{ $page.props.auth.user.name }}</span>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-0 bg-orange-500 rounded-full blur opacity-20 group-hover:opacity-40 transition-opacity"></div>
                            <UserAvatar
                                :user="$page.props.auth.user"
                                size="md"
                                rounded="full"
                                showBorder
                                class="relative z-10 ring-2 ring-white"
                            />
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Scrollable Area -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-[#f8fafc] custom-scrollbar">
                <div class="max-w-7xl mx-auto">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Scrollbar tuỳ chỉnh cho đẹp */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

