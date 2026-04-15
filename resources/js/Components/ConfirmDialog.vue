<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
                @click.self="cancel"
            >
                <div class="w-full max-w-sm rounded-3xl bg-white p-6 shadow-xl">
                    <div class="text-center">
                        <div
                            class="mx-auto mb-4 h-16 w-16 rounded-full bg-slate-100 flex items-center justify-center"
                        >
                            <span class="text-3xl">{{ icon }}</span>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">
                            {{ title }}
                        </h3>
                        <p class="text-sm text-slate-600 mb-6">
                            {{ message }}
                        </p>
                        <div class="flex gap-3">
                            <button
                                @click="cancel"
                                class="flex-1 rounded-2xl border border-slate-200 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                            >
                                {{ cancelText }}
                            </button>
                            <button
                                @click="confirm"
                                :disabled="loading"
                                class="flex-1 rounded-2xl bg-slate-900 py-3 text-sm font-medium text-white transition hover:bg-slate-950 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span
                                    v-if="loading"
                                    class="flex items-center justify-center gap-2"
                                >
                                    <div
                                        class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                                    ></div>
                                    {{ loadingText }}
                                </span>
                                <span v-else>{{ confirmText }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref } from "vue";

const show = ref(false);
const loading = ref(false);

const props = defineProps({
    title: {
        type: String,
        default: "Xác nhận",
    },
    message: {
        type: String,
        default: "Bạn có chắc chắn muốn thực hiện hành động này?",
    },
    icon: {
        type: String,
        default: "⚠️",
    },
    confirmText: {
        type: String,
        default: "Xác nhận",
    },
    cancelText: {
        type: String,
        default: "Hủy",
    },
    loadingText: {
        type: String,
        default: "Đang xử lý...",
    },
});

const emit = defineEmits(["confirm", "cancel"]);

const open = () => {
    show.value = true;
    loading.value = false;
};

const close = () => {
    show.value = false;
};

const confirm = () => {
    loading.value = true;
    emit("confirm");
};

const cancel = () => {
    emit("cancel");
    close();
};

const setLoading = (isLoading) => {
    loading.value = isLoading;
};

// Expose methods
defineExpose({
    open,
    close,
    setLoading,
});
</script>
