<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import DeleteUserForm from "./Partials/DeleteUserForm.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;
const activeTab = ref("profile");
</script>

<template>
    <Head title="Hồ sơ cá nhân" />

    <GuestLayout>
        <div class="py-12 bg-slate-50 min-h-screen relative overflow-hidden">
            <!-- Decorative background elements -->
            <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-gradient-to-br from-orange-100/40 to-orange-50/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-gradient-to-tr from-blue-50/40 to-transparent rounded-full blur-3xl translate-y-1/3 -translate-x-1/4 pointer-events-none"></div>
            
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 relative z-10 animate-fade-in-up">
                
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Cài đặt tài khoản</h1>
                    <p class="text-sm font-medium text-slate-500 mt-1">Quản lý thông tin cá nhân và bảo mật tài khoản của bạn.</p>
                </div>

                <div class="flex flex-col lg:flex-row gap-8">
                    
                    <!-- Sidebar Navigation -->
                    <div class="lg:w-1/4">
                        <div class="bg-white rounded-[2rem] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 sticky top-8">
                            <!-- User Mini Profile -->
                            <div class="flex flex-col items-center text-center pb-6 mb-6 border-b border-slate-100">
                                <div class="w-20 h-20 rounded-2xl overflow-hidden border-4 border-white shadow-[0_8px_20px_rgba(0,0,0,0.08)] mb-4 relative group cursor-pointer">
                                    <img
                                        v-if="user.avatar"
                                        :src="user.avatar.startsWith('/') ? '/storage/' + user.avatar.replace(/^\//, '') : user.avatar"
                                        alt="Avatar"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                    />
                                    <div v-else class="w-full h-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-3xl text-white font-black">
                                        {{ user.name.charAt(0) }}
                                    </div>
                                    <div class="absolute inset-0 bg-slate-900/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                </div>
                                <h3 class="font-black text-slate-800 text-lg leading-tight">{{ user.name }}</h3>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">Tham gia {{ new Date(user.created_at).getFullYear() }}</p>
                            </div>

                            <!-- Nav Links -->
                            <nav class="space-y-2">
                                <button
                                    @click="activeTab = 'profile'"
                                    :class="[
                                        'w-full flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition-all',
                                        activeTab === 'profile'
                                            ? 'bg-orange-50 text-orange-600 shadow-sm border border-orange-100/50'
                                            : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900',
                                    ]"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    Hồ sơ cá nhân
                                </button>
                                <button
                                    @click="activeTab = 'security'"
                                    :class="[
                                        'w-full flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition-all',
                                        activeTab === 'security'
                                            ? 'bg-orange-50 text-orange-600 shadow-sm border border-orange-100/50'
                                            : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900',
                                    ]"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    Bảo mật
                                </button>
                                <button
                                    @click="activeTab = 'danger'"
                                    :class="[
                                        'w-full flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition-all mt-6',
                                        activeTab === 'danger'
                                            ? 'bg-rose-50 text-rose-600 shadow-sm border border-rose-100/50'
                                            : 'text-rose-500/70 hover:bg-rose-50 hover:text-rose-600',
                                    ]"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Xóa tài khoản
                                </button>
                            </nav>
                        </div>
                    </div>

                    <!-- Content Sections -->
                    <div class="lg:w-3/4 min-h-[500px] relative">
                        
                        <!-- Profile Tab -->
                        <transition name="fade" mode="out-in">
                            <div
                                v-if="activeTab === 'profile'"
                                key="profile"
                                class="bg-white rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 p-8 sm:p-10"
                            >
                                <div class="mb-8 border-b border-slate-100 pb-6">
                                    <h2 class="text-xl font-black text-slate-800">Thông tin cá nhân</h2>
                                    <p class="text-sm font-medium text-slate-500 mt-1">Cập nhật thông tin tài khoản và địa chỉ email của bạn.</p>
                                </div>
                                <UpdateProfileInformationForm
                                    :must-verify-email="mustVerifyEmail"
                                    :status="status"
                                />
                            </div>

                            <!-- Security Tab -->
                            <div
                                v-else-if="activeTab === 'security'"
                                key="security"
                                class="bg-white rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 p-8 sm:p-10"
                            >
                                <div class="mb-8 border-b border-slate-100 pb-6">
                                    <h2 class="text-xl font-black text-slate-800">Đổi mật khẩu</h2>
                                    <p class="text-sm font-medium text-slate-500 mt-1">Đảm bảo tài khoản của bạn đang sử dụng một mật khẩu dài, ngẫu nhiên để an toàn.</p>
                                </div>
                                <UpdatePasswordForm />
                            </div>

                            <!-- Danger Tab -->
                            <div
                                v-else-if="activeTab === 'danger'"
                                key="danger"
                                class="bg-white rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-rose-100 p-8 sm:p-10 relative overflow-hidden"
                            >
                                <div class="absolute top-0 left-0 w-full h-2 bg-rose-500"></div>
                                <div class="mb-8 border-b border-slate-100 pb-6">
                                    <h2 class="text-xl font-black text-rose-600 flex items-center gap-2">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        Khu vực nguy hiểm
                                    </h2>
                                    <p class="text-sm font-medium text-slate-500 mt-1">Hành động này không thể hoàn tác. Toàn bộ dữ liệu của bạn sẽ bị xóa vĩnh viễn.</p>
                                </div>
                                <DeleteUserForm />
                            </div>
                        </transition>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from {
    opacity: 0;
    transform: translateY(10px);
}

.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
