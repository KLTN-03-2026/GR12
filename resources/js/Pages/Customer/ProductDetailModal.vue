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

const formatRating = (rating) => Number(rating).toFixed(1);
</script>

<template>
    <div class="h-full flex flex-col bg-[#f8fafc]">
        <!-- Danh sách Đánh Giá Cu => Premium Look -->
        <div class="p-8 pb-4 flex-1 overflow-y-auto no-scrollbar">
            <h3 class="font-black text-2xl mb-8 flex items-center gap-3">
                <span class="text-3xl">💬</span>
                <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent tracking-tighter">
                    Thực khách nói gì?
                </span>
            </h3>

            <div v-if="reviews.length === 0" class="text-center py-12">
                <div class="w-24 h-24 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-orange-100 shadow-inner">
                    <span class="text-4xl opacity-50">✨</span>
                </div>
                <p class="text-gray-500 font-medium">Chưa có đánh giá nào cho món này.</p>
                <p class="text-sm font-bold text-orange-400 mt-2">Hãy là người đầu tiên chia sẻ cảm nhận nhé!</p>
            </div>

            <div v-else class="space-y-6">
                <div
                    v-for="review in reviews"
                    :key="review.id"
                    class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 group relative overflow-hidden"
                >
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-orange-50/50 to-transparent rounded-bl-[4rem] -z-10 transition-transform group-hover:scale-110"></div>
                    
                    <div class="flex justify-between items-start mb-5">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-orange-400 to-pink-500 p-0.5 shadow-md">
                                    <div class="w-full h-full bg-white rounded-full flex items-center justify-center font-black text-orange-600 text-sm">
                                        {{ review.user.name.charAt(0) }}
                                    </div>
                                </div>
                                <div class="absolute -bottom-1 -right-1 bg-yellow-400 text-white text-[8px] font-black px-1.5 py-0.5 rounded-full shadow-sm flex items-center gap-0.5">
                                    {{ formatRating(review.rating) }} <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                </div>
                            </div>
                            <div>
                                <p class="font-black text-sm text-gray-900 leading-none mb-1 group-hover:text-orange-600 transition-colors">
                                    {{ review.user.name }}
                                </p>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    {{ new Date(review.created_at).toLocaleDateString('vi-VN') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-gray-700 text-sm mb-4 leading-relaxed font-medium relative z-10">
                        <span class="text-2xl text-gray-200 font-serif absolute -top-3 -left-2 -z-10">"</span>
                        {{ review.comment }}
                    </p>

                    <div
                        v-if="review.image"
                        class="w-24 h-24 rounded-2xl overflow-hidden shadow-sm group-hover:shadow-md transition-all duration-300 mb-4"
                    >
                        <img
                            :src="'/storage/' + review.image"
                            class="w-full h-full object-cover transform hover:scale-110 transition-transform duration-500 cursor-zoom-in"
                        />
                    </div>

                    <!-- Restaurant Reply -->
                    <div v-if="review.restaurant_reply" class="bg-gray-50 p-4 rounded-2xl border border-gray-100 relative mt-2">
                        <div class="absolute -top-2 left-6 w-4 h-4 bg-gray-50 border-t border-l border-gray-100 transform rotate-45"></div>
                        <p class="text-[10px] font-black text-orange-500 uppercase tracking-widest flex items-center gap-1.5 mb-1.5 relative z-10">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                            Phản hồi từ Quán
                        </p>
                        <p class="text-sm text-gray-700 font-medium relative z-10">
                            {{ review.restaurant_reply }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Đánh Giá Mới -->
        <div class="bg-white rounded-t-[3rem] p-8 pb-10 shadow-[0_-10px_40px_rgba(0,0,0,0.05)] border-t border-gray-100 z-10 shrink-0">
            <header class="mb-6 flex items-center justify-between">
                <div>
                    <h4 class="font-black text-gray-900 text-lg tracking-tight mb-1">
                        Đánh giá của bạn
                    </h4>
                    <p class="text-[10px] font-bold text-orange-500 uppercase tracking-widest flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Chỉ dành cho người đã mua
                    </p>
                </div>
                
                <!-- Interactive Stars -->
                <div class="flex gap-1 bg-gray-50 p-2 rounded-2xl border border-gray-100" @mouseleave="form.rating = form.rating">
                    <button
                        v-for="star in 5"
                        :key="star"
                        @click="form.rating = star"
                        type="button"
                        class="w-8 h-8 flex items-center justify-center transition-all duration-300 transform"
                        :class="star <= form.rating ? 'text-yellow-400 scale-110' : 'text-gray-300 hover:text-yellow-200 hover:scale-110'"
                    >
                        <svg class="w-6 h-6 drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    </button>
                </div>
            </header>

            <div class="relative mb-5 group">
                <textarea
                    v-model="form.comment"
                    placeholder="Món ăn có ngon không? Phục vụ thế nào? Hãy chia sẻ nhé..."
                    class="w-full bg-gray-50 rounded-2xl border-none p-5 pb-12 text-sm text-gray-800 focus:ring-2 focus:ring-orange-200 transition-all font-medium resize-none peer"
                    rows="3"
                ></textarea>
                
                <!-- Floating Action Buttons inside Textarea -->
                <div class="absolute bottom-3 left-3 flex items-center gap-3">
                    <button
                        type="button"
                        @click="fileInput.click()"
                        class="flex items-center justify-center w-8 h-8 rounded-full bg-white shadow-sm text-gray-400 hover:text-orange-500 hover:shadow transition-all"
                        title="Thêm ảnh minh họa"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </button>
                    <input
                        type="file"
                        ref="fileInput"
                        class="hidden"
                        @change="handleImageChange"
                        accept="image/*"
                    />
                    
                    <!-- Image Preview -->
                    <div
                        v-if="photoPreview"
                        class="relative w-8 h-8 rounded-lg overflow-hidden ring-2 ring-orange-500 shadow-md transition-all"
                    >
                        <img :src="photoPreview" class="w-full h-full object-cover" />
                        <button
                            @click="photoPreview = null; form.image = null;"
                            class="absolute inset-0 bg-black/50 flex items-center justify-center text-white opacity-0 hover:opacity-100 transition-opacity"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

            <button
                @click="submitReview"
                :disabled="form.processing"
                class="w-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-orange-500/30 transition-all active:scale-[0.98] disabled:opacity-50 disabled:shadow-none flex items-center justify-center gap-2"
            >
                <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span>{{ form.processing ? "Đang gửi..." : "Đăng đánh giá" }}</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
</style>
