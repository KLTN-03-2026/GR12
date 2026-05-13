<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Modal from "@/Components/Modal.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import { ref } from "vue";
const toast = useToast();

defineProps({
    products: Array,
});

const form = useForm({
    reason: ''
});
const isModalOpen = ref(false);
const selectedProduct = ref(null);
const rejectReasonVisible = ref(false);

const openProductModal = (product) => {
    selectedProduct.value = product;
    rejectReasonVisible.value = false;
    form.reset();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        selectedProduct.value = null;
        rejectReasonVisible.value = false;
    }, 300);
};

const confirmApprove = () => {
    form.post(route('admin.products.approve', selectedProduct.value.id), {
        onSuccess: () => {
            toast.success(`✅ Đã duyệt món ăn "${selectedProduct.value.name}" thành công!`);
            closeModal();
        },
        onError: () => {
            toast.error('⚠️ Có lỗi xảy ra, vui lòng thử lại.');
        },
    });
};

const confirmReject = () => {
    if (!form.reason.trim()) {
        toast.warning('⚠️ Vui lòng nhập lý do từ chối để nhà hàng có thể cải thiện.');
        return;
    }
    
    form.post(route('admin.products.reject', selectedProduct.value.id), {
        onSuccess: () => {
            toast.success(`❌ Đã từ chối món ăn "${selectedProduct.value.name}".`);
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
    <AdminLayout>
        <Head title="Duyệt món ăn" />

        <div class="mb-6">
            <h2 class="text-2xl font-black text-gray-800">Duyệt món ăn chờ phê duyệt</h2>
            <p class="text-gray-500">Admin có thể duyệt hoặc từ chối các món ăn vừa được quán ăn đăng lên.</p>
        </div>

        <Modal :show="isModalOpen" @close="closeModal" maxWidth="2xl">
            <div class="p-6 md:p-8" v-if="selectedProduct">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                    <h3 class="text-2xl font-black text-slate-800">Kiểm duyệt món ăn</h3>
                    <button @click="closeModal" class="text-slate-400 hover:bg-slate-100 rounded-full p-2 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="flex flex-col md:flex-row gap-6 mb-6">
                    <!-- Hình ảnh -->
                    <div class="w-full md:w-1/3">
                        <div class="aspect-square rounded-2xl overflow-hidden shadow-sm border border-slate-100 bg-slate-50 flex items-center justify-center relative">
                            <img v-if="selectedProduct.image" :src="selectedProduct.image.startsWith('http') ? selectedProduct.image : '/storage/' + selectedProduct.image" class="w-full h-full object-cover" />
                            <span v-else class="text-slate-400 text-sm font-bold">Chưa có ảnh</span>
                        </div>
                    </div>
                    
                    <!-- Thông tin -->
                    <div class="w-full md:w-2/3 space-y-4">
                        <div>
                            <span class="bg-orange-100 text-orange-600 text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-md">{{ selectedProduct.category?.name || 'Chưa phân loại' }}</span>
                            <h4 class="text-xl font-black text-slate-900 mt-2">{{ selectedProduct.name }}</h4>
                            <p class="text-2xl font-black text-orange-500 mt-1">{{ new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(selectedProduct.price) }}</p>
                        </div>
                        
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex items-center gap-3">
                            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center font-black text-orange-500 border border-slate-200">
                                {{ (selectedProduct.user?.restaurant_name || selectedProduct.user?.name || 'R').charAt(0) }}
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Nhà hàng đối tác</p>
                                <p class="font-bold text-slate-800 text-sm">{{ selectedProduct.user?.restaurant_name || selectedProduct.user?.name }}</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-bold text-slate-800 mb-1">Mô tả món ăn:</p>
                            <p class="text-sm text-slate-600 bg-slate-50 p-3 rounded-xl min-h-[60px] whitespace-pre-wrap">{{ selectedProduct.description || 'Không có mô tả.' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Tùy chọn (Toppings) -->
                <div v-if="selectedProduct.options && selectedProduct.options.length > 0" class="mb-6">
                    <p class="text-sm font-bold text-slate-800 mb-3">Tùy chọn / Topping đi kèm:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div v-for="opt in selectedProduct.options" :key="opt.id" class="flex justify-between items-center bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-100">
                            <div>
                                <span class="text-[10px] bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded mr-2 font-bold">{{ opt.option_name }}</span>
                                <span class="text-sm text-slate-700 font-medium">{{ opt.option_value }}</span>
                            </div>
                            <span class="text-sm font-bold text-orange-500">+{{ new Intl.NumberFormat('vi-VN').format(opt.additional_price) }}đ</span>
                        </div>
                    </div>
                </div>

                <!-- Vùng hiển thị nhập lý do từ chối -->
                <div v-if="rejectReasonVisible" class="bg-red-50 p-4 rounded-2xl border border-red-100 mb-6 transition-all">
                    <label class="block text-sm font-bold text-red-800 mb-2">Lý do từ chối (Bắt buộc):</label>
                    <textarea v-model="form.reason" rows="3" class="w-full border-red-200 rounded-xl focus:border-red-400 focus:ring focus:ring-red-200 focus:ring-opacity-50 text-sm bg-white" placeholder="VD: Hình ảnh không rõ nét, mô tả thiếu thông tin..."></textarea>
                    <div v-if="form.errors.reason" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.reason }}</div>
                </div>

                <!-- Hành động -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="closeModal" class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">Đóng</button>
                    
                    <template v-if="!rejectReasonVisible">
                        <button type="button" @click="rejectReasonVisible = true" class="px-5 py-2.5 rounded-xl text-sm font-black text-red-600 bg-red-50 hover:bg-red-100 transition-colors border border-red-200">Từ Chối</button>
                        <button type="button" @click="confirmApprove" class="px-6 py-2.5 rounded-xl text-sm font-black text-white bg-green-500 hover:bg-green-600 transition-colors shadow-lg shadow-green-500/30">Duyệt Món Ăn</button>
                    </template>
                    
                    <template v-else>
                        <button type="button" @click="rejectReasonVisible = false" class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 transition-colors">Hủy từ chối</button>
                        <button type="button" @click="confirmReject" class="px-6 py-2.5 rounded-xl text-sm font-black text-white bg-red-500 hover:bg-red-600 transition-colors shadow-lg shadow-red-500/30">Xác nhận Từ Chối</button>
                    </template>
                </div>
            </div>
        </Modal>

        <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="p-4 font-bold text-gray-700 w-16 text-center">Ảnh</th>
                        <th class="p-4 font-bold text-gray-700">Món ăn</th>
                        <th class="p-4 font-bold text-gray-700">Quán ăn</th>
                        <th class="p-4 font-bold text-gray-700">Giá</th>
                        <th class="p-4 font-bold text-gray-700 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50 transition-colors">
                        <td class="p-4 text-center">
                            <div class="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden mx-auto flex items-center justify-center">
                                <img v-if="product.image" :src="product.image.startsWith('http') ? product.image : '/storage/' + product.image" class="w-full h-full object-cover" />
                                <span v-else class="text-[10px] font-bold text-slate-400">Trống</span>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-gray-900">{{ product.name }}</div>
                            <div class="text-[10px] bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full inline-block mt-1 font-bold">{{ product.category?.name || 'Không có danh mục' }}</div>
                        </td>
                        <td class="p-4 text-sm text-gray-600">
                            <p class="font-bold text-gray-800">{{ product.user?.restaurant_name || product.user?.name }}</p>
                        </td>
                        <td class="p-4 font-black text-orange-500">{{ new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(product.price) }}</td>
                        <td class="p-4">
                            <div class="flex justify-center items-center">
                                <button @click="openProductModal(product)" class="px-5 py-2.5 rounded-xl text-xs font-black shadow-sm transition hover:shadow-md active:scale-95 bg-slate-900 hover:bg-orange-500 text-white flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    KIỂM DUYỆT
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="products.length === 0" class="p-20 text-center text-gray-400">
                <p class="text-sm font-medium">✨ Hiện không có món ăn nào đang chờ phê duyệt.</p>
            </div>
        </div>
    </AdminLayout>
</template>
