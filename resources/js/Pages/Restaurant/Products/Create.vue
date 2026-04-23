<script setup>
import RestaurantLayout from "@/Layouts/RestaurantLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import InputError from "@/Components/InputError.vue";

// Thiết lập Layout dành cho Restaurant Hub
defineOptions({ layout: RestaurantLayout });

const props = defineProps({
    categories: Array,
});

const imagePreview = ref(null);
const galleryPreviews = ref([]);

const form = useForm({
    name: "",
    category_id: "",
    price: "",
    description: "",
    image: null,
    stock_quantity: 999, // Mặc định số lượng lớn để luôn sẵn sàng
    is_available: true, // Trạng thái cho phép đặt món hay không
    available_from: "06:00",
    available_to: "22:00",
    gallery: [],
    options: [],
});

// 1. Xử lý ảnh đại diện món ăn
const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        if (imagePreview.value) URL.revokeObjectURL(imagePreview.value);
        imagePreview.value = URL.createObjectURL(file);
    }
};

// 2. Xử lý Album ảnh (Gallery)
const handleGalleryChange = (e) => {
    const files = Array.from(e.target.files);
    form.gallery = [...form.gallery, ...files];
    const newPreviews = files.map((file) => URL.createObjectURL(file));
    galleryPreviews.value = [...galleryPreviews.value, ...newPreviews];
};

const removeGalleryItem = (index) => {
    URL.revokeObjectURL(galleryPreviews.value[index]);
    galleryPreviews.value.splice(index, 1);
    form.gallery.splice(index, 1);
};

// 3. Xử lý Toppings (Options) có kèm ảnh
const addOption = () => {
    form.options.push({
        option_name: "",
        option_value: "",
        additional_price: 0,
        image: null,
        preview: null,
    });
};

const removeOption = (index) => {
    if (form.options[index].preview)
        URL.revokeObjectURL(form.options[index].preview);
    form.options.splice(index, 1);
};

const handleOptionImage = (e, index) => {
    const file = e.target.files[0];
    if (file) {
        form.options[index].image = file;
        if (form.options[index].preview)
            URL.revokeObjectURL(form.options[index].preview);
        form.options[index].preview = URL.createObjectURL(file);
    }
};

const triggerOptionFileInput = (index) => {
    const el = document.getElementById("opt-file-" + index);
    if (el) el.click();
};

// 4. Gửi dữ liệu về Server
const submit = () => {
    form.post(route("restaurant.products.store"), {
        forceFormData: true,
        onStart: () => {
            // Có thể thêm loading state ở đây
        },
        onSuccess: () => {
            // Thông báo thành công do Watcher ở Index lo (Cách 1)
        },
        onError: () => {
            toast.error("Vui lòng kiểm tra lại các thông tin nhé! 😥");
        },
    });
};
</script>

<template>
    <Head title="Thêm món ăn mới" />

    <div
        class="space-y-10 pb-20 animate-in fade-in slide-in-from-bottom-4 duration-700"
    >
        <div
            class="flex flex-col md:flex-row md:items-end justify-between gap-4"
        >
            <div>
                <h2
                    class="text-5xl font-black text-gray-900 italic tracking-tighter uppercase leading-none"
                >
                    Thêm món mới 🍕
                </h2>
                <p
                    class="text-[10px] font-black text-orange-400 uppercase tracking-[0.3em] mt-3 bg-orange-50 w-fit px-3 py-1 rounded-lg"
                >
                    Cập nhật thực đơn FoodXpress
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
                                placeholder="Ví dụ: Bún Đậu Mắm Tôm Đặc Biệt"
                                class="w-full bg-gray-50 border-none rounded-2xl p-5 font-bold text-gray-800 focus:ring-4 focus:ring-orange-100 transition-all"
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="space-y-3">
                            <label
                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-2"
                                >Danh mục món</label
                            >
                            <select
                                v-model="form.category_id"
                                class="w-full bg-gray-50 border-none rounded-2xl p-5 font-bold text-gray-800 focus:ring-4 focus:ring-orange-100 transition-all h-[64px]"
                            >
                                <option value="" disabled>Chọn loại món</option>
                                <option
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :value="cat.id"
                                >
                                    {{ cat.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.category_id" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <label
                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-2"
                                >Giá niêm yết (đ)</label
                            >
                            <div class="relative">
                                <input
                                    v-model="form.price"
                                    type="number"
                                    class="w-full bg-gray-50 border-none rounded-2xl p-5 font-black text-xl text-orange-600 focus:ring-4 focus:ring-orange-100 transition-all"
                                />
                                <span
                                    class="absolute right-5 top-1/2 -translate-y-1/2 font-black text-gray-300 font-sans"
                                    >VND</span
                                >
                            </div>
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
                                                ? "Sẵn sàng bán"
                                                : "Tạm ngưng nhận đơn"
                                        }}
                                    </span>
                                </div>
                                <div
                                    :class="
                                        form.is_available
                                            ? 'bg-green-500'
                                            : 'bg-red-500'
                                    "
                                    class="w-10 h-5 rounded-full relative transition-colors shadow-inner"
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
                            <InputError :message="form.errors.is_available" />
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label
                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-2"
                            >Mô tả hấp dẫn</label
                        >
                        <textarea
                            v-model="form.description"
                            rows="4"
                            class="w-full bg-gray-50 border-none rounded-[2rem] p-6 font-medium text-sm text-gray-600 focus:ring-4 focus:ring-orange-100 transition-all"
                            placeholder="Mô tả sự hấp dẫn của món ăn..."
                        ></textarea>
                        <InputError :message="form.errors.description" />
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
                            class="bg-orange-50 text-orange-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-orange-500 hover:text-white transition-all shadow-sm"
                        >
                            + Thêm lựa chọn
                        </button>
                    </div>

                    <div class="space-y-6">
                        <div
                            v-for="(opt, index) in form.options"
                            :key="index"
                            class="flex flex-wrap md:flex-nowrap gap-4 items-start bg-gray-50/50 p-5 rounded-[2rem] border border-dashed border-gray-200 transition-all hover:border-orange-200 group"
                        >
                            <div class="relative w-14 h-14 flex-shrink-0 mt-1">
                                <div
                                    @click="triggerOptionFileInput(index)"
                                    class="w-full h-full bg-white rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center cursor-pointer overflow-hidden hover:border-orange-400 transition-all"
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
                                    :id="'opt-file-' + index"
                                    class="hidden"
                                    @change="handleOptionImage($event, index)"
                                    accept="image/*"
                                />
                            </div>

                            <div
                                class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-2"
                            >
                                <div>
                                    <input
                                        v-model="opt.option_name"
                                        placeholder="Tên (VD: Thêm)"
                                        class="w-full bg-white border-none rounded-xl p-3 text-xs font-bold shadow-sm"
                                    />
                                    <InputError
                                        :message="
                                            form.errors[
                                                `options.${index}.option_name`
                                            ]
                                        "
                                    />
                                </div>
                                <div>
                                    <input
                                        v-model="opt.option_value"
                                        placeholder="Giá trị (VD: Trứng)"
                                        class="w-full bg-white border-none rounded-xl p-3 text-xs font-bold shadow-sm"
                                    />
                                    <InputError
                                        :message="
                                            form.errors[
                                                `options.${index}.option_value`
                                            ]
                                        "
                                    />
                                </div>
                                <div>
                                    <input
                                        v-model="opt.additional_price"
                                        type="number"
                                        placeholder="+ Giá"
                                        class="w-full bg-white border-none rounded-xl p-3 text-xs font-black text-orange-600 shadow-sm"
                                    />
                                    <InputError
                                        :message="
                                            form.errors[
                                                `options.${index}.additional_price`
                                            ]
                                        "
                                    />
                                </div>
                            </div>

                            <button
                                @click="removeOption(index)"
                                type="button"
                                class="w-10 h-10 bg-red-50 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition-all flex items-center justify-center mt-1"
                            >
                                ✕
                            </button>
                        </div>
                        <div
                            v-if="form.options.length === 0"
                            class="text-center py-10 border-2 border-dashed border-gray-50 rounded-[2.5rem]"
                        >
                            <p
                                class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em]"
                            >
                                Món ăn này chưa có topping
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <div
                    class="bg-white p-8 rounded-[3rem] shadow-sm border border-gray-50 text-center"
                >
                    <label
                        class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6 block italic tracking-widest"
                        >Hình đại diện món</label
                    >
                    <div
                        @click="$refs.mainImage.click()"
                        class="aspect-square bg-gray-50 rounded-[2.5rem] border-4 border-dashed border-gray-100 flex flex-col items-center justify-center cursor-pointer hover:border-orange-200 transition-all overflow-hidden relative group"
                    >
                        <img
                            v-if="imagePreview"
                            :src="imagePreview"
                            class="w-full h-full object-cover animate-in fade-in duration-500"
                        />
                        <div
                            v-else
                            class="text-gray-300 flex flex-col items-center"
                        >
                            <span class="text-5xl mb-4">📸</span>
                            <span
                                class="text-[9px] font-black uppercase tracking-[0.3em]"
                                >Chọn ảnh</span
                            >
                        </div>
                    </div>
                    <input
                        type="file"
                        ref="mainImage"
                        class="hidden"
                        @change="handleImageChange"
                        accept="image/*"
                    />
                    <InputError :message="form.errors.image" />
                </div>

                <div
                    class="bg-white p-8 rounded-[3rem] shadow-sm border border-gray-50"
                >
                    <label
                        class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6 block italic"
                        >Album ảnh món ăn</label
                    >
                    <div class="grid grid-cols-3 gap-3">
                        <div
                            v-for="(p, i) in galleryPreviews"
                            :key="i"
                            class="relative group aspect-square rounded-2xl overflow-hidden shadow-sm border-2 border-white"
                        >
                            <img :src="p" class="w-full h-full object-cover" />
                            <button
                                @click="removeGalleryItem(i)"
                                type="button"
                                class="absolute top-1 right-1 bg-red-500 text-white p-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity"
                            >
                                ✕
                            </button>
                        </div>
                        <button
                            type="button"
                            @click="$refs.galleryInput.click()"
                            class="aspect-square bg-gray-50 text-gray-300 rounded-2xl border-2 border-dashed border-gray-200 flex items-center justify-center text-2xl hover:bg-orange-50 hover:text-orange-400 transition-all"
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
                        class="font-black text-sm italic uppercase tracking-[0.2em] text-orange-400 text-center"
                    >
                        Giờ phục vụ
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label
                                class="text-[9px] font-black text-gray-500 uppercase"
                                >Từ</label
                            >
                            <input
                                v-model="form.available_from"
                                type="time"
                                class="w-full bg-white/10 border-none rounded-xl p-3 font-black text-sm text-white focus:ring-orange-500"
                            />
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[9px] font-black text-gray-500 uppercase"
                                >Đến</label
                            >
                            <input
                                v-model="form.available_to"
                                type="time"
                                class="w-full bg-white/10 border-none rounded-xl p-3 font-black text-sm text-white focus:ring-orange-500"
                            />
                        </div>
                    </div>
                    <InputError :message="form.errors.available_from" />
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
                                    class="w-full bg-gray-50 border-none rounded-xl p-4 font-bold text-sm focus:ring-2 focus:ring-orange-100"
                                />
                                <p
                                    class="text-[8px] text-gray-400 font-medium italic"
                                >
                                    * Mặc định 999 nghĩa là không giới hạn.
                                </p>
                            </div>
                        </div>
                    </details>
                </div>

                <button
                    :disabled="form.processing"
                    type="submit"
                    class="w-full bg-orange-500 text-white py-6 rounded-[2.5rem] font-black text-sm uppercase tracking-[0.4em] shadow-2xl shadow-orange-100 hover:bg-gray-900 transition-all active:scale-95 disabled:opacity-50"
                >
                    {{
                        form.processing
                            ? "ĐANG LƯU HỆ THỐNG..."
                            : "LÊN KỆ NGAY 🚀"
                    }}
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
