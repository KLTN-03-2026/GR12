<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    users: Object,
    filters: Object,
    stats: Object,
});

defineOptions({ layout: AdminLayout });

const search = ref(props.filters.search || '');
const role = ref(props.filters.role || '');

watch([search, role], () => {
    router.get(
        route('admin.wallets.index'),
        { search: search.value, role: role.value },
        { preserveState: true, replace: true, preserveScroll: true }
    );
});

// Modal điều chỉnh số dư
const isModalOpen = ref(false);
const selectedUser = ref(null);

const form = useForm({
    amount: '',
    reason: '',
});

const openAdjustModal = (user) => {
    selectedUser.value = user;
    form.amount = '';
    form.reason = '';
    isModalOpen.value = true;
};

const submitAdjust = () => {
    form.post(route('admin.wallets.adjust', selectedUser.value.id), {
        onSuccess: () => {
            isModalOpen.value = false;
        },
        preserveScroll: true
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
};
</script>

<template>
    <Head title="Quản lý Ví điện tử" />

    <div class="space-y-6 pb-10">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Ví điện tử Người dùng</h2>
                <p class="text-slate-500 text-sm mt-1">Giám sát và điều chỉnh số dư ví của toàn hệ thống</p>
            </div>
        </div>

        <!-- Thống kê nhanh -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl p-5 shadow-lg border border-slate-700">
                <div class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Tổng tiền trong hệ thống</div>
                <div class="text-2xl font-black text-white">{{ formatCurrency(stats.total_balance) }}</div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <div class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Ví Khách hàng</div>
                <div class="text-xl font-black text-slate-800">{{ formatCurrency(stats.total_customers) }}</div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <div class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Ví Quán ăn</div>
                <div class="text-xl font-black text-slate-800">{{ formatCurrency(stats.total_restaurants) }}</div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <div class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Ví Shipper</div>
                <div class="text-xl font-black text-slate-800">{{ formatCurrency(stats.total_shippers) }}</div>
            </div>
        </div>

        <!-- Bộ lọc -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">🔍</span>
                <input v-model="search" type="text" placeholder="Tìm theo tên, email, SĐT..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-orange-500 transition-shadow">
            </div>
            <div class="w-full md:w-64">
                <select v-model="role" class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-orange-500 transition-shadow font-medium text-slate-700">
                    <option value="">Tất cả vai trò</option>
                    <option value="customer">Khách hàng</option>
                    <option value="restaurant">Quán ăn</option>
                    <option value="shipper">Shipper</option>
                </select>
            </div>
        </div>

        <!-- Bảng danh sách -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-6 py-4 font-bold">Người dùng</th>
                            <th class="px-6 py-4 font-bold">Vai trò</th>
                            <th class="px-6 py-4 font-bold text-right">Số dư ví</th>
                            <th class="px-6 py-4 font-bold text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img :src="user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random`" class="w-10 h-10 rounded-full object-cover shadow-sm">
                                    <div>
                                        <div class="font-bold text-slate-800">{{ user.name }}</div>
                                        <div class="text-xs text-slate-500">{{ user.phone }} • {{ user.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="user.role === 'customer'" class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-bold uppercase tracking-wider">Khách</span>
                                <span v-else-if="user.role === 'restaurant'" class="px-2.5 py-1 rounded-full bg-orange-50 text-orange-600 text-xs font-bold uppercase tracking-wider">Quán ăn</span>
                                <span v-else-if="user.role === 'shipper'" class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs font-bold uppercase tracking-wider">Shipper</span>
                                <span v-else class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-bold uppercase tracking-wider">Admin</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-black" :class="user.wallet_balance > 0 ? 'text-emerald-600' : 'text-slate-500'">
                                    {{ formatCurrency(user.wallet_balance) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="openAdjustModal(user)" class="px-4 py-2 bg-slate-100 hover:bg-orange-100 text-slate-600 hover:text-orange-600 font-bold rounded-lg transition-colors text-xs">
                                    Điều chỉnh
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between" v-if="users.links.length > 3">
                <div class="text-sm text-slate-500">
                    Hiển thị <span class="font-bold text-slate-800">{{ users.from }}</span> đến <span class="font-bold text-slate-800">{{ users.to }}</span> trong tổng <span class="font-bold text-slate-800">{{ users.total }}</span>
                </div>
                <div class="flex gap-1">
                    <component 
                        :is="link.url ? Link : 'span'"
                        v-for="(link, i) in users.links" 
                        :key="i"
                        :href="link.url"
                        v-html="link.label"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition-colors"
                        :class="[
                            link.active ? 'bg-orange-500 text-white shadow-md shadow-orange-500/20' : 'text-slate-600 hover:bg-slate-100',
                            !link.url ? 'opacity-50 cursor-not-allowed' : ''
                        ]"
                    />
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Điều chỉnh số dư -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="isModalOpen = false"></div>
        <div class="bg-white rounded-3xl w-full max-w-md relative z-10 shadow-2xl p-6 transform transition-all">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-black text-slate-800">Điều chỉnh số dư ví</h3>
                <button @click="isModalOpen = false" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 transition-colors">✕</button>
            </div>
            
            <div v-if="selectedUser" class="mb-6 p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center gap-3">
                <img :src="selectedUser.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(selectedUser.name)}`" class="w-12 h-12 rounded-full">
                <div>
                    <div class="font-bold text-slate-800">{{ selectedUser.name }}</div>
                    <div class="text-xs text-slate-500">Số dư hiện tại: <strong class="text-emerald-600">{{ formatCurrency(selectedUser.wallet_balance) }}</strong></div>
                </div>
            </div>

            <form @submit.prevent="submitAdjust" class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Số tiền (VNĐ)</label>
                    <input type="number" v-model="form.amount" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="VD: 50000 (Cộng) hoặc -50000 (Trừ)" required>
                    <p class="text-xs text-slate-500 mt-1">Nhập số dương để Cộng tiền, số âm (-) để Trừ tiền.</p>
                    <div v-if="form.errors.amount" class="text-red-500 text-xs mt-1">{{ form.errors.amount }}</div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Lý do điều chỉnh</label>
                    <input type="text" v-model="form.reason" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="VD: Thưởng thành tích xuất sắc" required>
                    <div v-if="form.errors.reason" class="text-red-500 text-xs mt-1">{{ form.errors.reason }}</div>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="button" @click="isModalOpen = false" class="flex-1 px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-colors">Hủy</button>
                    <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white font-bold rounded-xl shadow-lg shadow-orange-500/30 hover:-translate-y-0.5 transition-all disabled:opacity-50">
                        Xác nhận
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
