<script setup>
import RestaurantLayout from "@/Layouts/RestaurantLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3"; // Xóa usePage
import Swal from "sweetalert2";
// Xóa import watch, computed, useToast từ đây vì Layout sẽ lo

const props = defineProps({
    products: Array,
});

// --- HÀM XÓA SẢN PHẨM VỚI SWEETALERT2 ---
const deleteProduct = (id) => {
    Swal.fire({
        title: '<span class="font-black uppercase tracking-tighter text-gray-800">Tạm ngưng món này?</span>',
        html: '<p class="text-sm text-gray-500 font-medium">Món ăn sẽ được chuyển sang trạng thái <b>Tạm ẩn</b> để bảo vệ dữ liệu đơn hàng cũ.</p>',
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        cancelButtonColor: "#94a3b8",
        confirmButtonText: "ĐÚNG, TẠM ẨN!",
        cancelButtonText: "HỦY",
        background: "#ffffff",
        customClass: {
            popup: "rounded-[2.5rem] p-8",
            confirmButton:
                "rounded-xl font-black text-[10px] uppercase tracking-widest px-6 py-4 transition-all",
            cancelButton:
                "rounded-xl font-black text-[10px] uppercase tracking-widest px-6 py-4",
        },
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route("restaurant.products.destroy", id), {
                preserveScroll: true,
                // Không gọi toast.success ở đây nữa
            });
        }
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(price);
};
</script>

<template>
    <RestaurantLayout>
        <Head title="Quản lý thực đơn" />

        <div class="space-y-8 animate-in fade-in duration-700">
            <div
                class="flex flex-col md:flex-row md:items-end justify-between gap-6"
            >
                <div>
                    <h2
                        class="text-5xl font-black text-gray-900 italic tracking-tighter uppercase leading-none"
                    >
                        Thực đơn 🍱
                    </h2>
                    <p
                        class="text-[10px] font-black text-orange-400 uppercase tracking-[0.3em] mt-3 bg-orange-50 w-fit px-3 py-1 rounded-lg"
                    >
                        Danh sách món ăn đang kinh doanh
                    </p>
                </div>
                <Link
                    :href="route('restaurant.products.create')"
                    class="bg-orange-500 hover:bg-gray-900 text-white px-8 py-4 rounded-[2rem] font-black text-xs uppercase tracking-widest shadow-xl shadow-orange-100 transition-all active:scale-95 flex items-center gap-3"
                >
                    <span class="text-xl">+</span> Thêm món mới
                </Link>
            </div>

            <div
                v-if="products.length > 0"
                class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-8"
            >
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="bg-white rounded-[2.5rem] border border-gray-100 overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 group relative"
                >
                    <div class="relative h-56 overflow-hidden">
                        <img
                            v-if="product.image"
                            :src="'/storage/' + product.image"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                        />
                        <div
                            v-else
                            class="w-full h-full bg-orange-50 flex items-center justify-center text-orange-200"
                        >
                            <span class="text-4xl">🍕</span>
                        </div>

                        <div class="absolute top-4 left-4 flex flex-col gap-2">
                            <span
                                :class="
                                    product.is_available
                                        ? 'bg-green-500'
                                        : 'bg-red-500'
                                "
                                class="text-white text-[9px] font-black px-3 py-1.5 rounded-full uppercase shadow-lg"
                            >
                                {{
                                    product.is_available ? "Đang bán" : "Tạm ẩn"
                                }}
                            </span>
                            <span
                                v-if="product.status === 'pending'"
                                class="bg-orange-400 text-white text-[9px] font-black px-3 py-1.5 rounded-full uppercase shadow-lg animate-pulse"
                            >
                                Chờ duyệt
                            </span>
                        </div>

                        <div
                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3"
                        >
                            <Link
                                :href="
                                    route(
                                        'restaurant.products.edit',
                                        product.id,
                                    )
                                "
                                class="p-3 bg-white text-gray-900 rounded-full hover:bg-blue-500 hover:text-white transition-all"
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
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                    />
                                </svg>
                            </Link>
                            <button
                                @click="deleteProduct(product.id)"
                                class="p-3 bg-white text-red-500 rounded-full hover:bg-red-500 hover:text-white transition-all"
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
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-start gap-2">
                            <div class="flex-1 min-w-0">
                                <p
                                    class="text-[9px] font-black text-orange-400 uppercase tracking-widest truncate"
                                >
                                    {{
                                        product.category?.name ||
                                        "Chưa phân loại"
                                    }}
                                </p>
                                <h3
                                    class="font-black text-gray-800 text-lg leading-tight uppercase mt-1 truncate"
                                >
                                    {{ product.name }}
                                </h3>
                            </div>
                            <p
                                class="text-orange-600 font-black text-lg italic whitespace-nowrap"
                            >
                                {{ formatPrice(product.price) }}
                            </p>
                        </div>

                        <div
                            class="flex items-center gap-4 border-t border-gray-50 pt-4"
                        >
                            <div class="flex items-center gap-1">
                                <span class="text-xs">🕒</span>
                                <span
                                    class="text-[10px] font-bold text-gray-400 uppercase"
                                >
                                    {{ product.available_from }} -
                                    {{ product.available_to }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="text-xs">📦</span>
                                <span
                                    class="text-[10px] font-bold text-gray-400 uppercase"
                                >
                                    {{ product.stock_quantity }} suất
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="product.options?.length"
                            class="flex flex-wrap gap-1"
                        >
                            <span
                                class="text-[8px] font-black text-gray-400 bg-gray-100 px-2 py-0.5 rounded-md uppercase"
                            >
                                +{{ product.options.length }} Topping
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="bg-white rounded-[3rem] p-24 border-2 border-dashed border-orange-100 flex flex-col items-center justify-center text-center shadow-inner"
            >
                <div
                    class="bg-orange-50 w-24 h-24 rounded-full flex items-center justify-center mb-6 animate-bounce"
                >
                    <span class="text-5xl">🥙</span>
                </div>
                <h3
                    class="text-2xl font-black text-gray-800 uppercase tracking-tighter italic"
                >
                    Gian hàng đang trống
                </h3>
                <p
                    class="text-gray-400 mt-2 mb-8 max-w-xs font-medium text-sm leading-relaxed"
                >
                    Bạn chưa có món ăn nào. Hãy thêm những món "best-seller" của
                    quán nhé!
                </p>
                <Link
                    :href="route('restaurant.products.create')"
                    class="bg-gray-900 text-white px-10 py-4 rounded-2xl font-black text-[11px] uppercase tracking-[0.2em] shadow-2xl hover:bg-orange-500 transition-all active:scale-95"
                >
                    Bắt đầu ngay
                </Link>
            </div>
        </div>
    </RestaurantLayout>
</template>

<style scoped>
.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
