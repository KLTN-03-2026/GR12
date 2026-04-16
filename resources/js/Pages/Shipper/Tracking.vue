<template>
    <ShipperLayout>
        <template #default>
            <section
                class="mb-5 rounded-[28px] bg-slate-900/95 p-3 text-white shadow-sm ring-1 ring-white/10"
            >
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p
                            class="text-xs uppercase tracking-[0.2em] text-slate-400"
                        >
                            Trạng thái
                        </p>
                        <p class="mt-1 text-lg font-semibold">
                            {{
                                shipper.status === "offline"
                                    ? "Offline"
                                    : shipper.status === "busy"
                                      ? "Đang giao"
                                      : "Sẵn sàng"
                            }}
                        </p>
                    </div>
                    <button
                        @click="showStatusDialog"
                        :disabled="isCheckoutDisabled"
                        :class="[
                            'rounded-3xl px-3 py-2 text-xs font-semibold shadow-lg shadow-slate-900/10 transition',
                            shipper.status === 'offline'
                                ? 'bg-white text-slate-900 hover:bg-slate-100'
                                : isCheckoutDisabled
                                  ? 'bg-slate-300 text-slate-500 cursor-not-allowed'
                                  : 'bg-white text-slate-900 hover:bg-slate-100',
                        ]"
                    >
                        {{
                            shipper.status === "offline"
                                ? "Check-in"
                                : isCheckoutDisabled
                                  ? "Check-out (đang giao)"
                                  : "Check-out"
                        }}
                    </button>
                </div>
                <div
                    v-if="isCheckoutDisabled"
                    class="mt-2 rounded-3xl bg-amber-50 p-2 text-xs text-amber-700"
                >
                    Không thể check-out khi đang có đơn. Hoàn thành đơn hàng
                    trước khi nghỉ.
                </div>
                <div class="mt-3 grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="rounded-3xl bg-slate-800/80 p-2">
                        <div class="text-2xl font-bold">
                            {{ currentOrders.length }}
                        </div>
                        <div class="text-slate-400 text-xs mt-1">Đang giao</div>
                    </div>
                    <div class="rounded-3xl bg-slate-800/80 p-2">
                        <div class="text-2xl font-bold">
                            {{ availableOrders.length }}
                        </div>
                        <div class="text-slate-400 text-xs mt-1">Đơn có sẵn</div>
                    </div>
                    <div class="rounded-3xl bg-slate-800/80 p-2">
                        <div class="text-2xl font-bold">{{ statusText }}</div>
                        <div class="text-slate-400 text-xs mt-1">Bạn</div>
                    </div>
                </div>
            </section>

            <section
                class="mb-5 overflow-hidden rounded-[28px] bg-white shadow-sm ring-1 ring-slate-200"
            >
                <div id="shipper-map" class="h-64 w-full bg-slate-200"></div>
                <div class="space-y-3 p-4">
                    <div
                        class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-600"
                    >
                        <p class="font-semibold text-slate-900">
                            Bản đồ vận chuyển của bạn
                        </p>
                        <p class="mt-2">
                            Bản đồ sẽ hiển thị vị trí hiện tại của shipper và
                            giúp bạn định hướng nhanh qua khu vực Đà Nẵng.
                        </p>
                    </div>
                    <div
                        class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-600"
                    >
                        <p class="font-semibold text-slate-900">
                            Bán kính hoạt động
                        </p>
                        <p class="mt-2">
                            Bản đồ luôn giữ vùng di chuyển của bạn trong bán
                            kính 5km.
                        </p>
                        <p class="mt-2 text-sm text-slate-500">
                            Hệ thống tự động tìm đơn hàng có quán trong 2km từ
                            vị trí hiện tại.
                        </p>
                    </div>
                </div>
            </section>

            <section
                class="mb-5 rounded-[28px] bg-white p-4 shadow-sm ring-1 ring-slate-200"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">
                            Đơn hàng đang giao
                        </p>
                        <p class="text-xs text-slate-500">
                            Xem và điều phối đơn hàng hiện tại
                        </p>
                    </div>
                    <span
                        class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700"
                    >
                        {{ currentOrders.length }}
                    </span>
                </div>
                <div class="space-y-3">
                    <div
                        v-if="currentOrders.length === 0"
                        class="rounded-3xl border border-dashed border-slate-300 p-4 text-center text-sm text-slate-500"
                    >
                        Không có đơn hàng đang giao
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="order in currentOrders"
                            :key="order.id"
                            class="rounded-3xl border border-slate-200 p-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p
                                        class="text-sm font-semibold text-slate-900"
                                    >
                                        {{ order.order_code }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ order.address }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ order.phone }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p
                                        class="text-sm font-semibold text-slate-900"
                                    >
                                        {{ formatCurrency(order.total) }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ getStatusText(order.status) }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <Link
                                    :href="`/shipper/orders/${order.id}/detail`"
                                    class="rounded-3xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-blue-700"
                                >
                                    Xem chi tiết
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section
                class="rounded-[28px] bg-white p-4 shadow-sm ring-1 ring-slate-200"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">
                            Đơn hàng có sẵn
                        </p>
                        <p class="text-xs text-slate-500">
                            Những đơn hàng chờ shipper nhận
                        </p>
                    </div>
                    <span
                        class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700"
                    >
                        {{ availableOrders.length }}
                    </span>
                </div>
                <div class="space-y-3">
                    <div
                        v-if="availableOrders.length === 0"
                        class="rounded-3xl border border-dashed border-slate-300 p-4 text-center text-sm text-slate-500"
                    >
                        Hiện không có đơn hàng sẵn sàng
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="order in availableOrders"
                            :key="order.id"
                            class="rounded-3xl border border-slate-200 p-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p
                                        class="text-sm font-semibold text-slate-900"
                                    >
                                        {{ order.order_code }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ order.address }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Khoảng cách ~{{
                                            calculateDistance(order)
                                        }}km
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p
                                        class="text-sm font-semibold text-slate-900"
                                    >
                                        {{ formatCurrency(order.total) }}
                                    </p>
                                </div>
                            </div>
                            <button
                                @click="showAcceptDialog(order)"
                                class="mt-3 w-full rounded-3xl bg-orange-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-orange-600"
                            >
                                Nhận đơn
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <div
                v-if="showAssignmentModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
            >
                <div
                    class="max-w-3xl w-full rounded-[2rem] bg-white p-8 shadow-2xl border border-white/80"
                >
                    <div class="flex flex-col gap-4 text-center">
                        <div class="text-6xl">📣</div>
                        <h2 class="text-3xl font-black text-slate-900">
                            Đơn hàng mới đã được gán cho bạn!
                        </h2>
                        <p class="text-sm text-slate-500">
                            Đơn #{{ selectedOrder && selectedOrder.order_code }}
                            vừa được gán. Vui lòng xác nhận để bắt đầu nhận.
                        </p>
                    </div>
                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <div
                            class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5 text-left"
                        >
                            <p
                                class="text-xs uppercase tracking-[0.3em] text-slate-400 mb-3"
                            >
                                Quán
                            </p>
                            <p class="text-lg font-semibold text-slate-900">
                                {{
                                    selectedOrder && selectedOrder.restaurant
                                        ? selectedOrder.restaurant.name
                                        : "Quán chưa rõ"
                                }}
                            </p>
                            <p class="text-sm text-slate-500 mt-2">
                                Địa chỉ:
                                {{
                                    selectedOrder &&
                                    selectedOrder.restaurant &&
                                    selectedOrder.restaurant.latitude
                                        ? selectedOrder.restaurant.latitude +
                                          ", " +
                                          selectedOrder.restaurant.longitude
                                        : "Chưa có tọa độ"
                                }}
                            </p>
                        </div>
                        <div
                            class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5 text-left"
                        >
                            <p
                                class="text-xs uppercase tracking-[0.3em] text-slate-400 mb-3"
                            >
                                Khách hàng
                            </p>
                            <p class="text-lg font-semibold text-slate-900">
                                {{
                                    selectedOrder && selectedOrder.user
                                        ? selectedOrder.user.name
                                        : "Khách hàng"
                                }}
                            </p>
                            <p class="text-sm text-slate-500 mt-2">
                                Địa chỉ giao:
                                {{ selectedOrder && selectedOrder.address }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center"
                    >
                        <button
                            @click="handleAcceptOrder"
                            class="w-full rounded-3xl bg-emerald-600 px-6 py-4 text-sm font-black uppercase tracking-[0.2em] text-white shadow-xl hover:bg-emerald-700 transition"
                        >
                            Xác nhận nhận đơn
                        </button>
                        <button
                            @click="closeAssignmentModal"
                            class="w-full rounded-3xl border border-slate-200 bg-white px-6 py-4 text-sm font-black uppercase tracking-[0.2em] text-slate-700 hover:bg-slate-50 transition"
                        >
                            Xem sau
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
            shipper: data.shipper,
        });
        currentOrders.value = data.current_orders;
        availableOrders.value = data.available_orders;
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
        (order) => order.status === "assigned",
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

const closeAssignmentModal = () => {
    if (selectedOrder.value) {
        dismissedAssignedOrderId.value = selectedOrder.value.id;
    }
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

const createCircleMarker = (coords, options, popupText) => {
    if (!map.value || !coords) return null;
    const marker = L.circleMarker(coords, options).addTo(map.value);
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
            createCircleMarker(
                restaurantCoords,
                {
                    radius: 7,
                    fillColor: "#f59e0b",
                    color: "#ffffff",
                    weight: 2,
                    fillOpacity: 0.9,
                },
                `Quán: ${order.restaurant.name}`,
            );
        }

        if (customerCoords) {
            createCircleMarker(
                customerCoords,
                {
                    radius: 7,
                    fillColor: "#2563eb",
                    color: "#ffffff",
                    weight: 2,
                    fillOpacity: 0.9,
                },
                `Khách: ${order.user.name}`,
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
    if (shipperMarker.value) {
        shipperMarker.value.setLatLng(coords);
    } else {
        shipperMarker.value = L.circleMarker(coords, {
            radius: 10,
            fillColor: "#ef4444",
            color: "#ffffff",
            weight: 2,
            fillOpacity: 0.95,
        }).addTo(map.value);
        shipperMarker.value.bindPopup("Vị trí Shipper");
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

const getStatusText = (status) => {
    const statuses = {
        pending: "Chờ xác nhận",
        assigned: "Đơn mới chưa nhận",
        confirmed: "Đã xác nhận",
        shipping: "Đi tới quán",
        picked_up: "Đã lấy hàng",
        delivering: "Đang giao tới khách",
        completed: "Hoàn thành",
        cancelled: "Đã hủy",
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
