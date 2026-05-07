<script setup>
import RestaurantLayout from "@/Layouts/RestaurantLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import { useToast } from "vue-toastification";
import InputError from "@/Components/InputError.vue";

defineOptions({ layout: RestaurantLayout });

const props = defineProps({
    product: Object,
    categories: Array,
});
const toast = useToast();

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
    // Hàm phụ để cắt bỏ giây nếu có (06:00:00 -> 06:00)
    const formatTime = (time) => {
        if (!time) return null;
        return time.split(":").slice(0, 2).join(":");
    };

    // Cập nhật lại giá trị trong form trước khi gửi
    form.available_from = formatTime(form.available_from);
    form.available_to = formatTime(form.available_to);

    form.post(route("restaurant.products.update", props.product.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.success("Cập nhật món ăn thành công! ✨");
        },
        onError: (errors) => {
            console.log("Lỗi cụ thể:", errors);
            toast.error("Cập nhật thất bại! 😥");
        },
    });
};

// Drag & Drop cho ảnh đại diện
const isDragging = ref(false);

const handleDragOver = (e) => {
    e.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = (e) => {
    e.preventDefault();
    isDragging.value = false;
};

const handleDrop = (e) => {
    e.preventDefault();
    isDragging.value = false;
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        handleImageChange({ target: { files: files } });
    }
};
</script>

<template>
    <Head :title="'Chỉnh sửa: ' + product.name" />

    <div class="space-y-10 pb-20 animate-fade-in-up">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">
                    Chỉnh sửa món 🛠️
                </h2>
                <p class="text-[10px] font-black text-blue-600 uppercase tracking-[0.25em] mt-2 bg-blue-100/50 w-fit px-4 py-1.5 rounded-xl border border-blue-200/50">
                    Đang điều chỉnh: {{ product.name }}
                </p>
            </div>
            <Link
                :href="route('restaurant.products.index')"
                class="group flex items-center gap-3 px-6 py-3 bg-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-900 hover:text-white transition-all shadow-[0_4px_20px_rgb(0,0,0,0.05)] border border-slate-100"
            >
                <span class="group-hover:-translate-x-1 transition-transform">←</span>
                Quay lại
            </Link>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <!-- Thông tin cơ bản -->
                <div class="bg-white p-8 md:p-10 rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 space-y-8">
                    <h3 class="text-lg font-black text-slate-800 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center">1</span>
                        Thông tin cơ bản
                    </h3>
                    
                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="space-y-3 relative group">
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest block transition-colors group-focus-within:text-blue-500">Tên món ăn</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full bg-slate-50 border border-transparent hover:border-slate-200 rounded-2xl p-4 font-bold text-slate-800 focus:bg-white focus:border-blue-300 focus:ring-4 focus:ring-blue-100 transition-all outline-none"
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="space-y-3 relative group">
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest block transition-colors group-focus-within:text-blue-500">Danh mục món</label>
                            <select
                                v-model="form.category_id"
                                class="w-full bg-slate-50 border border-transparent hover:border-slate-200 rounded-2xl p-4 font-bold text-slate-800 focus:bg-white focus:border-blue-300 focus:ring-4 focus:ring-blue-100 transition-all outline-none h-[56px] appearance-none"
                            >
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.category_id" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="space-y-3 relative group">
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest block transition-colors group-focus-within:text-blue-500">Giá niêm yết trên App (đ)</label>
                            <div class="relative">
                                <input
                                    v-model="form.price"
                                    type="number"
                                    class="w-full bg-slate-50 border border-transparent hover:border-slate-200 rounded-2xl p-4 font-black text-xl text-orange-600 focus:bg-white focus:border-blue-300 focus:ring-4 focus:ring-blue-100 transition-all outline-none"
                                />
                                <span class="absolute right-5 top-1/2 -translate-y-1/2 font-black text-slate-300 text-sm tracking-widest uppercase">VND</span>
                            </div>
                            <div v-if="form.price > 0" class="text-[10px] font-bold text-slate-500 mt-2 bg-slate-50 p-3 rounded-xl border border-slate-100 leading-relaxed">
                                💡 <span class="text-emerald-600">Thực nhận: {{ new Intl.NumberFormat('vi-VN').format(form.price * 0.75) }}đ</span><br>
                                <span class="text-slate-400 font-medium">(Đã trừ 25% chiết khấu nền tảng. Chưa bao gồm thuế 4.5%.)</span>
                            </div>
                            <InputError :message="form.errors.price" />
                        </div>

                        <div class="space-y-3">
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest block">Trạng thái phục vụ</label>
                            <div
                                @click="form.is_available = !form.is_available"
                                :class="form.is_available ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700'"
                                class="flex items-center justify-between p-4 rounded-2xl border-2 cursor-pointer transition-all h-[56px] hover:shadow-md"
                            >
                                <div class="flex items-center gap-3">
                                    <span class="text-xl">{{ form.is_available ? "✅" : "❌" }}</span>
                                    <span class="font-black text-[10px] uppercase tracking-widest">
                                        {{ form.is_available ? "Đang bán" : "Tạm ngưng" }}
                                    </span>
                                </div>
                                <div :class="form.is_available ? 'bg-emerald-500' : 'bg-rose-500'" class="w-12 h-6 rounded-full relative transition-colors shadow-inner">
                                    <div :class="form.is_available ? 'translate-x-6' : 'translate-x-1'" class="absolute top-1 bg-white w-4 h-4 rounded-full transition-transform shadow-sm"></div>
                                </div>
                            </div>
                            <InputError :message="form.errors.is_available" />
                        </div>
                    </div>

                    <div class="space-y-3 relative group">
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest block transition-colors group-focus-within:text-blue-500">Mô tả món ăn</label>
                        <textarea
                            v-model="form.description"
                            rows="4"
                            class="w-full bg-slate-50 border border-transparent hover:border-slate-200 rounded-2xl p-5 font-medium text-sm text-slate-700 focus:bg-white focus:border-blue-300 focus:ring-4 focus:ring-blue-100 transition-all outline-none resize-none"
                        ></textarea>
                        <InputError :message="form.errors.description" />
                    </div>
                </div>

                <!-- Lựa chọn thêm -->
                <div class="bg-white p-8 md:p-10 rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
                    <div class="flex justify-between items-center mb-8">
                        <h3 class="text-lg font-black text-slate-800 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-500 flex items-center justify-center">2</span>
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

                    <div class="space-y-6">
                        <transition-group name="list">
                            <div
                                v-for="(opt, index) in form.options"
                                :key="index"
                                class="flex flex-wrap md:flex-nowrap gap-4 items-start bg-slate-50 p-5 rounded-[2rem] border border-slate-200 transition-all hover:border-blue-300 group shadow-sm hover:shadow-md"
                            >
                                <div class="relative w-16 h-16 flex-shrink-0">
                                    <div
                                        @click="$refs['optImage' + index][0].click()"
                                        class="w-full h-full bg-white rounded-2xl border-2 border-dashed border-slate-300 flex items-center justify-center cursor-pointer overflow-hidden hover:border-blue-400 transition-all"
                                    >
                                        <img v-if="opt.preview" :src="opt.preview" class="w-full h-full object-cover" />
                                        <svg v-else class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <input type="file" :ref="'optImage' + index" class="hidden" @change="handleOptionImage($event, index)" accept="image/*" />
                                </div>

                                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <input v-model="opt.option_name" placeholder="Tên nhóm (VD: Size)" class="w-full bg-white border border-slate-200 rounded-xl p-3 text-sm font-bold shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none" />
                                    </div>
                                    <div>
                                        <input v-model="opt.option_value" placeholder="Lựa chọn (VD: Lớn)" class="w-full bg-white border border-slate-200 rounded-xl p-3 text-sm font-bold shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none" />
                                    </div>
                                    <div>
                                        <input v-model="opt.additional_price" type="number" placeholder="+ Giá tiền" class="w-full bg-white border border-slate-200 rounded-xl p-3 text-sm font-black text-orange-600 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none" />
                                    </div>
                                </div>

                                <button
                                    @click="removeOption(index)"
                                    type="button"
                                    class="w-10 h-10 bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center mt-1"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </transition-group>
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <!-- Upload Hình ảnh -->
                <div class="bg-white p-8 rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 text-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-b from-blue-50/50 to-transparent pointer-events-none"></div>
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-4 block relative z-10">Ảnh đại diện hiện tại</label>
                    <div
                        @click="$refs.mainImage.click()"
                        @dragover="handleDragOver"
                        @dragleave="handleDragLeave"
                        @drop="handleDrop"
                        :class="[
                            'aspect-square rounded-[2rem] border-2 border-dashed flex flex-col items-center justify-center cursor-pointer transition-all overflow-hidden relative group z-10',
                            isDragging ? 'bg-blue-100 border-blue-500 shadow-[0_0_20px_rgba(59,130,246,0.2)]' : 'bg-slate-50 border-slate-200 hover:border-blue-400 hover:bg-blue-50/30'
                        ]"
                    >
                        <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover animate-fade-in" />
                        <div v-if="imagePreview" class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center text-white font-black text-[10px] uppercase tracking-widest z-20 backdrop-blur-sm">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Cập nhật ảnh
                            </div>
                        </div>
                    </div>
                    <input type="file" ref="mainImage" class="hidden" @change="handleImageChange" accept="image/*" />
                </div>

                <!-- Upload Gallery -->
                <div class="bg-white p-8 rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-4 block">Album chi tiết</label>
                    <div class="grid grid-cols-3 gap-3">
                        <div v-for="(img, i) in galleryPreviews" :key="i" class="relative group aspect-square rounded-2xl overflow-hidden shadow-sm border border-slate-100">
                            <img :src="img.url" class="w-full h-full object-cover" />
                            <button
                                type="button"
                                @click="removeGalleryImage(i)"
                                class="absolute top-1.5 right-1.5 bg-rose-500/90 backdrop-blur-md text-white p-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-all shadow-md hover:bg-rose-600"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <button
                            type="button"
                            @click="$refs.galleryInput.click()"
                            class="aspect-square bg-slate-50 text-slate-400 rounded-2xl border-2 border-dashed border-slate-200 flex items-center justify-center text-xl hover:border-blue-400 hover:bg-blue-50 hover:text-blue-500 transition-all"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </button>
                    </div>
                    <input type="file" ref="galleryInput" class="hidden" @change="handleGalleryChange" multiple accept="image/*" />
                </div>

                <!-- Cài đặt bổ sung -->
                <div class="bg-slate-900 p-8 rounded-[2.5rem] shadow-xl text-white relative overflow-hidden">
                    <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 16px 16px;"></div>
                    <div class="relative z-10 space-y-6">
                        <h3 class="font-black text-xs uppercase tracking-widest text-blue-400 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Giờ phục vụ
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[9px] font-bold text-slate-400 uppercase">Mở cửa</label>
                                <input v-model="form.available_from" type="time" class="w-full bg-white/10 border border-white/10 rounded-xl p-3 font-bold text-sm text-white focus:bg-white/20 focus:border-blue-500 outline-none transition-all" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-bold text-slate-400 uppercase">Đóng cửa</label>
                                <input v-model="form.available_to" type="time" class="w-full bg-white/10 border border-white/10 rounded-xl p-3 font-bold text-sm text-white focus:bg-white/20 focus:border-blue-500 outline-none transition-all" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
                    <h3 class="font-black text-xs uppercase tracking-widest text-slate-500 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Giới hạn suất ăn
                    </h3>
                    <div class="space-y-2">
                        <input
                            v-model="form.stock_quantity"
                            type="number"
                            class="w-full bg-slate-50 border border-transparent hover:border-slate-200 rounded-xl p-4 font-bold text-sm text-slate-800 focus:bg-white focus:border-blue-300 focus:ring-4 focus:ring-blue-100 outline-none transition-all"
                        />
                        <p class="text-[9px] text-slate-400 font-medium">* Đặt 999 nếu món không giới hạn số lượng.</p>
                    </div>
                </div>

                <button
                    :disabled="form.processing"
                    type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-5 rounded-[2rem] font-black text-sm uppercase tracking-[0.2em] shadow-[0_10px_30px_rgba(37,99,235,0.3)] hover:shadow-[0_15px_40px_rgba(37,99,235,0.4)] hover:-translate-y-1 transition-all active:scale-95 disabled:opacity-50 flex items-center justify-center gap-2"
                >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ form.processing ? "ĐANG LƯU THAY ĐỔI..." : "LƯU THAY ĐỔI ✅" }}
                </button>
            </div>
        </form>
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

.animate-fade-in {
    animation: fadeIn 0.3s ease-in forwards;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* List Transitions */
.list-enter-active,
.list-leave-active {
    transition: all 0.4s ease;
}
.list-enter-from {
    opacity: 0;
    transform: translateY(30px);
}
.list-leave-to {
    opacity: 0;
    transform: translateX(30px);
}

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button,
input[type="time"]::-webkit-calendar-picker-indicator {
    -webkit-appearance: none;
    margin: 0;
}
</style>
