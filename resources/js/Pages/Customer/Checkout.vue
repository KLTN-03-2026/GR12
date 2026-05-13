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
    settings: Object,
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
    const baseFee = Number(props.settings?.base_shipping_fee) || 15000;
    const baseRadius = Number(props.settings?.base_radius_km) || 2;
    const extraFee = Number(props.settings?.extra_shipping_fee_per_km) || 3000;

    if (!distance.value) return baseFee; // Fallback nếu không có distance

    // Tính phí giao: Phí cơ bản + Phí cộng thêm mỗi km vượt mức
    const additionalFee = Math.max(0, distance.value - baseRadius) * extraFee;
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

    <div class="min-h-screen bg-[#f8fafc] py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div v-if="cartItems && cartItems.length > 0">
                <div class="flex items-center gap-3 mb-8">
                    <span class="text-3xl">🔒</span>
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight">Thanh toán an toàn</h1>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
                    <!-- Left Column: Forms -->
                    <div class="lg:col-span-8 space-y-6">
                        
                        <!-- 1. Restaurant Info -->
                        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 transition-shadow hover:shadow-md">
                            <div class="flex items-start gap-4">
                                <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center shrink-0 border border-orange-100">
                                    <span class="text-2xl">🏪</span>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="text-lg font-black text-gray-900">Thông tin quán ăn</h3>
                                        <span class="text-[10px] font-black uppercase text-orange-600 bg-orange-50 px-2 py-1 rounded-full tracking-widest">Chi nhánh chính</span>
                                    </div>
                                    <div v-if="props.restaurant" class="mt-3 bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                        <p class="font-bold text-gray-800 text-base mb-1">{{ props.restaurant.restaurant_name || props.restaurant.name }}</p>
                                        <p class="text-sm text-gray-500 mb-4">{{ props.restaurant.address }}</p>
                                        <div class="flex gap-6">
                                            <div v-if="distance" class="flex flex-col">
                                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Khoảng cách</span>
                                                <span class="font-black text-gray-800">{{ distance.toFixed(1) }} km</span>
                                            </div>
                                            <div v-if="estimatedDeliveryTime" class="flex flex-col">
                                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Thời gian giao</span>
                                                <span class="font-black text-gray-800">{{ estimatedDeliveryTime }} phút</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Shipping Address -->
                        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 transition-shadow hover:shadow-md">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-8 h-8 rounded-full bg-gray-900 text-white flex items-center justify-center font-black text-sm">1</div>
                                <h3 class="text-xl font-black text-gray-900">Thông tin nhận hàng</h3>
                            </div>

                            <div class="space-y-6 pl-0 sm:pl-11">
                                <!-- Address Input -->
                                <div>
                                    <label class="text-xs font-black text-gray-700 uppercase tracking-widest mb-2 block">Địa chỉ giao hàng <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input
                                            v-model="form.address"
                                            type="text"
                                            placeholder="Nhập địa chỉ của bạn..."
                                            :class="{'ring-2 ring-red-500 bg-red-50': form.errors.address, 'focus:ring-2 focus:ring-orange-500 focus:bg-white bg-gray-50 border-gray-200': !form.errors.address}"
                                            class="w-full rounded-2xl p-4 pl-12 transition-all duration-200 font-medium text-gray-800 border"
                                        />
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">📍</span>
                                    </div>
                                    <p v-if="form.errors.address" class="text-red-500 text-xs mt-2 font-bold">{{ form.errors.address }}</p>

                                    <!-- Quick Actions -->
                                    <div class="flex flex-wrap items-center gap-3 mt-3">
                                        <button type="button" @click="getCurrentLocation" :disabled="isFetchingLocation" class="text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-2 rounded-xl transition-colors flex items-center gap-1.5 disabled:opacity-50">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            Vị trí hiện tại
                                        </button>
                                        <button type="button" @click="openMapModal" class="text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-xl transition-colors flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                                            Bản đồ
                                        </button>
                                        <button type="button" @click="addSavedAddress" class="text-xs font-bold text-orange-600 bg-orange-50 hover:bg-orange-100 px-3 py-2 rounded-xl transition-colors ml-auto flex items-center gap-1.5">
                                            Lưu địa chỉ
                                        </button>
                                    </div>
                                    <p v-if="geoStatus" class="text-[10px] text-gray-500 mt-2 italic">{{ geoStatus }}</p>
                                </div>

                                <!-- Saved Addresses -->
                                <div v-if="savedAddresses.length > 0" class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-3">Sổ địa chỉ</p>
                                    <div class="space-y-2">
                                        <div v-for="(addr, idx) in savedAddresses" :key="addr" class="flex items-center justify-between bg-white p-3 rounded-xl border border-gray-200 hover:border-orange-300 transition-colors cursor-pointer group" @click="selectSavedAddress(idx)">
                                            <div class="flex items-center gap-3 overflow-hidden">
                                                <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0" :class="selectedSavedAddressIndex === idx ? 'border-orange-500' : 'border-gray-300'">
                                                    <div v-if="selectedSavedAddressIndex === idx" class="w-2 h-2 bg-orange-500 rounded-full"></div>
                                                </div>
                                                <span class="text-sm font-medium text-gray-700 truncate group-hover:text-orange-600 transition-colors">{{ addr }}</span>
                                            </div>
                                            <button @click.stop="removeSavedAddress(idx)" class="text-gray-400 hover:text-red-500 p-1 rounded-md transition-colors opacity-0 group-hover:opacity-100">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <!-- Phone -->
                                    <div>
                                        <label class="text-xs font-black text-gray-700 uppercase tracking-widest mb-2 block">Số điện thoại <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input
                                                v-model="form.phone"
                                                type="tel"
                                                placeholder="09..."
                                                :class="{'ring-2 ring-red-500 bg-red-50': form.errors.phone, 'focus:ring-2 focus:ring-orange-500 focus:bg-white bg-gray-50 border-gray-200': !form.errors.phone}"
                                                class="w-full rounded-2xl p-4 pl-12 transition-all duration-200 font-medium text-gray-800 border"
                                            />
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">📞</span>
                                        </div>
                                        <p v-if="form.errors.phone" class="text-red-500 text-xs mt-2 font-bold">{{ form.errors.phone }}</p>
                                    </div>
                                    
                                    <!-- Note -->
                                    <div>
                                        <label class="text-xs font-black text-gray-700 uppercase tracking-widest mb-2 flex items-center justify-between">
                                            <span>Ghi chú</span>
                                            <span class="text-[10px] text-gray-400 normal-case tracking-normal">Tùy chọn</span>
                                        </label>
                                        <textarea
                                            v-model="form.note"
                                            rows="1"
                                            placeholder="Đừng bấm chuông..."
                                            class="w-full rounded-2xl p-4 bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-orange-500 focus:bg-white transition-all duration-200 font-medium text-gray-800 resize-none h-[58px]"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Payment & Tip -->
                        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 transition-shadow hover:shadow-md">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-8 h-8 rounded-full bg-gray-900 text-white flex items-center justify-center font-black text-sm">2</div>
                                <h3 class="text-xl font-black text-gray-900">Thanh toán & Tip</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pl-0 sm:pl-11">
                                <!-- Payment Methods -->
                                <div>
                                    <label class="text-xs font-black text-gray-700 uppercase tracking-widest mb-3 block">Phương thức thanh toán</label>
                                    <div class="space-y-3">
                                        <!-- Tiền mặt -->
                                        <label 
                                            class="flex items-center p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200 group"
                                            :class="form.payment_method === 'cod' ? 'border-orange-500 bg-orange-50' : 'border-gray-100 hover:border-gray-300 bg-white'"
                                        >
                                            <input type="radio" v-model="form.payment_method" value="cod" class="hidden">
                                            <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xl mr-4 shrink-0">💵</div>
                                            <div class="flex-1">
                                                <p class="font-bold text-gray-900">Tiền mặt</p>
                                                <p class="text-xs text-gray-500">Thanh toán khi nhận hàng</p>
                                            </div>
                                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="form.payment_method === 'cod' ? 'border-orange-500 bg-orange-500' : 'border-gray-300'">
                                                <svg v-if="form.payment_method === 'cod'" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            </div>
                                        </label>

                                        <!-- VNPay -->
                                        <label 
                                            class="flex items-center p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200 group"
                                            :class="form.payment_method === 'vnpay' ? 'border-blue-500 bg-blue-50' : 'border-gray-100 hover:border-gray-300 bg-white'"
                                        >
                                            <input type="radio" v-model="form.payment_method" value="vnpay" class="hidden">
                                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl mr-4 shrink-0">💳</div>
                                            <div class="flex-1">
                                                <p class="font-bold text-gray-900">VNPay</p>
                                                <p class="text-xs text-gray-500">Ví điện tử, thẻ ATM/Visa</p>
                                            </div>
                                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="form.payment_method === 'vnpay' ? 'border-blue-500 bg-blue-500' : 'border-gray-300'">
                                                <svg v-if="form.payment_method === 'vnpay'" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            </div>
                                        </label>

                                        <!-- Ví FoodXpress -->
                                        <label 
                                            class="flex items-center p-4 rounded-2xl border-2 transition-all duration-200 group relative overflow-hidden"
                                            :class="[
                                                user.wallet_balance >= total 
                                                    ? (form.payment_method === 'wallet' ? 'border-orange-500 bg-orange-50 cursor-pointer' : 'border-gray-100 hover:border-gray-300 bg-white cursor-pointer') 
                                                    : 'border-gray-100 bg-gray-50 opacity-70 cursor-not-allowed'
                                            ]"
                                        >
                                            <input type="radio" v-model="form.payment_method" value="wallet" class="hidden" :disabled="user.wallet_balance < total">
                                            <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-xl mr-4 shrink-0">👛</div>
                                            <div class="flex-1">
                                                <p class="font-bold text-gray-900 flex items-center gap-2">
                                                    Ví FoodXpress 
                                                    <span class="text-[10px] font-black uppercase text-white bg-orange-500 px-2 py-0.5 rounded-full">Mượt mà</span>
                                                </p>
                                                <p class="text-xs font-medium" :class="user.wallet_balance >= total ? 'text-gray-500' : 'text-red-500'">
                                                    Số dư: {{ formatPrice(user.wallet_balance || 0) }}
                                                    <span v-if="user.wallet_balance < total"> (Không đủ)</span>
                                                </p>
                                            </div>
                                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="form.payment_method === 'wallet' ? 'border-orange-500 bg-orange-500' : 'border-gray-300'">
                                                <svg v-if="form.payment_method === 'wallet'" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            </div>
                                            <a v-if="user.wallet_balance < total" href="/wallet" class="absolute inset-0 z-10 flex items-center justify-center bg-white/60 backdrop-blur-[1px] opacity-0 group-hover:opacity-100 transition-opacity">
                                                <span class="text-sm font-bold text-orange-600 underline">Nạp tiền vào ví</span>
                                            </a>
                                        </label>
                                    </div>

                                    <!-- VNPay Sub-options -->
                                    <div v-if="form.payment_method === 'vnpay'" class="mt-3 p-4 bg-blue-50 rounded-2xl border border-blue-100">
                                        <p class="text-[10px] font-black uppercase text-blue-800 mb-2">Chọn cổng thanh toán VNPay</p>
                                        <div class="grid grid-cols-3 gap-2">
                                            <label class="flex flex-col items-center justify-center py-2 px-1 rounded-xl border border-transparent cursor-pointer transition-all bg-white"
                                                :class="form.vnpay_bank_code === '' ? 'ring-2 ring-blue-500 shadow-sm' : 'hover:border-blue-200'">
                                                <input type="radio" v-model="form.vnpay_bank_code" value="" class="hidden">
                                                <span class="text-lg mb-0.5">📱</span>
                                                <span class="text-[10px] font-bold text-gray-700 text-center">Cổng QR</span>
                                            </label>
                                            <label class="flex flex-col items-center justify-center py-2 px-1 rounded-xl border border-transparent cursor-pointer transition-all bg-white"
                                                :class="form.vnpay_bank_code === 'VNBANK' ? 'ring-2 ring-blue-500 shadow-sm' : 'hover:border-blue-200'">
                                                <input type="radio" v-model="form.vnpay_bank_code" value="VNBANK" class="hidden">
                                                <span class="text-lg mb-0.5">🏧</span>
                                                <span class="text-[10px] font-bold text-gray-700 text-center">ATM Nội địa</span>
                                            </label>
                                            <label class="flex flex-col items-center justify-center py-2 px-1 rounded-xl border border-transparent cursor-pointer transition-all bg-white"
                                                :class="form.vnpay_bank_code === 'INTCARD' ? 'ring-2 ring-blue-500 shadow-sm' : 'hover:border-blue-200'">
                                                <input type="radio" v-model="form.vnpay_bank_code" value="INTCARD" class="hidden">
                                                <span class="text-lg mb-0.5">🌍</span>
                                                <span class="text-[10px] font-bold text-gray-700 text-center">Quốc tế</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tip -->
                                <div>
                                    <label class="text-xs font-black text-gray-700 uppercase tracking-widest mb-3 flex items-center justify-between">
                                        <span>Tip cho tài xế</span>
                                        <span class="text-[10px] text-gray-400 normal-case tracking-normal">100% cho shipper</span>
                                    </label>
                                    <div class="grid grid-cols-4 gap-2">
                                        <button
                                            v-for="amount in [0, 10000, 15000, 20000]"
                                            :key="amount"
                                            type="button"
                                            @click="form.shipper_tip = amount"
                                            :class="form.shipper_tip === amount ? 'bg-gray-900 text-white shadow-md' : 'bg-white border border-gray-200 text-gray-700 hover:border-gray-300'"
                                            class="rounded-xl py-3 text-center transition-all font-bold text-sm"
                                        >
                                            {{ amount === 0 ? 'Không' : (amount/1000) + 'k' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Order Summary (Sticky) -->
                    <div class="lg:col-span-4 relative">
                        <div class="sticky top-24 bg-white rounded-3xl p-6 shadow-xl border border-gray-100 flex flex-col max-h-[calc(100vh-8rem)]">
                            <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
                                🧾 Tóm tắt đơn hàng
                            </h3>

                            <!-- Voucher Selector -->
                            <div class="mb-6">
                                <button
                                    @click="showVoucherModal = true"
                                    class="w-full bg-orange-50 hover:bg-orange-100 border border-orange-100 rounded-2xl p-4 flex items-center justify-between transition-colors group"
                                >
                                    <div class="flex items-center gap-3">
                                        <span class="text-xl">🎟️</span>
                                        <div class="text-left">
                                            <p class="font-bold text-sm text-orange-900 group-hover:text-orange-700 transition-colors">
                                                {{ selectedVoucher ? 'Đã áp dụng mã' : 'Chọn mã giảm giá' }}
                                            </p>
                                            <p class="text-xs text-orange-600/70 font-medium">
                                                {{ selectedVoucher ? selectedVoucher.code : (props.vouchers?.length || 0) + ' mã khả dụng' }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="text-orange-500 font-bold">❯</span>
                                </button>
                                
                                <!-- Active Voucher Tag -->
                                <div v-if="selectedVoucher" class="mt-2 bg-green-50 border border-green-200 rounded-xl p-3 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-green-500">✅</span>
                                        <div>
                                            <p class="text-xs font-bold text-green-700">Giảm {{ voucherLabel }}</p>
                                            <p class="text-[10px] text-green-600">{{ selectedVoucher.code }}</p>
                                        </div>
                                    </div>
                                    <button @click="form.voucher_code = ''" class="w-6 h-6 flex items-center justify-center bg-green-100 hover:bg-green-200 text-green-700 rounded-full text-xs font-bold transition-colors">✕</button>
                                </div>
                            </div>

                            <!-- Cart Items (Scrollable) -->
                            <div class="flex-1 overflow-y-auto no-scrollbar mb-6 pr-1 space-y-4">
                                <div v-for="(item, index) in localCartItems" :key="item.id" class="flex gap-3 group">
                                    <div class="w-14 h-14 rounded-xl overflow-hidden shrink-0 border border-gray-100">
                                        <img :src="'/storage/' + item.product.image" class="w-full h-full object-cover" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-sm text-gray-800 line-clamp-2 leading-tight mb-1">{{ item.product.name }}</p>
                                        <div class="flex items-center justify-between">
                                            <p class="font-black text-sm text-orange-600">{{ formatPrice(item.product.price) }}</p>
                                            <div class="flex items-center bg-gray-100 rounded-lg">
                                                <button @click="updateQuantity(index, -1)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-gray-900 font-bold text-lg leading-none">-</button>
                                                <span class="text-xs font-black px-1 min-w-[20px] text-center text-gray-800">{{ item.quantity }}</span>
                                                <button @click="updateQuantity(index, 1)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-gray-900 font-bold text-sm leading-none">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bill Details (Receipt style) -->
                            <div class="border-t border-dashed border-gray-200 pt-4 mb-6 space-y-2 shrink-0">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Tạm tính</span>
                                    <span class="font-bold text-gray-800">{{ formatPrice(subtotal) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Phí giao hàng</span>
                                    <span class="font-bold text-gray-800">{{ formatPrice(shippingFee) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Phí dịch vụ</span>
                                    <span class="font-bold text-gray-800">{{ formatPrice(serviceFee) }}</span>
                                </div>
                                <div v-if="packagingFee > 0" class="flex justify-between text-sm">
                                    <span class="text-gray-500">Phí đóng gói</span>
                                    <span class="font-bold text-gray-800">{{ formatPrice(packagingFee) }}</span>
                                </div>
                                <div v-if="form.shipper_tip > 0" class="flex justify-between text-sm">
                                    <span class="text-gray-500">Tip tài xế</span>
                                    <span class="font-bold text-gray-800">{{ formatPrice(form.shipper_tip) }}</span>
                                </div>
                                <div v-if="voucherDiscount > 0" class="flex justify-between text-sm">
                                    <span class="text-green-600 font-medium">Khuyến mãi</span>
                                    <span class="font-bold text-green-600">-{{ formatPrice(voucherDiscount) }}</span>
                                </div>
                                <div class="pt-3 border-t border-gray-100 flex justify-between items-end">
                                    <span class="text-base font-black text-gray-900 uppercase tracking-tight">Tổng cộng</span>
                                    <span class="text-2xl font-black text-red-600">{{ formatPrice(total) }}</span>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button
                                @click="submitOrder"
                                :disabled="form.processing"
                                class="w-full shrink-0 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 disabled:opacity-50 text-white font-black py-4 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-200 uppercase tracking-widest text-sm flex items-center justify-center gap-2"
                            >
                                <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>{{ form.processing ? 'Đang xử lý...' : 'Đặt hàng ngay' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty Cart -->
            <div v-if="!cartItems || cartItems.length === 0" class="max-w-lg mx-auto text-center py-24">
                <div class="w-32 h-32 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-gray-100 shadow-inner">
                    <span class="text-5xl opacity-50">🛒</span>
                </div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight mb-2">Giỏ hàng trống</h2>
                <p class="text-gray-500 mb-8 font-medium">Có vẻ như bạn chưa chọn món nào. Hãy khám phá thực đơn của chúng tôi nhé!</p>
                <Link
                    :href="route('home')"
                    class="inline-flex items-center gap-2 bg-gray-900 text-white px-8 py-4 rounded-2xl font-bold hover:bg-gray-800 transition-colors shadow-lg hover:shadow-xl"
                >
                    <span>Khám phá món ngon</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
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
    <div v-show="showMapModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-md animate-fade-in">
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
