<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    product: Object,
    show: Boolean,
});

const emit = defineEmits(["close", "addToCart"]);

// Xử lý chọn Topping
const selectedOptions = ref([]);
const quantity = ref(1);

const totalPrice = computed(() => {
    let extra = selectedOptions.value.reduce(
        (sum, opt) => sum + parseFloat(opt.additional_price),
        0,
    );
    return (parseFloat(props.product.price) + extra) * quantity.value;
});

const toggleOption = (option) => {
    const index = selectedOptions.value.findIndex((o) => o.id === option.id);
    if (index > -1) {
        selectedOptions.value.splice(index, 1);
    } else {
        selectedOptions.value.push(option);
    }
};

const handleAddToCart = () => {
    emit("addToCart", {
        product: props.product,
        options: selectedOptions.value,
        quantity: quantity.value,
        total: totalPrice.value,
    });
    emit("close");
};
</script>

<template>
    <Transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 z-[200] flex items-end md:items-center justify-center p-0 md:p-4 bg-black/60 backdrop-blur-sm"
        >
            <div
                class="bg-white w-full md:max-w-2xl max-h-[90vh] overflow-y-auto rounded-t-[3rem] md:rounded-[3rem] shadow-2xl relative animate-slide-up"
            >
                <button
                    @click="$emit('close')"
                    class="absolute top-6 right-6 z-10 bg-white/80 backdrop-blur p-2 rounded-full shadow-lg"
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
                    />
                    <div
                        v-if="product.gallery?.length"
                        class="absolute bottom-6 left-6 bg-black/50 px-3 py-1 rounded-full text-[10px] text-white font-bold tracking-widest"
                    >
                        + {{ product.gallery.length }} ẢNH CHI TIẾT
                    </div>
                </div>

                <div class="p-8 space-y-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <span
                                class="text-[10px] font-black text-orange-500 uppercase tracking-[0.2em]"
                                >{{ product.category?.name }}</span
                            >
                            <h2
                                class="text-2xl font-black text-gray-900 leading-tight"
                            >
                                {{ product.name }}
                            </h2>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-black text-gray-900">
                                {{ Number(product.price).toLocaleString() }}đ
                            </p>
                        </div>
                    </div>

                    <p class="text-sm text-gray-500 leading-relaxed italic">
                        {{
                            product.description ||
                            "Không có mô tả cho món ăn này."
                        }}
                    </p>

                    <div
                        v-if="product.options?.length"
                        class="space-y-4 pt-4 border-t border-dashed border-gray-100"
                    >
                        <label
                            class="text-xs font-black text-gray-800 uppercase tracking-widest italic flex items-center gap-2"
                        >
                            <span>Topping & Tùy chọn</span>
                            <span class="h-px flex-1 bg-gray-100"></span>
                        </label>

                        <div class="grid grid-cols-1 gap-3">
                            <div
                                v-for="opt in product.options"
                                :key="opt.id"
                                @click="toggleOption(opt)"
                                :class="
                                    selectedOptions.find((o) => o.id === opt.id)
                                        ? 'border-orange-500 bg-orange-50'
                                        : 'border-gray-100 bg-gray-50'
                                "
                                class="flex items-center gap-4 p-4 rounded-[1.5rem] border-2 cursor-pointer transition-all hover:scale-[1.02]"
                            >
                                <div
                                    class="w-12 h-12 rounded-xl overflow-hidden bg-white border border-gray-100"
                                >
                                    <img
                                        :src="'/storage/' + opt.image"
                                        v-if="opt.image"
                                        class="w-full h-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="w-full h-full flex items-center justify-center text-[8px] font-bold text-gray-300"
                                    >
                                        N/A
                                    </div>
                                </div>

                                <div class="flex-1">
                                    <p class="text-xs font-bold text-gray-800">
                                        {{ opt.option_value }}
                                    </p>
                                    <p
                                        class="text-[10px] text-gray-400 font-medium"
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
                                            ? 'bg-orange-500'
                                            : 'bg-white border-2 border-gray-200'
                                    "
                                    class="w-6 h-6 rounded-full flex items-center justify-center transition-colors"
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
                                            stroke-width="3"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="sticky bottom-0 bg-white/80 backdrop-blur-xl p-8 border-t border-gray-100 flex items-center justify-between gap-6"
                >
                    <div
                        class="flex items-center bg-gray-100 rounded-2xl p-2 font-black"
                    >
                        <button
                            @click="quantity > 1 ? quantity-- : null"
                            class="w-10 h-10 flex items-center justify-center hover:text-orange-600 transition"
                        >
                            -
                        </button>
                        <span class="w-10 text-center">{{ quantity }}</span>
                        <button
                            @click="quantity++"
                            class="w-10 h-10 flex items-center justify-center hover:text-orange-600 transition"
                        >
                            +
                        </button>
                    </div>

                    <button
                        @click="handleAddToCart"
                        class="flex-1 bg-gray-900 text-white h-14 rounded-2xl font-black text-sm flex items-center justify-between px-6 shadow-xl shadow-gray-200 hover:bg-orange-600 transition-all active:scale-95"
                    >
                        <span>THÊM VÀO GIỎ</span>
                        <span>{{ totalPrice.toLocaleString() }}đ</span>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.animate-slide-up {
    animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes slideUp {
    from {
        transform: translateY(100%);
    }
    to {
        transform: translateY(0);
    }
}
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
