<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import ProductDetailModal from "@/Components/ProductDetailModal.vue";
import UserAvatar from "@/Components/UserAvatar.vue";

defineOptions({ layout: GuestLayout });

const props = defineProps({
    restaurant: Object,
    menu: Object,
    currentTime: String,
    cartItems: Array,
    reviews: Array,
});

const currentTab = ref("menu");
const isModalOpen = ref(false);
const selectedProduct = ref(null);

const averageRating = computed(() => {
    if (!props.reviews || props.reviews.length === 0) return 5.0;
    const sum = props.reviews.reduce((acc, r) => acc + r.rating, 0);
    return (sum / props.reviews.length).toFixed(1);
});

const categories = computed(() => Object.keys(props.menu || {}));
const activeCategory = ref(categories.value[0] || null);
const filteredMenu = computed(() => {
    if (!activeCategory.value) return props.menu || {};
    return {
        [activeCategory.value]: props.menu?.[activeCategory.value] || [],
    };
});
const totalProducts = computed(() =>
    categories.value.reduce(
        (sum, category) => sum + (props.menu[category]?.length || 0),
        0,
    ),
);
const reviewCount = computed(() => props.reviews?.length || 0);

const openProductModal = (product) => {
    selectedProduct.value = product;
    isModalOpen.value = true;
};

const handleAddToCart = (data) => {
    router.post(
        route("cart.add"),
        {
            product_id: data.product_id,
            quantity: data.quantity,
            options: data.options,
            note: data.note || "",
        },
        {
            preserveScroll: true,
            onSuccess: () => (isModalOpen.value = false),
        },
    );
};

const totalAmount = computed(() =>
    props.cartItems.reduce(
        (sum, item) => sum + item.product.price * item.quantity,
        0,
    ),
);
const totalItems = computed(() =>
    props.cartItems.reduce((sum, item) => sum + item.quantity, 0),
);
const formatPrice = (p) =>
    new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(p);

const isSelling = (from, to) => {
    if (!from || !to) return true; // Nếu không cài giờ thì coi như bán cả ngày

    // Cắt chuỗi để lấy định dạng HH:mm (bỏ phần giây nếu có)
    // Ví dụ: "11:35:12" -> "11:35" | "06:00:00" -> "06:00"
    const now = props.currentTime.substring(0, 5);
    const start = from.substring(0, 5);
    const end = to.substring(0, 5);

    return now >= start && now <= end;
};
</script>

<template>
    <Head :title="restaurant.restaurant_name || restaurant.name" />

    <div class="min-h-screen bg-[#f3f6fb] pb-32 font-sans">
        <header class="relative w-full overflow-hidden">
            <div
                class="relative h-[340px] md:h-[460px] lg:h-[520px] bg-gradient-to-br from-slate-900 via-slate-800 to-orange-700"
            >
                <img
                    v-if="restaurant.image"
                    :src="'/storage/' + restaurant.image"
                    class="absolute inset-0 w-full h-full object-cover opacity-60"
                />
                <div
                    class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-950/40 to-transparent"
                ></div>
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(255,159,64,0.18),_transparent_34%)]"
                ></div>
                <Link
                    href="/"
                    class="absolute top-6 left-6 z-50 bg-slate-950/30 backdrop-blur-xl p-3 rounded-3xl text-white border border-white/15 hover:bg-white/10 transition-all shadow-2xl"
                >
                    <svg
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M15 19l-7-7 7-7"
                            stroke-width="3"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </Link>

                <div class="absolute inset-x-0 bottom-0 pointer-events-none">
                    <div class="max-w-5xl mx-auto px-6 pb-10">
                        <div
                            class="grid grid-cols-1 lg:grid-cols-[1.2fr_0.8fr] gap-8 items-end"
                        >
                            <div
                                class="relative overflow-hidden rounded-[3rem] border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur-xl text-white"
                            >
                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-orange-500/20 px-4 py-2 text-[11px] font-black uppercase tracking-[0.24em] text-orange-100 shadow-sm shadow-orange-500/20"
                                >
                                    Đối tác FOODXPRESS
                                </span>
                                <h1
                                    class="mt-5 text-4xl md:text-6xl font-black tracking-tighter leading-tight uppercase"
                                >
                                    {{
                                        restaurant.restaurant_name ||
                                        restaurant.name
                                    }}
                                </h1>
                                <p
                                    class="mt-4 max-w-2xl text-sm md:text-base text-slate-100/85 leading-relaxed"
                                >
                                    {{
                                        restaurant.description ||
                                        "Thưởng thức món ngon chuẩn vị từ nhà hàng uy tín của chúng tôi. Chọn món, thêm vào giỏ và đợi tận hưởng."
                                    }}
                                </p>
                                <div
                                    class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4"
                                >
                                    <div
                                        class="rounded-3xl bg-white/10 p-4 border border-white/10"
                                    >
                                        <p
                                            class="text-[10px] uppercase tracking-[0.3em] text-slate-300 mb-1"
                                        >
                                            Danh mục
                                        </p>
                                        <p
                                            class="text-base font-black text-white"
                                        >
                                            {{ categories.length }}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-3xl bg-white/10 p-4 border border-white/10"
                                    >
                                        <p
                                            class="text-[10px] uppercase tracking-[0.3em] text-slate-300 mb-1"
                                        >
                                            Món ăn
                                        </p>
                                        <p
                                            class="text-base font-black text-white"
                                        >
                                            {{ totalProducts }}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-3xl bg-white/10 p-4 border border-white/10"
                                    >
                                        <p
                                            class="text-[10px] uppercase tracking-[0.3em] text-slate-300 mb-1"
                                        >
                                            Đánh giá
                                        </p>
                                        <p
                                            class="text-base font-black text-white"
                                        >
                                            {{ reviewCount }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="rounded-[3rem] bg-white/95 p-6 shadow-2xl border border-slate-200/70 backdrop-blur-xl"
                            >
                                <div
                                    class="flex items-center justify-between gap-4"
                                >
                                    <div>
                                        <p
                                            class="text-[10px] uppercase tracking-[0.3em] text-slate-500 font-black mb-2"
                                        >
                                            Địa chỉ
                                        </p>
                                        <p
                                            class="font-black text-slate-900 text-xl leading-tight"
                                        >
                                            {{ restaurant.address }}
                                        </p>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span
                                            class="text-sm font-black text-orange-600"
                                            >{{ averageRating }}
                                            <span class="text-slate-400 text-xs"
                                                >/5</span
                                            ></span
                                        >
                                        <div
                                            class="flex gap-1 text-orange-400 text-sm mt-1"
                                        >
                                            ★★★★★
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="mt-6 grid grid-cols-2 gap-3 text-[11px] uppercase tracking-[0.25em] text-slate-500"
                                >
                                    <span
                                        class="rounded-3xl bg-slate-50/80 px-3 py-2"
                                        >Giao hàng nhanh</span
                                    >
                                    <span
                                        class="rounded-3xl bg-slate-50/80 px-3 py-2"
                                        >Phục vụ tận tâm</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="relative bg-[#f3f6fb] -mt-10 pt-10 pb-4 border-t border-slate-200/80"
            >
                <div class="max-w-5xl mx-auto px-4">
                    <div
                        class="flex flex-col lg:flex-row items-center justify-between gap-4"
                    >
                        <div class="flex flex-wrap gap-3">
                            <button
                                @click="currentTab = 'menu'"
                                :class="
                                    currentTab === 'menu'
                                        ? 'bg-orange-600 text-white shadow-lg'
                                        : 'bg-white text-slate-600 shadow-sm hover:shadow-md'
                                "
                                class="rounded-full px-5 py-3 text-xs font-black uppercase tracking-[0.24em] transition-all duration-300"
                            >
                                THỰC ĐƠN
                            </button>
                            <button
                                @click="currentTab = 'reviews'"
                                :class="
                                    currentTab === 'reviews'
                                        ? 'bg-orange-600 text-white shadow-lg'
                                        : 'bg-white text-slate-600 shadow-sm hover:shadow-md'
                                "
                                class="rounded-full px-5 py-3 text-xs font-black uppercase tracking-[0.24em] transition-all duration-300"
                            >
                                ĐÁNH GIÁ ({{ reviewCount }})
                            </button>
                        </div>
                        <div
                            class="flex flex-wrap gap-3 text-[11px] uppercase tracking-[0.24em] text-slate-500"
                        >
                            <span
                                class="rounded-full bg-white px-4 py-3 shadow-sm"
                                >Giờ mở cửa 06:00</span
                            >
                            <span
                                class="rounded-full bg-white px-4 py-3 shadow-sm"
                                >Đóng cửa 22:00</span
                            >
                            <span
                                class="rounded-full bg-green-50 text-green-600 px-4 py-3 shadow-sm"
                                >OPEN NOW</span
                            >
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-5xl mx-auto px-4 py-12 md:py-20">
            <Transition name="fade-slide" mode="out-in">
                <div
                    v-if="currentTab === 'menu'"
                    key="menu_content"
                    class="space-y-14"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-4 mb-10"
                    >
                        <div>
                            <p
                                class="text-sm font-black uppercase tracking-[0.24em] text-orange-600"
                            >
                                Thực đơn nhà hàng
                            </p>
                            <h2
                                class="mt-3 text-3xl md:text-4xl font-black text-slate-900 tracking-tighter"
                            >
                                Chọn món ngon, thêm vào giỏ
                            </h2>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <button
                                @click="activeCategory = null"
                                :class="
                                    activeCategory === null
                                        ? 'bg-orange-600 text-white shadow-lg'
                                        : 'bg-white text-slate-700 shadow-sm hover:shadow-md'
                                "
                                class="rounded-full px-5 py-3 text-xs font-black uppercase tracking-[0.24em] transition-all duration-300"
                            >
                                Tất cả
                            </button>
                            <button
                                v-for="category in categories"
                                :key="category"
                                @click="activeCategory = category"
                                :class="
                                    activeCategory === category
                                        ? 'bg-orange-600 text-white shadow-lg'
                                        : 'bg-white text-slate-700 shadow-sm hover:shadow-md'
                                "
                                class="rounded-full px-5 py-3 text-xs font-black uppercase tracking-[0.24em] transition-all duration-300"
                            >
                                {{ category }}
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                        <div
                            v-for="(products, categoryName) in filteredMenu"
                            :key="categoryName"
                            class="space-y-6"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-2 h-12 rounded-full bg-orange-500"
                                ></div>
                                <div>
                                    <p
                                        class="text-xs uppercase tracking-[0.28em] text-slate-500 font-black mb-2"
                                    >
                                        {{ categoryName }}
                                    </p>
                                    <h3
                                        class="text-2xl font-black text-slate-900 tracking-tighter"
                                    >
                                        {{ products.length }} món hấp dẫn
                                    </h3>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6">
                                <div
                                    v-for="product in products"
                                    :key="product.id"
                                    @click="openProductModal(product)"
                                    class="group relative overflow-hidden rounded-[2.5rem] border border-slate-200/80 bg-white p-6 shadow-lg transition-all duration-500 hover:-translate-y-1 hover:shadow-2xl cursor-pointer"
                                    :class="
                                        !isSelling(
                                            product.available_from,
                                            product.available_to,
                                        )
                                            ? 'opacity-50 grayscale pointer-events-none'
                                            : ''
                                    "
                                >
                                    <div
                                        class="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-orange-50 to-transparent pointer-events-none"
                                    ></div>
                                    <div
                                        class="grid grid-cols-[90px_1fr] gap-5 items-center"
                                    >
                                        <div
                                            class="relative w-24 h-24 rounded-[2rem] overflow-hidden shadow-xl bg-slate-100 flex items-center justify-center"
                                        >
                                            <img
                                                v-if="product.image"
                                                :src="
                                                    '/storage/' + product.image
                                                "
                                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                            />
                                            <div
                                                v-else
                                                class="flex h-full w-full items-center justify-center text-slate-400 text-xs font-black uppercase tracking-[0.24em]"
                                            >
                                                No image
                                            </div>
                                            <span
                                                v-if="
                                                    !isSelling(
                                                        product.available_from,
                                                        product.available_to,
                                                    )
                                                "
                                                class="absolute inset-0 bg-slate-950/70 flex items-center justify-center text-[10px] uppercase tracking-[0.24em] text-white font-black"
                                            >
                                                {{
                                                    !product.is_available
                                                        ? "Hết món"
                                                        : "Ngoài giờ bán"
                                                }}
                                            </span>
                                        </div>
                                        <div class="space-y-3">
                                            <div
                                                class="flex items-start justify-between gap-4"
                                            >
                                                <h4
                                                    class="text-xl font-black text-slate-900 tracking-tight"
                                                >
                                                    {{ product.name }}
                                                </h4>
                                                <span
                                                    class="text-orange-600 font-black text-lg"
                                                    >{{
                                                        formatPrice(
                                                            product.price,
                                                        )
                                                    }}</span
                                                >
                                            </div>
                                            <p
                                                class="text-sm leading-relaxed text-slate-500 line-clamp-3"
                                            >
                                                {{ product.description }}
                                            </p>
                                            <div
                                                class="flex flex-wrap items-center gap-2"
                                            >
                                                <span
                                                    class="rounded-full bg-orange-50 text-orange-700 px-3 py-1 text-[10px] uppercase tracking-[0.25em] font-black"
                                                >
                                                    {{
                                                        product.category
                                                            ?.name ||
                                                        (typeof product.category ===
                                                        "string"
                                                            ? product.category
                                                            : categoryName)
                                                    }}
                                                </span>
                                                <span
                                                    class="rounded-full bg-slate-100 text-slate-500 px-3 py-1 text-[10px] uppercase tracking-[0.25em] font-black"
                                                >
                                                    {{ product.available_from }}
                                                    - {{ product.available_to }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else key="reviews_content" class="space-y-12">
                    <div
                        class="grid grid-cols-1 lg:grid-cols-[1.1fr_0.9fr] gap-8"
                    >
                        <div
                            class="rounded-[3rem] bg-slate-900 p-10 shadow-2xl border border-white/10 text-white"
                        >
                            <p
                                class="text-xs uppercase tracking-[0.3em] text-orange-300 font-black mb-4"
                            >
                                Đánh giá chung
                            </p>
                            <div class="flex items-center gap-6">
                                <div
                                    class="text-[5rem] leading-none font-black tracking-tighter"
                                >
                                    {{ averageRating }}
                                </div>
                                <div>
                                    <div
                                        class="flex items-center gap-2 text-orange-300 text-base"
                                    >
                                        <span>★★★★★</span>
                                        <span class="text-sm text-slate-300"
                                            >( {{ reviewCount }} nhận xét
                                            )</span
                                        >
                                    </div>
                                    <p
                                        class="mt-4 max-w-xl text-sm text-slate-300 leading-relaxed"
                                    >
                                        Nhận xét chân thực từ khách hàng đã ăn,
                                        giúp bạn chọn món ngon nhất và an tâm
                                        khi đặt hàng.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="grid gap-4 rounded-[3rem] bg-white p-8 shadow-lg border border-slate-200"
                        >
                            <div
                                v-for="i in [5, 4, 3, 2, 1]"
                                :key="i"
                                class="flex items-center gap-4"
                            >
                                <span
                                    class="w-8 text-sm font-black text-slate-500"
                                    >{{ i }}</span
                                >
                                <div
                                    class="flex-1 h-3 rounded-full bg-slate-100 overflow-hidden"
                                >
                                    <div
                                        class="h-full bg-gradient-to-r from-orange-400 to-orange-600 rounded-full transition-all duration-500"
                                        :style="{
                                            width:
                                                ((reviews?.filter(
                                                    (r) => r.rating === i,
                                                ).length /
                                                    reviewCount) *
                                                    100 || 0) + '%',
                                        }"
                                    ></div>
                                </div>
                                <span
                                    class="w-10 text-right text-xs font-black text-slate-500"
                                    >{{
                                        reviews?.filter((r) => r.rating === i)
                                            .length || 0
                                    }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                        <div
                            v-for="review in reviews"
                            :key="review.id"
                            class="group overflow-hidden rounded-[2.5rem] bg-white border border-slate-200 shadow-lg transition-all duration-500 hover:-translate-y-1"
                        >
                            <div class="p-8 space-y-5">
                                <div
                                    class="flex items-center justify-between gap-4"
                                >
                                    <div class="flex items-center gap-4">
                                        <UserAvatar
                                            :user="review.user"
                                            size="lg"
                                            rounded="xl"
                                        />
                                        <div>
                                            <p
                                                class="font-black text-slate-900"
                                            >
                                                {{ review.user.name }}
                                            </p>
                                            <div
                                                class="flex gap-1 text-orange-500 text-sm"
                                            >
                                                <span
                                                    v-for="s in review.rating"
                                                    :key="s"
                                                    >★</span
                                                >
                                            </div>
                                        </div>
                                    </div>
                                    <span
                                        class="text-[10px] uppercase tracking-[0.3em] text-slate-400 font-black"
                                    >
                                        {{
                                            new Date(
                                                review.created_at,
                                            ).toLocaleDateString()
                                        }}
                                    </span>
                                </div>
                                <p
                                    class="text-sm leading-relaxed text-slate-600"
                                >
                                    "{{ review.comment }}"
                                </p>
                                <img
                                    v-if="review.image"
                                    :src="'/storage/' + review.image"
                                    class="w-full rounded-[1.8rem] object-cover shadow-xl"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </main>

        <div
            v-if="totalItems > 0"
            class="fixed bottom-8 left-0 right-0 z-[100] px-6 pointer-events-none"
        >
            <div class="max-w-3xl mx-auto pointer-events-auto">
                <Link :href="route('cart.index')" class="block group">
                    <div
                        class="rounded-[3rem] border border-slate-200/80 bg-white/95 p-5 shadow-2xl backdrop-blur-xl flex flex-col sm:flex-row items-center justify-between gap-4 transition-all duration-300 hover:-translate-y-0.5"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-[1.75rem] bg-gradient-to-br from-orange-500 to-orange-600 text-2xl font-black text-white shadow-xl"
                            >
                                {{ totalItems }}
                            </div>
                            <div>
                                <p
                                    class="text-sm font-black uppercase tracking-[0.24em] text-slate-500"
                                >
                                    Giỏ hàng của bạn
                                </p>
                                <p
                                    class="text-lg font-black text-slate-900 tracking-tight"
                                >
                                    Bữa ăn đang chờ sẵn
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p
                                class="text-sm uppercase tracking-[0.28em] text-orange-500 font-black"
                            >
                                Tổng
                            </p>
                            <p
                                class="text-3xl font-black text-slate-900 tracking-tighter"
                            >
                                {{ formatPrice(totalAmount) }}
                            </p>
                        </div>
                    </div>
                </Link>
            </div>
        </div>

        <ProductDetailModal
            v-if="selectedProduct"
            :show="isModalOpen"
            :product="selectedProduct"
            @close="isModalOpen = false"
            @addToCart="handleAddToCart"
        />
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

/* Animation Chuyển Tab Cao Cấp */
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.fade-slide-enter-from {
    opacity: 0;
    transform: translateX(20px);
}
.fade-slide-leave-to {
    opacity: 0;
    transform: translateX(-20px);
}

.tracking-tighter {
    letter-spacing: -0.05em;
}
</style>
