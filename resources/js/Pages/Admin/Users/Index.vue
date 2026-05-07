<script setup>
import { ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UserAvatar from '@/Components/UserAvatar.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    users: Object,
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

const toggleBlock = (user) => {
    const action = user.status === 'blocked' ? 'MỞ KHÓA' : 'KHÓA';
    if (confirm(`Bạn có chắc chắn muốn ${action} tài khoản ${user.name}?`)) {
        router.patch(route('admin.users.toggle-block', user.id), {}, {
            preserveScroll: true
        });
    }
};

const getRoleColor = (userRole) => {
    switch (userRole) {
        case 'admin': return 'bg-purple-100 text-purple-700 border-purple-200';
        case 'restaurant': return 'bg-orange-100 text-orange-700 border-orange-200';
        case 'shipper': return 'bg-blue-100 text-blue-700 border-blue-200';
        default: return 'bg-green-100 text-green-700 border-green-200';
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
        case 'active': return 'bg-emerald-100 text-emerald-700';
        case 'pending': return 'bg-amber-100 text-amber-700';
        case 'blocked': return 'bg-rose-100 text-rose-700';
        default: return 'bg-slate-100 text-slate-700';
    }
};

const getStatusName = (userStatus) => {
    switch (userStatus) {
        case 'active': return 'Đang hoạt động';
        case 'pending': return 'Chờ duyệt';
        case 'blocked': return 'Đã bị khóa';
        default: return 'Không xác định';
    }
};
</script>

<template>
    <Head title="Quản lý Người dùng - Admin" />

    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Quản lý <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-pink-500">Người dùng</span></h1>
                <p class="text-slate-500 font-medium mt-2">Theo dõi và quản lý trạng thái tài khoản trên hệ thống</p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <select v-model="role" class="bg-slate-50 border border-slate-200 text-slate-700 rounded-xl px-4 py-2 font-bold focus:ring-2 focus:ring-orange-500 outline-none">
                    <option value="">Tất cả vai trò</option>
                    <option value="customer">Khách hàng</option>
                    <option value="restaurant">Quán ăn</option>
                    <option value="shipper">Tài xế</option>
                    <option value="admin">Admin</option>
                </select>

                <select v-model="status" class="bg-slate-50 border border-slate-200 text-slate-700 rounded-xl px-4 py-2 font-bold focus:ring-2 focus:ring-orange-500 outline-none">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active">Đang hoạt động</option>
                    <option value="pending">Chờ duyệt</option>
                    <option value="blocked">Đã bị khóa</option>
                </select>

                <input 
                    v-model="search" 
                    @input="handleSearch"
                    type="text" 
                    placeholder="Tìm kiếm Tên, SĐT, Email..." 
                    class="bg-slate-50 border border-slate-200 text-slate-700 rounded-xl px-4 py-2 font-bold focus:ring-2 focus:ring-orange-500 outline-none w-full sm:w-auto"
                >
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="p-4 text-xs font-black text-slate-500 uppercase tracking-widest whitespace-nowrap">Người dùng</th>
                            <th class="p-4 text-xs font-black text-slate-500 uppercase tracking-widest whitespace-nowrap">Liên hệ</th>
                            <th class="p-4 text-xs font-black text-slate-500 uppercase tracking-widest whitespace-nowrap">Vai trò</th>
                            <th class="p-4 text-xs font-black text-slate-500 uppercase tracking-widest whitespace-nowrap">Trạng thái</th>
                            <th class="p-4 text-xs font-black text-slate-500 uppercase tracking-widest whitespace-nowrap text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50 transition-colors">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <UserAvatar :user="user" size="md" />
                                    <div>
                                        <p class="font-bold text-slate-800">{{ user.name }}</p>
                                        <p class="text-xs text-slate-500 font-medium">ID: {{ user.id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <p class="text-sm font-bold text-slate-700">{{ user.phone || 'Chưa cập nhật' }}</p>
                                <p class="text-xs text-slate-500">{{ user.email }}</p>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getRoleColor(user.role)">
                                    {{ getRoleName(user.role) }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold" :class="getStatusBadge(user.status)">
                                    {{ getStatusName(user.status) }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <button 
                                    v-if="user.id !== $page.props.auth.user.id"
                                    @click="toggleBlock(user)"
                                    class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-300"
                                    :class="user.status === 'blocked' ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-rose-100 text-rose-700 hover:bg-rose-200'"
                                >
                                    {{ user.status === 'blocked' ? 'Mở khóa' : 'Khóa tài khoản' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="5" class="p-8 text-center text-slate-500 font-medium">
                                Không tìm thấy người dùng nào.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="users.links && users.links.length > 3" class="p-4 border-t border-slate-100 flex justify-center gap-2 flex-wrap">
                <template v-for="(link, index) in users.links" :key="index">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        v-html="link.label"
                        class="px-4 py-2 rounded-xl text-sm font-bold transition-colors"
                        :class="link.active ? 'bg-orange-500 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    />
                    <span
                        v-else
                        v-html="link.label"
                        class="px-4 py-2 rounded-xl text-sm font-bold bg-slate-50 text-slate-400 cursor-not-allowed"
                    />
                </template>
            </div>
        </div>
    </div>
</template>
