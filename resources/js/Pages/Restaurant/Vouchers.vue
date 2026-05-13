<script setup>
import RestaurantLayout from "@/Layouts/RestaurantLayout.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref } from "vue";
import Swal from "sweetalert2";

const props = defineProps({
    vouchers: Array,
});

defineOptions({ layout: RestaurantLayout });

const isCreating = ref(false);

const form = useForm({
    code: "",
    discount_type: "fixed",
    discount_value: "",
    expires_at: "",
    max_uses: "",
    minimum_product_price: "",
});

const submitVoucher = () => {
    form.post(route("restaurant.vouchers.store"), {
        preserveScroll: true,
        onSuccess: () => {
            isCreating.value = false;
            form.reset();
            Swal.fire({
                title: "Thành công!",
                text: "Mã giảm giá đã được tạo thành công.",
                icon: "success",
                confirmButtonColor: "#f97316",
                customClass: { popup: "rounded-[2rem]" },
            });
        },
    });
};

const deleteVoucher = (id) => {
    Swal.fire({
        title: "Xác nhận xóa?",
        text: "Mã giảm giá này sẽ bị vô hiệu hóa.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        cancelButtonColor: "#94a3b8",
        confirmButtonText: "Xóa ngay",
        cancelButtonText: "Hủy",
        customClass: { popup: "rounded-[2rem]" },
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route("restaurant.vouchers.destroy", id), {
                preserveScroll: true,
            });
        }
    });
};
</script>

<template>
    <Head title="Quản lý Khuyến Mãi" />

    <div class="space-y-8 animate-fade-in-up pb-10">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-2">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">
                    Mã Giảm Giá Của Quán
                </h2>
                <p class="text-slate-500 font-medium mt-1">Tự tạo mã khuyến mãi để tăng doanh số bán hàng.</p>
            </div>
            
            <button 
                v-if="!isCreating"
                @click="isCreating = true"
                class="bg-slate-900 hover:bg-orange-600 text-white px-6 py-3 rounded-2xl font-black text-sm transition-all shadow-[0_8px_20px_rgba(0,0,0,0.1)] hover:shadow-[0_8px_20px_rgba(249,115,22,0.3)] hover:-translate-y-1 active:translate-y-0 flex items-center justify-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Tạo mã mới
            </button>
        </div>

        <!-- Create Form -->
        <div v-if="isCreating" class="bg-white rounded-[2rem] p-6 md:p-8 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] animate-fade-in-up relative overflow-hidden">
            <div class="absolute right-0 top-0 w-32 h-32 bg-orange-50 rounded-bl-[100%] z-0"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-black text-slate-800">Tạo mã giảm giá mới</h3>
                    <button @click="isCreating = false" class="p-2 text-slate-400 hover:text-red-500 bg-slate-50 hover:bg-red-50 rounded-xl transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form @submit.prevent="submitVoucher" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-2">Mã Khuyến Mãi</label>
                        <input v-model="form.code" type="text" placeholder="VD: FREESHIP, GIAM20K..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-black text-slate-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all uppercase">
                        <p class="text-red-500 text-xs mt-1" v-if="form.errors.code">{{ form.errors.code }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-2">Loại giảm giá</label>
                        <select v-model="form.discount_type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all">
                            <option value="fixed">Giảm số tiền cố định (VNĐ)</option>
                            <option value="percent">Giảm theo %</option>
                        </select>
                        <p class="text-red-500 text-xs mt-1" v-if="form.errors.discount_type">{{ form.errors.discount_type }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-2">Giá trị giảm</label>
                        <input v-model="form.discount_value" type="number" placeholder="Nhập số tiền hoặc %" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all">
                        <p class="text-red-500 text-xs mt-1" v-if="form.errors.discount_value">{{ form.errors.discount_value }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-2">Số lượt sử dụng</label>
                        <input v-model="form.max_uses" type="number" placeholder="Nhập 0 để không giới hạn" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all">
                        <p class="text-red-500 text-xs mt-1" v-if="form.errors.max_uses">{{ form.errors.max_uses }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-2">Giá trị sản phẩm tối thiểu (VNĐ)</label>
                        <input v-model="form.minimum_product_price" type="number" placeholder="Để trống nếu không áp dụng" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all">
                        <p class="text-red-500 text-xs mt-1" v-if="form.errors.minimum_product_price">{{ form.errors.minimum_product_price }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-2">Ngày hết hạn</label>
                        <input v-model="form.expires_at" type="date" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all">
                        <p class="text-red-500 text-xs mt-1" v-if="form.errors.expires_at">{{ form.errors.expires_at }}</p>
                    </div>

                    <div class="col-span-1 md:col-span-2 flex justify-end mt-4">
                        <button type="submit" :disabled="form.processing" class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-3 rounded-xl font-black text-sm transition-all flex items-center gap-2 disabled:opacity-50">
                            <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Tạo Mã Giảm Giá
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="vouchers.length === 0 && !isCreating" class="bg-white rounded-[3rem] p-16 md:p-24 border border-slate-100 flex flex-col items-center justify-center text-center shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
            <div class="w-24 h-24 bg-orange-50 rounded-[2rem] flex items-center justify-center mb-6 shadow-inner border border-orange-100/50">
                <span class="text-5xl text-orange-500 font-serif">%</span>
            </div>
            <h3 class="text-2xl font-black text-slate-800 tracking-tight mb-2">Chưa có mã giảm giá nào</h3>
            <p class="text-slate-500 font-medium text-sm">Tạo mã giảm giá để thu hút khách hàng và tăng doanh thu.</p>
        </div>

        <!-- Vouchers List -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div 
                v-for="voucher in vouchers" 
                :key="voucher.id" 
                class="bg-white rounded-[2rem] border-2 border-dashed border-orange-200 p-6 relative group hover:border-orange-400 transition-colors duration-300 flex flex-col justify-between"
            >
                <!-- Decorative Circles -->
                <div class="absolute -left-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-[#f4f7f9] rounded-full border-r-2 border-dashed border-orange-200 group-hover:border-orange-400 transition-colors"></div>
                <div class="absolute -right-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-[#f4f7f9] rounded-full border-l-2 border-dashed border-orange-200 group-hover:border-orange-400 transition-colors"></div>

                <div>
                    <div class="flex items-start justify-between mb-4">
                        <span class="bg-orange-100 text-orange-600 text-xl font-black px-4 py-2 rounded-xl border border-orange-200 tracking-wider">
                            {{ voucher.code }}
                        </span>
                        <button @click="deleteVoucher(voucher.id)" class="w-8 h-8 flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>

                    <h4 class="text-2xl font-black text-slate-800 mb-2">
                        Giảm {{ voucher.discount_type === 'fixed' ? new Intl.NumberFormat('vi-VN').format(voucher.discount_value) + 'đ' : voucher.discount_value + '%' }}
                    </h4>
                    
                    <ul class="text-xs font-bold text-slate-500 space-y-1.5 mb-6">
                        <li v-if="voucher.minimum_product_price > 0" class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Cho sản phẩm từ {{ new Intl.NumberFormat('vi-VN').format(voucher.minimum_product_price) }}đ
                        </li>
                        <li class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Đã dùng: <span class="text-orange-600">{{ voucher.used_count }}</span> / {{ voucher.max_uses === 0 ? 'Không giới hạn' : voucher.max_uses }}
                        </li>
                    </ul>
                </div>

                <div class="pt-4 border-t border-dashed border-orange-100 flex items-center justify-between text-[11px] font-black uppercase tracking-widest">
                    <span class="text-slate-400">HSD:</span>
                    <span :class="new Date(voucher.expires_at) < new Date() ? 'text-red-500' : 'text-slate-700'">
                        {{ new Date(voucher.expires_at).toLocaleDateString('vi-VN') }}
                    </span>
                </div>
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
