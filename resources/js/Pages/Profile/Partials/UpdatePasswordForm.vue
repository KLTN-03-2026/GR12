<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <div class="mb-8">
            <h2 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                🔒 Đổi mật khẩu
            </h2>
            <p class="mt-2 text-gray-600">
                Đảm bảo tài khoản của bạn sử dụng mật khẩu mạnh, dài và ngẫu nhiên để bảo vệ an toàn.
            </p>
        </div>

        <form @submit.prevent="updatePassword" class="space-y-6">
            <!-- Current Password -->
            <div class="rounded-xl border-2 border-gray-100 p-5">
                <InputLabel for="current_password" value="🔑 Mật khẩu hiện tại" class="font-black text-gray-900 mb-2.5" />
                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="mt-2 block w-full border-gray-300 rounded-lg focus:border-orange-500 focus:ring-orange-500 text-base"
                    autocomplete="current-password"
                    placeholder="Nhập mật khẩu hiện tại"
                />
                <InputError
                    :message="form.errors.current_password"
                    class="mt-2"
                />
            </div>

            <!-- New Password -->
            <div class="rounded-xl border-2 border-gray-100 p-5">
                <InputLabel for="password" value="🆕 Mật khẩu mới" class="font-black text-gray-900 mb-2.5" />
                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="mt-2 block w-full border-gray-300 rounded-lg focus:border-orange-500 focus:ring-orange-500 text-base"
                    autocomplete="new-password"
                    placeholder="Nhập mật khẩu mới"
                />
                <InputError :message="form.errors.password" class="mt-2" />
                <p class="mt-2 text-sm text-gray-500">
                    💡 Mật khẩu phải có tối thiểu 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.
                </p>
            </div>

            <!-- Confirm Password -->
            <div class="rounded-xl border-2 border-gray-100 p-5">
                <InputLabel
                    for="password_confirmation"
                    value="✔️ Xác nhận mật khẩu"
                    class="font-black text-gray-900 mb-2.5"
                />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-2 block w-full border-gray-300 rounded-lg focus:border-orange-500 focus:ring-orange-500 text-base"
                    autocomplete="new-password"
                    placeholder="Xác nhận mật khẩu mới"
                />
                <InputError
                    :message="form.errors.password_confirmation"
                    class="mt-2"
                />
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center gap-4 pt-4">
                <PrimaryButton 
                    :disabled="form.processing"
                    class="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-black px-8 py-3 rounded-lg transition-all"
                >
                    <span v-if="!form.processing">🔐 Cập nhật mật khẩu</span>
                    <span v-else>⏳ Đang cập nhật...</span>
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-green-600 font-black"
                    >
                        ✅ Mật khẩu đã cập nhật thành công!
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
