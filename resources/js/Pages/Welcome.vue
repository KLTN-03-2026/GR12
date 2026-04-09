<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import ProductDetailModal from "@/Components/ProductDetailModal.vue";
import debounce from "lodash/debounce";

const props = defineProps({
    restaurants: Array,
    products: Array,
    categories: Array,
    filters: Object,
});

defineOptions({ layout: GuestLayout });

// --- 1. TÌM KIẾM & LỌC REAL-TIME ---
const search = ref(props.filters.search || "");
const selectedCat = ref(props.filters.category_id || "");

const updateFilters = debounce(() => {
    router.get(
        route("home"),
        { search: search.value, category_id: selectedCat.value },
        { preserveState: true, replace: true, preserveScroll: true },
    );
}, 400);

watch([search, selectedCat], () => {
    updateFilters();
});

// --- 2. XỬ LÝ MODAL VÀ GIỎ HÀNG ---
const isModalOpen = ref(false);
const selectedProduct = ref(null);

const openProductModal = (product) => {
    selectedProduct.value = product;
    isModalOpen.value = true;
};

const handleAddToCart = (data) => {
    console.log("Welcome nhận tín hiệu từ Modal:", data);
    router.post(
        route("cart.add"),
        {
            product_id: data.product.id,
            quantity: data.quantity,
            options: data.options,
            note: data.note,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            },
            onError: (errors) => {
                console.error("Lỗi giỏ hàng:", errors);
            },
        },
    );
};

const resetFilters = () => {
    search.value = "";
    selectedCat.value = "";
};
</script>

<template>
    <Head title="Trang chủ - FoodXpress" />

    <div
        class="mb-12 relative overflow-hidden rounded-[3rem] bg-orange-500 p-8 md:p-16 text-white shadow-2xl"
    >
        <div class="relative z-10 max-w-3xl">
            <h1 class="text-5xl md:text-6xl font-black mb-6 leading-tight">
                Thèm gì có nấy,<br />
                ship tới liền tay! 🍕
            </h1>

            <div class="relative max-w-xl group">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Tìm món ngon, tên quán tại Đà Nẵng..."
                    class="w-full h-16 pl-14 pr-10 rounded-2xl text-gray-900 border-none focus:ring-4 focus:ring-orange-300 transition-all shadow-xl font-medium"
                />
                <div class="absolute left-5 top-5 text-gray-400">
                    <svg
                        class="w-6 h-6"
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
            </div>
        </div>
        <div
            class="absolute right-[-5%] bottom-[-10%] text-[20rem] opacity-10 select-none rotate-12"
        >
            🍜
        </div>
    </div>

    <div class="relative mb-12">
        <div
            class="absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-gray-50 to-transparent z-10 pointer-events-none"
        ></div>
        <div
            class="absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-gray-50 to-transparent z-10 pointer-events-none"
        ></div>

        <div
            class="overflow-x-auto no-scrollbar flex items-center gap-4 pb-4 px-2"
        >
            <button
                @click="selectedCat = ''"
                :class="
                    selectedCat === ''
                        ? 'bg-orange-600 text-white shadow-lg shadow-orange-200'
                        : 'bg-white text-gray-600 border border-gray-100 hover:border-orange-200'
                "
                class="px-8 py-4 rounded-2xl font-black text-sm uppercase tracking-widest transition-all whitespace-nowrap"
            >
                🔥 Tất cả
            </button>

            <button
                v-for="cat in categories"
                :key="cat.id"
                @click="selectedCat = cat.id"
                :class="
                    selectedCat == cat.id
                        ? 'bg-orange-600 text-white shadow-lg shadow-orange-200'
                        : 'bg-white text-gray-600 border border-gray-100 hover:border-orange-200'
                "
                class="px-8 py-4 rounded-2xl font-black text-sm uppercase tracking-widest transition-all whitespace-nowrap flex items-center gap-2"
            >
                <span v-if="cat.icon">{{ cat.icon }}</span>
                {{ cat.name }}
            </button>
        </div>
    </div>

    <section class="mb-16">
        <div class="flex items-center justify-between mb-8 px-2">
            <h2 class="text-3xl font-black text-gray-800 tracking-tight">
                Món ngon dành cho bạn
            </h2>
            <button
                v-if="search || selectedCat"
                @click="resetFilters"
                class="text-orange-500 font-bold text-sm hover:underline italic"
            >
                Xóa bộ lọc ({{ products.length }} món)
            </button>
        </div>

        <div
            v-if="products.length > 0"
            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 px-2"
        >
            <div
                v-for="product in products"
                :key="product.id"
                @click="openProductModal(product)"
                class="bg-white rounded-[2.5rem] p-3 border border-gray-100 shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all cursor-pointer group"
            >
                <div
                    class="relative h-44 rounded-[2rem] overflow-hidden mb-4 shadow-inner"
                >
                    <img
                        :src="'/storage/' + product.image"
                        class="w-full h-full object-cover group-hover:scale-110 transition duration-700"
                    />
                    <div
                        class="absolute bottom-3 right-3 bg-white/90 backdrop-blur px-4 py-2 rounded-2xl text-xs font-black text-gray-900 shadow-xl border border-white/20"
                    >
                        {{ Number(product.price).toLocaleString() }}đ
                    </div>
                </div>
                <div class="px-3 pb-2">
                    <h4
                        class="font-black text-gray-800 line-clamp-1 group-hover:text-orange-600 transition text-lg"
                    >
                        {{ product.name }}
                    </h4>
                    <p
                        class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1 flex items-center gap-1"
                    >
                        <span class="text-orange-400 text-lg">🏠</span>
                        {{ product.user?.name }}
                    </p>
                </div>
            </div>
        </div>
        <div
            v-else
            class="text-center py-32 bg-white rounded-[4rem] border-4 border-dashed border-gray-100 mx-2"
        >
            <p class="text-7xl mb-6">🏜️</p>
            <h3
                class="text-2xl font-black text-gray-400 uppercase tracking-widest"
            >
                Không tìm thấy món bạn yêu thích!
            </h3>
            <button
                @click="resetFilters"
                class="mt-6 bg-orange-100 text-orange-600 px-8 py-3 rounded-full font-black hover:bg-orange-200 transition"
            >
                Thử tìm món khác nhé
            </button>
        </div>
    </section>

    <section class="mb-20 px-2">
        <h2 class="text-3xl font-black text-gray-800 mb-8">Quán ăn nổi bật</h2>
        <div
            v-if="restaurants.length > 0"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8"
        >
            <div
                v-for="shop in restaurants"
                :key="shop.id"
                class="group bg-white rounded-[3rem] overflow-hidden border border-gray-100 shadow-sm hover:shadow-2xl transition-all duration-500"
            >
                <Link :href="route('restaurant.menu', shop.id)">
                    <div class="relative h-56 overflow-hidden">
                        <img
                            :src="
                                'https://ui-avatars.com/api/?name=' +
                                shop.name +
                                '&background=ffedd5&color=f97316&bold=true'
                            "
                            class="w-full h-full object-cover group-hover:scale-110 transition duration-700"
                        />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"
                        ></div>
                        <div class="absolute bottom-6 left-6 text-white">
                            <h3 class="text-xl font-black leading-tight mb-1">
                                {{ shop.name }}
                            </h3>
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-[10px] bg-orange-600 px-3 py-1 rounded-full font-black shadow-lg"
                                    >4.8 ★</span
                                >
                                <p
                                    class="text-[10px] font-bold opacity-90 uppercase tracking-tighter italic"
                                >
                                    Free Ship • 20 phút
                                </p>
                            </div>
                        </div>
                    </div>
                </Link>
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
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Tạo cảm giác mượt mà cho hình ảnh khi hover */
img {
    image-rendering: -webkit-optimize-contrast;
}
</style>
