<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";

const props = defineProps({
    product: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});

const emit = defineEmits(["close"]);

const photoPreview = ref(null);
const fileInput = ref(null);

// Reactive form initialization
const form = useForm({
    product_id: props.product?.id || null,
    rating: 5,
    comment: "",
    image: null,
});

// Update product_id when product changes
watch(
    () => props.product?.id,
    (newId) => {
        if (newId && form.product_id !== newId) {
            form.product_id = newId;
        }
    },
);

const reviews = computed(() => props.product?.reviews || []);

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        const reader = new FileReader();
        reader.onload = (e) => (photoPreview.value = e.target.result);
        reader.readAsDataURL(file);
    }
};

const submitReview = () => {
    form.post(route("reviews.store"), {
        forceFormData: true, // Quan trọng khi gửi file
        onSuccess: () => {
            form.reset();
            photoPreview.value = null;
            emit("close");
        },
    });
};
</script>

<template>
    <div class="mt-8 border-t pt-8 px-2">
        <h3 class="font-black text-2xl mb-6 italic tracking-tighter uppercase">
            Thực khách nói gì? 💬
        </h3>

        <div
            class="space-y-6 max-h-[400px] overflow-y-auto no-scrollbar pr-2 mb-8"
        >
            <div
                v-for="review in reviews"
                :key="review.id"
                class="bg-gray-50 rounded-[2rem] p-6 border border-gray-100"
            >
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center font-black text-orange-600 text-xs shadow-sm"
                        >
                            {{ review.user.name.charAt(0) }}
                        </div>
                        <div>
                            <p
                                class="font-black text-sm text-gray-800 leading-none mb-1"
                            >
                                {{ review.user.name }}
                            </p>
                            <div class="flex text-orange-400 text-[10px]">
                                <span v-for="s in review.rating" :key="s"
                                    >★</span
                                >
                            </div>
                        </div>
                    </div>
                    <span
                        class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"
                        >{{
                            new Date(review.created_at).toLocaleDateString()
                        }}</span
                    >
                </div>
                <p class="text-gray-600 text-sm italic mb-4 leading-relaxed">
                    "{{ review.comment }}"
                </p>

                <div
                    v-if="review.image"
                    class="w-24 h-24 rounded-2xl overflow-hidden border-2 border-white shadow-md"
                >
                    <img
                        :src="'/storage/' + review.image"
                        class="w-full h-full object-cover"
                    />
                </div>
            </div>
        </div>

        <div
            class="bg-orange-50/50 rounded-[2.5rem] p-8 border border-orange-100"
        >
            <header class="mb-6 text-center">
                <h4
                    class="font-black text-gray-800 uppercase text-xs tracking-[0.2em]"
                >
                    Chia sẻ trải nghiệm của bạn
                </h4>
                <p
                    class="text-[10px] font-bold text-orange-400 uppercase tracking-widest mt-2"
                >
                    * Chỉ những khách hàng đã trải nghiệm món ăn tại quán mới có
                    thể để lại đánh giá kèm hình ảnh.
                </p>
            </header>

            <div class="flex justify-center gap-3 mb-6">
                <button
                    v-for="star in 5"
                    :key="star"
                    @click="form.rating = star"
                    type="button"
                    class="text-3xl transition-all duration-300 hover:scale-125"
                    :class="
                        star <= form.rating
                            ? 'text-orange-500 drop-shadow-[0_0_8px_rgba(249,115,22,0.4)]'
                            : 'text-gray-200'
                    "
                >
                    ★
                </button>
            </div>

            <textarea
                v-model="form.comment"
                placeholder="Món ăn có vừa miệng không bạn ơi?..."
                class="w-full bg-white rounded-2xl border-none p-5 text-sm shadow-sm focus:ring-4 focus:ring-orange-200 mb-4 transition-all"
            ></textarea>

            <div class="flex items-center gap-4 mb-6">
                <button
                    type="button"
                    @click="fileInput.click()"
                    class="flex items-center gap-2 bg-white px-4 py-3 rounded-xl shadow-sm border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-all"
                >
                    <span>📷 Thêm ảnh món ăn</span>
                </button>
                <input
                    type="file"
                    ref="fileInput"
                    class="hidden"
                    @change="handleImageChange"
                    accept="image/*"
                />

                <div
                    v-if="photoPreview"
                    class="relative w-16 h-16 rounded-xl overflow-hidden border-2 border-orange-400 shadow-lg animate-in fade-in zoom-in"
                >
                    <img
                        :src="photoPreview"
                        class="w-full h-full object-cover"
                    />
                    <button
                        @click="
                            photoPreview = null;
                            form.image = null;
                        "
                        class="absolute top-0 right-0 bg-red-500 text-white w-5 h-5 flex items-center justify-center text-[8px] rounded-bl-lg"
                    >
                        ✕
                    </button>
                </div>
            </div>

            <button
                @click="submitReview"
                :disabled="form.processing"
                class="w-full bg-gray-900 text-white py-4 rounded-2xl font-black text-[11px] uppercase tracking-[0.3em] shadow-xl shadow-orange-100 hover:bg-orange-600 transition-all active:scale-95 disabled:opacity-50"
            >
                {{ form.processing ? "ĐANG GỬI..." : "ĐĂNG ĐÁNH GIÁ 🚀" }}
            </button>
        </div>
    </div>
</template>
