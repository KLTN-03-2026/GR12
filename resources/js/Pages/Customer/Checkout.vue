<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import { computed } from "vue";

defineOptions({ layout: GuestLayout });

const props = defineProps({
    cartItems: Array,
    user: Object,
});

// useForm hỗ trợ quản lý lỗi (errors) từ Backend trả về
const form = useForm({
    address: props.user?.address || "",
    phone: props.user?.phone || "",
    note: "",
    payment_method: "cash",
});

const subtotal = computed(() => {
    if (!props.cartItems) return 0;
    return props.cartItems.reduce(
        (sum, item) => sum + parseFloat(item.product.price) * item.quantity,
        0,
    );
});

const shippingFee = 15000;
const total = computed(() => subtotal.value + shippingFee);

const formatPrice = (p) =>
    new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(p);

const submitOrder = () => {
    form.post(route("orders.store"), {
        preserveScroll: true,
        onSuccess: () => {
            console.log("Đặt hàng thành công!");
        },
    });
};
</script>

<template>
    <Head title="Thanh toán - FoodXpress" />

    <div class="min-h-screen bg-[#f8f9fb] py-12">
        <div class="max-w-6xl mx-auto px-4">
            <div v-if="cartItems && cartItems.length > 0">
                <h1
                    class="text-4xl font-black text-gray-900 mb-10 tracking-tighter italic"
                >
                    Xác nhận đơn hàng 🚀
                </h1>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        <div
                            class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100"
                        >
                            <h3
                                class="text-xl font-black text-gray-800 mb-6 flex items-center gap-2"
                            >
                                <span
                                    class="w-8 h-8 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center text-sm"
                                    >01</span
                                >
                                Thông tin nhận hàng
                            </h3>

                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label
                                        class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4 mb-2 block"
                                        >Địa chỉ giao hàng *</label
                                    >
                                    <input
                                        v-model="form.address"
                                        type="text"
                                        placeholder="Số nhà, tên đường, phường..."
                                        :class="{
                                            'ring-2 ring-red-500 bg-red-50':
                                                form.errors.address,
                                        }"
                                        class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-4 focus:ring-orange-100 transition-all font-bold text-gray-800"
                                    />
                                    <p
                                        v-if="form.errors.address"
                                        class="text-red-500 text-xs mt-2 ml-4 font-bold"
                                    >
                                        {{ form.errors.address }}
                                    </p>
                                </div>

                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                >
                                    <div>
                                        <label
                                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4 mb-2 block"
                                            >Số điện thoại *</label
                                        >
                                        <input
                                            v-model="form.phone"
                                            type="text"
                                            :class="{
                                                'ring-2 ring-red-500 bg-red-50':
                                                    form.errors.phone,
                                            }"
                                            class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-4 focus:ring-orange-100 transition-all font-bold text-gray-800"
                                        />
                                        <p
                                            v-if="form.errors.phone"
                                            class="text-red-500 text-xs mt-2 ml-4 font-bold"
                                        >
                                            {{ form.errors.phone }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4 mb-2 block"
                                            >Ghi chú thêm</label
                                        >
                                        <input
                                            v-model="form.note"
                                            type="text"
                                            placeholder="Ví dụ: Cổng sau, đừng bấm chuông..."
                                            class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-4 focus:ring-orange-100 transition-all font-bold text-gray-800"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100"
                        >
                            <h3
                                class="text-xl font-black text-gray-800 mb-6 flex items-center gap-2"
                            >
                                <span
                                    class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-sm"
                                    >02</span
                                >
                                Phương thức thanh toán
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div
                                    @click="form.payment_method = 'cash'"
                                    :class="
                                        form.payment_method === 'cash'
                                            ? 'border-orange-500 bg-orange-50/50'
                                            : 'border-gray-100'
                                    "
                                    class="cursor-pointer border-2 p-4 rounded-2xl flex items-center gap-4 transition-all group"
                                >
                                    <div
                                        class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-xl"
                                    >
                                        💵
                                    </div>
                                    <span
                                        class="font-black text-sm text-gray-700 uppercase tracking-tight"
                                        >Tiền mặt</span
                                    >
                                </div>
                                <div
                                    class="cursor-not-allowed border-2 border-gray-50 p-4 rounded-2xl flex items-center gap-4 opacity-40"
                                >
                                    <div
                                        class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-xl"
                                    >
                                        💳
                                    </div>
                                    <span
                                        class="font-black text-sm text-gray-700 uppercase tracking-tight"
                                        >Ví MoMo (Sắp có)</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div
                            class="bg-gray-900 rounded-[2.5rem] p-8 text-white sticky top-24 shadow-2xl"
                        >
                            <h3
                                class="text-xl font-black mb-8 italic uppercase text-orange-500"
                            >
                                Tóm tắt đơn hàng
                            </h3>

                            <div
                                class="space-y-6 mb-8 max-h-[300px] overflow-y-auto no-scrollbar"
                            >
                                <div
                                    v-for="item in cartItems"
                                    :key="item.id"
                                    class="flex items-center gap-4 border-b border-white/5 pb-4"
                                >
                                    <div
                                        class="w-12 h-12 rounded-xl overflow-hidden shrink-0 border border-white/10"
                                    >
                                        <img
                                            :src="
                                                '/storage/' + item.product.image
                                            "
                                            class="w-full h-full object-cover"
                                        />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="font-bold text-sm line-clamp-1 text-gray-200"
                                        >
                                            {{ item.product.name }}
                                        </p>
                                        <p
                                            class="text-[10px] text-gray-500 font-black"
                                        >
                                            SL: x{{ item.quantity }}
                                        </p>
                                    </div>
                                    <p
                                        class="font-black text-sm text-orange-400"
                                    >
                                        {{
                                            formatPrice(
                                                item.product.price *
                                                    item.quantity,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="border-t border-white/10 pt-6 space-y-4"
                            >
                                <div class="flex justify-between text-sm">
                                    <span
                                        class="text-gray-500 font-bold uppercase tracking-widest text-[10px]"
                                        >Tạm tính</span
                                    >
                                    <span class="font-bold text-gray-300">{{
                                        formatPrice(subtotal)
                                    }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span
                                        class="text-gray-500 font-bold uppercase tracking-widest text-[10px]"
                                        >Phí ship</span
                                    >
                                    <span class="font-bold text-gray-300">{{
                                        formatPrice(shippingFee)
                                    }}</span>
                                </div>
                                <div
                                    class="flex justify-between items-end pt-4 border-t border-white/5"
                                >
                                    <span
                                        class="text-orange-500 font-black uppercase tracking-tighter text-lg italic"
                                        >Tổng cộng</span
                                    >
                                    <span
                                        class="text-3xl font-black tracking-tighter text-white"
                                        >{{ formatPrice(total) }}</span
                                    >
                                </div>
                            </div>

                            <button
                                @click="submitOrder"
                                :disabled="form.processing"
                                class="w-full mt-10 bg-orange-500 hover:bg-orange-600 disabled:bg-gray-700 text-white font-black py-5 rounded-2xl shadow-xl shadow-orange-500/20 transition-all active:scale-95 uppercase tracking-widest text-sm"
                            >
                                {{
                                    form.processing
                                        ? "Đang xử lý..."
                                        : "Đặt hàng ngay 🛵"
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="max-w-md mx-auto text-center py-20 bg-white rounded-[3rem] shadow-sm border border-gray-100"
            >
                <div class="text-6xl mb-6">🎉</div>
                <h2 class="text-2xl font-black text-gray-800 uppercase italic">
                    Giỏ hàng trống!
                </h2>
                <p class="text-gray-500 mt-2">
                    Đơn hàng của bạn đã được tiếp nhận hoặc giỏ hàng chưa có món
                    nào.
                </p>
                <Link
                    :href="route('home')"
                    class="mt-8 inline-block bg-orange-500 text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest hover:shadow-lg transition-all"
                >
                    Quay về trang chủ
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
* {
    font-family: "Inter", sans-serif;
}
</style>
