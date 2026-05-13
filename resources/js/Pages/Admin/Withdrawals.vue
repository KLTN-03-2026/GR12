<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    withdrawals: Object,
    stats: Object,
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
    reason: 'Sai thông tin ngân hàng'
});

const selectedTx = ref(null);
const showApproveModal = ref(false);
const showRejectModal = ref(false);
const copiedField = ref(null);

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

const copyToClipboard = (text, fieldName) => {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        copiedField.value = fieldName;
        setTimeout(() => {
            copiedField.value = null;
        }, 2000);
    });
};

const getStatusColor = (status) => {
    switch (status) {
        case 'pending': return 'bg-amber-100 text-amber-700 border-amber-200';
        case 'completed': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        case 'rejected': return 'bg-rose-100 text-rose-700 border-rose-200';
        default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'pending': return 'Chờ chuyển khoản';
        case 'completed': return 'Đã thanh toán';
        case 'rejected': return 'Đã từ chối';
        default: return status;
    }
};
</script>

<template>
    <Head title="Quản lý Rút tiền - Admin" />

    <AdminLayout>
        <div class="space-y-6 pb-10">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">Xét duyệt Rút tiền</h2>
                    <p class="text-slate-500 text-sm mt-1 font-medium">Xử lý yêu cầu rút doanh thu từ đối tác Quán ăn và Shipper.</p>
                </div>
                
                <!-- Filter Tabs -->
                <div class="flex bg-slate-200/50 p-1 rounded-xl w-max">
                    <a :href="route('admin.withdrawals.index')" class="px-5 py-2 rounded-lg text-sm font-bold transition-all" :class="!filters.status ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700'">Tất cả</a>
                    <a :href="route('admin.withdrawals.index', { status: 'pending' })" class="px-5 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2" :class="filters.status === 'pending' ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20' : 'text-slate-500 hover:text-slate-700'">
                        Chờ duyệt <span v-if="stats.pending_count > 0" class="px-2 py-0.5 bg-white/20 rounded-full text-xs">{{ stats.pending_count }}</span>
                    </a>
                    <a :href="route('admin.withdrawals.index', { status: 'completed' })" class="px-5 py-2 rounded-lg text-sm font-bold transition-all" :class="filters.status === 'completed' ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20' : 'text-slate-500 hover:text-slate-700'">Đã xong</a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl p-5 shadow-lg shadow-orange-500/20 text-white relative overflow-hidden">
                    <div class="absolute right-0 bottom-0 opacity-10 translate-x-4 translate-y-4">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    </div>
                    <div class="text-white/80 text-xs font-black uppercase tracking-widest mb-1">Cần thanh toán ngay</div>
                    <div class="text-3xl font-black">{{ formatCurrency(stats.pending_amount) }}</div>
                    <div class="text-sm font-medium mt-1 text-white/90">Từ {{ stats.pending_count }} yêu cầu đang chờ</div>
                </div>
                
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-col justify-center">
                    <div class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1 flex justify-between">Đã thanh toán (Tổng) <span class="text-emerald-500 text-lg">✅</span></div>
                    <div class="text-2xl font-black text-slate-800">{{ formatCurrency(stats.completed_amount) }}</div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-col justify-center">
                    <div class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1 flex justify-between">Số lượt rút thành công <span class="text-blue-500 text-lg">📈</span></div>
                    <div class="text-2xl font-black text-slate-800">{{ stats.total_completed }} <span class="text-sm font-medium text-slate-400">giao dịch</span></div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-visible relative z-0">
                <div v-if="withdrawals.data.length === 0" class="text-center py-16">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-slate-100">
                        <span class="text-4xl opacity-50">☕</span>
                    </div>
                    <h3 class="text-lg font-black text-slate-800">Mọi thứ đều hoàn tất!</h3>
                    <p class="text-slate-500 font-medium mt-1">Hiện không có yêu cầu rút tiền nào cần xử lý.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-100 text-xs font-black text-slate-400 uppercase tracking-widest">
                                <th class="p-5 rounded-tl-3xl">Đối tác</th>
                                <th class="p-5">Số tiền yêu cầu</th>
                                <th class="p-5">Thông tin Ngân hàng chuyển tới</th>
                                <th class="p-5">Thời gian</th>
                                <th class="p-5">Trạng thái</th>
                                <th class="p-5 text-right rounded-tr-3xl">Xử lý</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="tx in withdrawals.data" :key="tx.id" class="hover:bg-slate-50/50 transition-colors group">
                                <td class="p-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center overflow-hidden shadow-inner border border-slate-100">
                                            <img v-if="tx.user.avatar" :src="tx.user.avatar.startsWith('http') ? tx.user.avatar : '/storage/' + tx.user.avatar" class="w-full h-full object-cover">
                                            <span v-else class="text-sm font-black text-slate-500">{{ tx.user.name.charAt(0) }}</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-slate-800 group-hover:text-amber-600 transition-colors">{{ tx.user.name }}</p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest" :class="tx.user.role === 'restaurant' ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600'">
                                                    {{ tx.user.role === 'restaurant' ? 'Quán ăn' : 'Tài xế' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-5">
                                    <span class="text-lg font-black text-rose-500 bg-rose-50 px-3 py-1.5 rounded-xl border border-rose-100">{{ formatCurrency(Math.abs(tx.amount)) }}</span>
                                </td>
                                <td class="p-5">
                                    <div v-if="tx.user.bank_account" class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded">{{ tx.user.bank_name || 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-black text-slate-700 tracking-wider">{{ tx.user.bank_account }}</span>
                                            <button @click="copyToClipboard(tx.user.bank_account, `acc_${tx.id}`)" class="text-slate-400 hover:text-orange-500 transition-colors" title="Sao chép STK">
                                                <svg v-if="copiedField === `acc_${tx.id}`" class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                            </button>
                                        </div>
                                        <p class="text-xs font-bold text-slate-500 uppercase">{{ tx.user.bank_account_name || tx.user.name }}</p>
                                    </div>
                                    <div v-else class="text-xs text-amber-600 bg-amber-50 px-3 py-2 rounded-lg inline-block border border-amber-100 font-medium">
                                        {{ tx.description }}
                                    </div>
                                </td>
                                <td class="p-5">
                                    <span class="text-sm font-medium text-slate-600">{{ formatDate(tx.created_at) }}</span>
                                </td>
                                <td class="p-5">
                                    <span class="px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border"
                                        :class="getStatusColor(tx.status)">
                                        {{ getStatusLabel(tx.status) }}
                                    </span>
                                </td>
                                <td class="p-5 text-right">
                                    <div class="flex items-center justify-end gap-2" v-if="tx.status === 'pending'">
                                        <button @click="openApprove(tx)" class="px-4 py-2 bg-emerald-500 text-white hover:bg-emerald-600 rounded-xl text-xs font-black transition-all shadow-md shadow-emerald-500/20 hover:-translate-y-0.5">Duyệt chuyển</button>
                                        <button @click="openReject(tx)" class="p-2 bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white rounded-xl transition-all" title="Từ chối & Hoàn tiền">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="withdrawals.links && withdrawals.links.length > 3" class="p-5 border-t border-slate-100 bg-slate-50/50 rounded-b-3xl flex justify-center gap-1.5 flex-wrap">
                    <template v-for="(link, pIndex) in withdrawals.links" :key="pIndex">
                        <a v-if="link.url" :href="link.url"
                            class="min-w-[2.5rem] h-10 px-2 flex items-center justify-center rounded-xl text-sm font-bold transition-all"
                            :class="link.active ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100'"
                            v-html="link.label"></a>
                        <span v-else class="min-w-[2.5rem] h-10 px-2 flex items-center justify-center rounded-xl text-sm font-bold bg-transparent text-slate-300"
                            v-html="link.label"></span>
                    </template>
                </div>
            </div>
        </div>

        <!-- Approve Modal (Fast Copier) -->
        <div v-if="showApproveModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showApproveModal = false"></div>
            <div class="bg-white rounded-[2rem] w-full max-w-md relative z-10 overflow-hidden shadow-2xl transform transition-all">
                <div class="bg-emerald-500 p-6 text-center text-white relative overflow-hidden">
                    <div class="absolute inset-0 opacity-10 flex items-center justify-center">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                    </div>
                    <h3 class="text-xl font-black relative z-10">Thanh toán Giải ngân</h3>
                    <p class="text-emerald-100 text-sm mt-1 font-medium relative z-10">Mở app Ngân hàng và chuyển chính xác số tiền sau</p>
                    <div class="text-4xl font-black mt-3 relative z-10 tracking-tight flex items-center justify-center gap-2 cursor-pointer hover:text-emerald-100 transition-colors" @click="copyToClipboard(Math.abs(selectedTx?.amount), 'amount')">
                        {{ formatCurrency(Math.abs(selectedTx?.amount)) }}
                        <svg v-if="copiedField === 'amount'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        <svg v-else class="w-5 h-5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                    </div>
                </div>
                
                <div class="p-6">
                    <!-- Bank details for easy copying -->
                    <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 mb-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Tên Ngân Hàng</span>
                            <span class="text-sm font-black text-slate-800">{{ selectedTx?.user.bank_name || 'Không rõ' }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center cursor-pointer group" @click="copyToClipboard(selectedTx?.user.bank_account, 'modal_acc')">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-widest group-hover:text-emerald-500 transition-colors">Số Tài Khoản</span>
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-black text-slate-800 tracking-wider group-hover:text-emerald-600 transition-colors">{{ selectedTx?.user.bank_account || 'Không rõ' }}</span>
                                <span v-if="copiedField === 'modal_acc'" class="text-emerald-500 text-xs font-bold">Đã chép!</span>
                                <svg v-else class="w-4 h-4 text-slate-400 group-hover:text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                            </div>
                        </div>

                        <div class="flex justify-between items-center border-t border-slate-200/60 pt-4">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Chủ Tài Khoản</span>
                            <span class="text-sm font-black text-slate-800 uppercase">{{ selectedTx?.user.bank_account_name || selectedTx?.user.name }}</span>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button @click="showApproveModal = false" class="flex-1 py-3.5 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors uppercase tracking-widest text-xs">Chưa chuyển</button>
                        <button @click="submitApprove" :disabled="form.processing" class="flex-1 py-3.5 rounded-xl font-black text-white bg-emerald-500 shadow-lg shadow-emerald-500/30 hover:bg-emerald-600 transition-all uppercase tracking-widest text-xs disabled:opacity-50">Đã Chuyển Xong</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showRejectModal = false"></div>
            <div class="bg-white rounded-[2rem] w-full max-w-md relative z-10 overflow-hidden shadow-2xl">
                <div class="p-6 text-center border-b border-slate-100 relative">
                    <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl">⛔</span>
                    </div>
                    <h3 class="text-xl font-black text-slate-900">Từ chối và Hoàn tiền?</h3>
                    <p class="text-sm text-slate-500 mt-2 font-medium">Yêu cầu rút tiền này sẽ bị hủy bỏ và <strong class="text-rose-500">{{ formatCurrency(Math.abs(selectedTx?.amount)) }}</strong> sẽ được trả lại vào ví trên hệ thống của người dùng.</p>
                </div>
                <form @submit.prevent="submitReject" class="p-6">
                    <div class="mb-6">
                        <label class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2 block">Lý do từ chối (Bắt buộc)</label>
                        <input type="text" v-model="rejectForm.reason" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none font-medium transition-colors" placeholder="Ví dụ: Sai thông tin ngân hàng">
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="showRejectModal = false" class="flex-1 py-3.5 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors uppercase tracking-widest text-xs">Hủy</button>
                        <button type="submit" :disabled="rejectForm.processing" class="flex-1 py-3.5 rounded-xl font-black text-white bg-rose-500 shadow-lg shadow-rose-500/30 hover:bg-rose-600 transition-all uppercase tracking-widest text-xs disabled:opacity-50">Hoàn Tiền</button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
