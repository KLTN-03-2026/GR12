<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";

const props = defineProps({
    cartItems: Array,
    settings: Object,
});

defineOptions({ layout: GuestLayout });

// Ref cho dialog xác nhận
const confirmDialog = ref(null);
const itemToRemove = ref(null);

// Cập nhật số lượng qua Server
const updateQty = (id, newQty) => {
    if (newQty < 1) return;
    router.patch(
        route("cart.update", id),
        { quantity: newQty },
        {
            preserveScroll: true,
        },
    );
};

// Xóa món khỏi giỏ hàng
const removeItem = (id) => {
    itemToRemove.value = id;
    confirmDialog.value.open();
};

// Thực hiện xóa sau khi xác nhận
const doRemove = () => {
    if (itemToRemove.value) {
        router.delete(route("cart.destroy", itemToRemove.value), {
            preserveScroll: true,
            onSuccess: () => {
                confirmDialog.value.close();
                itemToRemove.value = null;
            },
        });
    }
};

// Hàm định dạng tiền tệ
const formatPrice = (p) =>
    new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(p);

// Tính tổng tiền tạm tính
const subtotal = computed(() => props.cartItems.reduce((acc, item) => acc + item.product.price * item.quantity, 0));
const shippingFee = computed(() => Number(props.settings?.base_shipping_fee) || 15000); // Phí ship cơ bản từ settings
const totalPrice = computed(() => subtotal.value + shippingFee.value);
</script>

<template>
    <Head title="Giỏ hàng của tôi" />
    <div class="max-w-6xl mx-auto py-12 px-4 sm:px-6 lg:px-8 min-h-screen bg-[#F8FAFC]">
        <div class="flex items-baseline justify-between mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Giỏ hàng</h1>
            <span class="text-sm font-medium text-slate-500">{{ cartItems.length }} món ăn</span>
        </div>

        <div v-if="cartItems.length > 0" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Danh sách món ăn -->
            <div class="lg:col-span-8 space-y-4">
                <div v-for="item in cartItems" :key="item.id" class="group bg-white rounded-3xl p-4 flex gap-5 items-center shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300">
                    <div class="relative w-28 h-28 shrink-0 rounded-2xl overflow-hidden bg-slate-100">
                        <img :src="'/storage/' + item.product.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                    </div>

                    <div class="flex-1 flex flex-col justify-between py-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-slate-800 text-lg mb-1">{{ item.product.name }}</h3>
                                <p class="text-xs font-semibold text-slate-500 flex items-center gap-1">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    {{ item.product.user?.name }}
                                </p>
                            </div>
                            <button @click="removeItem(item.id)" class="text-slate-300 hover:text-red-500 transition-colors p-2 rounded-full hover:bg-red-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <span class="font-extrabold text-slate-900 text-lg">
                                {{ formatPrice(item.product.price * item.quantity) }}
                            </span>
                            <div class="flex items-center bg-slate-50 rounded-full border border-slate-200 p-1">
                                <button @click="updateQty(item.id, item.quantity - 1)" class="w-8 h-8 flex items-center justify-center text-slate-600 hover:bg-white rounded-full transition-colors hover:shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                                </button>
                                <span class="w-8 text-center font-bold text-sm text-slate-800">{{ item.quantity }}</span>
                                <button @click="updateQty(item.id, item.quantity + 1)" class="w-8 h-8 flex items-center justify-center text-slate-600 hover:bg-white rounded-full transition-colors hover:shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tóm tắt đơn hàng -->
            <div class="lg:col-span-4">
                <div class="bg-white p-6 sm:p-8 rounded-[2rem] border border-slate-100 shadow-sm sticky top-8">
                    <h2 class="text-xl font-bold text-slate-800 mb-6">Tóm tắt đơn hàng</h2>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-medium">Tạm tính</span>
                            <span class="font-semibold text-slate-800">{{ formatPrice(subtotal) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-medium">Phí giao hàng <span class="text-[10px] italic">(ước tính)</span></span>
                            <span class="font-semibold text-slate-800">{{ formatPrice(shippingFee) }}</span>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 my-6"></div>

                    <div class="flex justify-between items-end mb-8">
                        <span class="font-bold text-slate-800">Tổng cộng</span>
                        <div class="text-right">
                            <span class="block text-3xl font-black text-indigo-600">{{ formatPrice(totalPrice) }}</span>
                            <span class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Đã bao gồm VAT</span>
                        </div>
                    </div>

                    <Link :href="route('checkout')" class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-bold rounded-2xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all shadow-lg hover:shadow-indigo-500/30">
                        Tiếp tục đặt hàng
                        <svg class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </Link>

                    <div class="mt-6 flex items-center justify-center gap-4 opacity-60">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-24 bg-white rounded-[3rem] shadow-sm border border-slate-100 max-w-3xl mx-auto mt-10">
            <div class="w-48 h-48 mx-auto bg-slate-50 rounded-full flex items-center justify-center mb-8">
                <svg class="w-24 h-24 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
            </div>
            <h3 class="text-2xl font-bold text-slate-800 mb-3">Giỏ hàng của bạn đang trống</h3>
            <p class="text-slate-500 font-medium mb-10">Chưa có món ăn nào trong giỏ hàng của bạn.<br>Hãy lướt qua menu để tìm những món ngon nhé!</p>
            <Link :href="route('home')" class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-sm font-bold rounded-2xl text-white bg-slate-900 hover:bg-slate-800 transition-colors shadow-lg hover:shadow-xl">
                Khám phá Menu ngay
                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </Link>
        </div>
    </div>

    <!-- Dialog xác nhận xóa -->
    <ConfirmDialog
        ref="confirmDialog"
        title="Xác nhận xóa"
        message="Bạn có chắc chắn muốn xóa món này khỏi giỏ hàng?"
        icon="🗑️"
        confirm-text="Xóa"
        cancel-text="Hủy"
        @confirm="doRemove"
    />
</template>

<style scoped>
/* Đồng bộ font Inter cho sạch sẽ */
* {
    font-family: "Inter", sans-serif;
}
</style>
