<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head } from "@inertiajs/vue3";

// 1. PHẢI CÓ DÒNG NÀY: Để nhận dữ liệu từ AdminController truyền sang
defineProps({
    stats: Object,
});

// 2. GIỮ DÒNG NÀY: Để dùng Persistent Layout (Layout cố định)
defineOptions({ layout: AdminLayout });
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div
            class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-blue-500"
        >
            <div class="text-gray-500 text-sm font-bold uppercase">
                Tổng khách hàng
            </div>
            <div class="text-3xl font-black mt-2">
                {{ stats.total_customers }}
            </div>
        </div>

        <div
            class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-orange-500"
        >
            <div class="text-gray-500 text-sm font-bold uppercase">
                Quán ăn chờ duyệt
            </div>
            <div class="text-3xl font-black mt-2 text-orange-600">
                {{ stats.pending_partners || stats.pending_restaurants || 0 }}
            </div>
        </div>

        <div
            class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-yellow-500"
        >
            <div class="text-gray-500 text-sm font-bold uppercase">
                Món ăn chờ duyệt
            </div>
            <div class="text-3xl font-black mt-2 text-yellow-600">
                {{ stats.pending_products || 0 }}
            </div>
        </div>

        <div
            class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-green-500"
        >
            <div class="text-gray-500 text-sm font-bold uppercase">
                Shipper đang hoạt động
            </div>
            <div class="text-3xl font-black mt-2 text-green-600">
                {{ stats.active_shippers }}
            </div>
        </div>
    </div>

    <div
        class="mt-10 bg-white p-8 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4"
    >
        <div class="text-4xl">🚀</div>
        <div>
            <h3 class="text-lg font-bold text-gray-800">Chào Hoàng Anh!</h3>
            <p class="text-gray-500 italic text-sm">
                Chức năng biểu đồ doanh thu và quản lý đơn hàng sẽ được cập nhật
                tại đây sau.
            </p>
        </div>
    </div>
</template>
