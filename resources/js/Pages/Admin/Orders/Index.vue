<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, onMounted, onUnmounted } from "vue";
import axios from "axios";

const props = defineProps({
    orders: Object,
    filters: Object,
    stats: Object
});

const search = ref(props.filters.search || "");
const statusFilter = ref(props.filters.status || "");

// Detailed Order state
const selectedOrder = ref(null);
const showSlideOver = ref(false);
const isLoadingDetails = ref(false);

const applyFilters = () => {
    router.get(
        route("admin.orders.index"),
        {
            search: search.value,
            status: statusFilter.value,
            page: 1,
        },
        { preserveState: true, replace: true }
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
            pageSize: props.orders.per_page,
        },
        { preserveState: true, replace: true }
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
        { preserveState: true, replace: true }
    );
};

watch([search, statusFilter], () => {
    applyFilters();
});

const getStatusLabel = (status) => {
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
};

const getStatusColor = (status) => {
    const colors = {
        pending: "bg-yellow-50 text-yellow-700 ring-yellow-600/20",
        confirmed: "bg-blue-50 text-blue-700 ring-blue-600/20",
        preparing: "bg-orange-50 text-orange-700 ring-orange-600/20",
        ready: "bg-green-50 text-green-700 ring-green-600/20",
        delivering: "bg-purple-50 text-purple-700 ring-purple-600/20",
        delivered: "bg-emerald-50 text-emerald-700 ring-emerald-600/20",
        cancelled: "bg-red-50 text-red-700 ring-red-600/20",
    };
    return colors[status] || "bg-gray-50 text-gray-700 ring-gray-600/20";
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(amount);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString("vi-VN");
};

// Open Slide-over
const openDetails = async (orderId) => {
    showSlideOver.value = true;
    isLoadingDetails.value = true;
    selectedOrder.value = null; // reset
    try {
        const response = await axios.get(route('admin.orders.show', orderId));
        selectedOrder.value = response.data;
    } catch (error) {
        console.error("Failed to load order details", error);
        alert("Lỗi khi tải chi tiết đơn hàng.");
        showSlideOver.value = false;
    } finally {
        isLoadingDetails.value = false;
    }
};

const closeDetails = () => {
    showSlideOver.value = false;
};

const getVisiblePages = () => {
    const current = props.orders.current_page;
    const last = props.orders.last_page;
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
};

const handleKeydown = (e) => {
    if (e.key === 'Escape' && showSlideOver.value) {
        closeDetails();
    }
};

onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleKeydown));
</script>

<template>
    <AdminLayout>
        <Head title="Quản lý Đơn hàng" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header Section -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight">Quản lý Đơn hàng</h1>
                    <p class="mt-1 text-sm text-gray-500">Giám sát và kiểm soát toàn bộ giao dịch trên hệ thống.</p>
                </div>
            </div>

            <!-- Top Stats -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <!-- Stat 1 -->
                <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-gray-100 flex items-center p-5 relative">
                    <div class="p-3 rounded-xl bg-blue-50 text-blue-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 truncate">Tổng số đơn</p>
                        <p class="mt-1 text-2xl font-black text-gray-900">{{ stats?.total || 0 }}</p>
                    </div>
                </div>

                <!-- Stat 2 -->
                <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-gray-100 flex items-center p-5 relative">
                    <div class="p-3 rounded-xl bg-yellow-50 text-yellow-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 truncate">Đang chờ xử lý</p>
                        <p class="mt-1 text-2xl font-black text-gray-900">{{ stats?.pending || 0 }}</p>
                    </div>
                </div>

                <!-- Stat 3 -->
                <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-gray-100 flex items-center p-5 relative">
                    <div class="p-3 rounded-xl bg-purple-50 text-purple-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 truncate">Đang giao hàng</p>
                        <p class="mt-1 text-2xl font-black text-gray-900">{{ stats?.delivering || 0 }}</p>
                    </div>
                </div>

                <!-- Stat 4 -->
                <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-gray-100 flex items-center p-5 relative">
                    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 truncate">Đã giao hôm nay</p>
                        <p class="mt-1 text-2xl font-black text-gray-900">{{ stats?.completed_today || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Main Table Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Filters -->
                <div class="p-5 border-b border-gray-200 bg-gray-50/50">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1 max-w-md relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Tìm theo mã đơn, SĐT khách..."
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out"
                            />
                        </div>
                        <div class="w-full sm:w-48">
                            <select
                                v-model="statusFilter"
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-xl transition duration-150 ease-in-out"
                            >
                                <option value="">Tất cả trạng thái</option>
                                <option value="pending">Chờ xử lý</option>
                                <option value="confirmed">Đã xác nhận</option>
                                <option value="preparing">Đang chuẩn bị</option>
                                <option value="ready">Sẵn sàng giao</option>
                                <option value="delivering">Đang giao</option>
                                <option value="delivered">Đã giao</option>
                                <option value="cancelled">Đã hủy</option>
                            </select>
                        </div>
                        <div class="w-full sm:w-auto">
                            <button
                                @click="clearFilters"
                                class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Xóa lọc
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Thông tin Đơn</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Khách hàng</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Quán & Shipper</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tổng tiền</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-if="orders.data.length === 0">
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="text-base font-medium">Không tìm thấy đơn hàng nào.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-blue-600">#{{ order.order_code }}</span>
                                        <span class="text-xs text-gray-500 mt-0.5">{{ formatDate(order.created_at) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600 mr-3 shrink-0">
                                            {{ order.user?.name?.charAt(0) || 'U' }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-gray-900">{{ order.user?.name || 'Unknown' }}</span>
                                            <span class="text-xs text-gray-500">{{ order.phone }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center text-xs">
                                            <span class="w-4 inline-block text-gray-400">🏪</span>
                                            <span class="font-medium text-gray-700 truncate max-w-[150px]" :title="order.items?.[0]?.product?.user?.restaurant_name">
                                                {{ order.items?.[0]?.product?.user?.restaurant_name || order.items?.[0]?.product?.user?.name || 'N/A' }}
                                            </span>
                                        </div>
                                        <div class="flex items-center text-xs">
                                            <span class="w-4 inline-block text-gray-400">🛵</span>
                                            <span class="font-medium" :class="order.shipper?.user?.name ? 'text-gray-700' : 'text-gray-400 italic'">
                                                {{ order.shipper?.user?.name || 'Chưa gán' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-gray-900">{{ formatCurrency(order.total) }}</span>
                                        <span class="text-[10px] uppercase font-bold tracking-wider mt-0.5" :class="order.payment_method === 'vnpay' ? 'text-blue-600' : 'text-orange-500'">
                                            {{ order.payment_method }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold ring-1 ring-inset" :class="getStatusColor(order.status)">
                                        {{ getStatusLabel(order.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="openDetails(order.id)" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors font-bold">
                                        Chi tiết
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Hiển thị <span class="font-medium">{{ orders.from || 0 }}</span> đến <span class="font-medium">{{ orders.to || 0 }}</span> trong <span class="font-medium">{{ orders.total }}</span> kết quả
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <select @change="changePageSize($event.target.value)" :value="orders.per_page" class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="10">10 / trang</option>
                                <option value="25">25 / trang</option>
                                <option value="50">50 / trang</option>
                            </select>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <button v-if="orders.prev_page_url" @click="goToPage(orders.current_page - 1)" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Previous</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                </button>
                                
                                <template v-for="page in getVisiblePages()" :key="page">
                                    <button v-if="page === '...'" disabled class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</button>
                                    <button v-else @click="goToPage(page)" :class="[page === orders.current_page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50', 'relative inline-flex items-center px-4 py-2 border text-sm font-medium']">
                                        {{ page }}
                                    </button>
                                </template>

                                <button v-if="orders.next_page_url" @click="goToPage(orders.current_page + 1)" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Next</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SLIDE OVER MODAL FOR ORDER DETAILS -->
        <div v-if="showSlideOver" class="fixed inset-0 overflow-hidden z-[100]" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" @click="closeDetails"></div>
                <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                    <div class="w-screen max-w-md transform transition ease-in-out duration-500 sm:duration-700" :class="showSlideOver ? 'translate-x-0' : 'translate-x-full'">
                        <div class="h-full flex flex-col bg-white shadow-2xl overflow-y-scroll custom-scrollbar">
                            
                            <!-- Loading State -->
                            <div v-if="isLoadingDetails" class="flex-1 flex flex-col items-center justify-center p-6">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                                <p class="mt-4 text-gray-500 font-medium">Đang tải thông tin đơn hàng...</p>
                            </div>

                            <!-- Details Content -->
                            <template v-else-if="selectedOrder">
                                <div class="px-6 py-6 bg-gray-50 border-b border-gray-200 sm:px-8 relative">
                                    <button @click="closeDetails" class="absolute top-6 right-6 text-gray-400 hover:text-gray-500 focus:outline-none">
                                        <span class="sr-only">Đóng</span>
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                    <h2 class="text-2xl font-black text-gray-900" id="slide-over-title">Chi tiết đơn hàng</h2>
                                    <div class="mt-2 flex items-center gap-3">
                                        <span class="text-sm font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-md">#{{ selectedOrder.order_code }}</span>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold ring-1 ring-inset" :class="getStatusColor(selectedOrder.status)">
                                            {{ getStatusLabel(selectedOrder.status) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2 font-medium">Đặt lúc: {{ formatDate(selectedOrder.created_at) }}</p>
                                </div>

                                <div class="px-4 sm:px-6 py-6 space-y-8 flex-1">
                                    
                                    <!-- Customer Info -->
                                    <div>
                                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Khách hàng</h3>
                                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex items-start gap-4">
                                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold shrink-0">
                                                {{ selectedOrder.user?.name?.charAt(0) || 'U' }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900">{{ selectedOrder.user?.name || 'Unknown' }}</p>
                                                <p class="text-sm text-gray-500 mt-0.5">SĐT: {{ selectedOrder.phone }}</p>
                                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">📍 {{ selectedOrder.address }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Restaurant Info -->
                                    <div>
                                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Quán ăn</h3>
                                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex items-start gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-xl shrink-0">
                                                🏪
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900">{{ selectedOrder.items?.[0]?.product?.user?.restaurant_name || selectedOrder.items?.[0]?.product?.user?.name || 'Unknown' }}</p>
                                                <p class="text-sm text-gray-500 mt-0.5">SĐT: {{ selectedOrder.items?.[0]?.product?.user?.phone || 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Shipper Info -->
                                    <div>
                                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Tài xế</h3>
                                        <div v-if="selectedOrder.shipper" class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex items-start gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-xl shrink-0">
                                                🛵
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900">{{ selectedOrder.shipper.user?.name }}</p>
                                                <p class="text-sm text-gray-500 mt-0.5">SĐT: {{ selectedOrder.shipper.user?.phone || 'N/A' }}</p>
                                            </div>
                                        </div>
                                        <div v-else class="bg-yellow-50 p-4 rounded-xl border border-yellow-100 text-yellow-700 text-sm font-medium flex items-center gap-2">
                                            <span>⏳</span> Đơn hàng chưa được gán tài xế.
                                        </div>
                                    </div>

                                    <!-- Order Items -->
                                    <div>
                                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Chi tiết món</h3>
                                        <ul class="divide-y divide-gray-100 border border-gray-100 rounded-xl overflow-hidden">
                                            <li v-for="item in selectedOrder.items" :key="item.id" class="p-4 flex justify-between bg-white">
                                                <div class="flex-1 pr-4">
                                                    <p class="font-semibold text-gray-900 text-sm">{{ item.product?.name || 'Món đã xóa' }}</p>
                                                    <p class="text-xs text-gray-500 mt-1">{{ formatCurrency(item.price) }} x {{ item.quantity }}</p>
                                                </div>
                                                <div class="text-right font-black text-gray-900 text-sm self-center">
                                                    {{ formatCurrency(item.price * item.quantity) }}
                                                </div>
                                            </li>
                                            <!-- Financial Summary -->
                                            <li class="p-4 bg-gray-50 space-y-2">
                                                <div class="flex justify-between text-sm">
                                                    <span class="text-gray-500 font-medium">Tạm tính</span>
                                                    <span class="font-semibold text-gray-900">{{ formatCurrency(selectedOrder.total - selectedOrder.shipping_fee) }}</span>
                                                </div>
                                                <div class="flex justify-between text-sm">
                                                    <span class="text-gray-500 font-medium">Phí giao hàng <span class="text-xs">({{ selectedOrder.distance ? selectedOrder.distance + 'km' : '' }})</span></span>
                                                    <span class="font-semibold text-gray-900">{{ formatCurrency(selectedOrder.shipping_fee) }}</span>
                                                </div>
                                                <div v-if="selectedOrder.shipper_tip > 0" class="flex justify-between text-sm">
                                                    <span class="text-gray-500 font-medium">Tiền tip tài xế</span>
                                                    <span class="font-semibold text-gray-900">{{ formatCurrency(selectedOrder.shipper_tip) }}</span>
                                                </div>
                                                <div class="pt-2 mt-2 border-t border-gray-200 flex justify-between">
                                                    <span class="font-black text-gray-900">Tổng cộng</span>
                                                    <span class="font-black text-blue-600 text-lg">{{ formatCurrency(selectedOrder.total) }}</span>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                </div>
                                <!-- End Details Content -->
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
}
</style>
