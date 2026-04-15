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
</script>

<template>
    <Head title="Đánh giá & Phản hồi" />

    <div class="space-y-8">
        <div
            class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between"
        >
            <div>
                <h1 class="text-3xl font-black text-gray-900">
                    Đánh giá & Phản hồi
                </h1>
                <p class="text-sm text-gray-500 mt-2">
                    Xem phản hồi từ khách hàng về các món ăn của quán bạn.
                </p>
            </div>
            <div
                class="rounded-3xl bg-orange-50 border border-orange-100 px-6 py-5"
            >
                <p
                    class="text-xs uppercase tracking-[0.25em] text-orange-500 font-black"
                >
                    Điểm trung bình
                </p>
                <p class="text-4xl font-black text-orange-600 mt-2">
                    {{ averageRatingLabel }}
                </p>
            </div>
        </div>

        <div
            v-if="reviews.data.length === 0"
            class="rounded-[2rem] border border-dashed border-orange-200 bg-orange-50/70 p-10 text-center"
        >
            <p class="text-orange-700 font-black text-lg">
                Chưa có đánh giá nào
            </p>
            <p class="text-sm text-orange-600 mt-2">
                Mọi đánh giá sẽ được hiển thị tại đây khi khách hàng phản hồi.
            </p>
        </div>

        <div v-else class="grid gap-6">
            <div
                v-for="review in reviews.data"
                :key="review.id"
                class="bg-white rounded-[2rem] border border-gray-100 p-6 shadow-sm"
            >
                <div
                    class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        <p
                            class="text-sm font-black text-gray-500 uppercase tracking-[0.3em]"
                        >
                            Món ăn
                        </p>
                        <p class="text-xl font-black text-gray-900 mt-2">
                            {{ review.product.name }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            được đánh giá bởi
                            {{ review.user.name || review.user.email }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1">
                            <span
                                v-for="n in 5"
                                :key="n"
                                class="text-sm"
                                :class="
                                    n <= review.rating
                                        ? 'text-orange-500'
                                        : 'text-gray-300'
                                "
                                >★</span
                            >
                        </div>
                        <span class="text-sm font-bold text-gray-600"
                            >{{ review.rating }}/5</span
                        >
                    </div>
                </div>

                <div
                    class="mt-5 grid gap-4 md:grid-cols-[1fr_auto] items-start"
                >
                    <div class="space-y-4">
                        <p class="text-gray-700 leading-relaxed">
                            {{
                                review.comment ||
                                "Khách hàng chưa để lại bình luận."
                            }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{
                                new Date(review.created_at).toLocaleDateString(
                                    "vi-VN",
                                )
                            }}
                        </p>
                    </div>

                    <div
                        v-if="review.image"
                        class="overflow-hidden rounded-3xl border border-gray-200 bg-gray-50"
                    >
                        <img
                            :src="`/storage/${review.image}`"
                            alt="Review image"
                            class="h-40 w-40 object-cover"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <div
                class="rounded-3xl border border-gray-200 bg-white px-5 py-4 text-sm text-gray-500"
            >
                Tổng số đánh giá:
                <span class="font-black text-gray-900">{{
                    reviews.total
                }}</span>
            </div>
        </div>
    </div>
</template>
