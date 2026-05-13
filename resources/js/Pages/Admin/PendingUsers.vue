<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import { useToast } from "vue-toastification";
const toast = useToast();

const props = defineProps({
    users: Array,
});

const form = useForm({
    reason: ''
});
const selectedUser = ref(null);
const showModal = ref(false);
const rejectReasonVisible = ref(false);

const openReviewModal = (user) => {
    selectedUser.value = user;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    setTimeout(() => {
        selectedUser.value = null;
        rejectReasonVisible.value = false;
    }, 300);
};

const approveUser = () => {
    if (!selectedUser.value) return;
    form.post(route("admin.approve", selectedUser.value.id), {
        onSuccess: () => {
            toast.success("🚀 Đã duyệt đối tác thành công!");
            closeModal();
        },
        onError: () => toast.error("Có lỗi xảy ra, vui lòng thử lại."),
    });
};

const rejectUser = () => {
    if (!selectedUser.value) return;
    
    if (!form.reason.trim()) {
        toast.warning('⚠️ Vui lòng nhập lý do từ chối để đối tác có thể cải thiện.');
        return;
    }

    form.post(route("admin.reject", selectedUser.value.id), {
        onSuccess: () => {
            toast.success("Đã từ chối hồ sơ đối tác.");
            closeModal();
        },
        onError: (errors) => {
            if (errors.reason) {
                toast.error(errors.reason);
            } else {
                toast.error("Có lỗi xảy ra, vui lòng thử lại.");
            }
        }
    });
};

const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    return new Date(dateString).toLocaleDateString("vi-VN", {
        year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Duyệt Hồ sơ Đối tác - Admin" />

        <div class="mb-8">
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">Xét duyệt Đối tác mới</h2>
            <p class="text-slate-500 mt-1 font-medium">Kiểm tra thông tin giấy tờ và năng lực của Quán ăn / Shipper trước khi cho phép hoạt động.</p>
        </div>

        <!-- Empty State -->
        <div v-if="users.length === 0" class="bg-white rounded-3xl shadow-sm border border-slate-100 p-16 text-center">
            <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 border-2 border-slate-100">
                <span class="text-5xl opacity-50">✨</span>
            </div>
            <h3 class="text-xl font-black text-slate-800 mb-2">Không có hồ sơ chờ duyệt</h3>
            <p class="text-slate-500 font-medium">Tất cả đối tác đăng ký đã được xử lý hoàn tất.</p>
        </div>

        <!-- Grid Cards -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="user in users" :key="user.id" 
                 class="bg-white rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-100 overflow-hidden group flex flex-col relative transform hover:-translate-y-1">
                
                <!-- Card Header with Avatar Background -->
                <div class="h-24 relative" :class="user.role === 'restaurant' ? 'bg-orange-500' : 'bg-blue-600'">
                    <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] mix-blend-overlay"></div>
                    <div class="absolute -bottom-8 left-6">
                        <div class="w-16 h-16 rounded-2xl bg-white p-1.5 shadow-lg relative">
                            <div class="w-full h-full rounded-xl flex items-center justify-center text-2xl" :class="user.role === 'restaurant' ? 'bg-orange-100' : 'bg-blue-100'">
                                {{ user.role === 'restaurant' ? '🏪' : '🛵' }}
                            </div>
                            <div class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-rose-500 border-2 border-white flex items-center justify-center animate-pulse">
                                <span class="text-white text-[10px] font-bold">!</span>
                            </div>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <span class="px-3 py-1 bg-white/20 backdrop-blur-md text-white text-[10px] font-black uppercase tracking-widest rounded-full shadow-sm border border-white/10">
                            {{ user.role === 'restaurant' ? 'Quán ăn' : 'Tài xế' }}
                        </span>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="p-6 pt-12 flex-1 flex flex-col">
                    <h3 class="text-lg font-black text-slate-800 line-clamp-1 group-hover:text-blue-600 transition-colors">{{ user.name }}</h3>
                    <p class="text-xs text-slate-500 font-medium mb-4">{{ user.email }}</p>
                    
                    <div class="space-y-3 flex-1">
                        <div class="flex items-center gap-2 text-sm bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <span class="text-slate-400">📞</span>
                            <span class="font-bold text-slate-700">{{ user.phone }}</span>
                        </div>
                        
                        <div v-if="user.role === 'restaurant'" class="flex items-start gap-2 text-sm bg-orange-50/50 p-2.5 rounded-xl border border-orange-100/50">
                            <span class="text-orange-400 mt-0.5">📍</span>
                            <span class="font-medium text-slate-700 line-clamp-2 leading-relaxed text-xs">{{ user.address || 'Chưa cung cấp địa chỉ' }}</span>
                        </div>
                        
                        <div v-if="user.role === 'shipper'" class="flex items-center gap-2 text-sm bg-blue-50/50 p-2.5 rounded-xl border border-blue-100/50">
                            <span class="text-blue-400">🪪</span>
                            <span class="font-black text-slate-700 font-mono tracking-wider text-xs uppercase">{{ user.license_plate || 'Chưa có biển số' }}</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100">
                        <button @click="openReviewModal(user)" class="w-full py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-md transition-all group-hover:shadow-lg">
                            Bắt đầu Xét duyệt
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Modal -->
        <div v-if="showModal && selectedUser" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeModal"></div>
            
            <div class="bg-white rounded-3xl w-full max-w-2xl relative z-10 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <!-- Modal Header -->
                <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="text-xl font-black text-slate-800">Hồ sơ chờ duyệt #{{ selectedUser.id }}</h3>
                        <p class="text-xs text-slate-500 font-medium mt-1">Đăng ký lúc: {{ formatDate(selectedUser.created_at) }}</p>
                    </div>
                    <button @click="closeModal" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-200 text-slate-500 hover:bg-slate-300 hover:text-slate-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Modal Content (Scrollable) -->
                <div class="p-8 overflow-y-auto custom-scrollbar flex-1 space-y-8">
                    
                    <!-- Basic Info -->
                    <div>
                        <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span>Thông tin cơ bản</span>
                            <div class="h-px bg-slate-100 flex-1"></div>
                        </h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Họ và tên</p>
                                <p class="font-black text-slate-800">{{ selectedUser.name }}</p>
                            </div>
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Số điện thoại</p>
                                <p class="font-black text-slate-800">{{ selectedUser.phone }}</p>
                            </div>
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 col-span-2">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Email</p>
                                <p class="font-bold text-slate-700">{{ selectedUser.email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Role Specific Info -->
                    <div>
                        <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span>Thông tin nghiệp vụ ({{ selectedUser.role === 'restaurant' ? 'Quán ăn' : 'Tài xế' }})</span>
                            <div class="h-px bg-slate-100 flex-1"></div>
                        </h4>
                        
                        <!-- For Restaurant -->
                        <div v-if="selectedUser.role === 'restaurant'" class="space-y-4">
                            <div class="bg-orange-50 rounded-2xl p-4 border border-orange-100">
                                <p class="text-[10px] font-bold text-orange-400 uppercase tracking-widest mb-1">Tên Quán đăng ký</p>
                                <p class="font-black text-orange-900 text-lg">{{ selectedUser.restaurant_name || selectedUser.name }}</p>
                            </div>
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Địa chỉ kinh doanh</p>
                                <p class="font-bold text-slate-700">{{ selectedUser.address || 'Chưa cập nhật địa chỉ' }}</p>
                            </div>
                        </div>

                        <!-- For Shipper -->
                        <div v-if="selectedUser.role === 'shipper'" class="grid grid-cols-2 gap-4">
                            <div class="bg-blue-50 rounded-2xl p-4 border border-blue-100">
                                <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-1">Căn cước công dân</p>
                                <p class="font-black text-blue-900 font-mono tracking-wider">{{ selectedUser.id_card_number || 'Chưa cung cấp' }}</p>
                            </div>
                            <div class="bg-blue-50 rounded-2xl p-4 border border-blue-100">
                                <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-1">Biển số xe</p>
                                <p class="font-black text-blue-900 font-mono tracking-wider uppercase">{{ selectedUser.license_plate || 'Chưa cung cấp' }}</p>
                            </div>
                            <!-- Mock Document Verification -->
                            <div class="col-span-2 bg-slate-50 rounded-2xl p-5 border border-dashed border-slate-300 text-center">
                                <div class="w-12 h-12 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <span class="text-xl">📄</span>
                                </div>
                                <p class="text-xs font-bold text-slate-500 mb-1">Hệ thống chưa hỗ trợ nộp ảnh giấy tờ</p>
                                <p class="text-[10px] text-slate-400">Vui lòng kiểm tra lại thông tin căn cước trên cơ sở dữ liệu quốc gia trước khi duyệt.</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Vùng hiển thị nhập lý do từ chối -->
                <div v-if="rejectReasonVisible" class="bg-red-50 p-4 border-t border-red-100 transition-all">
                    <label class="block text-sm font-bold text-red-800 mb-2">Lý do từ chối (Bắt buộc):</label>
                    <textarea v-model="form.reason" rows="3" class="w-full border-red-200 rounded-xl focus:border-red-400 focus:ring focus:ring-red-200 focus:ring-opacity-50 text-sm bg-white" placeholder="VD: Ảnh Căn cước công dân bị mờ, Sai thông tin..."></textarea>
                    <div v-if="form.errors.reason" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.reason }}</div>
                </div>

                <!-- Modal Footer (Actions) -->
                <div class="px-8 py-5 border-t border-slate-100 bg-white flex gap-4">
                    <template v-if="!rejectReasonVisible">
                        <button @click="rejectReasonVisible = true" :disabled="form.processing" class="flex-1 py-3.5 rounded-xl font-bold text-rose-500 bg-rose-50 hover:bg-rose-500 hover:text-white transition-all uppercase tracking-widest text-xs border border-rose-100 hover:border-rose-500 disabled:opacity-50">
                            Từ chối Hồ sơ
                        </button>
                        <button @click="approveUser" :disabled="form.processing" class="flex-1 py-3.5 rounded-xl font-black text-white bg-emerald-500 shadow-lg shadow-emerald-500/30 hover:bg-emerald-600 transition-all uppercase tracking-widest text-xs disabled:opacity-50 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            Duyệt & Cấp quyền
                        </button>
                    </template>
                    <template v-else>
                        <button @click="rejectReasonVisible = false" :disabled="form.processing" class="flex-1 py-3.5 rounded-xl font-bold text-slate-500 bg-slate-50 hover:bg-slate-100 transition-all uppercase tracking-widest text-xs border border-slate-200 disabled:opacity-50">
                            Hủy từ chối
                        </button>
                        <button @click="rejectUser" :disabled="form.processing" class="flex-1 py-3.5 rounded-xl font-black text-white bg-rose-500 shadow-lg shadow-rose-500/30 hover:bg-rose-600 transition-all uppercase tracking-widest text-xs disabled:opacity-50 flex items-center justify-center gap-2">
                            Xác nhận Từ Chối
                        </button>
                    </template>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
}
</style>
