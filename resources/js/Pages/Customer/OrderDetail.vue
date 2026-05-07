<template>
    <GuestLayout>
        <Head :title="`Đơn hàng ${orderData.order_code} - FoodXpress`" />

        <div
            class="min-h-screen bg-[#f4f7f9] py-8 md:py-12 px-4 font-sans text-slate-900"
        >
            <div class="max-w-5xl mx-auto space-y-6">
                <div
                    class="bg-white rounded-[2.5rem] shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-white overflow-hidden p-8 md:p-12 transition-all"
                >
                    <div
                        class="flex flex-col md:flex-row justify-between items-start gap-6 mb-12"
                    >
                        <div class="space-y-2">
                            <p
                                class="text-[11px] uppercase tracking-[0.3em] text-orange-500 font-black flex items-center gap-2"
                            >
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"
                                    ></span>
                                    <span
                                        class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"
                                    ></span>
                                </span>
                                Thời gian giao dự kiến
                            </p>
                            <h2
                                class="text-4xl md:text-5xl font-black tracking-tight italic"
                            >
                                {{ estimatedDeliveryTime }}
                            </h2>
                            <p class="text-slate-400 text-sm font-medium">
                                FoodXpress sẽ cập nhật thông báo khi Shipper di
                                chuyển.
                            </p>
                        </div>
                        <div
                            class="px-8 py-4 bg-orange-50 text-orange-600 rounded-full font-black uppercase tracking-widest text-sm shadow-sm border border-orange-100"
                        >
                            {{ getStatusHeadline(orderData.status) }}
                        </div>
                    </div>

                    <div class="relative px-4 pb-4">
                        <div
                            class="absolute top-7 left-0 w-full h-[4px] bg-slate-100 rounded-full z-0"
                        >
                            <div
                                class="h-full bg-orange-500 transition-all duration-1000 shadow-[0_0_15px_rgba(249,115,22,0.4)]"
                                :style="{ width: `${progressPercent}%` }"
                            ></div>
                        </div>

                        <div
                            class="relative z-10 flex justify-between items-start"
                        >
                            <div
                                v-for="step in progressSteps"
                                :key="step.key"
                                class="flex flex-col items-center group flex-1"
                            >
                                <div
                                    :class="[
                                        'w-14 h-14 rounded-2xl flex items-center justify-center text-2xl transition-all duration-500 border-4 border-white shadow-md',
                                        step.active
                                            ? 'bg-orange-500 text-white scale-110 shadow-orange-200'
                                            : 'bg-slate-100 text-slate-400',
                                    ]"
                                >
                                    {{ step.icon }}
                                </div>
                                <span
                                    :class="[
                                        'mt-4 text-[10px] md:text-[11px] uppercase tracking-[0.15em] font-black text-center px-2',
                                        step.active
                                            ? 'text-slate-900'
                                            : 'text-slate-300',
                                    ]"
                                >
                                    {{ step.title }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-[1.6fr_1fr]">
                    <div class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <button
                                @click="showRestaurantDetail = true"
                                class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-50 hover:shadow-xl hover:border-orange-100 transition-all text-left group flex flex-col justify-between h-full min-h-[180px]"
                            >
                                <div class="w-full">
                                    <div
                                        class="flex justify-between items-start gap-2 mb-4"
                                    >
                                        <p
                                            class="text-[10px] uppercase tracking-[0.2em] text-slate-400 font-black"
                                        >
                                            🏪 Từ nhà hàng
                                        </p>
                                        <span
                                            class="text-xl opacity-0 group-hover:opacity-100 transition-opacity"
                                            >ℹ️</span
                                        >
                                    </div>
                                    <h3
                                        class="font-black text-base md:text-lg mb-3 line-clamp-1 group-hover:text-orange-600 transition-colors"
                                    >
                                        {{ restaurant.name }}
                                    </h3>
                                    <p
                                        class="text-sm text-slate-500 leading-relaxed line-clamp-2"
                                    >
                                        {{ restaurant.address }}
                                    </p>
                                </div>
                                <p
                                    v-if="restaurant.phone"
                                    class="text-xs text-slate-400 mt-5 font-medium"
                                >
                                    📞 {{ restaurant.phone }}
                                </p>
                            </button>

                            <button
                                @click="showCustomerDetail = true"
                                class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-50 hover:shadow-xl hover:border-blue-100 transition-all text-left group relative flex flex-col justify-between h-full min-h-[180px]"
                            >
                                <span
                                    class="absolute top-6 right-6 text-[9px] font-black px-3 py-1 bg-slate-100 rounded-lg uppercase tracking-wider group-hover:bg-blue-100 transition-colors"
                                    >Bạn</span
                                >
                                <div class="w-full pr-12">
                                    <div
                                        class="flex justify-between items-start gap-2 mb-4"
                                    >
                                        <p
                                            class="text-[10px] uppercase tracking-[0.2em] text-slate-400 font-black"
                                        >
                                            🏠 Giao đến
                                        </p>
                                        <span
                                            class="text-xl opacity-0 group-hover:opacity-100 transition-opacity"
                                            >ℹ️</span
                                        >
                                    </div>
                                    <h3
                                        class="font-black text-base md:text-lg mb-3 line-clamp-1 text-slate-900 group-hover:text-blue-600 transition-colors"
                                    >
                                        👤
                                        {{
                                            orderData.name ||
                                            (orderData.user
                                                ? orderData.user.name
                                                : "Người nhận")
                                        }}
                                    </h3>
                                    <p
                                        class="text-sm text-slate-500 leading-relaxed line-clamp-2"
                                    >
                                        {{ orderData.address }}
                                    </p>
                                </div>
                                <div class="w-full mt-5">
                                    <p
                                        class="text-xs text-slate-400 font-medium"
                                    >
                                        📞 {{ orderData.phone }}
                                    </p>
                                    <p
                                        v-if="orderData.note"
                                        class="text-xs text-slate-400 mt-2 line-clamp-1 italic"
                                    >
                                        📝 {{ orderData.note }}
                                    </p>
                                </div>
                            </button>
                        </div>

                        <div
                            class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden"
                        >
                            <div
                                class="p-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/20"
                            >
                                <h3
                                    class="font-black text-slate-800 flex items-center gap-2 text-xs uppercase tracking-[0.2em]"
                                >
                                    📍 Live Tracking
                                </h3>
                                <span
                                    v-if="showTrackingMap"
                                    class="flex h-2 w-2 rounded-full bg-green-500 animate-pulse"
                                ></span>
                            </div>
                            <div
                                v-if="showTrackingMap"
                                id="order-map"
                                class="h-[400px] w-full"
                            ></div>
                            <div
                                v-else
                                class="h-[250px] flex flex-col items-center justify-center bg-slate-50 text-slate-400 p-8 text-center italic"
                            >
                                <p class="font-bold text-sm">
                                    Shipper chưa bắt đầu di chuyển hoặc đang
                                    chuẩn bị hàng.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div
                            class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 p-8"
                        >
                            <h3 class="text-xl font-black mb-8 italic">
                                Đơn hàng của bạn
                            </h3>
                            <div class="space-y-6 mb-8">
                                <div
                                    v-for="item in orderData.items"
                                    :key="item.id"
                                    class="flex items-center gap-4 group"
                                >
                                    <div
                                        class="w-16 h-16 rounded-2xl overflow-hidden shadow-sm border border-slate-50"
                                    >
                                        <img
                                            :src="
                                                '/storage/' + item.product.image
                                            "
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                        />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="font-black text-slate-900 text-sm truncate"
                                        >
                                            {{ item.product.name }}
                                        </p>
                                        <p
                                            class="text-xs text-slate-400 font-bold mt-1 uppercase"
                                        >
                                            {{ item.quantity }} phần •
                                            {{ formatPrice(item.price) }}
                                        </p>
                                    </div>
                                    <p
                                        class="font-black text-slate-900 text-sm italic"
                                    >
                                        {{
                                            formatPrice(
                                                item.price * item.quantity,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="space-y-4 pt-6 border-t border-slate-50"
                            >
                                <div
                                    class="flex justify-between text-sm font-medium text-slate-500"
                                >
                                    <span>Tạm tính</span>
                                    <span class="font-bold text-slate-900">{{
                                        formatPrice(orderData.subtotal)
                                    }}</span>
                                </div>
                                <div
                                    class="flex justify-between text-sm font-medium text-slate-500"
                                >
                                    <span>Phí giao hàng</span>
                                    <span class="font-bold text-slate-900">{{
                                        formatPrice(orderData.shipping_fee)
                                    }}</span>
                                </div>
                                <div
                                    class="flex justify-between text-sm font-medium text-slate-500"
                                >
                                    <span>Phí dịch vụ</span>
                                    <span class="font-bold text-slate-900">{{
                                        formatPrice(orderData.service_fee || 0)
                                    }}</span>
                                </div>
                                <div
                                    v-if="orderData.packaging_fee > 0"
                                    class="flex justify-between text-sm font-medium text-slate-500"
                                >
                                    <span>Phí đóng gói</span>
                                    <span class="font-bold text-slate-900">{{
                                        formatPrice(orderData.packaging_fee)
                                    }}</span>
                                </div>
                                <div
                                    v-if="orderData.shipper_tip > 0"
                                    class="flex justify-between text-sm font-bold text-yellow-500"
                                >
                                    <span>Tip cho tài xế</span>
                                    <span>+{{
                                        formatPrice(orderData.shipper_tip)
                                    }}</span>
                                </div>
                                <div
                                    v-if="orderData.discount_amount > 0"
                                    class="flex justify-between text-sm font-bold text-green-600"
                                >
                                    <span>Giảm giá</span>
                                    <span
                                        >-{{
                                            formatPrice(
                                                orderData.discount_amount,
                                            )
                                        }}</span
                                    >
                                </div>

                                <div
                                    class="flex justify-between items-center py-4 px-5 bg-slate-50 rounded-2xl my-6 border border-slate-100"
                                >
                                    <span
                                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest"
                                        >Thanh toán bằng</span
                                    >
                                    <p
                                        class="font-black text-slate-900 text-sm italic"
                                    >
                                        {{
                                            orderData.payment_method === "cod"
                                                ? "💵 Tiền mặt"
                                                : "💳 VNPay"
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="flex justify-between text-2xl font-black text-slate-900 pt-2 border-t border-slate-100 italic"
                                >
                                    <span>Tổng cộng</span>
                                    <span class="text-orange-600">{{
                                        formatPrice(orderData.total)
                                    }}</span>
                                </div>
                            </div>

                            <div
                                v-if="
                                    [
                                        'picked_up',
                                        'shipping',
                                        'arrived',
                                    ].includes(orderData.status)
                                "
                                class="mt-8"
                            >
                                <button
                                    @click="confirmDelivery"
                                    class="w-full py-4 bg-green-600 hover:bg-green-700 text-white rounded-2xl font-black text-sm transition-all shadow-xl shadow-green-100 flex items-center justify-center gap-2"
                                >
                                    ✅ ĐÃ NHẬN ĐƯỢC HÀNG
                                </button>
                            </div>

                            <div v-if="canCancelOrder" class="mt-4">
                                <button
                                    @click="cancelOrder"
                                    class="w-full py-3 text-red-400 hover:text-red-600 font-black text-xs uppercase tracking-widest transition-all"
                                >
                                    Hủy đơn hàng
                                </button>
                            </div>
                        </div>

                        <div
                            v-if="orderData.shipper"
                            class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-xl shadow-slate-200"
                        >
                            <div class="flex items-center gap-5">
                                <UserAvatar
                                    :user="orderData.shipper.user"
                                    class="w-16 h-16 ring-4 ring-slate-800"
                                />
                                <div class="flex-1">
                                    <p
                                        class="text-[10px] font-black uppercase tracking-widest mb-1"
                                        :class="
                                            orderData.status === 'completed'
                                                ? 'text-green-400'
                                                : 'text-orange-400'
                                        "
                                    >
                                        {{
                                            orderData.status === "completed"
                                                ? "Shipper đã giao"
                                                : "Tài xế đang giao"
                                        }}
                                    </p>
                                    <p class="font-black text-lg">
                                        {{ orderData.shipper.user.name }}
                                    </p>
                                    <p class="text-slate-400 text-xs italic">
                                        {{ orderData.shipper.user.phone }}
                                    </p>
                                </div>
                                <a
                                    :href="
                                        'tel:' + orderData.shipper.user.phone
                                    "
                                    class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center hover:bg-orange-500 transition-all"
                                    >📞</a
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="showRestaurantDetail"
                class="fixed inset-0 z-50 flex items-end md:items-center justify-center bg-black/40 backdrop-blur-sm p-4 animate-fade-in"
            >
                <div
                    class="bg-white rounded-t-[2rem] md:rounded-[2rem] shadow-xl w-full md:max-w-md max-h-[80vh] flex flex-col overflow-hidden animate-slide-up md:animate-zoom-in"
                >
                    <div
                        class="flex justify-between items-center p-6 border-b border-slate-100 bg-gradient-to-r from-orange-50 to-white"
                    >
                        <h2 class="text-lg font-black text-slate-900">
                            🏪 Thông tin quán ăn
                        </h2>
                        <button
                            @click="showRestaurantDetail = false"
                            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 font-bold transition-all"
                        >
                            ✕
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-6 space-y-6">
                        <div>
                            <p
                                class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2"
                            >
                                Tên quán ăn
                            </p>
                            <p class="text-lg font-black text-slate-900">
                                {{ restaurant.name }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2"
                            >
                                Địa chỉ
                            </p>
                            <div class="flex items-start gap-3">
                                <p
                                    class="text-base text-slate-700 leading-relaxed flex-1"
                                >
                                    {{ restaurant.address }}
                                </p>
                                <button
                                    @click="
                                        copyToClipboard(
                                            restaurant.address,
                                            'Địa chỉ quán',
                                        )
                                    "
                                    class="shrink-0 p-2 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-all"
                                    title="Copy địa chỉ"
                                >
                                    📋
                                </button>
                            </div>
                        </div>

                        <div v-if="restaurant.phone">
                            <p
                                class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2"
                            >
                                Số điện thoại
                            </p>
                            <div class="flex items-center gap-3">
                                <a
                                    :href="'tel:' + restaurant.phone"
                                    class="text-base font-bold text-blue-600 hover:text-blue-700"
                                >
                                    📞 {{ restaurant.phone }}
                                </a>
                                <button
                                    @click="
                                        copyToClipboard(
                                            restaurant.phone,
                                            'Số điện thoại quán',
                                        )
                                    "
                                    class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                                    title="Copy số điện thoại"
                                >
                                    📋
                                </button>
                            </div>
                        </div>

                        <div v-if="restaurant.latitude && restaurant.longitude">
                            <p
                                class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2"
                            >
                                Vị trí tọa độ
                            </p>
                            <p class="text-sm text-slate-600 font-mono">
                                {{ restaurant.latitude }},
                                {{ restaurant.longitude }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="border-t border-slate-100 p-6 bg-slate-50 flex gap-3"
                    >
                        <a
                            v-if="restaurant.phone"
                            :href="'tel:' + restaurant.phone"
                            class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold flex items-center justify-center gap-2 transition-all"
                        >
                            📞 Gọi quán
                        </a>
                        <button
                            @click="showRestaurantDetail = false"
                            class="flex-1 py-3 bg-white hover:bg-slate-100 text-slate-700 rounded-xl font-bold border border-slate-200 transition-all"
                        >
                            Đóng
                        </button>
                    </div>
                </div>
            </div>

            <div
                v-if="showCustomerDetail"
                class="fixed inset-0 z-50 flex items-end md:items-center justify-center bg-black/40 backdrop-blur-sm p-4 animate-fade-in"
            >
                <div
                    class="bg-white rounded-t-[2rem] md:rounded-[2rem] shadow-xl w-full md:max-w-md max-h-[80vh] flex flex-col overflow-hidden animate-slide-up md:animate-zoom-in"
                >
                    <div
                        class="flex justify-between items-center p-6 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-white"
                    >
                        <h2 class="text-lg font-black text-slate-900">
                            👤 Thông tin giao hàng
                        </h2>
                        <button
                            @click="showCustomerDetail = false"
                            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 font-bold transition-all"
                        >
                            ✕
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-6 space-y-6">
                        <div>
                            <p
                                class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2"
                            >
                                Tên người nhận
                            </p>
                            <p class="text-base font-black text-slate-900">
                                {{
                                    orderData.name ||
                                    (orderData.user
                                        ? orderData.user.name
                                        : "Người nhận")
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2"
                            >
                                Địa chỉ giao hàng
                            </p>
                            <div class="flex items-start gap-3">
                                <p
                                    class="text-base text-slate-700 leading-relaxed flex-1"
                                >
                                    {{ orderData.address }}
                                </p>
                                <button
                                    @click="
                                        copyToClipboard(
                                            orderData.address,
                                            'Địa chỉ giao hàng',
                                        )
                                    "
                                    class="shrink-0 p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                                    title="Copy địa chỉ"
                                >
                                    📋
                                </button>
                            </div>
                        </div>

                        <div>
                            <p
                                class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2"
                            >
                                Số điện thoại
                            </p>
                            <div class="flex items-center gap-3">
                                <a
                                    :href="'tel:' + orderData.phone"
                                    class="text-base font-bold text-blue-600 hover:text-blue-700"
                                >
                                    📞 {{ orderData.phone }}
                                </a>
                                <button
                                    @click="
                                        copyToClipboard(
                                            orderData.phone,
                                            'Số điện thoại',
                                        )
                                    "
                                    class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                                    title="Copy số điện thoại"
                                >
                                    📋
                                </button>
                            </div>
                        </div>

                        <div v-if="orderData.note">
                            <p
                                class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2"
                            >
                                Ghi chú
                            </p>
                            <div
                                class="bg-yellow-50 border border-yellow-200 rounded-xl p-4"
                            >
                                <p class="text-sm text-slate-700">
                                    📝 {{ orderData.note }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="
                                orderData.user?.latitude &&
                                orderData.user?.longitude
                            "
                        >
                            <p
                                class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2"
                            >
                                Vị trí tọa độ
                            </p>
                            <p class="text-sm text-slate-600 font-mono">
                                {{ orderData.user.latitude }},
                                {{ orderData.user.longitude }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="border-t border-slate-100 p-6 bg-slate-50 flex gap-3"
                    >
                        <a
                            :href="'tel:' + orderData.phone"
                            class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold flex items-center justify-center gap-2 transition-all"
                        >
                            📞 Gọi
                        </a>
                        <button
                            @click="showCustomerDetail = false"
                            class="flex-1 py-3 bg-white hover:bg-slate-100 text-slate-700 rounded-xl font-bold border border-slate-200 transition-all"
                        >
                            Đóng
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>

    <!-- Chat Widget -->
    <ChatWidget 
        v-if="orderData" 
        :orderId="orderData.id" 
        :orderCode="orderData.order_code" 
    />
</template>
<script setup>
import { computed, ref, onMounted, watch, onUnmounted } from "vue";
import { Head } from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import ChatWidget from "@/Components/ChatWidget.vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import Swal from "sweetalert2";

const props = defineProps({ order: Object });

const map = ref(null);
const orderData = ref(props.order);
const shipperMarker = ref(null);
const refreshInterval = ref(null);
const showRestaurantDetail = ref(false);
const showCustomerDetail = ref(false);

const restaurant = computed(() => {
    if (orderData.value.restaurant) return orderData.value.restaurant;
    const productUser = orderData.value.items?.[0]?.product?.user;
    return {
        name: productUser?.restaurant_name ?? productUser?.name ?? "Nhà hàng",
        address: productUser?.address ?? "Đang cập nhật địa chỉ...",
        phone: productUser?.phone ?? "",
        latitude: productUser?.latitude,
        longitude: productUser?.longitude,
    };
});

const estimatedDeliveryTime = computed(() => {
    if (!orderData.value.created_at) return "--:--";
    const created = new Date(orderData.value.created_at);
    const start = new Date(created.getTime() + 20 * 60000);
    const end = new Date(created.getTime() + 40 * 60000);
    const formatTime = (date) =>
        new Intl.DateTimeFormat("vi-VN", {
            hour: "2-digit",
            minute: "2-digit",
        }).format(date);
    return `${formatTime(start)} - ${formatTime(end)}`;
});

const progressSteps = computed(() => [
    {
        key: "placed",
        icon: "📋",
        title: "Đặt đơn",
        active: [
            "pending",
            "confirmed",
            "assigned",
            "shipping",
            "arrived",
            "picked_up",
            "completed",
        ].includes(orderData.value.status),
    },
    {
        key: "confirmed",
        icon: "🍳",
        title: "Quán xác nhận",
        active: [
            "confirmed",
            "assigned",
            "shipping",
            "arrived",
            "picked_up",
            "completed",
        ].includes(orderData.value.status),
    },
    {
        key: "arrived",
        icon: "🏪",
        title: "Shipper đến quán",
        active: [
            "arrived",
            "picked_up",
            "completed",
        ].includes(orderData.value.status),
    },
    {
        key: "completed",
        icon: "🎉",
        title: "Giao thành công",
        active: ["completed"].includes(orderData.value.status),
    },
]);

const progressPercent = computed(() => {
    const s = orderData.value.status;
    const stepWidth = 100 / (progressSteps.value.length - 1); // 33.33%
    if (s === "pending") return 0;
    if (s === "confirmed") return stepWidth * 1;       // Bước 2
    if (s === "assigned") return stepWidth * 1.5;     // Giữa bước 2-3 (shipper nhận đơn)
    if (s === "shipping") return stepWidth * 2;       // Shipper đang di chuyển đến quán
    if (s === "arrived") return stepWidth * 2;        // Bước 3 — shipper đến quán
    if (s === "picked_up") return stepWidth * 2.5;    // Giữa bước 3-4 (đã lấy hàng)
    if (s === "completed") return 100;                // Bước 4
    return 0;
});

const showTrackingMap = computed(() => {
    return (
        ["shipping", "arrived", "picked_up"].includes(orderData.value.status) &&
        orderData.value.shipper?.current_latitude
    );
});

const getStatusHeadline = (status) => {
    const headlines = {
        pending: "⏳ Chờ quán xác nhận",
        confirmed: "🍳 Quán đã xác nhận & đang nấu",
        assigned: "🛵 Shipper đang trên đường đến quán",
        shipping: "🏃 Shipper đang di chuyển đến quán",
        arrived: "🏪 Shipper đã đến quán, chờ lấy hàng",
        picked_up: "🚀 Shipper đang giao đến bạn",
        completed: "🎉 Giao hàng thành công!",
        cancelled: "❌ Đơn hàng đã bị hủy",
    };
    return headlines[status] || "Đang xử lý";
};

// Polling & API
const startOrderPolling = () => {
    if (!refreshInterval.value)
        refreshInterval.value = setInterval(updateOrderData, 5000);
};
const stopOrderPolling = () => {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
        refreshInterval.value = null;
    }
};
const formatPrice = (p) =>
    new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(p);
const formatDate = (date) => new Date(date).toLocaleString("vi-VN");

const updateOrderData = async () => {
    try {
        const response = await fetch(
            `/api/orders/${orderData.value.id}/details`,
            { headers: { Accept: "application/json" }, credentials: "include" },
        );
        if (response.ok) orderData.value = await response.json();
    } catch (e) {
        console.error(e);
    }
};

const confirmDelivery = async () => {
    const result = await Swal.fire({
        title: "Bạn đã nhận đủ món?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#10b981",
        confirmButtonText: "Đúng, đã nhận!",
        cancelButtonText: "Chưa",
        customClass: { popup: "rounded-[2rem]" },
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(
                `/api/orders/${orderData.value.id}/confirm-delivery`,
                {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    credentials: "include",
                },
            );
            if (response.ok)
                Swal.fire(
                    "Thành công!",
                    "Chúc bạn ngon miệng!",
                    "success",
                ).then(() => window.location.reload());
        } catch (e) {
            Swal.fire("Lỗi", "Vui lòng thử lại", "error");
        }
    }
};

const cancelOrder = async () => {
    const result = await Swal.fire({
        title: "Hủy đơn hàng?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        confirmButtonText: "Hủy đơn",
        cancelButtonText: "Quay lại",
    });
    if (result.isConfirmed) {
        try {
            const response = await fetch(
                `/my-orders/${orderData.value.id}/cancel`,
                {
                    method: "DELETE",
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    credentials: "include",
                },
            );
            if (response.ok)
                Swal.fire(
                    "Đã hủy!",
                    "Đơn hàng đã hủy thành công.",
                    "success",
                ).then(() => window.location.reload());
            else {
                const err = await response.json();
                Swal.fire("Thất bại", err.error, "error");
            }
        } catch (e) {
            console.error(e);
        }
    }
};

// Copy to clipboard
const copyToClipboard = async (text, label) => {
    try {
        await navigator.clipboard.writeText(text);
        Swal.fire({
            title: "Đã sao chép!",
            text: `${label} đã được sao chép vào clipboard`,
            icon: "success",
            timer: 2000,
            showConfirmButton: false,
            customClass: { popup: "rounded-[1.5rem]" },
        });
    } catch (err) {
        Swal.fire({
            title: "Lỗi",
            text: "Không thể sao chép, vui lòng thử lại",
            icon: "error",
            customClass: { popup: "rounded-[1.5rem]" },
        });
    }
};

// Map
const initMap = () => {
    if (!orderData.value.shipper?.current_latitude) return;
    if (map.value) map.value.remove();
    map.value = L.map("order-map", {
        zoomControl: false,
        attributionControl: false,
    }).setView(
        [
            orderData.value.shipper.current_latitude,
            orderData.value.shipper.current_longitude,
        ],
        15,
    );
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(
        map.value,
    );

    if (orderData.value.user.latitude) {
        const customerIcon = L.divIcon({
            html: `<div class="flex items-center justify-center w-10 h-10 bg-blue-600 rounded-full shadow-lg border-2 border-white text-xl shadow-blue-500/50 animate-bounce-slow">🏠</div>`,
            className: "",
            iconSize: [40, 40],
            iconAnchor: [20, 40],
            popupAnchor: [0, -40]
        });

        L.marker(
            [orderData.value.user.latitude, orderData.value.user.longitude],
            { icon: customerIcon }
        )
            .addTo(map.value)
            .bindPopup("<b>Bạn</b><br/>Điểm giao hàng");
    }

    const shipperIcon = L.divIcon({
        html: `<div class="relative w-12 h-12">
            <div class="absolute inset-0 bg-orange-500 rounded-full animate-ping opacity-20"></div>
            <div class="relative flex items-center justify-center w-12 h-12 bg-orange-500 rounded-full shadow-xl border-2 border-white text-2xl shadow-orange-500/50 z-10">🛵</div>
        </div>`,
        className: "",
        iconSize: [48, 48],
        iconAnchor: [24, 24],
        popupAnchor: [0, -24]
    });

    shipperMarker.value = L.marker(
        [
            orderData.value.shipper.current_latitude,
            orderData.value.shipper.current_longitude,
        ],
        { icon: shipperIcon }
    )
        .addTo(map.value)
        .bindPopup("<b>Shipper</b><br/>Đang di chuyển");
};

watch(
    () => [
        orderData.value.shipper?.current_latitude,
        orderData.value.shipper?.current_longitude,
    ],
    ([lat, lng]) => {
        if (shipperMarker.value && lat && lng) {
            shipperMarker.value.setLatLng([lat, lng]);
            map.value?.panTo([lat, lng]);
        }
    },
);

watch(showTrackingMap, (v) => {
    if (v) setTimeout(initMap, 500);
});

onMounted(() => {
    if (showTrackingMap.value) setTimeout(initMap, 500);
    // Vẫn giữ fallback polling cho chắc chắn
    if (["shipping", "arrived", "picked_up"].includes(orderData.value.status))
        startOrderPolling();
        
    // Listen to real-time events using Laravel Echo
    if (window.Echo) {
        window.Echo.private(`order.${orderData.value.id}`)
            .listen('OrderStatusUpdated', (e) => {
                console.log('Order status updated via Echo:', e.order.status);
                orderData.value.status = e.order.status;
                if (e.order.status === 'shipping' || e.order.status === 'picked_up') {
                    if (!orderData.value.shipper) {
                        updateOrderData(); // Fetch full order if shipper is missing
                    }
                }
                
                // Show a toast when status changes
                Swal.fire({
                    title: 'Trạng thái cập nhật',
                    text: getStatusHeadline(e.order.status),
                    icon: 'info',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            })
            .listen('ShipperLocationUpdated', (e) => {
                console.log('Shipper location updated:', e);
                if (orderData.value.shipper) {
                    orderData.value.shipper.current_latitude = e.latitude;
                    orderData.value.shipper.current_longitude = e.longitude;
                }
            });
    }
});

onUnmounted(() => {
    stopOrderPolling();
    if (map.value) map.value.remove();
    
    // Ngắt kết nối Echo khi component unmount
    if (window.Echo) {
        window.Echo.leave(`order.${orderData.value.id}`);
    }
});

const canCancelOrder = computed(() => {
    return ![
        "shipping",
        "arrived",
        "picked_up",
        "delivering",
        "completed",
        "cancelled",
    ].includes(orderData.value.status);
});
</script>

<style scoped>
:deep(.leaflet-container) {
    border-radius: 0;
    font-family: inherit;
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@keyframes fade-in {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slide-up {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes zoom-in {
    from {
        transform: scale(0.95);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-in-out forwards;
}

.animate-slide-up {
    animation: slide-up 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.animate-zoom-in {
    animation: zoom-in 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes bounce-slow {
    0%, 100% {
        transform: translateY(-5%);
        animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
    }
    50% {
        transform: translateY(0);
        animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
    }
}

.animate-bounce-slow {
    animation: bounce-slow 2s infinite;
}
</style>
