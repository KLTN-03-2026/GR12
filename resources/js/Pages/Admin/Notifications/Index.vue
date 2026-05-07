<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const form = useForm({
    title: '',
    message: '',
    target_audience: 'all',
    type: 'info'
});

const submit = () => {
    form.post(route('admin.notifications.send'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Gửi thông báo - Admin" />

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
            <h1 class="text-3xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                <span class="text-4xl">📢</span> 
                Gửi <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-pink-500">Thông báo</span>
            </h1>
            <p class="text-slate-500 font-medium mt-2">Phát thông báo hệ thống đến tất cả người dùng hoặc theo nhóm.</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Target Audience -->
                <div class="space-y-2">
                    <label class="text-sm font-black text-slate-700 uppercase tracking-widest">Đối tượng nhận</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all duration-300"
                            :class="form.target_audience === 'all' ? 'border-orange-500 bg-orange-50' : 'border-slate-100 hover:border-orange-200 bg-white'">
                            <input type="radio" v-model="form.target_audience" value="all" class="hidden">
                            <span class="text-2xl">🌍</span>
                            <span class="font-bold text-slate-700">Tất cả mọi người</span>
                        </label>
                        <label class="flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all duration-300"
                            :class="form.target_audience === 'customer' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-100 hover:border-indigo-200 bg-white'">
                            <input type="radio" v-model="form.target_audience" value="customer" class="hidden">
                            <span class="text-2xl">👤</span>
                            <span class="font-bold text-slate-700">Khách hàng</span>
                        </label>
                        <label class="flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all duration-300"
                            :class="form.target_audience === 'restaurant' ? 'border-rose-500 bg-rose-50' : 'border-slate-100 hover:border-rose-200 bg-white'">
                            <input type="radio" v-model="form.target_audience" value="restaurant" class="hidden">
                            <span class="text-2xl">🏪</span>
                            <span class="font-bold text-slate-700">Quán ăn</span>
                        </label>
                        <label class="flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all duration-300"
                            :class="form.target_audience === 'shipper' ? 'border-emerald-500 bg-emerald-50' : 'border-slate-100 hover:border-emerald-200 bg-white'">
                            <input type="radio" v-model="form.target_audience" value="shipper" class="hidden">
                            <span class="text-2xl">🛵</span>
                            <span class="font-bold text-slate-700">Tài xế (Shipper)</span>
                        </label>
                    </div>
                </div>

                <!-- Type -->
                <div class="space-y-2">
                    <label class="text-sm font-black text-slate-700 uppercase tracking-widest">Loại thông báo</label>
                    <div class="flex flex-col gap-3">
                        <label class="flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all duration-300"
                            :class="form.type === 'info' ? 'border-blue-500 bg-blue-50' : 'border-slate-100 hover:border-blue-200 bg-white'">
                            <input type="radio" v-model="form.type" value="info" class="hidden">
                            <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-black">i</span>
                            <div>
                                <span class="font-bold text-slate-700 block">Thông tin chung (Info)</span>
                                <span class="text-xs text-slate-500">Thông báo bình thường, tin tức</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all duration-300"
                            :class="form.type === 'success' ? 'border-emerald-500 bg-emerald-50' : 'border-slate-100 hover:border-emerald-200 bg-white'">
                            <input type="radio" v-model="form.type" value="success" class="hidden">
                            <span class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-black">✓</span>
                            <div>
                                <span class="font-bold text-slate-700 block">Thành công / Tích cực</span>
                                <span class="text-xs text-slate-500">Ví dụ: Chương trình thưởng, Voucher mới</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all duration-300"
                            :class="form.type === 'warning' ? 'border-amber-500 bg-amber-50' : 'border-slate-100 hover:border-amber-200 bg-white'">
                            <input type="radio" v-model="form.type" value="warning" class="hidden">
                            <span class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center font-black">!</span>
                            <div>
                                <span class="font-bold text-slate-700 block">Cảnh báo / Quan trọng</span>
                                <span class="text-xs text-slate-500">Ví dụ: Bảo trì hệ thống, Vi phạm chính sách</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            <div class="space-y-4">
                <div>
                    <label class="text-sm font-black text-slate-700 uppercase tracking-widest mb-2 block">Tiêu đề thông báo *</label>
                    <input 
                        v-model="form.title" 
                        type="text" 
                        required
                        placeholder="VD: Cập nhật chính sách thưởng Tết 2026..."
                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 font-bold focus:ring-4 focus:ring-orange-200 outline-none transition-all"
                        :class="{'border-red-500 bg-red-50': form.errors.title}"
                    >
                    <p v-if="form.errors.title" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.title }}</p>
                </div>

                <div>
                    <label class="text-sm font-black text-slate-700 uppercase tracking-widest mb-2 block">Nội dung chi tiết *</label>
                    <textarea 
                        v-model="form.message" 
                        required
                        rows="5"
                        placeholder="Nhập nội dung chi tiết của thông báo..."
                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 font-medium focus:ring-4 focus:ring-orange-200 outline-none transition-all resize-none"
                        :class="{'border-red-500 bg-red-50': form.errors.message}"
                    ></textarea>
                    <p v-if="form.errors.message" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.message }}</p>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="bg-gradient-to-r from-orange-500 to-pink-500 text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest shadow-lg hover:shadow-orange-500/30 hover:-translate-y-1 transition-all disabled:opacity-50 flex items-center gap-2"
                >
                    <span v-if="form.processing" class="animate-spin text-xl">⏳</span>
                    <span v-else class="text-xl">🚀</span>
                    Gửi Thông Báo Ngay
                </button>
            </div>
        </form>
    </div>
</template>
