<script setup>
import { Head, router } from "@inertiajs/vue3";
import RestaurantLayout from "@/Layouts/RestaurantLayout.vue"; // Đảm bảo dùng đúng Layout

defineOptions({ layout: RestaurantLayout });

const props = defineProps({ orders: Object });

const formatPrice = (p) =>
    new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(p);

const changeStatus = (id, newStatus) => {
    router.patch(
        route("restaurant.orders.update", id),
        { status: newStatus },
        { preserveScroll: true },
    );
};

// Định nghĩa Text hiển thị tiếng Việt cho trạng thái
const statusLabel = {
    pending: "Đang chờ",
    processing: "Đang nấu",
    confirmed: "Sẵn sàng giao",
    assigned: "Đã gán shipper",
    shipping: "Đang giao",
    completed: "Hoàn tất",
    cancelled: "Đã hủy",
};

const getStatusStyle = (status) => {
    const base =
        "px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-tighter border shadow-sm ";
    switch (status) {
        case "pending":
            return base + "bg-amber-50 text-amber-600 border-amber-200";
        case "processing":
            return base + "bg-blue-50 text-blue-600 border-blue-200";
        case "confirmed":
            return base + "bg-emerald-50 text-emerald-600 border-emerald-200";
        case "assigned":
            return base + "bg-yellow-50 text-yellow-700 border-yellow-200";
        case "shipping":
            return base + "bg-purple-50 text-purple-600 border-purple-200";
        case "completed":
            return base + "bg-green-50 text-green-600 border-green-200";
        default:
            return base + "bg-red-50 text-red-600 border-red-200";
    }
};
</script>

<template>
    <Head title="Quản lý đơn hàng - FoodXpress" />

    <div class="max-w-6xl mx-auto space-y-10">
        <header
            class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6"
        >
            <div class="space-y-1">
                <h1
                    class="text-5xl font-black text-gray-900 tracking-tighter italic flex items-center gap-3"
                >
                    Đơn hàng mới <span class="animate-bounce text-4xl">🔥</span>
                </h1>
                <p
                    class="text-gray-400 font-bold uppercase text-[10px] tracking-[0.4em] ml-1"
                >
                    Hôm nay quán bạn có {{ orders.total }} đơn hàng
                </p>
            </div>

            <div class="flex gap-4">
                <div
                    class="bg-white p-6 rounded-[2rem] shadow-xl shadow-orange-100/50 border border-orange-50 flex items-center gap-4"
                >
                    <div
                        class="w-12 h-12 bg-orange-500 rounded-2xl flex items-center justify-center text-2xl shadow-lg shadow-orange-200 text-white"
                    >
                        💰
                    </div>
                    <div>
                        <span
                            class="text-[10px] font-black text-gray-400 uppercase block tracking-widest"
                            >Doanh thu</span
                        >
                        <span
                            class="text-xl font-black text-gray-900 tracking-tighter italic"
                            >Nổ đơn liên tục!</span
                        >
                    </div>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 gap-8">
            <div
                v-for="order in orders.data"
                :key="order.id"
                class="bg-white rounded-[3.5rem] p-2 shadow-sm border border-gray-100 hover:shadow-2xl hover:shadow-orange-200/20 transition-all duration-700 group flex flex-col lg:flex-row"
            >
                <div
                    class="p-8 lg:w-1/4 bg-gray-50/50 rounded-[3rem] border border-white flex flex-col justify-center text-center lg:text-left"
                >
                    <span
                        class="text-[10px] font-black text-orange-400 uppercase tracking-[0.3em] mb-2 block"
                        >#{{ order.order_code }}</span
                    >
                    <h3
                        class="font-black text-gray-900 text-2xl leading-tight mb-2 italic"
                    >
                        {{ order.user.name }}
                    </h3>
                    <p
                        class="text-sm font-bold text-gray-400 flex items-center justify-center lg:justify-start gap-2"
                    >
                        <span>📞</span> {{ order.phone }}
                    </p>
                    <div class="mt-6">
                        <span :class="getStatusStyle(order.status)">
                            {{ statusLabel[order.status] }}
                        </span>
                    </div>
                </div>

                <div class="p-8 lg:w-2/4 flex flex-col justify-center">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="h-[2px] flex-1 bg-gray-100"></div>
                        <span
                            class="text-[9px] font-black text-gray-300 uppercase tracking-widest"
                            >Danh sách món</span
                        >
                        <div class="h-[2px] flex-1 bg-gray-100"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex items-center gap-4 bg-gray-50 p-3 rounded-2xl border border-transparent hover:border-orange-200 transition-colors"
                        >
                            <div
                                class="w-12 h-12 rounded-xl overflow-hidden shrink-0 border-2 border-white shadow-sm"
                            >
                                <img
                                    :src="'/storage/' + item.product.image"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p
                                    class="text-sm font-black text-gray-700 truncate"
                                >
                                    {{ item.product.name }}
                                </p>
                                <p
                                    class="text-[10px] font-bold text-orange-500 uppercase tracking-tighter"
                                >
                                    Số lượng: {{ item.quantity }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="p-8 lg:w-1/4 flex flex-col justify-center items-center lg:items-end gap-6 bg-gradient-to-br from-white to-orange-50/30 rounded-[3rem]"
                >
                    <div class="text-center lg:text-right">
                        <p
                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1"
                        >
                            Thanh toán
                        </p>
                        <p
                            class="text-4xl font-black text-gray-900 tracking-tighter italic leading-none"
                        >
                            {{ formatPrice(order.total) }}
                        </p>
                    </div>

                    <div class="w-full space-y-3">
                        <button
                            v-if="order.status === 'pending'"
                            @click="changeStatus(order.id, 'processing')"
                            class="w-full bg-gray-900 text-white font-black py-4 rounded-[1.5rem] shadow-xl hover:bg-orange-600 hover:-translate-y-1 transition-all uppercase text-[10px] tracking-widest"
                        >
                            Bắt đầu nấu ngay 👨‍🍳
                        </button>

                        <button
                            v-if="order.status === 'processing'"
                            @click="changeStatus(order.id, 'confirmed')"
                            class="w-full bg-orange-500 text-white font-black py-4 rounded-[1.5rem] shadow-xl shadow-orange-200 hover:scale-105 transition-all uppercase text-[10px] tracking-widest"
                        >
                            Xác nhận hoàn thành món ✅
                        </button>

                        <button
                            v-if="order.status === 'confirmed'"
                            @click="changeStatus(order.id, 'assigned')"
                            class="w-full bg-emerald-600 text-white font-black py-4 rounded-[1.5rem] shadow-xl shadow-emerald-200 hover:scale-105 transition-all uppercase text-[10px] tracking-widest"
                        >
                            Gán cho shipper 🛵
                        </button>

                        <div
                            v-if="order.status === 'assigned'"
                            class="w-full rounded-[1.5rem] bg-emerald-50 text-emerald-700 font-black py-4 px-6 text-center border border-emerald-100"
                        >
                            Đã gán shipper, chờ xác nhận
                        </div>

                        <button
                            v-if="order.status === 'shipping'"
                            @click="changeStatus(order.id, 'completed')"
                            class="w-full bg-green-500 text-white font-black py-4 rounded-[1.5rem] shadow-xl shadow-green-200 hover:scale-105 transition-all uppercase text-[10px] tracking-widest"
                        >
                            Khách đã nhận hàng ✅
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="orders.data.length === 0"
            class="text-center py-40 bg-white rounded-[4rem] border-4 border-dashed border-gray-50"
        >
            <span class="text-8xl block mb-6">🏜️</span>
            <h3
                class="text-2xl font-black text-gray-300 uppercase tracking-widest italic"
            >
                Quán đang thong thả, chưa có đơn mới
            </h3>
        </div>
    </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap");
* {
    font-family: "Inter", sans-serif;
}

/* Hiệu ứng mượt cho các nút */
button {
    backface-visibility: hidden;
    transform: translateZ(0);
}
</style>
