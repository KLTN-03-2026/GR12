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
        <div
            class="py-12 bg-gradient-to-br from-orange-50 via-white to-red-50 min-h-screen"
        >
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Header Profile Card -->
                <div
                    class="bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-2xl p-8 mb-8 shadow-lg"
                >
                    <div class="flex items-center gap-6">
                        <!-- Avatar -->
                        <div class="relative">
                            <div
                                class="w-24 h-24 rounded-full overflow-hidden border-4 border-white shadow-lg"
                            >
                                <img
                                    v-if="user.avatar"
                                    :src="
                                        user.avatar.startsWith('/')
                                            ? '/storage/' +
                                              user.avatar.replace(/^\//, '')
                                            : user.avatar
                                    "
                                    alt="Avatar"
                                    class="w-full h-full object-cover"
                                />
                                <div
                                    v-else
                                    class="w-full h-full bg-white/20 flex items-center justify-center text-5xl"
                                >
                                    👤
                                </div>
                            </div>
                        </div>
                        <!-- Info -->
                        <div class="flex-1">
                            <h3 class="text-3xl font-black mb-2">
                                {{ user.name }}
                            </h3>
                            <p class="text-white/80 mb-2">
                                📧 {{ user.email }}
                            </p>
                            <p class="text-white/70 text-sm">
                                Thành viên từ
                                {{
                                    new Date(
                                        user.created_at,
                                    ).toLocaleDateString("vi-VN")
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="flex gap-4 mb-8 border-b-2 border-gray-200">
                    <button
                        @click="activeTab = 'profile'"
                        :class="[
                            'pb-4 px-6 font-black text-lg transition-all duration-300 border-b-4',
                            activeTab === 'profile'
                                ? 'text-orange-600 border-orange-500'
                                : 'text-gray-500 border-transparent hover:text-gray-700',
                        ]"
                    >
                        ℹ️ Thông tin
                    </button>
                    <button
                        @click="activeTab = 'security'"
                        :class="[
                            'pb-4 px-6 font-black text-lg transition-all duration-300 border-b-4',
                            activeTab === 'security'
                                ? 'text-orange-600 border-orange-500'
                                : 'text-gray-500 border-transparent hover:text-gray-700',
                        ]"
                    >
                        🔒 Bảo mật
                    </button>
                    <button
                        @click="activeTab = 'danger'"
                        :class="[
                            'pb-4 px-6 font-black text-lg transition-all duration-300 border-b-4',
                            activeTab === 'danger'
                                ? 'text-red-600 border-red-500'
                                : 'text-gray-500 border-transparent hover:text-gray-700',
                        ]"
                    >
                        ⚠️ Nguy hiểm
                    </button>
                </div>

                <!-- Content Sections -->
                <!-- Profile Tab -->
                <div
                    v-show="activeTab === 'profile'"
                    class="bg-white rounded-2xl shadow-lg p-8 transition-all duration-500"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <!-- Security Tab -->
                <div
                    v-show="activeTab === 'security'"
                    class="bg-white rounded-2xl shadow-lg p-8 transition-all duration-500"
                >
                    <UpdatePasswordForm />
                </div>

                <!-- Danger Tab -->
                <div
                    v-show="activeTab === 'danger'"
                    class="bg-white rounded-2xl shadow-lg p-8 transition-all duration-500"
                >
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
