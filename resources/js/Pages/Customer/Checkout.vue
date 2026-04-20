<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import { computed, ref, onMounted, watch } from "vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

defineOptions({ layout: GuestLayout });

const props = defineProps({
    cartItems: Array,
    user: Object,
    vouchers: Array,
    restaurant: Object,
});

// useForm hỗ trợ quản lý lỗi (errors) từ Backend trả về
const form = useForm({
    address: props.user?.address || props.user?.address_detail || "",
    phone: props.user?.phone || "",
    note: "",
    payment_method: "cod",
    voucher_code: "",
    latitude: props.user?.latitude || null,
    longitude: props.user?.longitude || null,
});

const savedAddresses = ref([]);
const geoStatus = ref("");
const isFetchingLocation = ref(false);
const selectedSavedAddressIndex = ref(null);
const map = ref(null);
const mapMarker = ref(null);
const mapMessage = ref("");
const isUpdatingFromMap = ref(false); // Flag để tránh infinite loop
const isGeocoding = ref(false); // Flag để hiển thị loading khi geocode
const distance = ref(null); // Khoảng cách từ quán đến vị trí giao hàng (km)
const estimatedDeliveryTime = ref(null); // Thời gian dự kiến giao (phút)

const calculateDistance = (lat1, lng1, lat2, lng2) => {
    const R = 6371; // Bán kính Trái Đất (km)
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLng / 2) * Math.sin(dLng / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
};

const loadSavedAddresses = () => {
    if (typeof window === "undefined") return;
    const saved = window.localStorage.getItem("delivery_addresses");
    savedAddresses.value = saved ? JSON.parse(saved) : [];
};

const saveSavedAddresses = () => {
    if (typeof window === "undefined") return;
    window.localStorage.setItem(
        "delivery_addresses",
        JSON.stringify(savedAddresses.value),
    );
};

const addSavedAddress = () => {
    const trimmed = form.address.trim();
    if (!trimmed) {
        form.errors.address = "Nhập địa chỉ để lưu.";
        return;
    }

    if (!savedAddresses.value.includes(trimmed)) {
        savedAddresses.value.unshift(trimmed);
        saveSavedAddresses();
    }

    selectedSavedAddressIndex.value = savedAddresses.value.indexOf(trimmed);
};

const selectSavedAddress = (index) => {
    selectedSavedAddressIndex.value = index;
    form.address = savedAddresses.value[index];
    mapMessage.value = "Địa chỉ đã chọn sẽ hiển thị trên bản đồ sau khi được xác định.";
    geocodeAddress(form.address);
};

const removeSavedAddress = (index) => {
    savedAddresses.value.splice(index, 1);
    saveSavedAddresses();
    if (selectedSavedAddressIndex.value === index) {
        selectedSavedAddressIndex.value = null;
    }
};

const initMap = () => {
    if (!map.value) {
        // Focus vào Đà Nẵng, Việt Nam
        map.value = L.map("checkout-map", {
            zoomControl: false,
            attributionControl: false,
            maxBounds: [
                [8.0, 102.0], // Tây Nam Việt Nam
                [23.5, 110.0] // Đông Bắc Việt Nam
            ],
            maxBoundsViscosity: 1.0, // Không cho kéo ra ngoài
        }).setView([16.047079, 108.206230], 12); // Đà Nẵng, zoom level 12

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 18,
            minZoom: 6,
            attribution:
                "&copy; <a href='https://www.openstreetmap.org/copyright'>OpenStreetMap</a> contributors",
        }).addTo(map.value);

        // Thêm event listener cho click trên bản đồ
        map.value.on('click', async (e) => {
            const { lat, lng } = e.latlng;
            isUpdatingFromMap.value = true;

            try {
                const address = await reverseGeocode(lat, lng);
                form.address = address || `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
                form.latitude = lat;
                form.longitude = lng;
                updateMap(lat, lng, "Vị trí đã chọn trên bản đồ");
                mapMessage.value = "Đã cập nhật địa chỉ từ vị trí trên bản đồ.";
            } catch (error) {
                form.address = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
                form.latitude = lat;
                form.longitude = lng;
                updateMap(lat, lng, "Vị trí đã chọn trên bản đồ");
                mapMessage.value = "Đã cập nhật tọa độ từ bản đồ.";
            } finally {
                setTimeout(() => {
                    isUpdatingFromMap.value = false;
                }, 100);
            }
        });
    }
};

const updateMap = (lat, lng, label = "Vị trí giao hàng") => {
    if (!map.value) {
        return;
    }
    const coords = [lat, lng];
    map.value.setView(coords, 15); // Zoom level 15 cho vị trí cụ thể
    if (mapMarker.value) {
        mapMarker.value.setLatLng(coords);
    } else {
        mapMarker.value = L.marker(coords).addTo(map.value);
    }
    if (label) {
        mapMarker.value.bindPopup(label).openPopup();
    }
};

const tryInitMapPosition = async () => {
    initMap();
    if (form.latitude && form.longitude) {
        updateMap(form.latitude, form.longitude, "Địa chỉ profile hiện tại");
        mapMessage.value = "Bản đồ đang hiển thị vị trí từ profile của bạn.";
    } else if (form.address) {
        await geocodeAddress(form.address);
    } else {
        mapMessage.value = "Nhập địa chỉ tại Đà Nẵng hoặc nhấn lấy vị trí hiện tại để hiển thị trên bản đồ.";
    }
};

const getCurrentLocation = () => {
    if (!navigator.geolocation) {
        geoStatus.value = "Trình duyệt không hỗ trợ định vị.";
        return;
    }

    isFetchingLocation.value = true;
    geoStatus.value = "Đang lấy vị trí hiện tại...";

    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const { latitude, longitude } = position.coords;
            geoStatus.value = "Đang xác định địa chỉ...";
            try {
                const address = await reverseGeocode(latitude, longitude);
                form.address =
                    address ||
                    `Lat: ${latitude.toFixed(6)}, Lng: ${longitude.toFixed(6)}`;
                form.latitude = latitude;
                form.longitude = longitude;
                updateMap(latitude, longitude, "Vị trí hiện tại của bạn");
                mapMessage.value = "Đã điền địa chỉ hiện tại và cập nhật lên bản đồ.";
                geoStatus.value = "Đã điền vị trí hiện tại vào địa chỉ.";
            } catch (error) {
                form.address = `Lat: ${latitude.toFixed(6)}, Lng: ${longitude.toFixed(6)}`;
                form.latitude = latitude;
                form.longitude = longitude;
                updateMap(latitude, longitude, "Vị trí hiện tại của bạn");
                geoStatus.value =
                    "Không lấy được địa chỉ chi tiết, đã điền tọa độ thay thế.";
            } finally {
                isFetchingLocation.value = false;
            }
        },
        (error) => {
            geoStatus.value =
                "Không thể lấy vị trí hiện tại. Vui lòng cho phép định vị.";
            isFetchingLocation.value = false;
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 },
    );
};

const reverseGeocode = async (lat, lng) => {
    const response = await fetch(
        `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`,
    );
    if (!response.ok) {
        throw new Error("Reverse geocode failed");
    }
    const data = await response.json();
    return data.display_name;
};

const geocodeAddress = async (address) => {
    if (!address || isUpdatingFromMap.value) return null;

    isGeocoding.value = true;
    mapMessage.value = "Đang tìm vị trí trên bản đồ...";

    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(
                address,
            )}`,
        );
        if (!response.ok) {
            throw new Error("Geocode failed");
        }
        const data = await response.json();
        if (!data || data.length === 0) {
            mapMessage.value =
                "Không tìm thấy vị trí trên bản đồ. Vui lòng kiểm tra lại địa chỉ.";
            return null;
        }
        const place = data[0];
        form.latitude = parseFloat(place.lat);
        form.longitude = parseFloat(place.lon);
        updateMap(form.latitude, form.longitude, "Vị trí theo địa chỉ của bạn");
        mapMessage.value = "Đã hiển thị vị trí theo địa chỉ hiện tại.";
        return place;
    } finally {
        isGeocoding.value = false;
    }
};

const subtotal = computed(() => {
    if (!props.cartItems) return 0;
    return props.cartItems.reduce(
        (sum, item) => sum + parseFloat(item.product.price) * item.quantity,
        0,
    );
});

const shippingFee = computed(() => {
    if (!distance.value) return 0;
    
    // Tính phí giao: dưới 3km là 12k, mỗi km tiếp theo là 3k
    if (distance.value <= 3) {
        return 12000;
    } else {
        const additionalKm = Math.ceil(distance.value - 3);
        return 12000 + (additionalKm * 3000);
    }
});

const selectedVoucher = computed(() => {
    return props.vouchers?.find((voucher) => voucher.code === form.voucher_code) || null;
});

const voucherDiscount = computed(() => {
    if (!selectedVoucher.value) return 0;
    const value = parseFloat(selectedVoucher.value.discount_value);
    if (selectedVoucher.value.discount_type === 'percent') {
        return Math.min(subtotal.value * (value / 100), subtotal.value);
    }
    return Math.min(subtotal.value, value);
});

const total = computed(() => {
    return subtotal.value + shippingFee.value - voucherDiscount.value;
});

const voucherLabel = computed(() => {
    if (!selectedVoucher.value) return '';
    return selectedVoucher.value.discount_type === 'percent'
        ? `${selectedVoucher.value.discount_value}%`
        : formatPrice(selectedVoucher.value.discount_value);
});

const formatPrice = (p) =>
    new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(p);

const submitOrder = () => {
    form.post(route("orders.store"), {
        preserveScroll: true,
        onSuccess: () => {
            console.log("Đặt hàng thành công!");
        },
    });
};

onMounted(() => {
    loadSavedAddresses();
    tryInitMapPosition();

    // Tính khoảng cách ban đầu nếu có tọa độ sẵn
    const calculateInitialDistance = () => {
        if (form.latitude && form.longitude && props.restaurant?.latitude && props.restaurant?.longitude) {
            const dist = calculateDistance(
                props.restaurant.latitude,
                props.restaurant.longitude,
                form.latitude,
                form.longitude
            );
            distance.value = dist;
            estimatedDeliveryTime.value = Math.ceil(5 + (dist * 2));
        }
    };
    
    // Chờ một chút để map khởi tạo xong rồi tính khoảng cách
    setTimeout(calculateInitialDistance, 500);

    // Watcher để tự động geocode khi người dùng nhập địa chỉ
    watch(
        () => form.address,
        async (newAddress, oldAddress) => {
            if (newAddress &&
                newAddress !== oldAddress &&
                newAddress.length > 3 &&
                !newAddress.match(/^Lat:\s*-?\d+\.\d+,\s*Lng:\s*-?\d+\.\d+$/)) { // Không geocode nếu là tọa độ
                // Debounce để tránh gọi API quá nhiều
                clearTimeout(window.geocodeTimeout);
                window.geocodeTimeout = setTimeout(async () => {
                    try {
                        await geocodeAddress(newAddress);
                    } catch (error) {
                        console.warn("Geocode error:", error);
                    }
                }, 1000); // Đợi 1 giây sau khi người dùng ngừng nhập
            }
        },
        { immediate: false }
    );

    // Tính khoảng cách và thời gian giao khi có tọa độ thay đổi
    watch(
        () => ({ custLat: form.latitude, custLng: form.longitude }),
        (newCoords) => {
            if (newCoords.custLat && newCoords.custLng && props.restaurant?.latitude && props.restaurant?.longitude) {
                const dist = calculateDistance(
                    props.restaurant.latitude,
                    props.restaurant.longitude,
                    newCoords.custLat,
                    newCoords.custLng
                );
                distance.value = dist;
                // Thời gian dự kiến: 5 phút chuẩn bị + 2 phút/km
                estimatedDeliveryTime.value = Math.ceil(5 + (dist * 2));
            }
        },
        { deep: true }
    );
});
</script>

<template>
    <Head title="Thanh toán - FoodXpress" />

    <div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-pink-50 py-12">
        <div class="max-w-6xl mx-auto px-4">
            <div v-if="cartItems && cartItems.length > 0">
                <h1
                    class="text-4xl font-black text-gray-900 mb-10 tracking-tighter italic bg-gradient-to-r from-orange-600 to-pink-600 bg-clip-text text-transparent animate-pulse"
                >
                    Xác nhận đơn hàng 🚀
                </h1>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Thông tin quán ăn -->
                        <div
                            class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-[2.5rem] p-8 shadow-lg border border-blue-200"
                        >
                            <h3
                                class="text-xl font-black text-blue-900 mb-6 flex items-center gap-3"
                            >
                                <span class="text-3xl">🏪</span>
                                <span>Thông tin quán ăn</span>
                            </h3>
                            <div v-if="props.restaurant" class="space-y-4">
                                <div class="flex items-start gap-3">
                                    <span class="text-2xl">🏘️</span>
                                    <div class="flex-1">
                                        <p class="text-xs font-black text-blue-600 uppercase tracking-widest mb-1">Địa chỉ quán</p>
                                        <p class="font-bold text-gray-800">{{ props.restaurant.restaurant_name || props.restaurant.name }}</p>
                                        <p class="text-sm text-gray-600 mt-1">{{ props.restaurant.address }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div v-if="distance" class="bg-white rounded-2xl p-4 border border-blue-200">
                                        <p class="text-xs font-black text-blue-600 uppercase tracking-widest mb-2">📏 Khoảng cách</p>
                                        <p class="text-2xl font-black text-blue-900">{{ distance.toFixed(1) }} km</p>
                                    </div>
                                    <div v-if="estimatedDeliveryTime" class="bg-white rounded-2xl p-4 border border-blue-200">
                                        <p class="text-xs font-black text-blue-600 uppercase tracking-widest mb-2">⏱️ Thời gian giao</p>
                                        <p class="text-2xl font-black text-blue-900">{{ estimatedDeliveryTime }} phút</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-gradient-to-br from-white to-gray-50 rounded-[2.5rem] p-8 shadow-xl border border-gray-200 hover:shadow-2xl transition-all duration-300"
                        >
                            <h3
                                class="text-xl font-black text-gray-800 mb-6 flex items-center gap-3"
                            >
                                <span
                                    class="w-10 h-10 bg-gradient-to-r from-orange-500 to-pink-500 text-white rounded-xl flex items-center justify-center text-sm font-black shadow-lg"
                                    >01</span
                                >
                                <span class="bg-gradient-to-r from-gray-700 to-gray-900 bg-clip-text text-transparent">Thông tin nhận hàng</span>
                            </h3>

                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label
                                        class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4 mb-2 block"
                                        >Địa chỉ giao hàng *</label
                                    >
                                    <input
                                        v-model="form.address"
                                        type="text"
                                        placeholder="Nhập địa chỉ tại Đà Nẵng để tự động định vị..."
                                        :class="{
                                            'ring-2 ring-red-500 bg-red-50':
                                                form.errors.address,
                                        }"
                                        class="w-full bg-gradient-to-r from-gray-50 to-gray-100 border-none rounded-2xl p-4 focus:ring-4 focus:ring-orange-200 transition-all duration-300 font-bold text-gray-800 shadow-inner hover:shadow-lg transform hover:scale-[1.01]"
                                    />
                                    <p
                                        v-if="form.errors.address"
                                        class="text-red-500 text-xs mt-2 ml-4 font-bold"
                                    >
                                        {{ form.errors.address }}
                                    </p>

                                    <div class="flex flex-col gap-3 mt-4">
                                        <div class="flex flex-wrap gap-3">
                                            <button
                                                type="button"
                                                @click="getCurrentLocation"
                                                :disabled="isFetchingLocation"
                                                class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white border border-blue-300 rounded-2xl px-4 py-3 text-xs font-black uppercase tracking-widest transition-all duration-300 hover:from-blue-600 hover:to-blue-700 hover:shadow-lg disabled:opacity-50 transform hover:scale-105 active:scale-95"
                                            >
                                                📍 Lấy vị trí hiện tại
                                            </button>
                                            <button
                                                type="button"
                                                @click="addSavedAddress"
                                                class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-orange-500 to-pink-500 text-white rounded-2xl px-4 py-3 text-xs font-black uppercase tracking-widest transition-all duration-300 hover:from-orange-600 hover:to-pink-600 hover:shadow-lg transform hover:scale-105 active:scale-95"
                                            >
                                                💾 Lưu địa chỉ này
                                            </button>
                                        </div>
                                        <p
                                            v-if="geoStatus"
                                            class="text-xs text-gray-500"
                                        >
                                            {{ geoStatus }}
                                        </p>
                                    </div>

                                    <div
                                        v-if="savedAddresses.length"
                                        class="bg-gradient-to-br from-gray-50 to-white rounded-[2rem] border border-gray-200 p-4 mt-4 shadow-inner"
                                    >
                                        <div class="flex items-center justify-between mb-4">
                                            <p class="text-xs font-black uppercase tracking-[0.2em] text-gray-500">
                                                📍 Địa chỉ đã lưu
                                            </p>
                                            <span class="text-[10px] font-black uppercase text-orange-500 bg-orange-100 px-2 py-1 rounded-full">
                                                Chọn nhanh
                                            </span>
                                        </div>
                                        <div class="space-y-3">
                                            <div
                                                v-for="(address, index) in savedAddresses"
                                                :key="address"
                                                class="flex items-start justify-between gap-3 p-4 rounded-3xl border border-gray-200 bg-white hover:border-orange-300 hover:shadow-md transition-all duration-300 group"
                                            >
                                                <button
                                                    type="button"
                                                    @click="selectSavedAddress(index)"
                                                    class="text-left flex-1"
                                                >
                                                    <p class="font-black text-sm text-gray-800 leading-tight group-hover:text-orange-600 transition-colors">
                                                        {{ address }}
                                                    </p>
                                                    <p class="text-[10px] text-gray-500 uppercase tracking-[0.2em] mt-1">
                                                        {{ selectedSavedAddressIndex === index ? '✅ Đã chọn' : '👆 Chọn địa chỉ' }}
                                                    </p>
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="removeSavedAddress(index)"
                                                    class="text-[10px] font-black uppercase tracking-[0.2em] text-red-500 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-all duration-300 transform hover:scale-110"
                                                >
                                                    🗑️ Xóa
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                >
                                    <div>
                                        <label
                                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4 mb-2 block"
                                            >Số điện thoại *</label
                                        >
                                        <input
                                            v-model="form.phone"
                                            type="text"
                                            :class="{
                                                'ring-2 ring-red-500 bg-red-50':
                                                    form.errors.phone,
                                            }"
                                            class="w-full bg-gradient-to-r from-gray-50 to-gray-100 border-none rounded-2xl p-4 focus:ring-4 focus:ring-orange-200 transition-all duration-300 font-bold text-gray-800 shadow-inner hover:shadow-lg transform hover:scale-[1.01]"
                                        />
                                        <p
                                            v-if="form.errors.phone"
                                            class="text-red-500 text-xs mt-2 ml-4 font-bold"
                                        >
                                            {{ form.errors.phone }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4 mb-2 block"
                                            >Ghi chú thêm</label
                                        >
                                        <input
                                            v-model="form.note"
                                            type="text"
                                            placeholder="Ví dụ: Cổng sau, đừng bấm chuông..."
                                            class="w-full bg-gradient-to-r from-gray-50 to-gray-100 border-none rounded-2xl p-4 focus:ring-4 focus:ring-orange-200 transition-all duration-300 font-bold text-gray-800 shadow-inner hover:shadow-lg transform hover:scale-[1.01]"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                    <div
                        class="bg-gradient-to-br from-white to-gray-50 rounded-[2.5rem] p-8 shadow-xl border border-gray-200 hover:shadow-2xl transition-all duration-300"
                    >
                        <h3
                            class="text-xl font-black text-gray-800 mb-6 flex items-center gap-3"
                        >
                            <span
                                class="w-10 h-10 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl flex items-center justify-center text-sm font-black shadow-lg"
                                >02</span
                            >
                            <span class="bg-gradient-to-r from-gray-700 to-gray-900 bg-clip-text text-transparent">📍 Bản đồ giao hàng</span>
                        </h3>

                        <div class="h-96 rounded-[2rem] overflow-hidden border-2 border-orange-200 relative shadow-inner bg-gradient-to-br from-gray-50 to-white">
                            <div id="checkout-map" class="w-full h-full"></div>
                            <div class="absolute top-4 left-4 bg-white/95 backdrop-blur-xl rounded-xl p-4 shadow-2xl border border-white/20">
                                <p class="text-sm font-black text-gray-700 mb-2 flex items-center gap-2">
                                    📍 <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Bản đồ Đà Nẵng</span>
                                </p>
                                <div class="text-[11px] text-gray-600 leading-tight space-y-1">
                                    <p>• Nhập địa chỉ để tự động định vị</p>
                                    <p>• Click trên bản đồ để chọn vị trí giao hàng</p>
                                    <p>• Kéo thả marker để điều chỉnh chính xác</p>
                                </div>
                            </div>
                            <div
                                v-if="isGeocoding"
                                class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center"
                            >
                                <div class="text-center">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500 mx-auto mb-2"></div>
                                    <p class="text-sm font-bold text-gray-700">Đang tìm vị trí...</p>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-4">
                            {{ mapMessage || 'Bản đồ hiển thị khu vực Đà Nẵng. Nhập địa chỉ hoặc click để chọn vị trí giao hàng.' }}
                        </p>
                    </div>

                    <div
                        class="bg-gradient-to-br from-white to-gray-50 rounded-[2.5rem] p-8 shadow-xl border border-gray-200 hover:shadow-2xl transition-all duration-300"
                    >
                        <h3
                            class="text-xl font-black text-gray-800 mb-6 flex items-center gap-3"
                        >
                                <span
                                    class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl flex items-center justify-center text-sm font-black shadow-lg"
                                    >03</span
                                >
                                <span class="bg-gradient-to-r from-gray-700 to-gray-900 bg-clip-text text-transparent">Phương thức thanh toán</span>
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div
                                    @click="form.payment_method = 'cod'"
                                    :class="
                                        form.payment_method === 'cod'
                                            ? 'border-orange-400 bg-gradient-to-r from-orange-50 to-pink-50 shadow-lg scale-105'
                                            : 'border-gray-200 hover:border-orange-300 hover:shadow-md'
                                    "
                                    class="cursor-pointer border-2 p-4 rounded-2xl flex items-center gap-4 transition-all duration-300 group transform hover:scale-[1.02]"
                                >
                                    <div
                                        class="w-12 h-12 bg-gradient-to-r from-green-400 to-green-500 rounded-xl shadow-lg flex items-center justify-center text-2xl group-hover:scale-110 transition-transform"
                                    >
                                        💵
                                    </div>
                                    <span
                                        class="font-black text-sm text-gray-700 uppercase tracking-tight group-hover:text-orange-600 transition-colors"
                                        >Thanh toán khi nhận hàng</span
                                    >
                                </div>
                                <div
                                    class="cursor-not-allowed border-2 border-gray-100 p-4 rounded-2xl flex items-center gap-4 opacity-50 bg-gray-50"
                                >
                                    <div
                                        class="w-12 h-12 bg-gray-200 rounded-xl shadow-sm flex items-center justify-center text-2xl"
                                    >
                                        💳
                                    </div>
                                    <span
                                        class="font-black text-sm text-gray-500 uppercase tracking-tight"
                                        >Ví MoMo (Sắp có)</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-gradient-to-br from-white to-gray-50 rounded-[2.5rem] p-8 shadow-xl border border-gray-200 hover:shadow-2xl transition-all duration-300"
                    >
                        <h3
                            class="text-xl font-black text-gray-800 mb-6 flex items-center gap-3"
                        >
                            <span
                                class="w-10 h-10 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl flex items-center justify-center text-sm font-black shadow-lg"
                                >04</span
                            >
                            <span class="bg-gradient-to-r from-gray-700 to-gray-900 bg-clip-text text-transparent">🎁 Mã giảm giá</span>
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label
                                    class="text-xs font-black text-gray-500 uppercase tracking-widest mb-2 block"
                                >Chọn voucher</label>
                                <select
                                    v-model="form.voucher_code"
                                    class="w-full rounded-2xl border border-gray-300 bg-gradient-to-r from-gray-50 to-gray-100 p-4 text-sm outline-none transition-all duration-300 focus:border-orange-400 focus:ring-4 focus:ring-orange-100 shadow-inner hover:shadow-lg"
                                >
                                    <option value="">Không dùng voucher</option>
                                    <option
                                        v-for="voucher in props.vouchers"
                                        :key="voucher.id"
                                        :value="voucher.code"
                                    >
                                        {{ voucher.code }} —
                                        {{
                                            voucher.discount_type === 'percent'
                                                ? voucher.discount_value + '%'
                                                : formatPrice(voucher.discount_value)
                                        }}
                                        • Hạn đến
                                        {{ new Date(voucher.expires_at).toLocaleDateString('vi-VN') }}
                                    </option>
                                </select>
                                <p v-if="form.errors.voucher_code" class="text-red-500 text-xs mt-2">{{ form.errors.voucher_code }}</p>
                            </div>

                            <div v-if="selectedVoucher" class="rounded-3xl border-2 border-green-300 bg-gradient-to-r from-green-50 to-green-100 p-4 text-sm text-green-700 shadow-lg">
                                🎉 Áp dụng voucher <span class="font-black bg-gradient-to-r from-green-600 to-green-700 bg-clip-text text-transparent">{{ selectedVoucher.code }}</span> - Giảm <span class="font-black">{{ voucherLabel }}</span>.
                            </div>
                            <div v-else class="rounded-3xl border border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 p-4 text-sm text-gray-600 shadow-inner">
                                🎁 Chọn voucher để nhận khuyến mãi khi thanh toán.
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div
                            class="bg-gradient-to-br from-gray-900 via-gray-800 to-black rounded-[2.5rem] p-8 text-white sticky top-24 shadow-2xl border border-gray-700 hover:shadow-3xl transition-all duration-300"
                        >
                            <h3
                                class="text-xl font-black mb-8 italic uppercase text-orange-400 bg-gradient-to-r from-orange-400 to-pink-400 bg-clip-text text-transparent"
                            >
                                🧾 Tóm tắt đơn hàng
                            </h3>

                            <!-- Phần tổng tiền hiển thị ở trên cùng -->
                            <div class="bg-gradient-to-r from-orange-500/10 to-pink-500/10 rounded-2xl p-6 mb-8 border border-orange-500/20">
                                <div class="text-center">
                                    <p class="text-sm text-gray-300 font-bold uppercase tracking-widest mb-2">💰 Tổng tiền</p>
                                    <p class="text-4xl font-black text-white mb-2">{{ formatPrice(total) }}</p>
                                    <div class="text-xs text-gray-400 space-y-1">
                                        <p>Tạm tính: {{ formatPrice(subtotal) }}</p>
                                        <p>Phí ship: {{ formatPrice(shippingFee) }}</p>
                                        <p v-if="voucherDiscount > 0" class="text-green-400">Giảm: -{{ formatPrice(voucherDiscount) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="space-y-6 mb-8 max-h-[300px] overflow-y-auto no-scrollbar"
                            >
                                <div
                                    v-for="item in cartItems"
                                    :key="item.id"
                                    class="flex items-center gap-4 border-b border-white/10 pb-4 hover:bg-white/5 rounded-lg p-2 transition-all duration-300 group"
                                >
                                    <div
                                        class="w-12 h-12 rounded-xl overflow-hidden shrink-0 border border-white/20 shadow-lg group-hover:shadow-xl transition-all duration-300"
                                    >
                                        <img
                                            :src="
                                                '/storage/' + item.product.image
                                            "
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                        />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="font-bold text-sm line-clamp-1 text-gray-200 group-hover:text-white transition-colors"
                                        >
                                            {{ item.product.name }}
                                        </p>
                                        <p
                                            class="text-[10px] text-gray-500 font-black bg-gray-700 px-2 py-1 rounded-full inline-block mt-1"
                                        >
                                            SL: x{{ item.quantity }}
                                        </p>
                                    </div>
                                    <p
                                        class="font-black text-sm text-orange-400 bg-orange-900/30 px-2 py-1 rounded-lg"
                                    >
                                        {{
                                            formatPrice(
                                                item.product.price *
                                                    item.quantity,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>

                            <button
                                @click="submitOrder"
                                :disabled="form.processing"
                                class="w-full bg-gradient-to-r from-orange-500 to-pink-500 hover:from-orange-600 hover:to-pink-600 disabled:from-gray-600 disabled:to-gray-700 text-white font-black py-5 rounded-2xl shadow-xl shadow-orange-500/30 hover:shadow-2xl hover:shadow-orange-500/40 transition-all duration-300 active:scale-95 uppercase tracking-widest text-sm transform hover:-translate-y-1"
                            >
                                <span v-if="form.processing" class="flex items-center justify-center gap-2">
                                    <div class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></div>
                                    Đang xử lý...
                                </span>
                                <span v-else>
                                    🚀 Đặt hàng ngay 🛵
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="max-w-md mx-auto text-center py-20 bg-gradient-to-br from-white to-gray-50 rounded-[3rem] shadow-2xl border border-gray-200 hover:shadow-3xl transition-all duration-300"
            >
                <div class="text-6xl mb-6 animate-bounce">🎉</div>
                <h2 class="text-2xl font-black text-gray-800 uppercase italic bg-gradient-to-r from-gray-700 to-gray-900 bg-clip-text text-transparent">
                    Giỏ hàng trống!
                </h2>
                <p class="text-gray-600 mt-2 font-medium">
                    Đơn hàng của bạn đã được tiếp nhận hoặc giỏ hàng chưa có món
                    nào.
                </p>
                <Link
                    :href="route('home')"
                    class="mt-8 inline-block bg-gradient-to-r from-orange-500 to-pink-500 text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest hover:from-orange-600 hover:to-pink-600 transition-all duration-300 shadow-xl hover:shadow-2xl transform hover:scale-105 active:scale-95"
                >
                    🏠 Quay về trang chủ
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
* {
    font-family: "Inter", sans-serif;
}
</style>
