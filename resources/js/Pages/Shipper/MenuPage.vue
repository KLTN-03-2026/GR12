<template>
    <ShipperLayout>
        <template #default>
            <div class="space-y-6 pb-24">
                <!-- Profile & Status Section -->
                <section
                    class="rounded-[2rem] bg-gradient-to-br from-slate-900 to-slate-800 p-6 text-white shadow-[0_10px_30px_-10px_rgba(15,23,42,0.6)] relative overflow-hidden ring-1 ring-white/10"
                >
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
                    
                    <div class="flex items-center gap-5 relative z-10">
                        <div
                            @click="goToTracking"
                            class="cursor-pointer transition hover:scale-105 active:scale-95 shrink-0"
                        >
                            <div
                                class="h-20 w-20 overflow-hidden rounded-full ring-4 ring-white/10 bg-slate-800 shadow-xl"
                            >
                                <img
                                    v-if="avatarUrl"
                                    :src="avatarUrl"
                                    alt="Avatar"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 text-2xl font-black text-white"
                                >
                                    {{ initials }}
                                </div>
                            </div>
                        </div>
                        <div class="flex-1" @click="goToTracking">
                            <h1 class="text-2xl font-black tracking-tight text-white line-clamp-1 cursor-pointer">
                                {{ fullName }}
                            </h1>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md bg-indigo-500/20 text-[10px] font-bold uppercase tracking-widest text-indigo-300 ring-1 ring-indigo-500/50">
                                    Đối tác
                                </span>
                                <span class="text-xs text-slate-400">Shipper Pro</span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Toggle -->
                    <div
                        class="mt-6 rounded-2xl bg-white/5 p-4 border border-white/10 relative z-10 flex items-center justify-between gap-4 backdrop-blur-md"
                    >
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-2 h-2 rounded-full" :class="isOnline ? 'bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)]' : 'bg-rose-500'"></div>
                                <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">
                                    Trạng thái
                                </p>
                            </div>
                            <p class="text-base font-black text-white">
                                {{ isOnline ? "Sẵn sàng nhận đơn" : "Đang nghỉ ngơi" }}
                            </p>
                        </div>
                        <button
                            @click="showStatusDialog"
                            class="rounded-xl px-5 py-2.5 text-sm font-bold transition-all active:scale-95 shrink-0"
                            :class="isOnline ? 'bg-rose-500/20 text-rose-300 hover:bg-rose-500/30 ring-1 ring-rose-500/50' : 'bg-emerald-500/20 text-emerald-300 hover:bg-emerald-500/30 ring-1 ring-emerald-500/50'"
                        >
                            {{ isOnline ? "Tắt hoạt động" : "Bật hoạt động" }}
                        </button>
                    </div>
                </section>

                <!-- Menu Items Section -->
                <section>
                    <h2 class="text-sm font-black uppercase tracking-widest text-slate-400 px-2 mb-3">Tính năng</h2>
                    <div class="bg-white rounded-[2rem] shadow-sm ring-1 ring-slate-100 overflow-hidden divide-y divide-slate-50">
                        <Link
                            href="/shipper/menu/destination"
                            class="flex items-center justify-between p-5 hover:bg-slate-50 active:bg-slate-100 transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-[1.25rem] bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">📍</div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">Điểm đến của tôi</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Lộ trình & điểm giao</p>
                                </div>
                            </div>
                            <span class="text-slate-300 text-xl font-light">›</span>
                        </Link>
                        
                        <Link
                            href="/shipper/notifications"
                            class="flex items-center justify-between p-5 hover:bg-slate-50 active:bg-slate-100 transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-[1.25rem] bg-orange-50 text-orange-600 flex items-center justify-center text-xl shrink-0">🔔</div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">Thông báo</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Tin tức hệ thống</p>
                                </div>
                            </div>
                            <span class="text-slate-300 text-xl font-light">›</span>
                        </Link>
                        
                        <Link
                            href="/shipper/income"
                            class="flex items-center justify-between p-5 hover:bg-slate-50 active:bg-slate-100 transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-[1.25rem] bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">💰</div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">Thu nhập</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Thống kê doanh thu</p>
                                </div>
                            </div>
                            <span class="text-slate-300 text-xl font-light">›</span>
                        </Link>
                        
                        <Link
                            href="/shipper/wallet"
                            class="flex items-center justify-between p-5 hover:bg-slate-50 active:bg-slate-100 transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-[1.25rem] bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">💳</div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">Ví thanh toán</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Số dư & Rút tiền</p>
                                </div>
                            </div>
                            <span class="text-slate-300 text-xl font-light">›</span>
                        </Link>
                        
                        <Link
                            href="/shipper/history"
                            class="flex items-center justify-between p-5 hover:bg-slate-50 active:bg-slate-100 transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-[1.25rem] bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">📋</div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">Lịch sử đơn hàng</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Các đơn đã giao</p>
                                </div>
                            </div>
                            <span class="text-slate-300 text-xl font-light">›</span>
                        </Link>
                    </div>
                </section>

                <section>
                    <h2 class="text-sm font-black uppercase tracking-widest text-slate-400 px-2 mb-3">Hỗ trợ & Cài đặt</h2>
                    <div class="bg-white rounded-[2rem] shadow-sm ring-1 ring-slate-100 overflow-hidden divide-y divide-slate-50">
                        <Link
                            href="/shipper/menu/help"
                            class="flex items-center justify-between p-5 hover:bg-slate-50 active:bg-slate-100 transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-[1.25rem] bg-sky-50 text-sky-600 flex items-center justify-center text-xl shrink-0">🎧</div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">Trung tâm trợ giúp</p>
                                </div>
                            </div>
                            <span class="text-slate-300 text-xl font-light">›</span>
                        </Link>
                        
                        <Link
                            href="/shipper/menu/settings"
                            class="flex items-center justify-between p-5 hover:bg-slate-50 active:bg-slate-100 transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-[1.25rem] bg-slate-50 text-slate-600 flex items-center justify-center text-xl shrink-0">⚙️</div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">Cài đặt ứng dụng</p>
                                </div>
                            </div>
                            <span class="text-slate-300 text-xl font-light">›</span>
                        </Link>
                    </div>
                </section>
            </div>

            <!-- Confirm Dialog -->
            <ConfirmDialog
                ref="statusDialog"
                :title="
                    isOnline
                        ? 'Xác nhận tắt hoạt động'
                        : 'Xác nhận bật hoạt động'
                "
                :message="
                    isOnline
                        ? 'Bạn có chắc chắn muốn tắt hoạt động? Bạn sẽ không nhận được đơn hàng mới.'
                        : 'Bạn có chắc chắn muốn bật hoạt động? Hệ thống sẽ bắt đầu gán đơn hàng cho bạn.'
                "
                :icon="isOnline ? '⏸️' : '▶️'"
                :confirm-text="isOnline ? 'Tắt hoạt động' : 'Bật hoạt động'"
                cancel-text="Hủy"
                :loading-text="
                    isOnline ? 'Đang tắt hoạt động...' : 'Đang bật hoạt động...'
                "
                @confirm="handleStatusChange"
                @cancel="hideStatusDialog"
            />
        </template>
    </ShipperLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import ShipperLayout from "@/Layouts/ShipperLayout.vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";

const page = usePage();
const toast = useToast();
const shipper = ref({});
const statusDialog = ref(null);
const hasAutoCheckedIn = ref(window.shipperHasAutoCheckedIn === true);

const fullName = computed(() => page.props.auth?.user?.name || "Shipper");
const avatarUrl = computed(() => {
    const user = page.props.auth?.user;
    return user?.profile_photo_path
        ? `/storage/${user.profile_photo_path}`
        : "";
});

const initials = computed(() =>
    fullName.value
        .split(" ")
        .map((word) => word[0])
        .join("")
        .slice(0, 2)
        .toUpperCase(),
);
const isOnline = computed(() => shipper.value.status !== "offline");

const authHeaders = {
    Accept: "application/json",
    "Content-Type": "application/json",
};
const csrfFetch = window.csrfFetch;

const fetchDashboard = async () => {
    try {
        const response = await csrfFetch("/api/shipper/dashboard", {
            headers: authHeaders,
        });
        if (!response.ok) {
            throw new Error("Failed to load dashboard");
        }
        const data = await response.json();
        shipper.value = data.shipper;
    } catch (error) {
        console.error("Error fetching dashboard:", error);
    }
};

const autoCheckIn = async () => {
    try {
        await checkIn();
        await fetchDashboardData();
        hasAutoCheckedIn.value = true;
        window.shipperHasAutoCheckedIn = true;
    } catch (error) {
        console.error("Auto check-in error:", error);
    }
};

const fetchDashboardData = async () => {
    try {
        const response = await csrfFetch("/api/shipper/dashboard", {
            headers: authHeaders,
        });
        if (!response.ok) {
            throw new Error("Failed to load dashboard");
        }
        const data = await response.json();
        shipper.value = data.shipper;
    } catch (error) {
        console.error("Error fetching dashboard:", error);
    }
};

const checkIn = async () => {
    const response = await csrfFetch("/api/shipper/check-in", {
        method: "POST",
        headers: authHeaders,
    });
    if (!response.ok) {
        const error = await response.json();
        throw new Error(error?.error || "Unable to check in");
    }
};

const checkOut = async () => {
    if (shipper.value.status === "busy") {
        throw new Error("Không thể tắt hoạt động khi đang giao đơn.");
    }
    const response = await csrfFetch("/api/shipper/check-out", {
        method: "POST",
        headers: authHeaders,
    });
    if (!response.ok) {
        const error = await response.json();
        throw new Error(error?.error || "Unable to check out");
    }
};

const showStatusDialog = () => {
    statusDialog.value?.open();
};

const hideStatusDialog = () => {
    statusDialog.value?.close();
};

const handleStatusChange = async () => {
    try {
        if (shipper.value.status === "offline") {
            await checkIn();
            toast.success("✅ Check-in thành công!");
        } else if (shipper.value.status === "busy") {
            toast.warning(
                "⚠️ Bạn đang có đơn hàng đang giao. Vui lòng hoàn thành đơn hàng trước khi check-out.",
            );
            statusDialog.value?.setLoading(false);
            statusDialog.value?.close();
            return;
        } else {
            await checkOut();
            toast.success("👋 Check-out thành công!");
        }
        statusDialog.value?.setLoading(false);
        statusDialog.value?.close();
        await fetchDashboard();
    } catch (error) {
        toast.error(error.message || "❌ Không thể thay đổi trạng thái.");
        console.error("Error updating status:", error);
        statusDialog.value?.setLoading(false);
    }
};

const goToTracking = () => {
    router.visit("/shipper/tracking");
};

onMounted(async () => {
    await fetchDashboard();

    // Auto check-in only on first mount within this session
    if (shipper.value.status === "offline" && !hasAutoCheckedIn.value) {
        await autoCheckIn();
    }
});
</script>
