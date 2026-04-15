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
import { Autoplay, EffectFade } from "swiper/modules";
import "swiper/css";
import "swiper/css/effect-fade";

const props = defineProps({
    auth: Object,
    restaurants: Array,
    products: Array,
    categories: Array,
    filters: Object,
});

defineOptions({ layout: GuestLayout });

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

const initMap = () => {
    const mapContainer = document.getElementById("map-home");
    if (!mapContainer) return;

    map = L.map("map-home", {
        scrollWheelZoom: true,
        zoomControl: true,
    }).setView([16.061, 108.2158], 13);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution:
            "&copy; <a href='https://www.openstreetmap.org/copyright'>OpenStreetMap</a> contributors",
        subdomains: "abc",
        maxZoom: 22,
    }).addTo(map);

    const foodIcon = L.divIcon({
        html: `
            <div class="relative">
                <div class="absolute -top-2 -left-2 w-8 h-8 bg-orange-500/20 rounded-full animate-ping"></div>
                <div class="relative bg-orange-500 w-8 h-8 rounded-2xl flex items-center justify-center shadow-lg border-2 border-white text-white rotate-45 group hover:scale-125 transition-all font-sans">
                    <span class="-rotate-45 text-xs">🍕</span>
                </div>
            </div>
        `,
        className: "custom-food-marker",
        iconSize: [32, 32],
        iconAnchor: [16, 16],
    });

    const markersGroup = L.featureGroup();

    props.restaurants.forEach((shop) => {
        if (shop.latitude && shop.longitude) {
            const m = L.marker([shop.latitude, shop.longitude], {
                icon: foodIcon,
            }).bindPopup(
                `
                    <div class="p-3 font-sans min-w-[180px]">
                        <b class="text-gray-800 text-sm leading-tight">${shop.restaurant_name || shop.name}</b>
                        <p class="text-[10px] text-gray-400 mb-4 font-bold">📍 ${shop.address}</p>
                        <a href="/restaurant-menu/${shop.id}" class="block text-center bg-orange-500 text-white py-2 rounded-xl text-[10px] font-black uppercase shadow-lg shadow-orange-100 no-underline">Khám phá menu ➔</a>
                    </div>
                `,
                { closeButton: false, offset: [0, -10] },
            );
            markersGroup.addLayer(m);
        }
    });

    markersGroup.addTo(map);

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                userLocation.value = [latitude, longitude];

                const userIcon = L.divIcon({
                    html: `<div class="relative"><div class="absolute -top-1 -left-1 w-6 h-6 bg-blue-500/40 rounded-full animate-ping"></div><div class="relative bg-blue-600 w-4 h-4 rounded-full border-2 border-white shadow-lg"></div></div>`,
                    className: "user-location-marker",
                    iconSize: [16, 16],
                });

                L.marker([latitude, longitude], { icon: userIcon })
                    .addTo(map)
                    .bindTooltip("Bạn đang ở đây!", {
                        permanent: true,
                        direction: "top",
                        className: "user-tooltip",
                    });

                if (markersGroup.getLayers().length > 0) {
                    const bounds = markersGroup.getBounds();
                    bounds.extend([latitude, longitude]);
                    map.fitBounds(bounds, { padding: [50, 50] });
                } else {
                    map.flyTo([latitude, longitude], 15);
                }

                router.post(
                    route("update-location"),
                    { latitude, longitude },
                    { preserveScroll: true, preserveState: true },
                );
            },
            (err) => console.warn("Định vị bị từ chối."),
        );
    }

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
                        <span
                            class="inline-flex items-center gap-2 bg-orange-500/90 text-[10px] font-black uppercase tracking-[0.4em] px-4 py-2 rounded-full mb-6 w-fit animate-pulse shadow-xl"
                        >
                            <span class="w-2 h-2 rounded-full bg-white"></span>
                            FoodXpress Đà Nẵng
                        </span>
                        <h1
                            class="text-4xl md:text-5xl lg:text-6xl font-black mb-4 leading-[0.92] tracking-tight italic drop-shadow-2xl"
                        >
                            {{ slide.slogan }}<br />
                            <span class="block text-orange-300"
                                >Món ngon đợi bạn</span
                            >
                        </h1>
                        <p
                            class="text-sm md:text-base lg:text-lg font-medium text-slate-200 max-w-xl tracking-wide leading-relaxed drop-shadow-lg"
                        >
                            {{ slide.sub }} Hãy tìm món, lọc nhanh và đặt ngay
                            trong vài giây.
                        </p>
                    </div>
                </div>
            </swiper-slide>
        </swiper>
    </div>

    <div class="relative -mt-24 px-4 md:px-6 z-20">
        <div class="mx-auto max-w-6xl">
            <div
                class="rounded-[3rem] border border-white/80 bg-white/95 backdrop-blur-2xl shadow-[0_45px_120px_rgba(15,23,42,0.16)] p-6 md:p-8"
            >
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div>
                        <p
                            class="text-xs uppercase tracking-[0.35em] text-orange-500 font-black mb-2"
                        >
                            Tìm món nhanh
                        </p>
                        <h2
                            class="text-2xl md:text-3xl font-black text-slate-900"
                        >
                            Hàm ý món ngon ngay tức thì
                        </h2>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <div
                            class="rounded-full bg-slate-900 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-white shadow-sm"
                        >
                            {{ restaurants.length }} quán
                        </div>
                        <div
                            class="rounded-full bg-orange-500 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-white shadow-sm"
                        >
                            {{ products.length }} món
                        </div>
                    </div>
                </div>

                <div
                    class="mt-6 grid gap-3 md:grid-cols-[2fr_1fr_1fr_auto] items-center"
                >
                    <div class="relative">
                        <div
                            class="absolute left-4 top-1/2 -translate-y-1/2 text-orange-400"
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
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Bạn muốn ăn gì hôm nay?"
                            class="w-full h-14 rounded-3xl border border-slate-200 bg-slate-50 text-slate-900 pl-14 pr-4 shadow-sm focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition-all"
                        />
                    </div>
                    <select
                        v-model="priceRange"
                        class="h-14 rounded-3xl border border-slate-200 bg-slate-50 text-slate-900 px-4 shadow-sm focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition-all"
                    >
                        <option value="">Mức giá</option>
                        <option value="0-50000">Dưới 50k</option>
                        <option value="50000-100000">50k - 100k</option>
                        <option value="100000+">Trên 100k</option>
                    </select>
                    <select
                        v-model="deliveryType"
                        class="h-14 rounded-3xl border border-slate-200 bg-slate-50 text-slate-900 px-4 shadow-sm focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition-all"
                    >
                        <option value="">Loại phục vụ</option>
                        <option value="takeaway">Mang về</option>
                        <option value="delivery">Giao tận nơi</option>
                        <option value="dinein">Ăn tại quán</option>
                    </select>
                    <button
                        @click="updateFilters()"
                        class="h-14 rounded-3xl bg-orange-500 px-6 text-sm font-black uppercase tracking-[0.12em] text-white shadow-xl hover:bg-orange-600 transition-all"
                    >
                        Tìm
                    </button>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    <button
                        v-for="tab in searchTabs"
                        :key="tab"
                        @click="activeTab = tab"
                        :class="
                            activeTab === tab
                                ? 'bg-orange-500 text-white shadow-lg'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                        "
                        class="rounded-full px-4 py-2 text-xs md:px-5 md:py-2 md:text-sm font-semibold transition-all"
                    >
                        {{ tab }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <section v-if="props.auth.user" class="mb-16 px-2">
        <div class="flex items-center justify-between mb-8">
            <h2
                class="text-3xl font-black text-gray-800 tracking-tighter italic uppercase flex items-center gap-3"
            >
                <div class="w-2 h-8 bg-orange-500 rounded-full"></div>
                Quán ăn quanh bạn
            </h2>
            <div class="flex items-center gap-4">
                <div
                    v-if="userLocation"
                    class="text-[10px] font-black text-blue-600 bg-blue-50 px-4 py-1.5 rounded-full animate-pulse"
                >
                    ĐỊA CHỈ ĐÃ XÁC ĐỊNH 📡
                </div>
                <button
                    @click="showMap = !showMap"
                    class="bg-gradient-to-r from-orange-500 to-red-500 text-white px-6 py-2 rounded-full font-black uppercase text-xs shadow-lg hover:shadow-xl transition-all flex items-center gap-2"
                >
                    <svg
                        v-if="showMap"
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 9l-7 7-7-7"
                        ></path>
                    </svg>
                    <svg
                        v-else
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 15l7-7 7 7"
                        ></path>
                    </svg>
                    <span>{{ showMap ? "Ẩn bản đồ" : "Hiện bản đồ" }}</span>
                </button>
            </div>
        </div>
        <div
            v-show="showMap"
            id="map-home"
            class="h-[500px] w-full rounded-[3.5rem] shadow-2xl border-[12px] border-white overflow-hidden relative z-0 transition-all duration-500"
        ></div>
    </section>

    <section v-else class="mb-16 px-2">
        <div
            class="bg-gradient-to-br from-orange-50 to-red-50 rounded-[3.5rem] p-8 md:p-12 text-center border-4 border-dashed border-orange-200 shadow-xl"
        >
            <div class="mb-6">
                <span class="text-6xl">🔑</span>
            </div>
            <p
                class="text-orange-900 font-black italic text-xl md:text-2xl mb-4"
            >
                Đăng nhập để khám phá các quán ăn quanh bạn!
            </p>
            <p
                class="text-orange-600/70 text-sm md:text-base font-bold uppercase tracking-widest mb-8"
            >
                Trải nghiệm tính năng định vị thời gian thực của FoodXpress
            </p>
            <Link
                :href="route('login')"
                class="bg-gradient-to-r from-orange-500 to-red-500 text-white px-8 md:px-10 py-3 md:py-4 rounded-2xl font-black uppercase text-xs md:text-sm shadow-xl shadow-orange-200 hover:shadow-2xl hover:scale-105 transition-all inline-block"
                >Đăng nhập ngay ➔</Link
            >
        </div>
    </section>

    <section class="mb-16 px-2 md:px-6">
        <div
            class="mb-8 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
        >
            <div>
                <p
                    class="text-xs uppercase tracking-[0.35em] text-orange-500 font-black mb-2"
                >
                    Khám phá nhanh
                </p>
                <h2
                    class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight"
                >
                    Quán ăn tại khu vực của bạn
                </h2>
            </div>
            <div class="grid gap-3 sm:auto-cols-min sm:grid-flow-col">
                <div
                    class="rounded-full bg-slate-900 px-4 py-3 text-xs font-black uppercase tracking-[0.18em] text-white shadow-sm"
                >
                    {{ restaurants.length }} quán
                </div>
                <div
                    class="rounded-full bg-orange-500 px-4 py-3 text-xs font-black uppercase tracking-[0.18em] text-white shadow-sm"
                >
                    {{ products.length }} món
                </div>
            </div>
        </div>

        <div class="overflow-x-auto no-scrollbar pb-2 mb-8">
            <div class="inline-flex gap-3">
                <button
                    @click="selectedCat = ''"
                    :class="
                        selectedCat === ''
                            ? 'bg-orange-500 text-white shadow-xl'
                            : 'bg-white text-slate-500 hover:bg-slate-50'
                    "
                    class="rounded-full px-6 py-3 text-xs md:text-sm font-black uppercase tracking-[0.18em] transition-all border border-slate-100"
                >
                    🔥 Tất cả
                </button>
                <button
                    v-for="cat in categories"
                    :key="cat.id"
                    @click="selectedCat = cat.id"
                    :class="
                        selectedCat == cat.id
                            ? 'bg-orange-500 text-white shadow-xl'
                            : 'bg-white text-slate-500 hover:bg-slate-50'
                    "
                    class="rounded-full px-6 py-3 text-xs md:text-sm font-black uppercase tracking-[0.18em] transition-all border border-slate-100 flex items-center gap-2 whitespace-nowrap"
                >
                    <span>{{ cat.icon || "🍱" }}</span> {{ cat.name }}
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-4">
            <Link
                v-for="shop in restaurants"
                :key="shop.id"
                :href="route('restaurant.menu', shop.id)"
                class="group relative overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white shadow-sm transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl"
            >
                <div class="relative h-80 overflow-hidden">
                    <img
                        :src="
                            shop.cover_image ||
                            'https://images.unsplash.com/photo-1498654896293-37aacf113fd9?auto=format&fit=crop&w=1200&q=80'
                        "
                        class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                    />
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"
                    ></div>
                    <div class="absolute bottom-6 left-6 right-6 text-white">
                        <div
                            class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-2 text-[10px] uppercase tracking-[0.25em] text-white backdrop-blur-sm"
                        >
                            📍
                            {{
                                shop.address
                                    ? shop.address.split(",")[0]
                                    : "Đà Nẵng"
                            }}
                        </div>
                        <h3
                            class="text-2xl font-black leading-tight tracking-tight group-hover:text-orange-300 transition-colors"
                        >
                            {{ shop.restaurant_name || shop.name }}
                        </h3>
                    </div>
                </div>
                <div class="p-5 border-t border-slate-100">
                    <p
                        class="text-xs uppercase tracking-[0.25em] text-slate-400 mb-2"
                    >
                        Quán ăn
                    </p>
                    <div class="text-sm font-black text-slate-900">
                        {{ shop.restaurant_name || shop.name }}
                    </div>
                </div>
            </Link>
        </div>
    </section>

    <section class="mb-20 px-2">
        <h2
            class="text-4xl font-black text-gray-900 mb-10 tracking-tighter italic uppercase"
        >
            Gợi ý món ngon ✨
        </h2>
        <div
            v-if="products.length > 0"
            class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-5 gap-8"
        >
            <div
                v-for="product in products"
                :key="product.id"
                @click="openProductModal(product)"
                class="bg-white rounded-[2.5rem] md:rounded-[3rem] p-4 border border-gray-50 shadow-sm hover:shadow-2xl hover:shadow-orange-200/50 hover:-translate-y-4 transition-all duration-500 cursor-pointer group overflow-hidden"
            >
                <div
                    class="relative h-40 md:h-48 rounded-[2rem] overflow-hidden mb-4 shadow-inner group-hover:shadow-lg transition-shadow"
                >
                    <img
                        :src="'/storage/' + product.image"
                        class="w-full h-full object-cover group-hover:scale-110 transition duration-700"
                    />
                    <div
                        class="absolute top-3 right-3 bg-gradient-to-r from-orange-500 to-red-500 text-white px-3 py-1 rounded-full text-[10px] font-black shadow-lg font-sans"
                    >
                        {{ Number(product.price).toLocaleString() }}đ
                    </div>
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"
                    ></div>
                </div>
                <h4
                    class="font-black text-gray-800 text-base md:text-lg italic truncate group-hover:text-orange-500 transition-colors font-sans leading-tight"
                >
                    {{ product.name }}
                </h4>
                <p
                    class="text-xs font-bold text-gray-400 uppercase mt-2 flex items-center gap-1 font-sans"
                >
                    🏪 {{ product.user?.restaurant_name || product.user?.name }}
                </p>
                <button
                    @click.stop="handleAddToCart({ product_id: product.id, options: [], quantity: 1, note: '' })"
                    class="mt-3 w-full bg-gradient-to-r from-orange-500 to-red-500 text-white font-black text-sm py-2 px-4 rounded-full hover:from-orange-600 hover:to-red-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
                >
                    Đặt hàng ngay 🚀
                </button>
            </div>
        </div>
    </section>

    <ProductDetailModal
        v-if="selectedProduct"
        :show="isModalOpen"
        :product="selectedProduct"
        @close="isModalOpen = false"
        @addToCart="handleAddToCart"
    />
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
