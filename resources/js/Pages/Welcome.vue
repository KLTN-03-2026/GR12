<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, onMounted, nextTick } from "vue";
import ProductDetailModal from "@/Components/ProductDetailModal.vue";
import debounce from "lodash/debounce";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

// Import Swiper Vue.js components & modules
import { Swiper, SwiperSlide } from "swiper/vue";
import { Autoplay, EffectFade, FreeMode, Pagination } from "swiper/modules";
import "swiper/css";
import "swiper/css/effect-fade";
import "swiper/css/free-mode";
import "swiper/css/pagination";

const props = defineProps({
    auth: Object,
    restaurants: Array,
    products: Array,
    categories: Array,
    filters: Object,
    vouchers: Array,
});

defineOptions({ layout: GuestLayout });

const copyVoucher = (code) => {
    navigator.clipboard.writeText(code).then(() => {
        showLocalToast.value = `Đã lưu mã: ${code}`;
        setTimeout(() => showLocalToast.value = '', 3000);
    });
};
const showLocalToast = ref('');
// --- 1. SLIDER DATA ---
const slides = [
    {
        image: "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1600&q=80",
        slogan: "Hương vị chuẩn gu",
        sub: "Giao tận cửa nhanh chóng trong 20 phút!",
    },
    {
        image: "https://images.unsplash.com/photo-1543353071-10c8ba85a904?auto=format&fit=crop&w=1600&q=80",
        slogan: "Cơm ngon nóng hổi",
        sub: "Bữa trưa trọn vị, tràn đầy năng lượng cho ngày mới.",
    },
    {
        image: "https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=1600&q=80",
        slogan: "Tiệc tại gia",
        sub: "Đặt món ngay hôm nay, nhận ngàn ưu đãi hấp dẫn.",
    },
];

// --- 2. TÌM KIẾM & LỌC REAL-TIME ---
const search = ref(props.filters.search || "");
const selectedCat = ref(props.filters.category_id || "");
const activeTab = ref(props.filters.active_tab || "Tất cả");
const priceRange = ref(props.filters.price_range || "");
const deliveryType = ref(props.filters.delivery_type || "");
const showMap = ref(true);
const showSearch = ref(true);
const searchTabs = ["Tất cả", "Món chính", "Đồ uống", "Tráng miệng", "Combo"];

const updateFilters = debounce(() => {
    router.get(
        route("home"),
        {
            search: search.value,
            category_id: selectedCat.value,
            active_tab: activeTab.value,
            price_range: priceRange.value,
            delivery_type: deliveryType.value,
        },
        { preserveState: true, replace: true, preserveScroll: true },
    );
}, 400);

watch([search, selectedCat, activeTab, priceRange, deliveryType], () => {
    updateFilters();
});

// --- 3. KHỞI TẠO BẢN ĐỒ & ĐỊNH VỊ ---
const userLocation = ref(null);
let map = null;
const markersGroup = ref(null);

const openDirections = (targetLat, targetLng) => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const url = `https://www.openstreetmap.org/directions?engine=graphhopper_car&route=${pos.coords.latitude},${pos.coords.longitude};${targetLat},${targetLng}`;
                window.open(url, "_blank");
            },
            () => {
                const url = `https://www.openstreetmap.org/directions?engine=graphhopper_car&route=${targetLat},${targetLng}`;
                window.open(url, "_blank");
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
            }
        );
    } else {
        const url = `https://www.openstreetmap.org/directions?engine=graphhopper_car&route=${targetLat},${targetLng}`;
        window.open(url, "_blank");
    }
};

const initMap = () => {
    const mapContainer = document.getElementById("map-home");
    if (!mapContainer) return;

    map = L.map("map-home", {
        scrollWheelZoom: true,
        zoomControl: true,
        zoomControlOptions: {
            position: 'topright'
        }
    }).setView([16.061, 108.2158], 13);

    // Sử dụng tile layer CartoDB Voyager cho tốc độ tải siêu nhanh và giao diện sáng, nhẹ
    L.tileLayer("https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png", {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        subdomains: "abcd",
        maxZoom: 20,
        minZoom: 3,
    }).addTo(map);

    markersGroup.value = L.featureGroup();

    // Tối ưu hóa Icon: Giảm DOM thừa, loại bỏ hiệu ứng ping liên tục để tránh lag khi có nhiều marker
    const foodIcon = L.divIcon({
        html: `
            <div class="relative bg-orange-500 w-8 h-8 rounded-2xl flex items-center justify-center shadow-lg border-2 border-white text-white hover:scale-110 transition-transform duration-200">
                <span class="text-sm">🍕</span>
            </div>
        `,
        className: "custom-food-marker",
        iconSize: [32, 32],
        iconAnchor: [16, 16],
    });

    props.restaurants.forEach((shop) => {
        if (shop.latitude && shop.longitude) {
            const marker = L.marker([shop.latitude, shop.longitude], {
                icon: foodIcon,
            }).bindPopup(
                `
                    <div class="p-4 font-sans min-w-[220px] max-w-[300px]">
                        <h3 class="font-bold text-gray-800 text-base mb-2">${shop.restaurant_name || shop.name}</h3>
                        <p class="text-sm text-gray-600 mb-3">📍 ${shop.address}</p>
                        <div class="flex gap-2">
                            <a href="/restaurant-menu/${shop.id}" class="flex-1 text-center bg-orange-500 text-white py-2 px-3 rounded-lg text-sm font-semibold hover:bg-orange-600 transition-colors no-underline">Xem menu</a>
                            <button data-direction-lat="${shop.latitude}" data-direction-lng="${shop.longitude}" class="open-directions-btn bg-blue-500 text-white py-2 px-3 rounded-lg text-sm font-semibold hover:bg-blue-600 transition-colors">Chỉ đường</button>
                        </div>
                    </div>
                `,
                { closeButton: true, offset: [0, -16], className: 'custom-popup' }
            );

            marker.on('popupopen', (e) => {
                const btn = e.popup._container.querySelector('.open-directions-btn');
                if (btn) {
                    btn.addEventListener('click', () => {
                        const lat = btn.getAttribute('data-direction-lat');
                        const lng = btn.getAttribute('data-direction-lng');
                        openDirections(lat, lng);
                    });
                }
            });

            markersGroup.value.addLayer(marker);
        }
    });

    markersGroup.value.addTo(map);

    // Lấy vị trí người dùng với độ chính xác cao hơn
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude, accuracy } = position.coords;
                userLocation.value = [latitude, longitude];

                console.log(`Vị trí xác định với độ chính xác: ${accuracy} mét`);

                const userIcon = L.divIcon({
                    html: `
                        <div class="relative">
                            <div class="absolute -top-2 -left-2 w-8 h-8 bg-blue-500/40 rounded-full animate-ping"></div>
                            <div class="relative bg-blue-600 w-5 h-5 rounded-full border-2 border-white shadow-lg"></div>
                        </div>
                    `,
                    className: "user-location-marker",
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });

                const userMarker = L.marker([latitude, longitude], { icon: userIcon })
                    .addTo(map)
                    .bindTooltip("Bạn đang ở đây!", {
                        permanent: false,
                        direction: "top",
                        className: "user-tooltip",
                    });

                // Tự động zoom và center
                if (markersGroup.value.getLayers().length > 0) {
                    const bounds = markersGroup.value.getBounds();
                    bounds.extend([latitude, longitude]);
                    map.fitBounds(bounds, { padding: [50, 50], maxZoom: 16 });
                } else {
                    map.setView([latitude, longitude], 15);
                }

                // Gửi vị trí lên server
                router.post(
                    route("update-location"),
                    { latitude, longitude },
                    { preserveScroll: true, preserveState: true },
                );
            },
            (err) => {
                console.warn("Không thể định vị:", err.message);
                // Fallback: hiển thị tất cả quán ăn
                if (markersGroup.value.getLayers().length > 0) {
                    map.fitBounds(markersGroup.value.getBounds(), { padding: [50, 50] });
                }
            },
            {
                enableHighAccuracy: true, // Yêu cầu độ chính xác cao
                timeout: 10000,
                maximumAge: 300000 // Cache vị trí trong 5 phút
            }
        );
    }

    // Thêm CSS cho popup
    const style = document.createElement('style');
    style.textContent = `
        .custom-popup .leaflet-popup-content-wrapper {
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .custom-popup .leaflet-popup-content {
            margin: 0;
        }
        .user-tooltip {
            background: rgba(37, 99, 235, 0.9) !important;
            color: white !important;
            border: none !important;
            font-weight: bold !important;
        }
    `;
    document.head.appendChild(style);

    setTimeout(() => map.invalidateSize(), 500);
};

onMounted(async () => {
    if (props.auth.user) {
        await nextTick();
        initMap();
    }
});

// --- 4. XỬ LÝ MODAL & GIỎ HÀNG ---
const isModalOpen = ref(false);
const selectedProduct = ref(null);

const openProductModal = (product) => {
    selectedProduct.value = product;
    isModalOpen.value = true;
};

const handleAddToCart = (data) => {
    router.post(route("cart.add"), data, {
        preserveScroll: true,
        onSuccess: () => (isModalOpen.value = false),
    });
};
</script>

<template>
    <Head title="Trang chủ - FoodXpress" />

    <div
        class="mb-12 relative h-[360px] md:h-[460px] overflow-hidden rounded-[3rem] shadow-[0_35px_90px_rgba(15,23,42,0.25)] group"
    >
        <swiper
            :modules="[Autoplay, EffectFade]"
            :effect="'fade'"
            :autoplay="{ delay: 6000, disableOnInteraction: false }"
            :loop="true"
            class="h-full w-full"
        >
            <swiper-slide v-for="(slide, index) in slides" :key="index">
                <div class="relative h-full w-full">
                    <img
                        :src="slide.image"
                        class="absolute inset-0 w-full h-full object-cover"
                    />
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/50 to-black/20"
                    ></div>

                    <div
                        class="relative z-10 h-full flex flex-col justify-center px-6 md:px-16 lg:px-20 text-white"
                    >
                        <div class="max-w-2xl bg-white/10 backdrop-blur-md border border-white/20 p-8 md:p-12 rounded-[2.5rem] shadow-[0_8px_32px_0_rgba(31,38,135,0.37)] transform transition-all duration-500 hover:scale-[1.02]">
                            <span
                                class="inline-flex items-center gap-2 bg-gradient-to-r from-orange-500 to-red-500 text-[10px] font-black uppercase tracking-[0.4em] px-4 py-2 rounded-full mb-6 w-fit animate-pulse shadow-xl shadow-orange-500/30"
                            >
                                <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                                <span class="relative w-2 h-2 -ml-4 rounded-full bg-white"></span>
                                FoodXpress
                            </span>
                            <h1
                                class="text-4xl md:text-5xl lg:text-6xl font-black mb-4 leading-[1.1] tracking-tight drop-shadow-2xl text-transparent bg-clip-text bg-gradient-to-r from-white via-orange-100 to-white"
                            >
                                {{ slide.slogan }}<br />
                                <span class="block text-orange-400 mt-2"
                                    >Món ngon đợi bạn</span
                                >
                            </h1>
                            <p
                                class="text-sm md:text-base lg:text-lg font-medium text-slate-100/90 tracking-wide leading-relaxed drop-shadow-lg mt-4"
                            >
                                {{ slide.sub }} Hãy tìm món, lọc nhanh và đặt ngay trong vài giây.
                            </p>
                        </div>
                    </div>
                </div>
            </swiper-slide>
        </swiper>
    </div>

    <div class="relative -mt-24 px-4 md:px-6 z-20">
        <div class="mx-auto max-w-[1400px]">
            <div
                class="rounded-[3rem] border border-white bg-white/80 backdrop-blur-xl shadow-[0_20px_60px_-15px_rgba(0,0,0,0.1)] hover:shadow-[0_30px_70px_-15px_rgba(249,115,22,0.15)] transition-all duration-500 p-8 md:p-10 relative overflow-hidden"
            >
                <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-orange-400/20 to-red-400/20 rounded-full blur-3xl -mr-32 -mt-32 pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-gradient-to-tr from-blue-400/10 to-transparent rounded-full blur-3xl -ml-32 -mb-32 pointer-events-none"></div>
                
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between relative z-10"
                >
                    <div>
                        <p
                            class="text-xs uppercase tracking-[0.35em] text-orange-500 font-black mb-2 flex items-center gap-2"
                        >
                            <span class="w-2 h-2 rounded-full bg-orange-500 inline-block"></span>
                            Tìm món nhanh
                        </p>
                        <h2
                            class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight"
                        >
                            Hương vị trong tầm tay
                        </h2>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <div
                            class="rounded-2xl bg-slate-900/5 backdrop-blur-sm border border-slate-900/10 px-5 py-2.5 text-xs font-black uppercase tracking-[0.2em] text-slate-900 shadow-sm flex items-center gap-2"
                        >
                            <span class="text-lg">🏪</span> {{ restaurants.length }} quán
                        </div>
                        <div
                            class="rounded-2xl bg-orange-500/10 backdrop-blur-sm border border-orange-500/20 px-5 py-2.5 text-xs font-black uppercase tracking-[0.2em] text-orange-600 shadow-sm flex items-center gap-2"
                        >
                            <span class="text-lg">🍔</span> {{ products.length }} món
                        </div>
                    </div>
                </div>

                <div
                    class="mt-8 grid gap-4 md:grid-cols-[2fr_1fr_1fr_auto] items-center relative z-10"
                >
                    <div class="relative group">
                        <div
                            class="absolute left-5 top-1/2 -translate-y-1/2 text-orange-400 transition-transform group-focus-within:scale-110"
                        >
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2.5"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Bạn muốn ăn gì hôm nay?"
                            class="w-full h-16 rounded-[2rem] border-2 border-slate-100 bg-white text-slate-900 pl-14 pr-6 shadow-sm focus:border-orange-400 focus:ring-0 transition-all font-medium text-lg placeholder:text-slate-400"
                        />
                    </div>
                    <div class="relative">
                        <select
                            v-model="priceRange"
                            class="w-full h-16 rounded-[2rem] border-2 border-slate-100 bg-white text-slate-900 px-6 shadow-sm focus:border-orange-400 focus:ring-0 transition-all font-medium appearance-none cursor-pointer"
                        >
                            <option value="">💰 Mức giá</option>
                            <option value="0-50000">Dưới 50k</option>
                            <option value="50000-100000">50k - 100k</option>
                            <option value="100000+">Trên 100k</option>
                        </select>
                        <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    <div class="relative">
                        <select
                            v-model="deliveryType"
                            class="w-full h-16 rounded-[2rem] border-2 border-slate-100 bg-white text-slate-900 px-6 shadow-sm focus:border-orange-400 focus:ring-0 transition-all font-medium appearance-none cursor-pointer"
                        >
                            <option value="">🛵 Phục vụ</option>
                            <option value="takeaway">Mang về</option>
                            <option value="delivery">Giao tận nơi</option>
                            <option value="dinein">Ăn tại quán</option>
                        </select>
                        <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    <button
                        @click="updateFilters()"
                        class="h-16 rounded-[2rem] bg-gradient-to-r from-orange-500 to-red-500 px-10 text-sm font-black uppercase tracking-[0.15em] text-white shadow-lg shadow-orange-500/30 hover:shadow-orange-500/50 hover:-translate-y-1 transition-all flex items-center justify-center gap-2"
                    >
                        <span>Tìm Kiếm</span>
                    </button>
                </div>

                <div class="mt-6 flex flex-wrap gap-3 relative z-10">
                    <button
                        v-for="tab in searchTabs"
                        :key="tab"
                        @click="activeTab = tab"
                        :class="
                            activeTab === tab
                                ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/20 scale-105'
                                : 'bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                        "
                        class="rounded-full px-5 py-2.5 text-xs md:text-sm font-bold transition-all duration-300 border border-slate-200"
                    >
                        {{ tab }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MAP SECTION -->
    <section v-if="props.auth.user" class="mb-16 px-2">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-3xl font-black text-gray-800 tracking-tighter italic uppercase flex items-center gap-3">
                <div class="w-2 h-8 bg-orange-500 rounded-full"></div>
                Quán ăn quanh bạn
            </h2>
            <div class="flex items-center gap-4">
                <div v-if="userLocation" class="text-[10px] font-black text-blue-600 bg-blue-50 px-4 py-1.5 rounded-full animate-pulse">
                    ĐỊA CHỈ ĐÃ XÁC ĐỊNH 📡
                </div>
                <button @click="showMap = !showMap" class="bg-gradient-to-r from-orange-500 to-red-500 text-white px-6 py-2 rounded-full font-black uppercase text-xs shadow-lg hover:shadow-xl transition-all flex items-center gap-2">
                    <svg v-if="showMap" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                    <span>{{ showMap ? "Ẩn bản đồ" : "Hiện bản đồ" }}</span>
                </button>
            </div>
        </div>
        <div v-show="showMap" class="h-[500px] w-full rounded-[3.5rem] shadow-2xl border-[12px] border-white overflow-hidden relative z-0 transition-all duration-500">
            <div id="map-home" class="h-full w-full rounded-3xl"></div>
        </div>
    </section>

    <section v-else class="mb-16 px-2">
        <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-[3.5rem] p-8 md:p-12 text-center border-4 border-dashed border-orange-200 shadow-xl">
            <div class="mb-6"><span class="text-6xl">🔑</span></div>
            <p class="text-orange-900 font-black italic text-xl md:text-2xl mb-4">Đăng nhập để khám phá các quán ăn quanh bạn!</p>
            <p class="text-orange-600/70 text-sm md:text-base font-bold uppercase tracking-widest mb-8">Trải nghiệm tính năng định vị thời gian thực của FoodXpress</p>
            <Link :href="route('login')" class="bg-gradient-to-r from-orange-500 to-red-500 text-white px-8 md:px-10 py-3 md:py-4 rounded-2xl font-black uppercase text-xs md:text-sm shadow-xl shadow-orange-200 hover:shadow-2xl hover:scale-105 transition-all inline-block">Đăng nhập ngay ➔</Link>
        </div>
    </section>

    <!-- VOUCHER SECTION BEAUTIFIED -->
    <section v-if="vouchers && vouchers.length > 0" class="mb-20 px-4 md:px-6 max-w-7xl mx-auto">
        <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-[3.5rem] p-6 md:p-8 lg:p-12 border-2 border-dashed border-orange-200/60 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-orange-300/20 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
            
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-4 relative z-10">
                <div>
                    <h2 class="text-3xl md:text-4xl font-black tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-red-600 flex items-center gap-3">
                        <span class="text-4xl animate-bounce">🎁</span> Ưu Đãi Độc Quyền
                    </h2>
                    <p class="text-xs text-orange-800/60 font-black uppercase tracking-widest mt-3">Săn mã xịn - Ăn phủ phê không lo về giá</p>
                </div>
            </div>
            
            <div class="flex overflow-x-auto gap-6 pb-6 no-scrollbar snap-x items-center relative z-10">
                <div v-for="voucher in vouchers" :key="voucher.id" class="snap-start shrink-0 w-[320px] lg:w-[380px] group cursor-pointer perspective-1000">
                    <div class="relative transform transition-all duration-500 group-hover:-translate-y-3 group-hover:scale-[1.02] rounded-[2rem]">
                        <div class="bg-gradient-to-br from-orange-500 via-red-500 to-pink-500 p-1.5 rounded-[2rem] shadow-xl relative overflow-hidden">
                            <!-- Background pattern -->
                            <div class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>
                            
                            <div class="bg-white rounded-[1.5rem] h-full flex relative overflow-hidden">
                                <!-- Left side (Discount) -->
                                <div class="w-[65%] p-5 border-r-2 border-dashed border-orange-200/60 flex flex-col justify-center bg-orange-50/30">
                                    <span class="inline-block bg-orange-100 text-orange-600 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full w-fit mb-3 shadow-sm">
                                        Mã giảm giá
                                    </span>
                                    <h3 class="text-3xl font-black text-slate-900 tracking-tighter mb-1">
                                        Giảm {{ voucher.discount_type === 'percent' ? voucher.discount_value + '%' : Number(voucher.discount_value).toLocaleString() + 'đ' }}
                                    </h3>
                                    <p class="text-[11px] text-slate-500 font-bold mb-4">
                                        Đơn tối thiểu {{ Number(voucher.minimum_product_price || 0).toLocaleString() }}đ
                                    </p>
                                    <div class="mt-auto pt-3 border-t border-slate-100/50">
                                        <p class="text-[10px] text-slate-400 font-bold flex items-center gap-1">
                                            <span>⏱</span> HSD: {{ new Date(voucher.expires_at).toLocaleDateString('vi-VN') }}
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Right side (Code & Copy) -->
                                <div class="w-[35%] bg-white p-4 flex flex-col items-center justify-center relative">
                                    <!-- Perforation circles -->
                                    <div class="absolute -left-3 top-0 w-6 h-6 bg-orange-500 rounded-full -mt-3 shadow-inner"></div>
                                    <div class="absolute -left-3 bottom-0 w-6 h-6 bg-pink-500 rounded-full -mb-3 shadow-inner"></div>
                                    
                                    <div class="w-full text-center mb-4 mt-2">
                                        <p class="text-[9px] uppercase font-black text-slate-400 tracking-widest mb-2">Mã của bạn</p>
                                        <span class="font-mono text-sm font-black text-orange-600 bg-orange-50 px-2 py-1.5 rounded-lg border border-orange-100 block truncate">{{ voucher.code }}</span>
                                    </div>
                                    <button @click.stop="copyVoucher(voucher.code)" class="w-full bg-slate-900 text-white rounded-xl py-2.5 text-[10px] font-black uppercase tracking-widest hover:bg-orange-500 transition-colors shadow-lg shadow-slate-900/20 active:scale-95 mb-1">
                                        Copy Mã
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-20 px-4 md:px-6 max-w-[1400px] mx-auto">
        <div
            class="mb-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
        >
            <div>
                <p
                    class="text-xs uppercase tracking-[0.35em] text-orange-500 font-black mb-2 flex items-center gap-2"
                >
                    <span class="w-2 h-2 rounded-full bg-orange-500 inline-block"></span>
                    Khám phá nhanh
                </p>
                <h2
                    class="text-3xl md:text-5xl font-black text-slate-900 tracking-tight"
                >
                    Quán ngon quanh bạn
                </h2>
            </div>
            <div class="grid gap-3 sm:auto-cols-min sm:grid-flow-col">
                <div
                    class="rounded-2xl bg-slate-900 px-5 py-3 text-xs font-black uppercase tracking-[0.15em] text-white shadow-lg flex items-center gap-2"
                >
                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                    {{ restaurants.length }} quán đang mở
                </div>
            </div>
        </div>

        <div class="overflow-x-auto no-scrollbar pb-4 mb-8">
            <div class="inline-flex gap-3 px-1">
                <button
                    @click="selectedCat = ''"
                    :class="
                        selectedCat === ''
                            ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/20'
                            : 'bg-white text-slate-600 hover:bg-slate-50'
                    "
                    class="rounded-full px-6 py-3 text-xs md:text-sm font-bold transition-all border border-slate-200 flex items-center gap-2"
                >
                    <span>🔥</span> Tất cả
                </button>
                <button
                    v-for="cat in categories"
                    :key="cat.id"
                    @click="selectedCat = cat.id"
                    :class="
                        selectedCat == cat.id
                            ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/20'
                            : 'bg-white text-slate-600 hover:bg-slate-50'
                    "
                    class="rounded-full px-6 py-3 text-xs md:text-sm font-bold transition-all border border-slate-200 flex items-center gap-2 whitespace-nowrap"
                >
                    <span>{{ cat.icon || '🍱' }}</span> {{ cat.name }}
                </button>
            </div>
        </div>

        <div class="w-full relative">
            <swiper
                :modules="[FreeMode, Pagination]"
                :slidesPerView="1.2"
                :spaceBetween="20"
                :freeMode="true"
                :pagination="{ clickable: true, dynamicBullets: true }"
                :breakpoints="{
                    '640': { slidesPerView: 2.2, spaceBetween: 20 },
                    '768': { slidesPerView: 3.2, spaceBetween: 24 },
                    '1024': { slidesPerView: 4.2, spaceBetween: 24 }
                }"
                class="pb-12 px-2"
            >
                <swiper-slide v-for="shop in restaurants" :key="shop.id" class="h-auto">
                    <Link
                        :href="route('restaurant.menu', shop.id)"
                        class="group block relative flex flex-col h-full bg-white rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-[0_20px_50px_-12px_rgba(0,0,0,0.1)] transition-all duration-500 overflow-hidden hover:-translate-y-2"
                    >
                <div class="relative h-64 overflow-hidden rounded-t-[2rem]">
                    <img
                        :src="
                            shop.cover_image ||
                            'https://images.unsplash.com/photo-1498654896293-37aacf113fd9?auto=format&fit=crop&w=1200&q=80'
                        "
                        class="h-full w-full object-cover transition duration-700 group-hover:scale-110"
                    />
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/20 to-transparent"
                    ></div>
                    
                    <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-md px-3 py-1.5 rounded-xl text-[10px] font-black tracking-wider flex items-center gap-1 shadow-sm text-slate-900">
                        <span class="text-yellow-500 text-sm">★</span> 4.8
                    </div>

                    <div class="absolute bottom-5 left-5 right-5 text-white">
                        <h3
                            class="text-2xl font-black leading-tight tracking-tight group-hover:text-orange-400 transition-colors mb-2"
                        >
                            {{ shop.restaurant_name || shop.name }}
                        </h3>
                        <div class="flex items-center gap-3 text-xs font-medium text-slate-300">
                            <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> {{ shop.address ? shop.address.split(',')[0] : 'Đà Nẵng' }}</span>
                            <span class="w-1 h-1 rounded-full bg-slate-500"></span>
                            <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> ~20 min</span>
                        </div>
                    </div>
                </div>
                <div class="p-5 flex items-center justify-between bg-white relative z-10 group-hover:bg-slate-50 transition-colors border-t border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-500 font-black text-sm">
                            {{ (shop.restaurant_name || shop.name).charAt(0) }}
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400 font-bold mb-0.5">Đối tác</p>
                            <p class="text-sm font-bold text-slate-900">FoodXpress</p>
                        </div>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-orange-500 group-hover:text-white transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </div>
                    </Link>
                </swiper-slide>
            </swiper>
        </div>
    </section>

    <section class="mb-20 px-4 md:px-6 max-w-[1400px] mx-auto">
        <div class="flex items-end justify-between mb-10">
            <div>
                <p class="text-xs uppercase tracking-[0.35em] text-orange-500 font-black mb-2 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-orange-500 inline-block"></span>
                    Hôm nay ăn gì
                </p>
                <h2 class="text-3xl md:text-5xl font-black text-slate-900 tracking-tight">
                    Gợi ý món ngon ✨
                </h2>
            </div>
        </div>
        
        <div v-if="products.length > 0" class="w-full relative">
            <swiper
                :modules="[FreeMode, Pagination]"
                :slidesPerView="2.2"
                :spaceBetween="16"
                :freeMode="true"
                :pagination="{ clickable: true, dynamicBullets: true }"
                :breakpoints="{
                    '640': { slidesPerView: 3.2, spaceBetween: 20 },
                    '768': { slidesPerView: 4.2, spaceBetween: 24 },
                    '1024': { slidesPerView: 5.2, spaceBetween: 24 }
                }"
                class="pb-12 px-2"
            >
                <swiper-slide v-for="product in products" :key="product.id" class="h-auto">
                    <div
                        @click="openProductModal(product)"
                        class="h-full bg-white rounded-[2rem] p-3 md:p-4 border border-slate-100 shadow-sm hover:shadow-[0_20px_50px_-12px_rgba(249,115,22,0.25)] hover:-translate-y-2 transition-all duration-500 cursor-pointer group relative overflow-hidden flex flex-col"
                    >
                <div class="absolute inset-0 bg-gradient-to-b from-orange-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                
                <div
                    class="relative h-40 md:h-48 rounded-[1.5rem] overflow-hidden mb-4 shadow-sm"
                >
                    <img
                        :src="product.image ? (product.image.startsWith('http') ? product.image : '/storage/' + product.image) : '/images/default-food.png'"
                        class="w-full h-full object-cover group-hover:scale-110 transition duration-700"
                    />
                    <!-- Price Ribbon -->
                    <div class="absolute top-3 left-0 bg-slate-900/80 backdrop-blur-md text-white px-4 py-1.5 rounded-r-full text-xs font-black shadow-lg flex items-center gap-1 border-y border-r border-white/20 z-10">
                        <span class="text-orange-400">🔥</span> {{ Number(product.price).toLocaleString() }}đ
                    </div>
                    
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col justify-end p-4 translate-y-4 group-hover:translate-y-0"
                    >
                        <button
                            @click.stop="handleAddToCart({ product_id: product.id, options: [], quantity: 1, note: '' })"
                            class="w-full bg-orange-500 text-white font-black text-sm py-2.5 px-4 rounded-xl hover:bg-orange-600 transition-colors shadow-lg active:scale-95 flex items-center justify-center gap-2"
                        >
                            <span>Thêm vào giỏ</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </button>
                    </div>
                </div>
                
                <div class="flex-1 flex flex-col relative z-10">
                    <h4
                        class="font-black text-slate-800 text-base md:text-lg truncate group-hover:text-orange-600 transition-colors leading-tight mb-2"
                    >
                        {{ product.name }}
                    </h4>
                    <p
                        class="text-[11px] font-bold text-slate-500 uppercase mt-auto flex items-center gap-1.5"
                    >
                        <span class="w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center text-[10px]">🏪</span>
                        <span class="truncate">{{ product.user?.restaurant_name || product.user?.name }}</span>
                    </p>
                </div>
                    </div>
                </swiper-slide>
            </swiper>
        </div>
    </section>

    <ProductDetailModal
        v-if="selectedProduct"
        :show="isModalOpen"
        :product="selectedProduct"
        @close="isModalOpen = false"
        @addToCart="handleAddToCart"
    />

    <!-- Local Toast -->
    <div v-if="showLocalToast" class="fixed top-24 right-4 bg-slate-900 text-white px-6 py-4 rounded-2xl shadow-2xl z-[100] flex items-center gap-3 transform transition-all duration-300">
        <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center">
            <span class="text-green-400 text-lg">✓</span>
        </div>
        <span class="text-sm font-bold tracking-wide">{{ showLocalToast }}</span>
    </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap");

* {
    font-family: "Inter", sans-serif;
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
#map-home {
    filter: saturate(1.2) contrast(1.05);
}

/* Animation for Floating Card */
.animate-bounce-slow {
    animation: bounce-slow 4s infinite;
}

@keyframes bounce-slow {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-20px);
    }
}

/* Smooth hover transitions */
.group:hover .group-hover\:scale-110 {
    transform: scale(1.1);
}

.group:hover .group-hover\:brightness-110 {
    filter: brightness(1.1);
}

/* Custom scrollbar for categories */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

:deep(.user-tooltip) {
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 10px;
    font-weight: 900;
    padding: 4px 8px;
}
:deep(.leaflet-popup-content-wrapper) {
    border-radius: 2rem;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
}
:deep(.leaflet-popup-content) {
    margin: 0 !important;
    width: 220px !important;
}
:deep(.leaflet-control-zoom) {
    border: none !important;
    margin: 20px !important;
}
:deep(.leaflet-control-zoom-in),
:deep(.leaflet-control-zoom-out) {
    border-radius: 15px !important;
    color: #fb7b1f !important;
    font-weight: 900 !important;
}
</style>
