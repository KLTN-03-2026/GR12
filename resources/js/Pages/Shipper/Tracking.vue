<template>
    <ShipperLayout>
        <template #default>
            <!-- Map Section (Edge to Edge, Taller for Tracking) -->
            <section class="-mt-4 md:-mt-5 -mx-4 md:mx-0 mb-6 relative bg-white md:rounded-[2rem] overflow-hidden shadow-sm border-b md:border border-slate-200">
                <div class="relative">
                    <div id="shipper-map" class="h-[45vh] min-h-[350px] w-full bg-slate-200 z-0"></div>
                    
                    <!-- Floating Top Status overlay on map -->
                    <div class="absolute top-4 left-4 right-4 z-10">
                        <div class="bg-white/95 backdrop-blur-md rounded-2xl p-3 shadow-lg ring-1 ring-slate-200/50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="relative flex h-3 w-3 shrink-0">
                                  <span v-if="shipper.status !== 'offline'" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                  <span :class="[
                                    'relative inline-flex rounded-full h-3 w-3',
                                    shipper.status === 'offline' ? 'bg-slate-400' : 'bg-emerald-500'
                                  ]"></span>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-900">
                                        {{ shipper.status === "offline" ? "Đang nghỉ" : (shipper.status === "busy" ? "Đang giao hàng" : "Sẵn sàng nhận đơn") }}
                                    </p>
                                    <p class="text-[10px] text-slate-500 line-clamp-1">Khu vực Đà Nẵng</p>
                                </div>
                            </div>
                            <button
                                @click="showStatusDialog"
                                :disabled="isCheckoutDisabled"
                                :class="[
                                    'rounded-xl px-3 py-1.5 text-[11px] font-bold shadow-sm transition-all transform active:scale-95 whitespace-nowrap',
                                    shipper.status === 'offline'
                                        ? 'bg-emerald-500 text-white'
                                        : isCheckoutDisabled
                                          ? 'bg-slate-100 text-slate-400 cursor-not-allowed'
                                          : 'bg-rose-500 text-white',
                                ]"
                            >
                                {{ shipper.status === "offline" ? "Bắt đầu" : (isCheckoutDisabled ? "Đang bận" : "Nghỉ ngơi") }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Floating Map Overlay Bottom -->
                    <div class="absolute bottom-4 left-4 right-4 z-10 pointer-events-none">
                        <div class="grid grid-cols-3 gap-2 text-center text-xs">
                            <div class="rounded-xl bg-white/90 backdrop-blur-md p-2 shadow-lg ring-1 ring-slate-200/50">
                                <div class="text-lg font-black text-slate-900">{{ currentOrders.length }}</div>
                                <div class="text-[9px] font-bold text-slate-500 uppercase tracking-wider mt-0.5">Đang giao</div>
                            </div>
                            <div class="rounded-xl bg-amber-500/90 text-white backdrop-blur-md p-2 shadow-lg ring-1 ring-amber-500/50">
                                <div class="text-lg font-black">{{ assignedOrders.length }}</div>
                                <div class="text-[9px] font-bold text-amber-100 uppercase tracking-wider mt-0.5">Đơn gán</div>
                            </div>
                            <div class="rounded-xl bg-white/90 backdrop-blur-md p-2 shadow-lg ring-1 ring-slate-200/50">
                                <div class="text-lg font-black text-slate-900">{{ availableOrders.length }}</div>
                                <div class="text-[9px] font-bold text-slate-500 uppercase tracking-wider mt-0.5">Có sẵn</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Assigned Orders Section (Highlighted) -->
            <section
                v-if="assignedOrders.length > 0"
                class="mb-6 rounded-[2rem] bg-gradient-to-br from-amber-500 to-orange-500 p-1 shadow-lg shadow-orange-500/20"
            >
                <div class="rounded-[1.8rem] bg-white p-5">
                    <div class="mb-5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl animate-bounce">📦</span>
                            <div>
                                <h2 class="text-lg font-black text-amber-600 tracking-tight">
                                    Đơn hàng ưu tiên
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Hệ thống vừa gán cho bạn
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 text-amber-700 font-bold text-sm">
                            {{ assignedOrders.length }}
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div
                            v-for="order in assignedOrders"
                            :key="order.id"
                            class="rounded-[1.5rem] border-2 border-amber-200 bg-amber-50/50 p-4 relative overflow-hidden"
                        >
                            <div class="flex items-start justify-between gap-3 relative z-10">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-black text-slate-900">
                                            {{ order.order_code }}
                                        </p>
                                        <span class="px-2 py-0.5 rounded-md bg-amber-200 text-[10px] font-bold text-amber-800">Chờ xác nhận</span>
                                    </div>
                                    <p class="mt-2 text-xs text-slate-600 line-clamp-2">
                                        <span class="font-semibold text-slate-900">Giao đến:</span> {{ order.address }}
                                    </p>
                                    <p v-if="order.is_food_ready" class="mt-2 text-[10px] font-bold text-orange-600 bg-orange-100 px-2 py-1 rounded-full inline-flex items-center gap-1">
                                        <span>🍲</span> Món đã nấu xong
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-base font-black text-slate-900">
                                        {{ formatCurrency(order.total) }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 pt-4 border-t border-amber-200/60 flex gap-2 relative z-10">
                                <button
                                    @click="showAcceptDialog(order)"
                                    class="flex-1 rounded-2xl bg-amber-500 px-4 py-3.5 text-sm font-bold text-white transition hover:bg-amber-600 active:scale-[0.98] shadow-lg shadow-amber-500/20"
                                >
                                    Nhận đơn
                                </button>
                                <button
                                    @click="handleRejectOrder(order)"
                                    class="rounded-2xl border border-amber-300 bg-white px-4 py-3.5 text-sm font-bold text-slate-600 transition hover:bg-amber-50 active:scale-[0.98]"
                                >
                                    Từ chối
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Current Orders Section -->
            <section
                class="mb-6 rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-slate-200"
            >
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-900 tracking-tight">
                            Đang giao
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Đơn hàng hiện tại
                        </p>
                    </div>
                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 font-bold text-sm">
                        {{ currentOrders.length }}
                    </div>
                </div>

                <div class="space-y-4">
                    <div
                        v-if="currentOrders.length === 0"
                        class="rounded-[1.5rem] border-2 border-dashed border-slate-200 p-6 text-center"
                    >
                        <p class="text-sm font-semibold text-slate-500">Trống</p>
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            v-for="order in currentOrders"
                            :key="order.id"
                            class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-black text-slate-900">
                                            {{ order.order_code }}
                                        </p>
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[10px] font-bold text-slate-500">{{ getStatusText(order.status) }}</span>
                                    </div>
                                    <p class="mt-2 text-xs text-slate-600 line-clamp-2">
                                        <span class="font-semibold text-slate-900">Giao đến:</span> {{ order.address }}
                                    </p>
                                    <p v-if="order.is_food_ready" class="mt-2 text-[10px] font-bold text-orange-600 bg-orange-100 px-2 py-1 rounded-full inline-flex items-center gap-1">
                                        <span>🍲</span> Món đã sẵn sàng
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-base font-black text-indigo-600">
                                        {{ formatCurrency(order.total) }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 pt-4 border-t border-slate-100">
                                <Link
                                    :href="`/shipper/orders/${order.id}/detail`"
                                    class="flex items-center justify-center gap-2 w-full rounded-2xl bg-slate-900 px-4 py-3.5 text-sm font-bold text-white transition hover:bg-slate-800 active:scale-[0.98]"
                                >
                                    <span>Xem chi tiết & Cập nhật</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Available Orders Section -->
            <section
                class="rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-slate-200"
            >
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-900 tracking-tight">
                            Đơn có sẵn
                        </h2>
                    </div>
                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-sky-100 text-sky-700 font-bold text-sm">
                        {{ availableOrders.length }}
                    </div>
                </div>
                <div class="space-y-4">
                    <div
                        v-if="availableOrders.length === 0"
                        class="rounded-[1.5rem] border-2 border-dashed border-slate-200 p-6 text-center"
                    >
                        <p class="text-sm font-semibold text-slate-500">Trống</p>
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            v-for="order in availableOrders"
                            :key="order.id"
                            class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <p class="text-sm font-black text-slate-900">
                                        {{ order.order_code }}
                                    </p>
                                    <p class="mt-2 text-xs text-slate-600 line-clamp-2">
                                        <span class="font-semibold text-slate-900">Giao đến:</span> {{ order.address }}
                                    </p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-sky-600 bg-sky-50 px-2 py-1 rounded-md">
                                            <span>📍</span> ~{{ calculateDistance(order) }}km
                                        </span>
                                        <span v-if="order.is_food_ready" class="inline-flex items-center gap-1 text-[10px] font-bold text-orange-600 bg-orange-100 px-2 py-1 rounded-md">
                                            <span>🍲</span> Xong món
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-base font-black text-slate-900">
                                        {{ formatCurrency(order.total) }}
                                    </p>
                                </div>
                            </div>
                            <button
                                @click="showAcceptDialog(order)"
                                class="mt-4 w-full rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-indigo-700 active:scale-[0.98] shadow-md shadow-indigo-600/20"
                            >
                                Nhận đơn
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Bottom Sheet Modal for New Assignment -->
            <div
                v-if="showAssignmentModal"
                class="fixed inset-0 z-50 flex flex-col justify-end pointer-events-none"
            >
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm pointer-events-auto transition-opacity" @click="closeAssignmentModal"></div>
                <div class="w-full md:max-w-md mx-auto bg-white rounded-t-[2.5rem] p-6 pb-safe relative pointer-events-auto shadow-[0_-20px_50px_rgba(0,0,0,0.2)] animate-slide-up transform border-t border-white/50">
                    <div class="absolute top-3 left-1/2 -translate-x-1/2 w-12 h-1.5 bg-slate-200 rounded-full"></div>
                    
                    <div class="mt-4 text-center">
                        <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4 ring-8 ring-amber-50">
                            <span class="text-4xl">🔔</span>
                        </div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                            Đơn hàng mới!
                        </h2>
                        <p class="text-sm font-medium text-slate-500 mt-2">
                            Bạn vừa được gán đơn <span class="text-indigo-600 font-bold">#{{ selectedOrder && selectedOrder.order_code }}</span>
                        </p>
                        
                        <div v-if="selectedOrder" class="mt-4 flex items-center justify-center gap-4">
                            <div class="bg-indigo-50 px-4 py-2 rounded-xl text-center flex-1 border border-indigo-100 shadow-sm shadow-indigo-100/50">
                                <p class="text-[10px] uppercase font-bold text-indigo-400 tracking-wider mb-0.5">Thu nhập</p>
                                <p class="text-lg font-black text-indigo-700">
                                    +{{ formatCurrency((parseFloat(selectedOrder.shipping_fee) || 0) + (parseFloat(selectedOrder.shipper_tip) || 0)) }}
                                </p>
                            </div>
                            <div class="bg-emerald-50 px-4 py-2 rounded-xl text-center flex-1 border border-emerald-100 shadow-sm shadow-emerald-100/50">
                                <p class="text-[10px] uppercase font-bold text-emerald-500/70 tracking-wider mb-0.5">Quãng đường</p>
                                <p class="text-lg font-black text-emerald-600">
                                    {{ selectedOrder.distance ? selectedOrder.distance + ' km' : '---' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center shrink-0">
                                    <span class="text-xl">🏪</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Lấy hàng tại</p>
                                    <p class="text-sm font-bold text-slate-900 mt-0.5 line-clamp-1">{{ selectedOrder && selectedOrder.restaurant ? selectedOrder.restaurant.name : "Quán" }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                                    <span class="text-xl">👤</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Giao đến</p>
                                    <p class="text-sm font-bold text-slate-900 mt-0.5 line-clamp-1">{{ selectedOrder && selectedOrder.user ? selectedOrder.user.name : "Khách" }}</p>
                                    <p class="text-[11px] font-medium text-slate-500 mt-0.5 line-clamp-1">{{ selectedOrder && selectedOrder.address }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col gap-3">
                        <button
                            @click="handleAcceptOrder"
                            class="w-full rounded-2xl bg-indigo-600 py-4 text-sm font-black text-white shadow-xl shadow-indigo-600/20 active:scale-95 transition-all"
                        >
                            Đồng ý nhận đơn
                        </button>
                        <button
                            @click="closeAssignmentModal"
                            class="w-full rounded-2xl border-2 border-slate-100 bg-white py-4 text-sm font-bold text-slate-500 active:scale-95 transition-all hover:bg-slate-50"
                        >
                            Để sau
                        </button>
                    </div>
                </div>
            </div>

            <!-- Status Change Dialog -->
            <ConfirmDialog
                ref="statusDialog"
                :title="
                    shipper.status === 'offline'
                        ? 'Xác nhận Check-in'
                        : 'Xác nhận Check-out'
                "
                :message="
                    shipper.status === 'offline'
                        ? 'Bạn có chắc chắn muốn check-in để bắt đầu nhận đơn hàng? Hệ thống sẽ theo dõi vị trí của bạn.'
                        : 'Bạn có chắc chắn muốn check-out và ngừng nhận đơn hàng?'
                "
                :icon="shipper.status === 'offline' ? '🚀' : '⏸️'"
                :confirm-text="
                    shipper.status === 'offline' ? 'Check-in ngay' : 'Check-out'
                "
                cancel-text="Hủy"
                :loading-text="
                    shipper.status === 'offline'
                        ? 'Đang check-in...'
                        : 'Đang check-out...'
                "
                @confirm="handleStatusChange"
                @cancel="hideStatusDialog"
            />

            <!-- Accept Order Dialog -->
            <ConfirmDialog
                ref="acceptDialog"
                title="Xác nhận nhận đơn"
                :message="
                    selectedOrder
                        ? `Bạn có chắc chắn muốn nhận đơn ${selectedOrder.order_code}? Bạn sẽ cần giao hàng đến địa chỉ: ${selectedOrder.address}`
                        : ''
                "
                icon="📦"
                confirm-text="Nhận đơn ngay"
                cancel-text="Hủy"
                loading-text="Đang nhận đơn..."
                @confirm="handleAcceptOrder"
                @cancel="hideAcceptDialog"
            />
        </template>
    </ShipperLayout>
</template>

<style scoped>
@keyframes slide-up {
    from {
        transform: translateY(100%);
    }
    to {
        transform: translateY(0);
    }
}
.animate-slide-up {
    animation: slide-up 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom, 24px);
}
</style>

<script setup>
import { ref, watch, computed, onMounted } from "vue";
import { Link, router } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import ShipperLayout from "@/Layouts/ShipperLayout.vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

const toast = useToast();

const currentOrders = ref([]);
const availableOrders = ref([]);
const assignedOrders = ref([]);
const shipper = ref({});
const map = ref(null);
const shipperMarker = ref(null);
const radiusCircle = ref(null);
const markers = ref([]);
const polylines = ref([]);
const trackingWatcherId = ref(null);
const statusDialog = ref(null);
const acceptDialog = ref(null);
const selectedOrder = ref(null);
const newAssignedOrder = ref(null);
const showAssignmentModal = ref(false);
const dismissedAssignedOrderId = ref(null);
const hasPlayedAssignmentSound = ref(false);
const hasAutoCheckedIn = ref(window.shipperHasAutoCheckedIn === true);

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
        console.log("📊 Dashboard data:", {
            current_orders: data.current_orders,
            available_orders: data.available_orders,
            assigned_orders: data.assigned_orders,
            shipper: data.shipper,
        });
        currentOrders.value = data.current_orders;
        availableOrders.value = data.available_orders;
        assignedOrders.value = data.assigned_orders;
        shipper.value = data.shipper;
        refreshMap();
        handleNewAssignedOrders();
    } catch (error) {
        console.error("Error fetching dashboard:", error);
    }
};

const playNotificationSound = () => {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        const context = new AudioContext();
        const oscillator = context.createOscillator();
        const gain = context.createGain();

        oscillator.type = "sine";
        oscillator.frequency.setValueAtTime(880, context.currentTime);
        gain.gain.setValueAtTime(0.12, context.currentTime);

        oscillator.connect(gain);
        gain.connect(context.destination);

        oscillator.start();
        setTimeout(() => {
            oscillator.frequency.setValueAtTime(1200, context.currentTime);
        }, 120);
        setTimeout(() => {
            gain.gain.exponentialRampToValueAtTime(
                0.0001,
                context.currentTime + 0.2,
            );
        }, 220);
        setTimeout(() => {
            oscillator.stop();
            context.close();
        }, 400);
    } catch (error) {
        console.warn("Notification sound unavailable:", error);
    }
};

const handleNewAssignedOrders = () => {
    const assignedOrder = currentOrders.value.find(
        (order) => order.status === "assigned" || order.status === "confirmed",
    );

    if (!assignedOrder || dismissedAssignedOrderId.value === assignedOrder.id) {
        return;
    }

    if (
        newAssignedOrder.value &&
        newAssignedOrder.value.id === assignedOrder.id &&
        showAssignmentModal.value
    ) {
        return;
    }

    selectedOrder.value = assignedOrder;
    newAssignedOrder.value = assignedOrder;
    showAssignmentModal.value = true;
    if (!hasPlayedAssignmentSound.value) {
        playNotificationSound();
        hasPlayedAssignmentSound.value = true;
    }
};

const rejectAssignedOrder = async (orderId) => {
    if (!orderId) return;

    const response = await csrfFetch(`/api/shipper/orders/${orderId}/reject`, {
        method: "POST",
        headers: authHeaders,
    });

    if (!response.ok) {
        const error = await response.json();
        throw new Error(error?.error || "Không thể hoãn đơn.");
    }

    await fetchDashboard();
};

const closeAssignmentModal = async () => {
    if (selectedOrder.value?.status === "assigned") {
        try {
            await rejectAssignedOrder(selectedOrder.value.id);
            toast.success("Đã hoãn đơn. Hệ thống sẽ tìm shipper khác.");
        } catch (error) {
            console.error("Error rejecting assigned order:", error);
            toast.error(
                error.message || "❌ Không thể hoãn đơn. Vui lòng thử lại.",
            );
            return;
        }
    }

    if (selectedOrder.value) {
        dismissedAssignedOrderId.value = selectedOrder.value.id;
    }
    selectedOrder.value = null;
    showAssignmentModal.value = false;
    hasPlayedAssignmentSound.value = false;
};

const autoCheckIn = async () => {
    try {
        await checkIn();
        // Fetch lại để update status
        const response = await csrfFetch("/api/shipper/dashboard", {
            headers: authHeaders,
        });
        if (response.ok) {
            const data = await response.json();
            currentOrders.value = data.current_orders;
            availableOrders.value = data.available_orders;
            shipper.value = data.shipper;
            refreshMap();
            hasAutoCheckedIn.value = true;
            window.shipperHasAutoCheckedIn = true;
        }
    } catch (error) {
        console.error("Auto check-in error:", error);
    }
};

const initMap = () => {
    if (map.value) {
        return;
    }

    map.value = L.map("shipper-map", {
        zoomControl: false,
        attributionControl: false,
    }).setView([16.0544, 108.2022], 12);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 18,
        minZoom: 10,
        attribution:
            "&copy; <a href='https://www.openstreetmap.org/copyright'>OpenStreetMap</a> contributors",
    }).addTo(map.value);
};

const clearMapLayers = () => {
    if (!map.value) return;
    markers.value.forEach((layer) => {
        if (map.value.hasLayer(layer)) {
            map.value.removeLayer(layer);
        }
    });
    markers.value = [];

    polylines.value.forEach((layer) => {
        if (map.value.hasLayer(layer)) {
            map.value.removeLayer(layer);
        }
    });
    polylines.value = [];
};

const createCustomMarker = (coords, type, popupText) => {
    if (!map.value || !coords) return null;
    
    let htmlContent = '';
    
    if (type === 'restaurant') {
        htmlContent = `<div class="flex items-center justify-center w-8 h-8 bg-amber-500 rounded-full shadow-lg border-2 border-white text-sm shadow-amber-500/50">🏪</div>`;
    } else if (type === 'customer') {
        htmlContent = `<div class="flex items-center justify-center w-8 h-8 bg-blue-600 rounded-full shadow-lg border-2 border-white text-sm shadow-blue-500/50">🏠</div>`;
    }
    
    const icon = L.divIcon({
        html: htmlContent,
        className: "",
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });

    const marker = L.marker(coords, { icon }).addTo(map.value);
    if (popupText) {
        marker.bindPopup(popupText);
    }
    markers.value.push(marker);
    return marker;
};

const renderOrderMarkers = () => {
    if (!map.value) return;

    clearMapLayers();

    const allOrders = [...currentOrders.value, ...availableOrders.value];

    allOrders.forEach((order) => {
        const restaurantCoords =
            order.restaurant.latitude && order.restaurant.longitude
                ? [order.restaurant.latitude, order.restaurant.longitude]
                : null;
        const customerCoords =
            order.user.latitude && order.user.longitude
                ? [order.user.latitude, order.user.longitude]
                : null;

        if (restaurantCoords) {
            createCustomMarker(
                restaurantCoords,
                'restaurant',
                `<b>Quán:</b> ${order.restaurant.name}`
            );
        }

        if (customerCoords) {
            createCustomMarker(
                customerCoords,
                'customer',
                `<b>Khách:</b> ${order.user.name}`
            );
        }

        const shipperCoords =
            shipper.value.current_latitude && shipper.value.current_longitude
                ? [
                      shipper.value.current_latitude,
                      shipper.value.current_longitude,
                  ]
                : null;

        if (order.status === "shipping" && shipperCoords && restaurantCoords) {
            const line = L.polyline([shipperCoords, restaurantCoords], {
                color: "#f97316",
                weight: 3,
                opacity: 0.8,
                dashArray: "8, 6",
            }).addTo(map.value);
            polylines.value.push(line);
        } else if (
            order.status === "assigned" &&
            shipperCoords &&
            restaurantCoords
        ) {
            const line = L.polyline([shipperCoords, restaurantCoords], {
                color: "#f97316",
                weight: 3,
                opacity: 0.8,
                dashArray: "8, 6",
            }).addTo(map.value);
            polylines.value.push(line);
        } else if (
            order.status === "picked_up" &&
            restaurantCoords &&
            customerCoords
        ) {
            const line = L.polyline([restaurantCoords, customerCoords], {
                color: "#2563eb",
                weight: 3,
                opacity: 0.8,
                dashArray: "8, 6",
            }).addTo(map.value);
            polylines.value.push(line);
        } else if (
            order.status === "delivering" &&
            shipperCoords &&
            customerCoords
        ) {
            const line = L.polyline([shipperCoords, customerCoords], {
                color: "#10b981",
                weight: 3,
                opacity: 0.8,
                dashArray: "8, 6",
            }).addTo(map.value);
            polylines.value.push(line);
        } else if (
            restaurantCoords &&
            customerCoords &&
            order.status !== "confirmed"
        ) {
            const line = L.polyline([restaurantCoords, customerCoords], {
                color: "#2563eb",
                weight: 2,
                opacity: 0.6,
                dashArray: "6, 8",
            }).addTo(map.value);
            polylines.value.push(line);
        }
    });
};

const updateShipPosition = (lat, lng) => {
    if (!map.value || !lat || !lng) {
        return;
    }

    const coords = [lat, lng];
    
    const shipperIcon = L.divIcon({
        html: `<div class="relative w-10 h-10">
            <div class="absolute inset-0 bg-rose-500 rounded-full animate-ping opacity-20"></div>
            <div class="relative flex items-center justify-center w-10 h-10 bg-rose-500 rounded-full shadow-xl border-2 border-white text-xl shadow-rose-500/50 z-10">🛵</div>
        </div>`,
        className: "",
        iconSize: [40, 40],
        iconAnchor: [20, 20],
        popupAnchor: [0, -20]
    });

    if (shipperMarker.value) {
        shipperMarker.value.setLatLng(coords);
        // Note: setting icon directly is sometimes tricky in leaflet, we'll re-create if it's currently a circleMarker
        if (shipperMarker.value.setStyle) {
            map.value.removeLayer(shipperMarker.value);
            shipperMarker.value = L.marker(coords, { icon: shipperIcon }).addTo(map.value).bindPopup("<b>Vị trí Shipper</b>");
        }
    } else {
        shipperMarker.value = L.marker(coords, { icon: shipperIcon }).addTo(map.value);
        shipperMarker.value.bindPopup("<b>Vị trí Shipper</b>");
    }
    shipperMarker.value.openPopup();

    if (radiusCircle.value) {
        radiusCircle.value.setLatLng(coords);
    } else {
        radiusCircle.value = L.circle(coords, {
            radius: 5000,
            color: "#2563eb",
            fillColor: "#2563eb",
            fillOpacity: 0.08,
            weight: 2,
            dashArray: "6, 8",
        }).addTo(map.value);
    }
};

const refreshMap = () => {
    if (!map.value) return;

    renderOrderMarkers();
    updateShipPosition(
        shipper.value.current_latitude,
        shipper.value.current_longitude,
    );

    if (shipper.value.current_latitude && shipper.value.current_longitude) {
        map.value.flyTo(
            [shipper.value.current_latitude, shipper.value.current_longitude],
            13,
            {
                animate: true,
            },
        );
    }
};

const startTracking = () => {
    if (trackingWatcherId.value || !navigator.geolocation) {
        return;
    }

    trackingWatcherId.value = navigator.geolocation.watchPosition(
        async (position) => {
            const { latitude, longitude } = position.coords;
            shipper.value.current_latitude = latitude;
            shipper.value.current_longitude = longitude;
            updateShipPosition(latitude, longitude);

            try {
                await csrfFetch("/api/shipper/location", {
                    method: "POST",
                    headers: authHeaders,
                    body: JSON.stringify({ latitude, longitude }),
                });
                await fetchDashboard();
            } catch (error) {
                console.error("Error updating shipper location:", error);
            }
        },
        (error) => {
            console.error("Tracking error:", error);
        },
        {
            enableHighAccuracy: true,
            maximumAge: 3000,
            timeout: 10000,
        },
    );
};

watch(shipper, () => {
    refreshMap();
});

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
        console.error("Error updating status:", error);
        toast.error(error.message || "❌ Không thể thay đổi trạng thái.");
        statusDialog.value?.setLoading(false);
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
        throw new Error("Không thể check-out khi đang giao đơn.");
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

const showAcceptDialog = (order) => {
    selectedOrder.value = order;
    acceptDialog.value?.open();
};

const hideAcceptDialog = () => {
    acceptDialog.value?.close();
    selectedOrder.value = null;
};

const handleAcceptOrder = async () => {
    if (!selectedOrder.value) return;

    const orderId = selectedOrder.value.id;

    try {
        const response = await csrfFetch(
            `/api/shipper/orders/${orderId}/accept`,
            {
                method: "POST",
                headers: authHeaders,
            },
        );
        if (response.ok) {
            toast.success("🎉 Đã nhận đơn hàng thành công!");
            hideAcceptDialog();
            showAssignmentModal.value = false;
            await fetchDashboard();
            router.visit(`/shipper/orders/${orderId}/detail`);
        } else {
            const error = await response.json();
            toast.error(error?.error || "❌ Không thể nhận đơn hàng.");
            acceptDialog.value?.setLoading(false);
        }
    } catch (error) {
        console.error("Error accepting order:", error);
        toast.error("❌ Có lỗi xảy ra. Vui lòng thử lại.");
        acceptDialog.value?.setLoading(false);
    }
};

const handleRejectOrder = async (order) => {
    const orderId = order.id;

    try {
        const response = await csrfFetch(
            `/api/shipper/orders/${orderId}/reject`,
            {
                method: "POST",
                headers: authHeaders,
            },
        );
        if (response.ok) {
            toast.success("Đã từ chối đơn hàng. Hệ thống sẽ tìm shipper khác.");
            await fetchDashboard();
        } else {
            const error = await response.json();
            toast.error(error?.error || "❌ Không thể từ chối đơn hàng.");
        }
    } catch (error) {
        console.error("Error rejecting order:", error);
        toast.error("❌ Có lỗi xảy ra. Vui lòng thử lại.");
    }
};

const getStatusText = (status) => {
    const statuses = {
        pending: "⏳ Quán xem thông báo",
        processing: "👨‍🍳 Quán đang chuẩn bị",
        confirmed: "✅ Tìm kiếm tài xế",
        assigned: "🛵 Đã gán cho bạn",
        shipping: "🚗 Đi tới quán",
        picked_up: "📦 Đã lấy hàng",
        delivering: "🚚 Đang giao tới khách",
        completed: "🎉 Hoàn thành",
        cancelled: "❌ Đã hủy",
    };
    return statuses[status] || status;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(amount);
};

const calculateDistance = (order) => {
    if (
        order.distance_to_restaurant !== undefined &&
        order.distance_to_restaurant !== null
    ) {
        return order.distance_to_restaurant.toFixed(1);
    }
    return Math.floor(Math.random() * 5) + 1;
};

const isCheckoutDisabled = computed(() => shipper.value.status === "busy");

const statusText = computed(() => {
    if (shipper.value.status === "offline") {
        return "OFFLINE";
    }
    if (shipper.value.status === "busy") {
        return "Đang giao";
    }
    return "Sẵn sàng";
});

onMounted(async () => {
    initMap();
    startTracking();
    await fetchDashboard();

    // Auto check-in only on first mount within this session
    if (shipper.value.status === "offline" && !hasAutoCheckedIn.value) {
        await autoCheckIn();
    }
});
</script>
