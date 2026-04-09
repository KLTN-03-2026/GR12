<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    cartItems: Array,
});

defineOptions({ layout: GuestLayout });

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
    if (confirm("Xóa món này khỏi giỏ hàng?")) {
        router.delete(route("cart.destroy", id), {
            preserveScroll: true,
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
const subtotal = computed(() => {
    return props.cartItems.reduce((acc, item) => {
        return acc + item.product.price * item.quantity;
    }, 0);
});

// Giả sử phí ship (có thể điều chỉnh logic sau)
const shippingFee = 0;
const totalPrice = computed(() => subtotal.value + shippingFee);
</script>

<template>
    <Head title="Giỏ hàng của tôi" />
    <div class="max-w-5xl mx-auto py-12 px-4 min-h-screen bg-[#f8f9fb]">
        <h1
            class="text-4xl font-black mb-10 text-gray-900 tracking-tighter italic"
        >
            Giỏ hàng của bạn 🛒
        </h1>

        <div
            v-if="cartItems.length > 0"
            class="grid grid-cols-1 lg:grid-cols-3 gap-10"
        >
            <div class="lg:col-span-2 space-y-4">
                <div
                    v-for="item in cartItems"
                    :key="item.id"
                    class="bg-white p-5 rounded-[2rem] border border-gray-100 flex gap-6 items-center shadow-sm hover:shadow-md transition-shadow group"
                >
                    <div
                        class="relative w-24 h-24 shrink-0 rounded-2xl overflow-hidden shadow-lg"
                    >
                        <img
                            :src="'/storage/' + item.product.image"
                            class="w-full h-full object-cover group-hover:scale-110 transition duration-500"
                        />
                    </div>

                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3
                                    class="font-black text-gray-800 text-lg leading-tight"
                                >
                                    {{ item.product.name }}
                                </h3>
                                <p
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1"
                                >
                                    🏠 {{ item.product.user?.name }}
                                </p>
                            </div>
                            <button
                                @click="removeItem(item.id)"
                                class="text-gray-300 hover:text-red-500 transition-colors p-1"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        stroke-width="2"
                                    />
                                </svg>
                            </button>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <div
                                class="flex items-center bg-gray-50 rounded-xl p-1 border border-gray-100"
                            >
                                <button
                                    @click="
                                        updateQty(item.id, item.quantity - 1)
                                    "
                                    class="w-8 h-8 flex items-center justify-center text-orange-500 font-black hover:bg-white rounded-lg transition"
                                >
                                    -
                                </button>
                                <span
                                    class="px-4 font-black text-sm text-gray-700"
                                    >{{ item.quantity }}</span
                                >
                                <button
                                    @click="
                                        updateQty(item.id, item.quantity + 1)
                                    "
                                    class="w-8 h-8 flex items-center justify-center text-orange-500 font-black hover:bg-white rounded-lg transition"
                                >
                                    +
                                </button>
                            </div>
                            <span
                                class="font-black text-orange-600 text-lg tracking-tight"
                            >
                                {{
                                    formatPrice(
                                        item.product.price * item.quantity,
                                    )
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div
                    class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-xl sticky top-6"
                >
                    <h2
                        class="text-xl font-black mb-8 italic uppercase tracking-tighter text-gray-800"
                    >
                        Tóm tắt đơn hàng
                    </h2>

                    <div
                        class="space-y-4 border-b border-dashed border-gray-100 pb-8"
                    >
                        <div class="flex justify-between text-sm">
                            <span
                                class="text-gray-400 font-bold uppercase tracking-widest text-[10px]"
                                >Tạm tính:</span
                            >
                            <span class="font-black text-gray-700">{{
                                formatPrice(subtotal)
                            }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span
                                class="text-gray-400 font-bold uppercase tracking-widest text-[10px]"
                                >Phí giao hàng:</span
                            >
                            <span
                                class="font-black text-green-500 uppercase text-[10px]"
                                >Miễn phí</span
                            >
                        </div>
                    </div>

                    <div class="flex justify-between items-end py-8">
                        <span class="font-black text-gray-800 uppercase italic"
                            >Tổng cộng:</span
                        >
                        <span
                            class="text-3xl font-black text-orange-600 tracking-tighter"
                            >{{ formatPrice(totalPrice) }}</span
                        >
                    </div>

                    <Link
                        :href="route('checkout')"
                        class="block w-full bg-orange-500 text-white text-center py-5 rounded-[1.5rem] font-black hover:bg-orange-600 transition shadow-xl shadow-orange-100 active:scale-95 uppercase tracking-widest text-sm"
                    >
                        Tiếp tục đặt hàng 🛵
                    </Link>

                    <p
                        class="text-[9px] text-center text-gray-400 mt-6 font-bold uppercase tracking-widest"
                    >
                        An toàn • Nhanh chóng • Đảm bảo
                    </p>
                </div>
            </div>
        </div>

        <div
            v-else
            class="text-center py-32 bg-white rounded-[3rem] shadow-sm border border-dashed border-gray-200"
        >
            <div class="text-8xl mb-6 animate-bounce">🛒</div>
            <h3 class="text-2xl font-black text-gray-300 uppercase italic">
                Giỏ hàng của bạn đang trống!
            </h3>
            <p class="text-gray-400 mt-2 font-medium">
                Bụng đói rồi, đi tìm món gì ngon thôi nào.
            </p>
            <Link
                :href="route('home')"
                class="mt-8 inline-block bg-gray-900 text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-orange-600 transition shadow-xl"
            >
                Khám phá Menu ngay
            </Link>
        </div>
    </div>
</template>

<style scoped>
/* Đồng bộ font Inter cho sạch sẽ */
* {
    font-family: "Inter", sans-serif;
}
</style>
