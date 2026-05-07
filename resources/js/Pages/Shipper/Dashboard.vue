<template>
    <ShipperLayout>
        <!-- Status Header Card -->
        <section
            class="mb-6 rounded-[2rem] bg-gradient-to-br from-slate-900 to-slate-800 p-5 text-white shadow-[0_10px_30px_-10px_rgba(15,23,42,0.6)] relative overflow-hidden ring-1 ring-white/10"
        >
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="flex items-center justify-between gap-4 relative z-10">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">
                        Phiên làm việc
                    </p>
                    <div class="mt-1 flex items-baseline gap-2">
                        <p class="text-2xl font-black tracking-tight">
                            {{
                                shipper.status === "offline"
                                    ? "Nghỉ ngơi"
                                    : shipper.status === "busy"
                                      ? "Đang giao"
                                      : "Sẵn sàng"
                            }}
                        </p>
                    </div>
                </div>
                <button
                    @click="toggleOnline"
                    :disabled="isCheckoutDisabled"
                    :class="[
                        'rounded-full px-5 py-3 text-sm font-bold shadow-lg transition-all transform active:scale-95',
                        shipper.status === 'offline'
                            ? 'bg-emerald-500 text-white hover:bg-emerald-400 shadow-emerald-500/20 ring-4 ring-emerald-500/20'
                            : isCheckoutDisabled
                              ? 'bg-slate-700 text-slate-500 cursor-not-allowed shadow-none'
                              : 'bg-rose-500 text-white hover:bg-rose-400 shadow-rose-500/20 ring-4 ring-rose-500/20',
                    ]"
                >
                    <div class="flex items-center gap-2">
                        <span class="text-lg">
                            {{ shipper.status === 'offline' ? '🚀' : '⏸️' }}
                        </span>
                        <span>
                            {{
                                shipper.status === "offline"
                                    ? "Bắt đầu"
                                    : isCheckoutDisabled
                                      ? "Đang bận"
                                      : "Nghỉ ngơi"
                            }}
                        </span>
                    </div>
                </button>
            </div>

            <div
                v-if="isCheckoutDisabled"
                class="mt-4 rounded-2xl bg-amber-500/10 p-3 text-xs text-amber-300 flex items-start gap-2 ring-1 ring-amber-500/20 backdrop-blur-md relative z-10"
            >
                <span class="text-amber-500 shrink-0">⚠️</span>
                <span>Không thể nghỉ khi đang có đơn. Vui lòng hoàn thành đơn hiện tại.</span>
            </div>

            <!-- Stats grid -->
            <div class="mt-5 grid grid-cols-3 gap-3 text-center text-xs relative z-10">
                <div class="rounded-[1.5rem] bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
                    <div class="text-2xl font-black text-white">
                        {{ currentOrders.length }}
                    </div>
                    <div class="text-[10px] font-medium tracking-wide text-slate-400 mt-0.5">Đang giao</div>
                </div>
                <div class="rounded-[1.5rem] bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
                    <div class="text-2xl font-black text-white">
                        {{ availableOrders.length }}
                    </div>
                    <div class="text-[10px] font-medium tracking-wide text-slate-400 mt-0.5">Đơn có sẵn</div>
                </div>
                <div class="rounded-[1.5rem] bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
                    <div class="text-xl font-bold text-white mt-1">
                        {{ statusText === 'OFFLINE' ? 'Nghỉ' : (statusText === 'Sẵn sàng' ? 'Rảnh' : 'Bận') }}
                    </div>
                    <div class="text-[10px] font-medium tracking-wide text-slate-400 mt-1">Trạng thái</div>
                </div>
            </div>
        </section>

        <!-- Edge-to-edge Map Section -->
        <section
            class="mb-6 overflow-hidden bg-white shadow-sm md:rounded-[2rem] -mx-4 md:mx-0 border-y md:border border-slate-200"
        >
            <div class="relative">
                <div id="shipper-map" class="h-64 sm:h-72 w-full bg-slate-200 z-0"></div>
                <!-- Floating Map Overlay -->
                <div class="absolute bottom-4 left-4 right-4 z-10 pointer-events-none">
                    <div class="rounded-2xl bg-white/90 backdrop-blur-md p-3 text-xs text-slate-600 shadow-lg ring-1 ring-slate-200/50 flex gap-3 items-center">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                            <span class="text-xl">📍</span>
                        </div>
                        <div>
                            <p class="font-bold text-slate-900">Bán kính 5km</p>
                            <p class="mt-0.5 text-[10px] leading-tight">Tìm đơn xung quanh 2km vị trí của bạn</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Current Orders -->
        <section
            class="mb-6 rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-slate-200"
        >
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">
                        Đang giao
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Đơn hàng bạn đang thực hiện
                    </p>
                </div>
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 font-bold text-sm">
                    {{ currentOrders.length }}
                </div>
            </div>

            <div class="space-y-4">
                <div
                    v-if="currentOrders.length === 0"
                    class="rounded-[1.5rem] border-2 border-dashed border-slate-200 p-8 text-center"
                >
                    <span class="text-4xl block mb-2 opacity-50">🛵</span>
                    <p class="text-sm font-semibold text-slate-500">Chưa có đơn hàng nào</p>
                </div>
                <div v-else class="space-y-4">
                    <div
                        v-for="order in currentOrders"
                        :key="order.id"
                        class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm transition-all hover:shadow-md"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-black text-slate-900">
                                        {{ order.order_code }}
                                    </p>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[10px] font-bold text-slate-500">{{ getStatusText(order.status) }}</span>
                                </div>
                                <p class="mt-2 text-xs text-slate-600 line-clamp-2 leading-relaxed">
                                    <span class="font-semibold text-slate-900">Giao đến:</span> {{ order.address }}
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
                                <span>Tiếp tục giao</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Available Orders -->
        <section
            class="rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-slate-200"
        >
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">
                        Đơn có sẵn
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Chờ bạn nhận ngay
                    </p>
                </div>
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-sky-100 text-sky-700 font-bold text-sm">
                    {{ availableOrders.length }}
                </div>
            </div>

            <div class="space-y-4">
                <div
                    v-if="availableOrders.length === 0"
                    class="rounded-[1.5rem] border-2 border-dashed border-slate-200 p-8 text-center"
                >
                    <span class="text-4xl block mb-2 opacity-50">👀</span>
                    <p class="text-sm font-semibold text-slate-500 mt-2">Đang tìm đơn xung quanh...</p>
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
                                <div class="mt-3 flex items-center gap-1.5 text-xs font-semibold text-sky-600 bg-sky-50 px-2.5 py-1.5 rounded-lg inline-flex">
                                    <span>📍</span>
                                    Cách bạn ~{{ calculateDistance(order) }}km
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-base font-black text-slate-900">
                                    {{ formatCurrency(order.total) }}
                                </p>
                            </div>
                        </div>
                        <button
                            @click="acceptOrder(order.id)"
                            class="mt-4 w-full rounded-2xl bg-indigo-600 px-4 py-3.5 text-sm font-bold text-white transition hover:bg-indigo-700 active:scale-[0.98] shadow-lg shadow-indigo-600/20"
                        >
                            Nhận đơn ngay
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </ShipperLayout>
</template>

<script setup>
import { ref, watch, computed, onMounted } from "vue";
import { Link } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import ShipperLayout from "@/Layouts/ShipperLayout.vue";
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
        currentOrders.value = data.current_orders;
        availableOrders.value = data.available_orders;
        shipper.value = data.shipper;
        refreshMap();
    } catch (error) {
        console.error("Error fetching dashboard:", error);
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

        if (
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

const toggleOnline = async () => {
    try {
        if (shipper.value.status === "offline") {
            await checkIn();
            toast.success("✅ Check-in thành công!");
        } else if (shipper.value.status === "busy") {
            toast.warning(
                "⚠️ Bạn đang có đơn hàng đang giao. Vui lòng hoàn thành đơn hàng trước khi check-out.",
            );
            return;
        } else {
            await checkOut();
            toast.success("👋 Check-out thành công!");
        }
        await fetchDashboard();
    } catch (error) {
        console.error("Error updating status:", error);
        toast.error(error.message || "❌ Không thể thay đổi trạng thái.");
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

const acceptOrder = async (orderId) => {
    try {
        const response = await fetch(`/api/shipper/orders/${orderId}/accept`, {
            method: "POST",
            headers: authHeaders,
            credentials: "include",
        });
        if (response.ok) {
            await fetchDashboard();
        }
    } catch (error) {
        console.error("Error accepting order:", error);
    }
};

const getStatusText = (status) => {
    const statuses = {
        pending: "Chờ xác nhận",
        confirmed: "Đã xác nhận",
        shipping: "Đang giao",
        picked_up: "Đã lấy hàng",
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

const autoCheckIn = async () => {
    try {
        await checkIn();
        await fetchDashboard();
        hasAutoCheckedIn.value = true;
        window.shipperHasAutoCheckedIn = true;
    } catch (error) {
        console.error("Auto check-in error:", error);
    }
};

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
