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

const localCartItems = ref([...(props.cartItems || [])]);
const showVoucherModal = ref(false);
const showMapModal = ref(false);

// useForm hỗ trợ quản lý lỗi (errors) từ Backend trả về
const form = useForm({
    address: props.user?.address || props.user?.address_detail || "",
    phone: props.user?.phone || "",
    note: "",
    payment_method: "cod",
    vnpay_bank_code: "VNBANK",
    voucher_code: "",
    latitude: props.user?.latitude || null,
    longitude: props.user?.longitude || null,
    shipper_tip: 0,
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
    const dLat = ((lat2 - lat1) * Math.PI) / 180;
    const dLng = ((lng2 - lng1) * Math.PI) / 180;
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos((lat1 * Math.PI) / 180) *
            Math.cos((lat2 * Math.PI) / 180) *
            Math.sin(dLng / 2) *
            Math.sin(dLng / 2);
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
    mapMessage.value =
        "Địa chỉ đã chọn sẽ hiển thị trên bản đồ sau khi được xác định.";
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
                [23.5, 110.0], // Đông Bắc Việt Nam
            ],
            maxBoundsViscosity: 1.0, // Không cho kéo ra ngoài
        }).setView([16.047079, 108.20623], 12); // Đà Nẵng, zoom level 12

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 18,
            minZoom: 6,
            attribution:
                "&copy; <a href='https://www.openstreetmap.org/copyright'>OpenStreetMap</a> contributors",
        }).addTo(map.value);

        // Thêm event listener cho click trên bản đồ
        map.value.on("click", async (e) => {
            const { lat, lng } = e.latlng;
            isUpdatingFromMap.value = true;

            try {
                const address = await reverseGeocode(lat, lng);
                form.address =
                    address || `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
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

const openMapModal = () => {
    showMapModal.value = true;
    setTimeout(() => {
        if (map.value) {
            map.value.invalidateSize();
        }
    }, 100);
};

const tryInitMapPosition = async () => {
    initMap();
    if (form.latitude && form.longitude) {
        updateMap(form.latitude, form.longitude, "Địa chỉ profile hiện tại");
        mapMessage.value = "Bản đồ đang hiển thị vị trí từ profile của bạn.";
    } else if (form.address) {
        await geocodeAddress(form.address);
    } else {
        mapMessage.value =
            "Nhập địa chỉ tại Đà Nẵng hoặc nhấn lấy vị trí hiện tại để hiển thị trên bản đồ.";
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
                mapMessage.value =
                    "Đã điền địa chỉ hiện tại và cập nhật lên bản đồ.";
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
    if (!localCartItems.value) return 0;
    return localCartItems.value.reduce(
        (sum, item) => sum + parseFloat(item.product.price) * item.quantity,
        0,
    );
});

const shippingFee = computed(() => {
    if (!distance.value) return 15000; // Fallback 15k nếu không có distance

    // Tính phí giao: 15k cơ bản + 3k/km từ km thứ 3
    const baseFee = 15000;
    const additionalFee = Math.max(0, distance.value - 2) * 3000;
    return baseFee + additionalFee;
});

const selectedVoucher = computed(() => {
    return (
        props.vouchers?.find((voucher) => voucher.code === form.voucher_code) ||
        null
    );
});

const voucherDiscount = computed(() => {
    if (!selectedVoucher.value) return 0;
    const value = parseFloat(selectedVoucher.value.discount_value);
    if (selectedVoucher.value.discount_type === "percent") {
        return Math.min(subtotal.value * (value / 100), subtotal.value);
    }
    return Math.min(subtotal.value, value);
});

const serviceFee = computed(() => 3000);
const packagingFee = computed(() => 0);

const total = computed(() => {
    return subtotal.value + shippingFee.value + serviceFee.value + packagingFee.value + form.shipper_tip - voucherDiscount.value;
});

const voucherLabel = computed(() => {
    if (!selectedVoucher.value) return "";
    return selectedVoucher.value.discount_type === "percent"
        ? `${selectedVoucher.value.discount_value}%`
        : formatPrice(selectedVoucher.value.discount_value);
});

const formatPrice = (p) =>
    new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(p);

const submitOrder = () => {
    if (localCartItems.value.length === 0) {
        alert("Giỏ hàng của bạn đang trống!");
        return;
    }
    form.post(route("orders.store"), {
        preserveScroll: true,
        onSuccess: () => {
            console.log("Đặt hàng thành công!");
        },
    });
};

const updateQuantity = async (index, change) => {
    const item = localCartItems.value[index];
    const newQuantity = item.quantity + change;
    
    if (newQuantity < 1) {
        if (confirm("Bạn có chắc chắn muốn xóa món này khỏi giỏ hàng?")) {
            removeItem(index);
        }
        return;
    }
    
    // Update local state optimistic
    item.quantity = newQuantity;
    
    try {
        await window.csrfFetch(`/cart/${item.id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: newQuantity })
        });
    } catch (error) {
        console.error("Failed to update cart:", error);
        // Revert on error
        item.quantity -= change;
    }
};

const removeItem = async (index) => {
    const item = localCartItems.value[index];
    const originalItems = [...localCartItems.value];
    
    // Optimistic UI
    localCartItems.value.splice(index, 1);
    
    try {
        await window.csrfFetch(`/cart/${item.id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        // If cart becomes empty, redirect to home
        if (localCartItems.value.length === 0) {
            window.location.href = route('home');
        }
    } catch (error) {
        console.error("Failed to remove item:", error);
        // Revert on error
        localCartItems.value = originalItems;
    }
};

onMounted(() => {
    loadSavedAddresses();
    tryInitMapPosition();

    // Tính khoảng cách ban đầu nếu có tọa độ sẵn
    const calculateInitialDistance = () => {
        if (
            form.latitude &&
            form.longitude &&
            props.restaurant?.latitude &&
            props.restaurant?.longitude
        ) {
            const dist = calculateDistance(
                props.restaurant.latitude,
                props.restaurant.longitude,
                form.latitude,
                form.longitude,
            );
            distance.value = dist;
            estimatedDeliveryTime.value = Math.ceil(5 + dist * 2);
        }
    };

    // Chờ một chút để map khởi tạo xong rồi tính khoảng cách
    setTimeout(calculateInitialDistance, 500);

    // Watcher để tự động geocode khi người dùng nhập địa chỉ
    watch(
        () => form.address,
        async (newAddress, oldAddress) => {
            if (
                newAddress &&
                newAddress !== oldAddress &&
                newAddress.length > 3 &&
                !newAddress.match(/^Lat:\s*-?\d+\.\d+,\s*Lng:\s*-?\d+\.\d+$/)
            ) {
                // Không geocode nếu là tọa độ
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
        { immediate: false },
    );

    // Tính khoảng cách và thời gian giao khi có tọa độ thay đổi
    watch(
        () => ({ custLat: form.latitude, custLng: form.longitude }),
        (newCoords) => {
            if (
                newCoords.custLat &&
                newCoords.custLng &&
                props.restaurant?.latitude &&
                props.restaurant?.longitude
            ) {
                const dist = calculateDistance(
                    props.restaurant.latitude,
                    props.restaurant.longitude,
                    newCoords.custLat,
                    newCoords.custLng,
                );
                distance.value = dist;
                // Thời gian dự kiến: 5 phút chuẩn bị + 2 phút/km
                estimatedDeliveryTime.value = Math.ceil(5 + dist * 2);
            }
        },
        { deep: true },
    );
});
</script>

<template>
    <Head title="Thanh toán - FoodXpress" />

    <div
        class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-pink-50 py-12"
    >
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
                                        <p
                                            class="text-xs font-black text-blue-600 uppercase tracking-widest mb-1"
                                        >
                                            Địa chỉ quán
                                        </p>
                                        <p class="font-bold text-gray-800">
                                            {{
                                                props.restaurant
                                                    .restaurant_name ||
                                                props.restaurant.name
                                            }}
                                        </p>
                                        <p class="text-sm text-gray-600 mt-1">
                                            {{ props.restaurant.address }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div
                                        v-if="distance"
                                        class="bg-white rounded-2xl p-4 border border-blue-200"
                                    >
                                        <p
                                            class="text-xs font-black text-blue-600 uppercase tracking-widest mb-2"
                                        >
                                            📏 Khoảng cách
                                        </p>
                                        <p
                                            class="text-2xl font-black text-blue-900"
                                        >
                                            {{ distance.toFixed(1) }} km
                                        </p>
                                    </div>
                                    <div
                                        v-if="estimatedDeliveryTime"
                                        class="bg-white rounded-2xl p-4 border border-blue-200"
                                    >
                                        <p
                                            class="text-xs font-black text-blue-600 uppercase tracking-widest mb-2"
                                        >
                                            ⏱️ Thời gian giao
                                        </p>
                                        <p
                                            class="text-2xl font-black text-blue-900"
                                        >
                                            {{ estimatedDeliveryTime }} phút
                                        </p>
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
                                <span
                                    class="bg-gradient-to-r from-gray-700 to-gray-900 bg-clip-text text-transparent"
                                    >Thông tin nhận hàng</span
                                >
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
                                                class="flex-1 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white border border-blue-300 rounded-2xl px-4 py-3 text-xs font-black uppercase tracking-widest transition-all duration-300 hover:from-blue-600 hover:to-blue-700 hover:shadow-lg disabled:opacity-50 transform hover:scale-105 active:scale-95"
                                            >
                                                📍 Lấy vị trí
                                            </button>
                                            <button
                                                type="button"
                                                @click="openMapModal"
                                                class="flex-1 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-2xl px-4 py-3 text-xs font-black uppercase tracking-widest transition-all duration-300 hover:from-emerald-600 hover:to-emerald-700 hover:shadow-lg transform hover:scale-105 active:scale-95"
                                            >
                                                🗺️ Chọn trên bản đồ
                                            </button>
                                            <button
                                                type="button"
                                                @click="addSavedAddress"
                                                class="flex-1 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-orange-500 to-pink-500 text-white rounded-2xl px-4 py-3 text-xs font-black uppercase tracking-widest transition-all duration-300 hover:from-orange-600 hover:to-pink-600 hover:shadow-lg transform hover:scale-105 active:scale-95"
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
                                        <div
                                            class="flex items-center justify-between mb-4"
                                        >
                                            <p
                                                class="text-xs font-black uppercase tracking-[0.2em] text-gray-500"
                                            >
                                                📍 Địa chỉ đã lưu
                                            </p>
                                            <span
                                                class="text-[10px] font-black uppercase text-orange-500 bg-orange-100 px-2 py-1 rounded-full"
                                            >
                                                Chọn nhanh
                                            </span>
                                        </div>
                                        <div class="space-y-3">
                                            <div
                                                v-for="(
                                                    address, index
                                                ) in savedAddresses"
                                                :key="address"
                                                class="flex items-start justify-between gap-3 p-4 rounded-3xl border border-gray-200 bg-white hover:border-orange-300 hover:shadow-md transition-all duration-300 group"
                                            >
                                                <button
                                                    type="button"
                                                    @click="
                                                        selectSavedAddress(
                                                            index,
                                                        )
                                                    "
                                                    class="text-left flex-1"
                                                >
                                                    <p
                                                        class="font-black text-sm text-gray-800 leading-tight group-hover:text-orange-600 transition-colors"
                                                    >
                                                        {{ address }}
                                                    </p>
                                                    <p
                                                        class="text-[10px] text-gray-500 uppercase tracking-[0.2em] mt-1"
                                                    >
                                                        {{
                                                            selectedSavedAddressIndex ===
                                                            index
                                                                ? "✅ Đã chọn"
                                                                : "👆 Chọn địa chỉ"
                                                        }}
                                                    </p>
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="
                                                        removeSavedAddress(
                                                            index,
                                                        )
                                                    "
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
                                    <div class="md:col-span-2 mt-4">
                                        <label
                                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4 mb-2 flex items-center gap-2"
                                            ><span>📝</span> Ghi chú đơn hàng (Tùy chọn)</label
                                        >
                                        <textarea
                                            v-model="form.note"
                                            rows="3"
                                            placeholder="Ví dụ: Cổng sau, đừng bấm chuông, cho thêm nhiều tương ớt..."
                                            class="w-full bg-gradient-to-r from-gray-50 to-gray-100 border-none rounded-2xl p-4 focus:ring-4 focus:ring-orange-200 transition-all duration-300 font-bold text-gray-800 shadow-inner hover:shadow-lg resize-none"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bản đồ đã được chuyển vào Modal ẩn -->

                        <div
                            class="bg-gradient-to-br from-white to-gray-50 rounded-[2.5rem] p-8 shadow-xl border border-gray-200 hover:shadow-2xl transition-all duration-300"
                        >
                            <h3
                                class="text-xl font-black text-gray-800 mb-6 flex items-center gap-3"
                            >
                                <span
                                    class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl flex items-center justify-center text-sm font-black shadow-lg"
                                    >02</span
                                >
                                <span
                                    class="bg-gradient-to-r from-gray-700 to-gray-900 bg-clip-text text-transparent"
                                    >💳 Thanh toán & Tip</span
                                >
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Cột Thanh toán -->
                                <div>
                                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Phương thức thanh toán</h4>
                                    <div class="space-y-3">
                                        <div
                                            @click="form.payment_method = 'cod'"
                                            :class="
                                                form.payment_method === 'cod'
                                                    ? 'border-orange-400 bg-gradient-to-r from-orange-50 to-pink-50 shadow-lg scale-[1.02]'
                                                    : 'border-gray-200 hover:border-orange-300 hover:shadow-md'
                                            "
                                            class="cursor-pointer border-2 p-4 rounded-2xl flex items-center gap-4 transition-all duration-300 group"
                                        >
                                            <div
                                                class="w-10 h-10 bg-gradient-to-r from-green-400 to-green-500 rounded-xl shadow-md flex items-center justify-center text-xl group-hover:scale-110 transition-transform"
                                            >
                                                💵
                                            </div>
                                            <span class="font-black text-sm text-gray-700 uppercase tracking-tight group-hover:text-orange-600 transition-colors"
                                                >Tiền mặt</span
                                            >
                                        </div>
                                        <div
                                            @click="form.payment_method = 'vnpay'"
                                            :class="
                                                form.payment_method === 'vnpay'
                                                    ? 'border-blue-400 bg-gradient-to-r from-blue-50 to-blue-50 shadow-lg scale-[1.02]'
                                                    : 'border-gray-200 hover:border-blue-300 hover:shadow-md'
                                            "
                                            class="cursor-pointer border-2 p-4 rounded-2xl flex items-center gap-4 transition-all duration-300 group"
                                        >
                                            <div
                                                class="w-10 h-10 bg-gradient-to-r from-blue-400 to-blue-500 rounded-xl shadow-md flex items-center justify-center text-xl group-hover:scale-110 transition-transform"
                                            >
                                                💳
                                            </div>
                                            <span class="font-black text-sm text-gray-700 uppercase tracking-tight group-hover:text-blue-600 transition-colors"
                                                >VNPay</span
                                            >
                                        </div>
                                    </div>
                                    
                                    <!-- VNPay Options -->
                                    <div v-if="form.payment_method === 'vnpay'" class="mt-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border border-blue-100 transition-all duration-300 animate-fade-in">
                                        <div class="grid grid-cols-3 gap-2">
                                            <label class="flex flex-col items-center justify-center p-2 rounded-xl border-2 cursor-pointer transition-all bg-white"
                                                :class="form.vnpay_bank_code === '' ? 'border-blue-500 shadow-md scale-105' : 'border-transparent hover:border-blue-200'">
                                                <input type="radio" v-model="form.vnpay_bank_code" value="" class="hidden">
                                                <span class="text-xl mb-1">📱</span>
                                                <span class="text-[9px] font-bold text-gray-700 text-center">Cổng QR</span>
                                            </label>
                                            <label class="flex flex-col items-center justify-center p-2 rounded-xl border-2 cursor-pointer transition-all bg-white"
                                                :class="form.vnpay_bank_code === 'VNBANK' ? 'border-blue-500 shadow-md scale-105' : 'border-transparent hover:border-blue-200'">
                                                <input type="radio" v-model="form.vnpay_bank_code" value="VNBANK" class="hidden">
                                                <span class="text-xl mb-1">🏧</span>
                                                <span class="text-[9px] font-bold text-gray-700 text-center">ATM Nội địa</span>
                                            </label>
                                            <label class="flex flex-col items-center justify-center p-2 rounded-xl border-2 cursor-pointer transition-all bg-white"
                                                :class="form.vnpay_bank_code === 'INTCARD' ? 'border-blue-500 shadow-md scale-105' : 'border-transparent hover:border-blue-200'">
                                                <input type="radio" v-model="form.vnpay_bank_code" value="INTCARD" class="hidden">
                                                <span class="text-xl mb-1">🌍</span>
                                                <span class="text-[9px] font-bold text-gray-700 text-center">Quốc tế</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Cột Tiền Tip -->
                                <div>
                                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Tip cho tài xế</h4>
                                    <div class="grid grid-cols-2 gap-3 mb-4">
                                        <button
                                            v-for="amount in [0, 10000, 15000, 20000]"
                                            :key="amount"
                                            type="button"
                                            @click="form.shipper_tip = amount"
                                            :class="
                                                form.shipper_tip === amount
                                                    ? 'border-yellow-400 bg-gradient-to-r from-yellow-50 to-yellow-100 shadow-md scale-105 ring-2 ring-yellow-400'
                                                    : 'border-gray-200 bg-white hover:border-yellow-300 hover:shadow-sm'
                                            "
                                            class="border-2 rounded-2xl py-3 text-center transition-all duration-300 transform flex flex-col items-center justify-center gap-1"
                                        >
                                            <span v-if="amount > 0" class="text-lg leading-none">💖</span>
                                            <span v-else class="text-lg leading-none opacity-50">💨</span>
                                            <span class="font-black text-sm text-gray-800 block">
                                                {{ amount === 0 ? 'Không' : (amount/1000) + 'k' }}
                                            </span>
                                        </button>
                                    </div>
                                    <div class="p-3 bg-yellow-50 rounded-xl border border-yellow-100">
                                        <p class="text-[10px] text-yellow-700 font-bold leading-relaxed">
                                            🌟 100% tiền Tip sẽ được chuyển trực tiếp cho tài xế giao hàng để cổ vũ tinh thần!
                                        </p>
                                    </div>
                                </div>
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

                            <!-- Voucher Section -->
                            <div class="mb-8 space-y-3">
                                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Khuyến mãi</p>
                                <button
                                    @click="showVoucherModal = true"
                                    class="w-full bg-white/10 hover:bg-white/20 border border-white/20 hover:border-orange-400 rounded-2xl p-4 flex items-center justify-between transition-all duration-300 group"
                                >
                                    <div class="flex items-center gap-3">
                                        <span class="text-xl group-hover:scale-110 transition-transform">🎟️</span>
                                        <div class="text-left">
                                            <p class="font-bold text-sm text-white group-hover:text-orange-400 transition-colors">
                                                {{ selectedVoucher ? 'Đã áp dụng mã' : 'Chọn mã giảm giá' }}
                                            </p>
                                            <p class="text-[10px] text-gray-400 font-medium">
                                                {{ selectedVoucher ? selectedVoucher.code : (props.vouchers?.length || 0) + ' mã khả dụng' }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="text-orange-400 font-black text-sm group-hover:translate-x-1 transition-transform">❯</span>
                                </button>
                                
                                <div
                                    v-if="selectedVoucher"
                                    class="rounded-xl border border-green-500/30 bg-green-500/10 p-3 flex items-center justify-between"
                                >
                                    <div>
                                        <p class="text-[10px] font-black text-green-400 uppercase tracking-widest">Đã áp dụng</p>
                                        <p class="text-xs font-bold text-green-300">
                                            Giảm <span class="font-black text-green-400">{{ voucherLabel }}</span>
                                        </p>
                                    </div>
                                    <button @click="form.voucher_code = ''" class="w-6 h-6 flex items-center justify-center bg-green-500/20 hover:bg-green-500/40 text-green-300 rounded-full font-bold transition-colors text-xs">
                                        ✕
                                    </button>
                                </div>
                            </div>

                            <!-- Phần tổng tiền hiển thị ở trên cùng -->
                            <div
                                class="bg-gradient-to-r from-orange-500/10 to-pink-500/10 rounded-2xl p-6 mb-8 border border-orange-500/20"
                            >
                                <div class="text-center">
                                    <p
                                        class="text-sm text-gray-300 font-bold uppercase tracking-widest mb-2"
                                    >
                                        💰 Tổng tiền
                                    </p>
                                    <p
                                        class="text-4xl font-black text-white mb-2"
                                    >
                                        {{ formatPrice(total) }}
                                    </p>
                                    <div
                                        class="text-xs text-gray-400 space-y-1"
                                    >
                                        <p>
                                            Tạm tính:
                                            {{ formatPrice(subtotal) }}
                                        </p>
                                        <p>
                                            Phí ship:
                                            {{ formatPrice(shippingFee) }}
                                        </p>
                                        <p>
                                            Phí dịch vụ:
                                            {{ formatPrice(serviceFee) }}
                                        </p>
                                        <p v-if="packagingFee > 0">
                                            Phí đóng gói:
                                            {{ formatPrice(packagingFee) }}
                                        </p>
                                        <p v-if="form.shipper_tip > 0" class="text-yellow-400">
                                            Tip tài xế:
                                            {{ formatPrice(form.shipper_tip) }}
                                        </p>
                                        <p
                                            v-if="voucherDiscount > 0"
                                            class="text-green-400"
                                        >
                                            Giảm: -{{
                                                formatPrice(voucherDiscount)
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="space-y-6 mb-8 max-h-[300px] overflow-y-auto no-scrollbar"
                            >
                                <div
                                    v-for="(item, index) in localCartItems"
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
                                        <div class="flex items-center gap-3 mt-1.5">
                                            <div class="flex items-center bg-gray-700/50 rounded-full border border-gray-600">
                                                <button @click="updateQuantity(index, -1)" class="w-6 h-6 flex items-center justify-center text-gray-300 hover:text-white hover:bg-gray-600 rounded-l-full transition-colors font-black">
                                                    -
                                                </button>
                                                <span class="text-[10px] text-white font-black px-2 w-6 text-center">
                                                    {{ item.quantity }}
                                                </span>
                                                <button @click="updateQuantity(index, 1)" class="w-6 h-6 flex items-center justify-center text-gray-300 hover:text-white hover:bg-gray-600 rounded-r-full transition-colors font-black">
                                                    +
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p
                                            class="font-black text-sm text-orange-400 bg-orange-900/30 px-2 py-1 rounded-lg inline-block"
                                        >
                                            {{
                                                formatPrice(
                                                    item.product.price *
                                                        item.quantity,
                                                )
                                            }}
                                        </p>
                                        <button @click="removeItem(index)" class="block ml-auto mt-1 text-[10px] text-red-400 hover:text-red-300 underline opacity-0 group-hover:opacity-100 transition-opacity">Xóa</button>
                                    </div>
                                </div>
                            </div>

                            <button
                                @click="submitOrder"
                                :disabled="form.processing"
                                class="w-full bg-gradient-to-r from-orange-500 to-pink-500 hover:from-orange-600 hover:to-pink-600 disabled:from-gray-600 disabled:to-gray-700 text-white font-black py-5 rounded-2xl shadow-xl shadow-orange-500/30 hover:shadow-2xl hover:shadow-orange-500/40 transition-all duration-300 active:scale-95 uppercase tracking-widest text-sm transform hover:-translate-y-1"
                            >
                                <span
                                    v-if="form.processing"
                                    class="flex items-center justify-center gap-2"
                                >
                                    <div
                                        class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"
                                    ></div>
                                    Đang xử lý...
                                </span>
                                <span v-else> 🚀 Đặt hàng ngay 🛵 </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="!cartItems || cartItems.length === 0"
                class="max-w-md mx-auto text-center py-20 bg-gradient-to-br from-white to-gray-50 rounded-[3rem] shadow-2xl border border-gray-200 hover:shadow-3xl transition-all duration-300"
            >
                <div class="text-6xl mb-6 animate-bounce">🎉</div>
                <h2
                    class="text-2xl font-black text-gray-800 uppercase italic bg-gradient-to-r from-gray-700 to-gray-900 bg-clip-text text-transparent"
                >
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

    <!-- Voucher Modal -->
    <div v-if="showVoucherModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/40 backdrop-blur-sm animate-fade-in">
        <div class="bg-white rounded-[2rem] w-full max-w-md shadow-2xl overflow-hidden animate-slide-up flex flex-col max-h-[80vh]">
            <div class="p-6 bg-gradient-to-r from-orange-500 to-pink-500 text-white flex justify-between items-center shrink-0">
                <h3 class="text-lg font-black tracking-tight uppercase flex items-center gap-2">
                    🎟️ Chọn Mã Giảm Giá
                </h3>
                <button @click="showVoucherModal = false" class="w-8 h-8 flex items-center justify-center bg-white/20 hover:bg-white/30 rounded-full transition-colors font-bold">✕</button>
            </div>
            
            <div class="p-6 overflow-y-auto space-y-4 flex-1 bg-gray-50">
                <div v-if="!props.vouchers || props.vouchers.length === 0" class="text-center py-8">
                    <p class="text-4xl mb-3">🥺</p>
                    <p class="text-gray-500 font-medium">Hiện chưa có mã giảm giá nào phù hợp cho đơn hàng này.</p>
                </div>
                
                <div v-else class="space-y-4">
                    <div 
                        v-for="voucher in props.vouchers" 
                        :key="voucher.id"
                        class="relative bg-white rounded-2xl border-2 overflow-hidden flex cursor-pointer transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg"
                        :class="form.voucher_code === voucher.code ? 'border-orange-500 shadow-md ring-2 ring-orange-200' : 'border-gray-200 hover:border-orange-300'"
                        @click="form.voucher_code = voucher.code; showVoucherModal = false;"
                    >
                        <!-- Left edge zigzag -->
                        <div class="w-16 bg-gradient-to-b from-orange-400 to-pink-500 flex flex-col items-center justify-center text-white p-2 relative shrink-0">
                            <span class="text-2xl rotate-[-90deg] font-black tracking-widest block transform origin-center whitespace-nowrap mt-4 mb-4">MÃ GIẢM</span>
                            <!-- Decor dots -->
                            <div class="absolute -left-2 top-1/2 -translate-y-1/2 flex flex-col gap-2">
                                <div v-for="i in 5" :key="i" class="w-4 h-4 bg-gray-50 rounded-full"></div>
                            </div>
                        </div>
                        
                        <div class="p-4 flex-1 flex flex-col justify-center">
                            <div class="flex justify-between items-start mb-1">
                                <p class="font-black text-gray-800 text-lg leading-tight">{{ voucher.code }}</p>
                                <span v-if="form.voucher_code === voucher.code" class="bg-orange-100 text-orange-600 text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-widest">Đang chọn</span>
                            </div>
                            <p class="text-sm font-bold text-gray-600 mb-2">
                                Giảm {{ voucher.discount_type === 'percent' ? voucher.discount_value + '%' : formatPrice(voucher.discount_value) }}
                            </p>
                            <p class="text-[10px] font-medium text-gray-400">
                                HSD: {{ new Date(voucher.expires_at).toLocaleDateString('vi-VN') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-4 bg-white border-t border-gray-100 shrink-0">
                <button 
                    @click="form.voucher_code = ''; showVoucherModal = false;"
                    class="w-full py-3 rounded-xl bg-gray-100 text-gray-600 font-bold hover:bg-gray-200 transition-colors"
                >
                    Bỏ chọn mã
                </button>
            </div>
        </div>
    </div>
    <!-- Map Modal -->
    <div v-if="showMapModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-md animate-fade-in">
        <div class="bg-white rounded-[2rem] w-full max-w-2xl shadow-2xl overflow-hidden animate-slide-up flex flex-col h-[80vh] md:h-[600px]">
            <div class="p-4 bg-gradient-to-r from-blue-500 to-indigo-600 text-white flex justify-between items-center shrink-0">
                <h3 class="text-lg font-black tracking-tight uppercase flex items-center gap-2">
                    📍 Chọn vị trí giao hàng
                </h3>
                <button @click="showMapModal = false" class="w-8 h-8 flex items-center justify-center bg-white/20 hover:bg-white/30 rounded-full transition-colors font-bold">✕</button>
            </div>
            
            <div class="flex-1 relative bg-gray-100">
                <div id="checkout-map" class="w-full h-full z-10"></div>
                
                <div class="absolute top-4 left-4 right-4 md:right-auto md:w-64 bg-white/95 backdrop-blur-xl rounded-xl p-4 shadow-2xl border border-white/20 z-20">
                    <p class="text-sm font-black text-gray-700 mb-2 flex items-center gap-2">
                        📍 <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Hướng dẫn</span>
                    </p>
                    <div class="text-[11px] text-gray-600 leading-tight space-y-1 font-medium">
                        <p>• Nhập địa chỉ bên ngoài để tự động định vị</p>
                        <p>• Click trên bản đồ để chọn vị trí</p>
                        <p>• Kéo thả marker để điều chỉnh chính xác</p>
                    </div>
                </div>
                
                <div v-if="isGeocoding" class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-30">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-2"></div>
                        <p class="text-sm font-bold text-gray-700">Đang tìm vị trí...</p>
                    </div>
                </div>
            </div>
            
            <div class="p-4 bg-white border-t border-gray-100 shrink-0">
                <button 
                    @click="showMapModal = false"
                    class="w-full py-4 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-black uppercase tracking-widest hover:shadow-lg transform hover:-translate-y-0.5 transition-all"
                >
                    Xác nhận vị trí này
                </button>
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
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fadeIn 0.2s ease-out;
}
.animate-slide-up {
    animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
