<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const { orders, filters } = defineProps({
    orders: Object,
    filters: Object,
});

const search = ref(filters.search || "");
const statusFilter = ref(filters.status || "");

const applyFilters = () => {
    router.get(
        route("admin.orders.index"),
        {
            search: search.value,
            status: statusFilter.value,
            page: 1, // Reset to first page when filtering
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const clearFilters = () => {
    search.value = "";
    statusFilter.value = "";
    applyFilters();
};

const goToPage = (page) => {
    router.get(
        route("admin.orders.index"),
        {
            search: search.value,
            status: statusFilter.value,
            page: page,
            pageSize: orders.per_page,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const changePageSize = (pageSize) => {
    router.get(
        route("admin.orders.index"),
        {
            search: search.value,
            status: statusFilter.value,
            page: 1,
            pageSize: pageSize,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

// Watch for changes in search and statusFilter to apply filters
watch([search, statusFilter], () => {
    applyFilters();
});
</script>

<template>
    <AdminLayout>
        <Head title="Quản lý Đơn hàng" />

        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Quản lý Đơn hàng
                    </h1>
                </div>

                <!-- Filters -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Tìm kiếm
                            </label>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Mã đơn hoặc số điện thoại..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                        <div class="md:w-48">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Trạng thái
                            </label>
                            <select
                                v-model="statusFilter"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Tất cả</option>
                                <option value="pending">Chờ xử lý</option>
                                <option value="confirmed">Đã xác nhận</option>
                                <option value="preparing">Đang chuẩn bị</option>
                                <option value="ready">Sẵn sàng giao</option>
                                <option value="delivering">Đang giao</option>
                                <option value="delivered">Đã giao</option>
                                <option value="cancelled">Đã hủy</option>
                            </select>
                        </div>
                        <div class="md:w-32 flex items-end">
                            <button
                                @click="clearFilters"
                                class="w-full px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition"
                            >
                                Xóa lọc
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Mã đơn
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Thời gian đặt
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Khách hàng
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Tổng tiền
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Thanh toán
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Trạng thái
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-if="orders.data.length === 0"
                                class="text-center"
                            >
                                <td
                                    colspan="6"
                                    class="px-6 py-4 text-sm text-gray-500"
                                >
                                    Không có đơn hàng nào phù hợp.
                                </td>
                            </tr>
                            <tr
                                v-for="order in orders.data"
                                :key="order.id"
                                class="hover:bg-gray-50"
                            >
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                >
                                    {{ order.order_code }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{
                                        new Date(
                                            order.created_at,
                                        ).toLocaleString("vi-VN")
                                    }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    <div>
                                        <div class="font-medium">
                                            {{ order.user.name }}
                                        </div>
                                        <div class="text-gray-500">
                                            {{ order.phone }}
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    {{
                                        new Intl.NumberFormat("vi-VN", {
                                            style: "currency",
                                            currency: "VND",
                                        }).format(order.total)
                                    }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{
                                        order.payment_method === "cod"
                                            ? "Thanh toán khi nhận hàng"
                                            : "Thanh toán online"
                                    }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="{
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                            'bg-yellow-100 text-yellow-800':
                                                order.status === 'pending',
                                            'bg-blue-100 text-blue-800':
                                                order.status === 'confirmed',
                                            'bg-orange-100 text-orange-800':
                                                order.status === 'preparing',
                                            'bg-green-100 text-green-800':
                                                order.status === 'ready' ||
                                                order.status === 'delivered',
                                            'bg-purple-100 text-purple-800':
                                                order.status === 'delivering',
                                            'bg-red-100 text-red-800':
                                                order.status === 'cancelled',
                                        }"
                                    >
                                        {{ getStatusLabel(order.status) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-700">
                                Hiển thị {{ orders.from || 0 }} đến
                                {{ orders.to || 0 }} trong tổng số
                                {{ orders.total }} đơn hàng
                            </span>
                            <select
                                @change="changePageSize($event.target.value)"
                                :value="orders.per_page"
                                class="text-sm border border-gray-300 rounded px-2 py-1"
                            >
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span class="text-sm text-gray-700"
                                >bản ghi/trang</span
                            >
                        </div>
                        <div class="flex space-x-1">
                            <button
                                v-if="orders.prev_page_url"
                                @click="goToPage(orders.current_page - 1)"
                                class="px-3 py-1 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50"
                            >
                                Trước
                            </button>
                            <template
                                v-for="page in getVisiblePages()"
                                :key="page"
                            >
                                <button
                                    v-if="page === '...'"
                                    class="px-3 py-1 text-sm bg-white border border-gray-300 rounded cursor-default"
                                    disabled
                                >
                                    ...
                                </button>
                                <button
                                    v-else
                                    @click="goToPage(page)"
                                    :class="{
                                        'px-3 py-1 text-sm border rounded': true,
                                        'bg-blue-500 text-white border-blue-500':
                                            page === orders.current_page,
                                        'bg-white border-gray-300 hover:bg-gray-50':
                                            page !== orders.current_page,
                                    }"
                                >
                                    {{ page }}
                                </button>
                            </template>
                            <button
                                v-if="orders.next_page_url"
                                @click="goToPage(orders.current_page + 1)"
                                class="px-3 py-1 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50"
                            >
                                Sau
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script>
export default {
    methods: {
        getStatusLabel(status) {
            const labels = {
                pending: "Chờ xử lý",
                confirmed: "Đã xác nhận",
                preparing: "Đang chuẩn bị",
                ready: "Sẵn sàng giao",
                delivering: "Đang giao",
                delivered: "Đã giao",
                cancelled: "Đã hủy",
            };
            return labels[status] || status;
        },
        getVisiblePages() {
            const current = this.orders.current_page;
            const last = this.orders.last_page;
            const pages = [];

            if (last <= 7) {
                for (let i = 1; i <= last; i++) {
                    pages.push(i);
                }
            } else {
                pages.push(1);
                if (current > 4) pages.push("...");
                const start = Math.max(2, current - 1);
                const end = Math.min(last - 1, current + 1);
                for (let i = start; i <= end; i++) {
                    pages.push(i);
                }
                if (current < last - 3) pages.push("...");
                pages.push(last);
            }

            return pages;
        },
    },
};
</script>
