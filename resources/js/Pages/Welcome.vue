<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import ProductDetailModal from "@/Components/ProductDetailModal.vue";
import debounce from "lodash/debounce"; // Hoàng Anh nhớ chạy lệnh: npm install lodash

const props = defineProps({
    restaurants: Array,
    products: Array,
    categories: Array, // Nhận danh mục từ Backend
    filters: Object,
});

defineOptions({ layout: GuestLayout });

// --- XỬ LÝ TÌM KIẾM & LỌC REAL-TIME ---
const search = ref(props.filters.search || "");
const selectedCat = ref(props.filters.category_id || "");

// Hàm thực hiện gửi request lên server (Debounced)
const updateFilters = debounce(() => {
    router.get(
        route("home"),
        { search: search.value, category_id: selectedCat.value },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        },
    );
}, 400); // Đợi người dùng ngừng gõ 0.4s mới gửi yêu cầu

// Theo dõi sự thay đổi của input tìm kiếm và danh mục
watch([search, selectedCat], () => {
    updateFilters();
});

// --- XỬ LÝ MODAL CHI TIẾT ---
const isModalOpen = ref(false);
const selectedProduct = ref(null);

const openProductModal = (product) => {
    selectedProduct.value = product;
    isModalOpen.value = true;
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
                <div
                    v-if="search"
                    @click="search = ''"
                    class="absolute right-5 top-5 cursor-pointer text-gray-300 hover:text-orange-500"
                >
                    <svg
                        class="w-6 h-6"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"
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

    <div
        class="mb-10 overflow-x-auto no-scrollbar flex items-center gap-3 pb-2"
    >
        <button
            @click="selectedCat = ''"
            :class="
                selectedCat === ''
                    ? 'bg-orange-600 text-white shadow-lg shadow-orange-100'
                    : 'bg-white text-gray-500 border border-gray-100'
            "
            class="px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest transition-all whitespace-nowrap"
        >
            🔥 Tất cả
        </button>
        <button
            v-for="cat in categories"
            :key="cat.id"
            @click="selectedCat = cat.id"
            :class="
                selectedCat == cat.id
                    ? 'bg-orange-600 text-white shadow-lg shadow-orange-100'
                    : 'bg-white text-gray-500 border border-gray-100'
            "
            class="px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest transition-all whitespace-nowrap"
        >
            {{ cat.name }}
        </button>
    </div>

    <section class="mb-16">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-3xl font-black text-gray-800 tracking-tight">
                Món ngon dành cho bạn
            </h2>
            <button
                v-if="search || selectedCat"
                @click="resetFilters"
                class="text-orange-500 font-bold text-sm hover:underline"
            >
                Xóa lọc
            </button>
        </div>

        <div
            v-if="products.length > 0"
            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6"
        >
            <div
                v-for="product in products"
                :key="product.id"
                @click="openProductModal(product)"
                class="bg-white rounded-[2rem] p-3 border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all cursor-pointer group"
            >
                <div
                    class="relative h-40 rounded-[1.5rem] overflow-hidden mb-4"
                >
                    <img
                        :src="'/storage/' + product.image"
                        class="w-full h-full object-cover group-hover:scale-110 transition duration-500"
                    />
                    <div
                        class="absolute bottom-2 right-2 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-black text-gray-900 shadow-sm"
                    >
                        {{ Number(product.price).toLocaleString() }}đ
                    </div>
                </div>
                <div class="px-2">
                    <h4
                        class="font-bold text-gray-800 line-clamp-1 group-hover:text-orange-600 transition"
                    >
                        {{ product.name }}
                    </h4>
                    <p
                        class="text-[10px] text-gray-400 flex items-center gap-1 mt-1"
                    >
                        <svg
                            class="w-3 h-3 text-orange-400"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"
                            />
                        </svg>
                        {{ product.user?.name }}
                    </p>
                </div>
            </div>
        </div>
        <div
            v-else
            class="text-center py-20 bg-gray-50 rounded-[3rem] border-2 border-dashed border-gray-200"
        >
            <p class="text-4xl mb-4">🔎</p>
            <h3
                class="text-lg font-black text-gray-400 uppercase tracking-widest"
            >
                Không tìm thấy món ăn nào!
            </h3>
        </div>
    </section>

    <section class="mb-20">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-3xl font-black text-gray-800">Quán ăn nổi bật</h2>
        </div>
        <div
            v-if="restaurants.length > 0"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8"
        >
            <div
                v-for="shop in restaurants"
                :key="shop.id"
                class="group bg-white rounded-[2rem] overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300"
            >
                <Link :href="route('restaurant.menu', shop.id)">
                    <div class="relative h-48">
                        <img
                            :src="
                                'https://ui-avatars.com/api/?name=' +
                                shop.name +
                                '&background=ffedd5&color=f97316'
                            "
                            class="w-full h-full object-cover"
                        />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"
                        ></div>
                        <div class="absolute bottom-4 left-4 text-white">
                            <h3 class="text-lg font-black leading-tight">
                                {{ shop.name }}
                            </h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span
                                    class="text-[9px] bg-orange-600 px-2 py-0.5 rounded-full font-bold"
                                    >4.8 ★</span
                                >
                                <p class="text-[10px] opacity-80 font-medium">
                                    Free Ship • 20-30 phút
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
    />
</template>

<style scoped>
/* Ẩn thanh cuộn cho thanh danh mục */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
