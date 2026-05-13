<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    logs: Object
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('vi-VN');
};

const getActionColor = (action) => {
    if (action.includes('approve')) return 'text-emerald-600 bg-emerald-50 border-emerald-100';
    if (action.includes('reject') || action.includes('block')) return 'text-rose-600 bg-rose-50 border-rose-100';
    if (action.includes('update') || action.includes('toggle')) return 'text-blue-600 bg-blue-50 border-blue-100';
    return 'text-slate-600 bg-slate-50 border-slate-100';
};

const formatActionName = (action) => {
    const map = {
        'approve_user': 'Duyệt Đối tác',
        'reject_user': 'Từ chối Đối tác',
        'toggle_block': 'Khóa/Mở tài khoản',
        'approve_withdrawal': 'Duyệt Rút tiền',
        'reject_withdrawal': 'Từ chối Rút tiền',
        'update_settings': 'Sửa Cấu hình'
    };
    return map[action] || action;
};
</script>

<template>
    <Head title="Nhật ký Hệ thống - Admin" />

    <AdminLayout>
        <div class="mb-6">
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Nhật ký Hệ thống (Audit Logs)</h2>
            <p class="text-sm text-slate-500 mt-1 font-medium">Lưu vết mọi thao tác quan trọng của toàn bộ Quản trị viên nhằm đảm bảo tính minh bạch.</p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden relative">
            <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
            </div>
            
            <div class="overflow-x-auto relative z-10">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100 text-xs font-black text-slate-400 uppercase tracking-widest">
                            <th class="p-5">Thời gian</th>
                            <th class="p-5">Quản trị viên</th>
                            <th class="p-5">Hành động</th>
                            <th class="p-5 w-1/3">Chi tiết</th>
                            <th class="p-5">IP / Thiết bị</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-5">
                                <span class="text-sm font-bold text-slate-700">{{ formatDate(log.created_at) }}</span>
                            </td>
                            <td class="p-5">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center font-black text-xs text-slate-500">
                                        {{ log.admin?.name?.charAt(0) || 'A' }}
                                    </div>
                                    <span class="text-sm font-black text-slate-800">{{ log.admin?.name || 'Hệ thống' }}</span>
                                </div>
                            </td>
                            <td class="p-5">
                                <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border" :class="getActionColor(log.action)">
                                    {{ formatActionName(log.action) }}
                                </span>
                            </td>
                            <td class="p-5">
                                <p class="text-sm font-medium text-slate-600 line-clamp-2 leading-relaxed">{{ log.description }}</p>
                            </td>
                            <td class="p-5">
                                <p class="text-xs font-mono font-bold text-slate-500 mb-0.5">{{ log.ip_address }}</p>
                                <p class="text-[10px] text-slate-400 truncate max-w-[150px]" :title="log.user_agent">{{ log.user_agent }}</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="logs.links && logs.links.length > 3" class="p-5 border-t border-slate-100 bg-slate-50/50 flex justify-center gap-1.5 flex-wrap">
                <template v-for="(link, pIndex) in logs.links" :key="pIndex">
                    <a v-if="link.url" :href="link.url"
                        class="min-w-[2.5rem] h-10 px-2 flex items-center justify-center rounded-xl text-sm font-bold transition-all"
                        :class="link.active ? 'bg-slate-800 text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100'"
                        v-html="link.label"></a>
                    <span v-else class="min-w-[2.5rem] h-10 px-2 flex items-center justify-center rounded-xl text-sm font-bold bg-transparent text-slate-300"
                        v-html="link.label"></span>
                </template>
            </div>
        </div>
    </AdminLayout>
</template>
