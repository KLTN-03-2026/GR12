<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object,
});

defineOptions({ layout: AdminLayout });

const form = useForm({
    platform_commission: props.settings.platform_commission || 20,
    base_shipping_fee: props.settings.base_shipping_fee || 15000,
    extra_shipping_fee_per_km: props.settings.extra_shipping_fee_per_km || 3000,
    base_radius_km: props.settings.base_radius_km || 2,
});

const submit = () => {
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Cấu hình Hệ thống" />

    <div class="space-y-6 pb-10 max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Cấu hình Hệ thống</h2>
                <p class="text-slate-500 text-sm mt-1">Thay đổi các thông số lõi của hệ thống FoodXpress</p>
            </div>
            <button @click="submit" :disabled="form.processing" class="px-6 py-2.5 bg-gradient-to-r from-orange-500 to-red-500 text-white font-bold rounded-xl shadow-lg hover:shadow-orange-500/30 hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50">
                <span v-if="form.processing">Đang lưu...</span>
                <span v-else>💾 Lưu thay đổi</span>
            </button>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Tài chính / Doanh thu -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center text-xl">💰</div>
                    <h3 class="text-lg font-bold text-slate-800">Tài chính & Hoa hồng</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Phần trăm hoa hồng quán ăn (%)</label>
                        <div class="relative">
                            <input type="number" v-model="form.platform_commission" class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors" required min="0" max="100" />
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400 font-bold">%</div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Mức chiết khấu hệ thống sẽ thu từ các đơn hàng thành công.</p>
                        <div v-if="form.errors.platform_commission" class="text-red-500 text-xs mt-1">{{ form.errors.platform_commission }}</div>
                    </div>
                </div>
            </div>

            <!-- Vận chuyển / Phí Ship -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-xl">🛵</div>
                    <h3 class="text-lg font-bold text-slate-800">Thông số Vận chuyển</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Phí giao hàng cơ bản (VNĐ)</label>
                        <input type="number" v-model="form.base_shipping_fee" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors" required min="0" />
                        <p class="text-xs text-slate-500 mt-2">Áp dụng cho khoảng cách cơ bản (dưới {{ form.base_radius_km }}km).</p>
                        <div v-if="form.errors.base_shipping_fee" class="text-red-500 text-xs mt-1">{{ form.errors.base_shipping_fee }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Bán kính cơ bản (Km)</label>
                        <input type="number" v-model="form.base_radius_km" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors" required min="0" />
                        <div v-if="form.errors.base_radius_km" class="text-red-500 text-xs mt-1">{{ form.errors.base_radius_km }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Phụ phí mỗi Km tiếp theo (VNĐ)</label>
                        <input type="number" v-model="form.extra_shipping_fee_per_km" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors" required min="0" />
                        <p class="text-xs text-slate-500 mt-2">Số tiền cộng thêm cho mỗi km vượt quá {{ form.base_radius_km }}km.</p>
                        <div v-if="form.errors.extra_shipping_fee_per_km" class="text-red-500 text-xs mt-1">{{ form.errors.extra_shipping_fee_per_km }}</div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
