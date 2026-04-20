<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
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
const avatarPreview = ref(
    user.avatar
        ? user.avatar.startsWith("/")
            ? `/storage/${user.avatar.replace(/^\//, "")}`
            : user.avatar
        : null,
);

const form = useForm({
    name: user.name,
    email: user.email,
    avatar: null,
});

const updateAvatar = (event) => {
    const file = event.target.files[0];
    form.avatar = file || null;

    if (!file) {
        avatarPreview.value = user.avatar
            ? user.avatar.startsWith("/")
                ? `/storage/${user.avatar.replace(/^\//, "")}`
                : user.avatar
            : null;
        return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        avatarPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};
</script>

<template>
    <section>
        <div class="mb-8">
            <h2
                class="text-2xl font-black text-gray-900 flex items-center gap-2"
            >
                ℹ️ Cập nhật thông tin cá nhân
            </h2>
            <p class="mt-2 text-gray-600">
                Cập nhật thông tin hồ sơ và địa chỉ email của bạn.
            </p>
        </div>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="space-y-6"
        >
            <!-- Avatar Field -->
            <div class="rounded-xl border-2 border-gray-100 p-5">
                <InputLabel
                    for="avatar"
                    value="🖼️ Ảnh đại diện"
                    class="font-black text-gray-900 mb-2.5"
                />
                <div class="flex items-center gap-4">
                    <div
                        class="w-24 h-24 rounded-full overflow-hidden border border-gray-200 bg-gray-100"
                    >
                        <img
                            v-if="avatarPreview"
                            :src="avatarPreview"
                            alt="Avatar preview"
                            class="w-full h-full object-cover"
                        />
                        <div
                            v-else
                            class="w-full h-full flex items-center justify-center text-3xl text-gray-400"
                        >
                            👤
                        </div>
                    </div>
                    <div class="flex-1">
                        <input
                            id="avatar"
                            type="file"
                            accept="image/*"
                            @change="updateAvatar"
                            class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-full file:border-0 file:bg-orange-500 file:px-4 file:py-2 file:text-white file:font-black"
                        />
                        <p class="mt-2 text-sm text-gray-500">
                            Chọn hình ảnh JPG/PNG, tối đa 2MB.
                        </p>
                        <InputError
                            class="mt-2"
                            :message="form.errors.avatar"
                        />
                    </div>
                </div>
            </div>

            <!-- Name Field -->
            <div class="rounded-xl border-2 border-gray-100 p-5">
                <InputLabel
                    for="name"
                    value="👤 Tên đầy đủ"
                    class="font-black text-gray-900 mb-2.5"
                />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-2 block w-full border-gray-300 rounded-lg focus:border-orange-500 focus:ring-orange-500 text-base"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Nhập tên của bạn"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <!-- Email Field -->
            <div class="rounded-xl border-2 border-gray-100 p-5">
                <InputLabel
                    for="email"
                    value="📧 Email"
                    class="font-black text-gray-900 mb-2.5"
                />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full border-gray-300 rounded-lg focus:border-orange-500 focus:ring-orange-500 text-base"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="Nhập email của bạn"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Email Verification Notice -->
            <div
                v-if="mustVerifyEmail && user.email_verified_at === null"
                class="rounded-xl bg-yellow-50 border-2 border-yellow-200 p-5"
            >
                <div class="flex items-start gap-3">
                    <span class="text-2xl">⚠️</span>
                    <div>
                        <p class="font-black text-yellow-900 mb-2">
                            Email chưa được xác minh
                        </p>
                        <p class="text-sm text-yellow-800 mb-3">
                            Vui lòng xác minh email của bạn để tiếp tục sử dụng
                            tất cả các tính năng.
                        </p>
                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white font-black rounded-lg hover:bg-yellow-600 transition-colors"
                        >
                            📤 Gửi lại email xác minh
                        </Link>
                    </div>
                </div>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-show="status === 'verification-link-sent'"
                        class="mt-3 p-3 bg-green-100 border border-green-400 text-green-800 rounded-lg font-bold"
                    >
                        ✅ Email xác minh đã được gửi.
                    </div>
                </Transition>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center gap-4 pt-4">
                <PrimaryButton
                    :disabled="form.processing"
                    class="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-black px-8 py-3 rounded-lg transition-all"
                >
                    <span v-if="!form.processing">✅ Lưu thay đổi</span>
                    <span v-else>⏳ Đang lưu...</span>
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
                        ✅ Đã lưu thành công!
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
