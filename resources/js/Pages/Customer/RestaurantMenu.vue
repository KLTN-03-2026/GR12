<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue"; // 1. Import Layout
import { Head, Link, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import ProductDetailModal from "@/Components/ProductDetailModal.vue";

// Thiết lập Layout mặc định
defineOptions({ layout: GuestLayout });

const props = defineProps({
    restaurant: Object,
    menu: Object,
    currentTime: String,
    cartItems: Array,
});

const isModalOpen = ref(false);
const selectedProduct = ref(null);

const openProductModal = (product) => {
    selectedProduct.value = product;
    isModalOpen.value = true;
};

const handleAddToCart = (data) => {
    router.post(
        route("cart.add"),
        {
            product_id: data.product.id,
            quantity: data.quantity,
            options: data.options,
            note: data.note || "",
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            },
        },
    );
};

const totalAmount = computed(() =>
    props.cartItems.reduce(
        (sum, item) => sum + item.product.price * item.quantity,
        0,
    ),
);

const totalItems = computed(() =>
    props.cartItems.reduce((sum, item) => sum + item.quantity, 0),
);

const isSelling = (from, to) =>
    props.currentTime >= from && props.currentTime <= to;

const formatPrice = (p) =>
    new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(p);
</script>

<template>
    <Head :title="restaurant.restaurant_name" />

    <div class="min-h-screen bg-[#f8f9fb] pb-32">
        <div class="relative h-[300px] md:h-[400px] w-full overflow-hidden">
            <div class="absolute inset-0 bg-gray-900">
                <img
                    v-if="restaurant.image"
                    :src="'/storage/' + restaurant.image"
                    class="w-full h-full object-cover opacity-60 scale-105 transition-transform duration-700 hover:scale-100"
                />
                <div
                    class="absolute inset-0 bg-gradient-to-t from-[#f8f9fb] via-transparent to-black/30"
                ></div>
            </div>

            <Link
                href="/"
                class="absolute top-6 left-6 z-50 bg-white/10 backdrop-blur-xl p-3 rounded-2xl text-white border border-white/20 hover:bg-white/20 transition-all shadow-xl"
            >
                <svg
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M15 19l-7-7 7-7"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </Link>

            <div
                class="absolute bottom-10 left-6 right-6 z-20 max-w-5xl mx-auto"
            >
                <div class="flex flex-col gap-3">
                    <span
                        class="bg-orange-500 text-white text-[10px] font-black px-3 py-1.5 rounded-xl w-fit uppercase tracking-widest shadow-lg shadow-orange-500/30"
                    >
                        Đối Tác FoodXpress
                    </span>
                    <h1
                        class="text-5xl md:text-7xl font-black text-white tracking-tighter drop-shadow-2xl italic"
                    >
                        {{ restaurant.name }}
                    </h1>
                    <div
                        class="flex items-center gap-2 text-white/90 font-bold bg-black/20 backdrop-blur-md w-fit px-4 py-2 rounded-2xl border border-white/10"
                    >
                        <svg
                            class="w-5 h-5 text-orange-400"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                            />
                        </svg>
                        <span class="text-sm md:text-base">{{
                            restaurant.address
                        }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-4 -mt-12 relative z-30">
            <div
                class="bg-white/70 backdrop-blur-2xl rounded-[3rem] p-6 md:p-10 shadow-[0_20px_50px_rgba(0,0,0,0.04)] border border-white flex flex-wrap justify-around items-center gap-6"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 bg-orange-100 rounded-2xl flex items-center justify-center text-orange-600 shadow-inner"
                    >
                        <span class="text-xl font-black">4.8</span>
                    </div>
                    <div>
                        <p
                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest"
                        >
                            Đánh giá
                        </p>
                        <div class="flex text-orange-400 text-xs">★★★★★</div>
                    </div>
                </div>

                <div class="h-12 w-px bg-gray-100 hidden md:block"></div>

                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 shadow-inner"
                    >
                        <svg
                            class="w-7 h-7"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                stroke-width="2"
                            />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest"
                        >
                            Giờ mở cửa
                        </p>
                        <p class="text-lg font-black text-gray-800">
                            06:00 - 22:00
                        </p>
                    </div>
                </div>

                <div
                    class="bg-green-500/10 text-green-600 px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 border border-green-500/20"
                >
                    <span
                        class="w-2 h-2 bg-green-500 rounded-full animate-ping"
                    ></span>
                    Đang phục vụ
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-4 mt-20 space-y-24">
            <div
                v-for="(products, categoryName) in menu"
                :key="categoryName"
                class="category-group"
            >
                <div class="flex items-center gap-6 mb-12">
                    <h2
                        class="text-4xl font-black text-gray-900 tracking-tighter italic"
                    >
                        {{ categoryName }}
                    </h2>
                    <div
                        class="h-[2px] flex-1 bg-gradient-to-r from-orange-200 to-transparent"
                    ></div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <div
                        v-for="product in products"
                        :key="product.id"
                        @click="openProductModal(product)"
                        class="group relative bg-white rounded-[2.5rem] p-4 flex gap-6 hover:shadow-[0_30px_60px_-15px_rgba(0,0,0,0.08)] transition-all duration-500 border border-transparent hover:border-orange-100 cursor-pointer"
                        :class="
                            !isSelling(
                                product.available_from,
                                product.available_to,
                            )
                                ? 'opacity-40 grayscale pointer-events-none'
                                : ''
                        "
                    >
                        <div
                            class="relative w-32 h-32 md:w-40 md:h-40 flex-shrink-0 rounded-[2rem] overflow-hidden shadow-2xl transition-transform duration-500 group-hover:scale-105 group-hover:-rotate-2"
                        >
                            <img
                                :src="'/storage/' + product.image"
                                class="w-full h-full object-cover"
                            />
                        </div>

                        <div class="flex-1 flex flex-col justify-center">
                            <h3
                                class="text-2xl font-black text-gray-800 mb-2 group-hover:text-orange-600 transition-colors"
                            >
                                {{ product.name }}
                            </h3>
                            <p
                                class="text-xs text-gray-400 line-clamp-2 mb-4 font-medium leading-relaxed"
                            >
                                {{ product.description }}
                            </p>
                            <div
                                class="flex justify-between items-center mt-auto"
                            >
                                <span
                                    class="text-2xl font-black text-gray-900 tracking-tighter italic"
                                >
                                    {{ formatPrice(product.price) }}
                                </span>
                                <div
                                    class="w-12 h-12 bg-gray-50 text-gray-900 rounded-2xl flex items-center justify-center group-hover:bg-orange-500 group-hover:text-white group-hover:rotate-90 transition-all duration-500 shadow-sm group-hover:shadow-orange-200"
                                >
                                    <svg
                                        class="w-6 h-6"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            d="M12 4v16m8-8H4"
                                            stroke-width="3"
                                            stroke-linecap="round"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="totalItems > 0"
            class="fixed bottom-8 left-0 right-0 z-[100] px-6 pointer-events-none"
        >
            <div class="max-w-md mx-auto pointer-events-auto">
                <Link :href="route('cart.index')" class="block group">
                    <div
                        class="bg-gray-900 rounded-[2.5rem] p-4 flex justify-between items-center shadow-[0_40px_80px_-15px_rgba(0,0,0,0.4)] border border-white/10 group-hover:scale-[1.03] transition-all duration-500"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="w-14 h-14 rounded-[1.5rem] bg-orange-500 flex items-center justify-center font-black text-xl text-white shadow-lg group-hover:animate-bounce"
                            >
                                {{ totalItems }}
                            </div>
                            <div>
                                <span
                                    class="block font-black text-white text-lg uppercase tracking-tight"
                                    >Giỏ hàng</span
                                >
                                <span
                                    class="text-[9px] text-gray-500 font-black uppercase tracking-[0.2em]"
                                    >Cửa hàng: {{ restaurant.name }}</span
                                >
                            </div>
                        </div>
                        <div class="text-right pr-4">
                            <p
                                class="text-[10px] text-gray-500 uppercase font-black mb-1"
                            >
                                Thanh toán
                            </p>
                            <p
                                class="text-orange-400 font-black text-2xl tracking-tighter"
                            >
                                {{ formatPrice(totalAmount) }}
                            </p>
                        </div>
                    </div>
                </Link>
            </div>
        </div>

        <ProductDetailModal
            v-if="selectedProduct"
            :show="isModalOpen"
            :product="selectedProduct"
            @close="isModalOpen = false"
            @addToCart="handleAddToCart"
        />
    </div>
</template>

<style scoped>
.category-group {
    animation: fadeInUp 0.8s ease backwards;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
