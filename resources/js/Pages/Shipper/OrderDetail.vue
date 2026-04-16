<template>
    <ShipperLayout>
        <template #title>Chi tiết đơn hàng: {{ order.order_code }}</template>
        <template #subtitle>Quản lý giao hàng</template>

        <div class="flex justify-between items-center mb-6">
            <div></div>
            <Link
                href="/shipper/dashboard"
                class="text-blue-600 hover:text-blue-800"
                >← Quay lại Dashboard</Link
            >
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <h2 class="text-xl font-semibold mb-4">Thông tin liên hệ</h2>
                <div class="grid gap-4 lg:grid-cols-2">
                    <!-- Customer Card -->
                    <div
                        @click="showCustomerDetail = !showCustomerDetail"
                        class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm cursor-pointer hover:shadow-md hover:border-blue-300 transition-all"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p
                                    class="text-sm uppercase tracking-[0.24em] text-slate-500 mb-4"
                                >
                                    👤 Khách hàng
                                </p>
                                <p class="font-semibold text-slate-900">
                                    {{ order.user.name }}
                                </p>
                                <p class="text-sm text-slate-500 mt-2">
                                    {{ order.phone }}
                                </p>
                                <p
                                    class="text-sm text-slate-500 mt-2 line-clamp-2"
                                >
                                    {{ order.address }}
                                </p>
                            </div>
                            <span class="text-2xl">{{
                                showCustomerDetail ? "▼" : "▶"
                            }}</span>
                        </div>

                        <!-- Customer Actions -->
                        <div
                            v-if="showCustomerDetail"
                            class="mt-4 space-y-2 pt-4 border-t border-slate-200"
                        >
                            <a
                                :href="`tel:${order.phone}`"
                                class="flex items-center justify-center gap-2 rounded-3xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100 transition"
                            >
                                <span>📞</span>
                                Gọi khách hàng
                            </a>
                            <button
                                @click.stop="showCustomerMap = true"
                                class="w-full flex items-center justify-center gap-2 rounded-3xl border border-orange-200 bg-orange-50 px-4 py-2 text-sm font-semibold text-orange-700 hover:bg-orange-100 transition"
                            >
                                <span>🗺️</span>
                                Xem bản đồ
                            </button>
                        </div>
                    </div>

                    <!-- Restaurant Card -->
                    <div
                        @click="showRestaurantDetail = !showRestaurantDetail"
                        class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm cursor-pointer hover:shadow-md hover:border-orange-300 transition-all"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p
                                    class="text-sm uppercase tracking-[0.24em] text-slate-500 mb-4"
                                >
                                    🏪 Quán ăn
                                </p>
                                <p class="font-semibold text-slate-900">
                                    {{ restaurant.name }}
                                </p>
                                <p class="text-sm text-slate-500 mt-2">
                                    {{ restaurant.phone }}
                                </p>
                                <p
                                    class="text-sm text-slate-500 mt-2 line-clamp-2"
                                >
                                    {{ restaurant.address }}
                                </p>
                                <p class="text-sm text-slate-500 mt-2">
                                    Trạng thái:
                                    <span
                                        class="font-semibold text-slate-900"
                                        >{{ getStatusText(order.status) }}</span
                                    >
                                </p>
                            </div>
                            <span class="text-2xl">{{
                                showRestaurantDetail ? "▼" : "▶"
                            }}</span>
                        </div>

                        <!-- Restaurant Actions -->
                        <div
                            v-if="showRestaurantDetail"
                            class="mt-4 space-y-2 pt-4 border-t border-slate-200"
                        >
                            <a
                                :href="`tel:${restaurant.phone}`"
                                class="flex items-center justify-center gap-2 rounded-3xl border border-orange-200 bg-orange-50 px-4 py-2 text-sm font-semibold text-orange-700 hover:bg-orange-100 transition"
                            >
                                <span>📞</span>
                                Gọi quán ăn
                            </a>
                            <button
                                @click.stop="showRestaurantMap = true"
                                class="w-full flex items-center justify-center gap-2 rounded-3xl border border-orange-200 bg-orange-50 px-4 py-2 text-sm font-semibold text-orange-700 hover:bg-orange-100 transition"
                            >
                                <span>🗺️</span>
                                Chỉ đường đến quán
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <h2 class="text-xl font-semibold mb-6">📋 Chi tiết đơn hàng</h2>

                <!-- Items Container -->
                <div class="space-y-4 mb-6">
                    <div
                        v-for="item in order.items"
                        :key="item.id"
                        class="rounded-2xl border-2 border-slate-200 bg-white hover:border-blue-400 hover:shadow-md transition-all p-5"
                    >
                        <div class="flex justify-between items-start gap-4">
                            <!-- Left: Item Info -->
                            <div class="flex-1 min-w-0">
                                <h3
                                    class="text-lg font-bold text-slate-900 mb-2"
                                >
                                    {{ item.product.name }}
                                </h3>
                                <div class="flex gap-6 text-sm">
                                    <div>
                                        <p
                                            class="text-slate-500 text-xs uppercase tracking-wide"
                                        >
                                            Đơn giá
                                        </p>
                                        <p
                                            class="text-base font-semibold text-blue-600"
                                        >
                                            {{ formatCurrency(item.price) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-slate-500 text-xs uppercase tracking-wide"
                                        >
                                            Số lượng
                                        </p>
                                        <p
                                            class="text-2xl font-bold text-slate-900"
                                        >
                                            {{ item.quantity }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Total -->
                            <div class="text-right">
                                <p
                                    class="text-slate-500 text-xs uppercase tracking-wide mb-1"
                                >
                                    Thành tiền
                                </p>
                                <p class="text-2xl font-black text-blue-600">
                                    {{
                                        formatCurrency(
                                            item.price * item.quantity,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Card -->
                <div
                    class="rounded-2xl bg-gradient-to-br from-blue-50 to-slate-50 border-2 border-blue-200 p-6"
                >
                    <div class="space-y-3">
                        <!-- Subtotal -->
                        <div
                            class="flex justify-between items-center pb-3 border-b-2 border-blue-100"
                        >
                            <span class="text-slate-700 font-medium"
                                >Giá trị đơn hàng</span
                            >
                            <span class="text-xl font-bold text-slate-900">
                                {{ formatCurrency(order.subtotal) }}
                            </span>
                        </div>

                        <!-- Discount -->
                        <div
                            v-if="order.discount_amount"
                            class="flex justify-between items-center pb-3 border-b-2 border-blue-100"
                        >
                            <span class="text-slate-700 font-medium"
                                >Khấu trừ</span
                            >
                            <span
                                class="text-lg font-semibold text-emerald-600"
                            >
                                -{{ formatCurrency(order.discount_amount) }}
                            </span>
                        </div>

                        <!-- Shipping -->
                        <div
                            class="flex justify-between items-center pb-3 border-b-2 border-blue-100"
                        >
                            <span class="text-slate-700 font-medium"
                                >Phí giao hàng</span
                            >
                            <span class="text-lg font-semibold text-slate-900">
                                {{ formatCurrency(order.shipping_fee) }}
                            </span>
                        </div>

                        <!-- Total -->
                        <div class="flex justify-between items-center pt-2">
                            <span class="text-xl font-bold text-slate-900"
                                >Tổng cộng</span
                            >
                            <span class="text-3xl font-black text-blue-600">
                                {{ formatCurrency(order.total) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons - Swipe to Confirm -->
        <div class="mt-8">
            <h2 class="text-lg font-semibold mb-4">🎯 Hành động</h2>
            <div class="space-y-3">
                <!-- Confirm Pickup: shipping → picked_up -->
                <div
                    v-if="order.status === 'shipping'"
                    @touchstart="startSwipe($event, 'confirmPickup')"
                    @touchmove="handleSwipe($event)"
                    @touchend="endSwipe('confirmPickup')"
                    @mousemove="isMobile() ? null : handleSwipe($event)"
                    @mousedown="
                        !isMobile() && startSwipe($event, 'confirmPickup')
                    "
                    @mouseup="!isMobile() && endSwipe('confirmPickup')"
                    class="relative h-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl overflow-hidden cursor-grab active:cursor-grabbing select-none group shadow-lg hover:shadow-xl transition-shadow"
                >
                    <div
                        class="absolute inset-0 bg-white/15 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"
                    >
                        <span class="text-sm font-semibold text-white"
                            >← Vuốt sang phải để xác nhận</span
                        >
                    </div>
                    <div
                        :style="{ transform: `translateX(${swipeDistance}px)` }"
                        class="absolute inset-y-0 left-0 bg-gradient-to-r from-blue-600 to-blue-700 flex items-center justify-center px-6 py-4 transition-transform duration-75 will-change-transform"
                        style="width: 100%; min-width: 200px"
                    >
                        <div class="text-center w-full text-white">
                            <div
                                class="text-2xl mb-1 transition-all duration-300"
                            >
                                {{
                                    isLoading && swipeAction === "confirmPickup"
                                        ? "⏳"
                                        : "📦"
                                }}
                            </div>
                            <div class="font-semibold text-sm">
                                {{
                                    isLoading && swipeAction === "confirmPickup"
                                        ? "Đang xử lý..."
                                        : "Vuốt để xác nhận"
                                }}
                            </div>
                        </div>
                    </div>
                    <div
                        class="absolute inset-0 flex items-center justify-center pointer-events-none"
                    >
                        <div class="text-white/40 text-center">
                            <div class="text-2xl mb-1">📦</div>
                        </div>
                    </div>
                </div>

                <!-- Start Delivery: picked_up → delivering -->
                <div
                    v-if="order.status === 'picked_up'"
                    @touchstart="startSwipe($event, 'startDelivery')"
                    @touchmove="handleSwipe($event)"
                    @touchend="endSwipe('startDelivery')"
                    @mousemove="isMobile() ? null : handleSwipe($event)"
                    @mousedown="
                        !isMobile() && startSwipe($event, 'startDelivery')
                    "
                    @mouseup="!isMobile() && endSwipe('startDelivery')"
                    class="relative h-16 bg-gradient-to-r from-green-500 to-green-600 rounded-xl overflow-hidden cursor-grab active:cursor-grabbing select-none group shadow-lg hover:shadow-xl transition-shadow"
                >
                    <div
                        class="absolute inset-0 bg-white/15 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"
                    >
                        <span class="text-sm font-semibold text-white"
                            >← Vuốt sang phải để xác nhận</span
                        >
                    </div>
                    <div
                        :style="{ transform: `translateX(${swipeDistance}px)` }"
                        class="absolute inset-y-0 left-0 bg-gradient-to-r from-green-600 to-green-700 flex items-center justify-center px-6 py-4 transition-transform duration-75 will-change-transform"
                        style="width: 100%; min-width: 200px"
                    >
                        <div class="text-center w-full text-white">
                            <div
                                class="text-2xl mb-1 transition-all duration-300"
                            >
                                {{
                                    isLoading && swipeAction === "startDelivery"
                                        ? "⏳"
                                        : "🚚"
                                }}
                            </div>
                            <div class="font-semibold text-sm">
                                {{
                                    isLoading && swipeAction === "startDelivery"
                                        ? "Đang xử lý..."
                                        : "Vuốt để bắt đầu"
                                }}
                            </div>
                        </div>
                    </div>
                    <div
                        class="absolute inset-0 flex items-center justify-center pointer-events-none"
                    >
                        <div class="text-white/40 text-center">
                            <div class="text-2xl mb-1">🚚</div>
                        </div>
                    </div>
                </div>

                <!-- Complete Order: delivering → completed -->
                <div
                    v-if="order.status === 'delivering'"
                    @touchstart="startSwipe($event, 'completeOrder')"
                    @touchmove="handleSwipe($event)"
                    @touchend="endSwipe('completeOrder')"
                    @mousemove="isMobile() ? null : handleSwipe($event)"
                    @mousedown="
                        !isMobile() && startSwipe($event, 'completeOrder')
                    "
                    @mouseup="!isMobile() && endSwipe('completeOrder')"
                    class="relative h-16 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl overflow-hidden cursor-grab active:cursor-grabbing select-none group shadow-lg hover:shadow-xl transition-shadow"
                >
                    <div
                        class="absolute inset-0 bg-white/15 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"
                    >
                        <span class="text-sm font-semibold text-white"
                            >← Vuốt sang phải để xác nhận</span
                        >
                    </div>
                    <div
                        :style="{ transform: `translateX(${swipeDistance}px)` }"
                        class="absolute inset-y-0 left-0 bg-gradient-to-r from-emerald-600 to-emerald-700 flex items-center justify-center px-6 py-4 transition-transform duration-75 will-change-transform"
                        style="width: 100%; min-width: 200px"
                    >
                        <div class="text-center w-full text-white">
                            <div
                                class="text-2xl mb-1 transition-all duration-300"
                            >
                                {{
                                    isLoading && swipeAction === "completeOrder"
                                        ? "⏳"
                                        : "✅"
                                }}
                            </div>
                            <div class="font-semibold text-sm">
                                {{
                                    isLoading && swipeAction === "completeOrder"
                                        ? "Đang xử lý..."
                                        : "Vuốt để hoàn thành"
                                }}
                            </div>
                        </div>
                    </div>
                    <div
                        class="absolute inset-0 flex items-center justify-center pointer-events-none"
                    >
                        <div class="text-white/40 text-center">
                            <div class="text-2xl mb-1">✅</div>
                        </div>
                    </div>
                </div>

                <!-- Completed State -->
                <div
                    v-if="order.status === 'completed'"
                    class="h-16 rounded-xl bg-gradient-to-r from-slate-100 to-slate-200 flex items-center justify-center shadow-md"
                >
                    <div class="text-center">
                        <div class="text-3xl animate-bounce">🎉</div>
                        <div class="font-semibold text-slate-700 text-sm mt-1">
                            Đơn hàng đã hoàn thành
                        </div>
                    </div>
                </div>

                <!-- Waiting State -->
                <div
                    v-if="
                        ['pending', 'assigned', 'confirmed'].includes(
                            order.status,
                        )
                    "
                    class="h-16 rounded-xl bg-gradient-to-r from-slate-100 to-slate-200 flex items-center justify-center shadow-md"
                >
                    <div class="text-center">
                        <div class="text-2xl">⏳</div>
                        <div class="font-semibold text-slate-700 text-sm mt-1">
                            {{
                                order.status === "pending"
                                    ? "Chờ quán xác nhận"
                                    : "Chờ xác nhận"
                            }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Timeline - Compact Horizontal -->
        <div class="mt-8 mb-8">
            <div class="flex items-center justify-between gap-2">
                <div class="flex-1 flex items-center">
                    <div
                        :class="[
                            'w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold transition-all',
                            order.status !== 'pending'
                                ? 'bg-gradient-to-br from-green-400 to-green-600 text-white shadow-lg'
                                : 'bg-gray-200 text-gray-600',
                        ]"
                    >
                        📋
                    </div>
                    <div
                        class="h-1 flex-1 mx-1 transition-all"
                        :class="
                            order.status !== 'pending'
                                ? 'bg-gradient-to-r from-green-500 to-gray-300'
                                : 'bg-gray-300'
                        "
                    ></div>
                </div>
                <div class="flex-1 flex items-center">
                    <div
                        :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all',
                            [
                                'confirmed',
                                'picked_up',
                                'shipping',
                                'delivered',
                                'completing',
                                'completed',
                            ].includes(order.status)
                                ? 'bg-gradient-to-br from-green-400 to-green-600 text-white shadow-lg'
                                : 'bg-gray-200 text-gray-600',
                        ]"
                    >
                        🏪
                    </div>
                    <div
                        class="h-1 flex-1 mx-1 transition-all"
                        :class="
                            [
                                'picked_up',
                                'shipping',
                                'delivered',
                                'completing',
                                'completed',
                            ].includes(order.status)
                                ? 'bg-gradient-to-r from-green-500 to-gray-300'
                                : 'bg-gray-300'
                        "
                    ></div>
                </div>
                <div class="flex-1 flex items-center">
                    <div
                        :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all',
                            [
                                'picked_up',
                                'shipping',
                                'delivered',
                                'completing',
                                'completed',
                            ].includes(order.status)
                                ? 'bg-gradient-to-br from-green-400 to-green-600 text-white shadow-lg'
                                : 'bg-gray-200 text-gray-600',
                        ]"
                    >
                        📦
                    </div>
                    <div
                        class="h-1 flex-1 mx-1 transition-all"
                        :class="
                            [
                                'shipping',
                                'delivered',
                                'completing',
                                'completed',
                            ].includes(order.status)
                                ? 'bg-gradient-to-r from-green-500 to-gray-300'
                                : 'bg-gray-300'
                        "
                    ></div>
                </div>
                <div class="flex-1 flex items-center">
                    <div
                        :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all',
                            [
                                'shipping',
                                'delivered',
                                'completing',
                                'completed',
                            ].includes(order.status)
                                ? 'bg-gradient-to-br from-green-400 to-green-600 text-white shadow-lg'
                                : 'bg-gray-200 text-gray-600',
                        ]"
                    >
                        🚚
                    </div>
                    <div
                        class="h-1 flex-1 mx-1 transition-all"
                        :class="
                            order.status === 'completed'
                                ? 'bg-green-500'
                                : 'bg-gray-300'
                        "
                    ></div>
                </div>
                <div
                    :class="[
                        'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all',
                        order.status === 'completed'
                            ? 'bg-gradient-to-br from-green-400 to-green-600 text-white shadow-lg'
                            : 'bg-gray-200 text-gray-600',
                    ]"
                >
                    ✅
                </div>
            </div>
            <p class="text-center text-xs text-gray-500 mt-3 font-medium">
                {{ getStatusText(order.status) }}
            </p>
        </div>

        <!-- Customer Map Modal -->
        <div
            v-if="showCustomerMap"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        >
            <div
                class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-96 flex flex-col"
            >
                <div class="flex justify-between items-center p-4 border-b">
                    <h2 class="text-xl font-bold">🗺️ Vị trí khách hàng</h2>
                    <button
                        @click="
                            showCustomerMap = false;
                            customerMapInstance = null;
                        "
                        class="text-gray-500 hover:text-gray-700 text-2xl"
                    >
                        ✕
                    </button>
                </div>
                <div id="customer-map" class="flex-1 bg-gray-200"></div>
                <div class="p-4 border-t space-y-2">
                    <p class="text-sm text-gray-600">
                        📍 {{ order.user.name }} - {{ order.address }}
                    </p>
                    <div class="flex gap-2">
                        <a
                            :href="`tel:${order.phone}`"
                            class="flex-1 flex items-center justify-center gap-2 rounded-lg border border-blue-300 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100"
                        >
                            📞 Gọi
                        </a>
                        <button
                            @click="openCustomerInMaps"
                            class="flex-1 flex items-center justify-center gap-2 rounded-lg border border-green-300 bg-green-50 px-4 py-2 text-sm font-semibold text-green-700 hover:bg-green-100"
                        >
                            🗺️ Google Maps
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Restaurant Map Modal -->
        <div
            v-if="showRestaurantMap"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        >
            <div
                class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-96 flex flex-col"
            >
                <div class="flex justify-between items-center p-4 border-b">
                    <h2 class="text-xl font-bold">🗺️ Vị trí quán ăn</h2>
                    <button
                        @click="
                            showRestaurantMap = false;
                            restaurantMapInstance = null;
                        "
                        class="text-gray-500 hover:text-gray-700 text-2xl"
                    >
                        ✕
                    </button>
                </div>
                <div id="restaurant-map" class="flex-1 bg-gray-200"></div>
                <div class="p-4 border-t space-y-2">
                    <p class="text-sm text-gray-600">
                        🏪 {{ restaurant.name }} - {{ restaurant.address }}
                    </p>
                    <p class="text-sm text-gray-500">
                        📞 {{ restaurant.phone }}
                    </p>
                    <div class="flex gap-2">
                        <a
                            :href="`tel:${restaurant.phone}`"
                            class="flex-1 flex items-center justify-center gap-2 rounded-lg border border-orange-300 bg-orange-50 px-4 py-2 text-sm font-semibold text-orange-700 hover:bg-orange-100"
                        >
                            📞 Gọi
                        </a>
                        <button
                            @click="openRestaurantInMaps"
                            class="flex-1 flex items-center justify-center gap-2 rounded-lg border border-green-300 bg-green-50 px-4 py-2 text-sm font-semibold text-green-700 hover:bg-green-100"
                        >
                            🗺️ Google Maps
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex gap-4">
            <Link
                href="/shipper/dashboard"
                class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600"
            >
                Quay lại Dashboard
            </Link>
        </div>
    </ShipperLayout>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import ShipperLayout from "@/Layouts/ShipperLayout.vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

const toast = useToast();

// Mobile-optimized toast helper
const showToast = (type, message, options = {}) => {
    const isMobile =
        /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
            navigator.userAgent,
        );

    const defaultOptions = {
        position: isMobile ? "bottom-center" : "top-right",
        timeout: isMobile ? 4000 : 3000, // Longer on mobile
        closeOnClick: true,
        pauseOnFocusLoss: true,
        pauseOnHover: true,
        draggable: true,
        draggablePercent: 0.6,
        showCloseButtonOnHover: false,
        hideProgressBar: false,
        closeButton: "button",
        icon: true,
        rtl: false,
        // Mobile-specific styling
        toastClassName: isMobile ? "mobile-toast" : "",
        bodyClassName: isMobile ? "mobile-toast-body" : "",
        progressClassName: isMobile ? "mobile-toast-progress" : "",
    };

    const finalOptions = { ...defaultOptions, ...options };

    // Add vibration feedback for mobile
    if (isMobile && "vibrate" in navigator) {
        if (type === "success") {
            navigator.vibrate(200); // Short vibration for success
        } else if (type === "error") {
            navigator.vibrate([200, 100, 200]); // Double vibration for error
        }
    }

    return toast[type](message, finalOptions);
};
const csrfFetch = window.csrfFetch;

const props = defineProps({
    order: Object,
});

const order = ref(props.order);
const showRouting = ref(false);
const routingMap = ref(null);
const showCustomerDetail = ref(false);
const showRestaurantDetail = ref(false);
const showCustomerMap = ref(false);
const showRestaurantMap = ref(false);
const customerMapInstance = ref(null);
const restaurantMapInstance = ref(null);

// Action state
const isLoading = ref(false);

// Swipe to confirm state
const swipeDistance = ref(0);
const swipeStartX = ref(0);
const swipeAction = ref(null);
const swipeThreshold = 100; // Pixels to drag to trigger action

const restaurant = computed(() => {
    const restaurantUser = order.value?.items?.[0]?.product?.user;
    return {
        name:
            restaurantUser?.restaurant_name ||
            restaurantUser?.name ||
            "Quán ăn",
        address: restaurantUser?.address || "Chưa có địa chỉ",
        phone: restaurantUser?.phone || "Không có SĐT",
        latitude: restaurantUser?.latitude || "--",
        longitude: restaurantUser?.longitude || "--",
    };
});

const restaurantName = computed(() => restaurant.value.name);

watch(showRouting, (newVal) => {
    if (newVal) {
        setTimeout(() => {
            initRoutingMap();
        }, 100);
    }
});

// Watch for customer map modal
watch(showCustomerMap, (newVal) => {
    if (newVal) {
        setTimeout(() => {
            initCustomerMap();
        }, 100);
    } else {
        if (customerMapInstance.value) {
            customerMapInstance.value.remove();
            customerMapInstance.value = null;
        }
    }
});

// Watch for restaurant map modal
watch(showRestaurantMap, (newVal) => {
    if (newVal) {
        setTimeout(() => {
            initRestaurantMap();
        }, 100);
    } else {
        if (restaurantMapInstance.value) {
            restaurantMapInstance.value.remove();
            restaurantMapInstance.value = null;
        }
    }
});

const initRoutingMap = () => {
    const mapElement = document.getElementById("routing-map");
    if (!mapElement || routingMap.value) return;

    routingMap.value = L.map(mapElement, {
        zoomControl: true,
        attributionControl: false,
    }).setView([16.0544, 108.2022], 12);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 18,
        minZoom: 10,
        attribution: "&copy; OpenStreetMap contributors",
    }).addTo(routingMap.value);

    // Add markers
    if (order.value.status === "shipping") {
        // Route to restaurant
        const restaurantLat = order.value.items?.[0]?.product?.user?.latitude;
        const restaurantLng = order.value.items?.[0]?.product?.user?.longitude;

        if (restaurantLat && restaurantLng) {
            L.marker([restaurantLat, restaurantLng])
                .bindPopup(`📍 ${restaurantName.value}`)
                .addTo(routingMap.value);

            routingMap.value.setView([restaurantLat, restaurantLng], 14);
        }
    } else {
        // Route to customer
        const customerLat = order.value.user?.latitude;
        const customerLng = order.value.user?.longitude;

        if (customerLat && customerLng) {
            L.marker([customerLat, customerLng])
                .bindPopup(`📍 ${order.value.user.name}`)
                .addTo(routingMap.value);

            routingMap.value.setView([customerLat, customerLng], 14);
        }
    }
};

const openInMaps = () => {
    if (order.value.status === "shipping") {
        // Open restaurant location in Google Maps
        const restaurantLat = order.value.items?.[0]?.product?.user?.latitude;
        const restaurantLng = order.value.items?.[0]?.product?.user?.longitude;

        if (restaurantLat && restaurantLng) {
            window.open(
                `https://www.google.com/maps/dir/?api=1&destination=${restaurantLat},${restaurantLng}`,
                "_blank",
            );
        }
    } else {
        // Open customer location in Google Maps
        const customerLat = order.value.user?.latitude;
        const customerLng = order.value.user?.longitude;

        if (customerLat && customerLng) {
            window.open(
                `https://www.google.com/maps/dir/?api=1&destination=${customerLat},${customerLng}`,
                "_blank",
            );
        }
    }
};

const initCustomerMap = () => {
    const mapElement = document.getElementById("customer-map");
    if (!mapElement) return;

    const customerLat = order.value.user?.latitude;
    const customerLng = order.value.user?.longitude;

    if (!customerLat || !customerLng) {
        showToast("error", "Vị trí khách hàng không có sẵn");
        return;
    }

    if (customerMapInstance.value) {
        customerMapInstance.value.remove();
    }

    customerMapInstance.value = L.map(mapElement, {
        zoomControl: true,
        attributionControl: false,
    }).setView([customerLat, customerLng], 15);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 18,
        minZoom: 10,
        attribution: "&copy; OpenStreetMap contributors",
    }).addTo(customerMapInstance.value);

    L.marker([customerLat, customerLng], {
        icon: L.icon({
            iconUrl:
                "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png",
            shadowUrl:
                "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png",
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41],
        }),
    })
        .bindPopup(
            `<b>👤 ${order.value.user.name}</b><br/>${order.value.address}`,
        )
        .openPopup()
        .addTo(customerMapInstance.value);
};

const initRestaurantMap = () => {
    const mapElement = document.getElementById("restaurant-map");
    if (!mapElement) return;

    const restaurantLat = order.value.items?.[0]?.product?.user?.latitude;
    const restaurantLng = order.value.items?.[0]?.product?.user?.longitude;

    if (!restaurantLat || !restaurantLng) {
        showToast("error", "Vị trí quán ăn không có sẵn");
        return;
    }

    if (restaurantMapInstance.value) {
        restaurantMapInstance.value.remove();
    }

    restaurantMapInstance.value = L.map(mapElement, {
        zoomControl: true,
        attributionControl: false,
    }).setView([restaurantLat, restaurantLng], 15);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 18,
        minZoom: 10,
        attribution: "&copy; OpenStreetMap contributors",
    }).addTo(restaurantMapInstance.value);

    L.marker([restaurantLat, restaurantLng], {
        icon: L.icon({
            iconUrl:
                "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/images/marker-icon-2x.png",
            shadowUrl:
                "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png",
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41],
        }),
    })
        .bindPopup(
            `<b>🏪 ${restaurant.value.name}</b><br/>${restaurant.value.address}`,
        )
        .openPopup()
        .addTo(restaurantMapInstance.value);
};

const openCustomerInMaps = () => {
    const customerLat = order.value.user?.latitude;
    const customerLng = order.value.user?.longitude;

    if (customerLat && customerLng) {
        window.open(
            `https://www.google.com/maps/dir/?api=1&destination=${customerLat},${customerLng}`,
            "_blank",
        );
    }
};

const openRestaurantInMaps = () => {
    const restaurantLat = order.value.items?.[0]?.product?.user?.latitude;
    const restaurantLng = order.value.items?.[0]?.product?.user?.longitude;

    if (restaurantLat && restaurantLng) {
        window.open(
            `https://www.google.com/maps/dir/?api=1&destination=${restaurantLat},${restaurantLng}`,
            "_blank",
        );
    }
};

const confirmPickup = async () => {
    try {
        isLoading.value = true;
        const response = await csrfFetch(
            `/api/shipper/orders/${order.value.id}/confirm-pickup`,
            {
                method: "POST",
                headers: {
                    Accept: "application/json",
                },
            },
        );
        if (response.ok) {
            order.value.status = "picked_up";
            showToast("success", "✅ Đã xác nhận lấy hàng");
        } else {
            const error = await response.json();
            throw new Error(error?.error || "Không thể xác nhận lấy hàng.");
        }
    } catch (error) {
        console.error("Error confirming pickup:", error);
        showToast("error", error.message || "❌ Có lỗi khi xác nhận lấy hàng");
    } finally {
        isLoading.value = false;
    }
};

const completeOrder = async () => {
    try {
        isLoading.value = true;
        const response = await csrfFetch(
            `/api/shipper/orders/${order.value.id}/complete`,
            {
                method: "POST",
                headers: {
                    Accept: "application/json",
                },
            },
        );
        if (response.ok) {
            showToast("success", "✅ Giao hàng hoàn thành");
            router.visit("/shipper/dashboard");
        } else {
            const error = await response.json();
            throw new Error(error?.error || "Không thể hoàn thành giao hàng.");
        }
    } catch (error) {
        console.error("Error completing order:", error);
        showToast("error", error.message || "❌ Có lỗi khi hoàn thành đơn");
    } finally {
        isLoading.value = false;
    }
};

const startDelivery = async () => {
    try {
        isLoading.value = true;
        const response = await csrfFetch(
            `/api/shipper/orders/${order.value.id}/start-delivery`,
            {
                method: "POST",
                headers: {
                    Accept: "application/json",
                },
            },
        );

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error?.error || "Không thể bắt đầu giao hàng.");
        }

        order.value.status = "shipping";
        showToast("success", "🚚 Bắt đầu giao hàng");

        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(
                updateLocation,
                handleLocationError,
                {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0,
                },
            );
        }
    } catch (error) {
        console.error("Error starting delivery:", error);
        showToast("error", error.message || "❌ Có lỗi khi bắt đầu giao hàng");
    } finally {
        isLoading.value = false;
    }
};

const updateLocation = async (position) => {
    try {
        await csrfFetch("/api/shipper/location", {
            method: "POST",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
            }),
        });
    } catch (error) {
        console.error("Error updating location:", error);
    }
};

const handleLocationError = (error) => {
    console.error("Location error:", error);
};

// Swipe to Confirm Gesture Handlers
const startSwipe = (event, action) => {
    if (isLoading.value) return;
    swipeStartX.value = event.touches
        ? event.touches[0].clientX
        : event.clientX;
    swipeAction.value = action;
    swipeDistance.value = 0;
};

const handleSwipe = (event) => {
    if (!swipeStartX.value || isLoading.value) return;
    const currentX = event.touches ? event.touches[0].clientX : event.clientX;
    const distance = currentX - swipeStartX.value;

    // Only allow positive swipe (right direction)
    if (distance > 0) {
        swipeDistance.value = Math.min(distance, 150); // Max drag 150px
    }
};

const endSwipe = async (action) => {
    if (swipeDistance.value > swipeThreshold) {
        // Swipe threshold reached, execute action
        swipeDistance.value = 0;
        swipeStartX.value = 0;

        if (action === "confirmPickup") {
            await confirmPickup();
        } else if (action === "startDelivery") {
            await startDelivery();
        } else if (action === "completeOrder") {
            await completeOrder();
        }
    } else {
        // Swipe threshold not reached, animate back
        swipeDistance.value = 0;
        swipeStartX.value = 0;
    }
    swipeAction.value = null;
};

const isMobile = () => {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
        navigator.userAgent,
    );
};

const getStatusText = (status) => {
    const statuses = {
        pending: "Chờ xác nhận",
        assigned: "Đã gán shipper",
        confirmed: "Đã xác nhận",
        shipping: "Đi tới quán",
        picked_up: "Đã lấy hàng",
        delivering: "Đang giao tới khách",
        completed: "Hoàn thành",
        cancelled: "Đã hủy",
    };
    return statuses[status] || status;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(amount);
};
</script>

<style scoped>
/* Mobile-optimized toast styles */
@media (max-width: 768px) {
    .mobile-toast {
        margin: 0 8px !important;
        border-radius: 12px !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15) !important;
        backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
    }

    .mobile-toast-body {
        font-size: 16px !important;
        line-height: 1.5 !important;
        padding: 16px !important;
        font-weight: 500 !important;
    }

    .mobile-toast-progress {
        height: 3px !important;
        border-radius: 0 0 12px 12px !important;
    }

    /* Toast container positioning for mobile */
    .Vue-Toastification__container--bottom-center {
        bottom: 20px !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        width: calc(100vw - 32px) !important;
        max-width: 400px !important;
    }

    /* Success toast styling */
    .Vue-Toastification__toast--success {
        background: linear-gradient(135deg, #10b981, #059669) !important;
        color: white !important;
    }

    /* Error toast styling */
    .Vue-Toastification__toast--error {
        background: linear-gradient(135deg, #ef4444, #dc2626) !important;
        color: white !important;
    }

    /* Warning toast styling */
    .Vue-Toastification__toast--warning {
        background: linear-gradient(135deg, #f59e0b, #d97706) !important;
        color: white !important;
    }

    /* Info toast styling */
    .Vue-Toastification__toast--info {
        background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
        color: white !important;
    }
}

/* Desktop toast styles for consistency */
@media (min-width: 769px) {
    .Vue-Toastification__toast {
        border-radius: 8px !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1) !important;
    }
}
</style>
