<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const showPassword = ref(false);

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Đăng nhập FoodXpress" />

        <div class="min-h-screen flex">
            <!-- LEFT IMAGE -->
            <div
                class="hidden md:flex w-1/2 bg-orange-500 items-center justify-center"
            >
                <img
                    src="https://images.unsplash.com/photo-1504674900247-0877df9cc836"
                    class="object-cover h-full w-full opacity-90"
                />
            </div>

            <!-- RIGHT FORM -->
            <div
                class="flex w-full md:w-1/2 items-center justify-center bg-gray-50 px-6"
            >
                <div class="w-full max-w-md bg-white p-8 rounded-3xl shadow-xl">
                    <div class="mb-6 text-center">
                        <h1 class="text-3xl font-black text-gray-800">
                            Chào mừng trở lại 👋
                        </h1>
                        <p class="text-gray-500 mt-2">
                            Đăng nhập để tiếp tục với
                            <span class="text-orange-500 font-bold"
                                >FoodXpress</span
                            >
                        </p>
                    </div>

                    <div
                        v-if="status"
                        class="mb-4 text-sm font-medium text-green-600 bg-green-50 p-3 rounded-xl"
                    >
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- EMAIL -->
                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput
                                id="email"
                                type="email"
                                class="mt-1 block w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500"
                                v-model="form.email"
                                required
                                autofocus
                                placeholder="example@email.com"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.email"
                            />
                        </div>

                        <!-- PASSWORD -->
                        <div>
                            <InputLabel for="password" value="Mật khẩu" />
                            <div class="relative mt-1">
                                <TextInput
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="block w-full rounded-xl border-gray-200 pr-12 focus:border-orange-500 focus:ring-orange-500"
                                    v-model="form.password"
                                    required
                                    placeholder="••••••••"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-orange-500"
                                >
                                    <span v-if="showPassword">🙈</span>
                                    <span v-else>👁️</span>
                                </button>
                            </div>
                            <InputError
                                class="mt-2"
                                :message="form.errors.password"
                            />
                        </div>

                        <!-- OPTIONS -->
                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <Checkbox v-model:checked="form.remember" />
                                <span class="ml-2 text-sm text-gray-600"
                                    >Ghi nhớ</span
                                >
                            </label>

                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm text-orange-600 hover:underline"
                            >
                                Quên mật khẩu?
                            </Link>
                        </div>

                        <!-- BUTTON -->
                        <PrimaryButton
                            class="w-full justify-center py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold"
                            :disabled="form.processing"
                        >
                            Đăng nhập
                        </PrimaryButton>

                        <!-- REGISTER -->
                        <div class="text-center text-sm text-gray-600">
                            Chưa có tài khoản?
                            <Link
                                :href="route('register')"
                                class="text-orange-600 font-semibold ml-1 hover:underline"
                            >
                                Đăng ký ngay
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
