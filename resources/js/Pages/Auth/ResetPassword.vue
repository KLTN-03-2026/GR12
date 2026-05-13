<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Đặt lại mật khẩu" />

        <div class="flex items-center justify-center min-h-[70vh] py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md bg-white p-8 rounded-[2rem] shadow-xl ring-1 ring-slate-100">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-orange-100 text-orange-500 mb-4 shadow-inner">
                        <span class="text-3xl">🛡️</span>
                    </div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Tạo mật khẩu mới</h2>
                    <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                        Vui lòng nhập mật khẩu mới cho tài khoản của bạn. Đảm bảo mật khẩu đủ mạnh để bảo vệ tài khoản.
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <InputLabel for="email" value="Địa chỉ Email" class="font-bold text-slate-700" />
                        <div class="mt-2 relative">
                            <TextInput
                                id="email"
                                type="email"
                                class="block w-full rounded-xl border-slate-200 pl-11 bg-slate-50 text-slate-500 focus:border-slate-300 focus:ring-0 shadow-inner"
                                v-model="form.email"
                                required
                                readonly
                            />
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <span class="text-slate-400">📧</span>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Mật khẩu mới" class="font-bold text-slate-700" />
                        <div class="mt-2 relative">
                            <TextInput
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                class="block w-full rounded-xl border-slate-200 pl-11 pr-12 focus:border-orange-500 focus:ring-orange-500 shadow-sm transition-colors"
                                v-model="form.password"
                                required
                                autofocus
                                autocomplete="new-password"
                                placeholder="••••••••"
                            />
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <span class="text-slate-400">🔒</span>
                            </div>
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-orange-500 transition-colors"
                            >
                                <span v-if="showPassword">🙈</span>
                                <span v-else>👁️</span>
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel
                            for="password_confirmation"
                            value="Xác nhận mật khẩu"
                            class="font-bold text-slate-700"
                        />
                        <div class="mt-2 relative">
                            <TextInput
                                id="password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                class="block w-full rounded-xl border-slate-200 pl-11 pr-12 focus:border-orange-500 focus:ring-orange-500 shadow-sm transition-colors"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                            />
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <span class="text-slate-400">🔐</span>
                            </div>
                            <button
                                type="button"
                                @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-orange-500 transition-colors"
                            >
                                <span v-if="showConfirmPassword">🙈</span>
                                <span v-else>👁️</span>
                            </button>
                        </div>
                        <InputError
                            class="mt-2"
                            :message="form.errors.password_confirmation"
                        />
                    </div>

                    <div class="pt-4">
                        <PrimaryButton
                            class="w-full justify-center py-3.5 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold shadow-lg shadow-orange-500/30 transition-all hover:scale-[1.02] active:scale-95"
                            :class="{ 'opacity-70 cursor-not-allowed': form.processing }"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing" class="flex items-center gap-2">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Đang xử lý...
                            </span>
                            <span v-else>Đổi mật khẩu</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>
