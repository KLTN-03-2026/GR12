<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";

// Khai báo state
const products = ref([]);
const loading = ref(true);
const cart = ref([]);

// Gọi API lấy dữ liệu khi trang web vừa load
onMounted(async () => {
    try {
        const response = await axios.get("/api/products");
        products.value = response.data.data;
    } catch (error) {
        console.error("Lỗi khi lấy dữ liệu:", error);
    } finally {
        loading.value = false;
    }
});

// Hàm thêm vào giỏ hàng
const addToCart = (product) => {
    cart.value.push(product);
};

// Tính tổng số lượng trong giỏ
const cartCount = computed(() => cart.value.length);
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <nav class="bg-white shadow-sm sticky top-0 z-50">
            <div
                class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center"
            >
                <div class="flex items-center gap-2">
                    <div class="bg-orange-500 p-2 rounded-lg">
                        <svg
                            class="w-6 h-6 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
                            ></path>
                        </svg>
                    </div>
                    <span
                        class="text-2xl font-black text-gray-800 tracking-tighter"
                        >FoodXpress</span
                    >
                </div>

                <div class="flex items-center gap-6">
                    <a
                        href="#"
                        class="text-gray-600 hover:text-orange-500 font-medium transition"
                        >Thực đơn</a
                    >
                    <a
                        href="#"
                        class="text-gray-600 hover:text-orange-500 font-medium transition"
                        >Đơn hàng</a
                    >
                    <div
                        class="bg-gray-100 p-2 rounded-full relative cursor-pointer"
                    >
                        <svg
                            class="w-6 h-6 text-gray-700"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M16 11V7a4 4 0 118 0v4M5 9h14l1 12H4L5 9z"
                            ></path>
                        </svg>
                        <span
                            class="absolute -top-1 -right-1 bg-orange-600 text-white text-[10px] w-5 h-5 flex items-center justify-center rounded-full border-2 border-white font-bold"
                        >
                            {{ cartCount }}
                        </span>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-6xl mx-auto p-6">
            <div v-if="loading" class="text-center py-20">
                <div
                    class="animate-spin inline-block w-8 h-8 border-4 border-orange-500 border-t-transparent rounded-full mb-4"
                ></div>
                <p class="text-gray-500 font-medium">
                    Đang tìm món ngon cho bạn...
                </p>
            </div>

            <div
                v-else
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8"
            >
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100"
                >
                    <div class="relative overflow-hidden">
                        <img
                            :src="
                                product.image
                                    ? '/storage/products/' + product.image
                                    : 'https://via.placeholder.com/400x300?text=FoodXpress'
                            "
                            :alt="product.name"
                            class="w-full h-52 object-cover group-hover:scale-110 transition-transform duration-500"
                        />
                        <div class="absolute top-3 left-3">
                            <span
                                class="bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-semibold text-gray-700 shadow-sm"
                            >
                                {{ product.category?.name || "Món ăn" }}
                            </span>
                        </div>
                    </div>

                    <div class="p-5">
                        <h2
                            class="text-xl font-bold text-gray-800 mb-2 group-hover:text-orange-600 transition-colors"
                        >
                            {{ product.name }}
                        </h2>
                        <p class="text-gray-500 text-sm line-clamp-2 mb-4 h-10">
                            {{ product.description }}
                        </p>

                        <div
                            class="flex justify-between items-center border-t pt-4"
                        >
                            <div class="flex flex-col">
                                <span
                                    class="text-xs text-gray-400 uppercase font-semibold"
                                    >Giá bán</span
                                >
                                <span
                                    class="text-orange-600 font-black text-xl"
                                >
                                    {{
                                        Number(product.price).toLocaleString(
                                            "vi-VN",
                                        )
                                    }}đ
                                </span>
                            </div>
                            <button
                                @click="addToCart(product)"
                                class="bg-orange-500 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-orange-600 active:scale-95 transition-all shadow-lg shadow-orange-200"
                            >
                                Thêm +
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
