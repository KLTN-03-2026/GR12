<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import ShipperLayout from '@/Layouts/ShipperLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    transactions: Object
});

const page = usePage();
const user = page.props.auth.user;

const showDepositModal = ref(false);
const showWithdrawModal = ref(false);

const depositForm = useForm({
    amount: '',
    method: 'vnpay',
    bank_code: 'VNBANK'
});

const withdrawForm = useForm({
    amount: '',
    bank_account: user.bank_account || '',
    bank_name: user.bank_name || '',
    account_name: user.bank_account_name || ''
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN').format(value || 0) + 'đ';
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('vi-VN');
};

const handleDeposit = () => {
    if (!depositForm.amount || depositForm.amount < 10000) {
        alert('Vui lòng nhập số tiền cần nạp (Tối thiểu 10.000đ)');
        return;
    }

    axios.post(route('wallet.deposit'), depositForm.data())
        .then(response => {
            if (response.data.payment_url) {
                window.location.href = response.data.payment_url;
            }
        })
        .catch(error => {
            if (error.response?.status === 422) {
                const errors = error.response.data.errors;
                const firstError = Object.values(errors)[0][0];
                alert(firstError);
            } else {
                alert(error.response?.data?.error || 'Có lỗi xảy ra khi nạp tiền');
            }
        });
};

const handleWithdraw = () => {
    withdrawForm.post(route('wallet.withdraw'), {
        onSuccess: () => {
            showWithdrawModal.value = false;
            withdrawForm.reset();
            alert('Yêu cầu rút tiền thành công. Vui lòng chờ kế toán xử lý.');
        }
    });
};

const getTransactionTypeName = (type) => {
    const types = {
        'deposit': 'Nạp tiền / Hoàn ứng',
        'withdraw': 'Rút tiền',
        'order_revenue': 'Doanh thu đơn hàng',
        'order_payment': 'Ứng trả quán',
        'admin_fee_deduction': 'Trừ phí nền tảng'
    };
    return types[type] || type;
};

const getTransactionColor = (type, amount) => {
    if (amount > 0) return 'text-emerald-500';
    return 'text-rose-500';
};
</script>

<template>
    <Head title="Ví Tài Xế - FoodXpress" />

    <ShipperLayout>
        <div class="space-y-6">
            <!-- Balance Card -->
            <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-[2rem] p-6 shadow-xl text-white relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-32 h-32 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col items-center text-center">
                    <p class="text-[10px] font-bold text-indigo-200 uppercase tracking-[0.2em] mb-2">Số dư khả dụng</p>
                    <p class="text-4xl font-black text-white tracking-tight drop-shadow-md mb-6">
                        {{ formatCurrency(user.wallet_balance) }}
                    </p>
                    
                    <div class="flex items-center gap-3 w-full">
                        <button 
                            @click="showDepositModal = true"
                            class="flex-1 flex flex-col items-center justify-center gap-2 py-4 rounded-2xl bg-white/20 hover:bg-white/30 text-white font-black text-xs uppercase tracking-widest backdrop-blur-sm border border-white/10 transition-all active:scale-95"
                        >
                            <span class="text-2xl">💳</span>
                            Nạp tiền
                        </button>
                        <button 
                            @click="showWithdrawModal = true"
                            class="flex-1 flex flex-col items-center justify-center gap-2 py-4 rounded-2xl bg-indigo-950/40 hover:bg-indigo-950/60 text-white font-black text-xs uppercase tracking-widest backdrop-blur-sm border border-white/5 transition-all active:scale-95"
                        >
                            <span class="text-2xl">🏦</span>
                            Rút tiền
                        </button>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="bg-white rounded-[2rem] p-5 shadow-sm border border-slate-100">
                <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Lịch sử giao dịch</h2>
                
                <div v-if="transactions.data.length === 0" class="text-center py-10">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl">💸</span>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">Chưa có biến động số dư.</p>
                </div>

                <div v-else class="space-y-3">
                    <div 
                        v-for="tx in transactions.data" 
                        :key="tx.id"
                        class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100/50"
                    >
                        <div class="flex items-center gap-3">
                            <div 
                                class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0 shadow-sm"
                                :class="tx.amount > 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'"
                            >
                                {{ tx.amount > 0 ? '↓' : '↑' }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900">{{ getTransactionTypeName(tx.type) }}</p>
                                <p class="text-[10px] text-slate-500 mt-0.5 max-w-[150px] truncate">{{ tx.description }}</p>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-black" :class="getTransactionColor(tx.type, tx.amount)">
                                {{ tx.amount > 0 ? '+' : '' }}{{ formatCurrency(tx.amount) }}
                            </p>
                            <p class="text-[9px] text-slate-400 mt-0.5">{{ formatDate(tx.created_at).split(' ')[1] }}</p>
                        </div>
                    </div>
                </div>
                
                <div v-if="transactions.next_page_url || transactions.prev_page_url" class="mt-4 flex justify-between gap-2">
                    <a v-if="transactions.prev_page_url" :href="transactions.prev_page_url" class="text-xs font-bold text-indigo-600">← Trang trước</a>
                    <span v-else></span>
                    <a v-if="transactions.next_page_url" :href="transactions.next_page_url" class="text-xs font-bold text-indigo-600">Trang sau →</a>
                </div>
            </div>
        </div>

        <!-- Deposit Modal / Bottom Sheet -->
        <div v-if="showDepositModal" class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showDepositModal = false"></div>
            <div class="bg-white rounded-t-[2rem] sm:rounded-[2rem] w-full max-w-md relative z-10 overflow-hidden shadow-2xl pb-safe animate-slide-up">
                <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mt-3 sm:hidden"></div>
                <div class="p-6 text-center border-b border-slate-100">
                    <h3 class="text-lg font-black text-slate-900">Nạp tiền vào ví</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">Số tiền cần nạp (đ)</label>
                        <input 
                            v-model="depositForm.amount"
                            type="number"
                            placeholder="VD: 50000"
                            class="w-full bg-slate-50 border-none rounded-2xl p-4 font-black text-xl text-center focus:ring-2 focus:ring-indigo-500 outline-none"
                        />
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">Phương thức</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label 
                                class="flex flex-col items-center justify-center p-3 rounded-2xl border-2 cursor-pointer transition-all"
                                :class="depositForm.method === 'vnpay' ? 'border-blue-500 bg-blue-50' : 'border-slate-100 bg-white'"
                            >
                                <input type="radio" v-model="depositForm.method" value="vnpay" class="hidden">
                                <span class="font-black text-blue-600 text-sm">VNPay</span>
                            </label>
                            <label class="flex flex-col items-center justify-center p-3 rounded-2xl border-2 border-slate-100 bg-slate-50 cursor-not-allowed opacity-60">
                                <span class="font-black text-pink-500 text-sm mb-0.5">Momo</span>
                                <span class="text-[8px] font-bold text-slate-400 bg-white px-2 py-0.5 rounded-full shadow-sm">Bảo trì</span>
                            </label>
                        </div>
                    </div>
                    <div v-if="depositForm.method === 'vnpay'" class="p-3 bg-blue-50/50 rounded-2xl border border-blue-100/50 mt-2">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">Kênh thanh toán VNPay</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 rounded-xl bg-white border cursor-pointer transition-all"
                                :class="depositForm.bank_code === '' ? 'border-blue-500 shadow-sm' : 'border-slate-100 hover:border-blue-200'">
                                <input type="radio" v-model="depositForm.bank_code" value="" class="hidden">
                                <span class="text-xl">📱</span>
                                <span class="text-sm font-bold text-slate-700">Cổng VNPay (Hỗ trợ quét QR)</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-xl bg-white border cursor-pointer transition-all"
                                :class="depositForm.bank_code === 'VNBANK' ? 'border-blue-500 shadow-sm' : 'border-slate-100 hover:border-blue-200'">
                                <input type="radio" v-model="depositForm.bank_code" value="VNBANK" class="hidden">
                                <span class="text-xl">🏧</span>
                                <div class="flex-1">
                                    <span class="text-sm font-bold text-slate-700 block">Thẻ ATM nội địa (Test Sandbox)</span>
                                    <span class="text-[10px] text-slate-500">Mã OTP test: <strong class="text-blue-600 bg-blue-100 px-1 rounded">123456</strong></span>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-xl bg-white border cursor-pointer transition-all"
                                :class="depositForm.bank_code === 'INTCARD' ? 'border-blue-500 shadow-sm' : 'border-slate-100 hover:border-blue-200'">
                                <input type="radio" v-model="depositForm.bank_code" value="INTCARD" class="hidden">
                                <span class="text-xl">🌍</span>
                                <span class="text-sm font-bold text-slate-700">Thẻ quốc tế</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="p-4 bg-white flex gap-3 pb-8">
                    <button @click="showDepositModal = false" class="flex-1 py-4 rounded-2xl font-bold text-slate-500 bg-slate-50 active:scale-95 transition-transform">Hủy</button>
                    <button @click="handleDeposit" class="flex-1 py-4 rounded-2xl font-black text-white bg-indigo-600 shadow-[0_8px_20px_rgba(79,70,229,0.3)] active:scale-95 transition-transform">Thanh toán</button>
                </div>
            </div>
        </div>

        <!-- Withdraw Modal -->
        <div v-if="showWithdrawModal" class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showWithdrawModal = false"></div>
            <div class="bg-white rounded-t-[2rem] sm:rounded-[2rem] w-full max-w-md relative z-10 overflow-hidden shadow-2xl pb-safe animate-slide-up h-[80vh] flex flex-col">
                <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mt-3 sm:hidden shrink-0"></div>
                <div class="p-5 text-center border-b border-slate-100 shrink-0">
                    <h3 class="text-lg font-black text-slate-900">Rút tiền về tài khoản</h3>
                </div>
                <form @submit.prevent="handleWithdraw" class="p-6 space-y-4 overflow-y-auto flex-1 custom-scrollbar">
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">Số tiền cần rút (đ)</label>
                        <input 
                            v-model="withdrawForm.amount"
                            type="number"
                            placeholder="Tối thiểu 50,000đ"
                            required
                            class="w-full bg-slate-50 border border-slate-100 rounded-2xl p-4 font-black focus:ring-2 focus:ring-indigo-500 focus:bg-white outline-none"
                        />
                        <p v-if="withdrawForm.errors.amount" class="text-xs text-rose-500 mt-1">{{ withdrawForm.errors.amount }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">Ngân hàng thụ hưởng</label>
                        <input 
                            v-model="withdrawForm.bank_name"
                            type="text"
                            placeholder="VD: Vietcombank, Techcombank..."
                            required
                            class="w-full bg-slate-50 border border-slate-100 rounded-2xl p-4 font-bold focus:ring-2 focus:ring-indigo-500 focus:bg-white outline-none"
                        />
                        <p v-if="withdrawForm.errors.bank_name" class="text-xs text-rose-500 mt-1">{{ withdrawForm.errors.bank_name }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">Số tài khoản</label>
                        <input 
                            v-model="withdrawForm.bank_account"
                            type="text"
                            placeholder="Nhập số tài khoản"
                            required
                            class="w-full bg-slate-50 border border-slate-100 rounded-2xl p-4 font-bold focus:ring-2 focus:ring-indigo-500 focus:bg-white outline-none"
                        />
                        <p v-if="withdrawForm.errors.bank_account" class="text-xs text-rose-500 mt-1">{{ withdrawForm.errors.bank_account }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">Tên chủ tài khoản</label>
                        <input 
                            v-model="withdrawForm.account_name"
                            type="text"
                            placeholder="VIET HOA CHU KHONG DAU"
                            required
                            class="w-full bg-slate-50 border border-slate-100 rounded-2xl p-4 font-bold uppercase focus:ring-2 focus:ring-indigo-500 focus:bg-white outline-none"
                        />
                        <p v-if="withdrawForm.errors.account_name" class="text-xs text-rose-500 mt-1">{{ withdrawForm.errors.account_name }}</p>
                    </div>
                    
                    <div class="mt-6 pt-4 border-t border-slate-100">
                        <button type="submit" :disabled="withdrawForm.processing" class="w-full py-4 rounded-2xl font-black text-white bg-slate-900 shadow-xl shadow-slate-900/20 active:scale-95 transition-transform disabled:opacity-50">Yêu cầu rút tiền</button>
                    </div>
                </form>
            </div>
        </div>
    </ShipperLayout>
</template>

<style scoped>
.animate-slide-up {
    animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes slideUp {
    from {
        transform: translateY(100%);
    }
    to {
        transform: translateY(0);
    }
}
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom, 20px);
}
</style>
