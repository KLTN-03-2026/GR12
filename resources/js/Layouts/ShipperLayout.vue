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
    <div class="min-h-screen bg-slate-100 px-4 py-6">
        <div class="mx-auto w-full max-w-md">
            <div
                class="relative overflow-hidden rounded-[42px] border border-slate-200 bg-white shadow-[0_30px_70px_-20px_rgba(15,23,42,0.25)]"
            >
                <div
                    class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-cyan-400 via-sky-500 to-indigo-500"
                ></div>

                <header class="bg-slate-950 px-5 pt-6 pb-5 text-white">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <UserAvatar
                                :user="{ name: fullName }"
                                size="sm"
                                rounded="full"
                            />
                            <div>
                                <p
                                    class="text-base font-semibold leading-tight"
                                >
                                    {{ fullName }}
                                </p>
                                <p
                                    class="text-[11px] uppercase tracking-[0.24em] text-slate-400"
                                >
                                    Shipper
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold">FoodXpress</p>
                            <p class="text-xs text-slate-400">Mobile UI</p>
                        </div>
                    </div>

                    <div
                        class="mt-5 rounded-3xl bg-slate-900/90 p-4 ring-1 ring-white/10"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p
                                    class="text-xs uppercase tracking-[0.2em] text-slate-400"
                                >
                                    Trạng thái
                                </p>
                                <span
                                    class="mt-2 inline-flex rounded-full bg-emerald-500/15 px-3 py-1 text-sm font-semibold text-emerald-300"
                                >
                                    Online ready
                                </span>
                            </div>
                            <div
                                class="rounded-3xl bg-white/10 px-3 py-1 text-xs text-slate-200"
                            >
                                Đà Nẵng
                            </div>
                        </div>
                    </div>
                </header>

                <main class="p-4">
                    <slot />
                </main>

                <footer class="border-t border-slate-200 bg-slate-50 px-4 py-3">
                    <nav
                        class="grid grid-cols-5 gap-2 text-center text-[11px] uppercase tracking-[0.2em] text-slate-500"
                    >
                        <Link
                            href="/shipper/dashboard"
                            class="rounded-3xl bg-white px-3 py-2 shadow-sm transition hover:bg-slate-100"
                        >
                            <span class="block text-lg">🏠</span>
                            Trang chủ
                        </Link>
                        <Link
                            href="/shipper/tracking"
                            class="rounded-3xl bg-white px-3 py-2 shadow-sm transition hover:bg-slate-100"
                        >
                            <span class="block text-lg">🗺️</span>
                            Giao hàng
                        </Link>
                        <Link
                            href="/shipper/notifications"
                            class="rounded-3xl bg-white px-3 py-2 shadow-sm transition hover:bg-slate-100"
                        >
                            <span class="block text-lg">🔔</span>
                            Thông báo
                        </Link>
                        <Link
                            href="/shipper/menu/settings"
                            class="rounded-3xl bg-white px-3 py-2 shadow-sm transition hover:bg-slate-100"
                        >
                            <span class="block text-lg">⚙️</span>
                            Cài đặt
                        </Link>
                        <button
                            type="button"
                            @click="showLogoutDialog"
                            class="w-full rounded-3xl bg-white px-3 py-2 shadow-sm transition hover:bg-slate-100"
                        >
                            <span class="block text-lg">🚪</span>
                            Đăng xuất
                        </button>
                    </nav>
                </footer>
            </div>
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
