<script setup>
import RestaurantLayout from "@/Layouts/RestaurantLayout.vue";
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    reviews: Object,
    averageRating: Number,
});

defineOptions({ layout: RestaurantLayout });

const averageRatingLabel = computed(() => {
    if (!props.averageRating || props.averageRating === 0) {
        return "Chưa có đánh giá";
    }
    return `${props.averageRating} / 5`;
});

import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const replyingTo = ref(null);
const replyForm = useForm({
    restaurant_reply: ''
});

const openReply = (review) => {
    replyingTo.value = review.id;
    replyForm.restaurant_reply = review.restaurant_reply || '';
};

const submitReply = (reviewId) => {
    replyForm.post(route('restaurant.reviews.reply', reviewId), {
        preserveScroll: true,
        onSuccess: () => {
            replyingTo.value = null;
        }
    });
};
</script>

<template>
    <Head title="Đánh giá & Phản hồi" />

    <div class="space-y-10 pb-20 animate-fade-in-up">
        <!-- Header Section -->
        <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
            <div class="space-y-2">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                    Đánh giá & Phản hồi <span class="animate-bounce">⭐</span>
                </h1>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest bg-slate-100 w-fit px-4 py-1.5 rounded-xl">
                    Lắng nghe khách hàng để hoàn thiện hơn
                </p>
            </div>
            
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-[2rem] p-6 shadow-[0_10px_30px_rgba(249,115,22,0.3)] text-white relative overflow-hidden min-w-[200px] flex flex-col justify-center">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/20 rounded-full blur-2xl"></div>
                <p class="text-[10px] uppercase tracking-[0.3em] font-black text-orange-100 mb-1 relative z-10">
                    Điểm trung bình
                </p>
                <div class="flex items-end gap-2 relative z-10">
                    <p class="text-5xl font-black tracking-tighter leading-none">{{ averageRatingLabel.split(' ')[0] }}</p>
                    <p class="text-lg font-bold text-orange-200 mb-1" v-if="averageRatingLabel !== 'Chưa có đánh giá'">/ 5</p>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="reviews.data.length === 0" class="bg-white rounded-[3rem] p-16 md:p-24 border border-slate-100 flex flex-col items-center justify-center text-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-orange-50/50 to-transparent"></div>
            <div class="relative z-10 flex flex-col items-center">
                <div class="w-24 h-24 bg-orange-50 rounded-[2rem] flex items-center justify-center mb-6 shadow-inner border border-orange-100/50">
                    <span class="text-5xl">💬</span>
                </div>
                <h3 class="text-2xl font-black text-slate-800 tracking-tight mb-2">
                    Chưa có đánh giá nào
                </h3>
                <p class="text-slate-500 font-medium text-sm">
                    Mọi đánh giá sẽ được hiển thị tại đây khi khách hàng phản hồi.
                </p>
            </div>
        </div>

        <!-- Reviews List -->
        <div v-else class="grid gap-6 md:grid-cols-2">
            <div
                v-for="review in reviews.data"
                :key="review.id"
                class="bg-white rounded-[2rem] border border-slate-100 p-7 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_15px_40px_rgba(249,115,22,0.1)] transition-all duration-300 group flex flex-col h-full"
            >
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-lg font-black text-slate-400 shadow-inner">
                            {{ (review.user.name || review.user.email).charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <p class="font-black text-slate-800 text-sm tracking-tight">{{ review.user.name || review.user.email }}</p>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ new Date(review.created_at).toLocaleDateString("vi-VN") }}</p>
                        </div>
                    </div>
                    <div class="flex gap-1 bg-orange-50 px-3 py-1.5 rounded-lg border border-orange-100/50">
                        <svg v-for="n in 5" :key="n" class="w-4 h-4" :class="n <= review.rating ? 'text-orange-500' : 'text-slate-200'" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                </div>

                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 mb-5 relative overflow-hidden group-hover:border-orange-200/50 transition-colors">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Món ăn đánh giá</p>
                    <p class="font-bold text-slate-800 text-sm truncate relative z-10">{{ review.product.name }}</p>
                </div>

                <div class="flex-1 flex flex-col md:flex-row gap-5">
                    <p class="text-slate-600 text-sm leading-relaxed flex-1 font-medium italic">
                        "{{ review.comment || "Không có nội dung đánh giá." }}"
                    </p>

                    <div v-if="review.image" class="w-full md:w-28 h-28 shrink-0 rounded-[1.5rem] overflow-hidden border-2 border-slate-100 shadow-sm group-hover:border-orange-200 transition-colors">
                        <img
                            :src="`/storage/${review.image}`"
                            alt="Review image"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        />
                    </div>
                </div>

                <!-- Reply Section -->
                <div class="mt-6 pt-5 border-t border-slate-100">
                    <div v-if="review.restaurant_reply && replyingTo !== review.id" class="bg-orange-50/50 p-4 rounded-2xl border border-orange-100">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[10px] font-black text-orange-600 uppercase tracking-widest flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                Quán phản hồi
                            </p>
                            <button @click="openReply(review)" class="text-[10px] text-slate-400 font-bold hover:text-orange-500 transition-colors">
                                Chỉnh sửa
                            </button>
                        </div>
                        <p class="text-sm text-slate-700 font-medium">
                            {{ review.restaurant_reply }}
                        </p>
                    </div>

                    <div v-else-if="replyingTo === review.id" class="animate-fade-in-up">
                        <textarea 
                            v-model="replyForm.restaurant_reply" 
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-sm focus:ring-2 focus:ring-orange-200 focus:border-orange-400 transition-all mb-3 resize-none"
                            rows="3"
                            placeholder="Nhập nội dung phản hồi của quán..."
                        ></textarea>
                        <div class="flex items-center justify-end gap-3">
                            <button @click="replyingTo = null" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-50 rounded-xl transition-colors">Hủy</button>
                            <button @click="submitReply(review.id)" :disabled="replyForm.processing" class="px-5 py-2.5 bg-slate-900 text-white text-xs font-black rounded-xl hover:bg-slate-800 transition-all disabled:opacity-50 flex items-center gap-2">
                                <svg v-if="replyForm.processing" class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Gửi Phản Hồi
                            </button>
                        </div>
                    </div>

                    <button v-else @click="openReply(review)" class="text-xs font-bold text-slate-500 hover:text-orange-600 transition-colors flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                        Phản hồi khách hàng
                    </button>
                </div>
            </div>
        </div>

        <div class="flex justify-center mt-8">
            <div class="inline-flex items-center gap-3 rounded-full border border-slate-200 bg-white px-6 py-3 shadow-[0_4px_20px_rgb(0,0,0,0.03)] text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                <span>Tổng số đánh giá:</span>
                <span class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center font-black text-sm">{{ reviews.total }}</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
