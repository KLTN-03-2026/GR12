<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useToast } from "vue-toastification";
const toast = useToast();

const form = useForm({
    title: '',
    message: '',
    type: 'info',
    target: 'all'
});

const submit = () => {
    form.post(route('admin.push-notifications.store'), {
        onSuccess: () => {
            toast.success("🚀 Đã gửi thông báo thành công!");
            form.reset();
        },
        onError: () => {
            toast.error("Có lỗi xảy ra, vui lòng thử lại.");
        }
    });
};

const applyTemplate = (templateId) => {
    switch (templateId) {
        case 1:
            form.title = "Trời đang mưa to! 🌧️";
            form.message = "Các tài xế chú ý an toàn khi giao hàng nhé. Khách hàng thông cảm nếu đơn hàng đến trễ hơn dự kiến đôi chút.";
            form.type = "warning";
            form.target = "all";
            break;
        case 2:
            form.title = "Tặng mã giảm giá cuối tuần! 🎉";
            form.message = "Nhập ngay mã WEEKEND50 để được giảm 50K cho mọi đơn hàng từ 150K. Nhanh tay kẻo lỡ!";
            form.type = "success";
            form.target = "customer";
            break;
        case 3:
            form.title = "Bảo trì hệ thống ⚙️";
            form.message = "Hệ thống sẽ tiến hành bảo trì từ 02:00 sáng đến 04:00 sáng mai. Mong các đối tác chủ động sắp xếp thời gian hoạt động.";
            form.type = "error";
            form.target = "restaurant";
            break;
    }
};
</script>

<template>
    <Head title="Trung tâm Thông báo - Admin" />

    <AdminLayout>
        <div class="mb-6">
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Trung tâm Thông báo (Broadcast)</h2>
            <p class="text-sm text-slate-500 mt-1 font-medium">Phát thông báo đẩy đến tất cả người dùng hoặc các nhóm đối tượng cụ thể.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Section -->
            <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-6 sm:p-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Tiêu đề thông báo</label>
                        <input type="text" v-model="form.title" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors font-medium text-slate-800" placeholder="VD: Tặng mã giảm giá cuối tuần!">
                        <div v-if="form.errors.title" class="text-rose-500 text-xs mt-1">{{ form.errors.title }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Nội dung chi tiết</label>
                        <textarea v-model="form.message" rows="4" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors resize-none font-medium text-slate-800" placeholder="Chi tiết thông báo..."></textarea>
                        <div v-if="form.errors.message" class="text-rose-500 text-xs mt-1">{{ form.errors.message }}</div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Loại thông báo</label>
                            <select v-model="form.type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors font-bold text-slate-700">
                                <option value="info">ℹ️ Thông tin chung (Xanh dương)</option>
                                <option value="success">✅ Tin vui / Khuyến mãi (Xanh lá)</option>
                                <option value="warning">⚠️ Cảnh báo (Vàng)</option>
                                <option value="error">🚨 Khẩn cấp / Lỗi (Đỏ)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Đối tượng nhận</label>
                            <select v-model="form.target" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors font-bold text-slate-700">
                                <option value="all">🌍 Tất cả mọi người</option>
                                <option value="customer">👤 Chỉ Khách hàng</option>
                                <option value="shipper">🛵 Chỉ Tài xế (Shipper)</option>
                                <option value="restaurant">🏪 Chỉ Quán ăn</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <p class="text-xs text-slate-500 font-medium">Thông báo sẽ được đẩy trực tiếp vào chuông thông báo của đối tượng được chọn.</p>
                        <button type="submit" :disabled="form.processing" class="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black uppercase tracking-widest text-xs shadow-lg shadow-blue-600/30 transition-all hover:-translate-y-0.5 disabled:opacity-50 flex items-center gap-2">
                            <span>Phát sóng</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Templates Section -->
            <div class="space-y-4">
                <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-4">Các mẫu có sẵn</h3>
                
                <div @click="applyTemplate(1)" class="bg-gradient-to-br from-amber-50 to-orange-50 p-5 rounded-2xl border border-orange-100 cursor-pointer hover:shadow-md transition-all group">
                    <div class="text-xl mb-2">🌧️</div>
                    <h4 class="font-bold text-orange-800 text-sm group-hover:text-orange-600 transition-colors">Cảnh báo Thời tiết</h4>
                    <p class="text-xs text-orange-600 mt-1 line-clamp-2">Gửi tất cả: Nhắc nhở an toàn mùa mưa bão...</p>
                </div>

                <div @click="applyTemplate(2)" class="bg-gradient-to-br from-emerald-50 to-teal-50 p-5 rounded-2xl border border-emerald-100 cursor-pointer hover:shadow-md transition-all group">
                    <div class="text-xl mb-2">🎉</div>
                    <h4 class="font-bold text-emerald-800 text-sm group-hover:text-emerald-600 transition-colors">Chiến dịch Khuyến mãi</h4>
                    <p class="text-xs text-emerald-600 mt-1 line-clamp-2">Gửi Khách hàng: Mã giảm giá cuối tuần...</p>
                </div>

                <div @click="applyTemplate(3)" class="bg-gradient-to-br from-rose-50 to-red-50 p-5 rounded-2xl border border-rose-100 cursor-pointer hover:shadow-md transition-all group">
                    <div class="text-xl mb-2">⚙️</div>
                    <h4 class="font-bold text-rose-800 text-sm group-hover:text-rose-600 transition-colors">Bảo trì Hệ thống</h4>
                    <p class="text-xs text-rose-600 mt-1 line-clamp-2">Gửi Quán ăn: Thông báo thời gian bảo trì server...</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
