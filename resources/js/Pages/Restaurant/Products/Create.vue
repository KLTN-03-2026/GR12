<script setup>
import RestaurantLayout from "@/Layouts/RestaurantLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import { ref } from "vue";

defineProps({ categories: Array });

const form = useForm({
    name: "",
    category_id: "",
    price: "",
    description: "", // Đảm bảo đã có trường mô tả
    image: null,
    gallery: [],
    options: [],
});

// --- 1. Xử lý Ảnh chính (Avatar món) ---
const mainPreview = ref(null);
const handleMainImage = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        mainPreview.value = URL.createObjectURL(file);
    }
};
const removeMainImage = () => {
    form.image = null;
    mainPreview.value = null;
};

// --- 2. Xử lý Gallery (Xóa từng ảnh một) ---
const galleryPreviews = ref([]);
const handleGalleryImages = (e) => {
    const files = Array.from(e.target.files);
    // Thêm tiếp ảnh mới vào mảng cũ thay vì thay thế hoàn toàn
    form.gallery = [...form.gallery, ...files];

    const newPreviews = files.map((file) => URL.createObjectURL(file));
    galleryPreviews.value = [...galleryPreviews.value, ...newPreviews];
};

const removeGalleryImage = (index) => {
    // Xóa trong mảng file gửi lên server
    form.gallery.splice(index, 1);
    // Xóa trong mảng preview hiển thị
    galleryPreviews.value.splice(index, 1);
};

// --- 3. Xử lý Topping ---
const addOption = () => {
    form.options.push({
        option_name: "Topping",
        option_value: "",
        additional_price: 0,
        image: null,
        preview: null,
    });
};

const handleOptionImage = (index, e) => {
    const file = e.target.files[0];
    if (file) {
        form.options[index].image = file;
        form.options[index].preview = URL.createObjectURL(file);
    }
};
const removeOptionImage = (index) => {
    form.options[index].image = null;
    form.options[index].preview = null;
};

const removeOption = (index) => form.options.splice(index, 1);

const submitForm = () => {
    form.post(route("restaurant.products.store"), {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <RestaurantLayout>
        <Head title="Đăng món FoodXpress" />

        <div class="max-w-6xl mx-auto px-4 pb-20 mt-8">
            <form
                @submit.prevent="submitForm"
                class="grid grid-cols-1 lg:grid-cols-12 gap-8"
            >
                <div class="lg:col-span-4 space-y-6">
                    <div
                        class="bg-white p-5 rounded-[2.5rem] border border-orange-100 shadow-sm relative"
                    >
                        <label
                            class="block text-[10px] font-black text-gray-400 uppercase mb-4 text-center tracking-widest"
                            >Ảnh đại diện chính</label
                        >
                        <div
                            class="relative h-64 bg-orange-50 rounded-[2rem] border-2 border-dashed border-orange-200 overflow-hidden group"
                        >
                            <template v-if="mainPreview">
                                <img
                                    :src="mainPreview"
                                    class="w-full h-full object-cover"
                                />
                                <button
                                    type="button"
                                    @click="removeMainImage"
                                    class="absolute top-4 right-4 bg-red-500 text-white p-2 rounded-full shadow-lg hover:bg-red-600 transition scale-90"
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
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </template>
                            <label
                                v-else
                                class="flex flex-col items-center justify-center h-full cursor-pointer hover:bg-orange-100 transition"
                            >
                                <svg
                                    class="w-10 text-orange-300 mb-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        stroke-width="2"
                                    />
                                </svg>
                                <span
                                    class="text-[10px] font-bold text-orange-400 uppercase"
                                    >Chọn ảnh chính</span
                                >
                                <input
                                    type="file"
                                    @change="handleMainImage"
                                    class="hidden"
                                />
                            </label>
                        </div>
                    </div>

                    <div
                        class="bg-white p-5 rounded-[2.5rem] border border-orange-100 shadow-sm"
                    >
                        <label
                            class="block text-[10px] font-black text-gray-400 uppercase mb-4 px-2 tracking-widest"
                            >Gallery Ảnh chi tiết</label
                        >
                        <div class="grid grid-cols-3 gap-2">
                            <div
                                v-for="(src, idx) in galleryPreviews"
                                :key="idx"
                                class="relative aspect-square rounded-xl overflow-hidden border group"
                            >
                                <img
                                    :src="src"
                                    class="w-full h-full object-cover"
                                />
                                <button
                                    type="button"
                                    @click="removeGalleryImage(idx)"
                                    class="absolute top-1 right-1 bg-black/50 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition"
                                >
                                    <svg
                                        class="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                            <label
                                class="aspect-square flex flex-col items-center justify-center border-2 border-dashed border-orange-200 rounded-xl cursor-pointer hover:bg-orange-50 transition"
                            >
                                <span class="text-xl text-orange-400 font-light"
                                    >+</span
                                >
                                <input
                                    type="file"
                                    multiple
                                    @change="handleGalleryImages"
                                    class="hidden"
                                />
                            </label>
                        </div>
                    </div>
                </div>

                <div
                    class="lg:col-span-8 bg-white p-10 rounded-[3rem] border border-orange-100 shadow-sm space-y-8"
                >
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label
                                class="text-xs font-bold text-gray-400 uppercase tracking-widest"
                                >Tên món ăn</label
                            >
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full border-none bg-gray-50 rounded-2xl p-4 mt-1 focus:ring-orange-500"
                            />
                        </div>
                        <div>
                            <label
                                class="text-xs font-bold text-gray-400 uppercase tracking-widest"
                                >Danh mục</label
                            >
                            <select
                                v-model="form.category_id"
                                class="w-full border-none bg-gray-50 rounded-2xl p-4 mt-1 focus:ring-orange-500"
                            >
                                <option value="">Chọn loại</option>
                                <option
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :value="cat.id"
                                >
                                    {{ cat.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="text-xs font-bold text-gray-400 uppercase tracking-widest"
                                >Giá (VNĐ)</label
                            >
                            <input
                                v-model="form.price"
                                type="number"
                                class="w-full border-none bg-gray-50 rounded-2xl p-4 mt-1 focus:ring-orange-500"
                            />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label
                            class="text-xs font-bold text-gray-400 uppercase tracking-widest"
                            >Mô tả hương vị</label
                        >
                        <textarea
                            v-model="form.description"
                            rows="4"
                            placeholder="Nhập mô tả hấp dẫn cho món ăn..."
                            class="w-full border-none bg-gray-50 rounded-[1.5rem] p-5 focus:ring-orange-500 outline-none shadow-inner text-sm"
                        ></textarea>
                        <div
                            v-if="form.errors.description"
                            class="text-red-500 text-[10px]"
                        >
                            {{ form.errors.description }}
                        </div>
                    </div>

                    <div class="pt-6 border-t border-dashed border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <label
                                class="text-sm font-black text-gray-800 uppercase italic tracking-widest"
                                >Topping & Tùy chọn</label
                            >
                            <button
                                type="button"
                                @click="addOption"
                                class="bg-orange-500 text-white px-5 py-2 rounded-full text-[10px] font-black shadow-lg hover:bg-orange-600 transition"
                            >
                                + THÊM MỚI
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="(opt, index) in form.options"
                                :key="index"
                                class="flex items-center gap-4 p-4 bg-gray-50 rounded-[2rem] border border-gray-100 group transition-all hover:bg-white hover:shadow-xl relative"
                            >
                                <div
                                    class="relative w-14 h-14 bg-white rounded-2xl border-2 border-orange-50 overflow-hidden flex-shrink-0 group-hover:border-orange-200 cursor-pointer"
                                >
                                    <template v-if="opt.preview">
                                        <img
                                            :src="opt.preview"
                                            class="w-full h-full object-cover"
                                        />
                                        <button
                                            type="button"
                                            @click="removeOptionImage(index)"
                                            class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition"
                                        >
                                            <span
                                                class="text-[8px] text-white font-bold uppercase tracking-tighter"
                                                >Xóa ảnh</span
                                            >
                                        </button>
                                    </template>
                                    <div
                                        v-else
                                        class="flex items-center justify-center h-full text-[8px] text-gray-400 font-bold uppercase"
                                    >
                                        ẢNH
                                    </div>
                                    <input
                                        type="file"
                                        @change="
                                            (e) => handleOptionImage(index, e)
                                        "
                                        class="absolute inset-0 opacity-0 cursor-pointer"
                                    />
                                </div>

                                <input
                                    v-model="opt.option_name"
                                    type="text"
                                    placeholder="Nhóm"
                                    class="w-24 border-none bg-white rounded-xl text-xs font-bold"
                                />
                                <input
                                    v-model="opt.option_value"
                                    type="text"
                                    placeholder="Tên topping"
                                    class="flex-1 border-none bg-white rounded-xl text-xs"
                                />
                                <input
                                    v-model="opt.additional_price"
                                    type="number"
                                    class="w-24 border-none bg-white rounded-xl text-xs font-black text-orange-500"
                                />

                                <button
                                    @click="removeOption(index)"
                                    type="button"
                                    class="text-gray-300 hover:text-red-500 px-2 transition"
                                >
                                    <svg
                                        class="w-5 h-5"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-6">
                        <Link
                            :href="route('restaurant.products.index')"
                            class="text-xs font-bold text-gray-400 hover:text-red-500 transition"
                            >HỦY BỎ</Link
                        >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-gray-900 text-white px-12 py-5 rounded-[2.5rem] font-black text-sm shadow-2xl hover:bg-orange-600 transition active:scale-95 disabled:opacity-50 tracking-[0.2em]"
                        >
                            {{
                                form.processing
                                    ? "ĐANG ĐĂNG..."
                                    : "XÁC NHẬN ĐĂNG MÓN"
                            }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </RestaurantLayout>
</template>
