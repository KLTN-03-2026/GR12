<template>
    <ShipperLayout>
        <template #title>Đơn hàng: {{ order.order_code }}</template>

        <div class="pb-32 space-y-6">
            <!-- Order Timeline - Compact Horizontal -->
            <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-100">
                <div class="flex items-center justify-between gap-2">
                    <!-- Step 1: Nhận đơn / Đang đến quán -->
                    <div class="flex-1 flex items-center">
                        <div
                            :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all shrink-0',
                                ['confirmed', 'assigned', 'shipping', 'arrived', 'picked_up', 'completed'].includes(order.status)
                                    ? 'bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-lg shadow-emerald-500/30'
                                    : 'bg-slate-100 text-slate-400',
                            ]"
                        >
                            🛵
                        </div>
                        <div
                            class="h-1 flex-1 mx-1 transition-all rounded-full"
                            :class="
                                ['arrived', 'picked_up', 'completed'].includes(order.status)
                                    ? 'bg-gradient-to-r from-emerald-500 to-slate-200'
                                    : 'bg-slate-200'
                            "
                        ></div>
                    </div>

                    <!-- Step 2: Đến quán -->
                    <div class="flex-1 flex items-center">
                        <div
                            :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all shrink-0',
                                ['arrived', 'picked_up', 'completed'].includes(order.status)
                                    ? 'bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-lg shadow-emerald-500/30'
                                    : 'bg-slate-100 text-slate-400',
                            ]"
                        >
                            🏪
                        </div>
                        <div
                            class="h-1 flex-1 mx-1 transition-all rounded-full"
                            :class="
                                ['picked_up', 'completed'].includes(order.status)
                                    ? 'bg-gradient-to-r from-emerald-500 to-slate-200'
                                    : 'bg-slate-200'
                            "
                        ></div>
                    </div>

                    <!-- Step 3: Lấy hàng / Đang giao -->
                    <div class="flex-1 flex items-center">
                        <div
                            :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all shrink-0',
                                ['picked_up', 'completed'].includes(order.status)
                                    ? 'bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-lg shadow-emerald-500/30'
                                    : 'bg-slate-100 text-slate-400',
                            ]"
                        >
                            📦
                        </div>
                        <div
                            class="h-1 flex-1 mx-1 transition-all rounded-full"
                            :class="
                                ['completed'].includes(order.status)
                                    ? 'bg-gradient-to-r from-emerald-500 to-slate-200'
                                    : 'bg-slate-200'
                            "
                        ></div>
                    </div>

                    <!-- Step 4: Hoàn thành -->
                    <div
                        :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all shrink-0',
                            ['completed'].includes(order.status)
                                ? 'bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-lg shadow-emerald-500/30'
                                : 'bg-slate-100 text-slate-400',
                        ]"
                    >
                        ✅
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-xs font-bold text-slate-600">
                        Trạng thái: <span class="text-indigo-600">{{ getStatusText(order.status) }}</span>
                    </span>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="space-y-4">
                <!-- Restaurant Card -->
                <div
                    @click="showRestaurantDetail = !showRestaurantDetail"
                    class="rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm cursor-pointer transition-all"
                    :class="showRestaurantDetail ? 'ring-2 ring-orange-400/50' : 'hover:shadow-md'"
                >
                    <div class="flex justify-between items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center shrink-0 text-xl">
                            🏪
                        </div>
                        <div class="flex-1">
                            <p class="text-[10px] uppercase font-bold tracking-widest text-slate-400 mb-1">
                                Lấy hàng tại
                            </p>
                            <p class="font-black text-slate-900 text-base">
                                {{ restaurant.name }}
                            </p>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-2">
                                {{ restaurant.address }}
                            </p>
                            <p v-if="order.is_food_ready" class="mt-2 text-[10px] font-bold text-orange-600 bg-orange-100 px-2 py-1 rounded-full inline-block">
                                🍲 Món đã nấu xong
                            </p>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center shrink-0 text-slate-400">
                            <span class="transform transition-transform text-xs" :class="showRestaurantDetail ? 'rotate-180' : ''">▼</span>
                        </div>
                    </div>

                    <!-- Restaurant Actions -->
                    <div v-show="showRestaurantDetail" class="mt-5 space-y-3 pt-5 border-t border-slate-100">
                        <div class="flex items-center gap-2 text-sm text-slate-600 font-medium bg-slate-50 p-3 rounded-2xl">
                            <span class="text-xl">📞</span> {{ restaurant.phone }}
                        </div>
                        <div class="flex gap-2">
                            <a :href="`tel:${restaurant.phone}`" class="flex-1 flex items-center justify-center gap-2 rounded-2xl bg-indigo-50 px-4 py-3.5 text-sm font-bold text-indigo-600 active:scale-95 transition-all">
                                Gọi quán
                            </a>
                            <button @click.stop="showRestaurantMap = true" class="flex-1 flex items-center justify-center gap-2 rounded-2xl bg-orange-50 px-4 py-3.5 text-sm font-bold text-orange-600 active:scale-95 transition-all">
                                Xem bản đồ
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Customer Card -->
                <div
                    @click="showCustomerDetail = !showCustomerDetail"
                    class="rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm cursor-pointer transition-all"
                    :class="showCustomerDetail ? 'ring-2 ring-blue-400/50' : 'hover:shadow-md'"
                >
                    <div class="flex justify-between items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center shrink-0 text-xl">
                            👤
                        </div>
                        <div class="flex-1">
                            <p class="text-[10px] uppercase font-bold tracking-widest text-slate-400 mb-1">
                                Giao hàng đến
                            </p>
                            <p class="font-black text-slate-900 text-base">
                                {{ order.user.name }}
                            </p>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-2">
                                {{ order.address }}
                            </p>
                            <p v-if="order.note" class="text-xs font-semibold text-orange-600 mt-2 bg-orange-50 px-2.5 py-1.5 rounded-lg flex gap-1.5 items-start border border-orange-100/50">
                                <span>📝</span> <span class="line-clamp-2">{{ order.note }}</span>
                            </p>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center shrink-0 text-slate-400">
                            <span class="transform transition-transform text-xs" :class="showCustomerDetail ? 'rotate-180' : ''">▼</span>
                        </div>
                    </div>

                    <!-- Customer Actions -->
                    <div v-show="showCustomerDetail" class="mt-5 space-y-3 pt-5 border-t border-slate-100">
                        <div class="flex items-center gap-2 text-sm text-slate-600 font-medium bg-slate-50 p-3 rounded-2xl">
                            <span class="text-xl">📞</span> {{ order.phone }}
                        </div>
                        <div class="flex gap-2">
                            <a :href="`tel:${order.phone}`" class="flex-1 flex items-center justify-center gap-2 rounded-2xl bg-indigo-50 px-4 py-3.5 text-sm font-bold text-indigo-600 active:scale-95 transition-all">
                                Gọi khách
                            </a>
                            <button @click.stop="showCustomerMap = true" class="flex-1 flex items-center justify-center gap-2 rounded-2xl bg-blue-50 px-4 py-3.5 text-sm font-bold text-blue-600 active:scale-95 transition-all">
                                Xem bản đồ
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items & Summary -->
            <div class="bg-white rounded-[2rem] p-5 shadow-sm border border-slate-100">
                <h2 class="text-base font-black text-slate-900 mb-4 flex items-center gap-2">
                    <span>📋</span> Chi tiết đơn hàng
                </h2>

                <div class="space-y-4 mb-6">
                    <div v-for="item in order.items" :key="item.id" class="flex items-center gap-3 border-b border-slate-50 pb-4 last:border-0 last:pb-0">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-sm font-bold text-slate-500 shrink-0">
                            {{ item.quantity }}x
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-slate-900 line-clamp-1">{{ item.product.name }}</p>
                            <p class="text-[10px] text-slate-500">{{ formatCurrency(item.price) }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-black text-slate-900">{{ formatCurrency(item.price * item.quantity) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Summary Card -->
                <div class="rounded-[1.5rem] bg-slate-50 p-4 space-y-4">
                    <!-- Thu nhập tài xế -->
                    <div class="bg-indigo-50/50 rounded-xl p-3 border border-indigo-100/50">
                        <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2">Thu nhập của bạn</p>
                        <div class="space-y-1.5">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-600 font-medium">Phí giao hàng</span>
                                <span class="text-slate-900 font-bold">{{ formatCurrency(order.shipping_fee) }}</span>
                            </div>
                            <div v-if="order.shipper_tip > 0" class="flex justify-between items-center text-xs">
                                <span class="text-slate-600 font-medium flex items-center gap-1"><span>💖</span> Tiền Tip từ khách</span>
                                <span class="text-orange-500 font-bold">+{{ formatCurrency(order.shipper_tip) }}</span>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-t border-indigo-100 flex justify-between items-center">
                            <span class="text-sm font-bold text-indigo-900">Tổng thu nhập</span>
                            <span class="text-lg font-black text-indigo-600">{{ formatCurrency(parseFloat(order.shipping_fee) + parseFloat(order.shipper_tip || 0)) }}</span>
                        </div>
                    </div>

                    <!-- Tiền thu khách -->
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Chi tiết thu khách</p>
                        <div class="space-y-1.5 px-1">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-500 font-medium">Tiền hàng</span>
                                <span class="text-slate-900 font-bold">{{ formatCurrency(order.subtotal) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-500 font-medium">Phí giao hàng</span>
                                <span class="text-slate-900 font-bold">{{ formatCurrency(order.shipping_fee) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-500 font-medium">Phí dịch vụ & đóng gói</span>
                                <span class="text-slate-900 font-bold">{{ formatCurrency(parseFloat(order.service_fee || 0) + parseFloat(order.packaging_fee || 0)) }}</span>
                            </div>
                            <div v-if="order.shipper_tip > 0" class="flex justify-between items-center text-xs">
                                <span class="text-slate-500 font-medium">Tiền Tip</span>
                                <span class="text-slate-900 font-bold">{{ formatCurrency(order.shipper_tip) }}</span>
                            </div>
                            <div v-if="order.discount_amount" class="flex justify-between items-center text-xs">
                                <span class="text-slate-500 font-medium">Khuyến mãi</span>
                                <span class="text-emerald-600 font-bold">-{{ formatCurrency(order.discount_amount) }}</span>
                            </div>
                        </div>
                        <div class="pt-2.5 mt-2.5 border-t border-slate-200/60 flex justify-between items-center px-1">
                            <span class="text-sm font-bold text-slate-900">
                                {{ order.payment_method === 'vnpay' ? 'Đã thanh toán (VNPay)' : 'Thu tiền mặt' }}
                            </span>
                            <span class="text-xl font-black" :class="order.payment_method === 'vnpay' ? 'text-emerald-500 line-through' : 'text-slate-900'">
                                {{ formatCurrency(order.total) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Action Bar -->
        <div class="fixed bottom-0 left-0 right-0 z-40 bg-white/90 backdrop-blur-md p-4 pb-safe border-t border-slate-200 shadow-[0_-10px_30px_rgba(0,0,0,0.1)]">
            <div class="max-w-md mx-auto">
                <!-- Arrive at Restaurant: assigned/shipping/confirmed → arrived -->
                <div v-if="['assigned', 'shipping', 'confirmed'].includes(order.status)" class="space-y-3">
                    <div
                        @touchstart="startSwipe($event, 'arriveAtRestaurant')"
                        @touchmove="handleSwipe($event)"
                        @touchend="endSwipe('arriveAtRestaurant')"
                        @mousemove="isMobile() ? null : handleSwipe($event)"
                        @mousedown="!isMobile() && startSwipe($event, 'arriveAtRestaurant')"
                        @mouseup="!isMobile() && endSwipe('arriveAtRestaurant')"
                        class="relative h-[60px] bg-slate-100 rounded-full overflow-hidden cursor-grab active:cursor-grabbing select-none group border border-slate-200"
                    >
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <span class="text-sm font-bold text-slate-400">Vuốt để xác nhận đến quán →</span>
                        </div>
                        <div
                            :style="{ transform: `translateX(${swipeDistance}px)` }"
                            class="absolute inset-y-0 left-0 bg-indigo-600 flex items-center justify-center rounded-full shadow-lg transition-transform duration-75 will-change-transform z-10"
                            style="width: 60px"
                        >
                            <span class="text-white text-xl">🏪</span>
                        </div>
                        <div
                            :style="{ width: `max(60px, ${swipeDistance + 60}px)` }"
                            class="absolute inset-y-0 left-0 bg-indigo-500 transition-all duration-75 will-change-[width] z-0 opacity-50"
                        ></div>
                    </div>
                    <button
                        @click="arriveAtRestaurant"
                        :disabled="isLoading"
                        class="w-full text-xs font-bold text-indigo-600 py-2"
                    >
                        Nhấn vào đây nếu không thể vuốt
                    </button>
                </div>

                <!-- Confirm Pickup: arrived → picked_up -->
                <div v-if="order.status === 'arrived'" class="space-y-3">
                    <div
                        @touchstart="startSwipe($event, 'confirmPickup')"
                        @touchmove="handleSwipe($event)"
                        @touchend="endSwipe('confirmPickup')"
                        @mousemove="isMobile() ? null : handleSwipe($event)"
                        @mousedown="!isMobile() && startSwipe($event, 'confirmPickup')"
                        @mouseup="!isMobile() && endSwipe('confirmPickup')"
                        class="relative h-[60px] bg-slate-100 rounded-full overflow-hidden cursor-grab active:cursor-grabbing select-none group border border-slate-200"
                    >
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <span class="text-sm font-bold text-slate-400">Vuốt để xác nhận lấy hàng →</span>
                        </div>
                        <div
                            :style="{ transform: `translateX(${swipeDistance}px)` }"
                            class="absolute inset-y-0 left-0 bg-orange-500 flex items-center justify-center rounded-full shadow-lg transition-transform duration-75 will-change-transform z-10"
                            style="width: 60px"
                        >
                            <span class="text-white text-xl">📦</span>
                        </div>
                        <div
                            :style="{ width: `max(60px, ${swipeDistance + 60}px)` }"
                            class="absolute inset-y-0 left-0 bg-orange-400 transition-all duration-75 will-change-[width] z-0 opacity-50"
                        ></div>
                    </div>
                </div>

                <!-- Complete Order: picked_up → completed -->
                <div v-if="order.status === 'picked_up'" class="space-y-3">
                    <div
                        @touchstart="startSwipe($event, 'completeOrder')"
                        @touchmove="handleSwipe($event)"
                        @touchend="endSwipe('completeOrder')"
                        @mousemove="isMobile() ? null : handleSwipe($event)"
                        @mousedown="!isMobile() && startSwipe($event, 'completeOrder')"
                        @mouseup="!isMobile() && endSwipe('completeOrder')"
                        class="relative h-[60px] bg-slate-100 rounded-full overflow-hidden cursor-grab active:cursor-grabbing select-none group border border-slate-200"
                    >
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <span class="text-sm font-bold text-slate-400">Vuốt để hoàn tất giao hàng →</span>
                        </div>
                        <div
                            :style="{ transform: `translateX(${swipeDistance}px)` }"
                            class="absolute inset-y-0 left-0 bg-emerald-500 flex items-center justify-center rounded-full shadow-lg transition-transform duration-75 will-change-transform z-10"
                            style="width: 60px"
                        >
                            <span class="text-white text-xl">✅</span>
                        </div>
                        <div
                            :style="{ width: `max(60px, ${swipeDistance + 60}px)` }"
                            class="absolute inset-y-0 left-0 bg-emerald-400 transition-all duration-75 will-change-[width] z-0 opacity-50"
                        ></div>
                    </div>
                </div>

                <!-- Completed State -->
                <div v-if="order.status === 'completed'" class="flex items-center justify-center gap-2 h-[60px] bg-emerald-50 rounded-full text-emerald-600 font-bold border border-emerald-100">
                    <span class="text-xl">🎉</span> Đơn hàng đã hoàn thành
                </div>

                <!-- Waiting State -->
                <div v-if="order.status === 'pending'" class="flex items-center justify-center gap-2 h-[60px] bg-slate-50 rounded-full text-slate-500 font-bold border border-slate-200">
                    <span class="text-xl animate-pulse">⏳</span> Chờ quán xác nhận
                </div>

                <!-- Báo cáo sự cố Button -->
                <div v-if="['shipping', 'arrived', 'picked_up'].includes(order.status)" class="mt-4 text-center">
                    <button @click="showIncidentModal = true" class="text-sm font-bold text-red-400 hover:text-red-500 underline underline-offset-2 transition-colors">
                        Báo cáo sự cố / Hủy đơn
                    </button>
                </div>
            </div>
        </div>

        <!-- Incident Modal -->
        <div v-if="showIncidentModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
            <div class="bg-white rounded-[2rem] shadow-xl w-full max-w-md max-h-[90vh] flex flex-col overflow-hidden animate-slide-up">
                <div class="flex justify-between items-center p-5 border-b border-slate-100">
                    <h2 class="text-lg font-black text-slate-900">⚠️ Báo cáo sự cố</h2>
                    <button @click="showIncidentModal = false" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold active:scale-95 transition-all">
                        ✕
                    </button>
                </div>
                <div class="p-5 overflow-y-auto space-y-4">
                    <p class="text-sm text-slate-600">Vui lòng chọn lý do hoặc nhập chi tiết sự cố. Đơn hàng sẽ bị hoàn về hệ thống hoặc hủy bỏ tùy theo trạng thái.</p>
                    
                    <div class="space-y-2">
                        <button v-for="reason in predefinedReasons" :key="reason"
                            @click="incidentReason = reason"
                            :class="incidentReason === reason ? 'border-red-500 bg-red-50 text-red-700' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                            class="w-full text-left px-4 py-3 rounded-xl border font-medium transition-all text-sm"
                        >
                            {{ reason }}
                        </button>
                    </div>

                    <div class="mt-4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Chi tiết (tùy chọn)</label>
                        <textarea v-model="incidentReason" rows="3" placeholder="Nhập lý do cụ thể..." class="w-full rounded-xl border-slate-200 focus:border-red-500 focus:ring focus:ring-red-200 transition-all text-sm"></textarea>
                    </div>
                </div>
                <div class="p-5 border-t border-slate-100 bg-slate-50 flex gap-3">
                    <button @click="showIncidentModal = false" class="flex-1 py-3.5 rounded-xl font-bold text-slate-600 bg-white border border-slate-200 active:scale-95 transition-transform">
                        Đóng
                    </button>
                    <button @click="submitIncident" :disabled="!incidentReason || isSubmittingIncident" class="flex-1 py-3.5 rounded-xl font-bold text-white bg-red-600 hover:bg-red-700 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2">
                        <span v-if="isSubmittingIncident" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        Xác nhận
                    </button>
                </div>
            </div>
        </div>

        <!-- Customer Map Modal -->
        <div v-if="showCustomerMap" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
            <div class="bg-white rounded-[2rem] shadow-xl w-full max-w-md max-h-[80vh] flex flex-col overflow-hidden animate-slide-up">
                <div class="flex justify-between items-center p-5 border-b border-slate-100">
                    <h2 class="text-lg font-black text-slate-900">🗺️ Vị trí khách hàng</h2>
                    <button @click="showCustomerMap = false; customerMapInstance = null;" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold active:scale-95 transition-all">
                        ✕
                    </button>
                </div>
                <div id="customer-map" class="h-64 md:h-80 w-full bg-slate-200"></div>
                <div class="p-5 border-t border-slate-100 space-y-4">
                    <div class="flex gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-xl shrink-0">📍</div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ order.user.name }}</p>
                            <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ order.address }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a :href="`tel:${order.phone}`" class="flex-1 flex items-center justify-center gap-2 rounded-2xl bg-indigo-50 px-4 py-3 text-sm font-bold text-indigo-600">
                            📞 Gọi
                        </a>
                        <button @click="openCustomerInMaps" class="flex-1 flex items-center justify-center gap-2 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-600">
                            🗺️ Google Maps
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Restaurant Map Modal -->
        <div v-if="showRestaurantMap" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
            <div class="bg-white rounded-[2rem] shadow-xl w-full max-w-md max-h-[80vh] flex flex-col overflow-hidden animate-slide-up">
                <div class="flex justify-between items-center p-5 border-b border-slate-100">
                    <h2 class="text-lg font-black text-slate-900">🗺️ Vị trí quán ăn</h2>
                    <button @click="showRestaurantMap = false; restaurantMapInstance = null;" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold active:scale-95 transition-all">
                        ✕
                    </button>
                </div>
                <div id="restaurant-map" class="h-64 md:h-80 w-full bg-slate-200"></div>
                <div class="p-5 border-t border-slate-100 space-y-4">
                    <div class="flex gap-3">
                        <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-xl shrink-0">🏪</div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ restaurant.name }}</p>
                            <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ restaurant.address }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a :href="`tel:${restaurant.phone}`" class="flex-1 flex items-center justify-center gap-2 rounded-2xl bg-orange-50 px-4 py-3 text-sm font-bold text-orange-600">
                            📞 Gọi
                        </a>
                        <button @click="openRestaurantInMaps" class="flex-1 flex items-center justify-center gap-2 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-600">
                            🗺️ Google Maps
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </ShipperLayout>

    <!-- Chat Widget -->
    <ChatWidget 
        v-if="order" 
        :orderId="order.id" 
        :orderCode="order.order_code" 
    />
</template>

<style scoped>
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom, 16px);
}
@keyframes slide-up {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
.animate-slide-up {
    animation: slide-up 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import ShipperLayout from "@/Layouts/ShipperLayout.vue";
import ChatWidget from "@/Components/ChatWidget.vue";
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

// Incident state
const showIncidentModal = ref(false);
const incidentReason = ref("");
const isSubmittingIncident = ref(false);
const predefinedReasons = [
    "Xe hỏng / Tai nạn",
    "Quán đóng cửa / Hết món",
    "Khách không nghe máy / Bom hàng",
    "Kẹt xe / Thời tiết xấu",
    "Khác"
];

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

const arriveAtRestaurant = async () => {
    try {
        isLoading.value = true;
        const response = await csrfFetch(
            `/api/shipper/orders/${order.value.id}/arrive-at-restaurant`,
            {
                method: "POST",
                headers: {
                    Accept: "application/json",
                },
            },
        );
        if (response.ok) {
            order.value.status = "arrived";
            showToast("success", "🏪 Đã xác nhận đến quán");
        } else {
            const error = await response.json();
            throw new Error(error?.error || "Không thể xác nhận đến quán.");
        }
    } catch (error) {
        console.error("Error arriving at restaurant:", error);
        showToast("error", error.message || "❌ Có lỗi khi xác nhận đến quán");
    } finally {
        isLoading.value = false;
    }
};

const submitIncident = async () => {
    if (!incidentReason.value) return;
    
    try {
        isSubmittingIncident.value = true;
        const response = await csrfFetch(
            `/api/shipper/orders/${order.value.id}/report-incident`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                body: JSON.stringify({ reason: incidentReason.value })
            }
        );
        
        if (response.ok) {
            const data = await response.json();
            showToast("success", data.message || "Báo cáo thành công");
            showIncidentModal.value = false;
            
            // Cập nhật giao diện cục bộ (hoặc chuyển hướng)
            if (data.message.includes('Hủy đơn')) {
                order.value.status = 'cancelled';
            } else {
                order.value.status = 'confirmed';
            }
            
            // Chuyển hướng về Dashboard sau 1s
            setTimeout(() => {
                router.visit('/shipper/dashboard');
            }, 1000);
        } else {
            const error = await response.json();
            throw new Error(error?.error || "Không thể báo cáo sự cố.");
        }
    } catch (error) {
        console.error("Error reporting incident:", error);
        showToast("error", error.message || "❌ Có lỗi khi báo cáo sự cố");
    } finally {
        isSubmittingIncident.value = false;
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
            showToast("success", "📦 Đã xác nhận lấy hàng");
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

        if (action === "arriveAtRestaurant") {
            await arriveAtRestaurant();
        } else if (action === "confirmPickup") {
            await confirmPickup();
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
        pending: "Chờ quán xác nhận",
        confirmed: "Quán đã xác nhận",
        assigned: "Đang đến quán",
        shipping: "Đang đến quán",
        arrived: "Đã đến quán, chờ lấy hàng",
        picked_up: "Đã lấy hàng, đang giao đến khách",
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

// Hoàn tất đơn hàng: picked_up → completed
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

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error?.error || "Không thể hoàn thành đơn hàng.");
        }

        order.value.status = "completed";
        showToast("success", "🎉 Chúc mừng! Bạn đã hoàn thành đơn hàng");

        // Có thể điều hướng về dashboard sau khi hoàn thành
        router.visit("/shipper/dashboard");
    } catch (error) {
        console.error("Error completing order:", error);
        showToast(
            "error",
            error.message || "❌ Có lỗi khi xác nhận hoàn thành",
        );
    } finally {
        isLoading.value = false;
    }
};
</script>
