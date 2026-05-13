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
const note = ref("");
const showSidebar = ref(false);

// 2. Tính tổng tiền
const totalPrice = computed(() => {
    const extra = selectedOptions.value.reduce(
        (sum, opt) => sum + parseFloat(opt.additional_price || 0),
        0,
    );
    return (parseFloat(props.product.price || 0) + extra) * quantity.value;
});

// 3. Logic chọn Topping
const toggleOption = (option) => {
    const index = selectedOptions.value.findIndex((o) => o.id === option.id);
    if (index > -1) {
        selectedOptions.value.splice(index, 1);
    } else {
        selectedOptions.value.push(option);
    }
};

// 4. Submit
const handleAddToCart = () => {
    emit("addToCart", {
        product_id: props.product.id,
        options: selectedOptions.value.map((opt) => opt.id),
        quantity: quantity.value,
        note: note.value,
    });
    emit("close");
};
</script>

<template>
    <Transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 z-[200] flex items-end md:items-center justify-center p-0 md:p-4 bg-slate-900/40 backdrop-blur-sm"
            @click.self="$emit('close')"
        >
            <div
                class="bg-white w-full md:max-w-lg md:rounded-[3rem] rounded-t-[2.5rem] shadow-2xl relative animate-slide-up flex flex-col max-h-[90vh] md:max-h-[85vh] overflow-hidden"
            >
                <!-- Close Button -->
                <button
                    @click="$emit('close')"
                    class="absolute top-4 right-4 z-20 w-10 h-10 flex items-center justify-center bg-white/20 backdrop-blur-md rounded-full shadow-sm text-white hover:bg-white/40 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <!-- Scrollable Content -->
                <div class="overflow-y-auto no-scrollbar flex-1 relative">
                    <!-- Image Header -->
                    <div class="relative h-64 md:h-80 w-full bg-slate-100 shrink-0">
                        <img
                            :src="'/storage/' + product.image"
                            class="w-full h-full object-cover"
                            :alt="product.name"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/20"></div>
                        <div v-if="product.category" class="absolute top-4 left-4">
                            <span class="bg-orange-500 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full shadow-md">
                                {{ product.category.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="px-6 md:px-8 py-6 bg-white relative -mt-6 rounded-t-[2.5rem]">
                        <div class="flex items-start justify-between gap-4 mb-6">
                            <div>
                                <h2 class="text-3xl font-black text-slate-900 leading-tight tracking-tight mb-2">
                                    {{ product.name }}
                                </h2>
                                <p class="text-sm text-slate-500 leading-relaxed font-medium">
                                    {{ product.description || 'Hương vị tuyệt vời đang chờ bạn khám phá. Đặt ngay để thưởng thức!' }}
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="bg-slate-900 text-white px-4 py-2.5 rounded-[1.25rem] shadow-lg">
                                    <p class="text-[9px] uppercase tracking-widest text-slate-400 font-bold mb-0.5">Giá gốc</p>
                                    <p class="text-xl font-black">{{ Number(product.price).toLocaleString() }}đ</p>
                                </div>
                            </div>
                        </div>

                        <!-- Options / Toppings -->
                        <div v-if="product.options?.length" class="mb-8">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-black text-slate-900">Thêm Tùy Chọn</h3>
                                <span class="text-xs font-bold text-orange-500 uppercase tracking-wider bg-orange-50 px-2 py-1 rounded-md">Tùy ý</span>
                            </div>
                            <div class="space-y-3">
                                <label
                                    v-for="opt in product.options"
                                    :key="opt.id"
                                    class="flex items-center justify-between p-4 rounded-2xl border-2 transition-all cursor-pointer"
                                    :class="selectedOptions.find((o) => o.id === opt.id) ? 'border-orange-500 bg-orange-50/50' : 'border-slate-100 hover:border-slate-200 bg-white'"
                                    @click.prevent="toggleOption(opt)"
                                >
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-xl bg-slate-100 overflow-hidden shrink-0">
                                            <img v-if="opt.image" :src="'/storage/' + opt.image" class="w-full h-full object-cover" />
                                            <div v-else class="w-full h-full flex items-center justify-center text-slate-300 text-xl">✨</div>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ opt.option_value }}</p>
                                            <p class="text-xs font-black text-orange-500">+{{ Number(opt.additional_price).toLocaleString() }}đ</p>
                                        </div>
                                    </div>
                                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors shrink-0"
                                        :class="selectedOptions.find((o) => o.id === opt.id) ? 'border-orange-500 bg-orange-500' : 'border-slate-300'">
                                        <svg v-if="selectedOptions.find((o) => o.id === opt.id)" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="mb-4">
                            <h3 class="text-sm font-black text-slate-900 mb-3">Ghi Chú Cho Quán</h3>
                            <textarea
                                v-model="note"
                                rows="2"
                                placeholder="Ví dụ: Không hành, nhiều cay, cho thêm đũa..."
                                class="w-full rounded-2xl border-2 border-slate-100 bg-slate-50 p-4 text-sm font-medium text-slate-700 focus:border-orange-500 focus:ring-0 transition-colors resize-none"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Bottom Action Bar -->
                <div class="bg-white p-4 md:px-6 md:py-5 border-t border-slate-100 shrink-0">
                    <div class="flex items-center gap-3">
                        <!-- Quantity Selector -->
                        <div class="flex items-center bg-slate-900 text-white rounded-[1.5rem] p-1.5 shadow-lg shrink-0">
                            <button
                                @click="quantity > 1 ? quantity-- : null"
                                class="w-10 h-10 flex items-center justify-center rounded-[1.25rem] hover:bg-slate-800 transition-colors active:scale-95 text-slate-300 hover:text-white"
                                :class="{'opacity-50 cursor-not-allowed hover:bg-transparent': quantity <= 1}"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"/></svg>
                            </button>
                            <span class="w-8 text-center font-black text-lg">{{ quantity }}</span>
                            <button
                                @click="quantity++"
                                class="w-10 h-10 flex items-center justify-center rounded-[1.25rem] hover:bg-slate-800 transition-colors active:scale-95 text-slate-300 hover:text-white"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>

                        <!-- Add to Cart Button -->
                        <button
                            @click="handleAddToCart"
                            class="flex-1 bg-gradient-to-r from-orange-500 to-amber-500 text-white rounded-[1.5rem] py-4 px-6 flex items-center justify-between shadow-lg shadow-orange-500/30 hover:shadow-orange-500/40 hover:-translate-y-0.5 active:scale-[0.98] transition-all"
                        >
                            <span class="font-black uppercase tracking-widest text-xs md:text-sm">Thêm vào giỏ</span>
                            <span class="font-black text-base md:text-lg">{{ totalPrice.toLocaleString() }}đ</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.animate-slide-up {
    animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
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
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
