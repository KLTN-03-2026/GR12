<script setup>
import { Link, usePage, router } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import UserAvatar from "@/Components/UserAvatar.vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import { computed, ref } from "vue";

const page = usePage();
const toast = useToast();
const logoutDialog = ref(null);
const fullName = computed(() => page.props.auth?.user?.name || "Shipper");

const showLogoutDialog = (e) => {
    e.preventDefault();
    logoutDialog.value?.open();
};

const handleLogout = () => {
    // Close the dialog first, then perform logout
    logoutDialog.value?.close();

    // Reset auto-checkin state so next login starts fresh
    window.shipperHasAutoCheckedIn = false;

    // Perform logout - server will handle redirect
    router.post("/logout");
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 md:bg-slate-100 md:py-6 flex justify-center font-sans">
        <!-- Main Mobile Container -->
        <div class="w-full md:max-w-md bg-slate-50 md:bg-white md:rounded-[42px] md:shadow-[0_30px_70px_-20px_rgba(15,23,42,0.25)] md:border border-slate-200 relative flex flex-col overflow-hidden min-h-[100dvh] md:min-h-[850px]">
            
            <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-cyan-400 via-sky-500 to-indigo-500 z-50"></div>

            <!-- Header -->
            <header class="bg-slate-950 px-5 pt-8 pb-6 text-white shrink-0 rounded-b-[2rem] md:rounded-none z-10 shadow-[0_10px_30px_-10px_rgba(15,23,42,0.5)] relative">
                <!-- Background decor -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/20 rounded-full blur-3xl -mr-10 -mt-10 pointer-events-none"></div>

                <div class="flex items-center justify-between gap-3 relative z-10">
                    <div class="flex items-center gap-3">
                        <UserAvatar
                            :user="{ name: fullName }"
                            size="md"
                            rounded="full"
                            class="ring-2 ring-white/20"
                        />
                        <div>
                            <p class="text-base font-semibold leading-tight tracking-wide">
                                {{ fullName }}
                            </p>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-cyan-400 mt-1">
                                Tài xế đối tác
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Số dư ví</p>
                        <div class="font-black text-emerald-400 text-lg flex items-center gap-1 justify-end">
                            {{ new Intl.NumberFormat('vi-VN').format(page.props.auth?.user?.wallet_balance || 0) }}<span class="text-xs">đ</span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 rounded-[1.5rem] bg-white/5 p-4 ring-1 ring-white/10 backdrop-blur-md relative z-10">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400 font-medium">
                                Trạng thái
                            </p>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="relative flex h-3 w-3">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                </span>
                                <span class="text-sm font-semibold text-emerald-300 tracking-wide">
                                    Đang hoạt động
                                </span>
                            </div>
                        </div>
                        <div class="rounded-full bg-indigo-500/20 px-3 py-1.5 text-xs font-medium text-indigo-200 ring-1 ring-indigo-500/30">
                            📍 Đà Nẵng
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto overflow-x-hidden pb-24 relative custom-scrollbar bg-slate-50">
                <div class="p-4 md:p-5">
                    <slot />
                </div>
            </main>

            <!-- Bottom Navigation -->
            <footer class="absolute bottom-0 inset-x-0 z-40 bg-white/80 backdrop-blur-xl border-t border-slate-200/50 pb-safe shadow-[0_-10px_40px_rgba(0,0,0,0.05)]">
                <nav class="flex justify-around items-center px-2 py-2">
                    <Link
                        href="/shipper/dashboard"
                        :class="[page.url.startsWith('/shipper/dashboard') ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600', 'flex flex-col items-center flex-1 py-2 transition-colors relative']"
                    >
                        <div v-if="page.url.startsWith('/shipper/dashboard')" class="absolute top-0 w-8 h-1 bg-indigo-600 rounded-b-full shadow-[0_2px_10px_rgba(79,70,229,0.5)]"></div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mb-1"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        <span class="text-[10px] font-bold tracking-wide mt-0.5">Trang chủ</span>
                    </Link>
                    
                    <Link
                        href="/shipper/tracking"
                        :class="[page.url.startsWith('/shipper/tracking') ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600', 'flex flex-col items-center flex-1 py-2 transition-colors relative']"
                    >
                        <div v-if="page.url.startsWith('/shipper/tracking')" class="absolute top-0 w-8 h-1 bg-indigo-600 rounded-b-full shadow-[0_2px_10px_rgba(79,70,229,0.5)]"></div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mb-1"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/></svg>
                        <span class="text-[10px] font-bold tracking-wide mt-0.5">Bản đồ</span>
                    </Link>

                    <Link
                        href="/shipper/notifications"
                        :class="[page.url.startsWith('/shipper/notifications') ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600', 'flex flex-col items-center flex-1 py-2 transition-colors relative']"
                    >
                        <div v-if="page.url.startsWith('/shipper/notifications')" class="absolute top-0 w-8 h-1 bg-indigo-600 rounded-b-full shadow-[0_2px_10px_rgba(79,70,229,0.5)]"></div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mb-1"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        <span class="text-[10px] font-bold tracking-wide mt-0.5">Thông báo</span>
                    </Link>

                    <Link
                        href="/shipper/menu/settings"
                        :class="[page.url.startsWith('/shipper/menu/settings') ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600', 'flex flex-col items-center flex-1 py-2 transition-colors relative']"
                    >
                        <div v-if="page.url.startsWith('/shipper/menu/settings')" class="absolute top-0 w-8 h-1 bg-indigo-600 rounded-b-full shadow-[0_2px_10px_rgba(79,70,229,0.5)]"></div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mb-1"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        <span class="text-[10px] font-bold tracking-wide mt-0.5">Cài đặt</span>
                    </Link>

                    <button
                        type="button"
                        @click="showLogoutDialog"
                        class="text-rose-400 hover:text-rose-500 flex flex-col items-center flex-1 py-2 transition-colors relative"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mb-1"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        <span class="text-[10px] font-bold tracking-wide mt-0.5">Thoát</span>
                    </button>
                </nav>
            </footer>
        </div>

        <!-- Logout Confirmation Dialog -->
        <ConfirmDialog
            ref="logoutDialog"
            title="Xác nhận đăng xuất"
            message="Bạn có chắc chắn muốn đăng xuất khỏi hệ thống? Phiên làm việc của bạn sẽ kết thúc."
            icon="🚪"
            confirm-text="Đăng xuất"
            cancel-text="Hủy"
            loading-text="Đang đăng xuất..."
            @confirm="handleLogout"
            @cancel="logoutDialog?.close()"
        />
    </div>
</template>

<style scoped>
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom, 12px);
}
.custom-scrollbar::-webkit-scrollbar {
    width: 0px;
    background: transparent;
}
</style>
