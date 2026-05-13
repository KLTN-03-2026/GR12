<script setup>
import { ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UserAvatar from '@/Components/UserAvatar.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    users: Object,
    stats: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const role = ref(props.filters.role || '');
const status = ref(props.filters.status || '');

// Throttle search
let searchTimeout = null;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('admin.users.index'),
            { search: search.value, role: role.value, status: status.value },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }, 500);
};

watch([role, status], () => {
    handleSearch();
});

// Quản lý Modal Khóa Tài khoản
const isBlockModalOpen = ref(false);
const userToBlock = ref(null);
const blockReason = ref('');
const isAnalyzing = ref(false);
const aiAnalysisResult = ref('');

const openBlockModal = (user) => {
    userToBlock.value = user;
    blockReason.value = '';
    aiAnalysisResult.value = '';
    isBlockModalOpen.value = true;
    activeDropdown.value = null;
};

const confirmBlock = () => {
    if (!blockReason.value.trim()) {
        alert('Vui lòng nhập lý do khóa tài khoản.');
        return;
    }
    
    router.patch(route('admin.users.toggle-block', userToBlock.value.id), { reason: blockReason.value }, {
        preserveScroll: true,
        onSuccess: () => {
            isBlockModalOpen.value = false;
            if (selectedUser.value && selectedUser.value.id === userToBlock.value.id) {
                selectedUser.value.status = 'blocked';
                selectedUser.value.block_reason = blockReason.value;
            }
        }
    });
};

const analyzeRisk = async () => {
    if (!userToBlock.value) return;
    
    isAnalyzing.value = true;
    aiAnalysisResult.value = '';
    
    try {
        const response = await axios.post(route('admin.users.analyze-risk', userToBlock.value.id));
        if (response.data.success) {
            aiAnalysisResult.value = response.data.analysis;
        } else {
            aiAnalysisResult.value = 'Lỗi: ' + response.data.message;
        }
    } catch (error) {
        aiAnalysisResult.value = 'Đã xảy ra lỗi khi kết nối tới AI.';
    } finally {
        isAnalyzing.value = false;
    }
};

const toggleBlock = (user) => {
    if (user.status === 'blocked') {
        if (confirm(`Bạn có chắc chắn muốn MỞ KHÓA tài khoản ${user.name}?`)) {
            router.patch(route('admin.users.toggle-block', user.id), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    if (selectedUser.value && selectedUser.value.id === user.id) {
                        selectedUser.value.status = 'active';
                        selectedUser.value.block_reason = null;
                    }
                }
            });
        }
    } else {
        openBlockModal(user);
    }
};

const getRoleColor = (userRole) => {
    switch (userRole) {
        case 'admin': return 'bg-purple-100 text-purple-700 border-purple-200';
        case 'restaurant': return 'bg-orange-100 text-orange-700 border-orange-200';
        case 'shipper': return 'bg-blue-100 text-blue-700 border-blue-200';
        default: return 'bg-emerald-100 text-emerald-700 border-emerald-200';
    }
};

const getRoleName = (userRole) => {
    switch (userRole) {
        case 'admin': return 'Quản trị viên';
        case 'restaurant': return 'Quán ăn';
        case 'shipper': return 'Tài xế';
        default: return 'Khách hàng';
    }
};

const getStatusBadge = (userStatus) => {
    switch (userStatus) {
        case 'active': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        case 'pending': return 'bg-amber-100 text-amber-700 border-amber-200';
        case 'blocked': return 'bg-rose-100 text-rose-700 border-rose-200';
        default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};

const getStatusName = (userStatus) => {
    switch (userStatus) {
        case 'active': return 'Đang hoạt động';
        case 'pending': return 'Chờ duyệt';
        case 'blocked': return 'Đã khóa';
        default: return 'Không xác định';
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount || 0);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('vi-VN', {
        year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};

// Quản lý Modal chi tiết người dùng
const isModalOpen = ref(false);
const selectedUser = ref(null);
const activeDropdown = ref(null);

const openDetails = (user) => {
    selectedUser.value = user;
    isModalOpen.value = true;
    activeDropdown.value = null;
};

const toggleDropdown = (userId) => {
    if (activeDropdown.value === userId) {
        activeDropdown.value = null;
    } else {
        activeDropdown.value = userId;
    }
};

// Click outside dropdown to close
import { onMounted, onUnmounted } from 'vue';
const closeDropdown = (e) => {
    if (!e.target.closest('.dropdown-container')) {
        activeDropdown.value = null;
    }
};

onMounted(() => document.addEventListener('click', closeDropdown));
onUnmounted(() => document.removeEventListener('click', closeDropdown));
</script>

<template>
    <Head title="Quản lý Người dùng" />

    <div class="space-y-6 pb-10">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">Hồ sơ <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-red-500">Người dùng</span></h2>
                <p class="text-slate-500 text-sm mt-1 font-medium">Giám sát toàn diện tài khoản, đối tác và shipper trên hệ thống.</p>
            </div>
        </div>

        <!-- Thống kê nhanh (Stats Cards) -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4" v-if="stats">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-shadow group">
                <div class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1 flex justify-between">
                    Tổng User <span class="text-xl opacity-50 group-hover:opacity-100 transition-opacity text-blue-500">🌍</span>
                </div>
                <div class="text-3xl font-black text-slate-800">{{ stats.total }}</div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-emerald-100 hover:shadow-md hover:border-emerald-200 transition-shadow group">
                <div class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1 flex justify-between">
                    Khách hàng <span class="text-xl opacity-50 group-hover:opacity-100 transition-opacity text-emerald-500">👤</span>
                </div>
                <div class="text-3xl font-black text-emerald-600">{{ stats.customers }}</div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-orange-100 hover:shadow-md hover:border-orange-200 transition-shadow group">
                <div class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1 flex justify-between">
                    Quán ăn <span class="text-xl opacity-50 group-hover:opacity-100 transition-opacity text-orange-500">🏪</span>
                </div>
                <div class="text-3xl font-black text-orange-600">{{ stats.restaurants }}</div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-blue-100 hover:shadow-md hover:border-blue-200 transition-shadow group">
                <div class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1 flex justify-between">
                    Shipper <span class="text-xl opacity-50 group-hover:opacity-100 transition-opacity text-blue-500">🛵</span>
                </div>
                <div class="text-3xl font-black text-blue-600">{{ stats.shippers }}</div>
            </div>
            <div class="bg-gradient-to-br from-rose-50 to-white rounded-2xl p-5 shadow-sm border border-rose-100 hover:shadow-md transition-shadow group">
                <div class="text-rose-500 text-xs font-black uppercase tracking-widest mb-1 flex justify-between">
                    Đã Khóa <span class="text-xl opacity-50 group-hover:opacity-100 transition-opacity text-rose-500">🔒</span>
                </div>
                <div class="text-3xl font-black text-rose-600">{{ stats.blocked }}</div>
            </div>
        </div>

        <!-- Bộ lọc (Filters) -->
        <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-200 flex flex-col md:flex-row gap-4 items-center">
            <div class="flex-1 relative w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input v-model="search" @input="handleSearch" type="text" placeholder="Tìm kiếm theo Tên, Số điện thoại hoặc Email..." class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:bg-white focus:ring-2 focus:ring-orange-500 transition-all font-medium text-slate-700 shadow-inner">
            </div>
            <div class="flex gap-3 w-full md:w-auto">
                <select v-model="role" class="w-full md:w-48 px-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:bg-white focus:ring-2 focus:ring-orange-500 transition-all font-bold text-slate-700 shadow-inner">
                    <option value="">Tất cả Vai trò</option>
                    <option value="customer">Khách hàng</option>
                    <option value="restaurant">Quán ăn</option>
                    <option value="shipper">Tài xế</option>
                    <option value="admin">Quản trị viên</option>
                </select>
                <select v-model="status" class="w-full md:w-48 px-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:bg-white focus:ring-2 focus:ring-orange-500 transition-all font-bold text-slate-700 shadow-inner">
                    <option value="">Tất cả Trạng thái</option>
                    <option value="active">Đang hoạt động</option>
                    <option value="pending">Chờ duyệt</option>
                    <option value="blocked">Bị khóa</option>
                </select>
            </div>
        </div>

        <!-- Bảng danh sách (Table) -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-visible relative z-0">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100">
                            <th class="p-5 text-xs font-black text-slate-500 uppercase tracking-widest rounded-tl-3xl">Hồ sơ</th>
                            <th class="p-5 text-xs font-black text-slate-500 uppercase tracking-widest">Liên hệ</th>
                            <th class="p-5 text-xs font-black text-slate-500 uppercase tracking-widest">Vai trò & Trạng thái</th>
                            <th class="p-5 text-xs font-black text-slate-500 uppercase tracking-widest text-right">Ví điện tử</th>
                            <th class="p-5 text-xs font-black text-slate-500 uppercase tracking-widest text-center rounded-tr-3xl">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-orange-50/30 transition-colors group">
                            <td class="p-5">
                                <div class="flex items-center gap-4 cursor-pointer" @click="openDetails(user)">
                                    <div class="relative">
                                        <UserAvatar :user="user" size="lg" class="shadow-sm border-2 border-white group-hover:border-orange-100 transition-colors" />
                                        <span v-if="user.status === 'active'" class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></span>
                                        <span v-else-if="user.status === 'blocked'" class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-rose-500 border-2 border-white rounded-full"></span>
                                        <span v-else class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-amber-500 border-2 border-white rounded-full"></span>
                                    </div>
                                    <div>
                                        <p class="font-black text-slate-800 group-hover:text-orange-600 transition-colors text-base">{{ user.name }}</p>
                                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-0.5">ID: #{{ user.id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-5">
                                <div class="flex items-center gap-2 mb-1">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    <p class="text-sm font-bold text-slate-700">{{ user.phone || 'Chưa cập nhật' }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    <p class="text-xs text-slate-500 font-medium">{{ user.email }}</p>
                                </div>
                            </td>
                            <td class="p-5 space-y-2">
                                <div>
                                    <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-widest border" :class="getRoleColor(user.role)">
                                        {{ getRoleName(user.role) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border" :class="getStatusBadge(user.status)">
                                        {{ getStatusName(user.status) }}
                                    </span>
                                </div>
                            </td>
                            <td class="p-5 text-right">
                                <span class="font-black text-base" :class="user.wallet_balance > 0 ? 'text-emerald-600' : 'text-slate-500'">
                                    {{ formatCurrency(user.wallet_balance) }}
                                </span>
                            </td>
                            <td class="p-5 text-center relative dropdown-container">
                                <!-- Nút ba chấm (Options) -->
                                <button @click.stop="toggleDropdown(user.id)" class="w-10 h-10 rounded-xl bg-slate-50 hover:bg-slate-200 text-slate-500 flex items-center justify-center mx-auto transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div v-if="activeDropdown === user.id" class="absolute right-12 top-10 mt-1 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-10 animate-fade-in-up">
                                    <button @click="openDetails(user)" class="w-full text-left px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 hover:text-orange-500 flex items-center gap-2 transition-colors border-b border-slate-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Xem chi tiết
                                    </button>
                                    <button v-if="user.id !== $page.props.auth.user.id" @click="toggleBlock(user)" class="w-full text-left px-4 py-3 text-sm font-bold flex items-center gap-2 transition-colors" :class="user.status === 'blocked' ? 'text-emerald-600 hover:bg-emerald-50' : 'text-rose-600 hover:bg-rose-50'">
                                        <svg v-if="user.status === 'blocked'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        {{ user.status === 'blocked' ? 'Mở khóa tài khoản' : 'Khóa tài khoản' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="5" class="p-12 text-center">
                                <div class="text-4xl mb-4 opacity-50">🕵️‍♂️</div>
                                <p class="text-slate-500 font-bold text-lg">Không tìm thấy người dùng nào phù hợp.</p>
                                <p class="text-sm text-slate-400 mt-1">Hãy thử thay đổi từ khóa hoặc bộ lọc xem sao.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Phân trang (Pagination) -->
            <div v-if="users.links && users.links.length > 3" class="p-5 border-t border-slate-100 flex items-center justify-between bg-slate-50/50 rounded-b-3xl">
                <div class="text-sm font-medium text-slate-500 hidden sm:block">
                    Đang hiển thị <span class="font-black text-slate-800">{{ users.from }}</span> - <span class="font-black text-slate-800">{{ users.to }}</span> trên <span class="font-black text-slate-800">{{ users.total }}</span>
                </div>
                <div class="flex gap-1.5 flex-wrap justify-center">
                    <template v-for="(link, index) in users.links" :key="index">
                        <button
                            v-if="link.url"
                            @click="router.get(link.url, {}, { preserveScroll: true, preserveState: true })"
                            v-html="link.label"
                            class="min-w-[2.5rem] h-10 px-2 rounded-xl text-sm font-bold transition-all duration-300 flex items-center justify-center"
                            :class="link.active ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-500/30' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 hover:border-slate-300'"
                        ></button>
                        <span
                            v-else
                            v-html="link.label"
                            class="min-w-[2.5rem] h-10 px-2 flex items-center justify-center rounded-xl text-sm font-bold bg-transparent text-slate-300 cursor-not-allowed"
                        ></span>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Chi tiết Người dùng -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="isModalOpen = false"></div>
        
        <!-- Modal Content -->
        <div class="bg-white rounded-[2rem] w-full max-w-2xl relative z-10 shadow-2xl overflow-hidden transform transition-all flex flex-col max-h-[90vh]">
            <!-- Cover Header -->
            <div class="h-32 bg-gradient-to-r from-orange-400 via-red-500 to-purple-600 relative">
                <button @click="isModalOpen = false" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-black/20 text-white hover:bg-black/40 transition-colors backdrop-blur-md">✕</button>
            </div>
            
            <div class="px-8 pb-8 pt-0 flex-1 overflow-y-auto custom-scrollbar relative">
                <!-- Avatar Overlapping -->
                <div class="flex justify-between items-end -mt-16 mb-6">
                    <div class="relative rounded-full border-4 border-white bg-white shadow-lg inline-block">
                        <UserAvatar :user="selectedUser" size="xl" class="w-24 h-24 sm:w-28 sm:h-28" />
                        <span v-if="selectedUser.status === 'active'" class="absolute bottom-2 right-2 w-5 h-5 bg-emerald-500 border-4 border-white rounded-full"></span>
                    </div>
                    <div class="flex gap-2 mb-2">
                        <span class="px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-widest border" :class="getRoleColor(selectedUser.role)">
                            {{ getRoleName(selectedUser.role) }}
                        </span>
                        <span class="px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-widest border" :class="getStatusBadge(selectedUser.status)">
                            {{ getStatusName(selectedUser.status) }}
                        </span>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="mb-2">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ selectedUser.name }}</h3>
                    <p class="text-slate-500 font-bold uppercase tracking-wider text-xs mt-1">Thành viên từ: {{ formatDate(selectedUser.created_at) }}</p>
                </div>

                <!-- Thông báo nếu bị khóa -->
                <div v-if="selectedUser.status === 'blocked' && selectedUser.block_reason" class="mt-4 bg-rose-50 border border-rose-200 rounded-xl p-4 flex gap-3 items-start">
                    <span class="text-rose-500 text-xl mt-0.5">⚠️</span>
                    <div>
                        <h4 class="text-xs font-black uppercase tracking-widest text-rose-600 mb-1">Tài khoản đang bị khóa</h4>
                        <p class="text-sm font-medium text-rose-800">Lý do: {{ selectedUser.block_reason }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
                    <!-- Liên hệ -->
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                        <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Thông tin liên lạc</h4>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-slate-400 shadow-sm">📱</div>
                                <div>
                                    <div class="text-[10px] uppercase font-bold text-slate-400">Số điện thoại</div>
                                    <div class="text-sm font-bold text-slate-800">{{ selectedUser.phone || 'Chưa cập nhật' }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-slate-400 shadow-sm">✉️</div>
                                <div>
                                    <div class="text-[10px] uppercase font-bold text-slate-400">Email</div>
                                    <div class="text-sm font-bold text-slate-800 break-all">{{ selectedUser.email }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tài chính & Thống kê -->
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-5 rounded-2xl border border-slate-700 shadow-lg text-white">
                        <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Ví điện tử</h4>
                        <div class="flex items-end justify-between">
                            <div>
                                <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">Số dư hiện tại</div>
                                <div class="text-3xl font-black text-emerald-400">{{ formatCurrency(selectedUser.wallet_balance) }}</div>
                            </div>
                            <div class="text-4xl opacity-20">💳</div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-white/10 flex justify-between items-center">
                            <span class="text-xs text-slate-400 font-bold">Quản lý ví</span>
                            <button @click="router.get(route('admin.wallets.index'))" class="text-xs font-bold text-orange-400 hover:text-orange-300 uppercase tracking-wider">Tới ví →</button>
                        </div>
                    </div>
                </div>

                <!-- Thao tác nhanh -->
                <div class="mt-8 flex gap-3">
                    <button 
                        v-if="selectedUser.id !== $page.props.auth.user.id"
                        @click="toggleBlock(selectedUser)"
                        class="flex-1 py-3 px-4 rounded-xl font-black text-sm uppercase tracking-widest transition-all shadow-sm"
                        :class="selectedUser.status === 'blocked' ? 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-emerald-500/30' : 'bg-rose-50 border border-rose-200 text-rose-600 hover:bg-rose-100'"
                    >
                        {{ selectedUser.status === 'blocked' ? 'Mở khóa tài khoản' : 'Khóa tài khoản' }}
                    </button>
                    <button @click="isModalOpen = false" class="py-3 px-6 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-sm uppercase tracking-widest rounded-xl transition-colors">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Khóa Tài khoản (Block Modal) -->
    <div v-if="isBlockModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="isBlockModalOpen = false"></div>
        <div class="bg-white rounded-3xl w-full max-w-lg relative z-10 shadow-2xl p-6 sm:p-8 transform transition-all">
            <h3 class="text-2xl font-black text-slate-800 mb-2">Xác nhận Khóa Tài Khoản</h3>
            <p class="text-sm text-slate-500 mb-6 font-medium">Vui lòng cung cấp lý do rõ ràng trước khi vô hiệu hóa tài khoản <strong class="text-slate-800">{{ userToBlock?.name }}</strong>.</p>
            
            <!-- AI Risk Analyzer Section -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Trợ lý AI Phân tích Rủi ro</label>
                    <button 
                        v-if="!aiAnalysisResult && !isAnalyzing" 
                        @click="analyzeRisk" 
                        class="text-xs font-bold text-orange-500 hover:text-orange-600 flex items-center gap-1"
                    >
                        <span>🤖</span> Bấm để Phân tích
                    </button>
                </div>
                
                <div v-if="isAnalyzing" class="bg-slate-50 border border-slate-100 rounded-2xl p-4 flex items-center gap-3">
                    <div class="w-5 h-5 border-2 border-orange-500 border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-sm font-medium text-slate-600">AI đang phân tích hồ sơ...</span>
                </div>
                
                <div v-else-if="aiAnalysisResult" class="bg-gradient-to-r from-orange-50 to-amber-50 border border-orange-100 rounded-2xl p-4">
                    <div class="flex gap-2 items-start">
                        <span class="text-xl">🤖</span>
                        <p class="text-sm font-medium text-slate-800 leading-relaxed">{{ aiAnalysisResult }}</p>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Lý do khóa tài khoản (Bắt buộc)</label>
                    <textarea 
                        v-model="blockReason" 
                        rows="3" 
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors resize-none"
                        placeholder="VD: Khách hàng thường xuyên boom hàng..."
                        required
                    ></textarea>
                </div>
                
                <div class="pt-2 flex gap-3">
                    <button @click="isBlockModalOpen = false" class="flex-1 py-3 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-sm uppercase tracking-widest rounded-xl transition-colors">
                        Hủy
                    </button>
                    <button @click="confirmBlock" class="flex-1 py-3 px-4 bg-rose-500 hover:bg-rose-600 text-white font-black text-sm uppercase tracking-widest rounded-xl shadow-lg shadow-rose-500/30 transition-all">
                        Xác nhận Khóa
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.2s ease-out forwards;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Custom Scrollbar for Modal */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
