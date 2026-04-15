<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import { nextTick, ref } from "vue";

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: "",
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route("profile.destroy"), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section>
        <div class="mb-8">
            <h2
                class="text-2xl font-black text-red-600 flex items-center gap-2"
            >
                ⚠️ Xóa tài khoản
            </h2>
            <p class="mt-2 text-gray-600">
                Sau khi xóa tài khoản, tất cả dữ liệu và tài nguyên sẽ bị xóa
                vĩnh viễn. Vui lòng tải xuống bất kỳ dữ liệu nào bạn muốn giữ
                lại trước khi xóa.
            </p>
        </div>

        <!-- Warning Box -->
        <div class="rounded-xl bg-red-50 border-2 border-red-200 p-6 mb-6">
            <div class="flex items-start gap-4">
                <span class="text-3xl">🚨</span>
                <div>
                    <h3 class="font-black text-red-900 mb-2">
                        Hành động này không thể hoàn tác
                    </h3>
                    <div class="text-sm text-red-800 space-y-2">
                        <p>• Tất cả đơn hàng sẽ bị xóa</p>
                        <p>• Tất cả địa chỉ đã lưu sẽ bị xóa</p>
                        <p>• Tất cả đánh giá sẽ bị xóa</p>
                        <p>• Tất cả dữ liệu cá nhân sẽ bị xóa</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Button -->
        <button
            @click="confirmUserDeletion"
            class="inline-flex items-center px-6 py-3 bg-red-500 text-white font-black rounded-lg hover:bg-red-600 transition-colors shadow-lg hover:shadow-xl"
        >
            🗑️ Xóa tài khoản của tôi
        </button>

        <!-- Confirmation Modal -->
        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-8">
                <div class="flex items-start gap-4 mb-6">
                    <span class="text-4xl">⛔</span>
                    <div>
                        <h2 class="text-2xl font-black text-red-600 mb-2">
                            Có chắc xóa tài khoản không?
                        </h2>
                        <p class="text-gray-600">
                            Hành động này sẽ xóa vĩnh viễn tài khoản và tất cả
                            dữ liệu liên quan. Không có cách nào để khôi phục.
                        </p>
                    </div>
                </div>

                <!-- Password Input -->
                <div class="mb-6">
                    <InputLabel
                        for="password"
                        value="🔑 Nhập mật khẩu để xác nhận"
                        class="sr-only"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="block w-full border-gray-300 rounded-lg focus:border-red-500 focus:ring-red-500"
                        placeholder="Nhập mật khẩu của bạn"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3">
                    <SecondaryButton
                        @click="closeModal"
                        class="px-6 py-2 border border-gray-300 text-gray-700 font-black rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        ❌ Hủy bỏ
                    </SecondaryButton>

                    <button
                        class="px-6 py-2 bg-red-600 text-white font-black rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
                        :class="{
                            'opacity-50 cursor-not-allowed': form.processing,
                        }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        <span v-if="!form.processing">🗑️ Xóa tài khoản</span>
                        <span v-else>⏳ Đang xóa...</span>
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>
