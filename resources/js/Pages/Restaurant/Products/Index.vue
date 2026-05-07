<script setup>
import RestaurantLayout from "@/Layouts/RestaurantLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3"; // Xóa usePage
import Swal from "sweetalert2";
// Xóa import watch, computed, useToast từ đây vì Layout sẽ lo
defineOptions({ layout: RestaurantLayout });

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
    <Head title="Quản lý thực đơn" />

    <div class="space-y-8 animate-fade-in-up pb-10">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">
                    Thực đơn của quán 🍱
                </h2>
                <p class="text-[10px] font-black text-orange-600 uppercase tracking-[0.25em] mt-3 bg-orange-100/50 w-fit px-4 py-1.5 rounded-xl border border-orange-200/50">
                    Danh sách món ăn đang kinh doanh
                </p>
            </div>
            <Link
                :href="route('restaurant.products.create')"
                class="bg-gradient-to-r from-orange-500 to-orange-600 text-white px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-[0_10px_30px_rgba(249,115,22,0.3)] transition-all hover:scale-105 hover:shadow-[0_15px_40px_rgba(249,115,22,0.4)] active:scale-95 flex items-center gap-3"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                </svg>
                Thêm món mới
            </Link>
        </div>

        <div v-if="products.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-6">
            <div
                v-for="product in products"
                :key="product.id"
                class="bg-white rounded-[2rem] border border-slate-100 overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgb(249,115,22,0.1)] transition-all duration-500 group relative flex flex-col"
            >
                <!-- Image Container -->
                <div class="relative h-56 overflow-hidden bg-slate-50">
                    <img
                        v-if="product.image"
                        :src="'/storage/' + product.image"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-orange-200/50 bg-gradient-to-br from-orange-50 to-orange-100/50">
                        <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>

                    <!-- Status Badges -->
                    <div class="absolute top-4 left-4 flex flex-col gap-2 z-10">
                        <span
                            :class="product.is_available ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white'"
                            class="text-[9px] font-black px-3 py-1.5 rounded-full uppercase shadow-lg backdrop-blur-md"
                        >
                            {{ product.is_available ? "Đang bán" : "Tạm ẩn" }}
                        </span>
                        <span
                            v-if="product.status === 'pending'"
                            class="bg-orange-500 text-white text-[9px] font-black px-3 py-1.5 rounded-full uppercase shadow-lg animate-pulse"
                        >
                            Chờ duyệt
                        </span>
                    </div>

                    <!-- Hover Actions -->
                    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-4 z-20">
                        <Link
                            :href="route('restaurant.products.edit', product.id)"
                            class="w-12 h-12 bg-white text-slate-700 rounded-2xl flex items-center justify-center hover:bg-blue-500 hover:text-white hover:scale-110 transition-all shadow-xl"
                            title="Chỉnh sửa"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </Link>
                        <button
                            @click="deleteProduct(product.id)"
                            class="w-12 h-12 bg-white text-rose-500 rounded-2xl flex items-center justify-center hover:bg-rose-500 hover:text-white hover:scale-110 transition-all shadow-xl"
                            title="Tạm ngưng"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start gap-4 mb-2">
                            <div class="flex-1 min-w-0">
                                <p class="text-[9px] font-black text-orange-500 uppercase tracking-widest truncate mb-1">
                                    {{ product.category?.name || "Chưa phân loại" }}
                                </p>
                                <h3 class="font-black text-slate-800 text-lg leading-snug line-clamp-2">
                                    {{ product.name }}
                                </h3>
                            </div>
                            <p class="text-orange-600 font-black text-xl whitespace-nowrap bg-orange-50 px-3 py-1.5 rounded-xl border border-orange-100">
                                {{ formatPrice(product.price) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-4 mb-3">
                            <div class="flex items-center gap-1.5 bg-slate-50 px-2.5 py-1 rounded-lg">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="text-[10px] font-bold text-slate-500 uppercase">{{ product.available_from }} - {{ product.available_to }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 bg-slate-50 px-2.5 py-1 rounded-lg">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                <span class="text-[10px] font-bold text-slate-500 uppercase">{{ product.stock_quantity }} suất</span>
                            </div>
                        </div>

                        <div v-if="product.options?.length" class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-1 text-[9px] font-black text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg uppercase">
                                <svg class="w-3 h-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                {{ product.options.length }} Topping
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="bg-white rounded-[3rem] p-16 md:p-24 border border-slate-100 flex flex-col items-center justify-center text-center shadow-sm relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-orange-50/50 to-transparent"></div>
            <div class="relative z-10 flex flex-col items-center">
                <div class="bg-orange-100 w-24 h-24 rounded-[2rem] flex items-center justify-center mb-6 animate-bounce shadow-lg shadow-orange-200/50">
                    <span class="text-5xl">🥙</span>
                </div>
                <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-2">
                    Gian hàng đang trống
                </h3>
                <p class="text-slate-500 max-w-md font-medium text-sm leading-relaxed mb-8">
                    Bạn chưa có món ăn nào trong thực đơn. Hãy thêm những món "best-seller" của quán để bắt đầu nhận đơn hàng nhé!
                </p>
                <Link
                    :href="route('restaurant.products.create')"
                    class="bg-slate-900 text-white px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-slate-900/20 hover:bg-orange-500 hover:shadow-orange-500/30 transition-all active:scale-95 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    Tạo món ăn đầu tiên
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}
</style>
