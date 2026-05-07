<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    withdrawals: Object,
    filters: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN').format(value || 0) + 'đ';
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('vi-VN');
};

const form = useForm({});
const rejectForm = useForm({
    reason: 'Ngân hàng từ chối do sai thông tin'
});

const selectedTx = ref(null);
const showApproveModal = ref(false);
const showRejectModal = ref(false);

const openApprove = (tx) => {
    selectedTx.value = tx;
    showApproveModal.value = true;
};

const openReject = (tx) => {
    selectedTx.value = tx;
    showRejectModal.value = true;
};

const submitApprove = () => {
    form.post(route('admin.withdrawals.approve', selectedTx.value.id), {
        onSuccess: () => {
            showApproveModal.value = false;
            selectedTx.value = null;
        }
    });
};

const submitReject = () => {
    rejectForm.post(route('admin.withdrawals.reject', selectedTx.value.id), {
        onSuccess: () => {
            showRejectModal.value = false;
            selectedTx.value = null;
            rejectForm.reset();
        }
    });
};

const getStatusColor = (status) => {
    switch (status) {
        case 'pending': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'completed': return 'bg-emerald-100 text-emerald-800 border-emerald-200';
        case 'rejected': return 'bg-rose-100 text-rose-800 border-rose-200';
        default: return 'bg-slate-100 text-slate-800 border-slate-200';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'pending': return 'Chờ xử lý';
        case 'completed': return 'Đã chuyển tiền';
        case 'rejected': return 'Đã từ chối (Hoàn tiền)';
        default: return status;
    }
};
</script>

<template>
    <Head title="Quản lý Rút tiền - Admin" />

    <AdminLayout>
        <div class="mb-6 flex justify-between items-end">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Yêu cầu rút tiền</h1>
                <p class="text-sm text-slate-500 mt-1 font-medium">Duyệt và xử lý các yêu cầu rút tiền từ Quán ăn & Tài xế</p>
            </div>
            <div class="flex gap-2">
                <a :href="route('admin.withdrawals.index')" class="px-4 py-2 rounded-lg text-sm font-bold" :class="!filters.status ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 border'">Tất cả</a>
                <a :href="route('admin.withdrawals.index', { status: 'pending' })" class="px-4 py-2 rounded-lg text-sm font-bold" :class="filters.status === 'pending' ? 'bg-yellow-500 text-white' : 'bg-white text-slate-600 border'">Chờ xử lý</a>
                <a :href="route('admin.withdrawals.index', { status: 'completed' })" class="px-4 py-2 rounded-lg text-sm font-bold" :class="filters.status === 'completed' ? 'bg-emerald-500 text-white' : 'bg-white text-slate-600 border'">Đã hoàn thành</a>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100">
            <div v-if="withdrawals.data.length === 0" class="text-center py-10">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl">💸</span>
                </div>
                <p class="text-slate-500 font-medium">Chưa có yêu cầu rút tiền nào.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs font-black text-slate-400 uppercase tracking-widest">
                            <th class="py-4 px-4">Thời gian</th>
                            <th class="py-4 px-4">Người dùng</th>
                            <th class="py-4 px-4">Số tiền</th>
                            <th class="py-4 px-4 w-1/3">Nội dung / Số TK</th>
                            <th class="py-4 px-4">Trạng thái</th>
                            <th class="py-4 px-4 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="tx in withdrawals.data" :key="tx.id" class="hover:bg-slate-50/50 transition-colors group">
                            <td class="py-4 px-4">
                                <span class="text-sm font-bold text-slate-700">{{ formatDate(tx.created_at) }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center overflow-hidden">
                                        <img v-if="tx.user.avatar" :src="'/storage/' + tx.user.avatar" class="w-full h-full object-cover">
                                        <span v-else class="text-xs font-bold text-slate-500">{{ tx.user.name.charAt(0) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-900">{{ tx.user.name }}</p>
                                        <p class="text-[10px] font-bold text-slate-500 uppercase">{{ tx.user.role }} - {{ tx.user.phone }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-sm font-black text-rose-500">{{ formatCurrency(Math.abs(tx.amount)) }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed font-medium">{{ tx.description }}</p>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border"
                                    :class="getStatusColor(tx.status)">
                                    {{ getStatusLabel(tx.status) }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-2" v-if="tx.status === 'pending'">
                                    <button @click="openApprove(tx)" class="px-3 py-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white rounded-lg text-xs font-black transition-colors">Duyệt</button>
                                    <button @click="openReject(tx)" class="px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white rounded-lg text-xs font-black transition-colors">Từ chối</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="withdrawals.links && withdrawals.data.length > 0" class="mt-6 flex justify-center gap-2">
                <template v-for="(link, pIndex) in withdrawals.links" :key="pIndex">
                    <a v-if="link.url" :href="link.url"
                        class="px-4 py-2 rounded-xl text-sm font-bold transition-colors"
                        :class="link.active ? 'bg-slate-900 text-white shadow-md' : 'bg-slate-50 text-slate-500 hover:bg-slate-100'"
                        v-html="link.label"></a>
                    <span v-else class="px-4 py-2 rounded-xl text-sm font-bold bg-transparent text-slate-400"
                        v-html="link.label"></span>
                </template>
            </div>
        </div>

        <!-- Approve Modal -->
        <div v-if="showApproveModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showApproveModal = false"></div>
            <div class="bg-white rounded-[2rem] w-full max-w-md relative z-10 overflow-hidden shadow-2xl">
                <div class="p-6 text-center border-b border-slate-100">
                    <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl">💸</span>
                    </div>
                    <h3 class="text-lg font-black text-slate-900">Xác nhận đã chuyển khoản?</h3>
                    <p class="text-sm text-slate-500 mt-2">Bạn xác nhận đã chuyển số tiền <strong class="text-rose-500">{{ formatCurrency(Math.abs(selectedTx?.amount)) }}</strong> cho tài khoản của <strong>{{ selectedTx?.user.name }}</strong>?</p>
                </div>
                <div class="p-6 bg-slate-50">
                    <div class="bg-white p-4 rounded-xl border border-slate-100 mb-6 text-sm">
                        <p class="font-bold text-slate-700 mb-1">Thông tin chuyển khoản:</p>
                        <p class="text-slate-600">{{ selectedTx?.description }}</p>
                    </div>
                    <div class="flex gap-3">
                        <button @click="showApproveModal = false" class="flex-1 py-3.5 rounded-xl font-bold text-slate-500 hover:bg-slate-100 transition-colors">Hủy</button>
                        <button @click="submitApprove" :disabled="form.processing" class="flex-1 py-3.5 rounded-xl font-black text-white bg-emerald-500 shadow-lg shadow-emerald-500/30 hover:bg-emerald-600 transition-colors disabled:opacity-50">Xác nhận</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showRejectModal = false"></div>
            <div class="bg-white rounded-[2rem] w-full max-w-md relative z-10 overflow-hidden shadow-2xl">
                <div class="p-6 text-center border-b border-slate-100">
                    <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl">⛔</span>
                    </div>
                    <h3 class="text-lg font-black text-slate-900">Từ chối và Hoàn tiền?</h3>
                    <p class="text-sm text-slate-500 mt-2">Yêu cầu rút tiền này sẽ bị từ chối và <strong class="text-emerald-500">{{ formatCurrency(Math.abs(selectedTx?.amount)) }}</strong> sẽ được hoàn lại vào ví của người dùng.</p>
                </div>
                <form @submit.prevent="submitReject" class="p-6 bg-slate-50">
                    <div class="mb-6">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Lý do từ chối (Tùy chọn)</label>
                        <input type="text" v-model="rejectForm.reason" class="w-full bg-white border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-rose-500 outline-none" placeholder="Ví dụ: Sai thông tin ngân hàng">
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="showRejectModal = false" class="flex-1 py-3.5 rounded-xl font-bold text-slate-500 hover:bg-slate-100 transition-colors">Hủy</button>
                        <button type="submit" :disabled="rejectForm.processing" class="flex-1 py-3.5 rounded-xl font-black text-white bg-rose-500 shadow-lg shadow-rose-500/30 hover:bg-rose-600 transition-colors disabled:opacity-50">Từ chối & Hoàn tiền</button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
