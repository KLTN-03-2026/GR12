<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
    restaurant: Object,
    menu: Object,
    auth: Object,
});

// --- LOGIC MODAL & GIỎ HÀNG ---
const isModalOpen = ref(false);
const selectedProduct = ref(null);
const selectedOptions = ref([]); // Lưu ID của các topping đã chọn

// Mở Modal chi tiết món
const openModal = (product) => {
    selectedProduct.value = product;
    selectedOptions.value = []; // Reset lựa chọn cũ
    isModalOpen.value = true;
};

// Đóng Modal
const closeModal = () => {
    isModalOpen.value = false;
    selectedProduct.value = null;
};

// Tính tổng tiền (Giá gốc + Topping)
const totalPrice = computed(() => {
    if (!selectedProduct.value) return 0;
    let basePrice = parseFloat(selectedProduct.value.price);

    // Cộng dồn giá của các options được chọn
    let optionsPrice = selectedProduct.value.options
        .filter((opt) => selectedOptions.value.includes(opt.id))
        .reduce((sum, opt) => sum + parseFloat(opt.additional_price), 0);

    return basePrice + optionsPrice;
});

// Xử lý thêm vào giỏ hàng
const addToCart = () => {
    if (!props.auth?.user) {
        alert("Vui lòng đăng nhập để đặt món!");
        router.get(route("login"));
        return;
    }

    router.post(
        route("cart.add"),
        {
            product_id: selectedProduct.value.id,
            options: selectedOptions.value, // Gửi kèm danh sách topping
            total_price: totalPrice.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                // Bạn có thể thêm một thông báo nhỏ ở đây
            },
        },
    );
};

// Gom nhóm options theo tên (Size, Topping...) để hiển thị tiêu đề
const groupedOptions = computed(() => {
    if (!selectedProduct.value?.options) return {};
    return selectedProduct.value.options.reduce((acc, opt) => {
        (acc[opt.option_name] = acc[opt.option_name] || []).push(opt);
        return acc;
    }, {});
});

defineOptions({ layout: GuestLayout });
</script>

<template>
    <Head :title="'Thực đơn - ' + restaurant.name" />

    <div class="mb-10 relative h-64 rounded-[3rem] overflow-hidden shadow-2xl">
        <img
            src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1200"
            class="w-full h-full object-cover"
        />
        <div
            class="absolute inset-0 bg-black/40 flex flex-col justify-end p-10"
        >
            <h1 class="text-4xl font-black text-white italic uppercase">
                {{ restaurant.name }}
            </h1>
            <p class="text-orange-300 font-bold mt-2">
                ⭐ 4.9 • 15-25 phút • Freeship
            </p>
        </div>
    </div>

    <div
        v-for="(products, categoryName) in menu"
        :key="categoryName"
        class="mb-12"
    >
        <h2
            class="text-2xl font-black text-gray-800 mb-6 border-l-8 border-orange-50 pl-4"
        >
            {{ categoryName }}
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div
                v-for="product in products"
                :key="product.id"
                @click="openModal(product)"
                class="flex items-center gap-6 p-5 bg-white rounded-[2rem] border border-gray-100 hover:shadow-xl cursor-pointer transition-all group"
            >
                <div
                    class="w-28 h-28 bg-orange-50 rounded-2xl overflow-hidden flex-shrink-0"
                >
                    <img
                        :src="
                            product.image
                                ? '/storage/' + product.image
                                : 'https://ui-avatars.com/api/?name=' +
                                  product.name +
                                  '&background=ffedd5&color=f97316'
                        "
                        class="w-full h-full object-cover group-hover:scale-110 transition-duration-500"
                    />
                </div>

                <div class="flex-1">
                    <h3
                        class="text-xl font-bold text-gray-800 group-hover:text-orange-500"
                    >
                        {{ product.name }}
                    </h3>
                    <p class="text-gray-400 text-sm line-clamp-1 mt-1">
                        {{ product.description }}
                    </p>
                    <div class="flex items-center justify-between mt-4">
                        <span class="text-xl font-black text-orange-500"
                            >{{ Number(product.price).toLocaleString() }}đ</span
                        >
                        <div
                            class="bg-orange-100 text-orange-600 p-2 rounded-xl group-hover:bg-orange-500 group-hover:text-white transition-colors"
                        >
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M12 4v16m8-8H4"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div
        v-if="isModalOpen"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
    >
        <div
            class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
            @click="closeModal"
        ></div>

        <div
            class="relative bg-white w-full max-w-2xl rounded-[3rem] overflow-hidden shadow-2xl animate-fade-in flex flex-col max-h-[90vh]"
        >
            <button
                @click="closeModal"
                class="absolute top-6 right-6 z-10 bg-white/80 backdrop-blur-md p-2 rounded-full shadow-lg hover:bg-orange-500 hover:text-white transition-all"
            >
                <svg
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path d="M6 18L18 6M6 6l12 12" stroke-width="2.5" />
                </svg>
            </button>

            <div class="overflow-y-auto">
                <div class="h-64 sm:h-80 w-full relative">
                    <img
                        :src="
                            selectedProduct.image
                                ? '/storage/' + selectedProduct.image
                                : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800'
                        "
                        class="w-full h-full object-cover"
                    />
                </div>

                <div class="p-8 sm:p-10">
                    <div class="mb-8">
                        <h2
                            class="text-3xl font-black text-gray-800 italic uppercase tracking-tight"
                        >
                            {{ selectedProduct.name }}
                        </h2>
                        <p class="text-gray-500 mt-2 leading-relaxed">
                            {{
                                selectedProduct.description ||
                                "Món ăn tuyệt vời được chế biến từ nguyên liệu tươi ngon nhất."
                            }}
                        </p>
                    </div>

                    <div
                        v-if="Object.keys(groupedOptions).length > 0"
                        class="space-y-8"
                    >
                        <div
                            v-for="(options, groupName) in groupedOptions"
                            :key="groupName"
                        >
                            <h4
                                class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mb-4 flex items-center gap-2"
                            >
                                <span
                                    class="w-2 h-2 bg-orange-500 rounded-full"
                                ></span>
                                {{ groupName }}
                            </h4>
                            <div class="grid grid-cols-1 gap-3">
                                <label
                                    v-for="opt in options"
                                    :key="opt.id"
                                    class="flex items-center justify-between p-4 rounded-2xl border-2 cursor-pointer transition-all"
                                    :class="
                                        selectedOptions.includes(opt.id)
                                            ? 'border-orange-500 bg-orange-50'
                                            : 'border-gray-50 bg-gray-50 hover:border-orange-200'
                                    "
                                >
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="checkbox"
                                            :value="opt.id"
                                            v-model="selectedOptions"
                                            class="w-5 h-5 text-orange-500 border-gray-300 rounded-lg focus:ring-orange-500"
                                        />
                                        <span class="font-bold text-gray-700">{{
                                            opt.option_value
                                        }}</span>
                                    </div>
                                    <span class="font-black text-orange-500"
                                        >+{{
                                            Number(
                                                opt.additional_price,
                                            ).toLocaleString()
                                        }}đ</span
                                    >
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="p-8 border-t border-gray-100 bg-gray-50 flex items-center justify-between"
            >
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">
                        Tổng cộng
                    </p>
                    <p class="text-3xl font-black text-gray-800">
                        {{ totalPrice.toLocaleString() }}đ
                    </p>
                </div>
                <button
                    @click="addToCart"
                    class="bg-orange-500 text-white px-10 py-4 rounded-[1.5rem] font-black shadow-xl shadow-orange-200 hover:bg-gray-900 transition-all active:scale-95"
                >
                    THÊM VÀO GIỎ
                </button>
            </div>
        </div>
    </div>
</template>
