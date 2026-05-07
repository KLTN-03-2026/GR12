<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    vouchers: Array,
});

defineOptions({ layout: AdminLayout });

const form = useForm({
    code: "",
    discount_type: "fixed",
    discount_value: 0,
    expires_at: "",
    max_uses: 0,
    minimum_product_price: 0,
});

const activeVouchers = computed(() =>
    props.vouchers.filter(
        (voucher) => new Date(voucher.expires_at) > new Date(),
    ),
);

const totalUses = computed(() => 
    props.vouchers.reduce((sum, v) => sum + (v.used_count || 0), 0)
);

const submitVoucher = () => {
    form.post(route("admin.vouchers.store"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset(
                "code",
                "discount_type",
                "discount_value",
                "expires_at",
                "max_uses",
                "minimum_product_price",
            );
        },
    });
};

const deleteVoucher = (id) => {
    if (confirm("Bạn có chắc chắn muốn thu hồi (xóa) Voucher này? Khách hàng sẽ không thể sử dụng mã này nữa.")) {
        router.delete(route("admin.vouchers.destroy", id), {
            preserveScroll: true
        });
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN').format(value || 0) + 'đ';
};
</script>

<template>
    <Head title="Quản lý Voucher" />

    <div class="space-y-6 max-w-7xl mx-auto">
        <!-- HEADER & STATS -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Quản lý Voucher Khuyến Mãi</h1>
                    <p class="text-sm text-slate-500 mt-2 font-medium">Tạo và kiểm soát các mã giảm giá dành cho khách hàng. Trải nghiệm xem trước thời gian thực (Live Preview).</p>
                </div>
                <div class="flex gap-4">
                    <div class="bg-orange-50 border border-orange-100 px-6 py-4 rounded-2xl flex flex-col items-center justify-center">
                        <span class="text-3xl font-black text-orange-600">{{ activeVouchers.length }}</span>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-orange-400 mt-1">Đang hoạt động</span>
                    </div>
                    <div class="bg-blue-50 border border-blue-100 px-6 py-4 rounded-2xl flex flex-col items-center justify-center">
                        <span class="text-3xl font-black text-blue-600">{{ totalUses }}</span>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-blue-400 mt-1">Lượt đã dùng</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            <!-- LEFT: FORM -->
            <div class="xl:col-span-5 space-y-6">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                    <h2 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-2">
                        <span class="text-2xl">✍️</span> Tạo mã mới
                    </h2>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Mã Voucher (Code)</label>
                            <input v-model="form.code" type="text" placeholder="VD: SIEUSALE25" class="w-full rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-black text-orange-600 uppercase outline-none transition focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-50 placeholder:font-medium placeholder:normal-case placeholder:text-slate-400" />
                            <p v-if="form.errors.code" class="text-red-500 text-xs mt-2 font-bold">{{ form.errors.code }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Loại giảm</label>
                                <select v-model="form.discount_type" class="w-full rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-bold text-slate-700 outline-none focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-50">
                                    <option value="fixed">Tiền mặt (đ)</option>
                                    <option value="percent">Phần trăm (%)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Giá trị giảm</label>
                                <input v-model="form.discount_value" type="number" min="1" step="1" placeholder="Nhập số..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-bold text-slate-700 outline-none focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-50" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Giới hạn lượt</label>
                                <input v-model="form.max_uses" type="number" min="0" step="1" placeholder="0 = Vô hạn" class="w-full rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-bold text-slate-700 outline-none focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-50" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Đơn tối thiểu</label>
                                <input v-model="form.minimum_product_price" type="number" min="0" step="1000" placeholder="VD: 50000" class="w-full rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-bold text-slate-700 outline-none focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-50" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Hết hạn lúc</label>
                            <input v-model="form.expires_at" type="datetime-local" class="w-full rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-bold text-slate-700 outline-none focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-50" />
                            <p v-if="form.errors.expires_at" class="text-red-500 text-xs mt-2 font-bold">{{ form.errors.expires_at }}</p>
                        </div>

                        <button @click.prevent="submitVoucher" :disabled="form.processing" class="w-full mt-4 inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-orange-500 to-red-500 px-6 py-4 text-sm font-black uppercase tracking-[0.2em] text-white shadow-xl hover:shadow-2xl hover:scale-[1.02] transition-all disabled:opacity-70 disabled:hover:scale-100">
                            {{ form.processing ? "Đang xử lý..." : "Phát hành Voucher" }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- RIGHT: LIVE PREVIEW & LIST -->
            <div class="xl:col-span-7 space-y-6">
                <!-- LIVE PREVIEW TICKET -->
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
                    <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span> Live Preview
                    </h2>
                    
                    <div class="flex justify-center">
                        <!-- TICKET DESIGN -->
                        <div class="w-full max-w-sm bg-gradient-to-br from-orange-500 to-red-600 rounded-[2rem] p-1.5 shadow-2xl transform rotate-1 hover:rotate-0 transition-transform duration-500">
                            <div class="bg-white rounded-[1.5rem] h-full p-6 border-2 border-dashed border-orange-200/50 flex flex-col relative overflow-hidden">
                                <div class="absolute -left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-orange-500 rounded-full shadow-inner"></div>
                                <div class="absolute -right-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-red-600 rounded-full shadow-inner"></div>
                                
                                <div class="flex items-center justify-between mb-4 pl-4 pr-2">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-orange-600 bg-orange-50 px-3 py-1.5 rounded-full">Mã giảm giá</span>
                                    <span class="text-[10px] text-slate-400 font-bold bg-slate-50 px-2 py-1 rounded-md">
                                        HSD: {{ form.expires_at ? new Date(form.expires_at).toLocaleDateString('vi-VN') : 'DD/MM/YYYY' }}
                                    </span>
                                </div>
                                <div class="pl-4 pr-2">
                                    <h3 class="text-3xl font-black text-slate-900 mb-1 tracking-tight">
                                        Giảm {{ form.discount_type === 'percent' ? (form.discount_value || 0) + '%' : formatCurrency(form.discount_value) }}
                                    </h3>
                                    <p class="text-xs text-slate-500 font-medium mb-6">
                                        Đơn tối thiểu {{ formatCurrency(form.minimum_product_price) }}
                                    </p>
                                </div>
                                <div class="mt-auto pt-4 border-t-2 border-dashed border-slate-100 flex items-center justify-between pl-4 pr-2">
                                    <span class="font-mono text-lg font-black tracking-widest text-slate-800 bg-slate-50 px-3 py-1 rounded-lg border border-slate-200">
                                        {{ form.code ? form.code.toUpperCase() : 'XXXXXXX' }}
                                    </span>
                                    <button class="bg-gradient-to-r from-orange-500 to-red-500 text-white px-5 py-2 rounded-xl text-xs font-bold opacity-80 cursor-not-allowed">
                                        Copy
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACTIVE VOUCHERS LIST -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                    <h2 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-2">
                        <span class="text-2xl">🔥</span> Đang hoạt động
                    </h2>
                    
                    <div v-if="activeVouchers.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="voucher in activeVouchers" :key="voucher.id" class="bg-white rounded-2xl p-4 border-2 border-dashed border-slate-200 hover:border-orange-300 transition-colors relative group">
                            <button @click="deleteVoucher(voucher.id)" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-500 hover:text-white" title="Thu hồi mã">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                            <div class="mb-2">
                                <span class="font-mono text-sm font-black text-orange-600 bg-orange-50 px-2 py-1 rounded">{{ voucher.code }}</span>
                            </div>
                            <div class="text-lg font-black text-slate-800 mb-1 tracking-tight">
                                Giảm {{ voucher.discount_type === 'percent' ? voucher.discount_value + '%' : formatCurrency(voucher.discount_value) }}
                            </div>
                            <div class="text-xs text-slate-500 font-medium mb-3">
                                Đơn từ {{ formatCurrency(voucher.minimum_product_price) }}
                            </div>
                            <div class="flex items-center justify-between text-[10px] font-bold text-slate-400 pt-3 border-t border-slate-100">
                                <span>Đã dùng: <span class="text-slate-700">{{ voucher.used_count }}</span>/{{ voucher.max_uses ? voucher.max_uses : '∞' }}</span>
                                <span>HSD: {{ new Date(voucher.expires_at).toLocaleDateString('vi-VN') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="text-center py-12 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <span class="text-4xl block mb-3 opacity-40">📭</span>
                        <p class="text-sm text-slate-500 font-bold">Chưa có voucher nào đang hoạt động.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
