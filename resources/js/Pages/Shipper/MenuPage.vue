<template>
    <ShipperLayout>
        <template #default>
            <div class="space-y-4">
                <section
                    class="rounded-[32px] bg-white p-6 shadow-sm ring-1 ring-slate-200"
                >
                    <div class="flex flex-col items-center gap-4 text-center">
                        <div
                            @click="goToTracking"
                            class="cursor-pointer transition hover:opacity-80"
                        >
                            <div
                                class="h-24 w-24 overflow-hidden rounded-full border-4 border-slate-100 bg-slate-100"
                            >
                                <img
                                    v-if="avatarUrl"
                                    :src="avatarUrl"
                                    alt="Avatar"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center bg-slate-200 text-3xl font-black text-slate-600"
                                >
                                    {{ initials }}
                                </div>
                            </div>
                        </div>
                        <div
                            @click="goToTracking"
                            class="cursor-pointer transition hover:opacity-80"
                        >
                            <h1 class="text-2xl font-black text-slate-900">
                                {{ fullName }}
                            </h1>
                            <p
                                class="text-sm uppercase tracking-[0.3em] text-slate-400"
                            >
                                Shipper
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-6 rounded-[28px] bg-slate-900/95 p-5 text-white"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p
                                    class="text-xs uppercase tracking-[0.22em] text-slate-400"
                                >
                                    Trạng thái hoạt động
                                </p>
                                <p class="mt-2 text-lg font-semibold">
                                    {{
                                        isOnline
                                            ? "Đang làm"
                                            : "Ngừng hoạt động"
                                    }}
                                </p>
                            </div>
                            <button
                                @click="showStatusDialog"
                                class="rounded-full bg-white px-5 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100"
                            >
                                {{
                                    isOnline ? "Tắt hoạt động" : "Bật hoạt động"
                                }}
                            </button>
                        </div>
                        <p class="mt-3 text-sm text-slate-300">
                            Khi bật hoạt động, hệ thống tự động nhận đơn và hiển
                            thị lộ trình.
                        </p>
                    </div>
                </section>

                <section
                    class="rounded-[32px] bg-white p-4 shadow-sm ring-1 ring-slate-200"
                >
                    <div class="grid gap-3">
                        <Link
                            href="/shipper/menu/destination"
                            class="flex items-center justify-between rounded-3xl border border-slate-200 bg-slate-50 px-4 py-4 text-slate-800 hover:border-slate-300 hover:bg-slate-100"
                        >
                            <div>
                                <p class="font-semibold">Điểm đến của tôi</p>
                                <p class="text-sm text-slate-500">
                                    Xem lộ trình và điểm giao hàng tiếp theo
                                </p>
                            </div>
                            <span class="text-xl">›</span>
                        </Link>
                        <Link
                            href="/shipper/notifications"
                            class="flex items-center justify-between rounded-3xl border border-slate-200 bg-slate-50 px-4 py-4 text-slate-800 hover:border-slate-300 hover:bg-slate-100"
                        >
                            <div>
                                <p class="font-semibold">Thông báo</p>
                                <p class="text-sm text-slate-500">
                                    Tin mới và cập nhật đơn hàng
                                </p>
                            </div>
                            <span class="text-xl">›</span>
                        </Link>
                        <Link
                            href="/shipper/menu/income"
                            class="flex items-center justify-between rounded-3xl border border-slate-200 bg-slate-50 px-4 py-4 text-slate-800 hover:border-slate-300 hover:bg-slate-100"
                        >
                            <div>
                                <p class="font-semibold">Thu nhập</p>
                                <p class="text-sm text-slate-500">
                                    Xem thu nhập giao hàng của bạn
                                </p>
                            </div>
                            <span class="text-xl">›</span>
                        </Link>
                        <Link
                            href="/shipper/menu/wallet"
                            class="flex items-center justify-between rounded-3xl border border-slate-200 bg-slate-50 px-4 py-4 text-slate-800 hover:border-slate-300 hover:bg-slate-100"
                        >
                            <div>
                                <p class="font-semibold">Ví</p>
                                <p class="text-sm text-slate-500">
                                    Quản lý số dư và thanh toán
                                </p>
                            </div>
                            <span class="text-xl">›</span>
                        </Link>
                        <Link
                            href="/shipper/menu/history"
                            class="flex items-center justify-between rounded-3xl border border-slate-200 bg-slate-50 px-4 py-4 text-slate-800 hover:border-slate-300 hover:bg-slate-100"
                        >
                            <div>
                                <p class="font-semibold">Lịch sử đơn hàng</p>
                                <p class="text-sm text-slate-500">
                                    Xem lại các đơn đã giao
                                </p>
                            </div>
                            <span class="text-xl">›</span>
                        </Link>
                        <Link
                            href="/shipper/menu/help"
                            class="flex items-center justify-between rounded-3xl border border-slate-200 bg-slate-50 px-4 py-4 text-slate-800 hover:border-slate-300 hover:bg-slate-100"
                        >
                            <div>
                                <p class="font-semibold">Trung tâm trợ giúp</p>
                                <p class="text-sm text-slate-500">
                                    Hướng dẫn và hỗ trợ
                                </p>
                            </div>
                            <span class="text-xl">›</span>
                        </Link>
                        <Link
                            href="/shipper/menu/settings"
                            class="flex items-center justify-between rounded-3xl border border-slate-200 bg-slate-50 px-4 py-4 text-slate-800 hover:border-slate-300 hover:bg-slate-100"
                        >
                            <div>
                                <p class="font-semibold">Cài đặt</p>
                                <p class="text-sm text-slate-500">
                                    Tùy chỉnh trải nghiệm vận hành
                                </p>
                            </div>
                            <span class="text-xl">›</span>
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
