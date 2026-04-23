<script setup>
import RestaurantLayout from "@/Layouts/RestaurantLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import InputError from "@/Components/InputError.vue";

defineOptions({ layout: RestaurantLayout });

const props = defineProps({
    product: Object,
    categories: Array,
});

// Previews cho ảnh chính
const imagePreview = ref(
    props.product.image ? `/storage/${props.product.image}` : null,
);

// Previews cho Album ảnh
const galleryPreviews = ref(
    props.product.gallery?.map((img) => ({
        id: img.id,
        url: `/storage/${img.image_path}`,
        isExisting: true,
    })) || [],
);

const form = useForm({
    _method: "PUT",
    name: props.product.name,
    category_id: props.product.category_id,
    price: props.product.price,
    description: props.product.description || "",
    image: null,
    stock_quantity: props.product.stock_quantity ?? 999,
    is_available: Boolean(props.product.is_available),
    available_from: props.product.available_from || "06:00",
    available_to: props.product.available_to || "22:00",
    gallery: [],
    delete_gallery: [],
    options:
        props.product.options?.map((opt) => ({
            option_name: opt.option_name,
            option_value: opt.option_value,
            additional_price: opt.additional_price,
            existing_image: opt.image,
            preview: opt.image ? `/storage/${opt.image}` : null,
            image: null,
        })) || [],
});

// 1. Thay đổi ảnh chính
const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        if (imagePreview.value && !imagePreview.value.startsWith("/storage")) {
            URL.revokeObjectURL(imagePreview.value);
        }
        imagePreview.value = URL.createObjectURL(file);
    }
};

// 2. Album Gallery Logic
const handleGalleryChange = (e) => {
    const files = Array.from(e.target.files);
    form.gallery = [...form.gallery, ...files];
    files.forEach((file) => {
        galleryPreviews.value.push({
            url: URL.createObjectURL(file),
            isExisting: false,
        });
    });
};

const removeGalleryImage = (index) => {
    const item = galleryPreviews.value[index];
    if (item.isExisting) {
        form.delete_gallery.push(item.id);
    } else {
        const fileIndex = form.gallery.findIndex(
            (f) => URL.createObjectURL(f) === item.url,
        );
        if (fileIndex > -1) form.gallery.splice(fileIndex, 1);
    }
    galleryPreviews.value.splice(index, 1);
};

// 3. Toppings Logic
const addOption = () => {
    form.options.push({
        option_name: "",
        option_value: "",
        additional_price: 0,
        image: null,
        preview: null,
    });
};

const handleOptionImage = (e, index) => {
    const file = e.target.files[0];
    if (file) {
        form.options[index].image = file;
        form.options[index].preview = URL.createObjectURL(file);
    }
};

const removeOption = (index) => form.options.splice(index, 1);

const submit = () => {
    form.post(route("restaurant.products.update", props.product.id), {
        forceFormData: true,
        preserveScroll: true,
        onError: () => {
            toast.error("Cập nhật thất bại, hãy kiểm tra lại các ô nhập! 😥");
        },
    });
};
</script>

<template>
    <Head :title="'Chỉnh sửa: ' + product.name" />

    <div class="space-y-10 pb-20 animate-in fade-in duration-700">
        <div
            class="flex flex-col md:flex-row md:items-end justify-between gap-4"
        >
            <div>
                <h2
                    class="text-5xl font-black text-gray-900 italic tracking-tighter uppercase leading-none"
                >
                    Chỉnh sửa món 🛠️
                </h2>
                <p
                    class="text-[10px] font-black text-blue-500 uppercase tracking-[0.3em] mt-3 bg-blue-50 w-fit px-3 py-1 rounded-lg"
                >
                    Đang điều chỉnh: {{ product.name }}
                </p>
            </div>
            <Link
                :href="route('restaurant.products.index')"
                class="group flex items-center gap-3 px-6 py-3 bg-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-gray-900 hover:text-white transition-all shadow-sm border border-gray-100"
            >
                <span class="group-hover:-translate-x-1 transition-transform"
                    >←</span
                >
                Quay lại
            </Link>
        </div>

        <form
            @submit.prevent="submit"
            class="grid grid-cols-1 lg:grid-cols-3 gap-8"
        >
            <div class="lg:col-span-2 space-y-8">
                <div
                    class="bg-white p-10 rounded-[3rem] shadow-sm border border-gray-50 space-y-8"
                >
                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <label
                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-2"
                                >Tên món ăn</label
                            >
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full bg-gray-50 border-none rounded-2xl p-5 font-bold text-gray-800 focus:ring-4 focus:ring-blue-100 transition-all"
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="space-y-3">
                            <label
                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-2"
                                >Danh mục</label
                            >
                            <select
                                v-model="form.category_id"
                                class="w-full bg-gray-50 border-none rounded-2xl p-5 font-bold text-gray-800 focus:ring-4 focus:ring-blue-100 transition-all h-[64px]"
                            >
                                <option
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :value="cat.id"
                                >
                                    {{ cat.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <label
                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-2"
                                >Giá bán (đ)</label
                            >
                            <input
                                v-model="form.price"
                                type="number"
                                class="w-full bg-gray-50 border-none rounded-2xl p-5 font-black text-xl text-orange-600 focus:ring-4 focus:ring-blue-100 transition-all"
                            />
                            <InputError :message="form.errors.price" />
                        </div>

                        <div class="space-y-3">
                            <label
                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-2"
                                >Trạng thái phục vụ</label
                            >
                            <div
                                @click="form.is_available = !form.is_available"
                                :class="
                                    form.is_available
                                        ? 'bg-green-50 border-green-200 text-green-600'
                                        : 'bg-red-50 border-red-200 text-red-600'
                                "
                                class="flex items-center justify-between p-4 rounded-2xl border-2 border-dashed cursor-pointer transition-all h-[64px]"
                            >
                                <div class="flex items-center gap-3">
                                    <span class="text-xl">{{
                                        form.is_available ? "✅" : "❌"
                                    }}</span>
                                    <span
                                        class="font-black text-[10px] uppercase tracking-widest"
                                    >
                                        {{
                                            form.is_available
                                                ? "Đang bán"
                                                : "Tạm ngưng"
                                        }}
                                    </span>
                                </div>
                                <div
                                    :class="
                                        form.is_available
                                            ? 'bg-green-500'
                                            : 'bg-red-500'
                                    "
                                    class="w-10 h-5 rounded-full relative transition-colors"
                                >
                                    <div
                                        :class="
                                            form.is_available
                                                ? 'translate-x-5'
                                                : 'translate-x-1'
                                        "
                                        class="absolute top-1 bg-white w-3 h-3 rounded-full transition-transform shadow-sm"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label
                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-2"
                            >Mô tả</label
                        >
                        <textarea
                            v-model="form.description"
                            rows="4"
                            class="w-full bg-gray-50 border-none rounded-[2rem] p-6 font-medium text-sm text-gray-600 focus:ring-4 focus:ring-blue-100 transition-all"
                        ></textarea>
                    </div>
                </div>

                <div
                    class="bg-white p-10 rounded-[3rem] shadow-sm border border-gray-50"
                >
                    <div class="flex justify-between items-center mb-8">
                        <h3
                            class="font-black text-xl italic uppercase tracking-tighter text-gray-800"
                        >
                            Toppings & Lựa chọn
                        </h3>
                        <button
                            type="button"
                            @click="addOption"
                            class="bg-blue-50 text-blue-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-500 hover:text-white transition-all shadow-sm"
                        >
                            + Thêm tùy chọn
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(opt, index) in form.options"
                            :key="index"
                            class="flex flex-wrap md:flex-nowrap gap-4 items-center bg-gray-50/50 p-5 rounded-[2rem] border border-dashed border-gray-200 group"
                        >
                            <div class="relative w-14 h-14 flex-shrink-0">
                                <div
                                    @click="
                                        $refs['optImage' + index][0].click()
                                    "
                                    class="w-full h-full bg-white rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center cursor-pointer overflow-hidden hover:border-blue-400 transition-all"
                                >
                                    <img
                                        v-if="opt.preview"
                                        :src="opt.preview"
                                        class="w-full h-full object-cover"
                                    />
                                    <span v-else class="text-xl">📸</span>
                                </div>
                                <input
                                    type="file"
                                    :ref="'optImage' + index"
                                    class="hidden"
                                    @change="handleOptionImage($event, index)"
                                    accept="image/*"
                                />
                            </div>
                            <input
                                v-model="opt.option_name"
                                placeholder="Tên (VD: Size)"
                                class="flex-1 bg-white border-none rounded-xl p-3 text-xs font-bold shadow-sm"
                            />
                            <input
                                v-model="opt.option_value"
                                placeholder="Giá trị (VD: Lớn)"
                                class="flex-1 bg-white border-none rounded-xl p-3 text-xs font-bold shadow-sm"
                            />
                            <input
                                v-model="opt.additional_price"
                                type="number"
                                placeholder="+ Giá"
                                class="w-28 bg-white border-none rounded-xl p-3 text-xs font-black text-orange-600 shadow-sm"
                            />
                            <button
                                @click="removeOption(index)"
                                type="button"
                                class="w-10 h-10 bg-red-50 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition-all flex items-center justify-center"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <div
                    class="bg-white p-8 rounded-[3rem] shadow-sm border border-gray-50 text-center"
                >
                    <label
                        class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6 block italic"
                        >Ảnh đại diện hiện tại</label
                    >
                    <div
                        @click="$refs.mainImage.click()"
                        class="aspect-square bg-gray-50 rounded-[2.5rem] border-4 border-dashed border-gray-100 flex flex-col items-center justify-center cursor-pointer overflow-hidden relative group"
                    >
                        <img
                            v-if="imagePreview"
                            :src="imagePreview"
                            class="w-full h-full object-cover animate-in fade-in duration-500"
                        />
                        <div
                            class="absolute inset-0 bg-blue-600/80 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center text-white font-black text-[10px] uppercase tracking-widest"
                        >
                            Thay đổi ảnh
                        </div>
                    </div>
                    <input
                        type="file"
                        ref="mainImage"
                        class="hidden"
                        @change="handleImageChange"
                        accept="image/*"
                    />
                </div>

                <div
                    class="bg-white p-8 rounded-[3rem] shadow-sm border border-gray-50"
                >
                    <label
                        class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6 block italic"
                        >Album chi tiết</label
                    >
                    <div class="grid grid-cols-3 gap-3">
                        <div
                            v-for="(img, i) in galleryPreviews"
                            :key="i"
                            class="relative group aspect-square rounded-2xl overflow-hidden shadow-sm border-2 border-white"
                        >
                            <img
                                :src="img.url"
                                class="w-full h-full object-cover"
                            />
                            <button
                                type="button"
                                @click="removeGalleryImage(i)"
                                class="absolute top-1 right-1 bg-red-500 text-white p-1 rounded-lg opacity-0 group-hover:opacity-100 transition-all text-[8px]"
                            >
                                Xóa
                            </button>
                        </div>
                        <button
                            type="button"
                            @click="$refs.galleryInput.click()"
                            class="aspect-square bg-gray-50 text-gray-300 rounded-2xl border-2 border-dashed border-gray-200 flex items-center justify-center text-2xl hover:bg-blue-50"
                        >
                            +
                        </button>
                    </div>
                    <input
                        type="file"
                        ref="galleryInput"
                        class="hidden"
                        @change="handleGalleryChange"
                        multiple
                        accept="image/*"
                    />
                </div>

                <div
                    class="bg-gray-900 p-8 rounded-[3rem] shadow-xl text-white space-y-6"
                >
                    <h3
                        class="font-black text-sm italic uppercase tracking-[0.2em] text-blue-400 text-center"
                    >
                        Giờ phục vụ
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label
                                class="text-[9px] font-black text-gray-500 uppercase"
                                >Mở cửa</label
                            >
                            <input
                                v-model="form.available_from"
                                type="time"
                                class="w-full bg-white/10 border-none rounded-xl p-3 font-black text-sm text-white focus:ring-blue-500"
                            />
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[9px] font-black text-gray-500 uppercase"
                                >Đóng cửa</label
                            >
                            <input
                                v-model="form.available_to"
                                type="time"
                                class="w-full bg-white/10 border-none rounded-xl p-3 font-black text-sm text-white focus:ring-blue-500"
                            />
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white p-8 rounded-[3rem] shadow-sm border border-gray-50"
                >
                    <details class="group">
                        <summary
                            class="list-none flex justify-between items-center cursor-pointer"
                        >
                            <h3
                                class="font-black text-[10px] italic uppercase tracking-widest text-gray-400"
                            >
                                Giới hạn suất ăn (Nếu có)
                            </h3>
                            <span
                                class="text-gray-300 group-open:rotate-180 transition-transform"
                                >▼</span
                            >
                        </summary>
                        <div class="mt-6 space-y-4">
                            <div class="space-y-2">
                                <label
                                    class="text-[9px] font-black text-gray-400 uppercase"
                                    >Số lượng suất tối đa/ngày</label
                                >
                                <input
                                    v-model="form.stock_quantity"
                                    type="number"
                                    class="w-full bg-gray-50 border-none rounded-xl p-4 font-bold text-sm focus:ring-2 focus:ring-blue-100"
                                />
                            </div>
                        </div>
                    </details>
                </div>

                <button
                    :disabled="form.processing"
                    type="submit"
                    class="w-full bg-blue-600 text-white py-6 rounded-[2.5rem] font-black text-sm uppercase tracking-[0.4em] shadow-2xl shadow-blue-100 hover:bg-gray-900 transition-all active:scale-95"
                >
                    {{ form.processing ? "ĐANG LƯU..." : "LƯU THAY ĐỔI ✅" }}
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
details summary::-webkit-details-marker {
    display: none;
}
</style>
