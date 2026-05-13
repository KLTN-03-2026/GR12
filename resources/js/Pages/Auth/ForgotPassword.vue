<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Quên mật khẩu" />

        <div class="flex items-center justify-center min-h-[60vh] py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md bg-white p-8 rounded-[2rem] shadow-xl ring-1 ring-slate-100">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-orange-100 text-orange-500 mb-4 shadow-inner">
                        <span class="text-3xl">🔑</span>
                    </div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Quên mật khẩu?</h2>
                    <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                        Đừng lo lắng! Chỉ cần nhập email của bạn dưới đây, chúng tôi sẽ gửi cho bạn một liên kết để đặt lại mật khẩu mới.
                    </p>
                </div>

                <div v-if="status" class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-sm font-medium text-emerald-600 flex items-start gap-3">
                    <span class="text-emerald-500">✅</span>
                    <span>{{ status }}</span>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="email" value="Địa chỉ Email" class="font-bold text-slate-700" />
                        <div class="mt-2 relative">
                            <TextInput
                                id="email"
                                type="email"
                                class="block w-full rounded-xl border-slate-200 pl-11 focus:border-orange-500 focus:ring-orange-500 shadow-sm transition-colors"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="Nhập email của bạn..."
                            />
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <span class="text-slate-400">📧</span>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="pt-2 flex flex-col gap-4">
                        <PrimaryButton
                            class="w-full justify-center py-3.5 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold shadow-lg shadow-orange-500/30 transition-all hover:scale-[1.02] active:scale-95"
                            :class="{ 'opacity-70 cursor-not-allowed': form.processing }"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing" class="flex items-center gap-2">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Đang gửi...
                            </span>
                            <span v-else>Gửi liên kết khôi phục</span>
                        </PrimaryButton>

                        <div class="text-center">
                            <Link
                                :href="route('login')"
                                class="text-sm font-semibold text-slate-500 hover:text-orange-600 transition-colors inline-flex items-center gap-1"
                            >
                                <span>←</span> Quay lại đăng nhập
                            </Link>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>
