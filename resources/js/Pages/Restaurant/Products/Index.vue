<script setup>
import RestaurantLayout from "@/Layouts/RestaurantLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";

const props = defineProps({
    products: Array,
});

// Hàm định dạng tiền tệ Việt Nam
const formatPrice = (price) => {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(price);
};

// Hàm xóa sản phẩm
const deleteProduct = (id) => {
    if (confirm("Bạn có chắc chắn muốn xóa món ăn này không?")) {
        router.delete(route("restaurant.products.destroy", id));
    }
};
</script>

<template>
    <RestaurantLayout>
        <Head title="Quản lý món ăn" />
        <template>
            <div
                v-if="$page.props.flash.success"
                class="max-w-7xl mx-auto mb-6 p-4 bg-green-50 border border-green-200 rounded-2xl text-green-700 font-bold flex items-center gap-3 shadow-sm animate-fade-in"
            >
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
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                <span>{{ $page.props.flash.success }}</span>
            </div>
        </template>

        <template #header>
            <div class="flex justify-between items-center w-full">
                <span>Thực đơn của quán</span>
                <Link
                    :href="route('restaurant.products.create')"
                    class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg shadow-orange-200 transition-all active:scale-95 flex items-center gap-2"
                >
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M12 4v16m8-8H4"
                            stroke-width="2"
                            stroke-linecap="round"
                        />
                    </svg>
                    Thêm món mới
                </Link>
            </div>
        </template>

        <div
            v-if="products.length > 0"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
        >
            <div
                v-for="product in products"
                :key="product.id"
                class="bg-white rounded-3xl border border-orange-100 overflow-hidden shadow-sm hover:shadow-xl transition-shadow duration-300 group"
            >
                <div class="relative h-48 overflow-hidden">
                    <img
                        v-if="product.image"
                        :src="'/storage/' + product.image"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    />
                    <div
                        v-else
                        class="w-full h-full bg-orange-50 flex items-center justify-center text-orange-300"
                    >
                        <svg
                            class="w-12 h-12"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                stroke-width="2"
                            />
                        </svg>
                    </div>

                    <div class="absolute top-3 right-3">
                        <span
                            :class="
                                product.is_available
                                    ? 'bg-green-500'
                                    : 'bg-gray-500'
                            "
                            class="text-white text-[10px] font-black px-2 py-1 rounded-lg uppercase shadow-sm"
                        >
                            {{ product.is_available ? "Đang bán" : "Hết hàng" }}
                        </span>
                    </div>
                </div>

                <div class="p-5">
                    <h3
                        class="font-black text-gray-800 text-lg line-clamp-1 capitalize"
                    >
                        {{ product.name }}
                    </h3>
                    <p class="text-gray-400 text-xs mt-1 line-clamp-2 h-8">
                        {{
                            product.description ||
                            "Chưa có mô tả cho món ăn này."
                        }}
                    </p>

                    <div class="mt-4 flex justify-between items-end">
                        <div>
                            <p
                                class="text-[10px] text-gray-400 font-bold uppercase tracking-widest"
                            >
                                Giá bán
                            </p>
                            <p class="text-orange-600 font-black text-xl">
                                {{ formatPrice(product.price) }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <Link
                                :href="
                                    route(
                                        'restaurant.products.edit',
                                        product.id,
                                    )
                                "
                                class="p-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-colors"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        stroke-width="2"
                                    />
                                </svg>
                            </Link>
                            <button
                                @click="deleteProduct(product.id)"
                                class="p-2 bg-red-50 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition-colors"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        stroke-width="2"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-else
            class="bg-white rounded-3xl p-20 border-2 border-dashed border-orange-100 flex flex-col items-center justify-center text-center"
        >
            <div class="bg-orange-50 p-6 rounded-full mb-4">
                <svg
                    class="w-16 h-16 text-orange-200"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                        stroke-width="2"
                    />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800">Thực đơn còn trống</h3>
            <p class="text-gray-500 mb-8 max-w-sm">
                Hãy bắt đầu thêm những món ăn ngon nhất của bạn để khách hàng có
                thể đặt mua ngay nhé!
            </p>
            <Link
                :href="route('restaurant.products.create')"
                class="bg-orange-500 text-white px-8 py-3 rounded-2xl font-bold shadow-lg shadow-orange-200 hover:bg-orange-600 transition-all"
            >
                Thêm món đầu tiên
            </Link>
        </div>
    </RestaurantLayout>
</template>
