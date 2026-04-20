<script setup>
import { ref, computed } from "vue";
import UserAvatar from "@/Components/UserAvatar.vue";

const props = defineProps({
    product: Object,
    show: Boolean,
});

const emit = defineEmits(["close", "addToCart"]);

// 1. Quản lý trạng thái
const selectedOptions = ref([]);
const quantity = ref(1);
const note = ref(""); // Thêm trường ghi chú nếu khách muốn dặn dò
const showSidebar = ref(false); // Ẩn/hiện phần sidebar thông tin bổ sung

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

// 4. Gửi dữ liệu ra ngoài
const handleAddToCart = () => {
    emit("addToCart", {
        product_id: props.product.id,
        options: selectedOptions.value.map((opt) => opt.id), // Gửi danh sách ID để backend xử lý chuẩn hơn
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

                <div class="p-8 space-y-6 pb-10">
                    <div class="grid gap-6 md:grid-cols-[1.8fr_0.95fr] items-start">
                        <div class="space-y-6">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="space-y-2">
                                    <span
                                        class="inline-flex items-center rounded-full bg-orange-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.24em] text-orange-600"
                                    >
                                        {{ product.category?.name || 'Món ăn' }}
                                    </span>
                                    <h2 class="text-4xl font-black text-slate-900 leading-tight">
                                        {{ product.name }}
                                    </h2>
                                    <p class="max-w-2xl text-sm text-slate-500 leading-relaxed">
                                        {{
                                            product.description ||
                                            'Hương vị tuyệt vời đang chờ bạn khám phá.'
                                        }}
                                    </p>
                                </div>

                                <div class="flex flex-col items-start gap-3 sm:items-end">
                                    <div class="rounded-[2rem] bg-slate-900 px-5 py-3 text-white shadow-xl">
                                        <p class="text-xs uppercase tracking-[0.24em] text-slate-300">
                                            Giá khởi điểm
                                        </p>
                                        <p class="text-3xl font-black leading-none">
                                            {{ Number(product.price).toLocaleString() }}đ
                                        </p>
                                    </div>
                                    <button
                                        @click="showSidebar = !showSidebar"
                                        class="rounded-full border border-slate-200 bg-white px-5 py-3 text-xs font-black uppercase tracking-[0.24em] text-slate-700 shadow-sm transition hover:border-orange-300 hover:text-orange-600"
                                    >
                                        {{ showSidebar ? 'Ẩn chi tiết' : 'Mở chi tiết' }}
                                    </button>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-[2rem] bg-slate-50 p-5 border border-slate-200 shadow-sm">
                                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400 mb-2">Khung giờ phục vụ</p>
                                    <p class="font-black text-slate-900">{{ product.available_from }} - {{ product.available_to }}</p>
                                </div>
                                <div class="rounded-[2rem] bg-slate-50 p-5 border border-slate-200 shadow-sm">
                                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400 mb-2">Tùy chọn đã chọn</p>
                                    <p class="font-black text-slate-900">{{ selectedOptions.length }} tùy chọn</p>
                                </div>
                            </div>

                            <div v-if="product.options?.length" class="space-y-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Tùy chọn thêm</p>
                                        <p class="text-base font-black text-slate-900">Chọn thêm để tăng hương vị</p>
                                    </div>
                                    <span class="text-xs uppercase tracking-[0.24em] text-orange-500">Chạm để chọn</span>
                                </div>

                                <div class="grid gap-3">
                                    <div
                                        v-for="opt in product.options"
                                        :key="opt.id"
                                        @click="toggleOption(opt)"
                                        :class="[
                                            'flex items-center gap-4 rounded-[1.75rem] border p-4 transition-all duration-300 cursor-pointer',
                                            selectedOptions.find((o) => o.id === opt.id)
                                                ? 'border-orange-300 bg-orange-50 shadow-lg'
                                                : 'border-slate-200 bg-white hover:border-orange-200 hover:shadow-sm'
                                        ]"
                                    >
                                        <div
                                            class="flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 overflow-hidden shadow-sm"
                                        >
                                            <img
                                                v-if="opt.image"
                                                :src="'/storage/' + opt.image"
                                                class="h-full w-full object-cover"
                                            />
                                            <div
                                                v-else
                                                class="text-[10px] font-black text-slate-400"
                                            >
                                                😋
                                            </div>
                                        </div>

                                        <div class="flex-1">
                                            <p class="text-sm font-black text-slate-900">{{ opt.option_value }}</p>
                                            <p class="text-xs uppercase tracking-[0.22em] text-orange-500">
                                                +{{ Number(opt.additional_price).toLocaleString() }}đ
                                            </p>
                                        </div>

                                        <div
                                            :class="[
                                                'flex h-8 w-8 items-center justify-center rounded-full transition-all',
                                                selectedOptions.find((o) => o.id === opt.id)
                                                    ? 'bg-orange-500 text-white'
                                                    : 'bg-white border border-slate-200 text-slate-400'
                                            ]"
                                        >
                                            <svg
                                                v-if="selectedOptions.find((o) => o.id === opt.id)"
                                                class="h-4 w-4"
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
                                            <span v-else class="text-[12px] font-black">+</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.24em]">
                                    Ghi chú cho quán
                                </label>
                                <input
                                    v-model="note"
                                    type="text"
                                    placeholder="Ví dụ: Không hành, nhiều cay..."
                                    class="w-full rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-4 text-sm text-slate-700 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100"
                                />
                            </div>
                        </div>

                        <Transition name="slide-fade">
                            <aside
                                v-if="showSidebar"
                                class="rounded-[2rem] border border-slate-200 bg-slate-50 p-6 shadow-xl"
                            >
                                <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500 font-black mb-4">
                                    Thông tin nhanh
                                </p>
                                <div class="space-y-5 text-sm text-slate-600">
                                    <div class="rounded-3xl bg-white p-4 border border-slate-200 shadow-sm">
                                        <p class="text-xs uppercase tracking-[0.24em] text-slate-400 mb-2">Danh mục</p>
                                        <p class="font-black text-slate-900">{{ product.category?.name || 'Món ăn' }}</p>
                                    </div>
                                    <div class="rounded-3xl bg-white p-4 border border-slate-200 shadow-sm">
                                        <p class="text-xs uppercase tracking-[0.24em] text-slate-400 mb-2">Giờ phục vụ</p>
                                        <p class="font-black text-slate-900">{{ product.available_from }} - {{ product.available_to }}</p>
                                    </div>
                                    <div class="rounded-3xl bg-white p-4 border border-slate-200 shadow-sm">
                                        <p class="text-xs uppercase tracking-[0.24em] text-slate-400 mb-2">Tùy chọn đã chọn</p>
                                        <p class="font-black text-slate-900">{{ selectedOptions.length }} mục</p>
                                    </div>
                                    <div class="rounded-3xl bg-white p-4 border border-slate-200 shadow-sm">
                                        <p class="text-xs uppercase tracking-[0.24em] text-slate-400 mb-2">Tổng giá</p>
                                        <p class="text-lg font-black text-orange-600">{{ totalPrice.toLocaleString() }}đ</p>
                                    </div>
                                </div>
                            </aside>
                        </Transition>
                    </div>
                </div>

                <div
                    class="sticky bottom-0 left-0 right-0 bg-white/95 backdrop-blur-xl p-6 border-t border-gray-100 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between shadow-[0_-10px_30px_rgba(0,0,0,0.05)]"
                >
                    <div class="flex items-center gap-3 rounded-[1.75rem] bg-slate-900 px-4 py-3 text-white shadow-lg">
                        <button
                            @click="quantity > 1 ? quantity-- : null"
                            class="h-10 w-10 rounded-xl border border-white/10 bg-white/10 transition hover:bg-white/20"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4" />
                            </svg>
                        </button>
                        <span class="w-10 text-center text-lg font-bold">{{ quantity }}</span>
                        <button
                            @click="quantity++"
                            class="h-10 w-10 rounded-xl border border-white/10 bg-white/10 transition hover:bg-white/20"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>

                    <button
                        @click="handleAddToCart"
                        class="flex-1 rounded-[1.75rem] bg-orange-500 px-6 py-4 text-sm font-black uppercase tracking-[0.24em] text-white shadow-xl shadow-orange-200 transition hover:bg-orange-600 active:scale-[0.99]"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <span>Thêm vào giỏ</span>
                            <span class="rounded-full bg-white/15 px-4 py-2 text-base font-black text-white">
                                {{ totalPrice.toLocaleString() }}đ
                            </span>
                        </div>
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
.fade-leave-active,
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.3s ease;
}
.fade-enter-from,
.fade-leave-to,
.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
    transform: translateX(10px);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
    transform: translateX(10px);
}

.slide-fade-enter-to,
.slide-fade-leave-from {
    opacity: 1;
    transform: translateX(0);
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
