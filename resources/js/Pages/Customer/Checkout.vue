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

const shippingFee = 15000;

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
    return subtotal.value + shippingFee - voucherDiscount.value;
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
});
</script>

<template>
    <Head title="Thanh toán - FoodXpress" />

    <div class="min-h-screen bg-[#f8f9fb] py-12">
        <div class="max-w-6xl mx-auto px-4">
            <div v-if="cartItems && cartItems.length > 0">
                <h1
                    class="text-4xl font-black text-gray-900 mb-10 tracking-tighter italic"
                >
                    Xác nhận đơn hàng 🚀
                </h1>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        <div
                            class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100"
                        >
                            <h3
                                class="text-xl font-black text-gray-800 mb-6 flex items-center gap-2"
                            >
                                <span
                                    class="w-8 h-8 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center text-sm"
                                    >01</span
                                >
                                Thông tin nhận hàng
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
                                        class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-4 focus:ring-orange-100 transition-all font-bold text-gray-800"
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
                                                class="inline-flex items-center justify-center gap-2 bg-white text-gray-700 border border-gray-200 rounded-2xl px-4 py-3 text-xs font-black uppercase tracking-widest transition hover:bg-gray-50 disabled:opacity-50"
                                            >
                                                📍 Lấy vị trí hiện tại
                                            </button>
                                            <button
                                                type="button"
                                                @click="addSavedAddress"
                                                class="inline-flex items-center justify-center gap-2 bg-orange-500 text-white rounded-2xl px-4 py-3 text-xs font-black uppercase tracking-widest transition hover:bg-orange-600"
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
                                        class="bg-gray-50 rounded-[2rem] border border-gray-100 p-4 mt-4"
                                    >
                                        <div class="flex items-center justify-between mb-4">
                                            <p class="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                                Địa chỉ đã lưu
                                            </p>
                                            <span class="text-[10px] font-black uppercase text-orange-500">
                                                Chọn nhanh
                                            </span>
                                        </div>
                                        <div class="space-y-3">
                                            <div
                                                v-for="(address, index) in savedAddresses"
                                                :key="address"
                                                class="flex items-start justify-between gap-3 p-4 rounded-3xl border border-gray-200 bg-white"
                                            >
                                                <button
                                                    type="button"
                                                    @click="selectSavedAddress(index)"
                                                    class="text-left flex-1"
                                                >
                                                    <p class="font-black text-sm text-gray-800 leading-tight">
                                                        {{ address }}
                                                    </p>
                                                    <p class="text-[10px] text-gray-500 uppercase tracking-[0.2em] mt-1">
                                                        {{ selectedSavedAddressIndex === index ? 'Đã chọn' : 'Chọn địa chỉ' }}
                                                    </p>
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="removeSavedAddress(index)"
                                                    class="text-[10px] font-black uppercase tracking-[0.2em] text-red-500 hover:text-red-600"
                                                >
                                                    Xóa
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
                                            class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-4 focus:ring-orange-100 transition-all font-bold text-gray-800"
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
                                            class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-4 focus:ring-orange-100 transition-all font-bold text-gray-800"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                    <div
                        class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100"
                    >
                        <h3
                            class="text-xl font-black text-gray-800 mb-6 flex items-center gap-2"
                        >
                            <span
                                class="w-8 h-8 bg-green-100 text-green-600 rounded-lg flex items-center justify-center text-sm"
                                >02</span
                            >
                            Bản đồ giao hàng
                        </h3>

                        <div class="h-80 rounded-[2rem] overflow-hidden border border-gray-200 relative">
                            <div id="checkout-map" class="w-full h-full"></div>
                            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm rounded-lg p-3 shadow-lg">
                                <p class="text-xs font-bold text-gray-700 mb-1">📍 Bản đồ Đà Nẵng:</p>
                                <p class="text-[10px] text-gray-600 leading-tight">
                                    • Nhập địa chỉ để tự động định vị<br>
                                    • Click trên bản đồ để chọn vị trí giao hàng
                                </p>
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
                        class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100"
                    >
                        <h3
                            class="text-xl font-black text-gray-800 mb-6 flex items-center gap-2"
                        >
                                <span
                                    class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-sm"
                                    >03</span
                                >
                                Phương thức thanh toán
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div
                                    @click="form.payment_method = 'cod'"
                                    :class="
                                        form.payment_method === 'cod'
                                            ? 'border-orange-500 bg-orange-50/50'
                                            : 'border-gray-100'
                                    "
                                    class="cursor-pointer border-2 p-4 rounded-2xl flex items-center gap-4 transition-all group"
                                >
                                    <div
                                        class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-xl"
                                    >
                                        💵
                                    </div>
                                    <span
                                        class="font-black text-sm text-gray-700 uppercase tracking-tight"
                                        >Thanh toán khi nhận hàng</span
                                    >
                                </div>
                                <div
                                    class="cursor-not-allowed border-2 border-gray-50 p-4 rounded-2xl flex items-center gap-4 opacity-40"
                                >
                                    <div
                                        class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-xl"
                                    >
                                        💳
                                    </div>
                                    <span
                                        class="font-black text-sm text-gray-700 uppercase tracking-tight"
                                        >Ví MoMo (Sắp có)</span
                                    >
                                </div>
                            </div>
                        </div>
                    <div
                        class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 mt-6"
                    >
                        <h3
                            class="text-xl font-black text-gray-800 mb-6 flex items-center gap-2"
                        >
                            <span
                                class="w-8 h-8 bg-green-100 text-green-600 rounded-lg flex items-center justify-center text-sm"
                                >04</span
                            >
                            Mã giảm giá
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label
                                    class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2 block"
                                    >Chọn voucher</label
                                <select
                                    v-model="form.voucher_code"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 p-4 text-sm outline-none transition focus:border-orange-500"
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

                            <div v-if="selectedVoucher" class="rounded-3xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
                                Áp dụng voucher <span class="font-black">{{ selectedVoucher.code }}</span> - Giảm {{ voucherLabel }}.
                            </div>
                            <div v-else class="rounded-3xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                                Chọn voucher để nhận khuyến mãi khi thanh toán.
                            </div>
                        </div>
                    </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div
                            class="bg-gray-900 rounded-[2.5rem] p-8 text-white sticky top-24 shadow-2xl"
                        >
                            <h3
                                class="text-xl font-black mb-8 italic uppercase text-orange-500"
                            >
                                Tóm tắt đơn hàng
                            </h3>

                            <div
                                class="space-y-6 mb-8 max-h-[300px] overflow-y-auto no-scrollbar"
                            >
                                <div
                                    v-for="item in cartItems"
                                    :key="item.id"
                                    class="flex items-center gap-4 border-b border-white/5 pb-4"
                                >
                                    <div
                                        class="w-12 h-12 rounded-xl overflow-hidden shrink-0 border border-white/10"
                                    >
                                        <img
                                            :src="
                                                '/storage/' + item.product.image
                                            "
                                            class="w-full h-full object-cover"
                                        />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="font-bold text-sm line-clamp-1 text-gray-200"
                                        >
                                            {{ item.product.name }}
                                        </p>
                                        <p
                                            class="text-[10px] text-gray-500 font-black"
                                        >
                                            SL: x{{ item.quantity }}
                                        </p>
                                    </div>
                                    <p
                                        class="font-black text-sm text-orange-400"
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

                            <div
                                class="border-t border-white/10 pt-6 space-y-4"
                            >
                                <div class="flex justify-between text-sm">
                                    <span
                                        class="text-gray-500 font-bold uppercase tracking-widest text-[10px]"
                                        >Tạm tính</span
                                    >
                                    <span class="font-bold text-gray-300">{{
                                        formatPrice(subtotal)
                                    }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span
                                        class="text-gray-500 font-bold uppercase tracking-widest text-[10px]"
                                        >Phí ship</span
                                    >
                                    <span class="font-bold text-gray-300">{{
                                        formatPrice(shippingFee)
                                    }}</span>
                                </div>
                                <div v-if="voucherDiscount > 0" class="flex justify-between text-sm">
                                    <span
                                        class="text-gray-500 font-bold uppercase tracking-widest text-[10px]"
                                        >Giảm giá</span
                                    >
                                    <span class="font-bold text-green-300">-{{ formatPrice(voucherDiscount) }}</span>
                                </div>
                                <div
                                    class="flex justify-between items-end pt-4 border-t border-white/5"
                                >
                                    <span
                                        class="text-orange-500 font-black uppercase tracking-tighter text-lg italic"
                                        >Tổng cộng</span
                                    >
                                    <span
                                        class="text-3xl font-black tracking-tighter text-white"
                                        >{{ formatPrice(total) }}</span
                                    >
                                </div>
                            </div>

                            <button
                                @click="submitOrder"
                                :disabled="form.processing"
                                class="w-full mt-10 bg-orange-500 hover:bg-orange-600 disabled:bg-gray-700 text-white font-black py-5 rounded-2xl shadow-xl shadow-orange-500/20 transition-all active:scale-95 uppercase tracking-widest text-sm"
                            >
                                {{
                                    form.processing
                                        ? "Đang xử lý..."
                                        : "Đặt hàng ngay 🛵"
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="max-w-md mx-auto text-center py-20 bg-white rounded-[3rem] shadow-sm border border-gray-100"
            >
                <div class="text-6xl mb-6">🎉</div>
                <h2 class="text-2xl font-black text-gray-800 uppercase italic">
                    Giỏ hàng trống!
                </h2>
                <p class="text-gray-500 mt-2">
                    Đơn hàng của bạn đã được tiếp nhận hoặc giỏ hàng chưa có món
                    nào.
                </p>
                <Link
                    :href="route('home')"
                    class="mt-8 inline-block bg-orange-500 text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest hover:shadow-lg transition-all"
                >
                    Quay về trang chủ
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
