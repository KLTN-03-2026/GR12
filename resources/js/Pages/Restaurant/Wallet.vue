<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import RestaurantLayout from '@/Layouts/RestaurantLayout.vue';
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
            alert('Yêu cầu rút tiền thành công. Vui lòng chờ kế toán xử lý trong 24h.');
        }
    });
};

const getTransactionTypeName = (type) => {
    const types = {
        'deposit': 'Nạp tiền',
        'withdraw': 'Rút tiền',
        'order_revenue': 'Doanh thu đơn hàng',
        'order_payment': 'Ứng trả quán',
        'admin_fee_deduction': 'Trừ phí dịch vụ'
    };
    return types[type] || type;
};

const getTransactionColor = (type, amount) => {
    if (amount > 0) return 'text-emerald-500';
    return 'text-rose-500';
};
</script>

<template>
    <Head title="Ví Điện Tử - Restaurant Hub" />

    <RestaurantLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Ví điện tử</h1>
                    <p class="text-sm text-slate-500 mt-1 font-medium">Quản lý dòng tiền và số dư của quán</p>
                </div>
            </div>

            <!-- Balance Card -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-[2rem] p-8 shadow-xl text-white relative overflow-hidden">
                <!-- Decor -->
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Số dư khả dụng</p>
                        <p class="text-5xl font-black text-white tracking-tight drop-shadow-md">
                            {{ formatCurrency(user.wallet_balance) }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <button 
                            @click="showDepositModal = true"
                            class="flex-1 md:flex-none flex items-center justify-center gap-2 px-6 py-4 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-white font-black text-sm uppercase tracking-widest shadow-[0_8px_20px_rgba(16,185,129,0.3)] hover:-translate-y-1 transition-all"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                            Nạp tiền
                        </button>
                        <button 
                            @click="showWithdrawModal = true"
                            class="flex-1 md:flex-none flex items-center justify-center gap-2 px-6 py-4 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-black text-sm uppercase tracking-widest backdrop-blur-sm border border-white/10 transition-all hover:-translate-y-1"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 20V4m-8 8l8-8 8 8"></path></svg>
                            Rút tiền
                        </button>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100">
                <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6">Lịch sử giao dịch</h2>
                
                <div v-if="transactions.data.length === 0" class="text-center py-10">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl">💸</span>
                    </div>
                    <p class="text-slate-500 font-medium">Chưa có giao dịch nào.</p>
                </div>

                <div v-else class="space-y-4">
                    <div 
                        v-for="tx in transactions.data" 
                        :key="tx.id"
                        class="flex items-center justify-between p-4 rounded-2xl border border-slate-50 hover:bg-slate-50 transition-colors"
                    >
                        <div class="flex items-center gap-4">
                            <div 
                                class="w-12 h-12 rounded-xl flex items-center justify-center text-xl shrink-0"
                                :class="tx.amount > 0 ? 'bg-emerald-50 text-emerald-500' : 'bg-rose-50 text-rose-500'"
                            >
                                {{ tx.amount > 0 ? '↓' : '↑' }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ getTransactionTypeName(tx.type) }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ tx.description }}</p>
                                <p class="text-[10px] text-slate-400 mt-1">{{ formatDate(tx.created_at) }}</p>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-base font-black" :class="getTransactionColor(tx.type, tx.amount)">
                                {{ tx.amount > 0 ? '+' : '' }}{{ formatCurrency(tx.amount) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pagination (Simple) -->
                <div v-if="transactions.links && transactions.data.length > 0" class="mt-6 flex justify-center gap-2">
                    <template v-for="(link, pIndex) in transactions.links" :key="pIndex">
                        <a 
                            v-if="link.url" 
                            :href="link.url"
                            class="px-4 py-2 rounded-xl text-sm font-bold transition-colors"
                            :class="link.active ? 'bg-orange-500 text-white shadow-md' : 'bg-slate-50 text-slate-500 hover:bg-slate-100'"
                            v-html="link.label"
                        ></a>
                        <span 
                            v-else 
                            class="px-4 py-2 rounded-xl text-sm font-bold bg-transparent text-slate-400"
                            v-html="link.label"
                        ></span>
                    </template>
                </div>
            </div>
        </div>

        <!-- Deposit Modal -->
        <div v-if="showDepositModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showDepositModal = false"></div>
            <div class="bg-white rounded-[2rem] w-full max-w-md relative z-10 overflow-hidden shadow-2xl">
                <div class="p-6 text-center border-b border-slate-100">
                    <h3 class="text-lg font-black text-slate-900">Nạp tiền vào ví</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Số tiền cần nạp (đ)</label>
                        <input 
                            v-model="depositForm.amount"
                            type="number"
                            placeholder="VD: 50000"
                            class="w-full bg-slate-50 border-none rounded-2xl p-4 font-black text-xl focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Phương thức thanh toán</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label 
                                class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all"
                                :class="depositForm.method === 'vnpay' ? 'border-blue-500 bg-blue-50' : 'border-slate-100 bg-white hover:border-slate-200'"
                            >
                                <input type="radio" v-model="depositForm.method" value="vnpay" class="hidden">
                                <span class="font-black text-blue-600">VNPay</span>
                            </label>
                            <label 
                                class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-not-allowed opacity-50"
                            >
                                <input type="radio" value="momo" disabled class="hidden">
                                <span class="font-black text-pink-500 mb-1">Momo</span>
                                <span class="text-[9px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">Bảo trì</span>
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
                <div class="p-6 bg-slate-50 flex gap-3">
                    <button @click="showDepositModal = false" class="flex-1 py-3.5 rounded-xl font-bold text-slate-500 hover:bg-slate-100">Hủy</button>
                    <button @click="handleDeposit" class="flex-1 py-3.5 rounded-xl font-black text-white bg-emerald-500 shadow-[0_4px_15px_rgba(16,185,129,0.3)]">Thanh toán</button>
                </div>
            </div>
        </div>

        <!-- Withdraw Modal -->
        <div v-if="showWithdrawModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showWithdrawModal = false"></div>
            <div class="bg-white rounded-[2rem] w-full max-w-md relative z-10 overflow-hidden shadow-2xl">
                <div class="p-6 text-center border-b border-slate-100">
                    <h3 class="text-lg font-black text-slate-900">Rút tiền về thẻ</h3>
                </div>
                <form @submit.prevent="handleWithdraw" class="p-6 space-y-4">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Số tiền cần rút (đ)</label>
                        <input 
                            v-model="withdrawForm.amount"
                            type="number"
                            placeholder="Tối thiểu 50,000đ"
                            required
                            class="w-full bg-slate-50 border border-slate-100 rounded-xl p-3 font-bold focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                        <p v-if="withdrawForm.errors.amount" class="text-xs text-rose-500 mt-1">{{ withdrawForm.errors.amount }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Ngân hàng</label>
                        <input 
                            v-model="withdrawForm.bank_name"
                            type="text"
                            placeholder="VD: Vietcombank"
                            required
                            class="w-full bg-slate-50 border border-slate-100 rounded-xl p-3 font-bold focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                        <p v-if="withdrawForm.errors.bank_name" class="text-xs text-rose-500 mt-1">{{ withdrawForm.errors.bank_name }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Số tài khoản</label>
                        <input 
                            v-model="withdrawForm.bank_account"
                            type="text"
                            required
                            class="w-full bg-slate-50 border border-slate-100 rounded-xl p-3 font-bold focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                        <p v-if="withdrawForm.errors.bank_account" class="text-xs text-rose-500 mt-1">{{ withdrawForm.errors.bank_account }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Tên chủ thẻ</label>
                        <input 
                            v-model="withdrawForm.account_name"
                            type="text"
                            placeholder="VIET HOA CHU KHONG DAU"
                            required
                            class="w-full bg-slate-50 border border-slate-100 rounded-xl p-3 font-bold uppercase focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                        <p v-if="withdrawForm.errors.account_name" class="text-xs text-rose-500 mt-1">{{ withdrawForm.errors.account_name }}</p>
                    </div>
                    
                    <div class="mt-6 flex gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="showWithdrawModal = false" class="flex-1 py-3.5 rounded-xl font-bold text-slate-500 hover:bg-slate-100">Hủy</button>
                        <button type="submit" :disabled="withdrawForm.processing" class="flex-1 py-3.5 rounded-xl font-black text-white bg-slate-900 shadow-md hover:bg-slate-800 disabled:opacity-50">Yêu cầu rút</button>
                    </div>
                </form>
            </div>
        </div>
    </RestaurantLayout>
</template>
