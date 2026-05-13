<script setup>
import { ref } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineOptions({ layout: GuestLayout });

const props = defineProps({
    transactions: Object
});

const user = usePage().props.auth.user;
const showDepositModal = ref(false);
const depositAmount = ref(50000);
const isProcessing = ref(false);

const showWithdrawModal = ref(false);
const withdrawForm = ref({
    amount: 50000,
    bank_name: user.bank_name || '',
    bank_account: user.bank_account || '',
    account_name: user.bank_account_name || ''
});

const quickAmounts = [20000, 50000, 100000, 200000, 500000];

const formatPrice = (p) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(p);

const submitDeposit = async () => {
    if (depositAmount.value < 10000) {
        alert('Số tiền nạp tối thiểu là 10.000đ');
        return;
    }

    try {
        isProcessing.value = true;
        const response = await fetch('/wallet/deposit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                amount: depositAmount.value,
                method: 'vnpay'
            })
        });

        const data = await response.json();
        if (response.ok && data.payment_url) {
            window.location.href = data.payment_url;
        } else {
            alert(data.error || 'Có lỗi xảy ra khi tạo giao dịch.');
        }
    } catch (error) {
        console.error(error);
        alert('Lỗi kết nối tới hệ thống.');
    } finally {
        isProcessing.value = false;
    }
};

const submitWithdraw = () => {
    if (withdrawForm.value.amount < 50000) {
        alert('Số tiền rút tối thiểu là 50.000đ');
        return;
    }
    if (withdrawForm.value.amount > user.wallet_balance) {
        alert('Số dư không đủ để rút số tiền này');
        return;
    }
    if (!withdrawForm.value.bank_name || !withdrawForm.value.bank_account || !withdrawForm.value.account_name) {
        alert('Vui lòng nhập đầy đủ thông tin ngân hàng');
        return;
    }

    router.post(route('wallet.withdraw'), withdrawForm.value, {
        onSuccess: () => {
            showWithdrawModal.value = false;
            withdrawForm.value.amount = 50000;
        }
    });
};

const formatDate = (dateString) => {
    const d = new Date(dateString);
    return d.toLocaleDateString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <Head title="Ví FoodXpress của tôi" />

    <div class="min-h-screen bg-[#f8fafc] py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="flex items-center gap-3 mb-8">
                <span class="text-3xl">💳</span>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Ví FoodXpress</h1>
            </div>

            <!-- Balance Card -->
            <div class="bg-gradient-to-br from-orange-500 to-red-600 rounded-[2rem] p-8 shadow-xl shadow-orange-500/20 text-white mb-10 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/4"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/10 rounded-full blur-2xl translate-y-1/3 -translate-x-1/4"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div>
                        <p class="text-orange-100 font-medium uppercase tracking-widest text-xs mb-2">Số dư khả dụng</p>
                        <h2 class="text-5xl font-black tracking-tight">{{ formatPrice(user.wallet_balance || 0) }}</h2>
                        <p class="mt-3 text-sm text-orange-100 max-w-sm">
                            Sử dụng số dư này để thanh toán nhanh chóng các đơn hàng FoodXpress mà không cần chờ đợi xác nhận từ ngân hàng.
                        </p>
                    </div>
                    <div class="shrink-0 flex gap-3">
                        <button @click="showWithdrawModal = true" class="bg-white/20 text-white hover:bg-white/30 px-6 py-4 rounded-2xl font-bold shadow-lg hover:-translate-y-1 transition-all flex items-center gap-2">
                            <span>💸</span> Rút tiền
                        </button>
                        <button @click="showDepositModal = true" class="bg-white text-orange-600 hover:bg-orange-50 px-8 py-4 rounded-2xl font-bold shadow-lg hover:-translate-y-1 transition-all flex items-center gap-2">
                            <span>➕</span> Nạp tiền
                        </button>
                    </div>
                </div>
            </div>

            <!-- Transactions List -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 sm:p-8">
                <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
                    <span>📋</span> Lịch sử giao dịch
                </h3>

                <div v-if="!transactions.data || transactions.data.length === 0" class="text-center py-12">
                    <span class="text-4xl block mb-4 opacity-50">📭</span>
                    <p class="text-gray-500 font-medium">Bạn chưa có giao dịch nào.</p>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="tx in transactions.data" :key="tx.id" class="flex items-center justify-between p-4 rounded-2xl border border-gray-100 hover:border-orange-200 hover:bg-orange-50/30 transition-colors group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
                                 :class="tx.type === 'deposit' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'">
                                <span class="text-xl font-black">{{ tx.type === 'deposit' ? '+' : '-' }}</span>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900 text-sm group-hover:text-orange-700 transition-colors">{{ tx.description || (tx.type === 'deposit' ? 'Nạp tiền vào ví' : 'Thanh toán đơn hàng') }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ formatDate(tx.created_at) }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-black" :class="tx.type === 'deposit' ? 'text-green-600' : 'text-gray-900'">
                                {{ tx.type === 'deposit' ? '+' : '-' }}{{ formatPrice(Math.abs(tx.amount)) }}
                            </p>
                            <p class="text-[10px] uppercase font-bold tracking-widest mt-1"
                               :class="tx.status === 'completed' ? 'text-green-500' : (tx.status === 'pending' ? 'text-orange-500' : 'text-red-500')">
                                {{ tx.status }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="transactions.last_page > 1" class="mt-8 flex justify-center gap-2">
                    <component :is="link.url ? 'Link' : 'span'"
                               v-for="(link, idx) in transactions.links" :key="idx"
                               :href="link.url"
                               class="px-4 py-2 rounded-xl font-bold text-sm transition-colors"
                               :class="link.active ? 'bg-gray-900 text-white' : (link.url ? 'bg-gray-100 text-gray-600 hover:bg-gray-200' : 'bg-gray-50 text-gray-400 cursor-not-allowed')"
                               v-html="link.label">
                    </component>
                </div>
            </div>
        </div>

        <!-- Deposit Modal -->
        <div v-if="showDepositModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden animate-slide-up">
                <div class="flex justify-between items-center p-6 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-xl font-black text-gray-900">Nạp tiền vào ví</h2>
                    <button @click="showDepositModal = false" class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-300 hover:text-gray-700 transition-colors">✕</button>
                </div>
                
                <div class="p-6">
                    <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-3">Chọn hoặc nhập số tiền (VNĐ)</label>
                    <input 
                        type="number" 
                        v-model="depositAmount" 
                        class="w-full text-2xl font-black text-center text-gray-900 rounded-2xl border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 p-4 mb-4"
                    />
                    
                    <div class="flex flex-wrap gap-2 mb-6 justify-center">
                        <button v-for="amt in quickAmounts" :key="amt"
                                @click="depositAmount = amt"
                                class="px-4 py-2 rounded-xl text-sm font-bold border transition-colors"
                                :class="depositAmount === amt ? 'bg-orange-50 border-orange-500 text-orange-600' : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300'">
                            {{ formatPrice(amt).replace('₫','') }}
                        </button>
                    </div>

                    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm shrink-0 text-xl">💳</div>
                        <div>
                            <p class="font-bold text-gray-900 text-sm">Thanh toán qua VNPay</p>
                            <p class="text-xs text-gray-500">Hỗ trợ thẻ ATM nội địa, Visa/Mastercard</p>
                        </div>
                    </div>

                    <button @click="submitDeposit" :disabled="isProcessing" class="w-full bg-gray-900 hover:bg-black text-white font-bold py-4 rounded-2xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50 flex justify-center items-center gap-2">
                        <span v-if="isProcessing" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        Xác nhận nạp {{ formatPrice(depositAmount) }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Withdraw Modal -->
        <div v-if="showWithdrawModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden animate-slide-up">
                <div class="flex justify-between items-center p-6 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-xl font-black text-gray-900">Yêu cầu rút tiền</h2>
                    <button @click="showWithdrawModal = false" class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-300 hover:text-gray-700 transition-colors">✕</button>
                </div>
                
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-2">Số tiền rút (Tối thiểu 50.000đ)</label>
                        <input 
                            type="number" 
                            v-model="withdrawForm.amount" 
                            class="w-full text-lg font-bold text-gray-900 rounded-xl border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 p-3"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-2">Tên ngân hàng</label>
                        <input 
                            type="text" 
                            v-model="withdrawForm.bank_name" 
                            placeholder="VD: Vietcombank"
                            class="w-full text-base font-medium text-gray-900 rounded-xl border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 p-3"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-2">Số tài khoản</label>
                        <input 
                            type="text" 
                            v-model="withdrawForm.bank_account" 
                            class="w-full text-base font-medium text-gray-900 rounded-xl border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 p-3"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-2">Tên chủ tài khoản</label>
                        <input 
                            type="text" 
                            v-model="withdrawForm.account_name" 
                            placeholder="VIET HOA KHONG DAU"
                            class="w-full text-base font-medium text-gray-900 rounded-xl border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 p-3 uppercase"
                        />
                    </div>

                    <div class="pt-4">
                        <button @click="submitWithdraw" :disabled="isProcessing" class="w-full bg-gray-900 hover:bg-black text-white font-bold py-4 rounded-2xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50">
                            Gửi yêu cầu rút tiền
                        </button>
                        <p class="text-[10px] text-gray-500 text-center mt-3">Tiền sẽ được hoàn về tài khoản của bạn trong vòng 24h làm việc.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
<style scoped>
.animate-slide-up {
    animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
/* Hide scrollbar for Chrome, Safari and Opera */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
/* Hide scrollbar for IE, Edge and Firefox */
.no-scrollbar {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
</style>
