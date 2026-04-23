<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Modal from "@/Components/Modal.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import { ref, computed } from "vue";
const toast = useToast();

defineProps({
    products: Array,
});

const form = useForm({});
const isModalOpen = ref(false);
const selectedProduct = ref(null);
const actionType = ref('');

const modalTitle = computed(() => {
    if (actionType.value === 'approve') return 'Xác nhận duyệt món ăn';
    if (actionType.value === 'reject') return 'Xác nhận từ chối món ăn';
    return 'Xác nhận hành động';
});

const modalMessage = computed(() => {
    if (!selectedProduct.value) return '';
    return actionType.value === 'approve'
        ? `Bạn có chắc chắn muốn duyệt món ăn "${selectedProduct.value.name}" không? Món này sẽ được hiển thị trên hệ thống.`
        : `Bạn có chắc chắn muốn từ chối món ăn "${selectedProduct.value.name}" không? Món này sẽ không xuất hiện trên hệ thống.`;
});

const openProductModal = (product, type) => {
    selectedProduct.value = product;
    actionType.value = type;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedProduct.value = null;
    actionType.value = '';
};

const confirmProductAction = () => {
    if (!selectedProduct.value || !actionType.value) return;

    const routeName = actionType.value === 'approve'
        ? 'admin.products.approve'
        : 'admin.products.reject';

    form.post(route(routeName, selectedProduct.value.id), {
        onSuccess: () => {
            toast.success(
                actionType.value === 'approve'
                    ? `✅ Đã duyệt món ăn "${selectedProduct.value.name}" thành công!`
                    : `❌ Đã từ chối món ăn "${selectedProduct.value.name}".`
            );
            closeModal();
        },
        onError: () => {
            toast.error('⚠️ Có lỗi xảy ra, vui lòng thử lại.');
            closeModal();
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

        <Modal :show="isModalOpen" @close="closeModal" maxWidth="lg">
            <div class="p-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-r from-orange-500 to-yellow-500 text-white text-2xl">
                        {{ actionType === 'approve' ? '✅' : '❌' }}
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900">{{ modalTitle }}</h3>
                        <p class="mt-2 text-sm text-gray-600">{{ modalMessage }}</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        @click="closeModal"
                        class="rounded-lg border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-600 transition hover:bg-gray-50"
                    >
                        Hủy
                    </button>
                    <button
                        type="button"
                        @click="confirmProductAction"
                        :class="actionType === 'approve' ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-red-600 hover:bg-red-700 text-white'"
                        class="rounded-lg px-5 py-3 text-sm font-semibold transition"
                    >
                        {{ actionType === 'approve' ? 'Duyệt món ăn' : 'Từ chối món ăn' }}
                    </button>
                </div>
            </div>
        </Modal>

        <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="p-4 font-bold text-gray-700">Món ăn</th>
                        <th class="p-4 font-bold text-gray-700">Quán ăn</th>
                        <th class="p-4 font-bold text-gray-700">Giá</th>
                        <th class="p-4 font-bold text-gray-700">Ngày tạo</th>
                        <th class="p-4 font-bold text-gray-700 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50 transition-colors">
                        <td class="p-4">
                            <div class="font-bold text-gray-900">{{ product.name }}</div>
                            <div class="text-xs text-gray-500">{{ product.category?.name || 'Không có danh mục' }}</div>
                        </td>
                        <td class="p-4 text-sm text-gray-600">
                            <p class="font-bold text-gray-800">{{ product.user?.restaurant_name || product.user?.name }}</p>
                            <p class="text-xs text-gray-500">{{ product.user?.email }}</p>
                        </td>
                        <td class="p-4 font-semibold text-gray-900">{{ new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(product.price) }}</td>
                        <td class="p-4 text-center text-xs font-medium text-gray-500 uppercase">{{ product.created_at ? new Date(product.created_at).toLocaleDateString('vi-VN') : 'N/A' }}</td>
                        <td class="p-4">
                            <div class="flex justify-center items-center gap-3">
                                <button @click="openProductModal(product, 'approve')" class="px-5 py-2 rounded-lg text-xs font-black shadow-md transition hover:opacity-80 active:scale-95" style="background-color: #16a34a; color: white; min-width: 80px;">DUYỆT</button>
                                <button @click="openProductModal(product, 'reject')" class="px-5 py-2 rounded-lg text-xs font-black shadow-md transition hover:bg-gray-100 active:scale-95" style="background-color: white; color: #dc2626; border: 1px solid #fee2e2; min-width: 80px;">TỪ CHỐI</button>
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
