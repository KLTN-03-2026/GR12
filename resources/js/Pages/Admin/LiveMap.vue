<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import axios from 'axios';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

const mapContainer = ref(null);
let map = null;
let markers = [];
const activeOrders = ref([]);
const isLoading = ref(true);
let refreshInterval = null;
const subscribedOrders = new Set();

const customIcons = {
    customer: L.divIcon({
        className: 'custom-div-icon',
        html: `<div class="w-8 h-8 bg-blue-500 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-sm font-bold">👤</div>`,
        iconSize: [32, 32],
        iconAnchor: [16, 16]
    }),
    restaurant: L.divIcon({
        className: 'custom-div-icon',
        html: `<div class="w-8 h-8 bg-orange-500 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-sm font-bold">🏪</div>`,
        iconSize: [32, 32],
        iconAnchor: [16, 16]
    }),
    shipper: L.divIcon({
        className: 'custom-div-icon',
        html: `<div class="w-10 h-10 bg-purple-600 rounded-full border-2 border-white shadow-xl flex items-center justify-center text-white text-lg font-bold animate-bounce">🛵</div>`,
        iconSize: [40, 40],
        iconAnchor: [20, 20]
    })
};

const initMap = () => {
    // Default center (can be user's city center)
    map = L.map(mapContainer.value, {
        zoomControl: false // Disable default zoom control to move it
    }).setView([16.0544, 108.2022], 13); // Da Nang default

    L.control.zoom({
        position: 'bottomright'
    }).addTo(map);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap & CartoDB',
        subdomains: 'abcd',
        maxZoom: 19
    }).addTo(map);
};

const clearMarkers = () => {
    markers.forEach(marker => map.removeLayer(marker));
    markers = [];
};

const fetchMapData = async () => {
    try {
        const response = await axios.get(route('admin.live-map-data'));
        activeOrders.value = response.data;
        updateMapMarkers();
        subscribeToOrders();
        isLoading.value = false;
    } catch (error) {
        console.error('Lỗi khi tải dữ liệu bản đồ:', error);
    }
};

const subscribeToOrders = () => {
    if (!window.Echo) return;

    activeOrders.value.forEach(order => {
        if (!subscribedOrders.has(order.id)) {
            subscribedOrders.add(order.id);
            window.Echo.private(`order.${order.id}`)
                .listen('ShipperLocationUpdated', (e) => {
                    const targetOrder = activeOrders.value.find(o => o.id === order.id);
                    if (targetOrder && targetOrder.shipper) {
                        targetOrder.shipper.latitude = e.latitude;
                        targetOrder.shipper.longitude = e.longitude;
                        updateMapMarkers();
                    }
                });
        }
    });
};

const updateMapMarkers = () => {
    clearMarkers();
    
    if (!map) return;

    activeOrders.value.forEach(order => {
        // Customer Marker
        if (order.customer && order.customer.latitude && order.customer.longitude) {
            const popupContent = `
                <div class="p-2 min-w-[150px]">
                    <div class="text-xs font-bold text-gray-500 uppercase">Khách hàng</div>
                    <div class="font-black text-gray-900">${order.customer.name}</div>
                    <div class="text-xs text-gray-600 mt-1">Đơn hàng: <span class="font-bold text-blue-600">#${order.order_code}</span></div>
                </div>
            `;
            const marker = L.marker([order.customer.latitude, order.customer.longitude], { icon: customIcons.customer })
                .bindPopup(popupContent)
                .addTo(map);
            markers.push(marker);
        }

        // Restaurant Marker
        if (order.restaurant && order.restaurant.latitude && order.restaurant.longitude) {
            const popupContent = `
                <div class="p-2 min-w-[150px]">
                    <div class="text-xs font-bold text-orange-500 uppercase">Quán ăn</div>
                    <div class="font-black text-gray-900">${order.restaurant.restaurant_name || order.restaurant.name}</div>
                    <div class="text-xs text-gray-600 mt-1">Đang chuẩn bị đơn: <span class="font-bold text-blue-600">#${order.order_code}</span></div>
                </div>
            `;
            const marker = L.marker([order.restaurant.latitude, order.restaurant.longitude], { icon: customIcons.restaurant })
                .bindPopup(popupContent)
                .addTo(map);
            markers.push(marker);
        }

        // Shipper Marker
        if (order.shipper && order.shipper.latitude && order.shipper.longitude) {
            const popupContent = `
                <div class="p-2 min-w-[150px]">
                    <div class="text-xs font-bold text-purple-500 uppercase">Tài xế (Shipper)</div>
                    <div class="font-black text-gray-900">${order.shipper.name}</div>
                    <div class="text-xs text-gray-600 mt-1">Trạng thái: <span class="font-bold uppercase">${order.status}</span></div>
                    <div class="text-xs text-gray-600">Giao đơn: <span class="font-bold text-blue-600">#${order.order_code}</span></div>
                </div>
            `;
            const marker = L.marker([order.shipper.latitude, order.shipper.longitude], { icon: customIcons.shipper })
                .bindPopup(popupContent)
                .addTo(map);
            markers.push(marker);
        }
    });

    // Auto fit bounds if there are markers and it's the first load
    if (markers.length > 0 && isLoading.value) {
        const group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
};

onMounted(() => {
    initMap();
    fetchMapData();
    // Tăng tốc độ làm mới lên 3 giây để trải nghiệm Demo mượt mà hơn
    refreshInterval = setInterval(fetchMapData, 3000);
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
    if (map) map.remove();
    
    // Cleanup WebSocket listeners
    if (window.Echo) {
        subscribedOrders.forEach(orderId => {
            window.Echo.leave(`order.${orderId}`);
        });
    }
});

const getStatusColor = (status) => {
    const colors = {
        preparing: "text-orange-600 bg-orange-100",
        ready: "text-blue-600 bg-blue-100",
        delivering: "text-purple-600 bg-purple-100",
    };
    return colors[status] || "text-gray-600 bg-gray-100";
};

const getStatusLabel = (status) => {
    const labels = {
        preparing: "Đang làm món",
        ready: "Chờ lấy hàng",
        delivering: "Đang giao",
    };
    return labels[status] || status;
};
</script>

<template>
    <Head title="Control Center - Admin" />

    <AdminLayout>
        <div class="h-[calc(100vh-2rem)] flex flex-col -mx-4 sm:-mx-6 lg:-mx-8 -mt-8">
            <!-- Header Overlay -->
            <div class="bg-white/90 backdrop-blur-md border-b border-slate-200 p-4 px-6 flex items-center justify-between z-[50] shadow-sm">
                <div>
                    <h2 class="text-xl font-black text-slate-800 flex items-center gap-2">
                        <span class="relative flex h-3 w-3">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                        </span>
                        Live Tracking Map
                    </h2>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Giám sát toàn bộ hoạt động giao hàng theo thời gian thực</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-lg">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> Khách
                    </div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-lg">
                        <span class="w-2 h-2 rounded-full bg-orange-500"></span> Quán
                    </div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-lg">
                        <span class="w-2 h-2 rounded-full bg-purple-600"></span> Shipper
                    </div>
                </div>
            </div>

            <!-- Map Container -->
            <div class="flex-1 relative flex">
                <div ref="mapContainer" class="absolute inset-0 z-10 bg-slate-100"></div>

                <!-- Active Orders Sidebar Widget -->
                <div class="absolute top-4 left-4 z-[40] w-80 max-h-[calc(100vh-8rem)] flex flex-col bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-white overflow-hidden transition-all">
                    <div class="p-4 border-b border-slate-100/50 flex justify-between items-center bg-white/50">
                        <h3 class="font-black text-slate-800 text-sm uppercase tracking-widest">Đơn hàng Active ({{ activeOrders.length }})</h3>
                        <div v-if="isLoading" class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                    </div>
                    
                    <div class="overflow-y-auto custom-scrollbar flex-1 p-2 space-y-2">
                        <div v-if="activeOrders.length === 0" class="p-8 text-center text-slate-400">
                            <div class="text-3xl mb-2 opacity-50">📭</div>
                            <p class="text-xs font-bold">Không có đơn hàng nào đang chạy.</p>
                        </div>
                        
                        <div v-for="order in activeOrders" :key="order.id" class="bg-white p-3 rounded-2xl shadow-sm border border-slate-100 hover:border-blue-200 transition-colors cursor-pointer group">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-black text-slate-900 group-hover:text-blue-600">#{{ order.order_code }}</span>
                                <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md" :class="getStatusColor(order.status)">
                                    {{ getStatusLabel(order.status) }}
                                </span>
                            </div>
                            <div class="space-y-1 text-xs">
                                <div class="flex items-center gap-1.5 truncate">
                                    <span class="text-orange-500">🏪</span> <span class="font-medium text-slate-600 truncate">{{ order.restaurant?.restaurant_name || order.restaurant?.name || 'N/A' }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 truncate">
                                    <span class="text-purple-500">🛵</span> <span class="font-medium text-slate-600 truncate">{{ order.shipper?.name || 'Đang tìm...' }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 truncate">
                                    <span class="text-blue-500">👤</span> <span class="font-medium text-slate-600 truncate">{{ order.customer?.name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style>
/* Optional styling to hide leaflet attribution if needed */
.leaflet-control-attribution {
    display: none !important;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(203, 213, 225, 0.5);
    border-radius: 20px;
}
</style>
