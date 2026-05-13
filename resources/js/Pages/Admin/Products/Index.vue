<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    products: Object,
    stats: Object,
    filters: Object,
});

const toast = useToast();

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

// Throttle search
let searchTimeout = null;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('admin.products.index'),
            { search: search.value, status: status.value },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }, 500);
};

watch(search, handleSearch);
watch(status, handleSearch);

const form = useForm({
    reason: ''
});
const isModalOpen = ref(false);
const selectedProduct = ref(null);
const actionType = ref(''); // 'approve', 'reject', or 'view'

const openModal = (product, action = 'view') => {
    selectedProduct.value = product;
    actionType.value = action;
    form.reset();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        selectedProduct.value = null;
        actionType.value = '';
    }, 300);
};

const submitAction = () => {
    if (actionType.value === 'reject' && !form.reason.trim()) {
        toast.warning('⚠️ Vui lòng nhập lý do khóa món ăn.');
        return;
    }
    
    const routeName = actionType.value === 'approve' ? 'admin.products.approve' : 'admin.products.reject';
    
    form.post(route(routeName, selectedProduct.value.id), {
        onSuccess: () => {
            if (actionType.value === 'approve') {
                toast.success(`✅ Đã mở khóa/duyệt món ăn "${selectedProduct.value.name}".`);
            } else {
                toast.success(`❌ Đã khóa món ăn "${selectedProduct.value.name}".`);
            }
            closeModal();
        },
        onError: (errors) => {
            if (errors.reason) {
                toast.error(errors.reason);
            } else {
                toast.error('⚠️ Có lỗi xảy ra, vui lòng thử lại.');
            }
        },
    });
};
</script>

<template>
    <Head title="Quản lý toàn bộ món ăn" />

    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black text-gray-800 tracking-tight">Quản lý toàn bộ món ăn</h2>
            <p class="text-gray-500 mt-2 font-medium">Kiểm soát tất cả các món ăn đang có trên hệ thống.</p>
        </div>
    </div>

    <!-- Thống kê -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative z-10 flex flex-col items-center text-center">
                <span class="text-blue-500 bg-blue-50 w-12 h-12 rounded-xl flex items-center justify-center text-2xl font-black mb-3">🍔</span>
                <span class="text-2xl font-black text-slate-800">{{ stats.total }}</span>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Tổng món ăn</span>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-green-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative z-10 flex flex-col items-center text-center">
                <span class="text-green-500 bg-green-50 w-12 h-12 rounded-xl flex items-center justify-center text-2xl font-black mb-3">✅</span>
                <span class="text-2xl font-black text-slate-800">{{ stats.approved }}</span>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Đang hiển thị</span>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-orange-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative z-10 flex flex-col items-center text-center">
                <span class="text-orange-500 bg-orange-50 w-12 h-12 rounded-xl flex items-center justify-center text-2xl font-black mb-3">⏳</span>
                <span class="text-2xl font-black text-slate-800">{{ stats.pending }}</span>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Chờ duyệt</span>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-red-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative z-10 flex flex-col items-center text-center">
                <span class="text-red-500 bg-red-50 w-12 h-12 rounded-xl flex items-center justify-center text-2xl font-black mb-3">🔒</span>
                <span class="text-2xl font-black text-slate-800">{{ stats.rejected }}</span>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Đang bị khóa</span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6 flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input 
                    v-model="search"
                    type="text" 
                    placeholder="Tìm kiếm theo tên món hoặc tên quán..." 
                    class="w-full pl-11 pr-4 py-3 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all font-medium text-sm"
                >
            </div>
        </div>
        <div class="w-full sm:w-48">
            <select v-model="status" class="w-full px-4 py-3 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all font-medium text-sm text-gray-700">
                <option value="">Tất cả trạng thái</option>
                <option value="approved">Đang hiển thị</option>
                <option value="pending">Chờ duyệt</option>
                <option value="rejected">Bị khóa / Từ chối</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase tracking-widest text-slate-500 font-black">
                        <th class="p-4 w-16 text-center">Ảnh</th>
                        <th class="p-4">Thông tin món</th>
                        <th class="p-4">Quán ăn</th>
                        <th class="p-4">Trạng thái</th>
                        <th class="p-4 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="product in products.data" :key="product.id" class="hover:bg-slate-50/50 transition-colors">
                        <td class="p-4 text-center">
                            <div class="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden mx-auto flex items-center justify-center">
                                <img v-if="product.image" :src="product.image.startsWith('http') ? product.image : '/storage/' + product.image" class="w-full h-full object-cover" />
                                <span v-else class="text-[10px] font-bold text-slate-400">Trống</span>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-900">{{ product.name }}</div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full font-bold">{{ product.category?.name || 'Không có danh mục' }}</span>
                                <span class="font-black text-orange-500 text-xs">{{ new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(product.price) }}</span>
                            </div>
                        </td>
                        <td class="p-4 text-sm text-gray-600">
                            <p class="font-bold text-slate-800">{{ product.user?.restaurant_name || product.user?.name }}</p>
                        </td>
                        <td class="p-4">
                            <span v-if="product.status === 'approved'" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Hiển thị
                            </span>
                            <span v-else-if="product.status === 'pending'" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-orange-100 text-orange-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span> Chờ duyệt
                            </span>
                            <span v-else class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-red-100 text-red-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Đã khóa
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <button 
                                @click="openModal(product, 'view')" 
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black shadow-sm transition hover:shadow-md active:scale-95 bg-slate-900 text-white hover:bg-orange-500 mr-2"
                            >
                                👁️ CHI TIẾT
                            </button>
                            <button 
                                v-if="product.status === 'approved'" 
                                @click="openModal(product, 'reject')" 
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black shadow-sm transition hover:shadow-md active:scale-95 bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white"
                            >
                                🔒 KHÓA
                            </button>
                            <button 
                                v-else 
                                @click="openModal(product, 'approve')" 
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black shadow-sm transition hover:shadow-md active:scale-95 bg-green-50 text-green-600 border border-green-200 hover:bg-green-600 hover:text-white"
                            >
                                ✅ {{ product.status === 'pending' ? 'DUYỆT' : 'MỞ KHÓA' }}
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div v-if="products.data.length === 0" class="p-16 text-center">
                <span class="text-4xl mb-4 block">🔍</span>
                <p class="text-slate-500 font-medium">Không tìm thấy món ăn nào.</p>
            </div>
        </div>
        
        <!-- Pagination -->
        <div v-if="products.links && products.links.length > 3" class="p-4 border-t border-slate-100 flex justify-center bg-slate-50/50">
            <div class="flex gap-1 bg-white p-1 rounded-xl shadow-sm border border-slate-200">
                <template v-for="(link, p) in products.links" :key="p">
                    <div 
                        v-if="link.url === null" 
                        class="px-4 py-2 text-sm text-slate-400 font-bold" 
                        v-html="link.label" 
                    />
                    <router-link 
                        v-else 
                        :href="link.url" 
                        class="px-4 py-2 text-sm rounded-lg font-bold transition-all"
                        :class="link.active ? 'bg-orange-500 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100'"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </div>

    <!-- Modal Chi tiết & Khóa / Mở khóa -->
    <Modal :show="isModalOpen" @close="closeModal" maxWidth="2xl">
        <div class="p-6 md:p-8" v-if="selectedProduct">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6 sticky top-0 bg-white z-10">
                <h3 class="text-2xl font-black text-slate-800">
                    {{ actionType === 'view' ? 'Chi tiết món ăn' : (actionType === 'approve' ? 'Xác nhận Mở khóa?' : 'Xác nhận Khóa?') }}
                </h3>
                <button @click="closeModal" class="text-slate-400 hover:bg-slate-100 rounded-full p-2 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="max-h-[70vh] overflow-y-auto pr-2 custom-scrollbar">
                
                <!-- Thông tin cơ bản: Hình ảnh & Giá -->
                <div class="flex flex-col md:flex-row gap-6 mb-6">
                    <div class="w-full md:w-2/5">
                        <div class="aspect-square rounded-2xl overflow-hidden shadow-sm border border-slate-100 bg-slate-50 flex items-center justify-center relative">
                            <img v-if="selectedProduct.image" :src="selectedProduct.image.startsWith('http') ? selectedProduct.image : '/storage/' + selectedProduct.image" class="w-full h-full object-cover" />
                            <span v-else class="text-slate-400 text-sm font-bold">Chưa có ảnh</span>
                            
                            <div class="absolute top-3 left-3">
                                <span v-if="selectedProduct.status === 'approved'" class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-green-500 text-white shadow-md">Hiển thị</span>
                                <span v-else-if="selectedProduct.status === 'pending'" class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-orange-500 text-white shadow-md animate-pulse">Chờ duyệt</span>
                                <span v-else class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-red-500 text-white shadow-md">Đã khóa</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="w-full md:w-3/5 flex flex-col justify-center space-y-3">
                        <div>
                            <span class="bg-orange-100 text-orange-600 text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-md">{{ selectedProduct.category?.name || 'Chưa phân loại' }}</span>
                            <h4 class="text-2xl font-black text-slate-900 mt-2 leading-tight">{{ selectedProduct.name }}</h4>
                        </div>
                        
                        <div class="flex items-end gap-3 mt-1">
                            <p class="text-3xl font-black text-orange-500">{{ new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(selectedProduct.discount_price || selectedProduct.price) }}</p>
                            <p v-if="selectedProduct.discount_price" class="text-lg font-bold text-slate-400 line-through mb-1">{{ new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(selectedProduct.price) }}</p>
                        </div>

                        <div class="flex flex-wrap gap-2 mt-2">
                            <span class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-100 px-2.5 py-1.5 rounded-lg text-xs font-bold text-slate-600">
                                📦 {{ selectedProduct.stock_quantity }} suất
                            </span>
                            <span class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-100 px-2.5 py-1.5 rounded-lg text-xs font-bold text-slate-600">
                                🕒 {{ selectedProduct.available_from ? selectedProduct.available_from.substring(0,5) : '00:00' }} - {{ selectedProduct.available_to ? selectedProduct.available_to.substring(0,5) : '23:59' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Thông tin Quán ăn (Compact) -->
                <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100 mb-6 flex items-center gap-4">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center font-black text-xl text-blue-500 border-2 border-blue-200 shrink-0">
                        {{ (selectedProduct.user?.restaurant_name || selectedProduct.user?.name || 'R').charAt(0) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <h4 class="font-black text-slate-900 text-base truncate">{{ selectedProduct.user?.restaurant_name || selectedProduct.user?.name }}</h4>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-widest" :class="selectedProduct.user?.is_accepting_orders ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-600'">
                                <span class="w-1 h-1 rounded-full" :class="selectedProduct.user?.is_accepting_orders ? 'bg-green-500' : 'bg-slate-400'"></span>
                                {{ selectedProduct.user?.is_accepting_orders ? 'Mở cửa' : 'Đóng cửa' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium truncate flex items-center gap-2">
                            <span>📞 {{ selectedProduct.user?.phone || 'N/A' }}</span>
                            <span>•</span>
                            <span>📍 {{ selectedProduct.user?.address || 'N/A' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Mô tả & Toppings -->
                <div class="mb-6 space-y-4">
                    <div v-if="selectedProduct.description">
                        <p class="text-sm font-bold text-slate-800 mb-1">Mô tả:</p>
                        <p class="text-sm text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100 whitespace-pre-wrap leading-relaxed">{{ selectedProduct.description }}</p>
                    </div>

                    <div v-if="selectedProduct.options && selectedProduct.options.length > 0">
                        <p class="text-sm font-bold text-slate-800 mb-2">Tùy chọn / Topping:</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div v-for="opt in selectedProduct.options" :key="opt.id" class="flex justify-between items-center bg-slate-50 px-3 py-2 rounded-xl border border-slate-100">
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded font-bold">{{ opt.option_name }}</span>
                                    <span class="text-sm text-slate-800 font-bold">{{ opt.option_value }}</span>
                                </div>
                                <span class="text-xs font-black text-orange-500">+{{ new Intl.NumberFormat('vi-VN').format(opt.additional_price) }}đ</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Khu vực Khóa/Mở khóa -->
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <!-- Xem lý do khóa -->
                    <div v-if="selectedProduct.status === 'rejected' && selectedProduct.reject_reason && actionType === 'view'" class="bg-red-50 p-4 rounded-xl border border-red-100 mb-2">
                        <p class="text-xs font-black text-red-800 uppercase mb-1">Lý do khóa hiện tại:</p>
                        <p class="text-sm text-red-700 font-medium">{{ selectedProduct.reject_reason }}</p>
                    </div>

                    <!-- Form Khóa món ăn -->
                    <div v-if="actionType === 'reject'" class="bg-red-50 p-4 rounded-xl border border-red-200 mb-2">
                        <label class="block text-sm font-black text-red-800 mb-2">Nhập lý do khóa (bắt buộc):</label>
                        <textarea 
                            v-model="form.reason" 
                            rows="3" 
                            class="w-full border-red-200 rounded-xl focus:border-red-400 focus:ring focus:ring-red-200 text-sm bg-white font-medium" 
                            placeholder="VD: Món ăn vi phạm tiêu chuẩn cộng đồng..."
                        ></textarea>
                        <div v-if="form.errors.reason" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.reason }}</div>
                    </div>

                    <div v-if="actionType === 'approve'" class="bg-green-50 p-4 rounded-xl border border-green-200 mb-2 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <p class="text-sm font-bold text-green-800">Xác nhận mở khóa món ăn này. Món sẽ được hiển thị trên hệ thống.</p>
                    </div>
                </div>
            </div>

            <!-- Hành động -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 bg-white mt-2">
                <button type="button" @click="closeModal" class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                    Đóng
                </button>
                
                <template v-if="actionType === 'view'">
                    <button v-if="selectedProduct.status === 'approved'" @click="actionType = 'reject'" class="px-6 py-2.5 rounded-xl text-sm font-black text-red-600 bg-red-50 hover:bg-red-100 transition-colors border border-red-200">
                        🔒 KHÓA
                    </button>
                    <button v-else @click="actionType = 'approve'" class="px-6 py-2.5 rounded-xl text-sm font-black text-white bg-green-500 hover:bg-green-600 transition-colors shadow-lg shadow-green-500/30">
                        ✅ MỞ KHÓA
                    </button>
                </template>
                
                <template v-else-if="actionType === 'reject'">
                    <button @click="actionType = 'view'" class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 transition-colors">Hủy</button>
                    <button @click="submitAction" class="px-6 py-2.5 rounded-xl text-sm font-black text-white bg-red-500 hover:bg-red-600 transition-colors shadow-lg shadow-red-500/30">
                        XÁC NHẬN KHÓA
                    </button>
                </template>

                <template v-else-if="actionType === 'approve'">
                    <button @click="actionType = 'view'" class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 transition-colors">Hủy</button>
                    <button @click="submitAction" class="px-6 py-2.5 rounded-xl text-sm font-black text-white bg-green-500 hover:bg-green-600 transition-colors shadow-lg shadow-green-500/30">
                        XÁC NHẬN MỞ KHÓA
                    </button>
                </template>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9; 
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1; 
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8; 
}
</style>
