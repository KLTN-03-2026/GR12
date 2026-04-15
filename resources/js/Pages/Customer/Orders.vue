<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import ReviewForm from "@/Pages/Customer/ProductDetailModal.vue";
import { ref } from "vue";

defineOptions({ layout: GuestLayout });

const props = defineProps({
    orders: Array,
});

const showReviewModal = ref(false);
const selectedProduct = ref(null);

const openReviewModal = (product) => {
    selectedProduct.value = product;
    showReviewModal.value = true;
};

const closeReviewModal = () => {
    showReviewModal.value = false;
    selectedProduct.value = null;
};

const formatPrice = (p) =>
    new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(p);

// Hàm định dạng màu sắc cho từng trạng thái
const getStatusStyle = (status) => {
    switch (status) {
        case "pending":
            return "bg-amber-100 text-amber-600 border-amber-200";
        case "processing":
            return "bg-blue-100 text-blue-600 border-blue-200";
        case "shipping":
            return "bg-purple-100 text-purple-600 border-purple-200";
        case "completed":
            return "bg-green-100 text-green-600 border-green-200";
        case "cancelled":
            return "bg-red-100 text-red-600 border-red-200";
        default:
            return "bg-gray-100 text-gray-600";
    }
};

const getStatusText = (status) => {
    const statuses = {
        pending: "⏳ Đang chờ xác nhận",
        processing: "👨‍🍳 Đang chuẩn bị món",
        shipping: "🛵 Đang giao hàng",
        completed: "✅ Đã giao thành công",
        cancelled: "❌ Đã hủy",
    };
    return statuses[status] || status;
};
</script>

<template>
    <Head title="Đơn hàng của tôi - FoodXpress" />

    <div class="min-h-screen bg-[#f8f9fb] py-12 px-4">
        <div class="max-w-4xl mx-auto">
            <h1
                class="text-4xl font-black text-gray-900 mb-10 tracking-tighter italic flex items-center gap-4"
            >
                Lịch sử đặt hàng 📋
            </h1>

            <div v-if="orders.length > 0" class="space-y-8">
                <div
                    v-for="order in orders"
                    :key="order.id"
                    class="bg-white rounded-[2.5rem] overflow-hidden shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-500 group"
                >
                    <div
                        class="p-6 md:p-8 flex flex-wrap justify-between items-center gap-4 border-b border-gray-50 bg-gray-50/30"
                    >
                        <div>
                            <p
                                class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1"
                            >
                                Mã đơn hàng
                            </p>
                            <h2
                                class="text-xl font-black text-gray-800 tracking-tight"
                            >
                                #{{ order.order_code }}
                            </h2>
                        </div>
                        <div
                            :class="getStatusStyle(order.status)"
                            class="px-5 py-2 rounded-2xl text-xs font-black uppercase tracking-widest border shadow-sm"
                        >
                            {{ getStatusText(order.status) }}
                        </div>
                    </div>

                    <div class="p-6 md:p-8 space-y-6">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex items-center justify-between gap-4 group/item"
                        >
                            <div class="flex items-center gap-4 flex-1">
                                <div
                                    class="w-16 h-16 rounded-2xl overflow-hidden shadow-md shrink-0 border border-gray-100"
                                >
                                    <img
                                        :src="'/storage/' + item.product.image"
                                        class="w-full h-full object-cover"
                                    />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4
                                        class="font-black text-gray-800 line-clamp-1 italic"
                                    >
                                        {{ item.product.name }}
                                    </h4>
                                    <p
                                        class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1"
                                    >
                                        Số lượng: {{ item.quantity }} •
                                        {{ formatPrice(item.price) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div
                                    class="text-right font-black text-gray-700"
                                >
                                    {{
                                        formatPrice(item.price * item.quantity)
                                    }}
                                </div>
                                <Link
                                    :href="`/my-orders/${order.id}`"
                                    class="px-3 py-2 bg-blue-600 hover:bg-blue-700 border border-blue-200 rounded-lg text-white font-black text-xs uppercase tracking-widest transition-all duration-200 hover:shadow-md"
                                >
                                    Xem chi tiết
                                </Link>
                                <button
                                    v-if="order.status === 'completed'"
                                    @click="openReviewModal(item.product)"
                                    class="px-3 py-2 bg-orange-50 hover:bg-orange-100 border border-orange-200 rounded-lg text-orange-600 font-black text-xs uppercase tracking-widest transition-all duration-200 hover:shadow-md"
                                    title="Đánh giá sản phẩm này"
                                >
                                    ⭐ Đánh giá
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        class="p-6 md:p-8 bg-gray-50/50 border-t border-gray-50 flex flex-wrap justify-between items-end gap-6"
                    >
                        <div class="space-y-2">
                            <div
                                class="flex items-center gap-2 text-gray-500 text-sm"
                            >
                                <span class="text-lg">📍</span>
                                <span class="font-bold">{{
                                    order.address
                                }}</span>
                            </div>
                            <div
                                class="flex items-center gap-2 text-gray-400 text-[10px] font-black uppercase"
                            >
                                📅
                                {{
                                    new Date(order.created_at).toLocaleString(
                                        "vi-VN",
                                    )
                                }}
                            </div>
                        </div>
                        <div class="text-right">
                            <p
                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1"
                            >
                                Tổng thanh toán
                            </p>
                            <p
                                class="text-3xl font-black text-orange-600 tracking-tighter italic"
                            >
                                {{ formatPrice(order.total) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="text-center py-32 bg-white rounded-[4rem] border-4 border-dashed border-gray-100"
            >
                <p class="text-7xl mb-6">📦</p>
                <h3
                    class="text-2xl font-black text-gray-400 uppercase italic tracking-widest"
                >
                    Bạn chưa có đơn hàng nào!
                </h3>
                <Link
                    :href="route('home')"
                    class="mt-8 inline-block bg-orange-500 text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-orange-100 hover:scale-105 transition"
                >
                    Đặt món ngay thôi
                </Link>
            </div>
        </div>

        <!-- Review Modal -->
        <Teleport to="body">
            <div
                v-if="showReviewModal"
                @click="showReviewModal = false"
                class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            >
                <div
                    @click.stop
                    class="bg-white rounded-[2.5rem] shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
                >
                    <div
                        class="sticky top-0 bg-white flex justify-between items-center p-6 border-b border-gray-100 z-10"
                    >
                        <h2 class="text-xl font-black text-gray-900">
                            Đánh giá sản phẩm
                        </h2>
                        <button
                            @click="showReviewModal = false"
                            class="text-2xl text-gray-400 hover:text-gray-600 transition"
                        >
                            ✕
                        </button>
                    </div>
                    <div class="p-6">
                        <ReviewForm
                            v-if="selectedProduct"
                            :product="selectedProduct"
                            @close="closeReviewModal"
                        />
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
* {
    font-family: "Inter", sans-serif;
}
</style>
