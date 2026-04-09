<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    product: Object,
    show: Boolean,
});

const emit = defineEmits(["close", "addToCart"]);

// 1. Quản lý trạng thái
const selectedOptions = ref([]);
const quantity = ref(1);
const note = ref(""); // Thêm trường ghi chú nếu khách muốn dặn dò

// 2. Tính tổng tiền (Giá gốc + Toppings) * Số lượng
const totalPrice = computed(() => {
    const extra = selectedOptions.value.reduce(
        (sum, opt) => sum + parseFloat(opt.additional_price || 0),
        0,
    );
    return (parseFloat(props.product.price || 0) + extra) * quantity.value;
});

// 3. Logic chọn/bỏ chọn Topping
const toggleOption = (option) => {
    const index = selectedOptions.value.findIndex((o) => o.id === option.id);
    if (index > -1) {
        selectedOptions.value.splice(index, 1);
    } else {
        selectedOptions.value.push(option);
    }
};

// 4. Gửi dữ liệu ra ngoài Welcome.vue
const handleAddToCart = () => {
    emit("addToCart", {
        product: props.product,
        options: selectedOptions.value, // Gửi nguyên mảng object toppings đã chọn
        quantity: quantity.value,
        total: totalPrice.value,
        note: note.value,
    });

    // Reset lại trạng thái sau khi thêm thành công để lần sau mở món khác không bị dính dữ liệu cũ
    // quantity.value = 1;
    // selectedOptions.value = [];

    emit("close");
};
</script>

<template>
    <Transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 z-[200] flex items-end md:items-center justify-center p-0 md:p-4 bg-black/60 backdrop-blur-sm"
            @click.self="$emit('close')"
        >
            <div
                class="bg-white w-full md:max-w-2xl max-h-[95vh] overflow-y-auto rounded-t-[3rem] md:rounded-[3rem] shadow-2xl relative animate-slide-up"
            >
                <button
                    @click="$emit('close')"
                    class="absolute top-6 right-6 z-10 bg-white/80 backdrop-blur p-2 rounded-full shadow-lg hover:rotate-90 transition-all duration-300"
                >
                    <svg
                        class="w-5 h-5 text-gray-800"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="3"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>

                <div class="relative h-72 md:h-96 bg-gray-100">
                    <img
                        :src="'/storage/' + product.image"
                        class="w-full h-full object-cover"
                        :alt="product.name"
                    />
                    <div
                        v-if="product.gallery?.length"
                        class="absolute bottom-6 left-6 bg-black/50 px-3 py-1 rounded-full text-[10px] text-white font-bold tracking-widest uppercase shadow-lg"
                    >
                        📸 + {{ product.gallery.length }} ảnh chi tiết
                    </div>
                </div>

                <div class="p-8 space-y-6 pb-32">
                    <div class="flex justify-between items-start">
                        <div class="space-y-1">
                            <span
                                class="text-[10px] font-black text-orange-500 uppercase tracking-[0.2em] bg-orange-50 px-2 py-0.5 rounded"
                            >
                                {{ product.category?.name || "Món ăn" }}
                            </span>
                            <h2
                                class="text-3xl font-black text-gray-900 leading-tight"
                            >
                                {{ product.name }}
                            </h2>
                        </div>
                        <div class="text-right">
                            <p
                                class="text-2xl font-black text-gray-900 drop-shadow-sm"
                            >
                                {{ Number(product.price).toLocaleString() }}đ
                            </p>
                        </div>
                    </div>

                    <p
                        class="text-sm text-gray-500 leading-relaxed italic border-l-4 border-orange-200 pl-4 bg-gray-50 py-2 rounded-r-xl"
                    >
                        {{
                            product.description ||
                            "Hương vị tuyệt vời đang chờ bạn khám phá."
                        }}
                    </p>

                    <div v-if="product.options?.length" class="space-y-4 pt-4">
                        <label
                            class="text-xs font-black text-gray-800 uppercase tracking-widest italic flex items-center gap-2"
                        >
                            <span>Tùy chọn thêm</span>
                            <span class="h-px flex-1 bg-gray-100"></span>
                        </label>

                        <div class="grid grid-cols-1 gap-3">
                            <div
                                v-for="opt in product.options"
                                :key="opt.id"
                                @click="toggleOption(opt)"
                                :class="
                                    selectedOptions.find((o) => o.id === opt.id)
                                        ? 'border-orange-500 bg-orange-50 shadow-md translate-x-1'
                                        : 'border-gray-100 bg-gray-50 hover:bg-white hover:border-orange-200'
                                "
                                class="flex items-center gap-4 p-4 rounded-[1.5rem] border-2 cursor-pointer transition-all duration-300 group"
                            >
                                <div
                                    class="w-14 h-14 rounded-2xl overflow-hidden bg-white border border-gray-100 shadow-sm transition-transform group-hover:scale-105"
                                >
                                    <img
                                        :src="'/storage/' + opt.image"
                                        v-if="opt.image"
                                        class="w-full h-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="w-full h-full flex items-center justify-center text-[10px] font-black text-gray-300"
                                    >
                                        😋
                                    </div>
                                </div>

                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-800">
                                        {{ opt.option_value }}
                                    </p>
                                    <p
                                        class="text-[10px] text-orange-500 font-black"
                                    >
                                        +{{
                                            Number(
                                                opt.additional_price,
                                            ).toLocaleString()
                                        }}đ
                                    </p>
                                </div>

                                <div
                                    :class="
                                        selectedOptions.find(
                                            (o) => o.id === opt.id,
                                        )
                                            ? 'bg-orange-500 scale-110'
                                            : 'bg-white border-2 border-gray-200'
                                    "
                                    class="w-6 h-6 rounded-full flex items-center justify-center transition-all duration-300"
                                >
                                    <svg
                                        v-if="
                                            selectedOptions.find(
                                                (o) => o.id === opt.id,
                                            )
                                        "
                                        class="w-4 h-4 text-white"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="4"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label
                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest"
                            >Ghi chú cho quán</label
                        >
                        <input
                            v-model="note"
                            type="text"
                            placeholder="Ví dụ: Không hành, nhiều cay..."
                            class="w-full border-none bg-gray-50 rounded-2xl p-4 text-sm focus:ring-orange-500"
                        />
                    </div>
                </div>

                <div
                    class="fixed bottom-0 left-0 right-0 md:absolute bg-white/90 backdrop-blur-xl p-6 border-t border-gray-100 flex items-center justify-between gap-6 shadow-[0_-10px_30px_rgba(0,0,0,0.05)]"
                >
                    <div
                        class="flex items-center bg-gray-900 text-white rounded-[1.5rem] p-1.5 font-black shadow-lg"
                    >
                        <button
                            @click="quantity > 1 ? quantity-- : null"
                            class="w-10 h-10 flex items-center justify-center hover:bg-white/10 rounded-xl transition-all"
                        >
                            <svg
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="3"
                                    d="M20 12H4"
                                />
                            </svg>
                        </button>
                        <span class="w-8 text-center text-lg">{{
                            quantity
                        }}</span>
                        <button
                            @click="quantity++"
                            class="w-10 h-10 flex items-center justify-center hover:bg-white/10 rounded-xl transition-all"
                        >
                            <svg
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="3"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                        </button>
                    </div>

                    <button
                        @click="handleAddToCart"
                        class="flex-1 bg-orange-500 text-white h-14 rounded-2xl font-black text-sm flex items-center justify-between px-8 shadow-xl shadow-orange-100 hover:bg-orange-600 transition-all active:scale-95 group"
                    >
                        <span
                            class="tracking-widest group-hover:translate-x-1 transition-transform uppercase"
                            >Thêm vào giỏ</span
                        >
                        <span class="text-lg bg-white/20 px-3 py-1 rounded-xl"
                            >{{ totalPrice.toLocaleString() }}đ</span
                        >
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.animate-slide-up {
    animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes slideUp {
    from {
        transform: translateY(100%);
        opacity: 0.5;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Tùy chỉnh thanh cuộn cho Modal */
::-webkit-scrollbar {
    width: 4px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #f1f1f1;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #e5e5e5;
}
</style>
