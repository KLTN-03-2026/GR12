<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import ReviewForm from "@/Pages/Customer/ProductDetailModal.vue";
import { ref, computed } from "vue";

defineOptions({ layout: GuestLayout });

const props = defineProps({
    // Sử dụng cấu trúc phân trang của Laravel (LengthAwarePaginator)
    orders: Object,
});

const showReviewModal = ref(false);
const selectedProduct = ref(null);
const { auth } = usePage().props;

// Kiểm tra phiên đăng nhập (Tiêu chí 8 & 9)
const isLoggedIn = computed(() => !!auth.user);

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

const getStatusStyle = (status) => {
    const styles = {
        pending: "bg-amber-50 text-amber-600 border-amber-100",
        processing: "bg-blue-50 text-blue-600 border-blue-100",
        confirmed: "bg-indigo-50 text-indigo-600 border-indigo-100",
        shipping: "bg-purple-50 text-purple-600 border-purple-100",
        picked_up: "bg-orange-50 text-orange-600 border-orange-100",
        completed: "bg-green-50 text-green-600 border-green-100",
        cancelled: "bg-red-50 text-red-600 border-red-100",
    };
    return styles[status] || "bg-gray-50 text-gray-500 border-gray-100";
};

const getStatusText = (status) => {
    const statuses = {
        pending: "Chờ xác nhận",
        processing: "Đang chuẩn bị",
        confirmed: "Đã gán tài xế",
        shipping: "Đang giao hàng",
        picked_up: "Shipper đã lấy hàng",
        completed: "Giao thành công",
        cancelled: "Đã hủy",
    };
    return statuses[status] || status;
};
</script>

<template>
    <Head title="Đơn hàng của tôi - FoodXpress" />

    <div class="min-h-screen bg-[#f1f3f6] py-8 md:py-16 px-4">
        <div class="max-w-5xl mx-auto">
            <div
                class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4"
            >
                <div>
                    <h1
                        class="text-3xl md:text-5xl font-black text-slate-900 tracking-tighter italic"
                    >
                        Đơn Hàng Của Bạn <span class="text-orange-500">.</span>
                    </h1>
                    <p class="text-slate-500 font-medium mt-2">
                        Quản lý và theo dõi hành trình món ngon của bạn
                    </p>
                </div>
                <div
                    v-if="isLoggedIn"
                    class="bg-white px-6 py-3 rounded-2xl shadow-sm border border-white flex items-center gap-3"
                >
                    <div
                        class="w-2 h-2 bg-green-500 rounded-full animate-pulse"
                    ></div>
                    <span class="text-sm font-bold text-slate-700"
                        >Tài khoản: {{ auth.user.name }}</span
                    >
                </div>
            </div>

            <div
                v-if="!isLoggedIn"
                class="bg-white rounded-[3rem] p-12 text-center shadow-xl shadow-slate-200/50 border border-white"
            >
                <div class="text-6-xl mb-6">🔒</div>
                <h2 class="text-2xl font-black text-slate-900 mb-4 uppercase">
                    Phiên làm việc hết hạn
                </h2>
                <p class="text-slate-500 mb-8 max-w-md mx-auto font-medium">
                    Vui lòng đăng nhập để xem lại lịch sử đơn hàng và theo dõi
                    các món ăn đang giao.
                </p>
                <Link
                    :href="route('login')"
                    class="inline-block bg-slate-900 text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-orange-500 transition-all shadow-lg"
                >
                    Đăng nhập ngay
                </Link>
            </div>

            <div
                v-else-if="orders.data && orders.data.length > 0"
                class="space-y-6"
            >
                <div
                    v-for="order in orders.data"
                    :key="order.id"
                    class="bg-white rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.02)] border border-white overflow-hidden hover:shadow-xl hover:shadow-orange-100/50 transition-all duration-500 group"
                >
                    <div
                        class="p-6 md:p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-slate-50 bg-slate-50/30"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="bg-white p-3 rounded-2xl shadow-sm border border-slate-100"
                            >
                                <span class="text-2xl">📦</span>
                            </div>
                            <div>
                                <h2
                                    class="text-lg font-black text-slate-900 tracking-tight"
                                >
                                    #{{ order.order_code }}
                                </h2>
                                <p
                                    class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5"
                                >
                                    {{
                                        new Date(
                                            order.created_at,
                                        ).toLocaleString("vi-VN")
                                    }}
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <div
                                class="px-4 py-2 bg-slate-900 text-white text-[10px] font-black rounded-xl uppercase tracking-wider"
                            >
                                {{
                                    order.payment_method === "cod"
                                        ? "Tiền mặt"
                                        : "VNPay"
                                }}
                            </div>
                            <div
                                :class="getStatusStyle(order.status)"
                                class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border"
                            >
                                {{ getStatusText(order.status) }}
                            </div>
                        </div>
                    </div>

                    <div class="p-6 md:p-8 space-y-4">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex items-center justify-between gap-4"
                        >
                            <div class="flex items-center gap-4 flex-1 min-w-0">
                                <img
                                    :src="'/storage/' + item.product.image"
                                    class="w-14 h-14 rounded-xl object-cover border border-slate-100"
                                />
                                <div class="flex-1 min-w-0">
                                    <h4
                                        class="font-black text-slate-800 text-sm truncate italic"
                                    >
                                        {{ item.product.name }}
                                    </h4>
                                    <p
                                        class="text-xs text-slate-400 font-bold mt-1 uppercase"
                                    >
                                        {{ item.quantity }} phần •
                                        {{ formatPrice(item.price) }}
                                    </p>
                                </div>
                            </div>
                            <div class="hidden sm:block text-right">
                                <p class="font-black text-slate-900 text-sm">
                                    {{
                                        formatPrice(item.price * item.quantity)
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="p-6 md:p-8 bg-slate-50/50 border-t border-slate-50 flex flex-col md:flex-row justify-between items-center gap-6"
                    >
                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <div
                                class="bg-white p-2 rounded-lg border border-slate-100 text-slate-400"
                            >
                                📍
                            </div>
                            <p
                                class="text-sm text-slate-500 font-medium line-clamp-1 truncate max-w-xs"
                            >
                                {{ order.address }}
                            </p>
                        </div>

                        <div
                            class="flex items-center justify-between md:justify-end w-full md:w-auto gap-8"
                        >
                            <div class="text-right">
                                <p
                                    class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1"
                                >
                                    Tổng cộng
                                </p>
                                <p
                                    class="text-2xl font-black text-orange-600 tracking-tighter"
                                >
                                    {{ formatPrice(order.total) }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="route('orders.show', order.id)"
                                    class="p-4 bg-slate-900 text-white rounded-2xl hover:bg-orange-500 transition-all shadow-lg shadow-slate-100"
                                >
                                    <span class="sr-only">Chi tiết</span>
                                    →
                                </Link>
                                <button
                                    v-if="order.status === 'completed'"
                                    @click="
                                        openReviewModal(order.items[0].product)
                                    "
                                    class="px-5 py-3 bg-white border border-slate-200 rounded-2xl text-slate-700 font-black text-[10px] uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm"
                                >
                                    ⭐ Đánh Giá
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="orders.links && orders.links.length > 3"
                    class="flex justify-center items-center gap-2 pt-8"
                >
                    <template v-for="(link, k) in orders.links" :key="k">
                        <div
                            v-if="link.url === null"
                            class="px-4 py-2 text-slate-400 text-xs font-black uppercase"
                            v-html="link.label"
                        ></div>
                        <Link
                            v-else
                            :href="link.url"
                            class="px-4 py-2 rounded-xl text-xs font-black transition-all border"
                            :class="
                                link.active
                                    ? 'bg-orange-500 text-white border-orange-500 shadow-lg shadow-orange-100'
                                    : 'bg-white text-slate-600 border-slate-200 hover:border-orange-500'
                            "
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>

            <div
                v-else
                class="text-center py-24 bg-white rounded-[4rem] border-4 border-dashed border-slate-100"
            >
                <div
                    class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl shadow-inner"
                >
                    🍽️
                </div>
                <h3
                    class="text-2xl font-black text-slate-900 uppercase italic tracking-widest"
                >
                    Bụng đang đói?
                </h3>
                <p class="text-slate-400 font-medium mt-2">
                    Bạn chưa thực hiện đơn hàng nào trên FoodXpress.
                </p>
                <Link
                    :href="route('home')"
                    class="mt-8 inline-block bg-orange-500 text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest shadow-xl shadow-orange-200 hover:scale-105 transition-all"
                >
                    Khám phá quán ngon
                </Link>
            </div>
        </div>

        <Teleport to="body">
            <div
                v-if="showReviewModal"
                @click="showReviewModal = false"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4"
            >
                <div
                    @click.stop
                    class="bg-white rounded-[3rem] shadow-2xl max-w-xl w-full max-h-[85vh] overflow-hidden flex flex-col"
                >
                    <div
                        class="p-8 border-b border-slate-50 flex justify-between items-center"
                    >
                        <h2
                            class="text-xl font-black text-slate-900 uppercase tracking-tight"
                        >
                            Đánh giá món ăn
                        </h2>
                        <button
                            @click="showReviewModal = false"
                            class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-slate-400 hover:text-red-500 transition-all"
                        >
                            ✕
                        </button>
                    </div>
                    <div class="p-8 overflow-y-auto flex-1">
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
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap");
* {
    font-family: "Inter", sans-serif;
}

/* Trình cuộn đẹp hơn cho modal */
::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
</style>
