<script setup>
import { ref, watch, onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const items = ref([]);

// Hàm thêm một thông báo vào danh sách
const add = (message, type = "success") => {
    const id = Date.now();
    items.value.unshift({ id, message, type });

    // Tự động xóa sau 3 giây
    setTimeout(() => {
        items.value = items.value.filter((item) => item.id !== id);
    }, 3000);
};

// Theo dõi biến flash từ Backend gửi về
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) add(flash.success, "success");
        if (flash?.error) add(flash.error, "error");
    },
    { deep: true, immediate: true }
);

const remove = (id) => {
    items.value = items.value.filter((item) => item.id !== id);
};

onMounted(() => {
    if (page.props.auth && page.props.auth.user) {
        window.Echo.private(`App.Models.User.${page.props.auth.user.id}`)
            .notification((notification) => {
                if (notification.type) {
                    // SystemNotification có 'title', 'message', 'type'
                    add(notification.message || notification.title, notification.type === 'error' ? 'error' : 'success');
                } else if (notification.message) {
                    // Fallback
                    add(notification.message, 'success');
                }
            });
    }
});
</script>

<template>
    <div class="fixed top-6 right-6 z-[999] space-y-4 w-full max-w-xs">
        <TransitionGroup name="list">
            <div
                v-for="item in items"
                :key="item.id"
                class="relative overflow-hidden bg-white/90 backdrop-blur-xl border border-gray-100 p-4 rounded-[1.5rem] shadow-2xl flex items-center gap-4 group"
            >
                <div
                    :class="
                        item.type === 'success' ? 'bg-green-500' : 'bg-red-500'
                    "
                    class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0 shadow-lg"
                >
                    <svg
                        v-if="item.type === 'success'"
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M5 13l4 4L19 7"
                            stroke-width="3"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                    <svg
                        v-else
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M6 18L18 6M6 6l12 12"
                            stroke-width="3"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>

                <div class="flex-1">
                    <p
                        class="text-xs font-black uppercase tracking-widest text-gray-400 mb-0.5"
                    >
                        {{
                            item.type === "success" ? "Thành công" : "Thông báo"
                        }}
                    </p>
                    <p class="text-sm font-bold text-gray-800 leading-tight">
                        {{ item.message }}
                    </p>
                </div>

                <button
                    @click="remove(item.id)"
                    class="text-gray-300 hover:text-gray-500 transition"
                >
                    <svg
                        class="w-4 h-4"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        />
                    </svg>
                </button>

                <div
                    class="absolute bottom-0 left-0 h-1 bg-orange-500 animate-progress"
                ></div>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.animate-progress {
    animation: progress 3s linear forwards;
}
@keyframes progress {
    from {
        width: 100%;
    }
    to {
        width: 0%;
    }
}

/* Hiệu ứng mượt mà cho danh sách */
.list-enter-active,
.list-leave-active {
    transition: all 0.5s ease;
}
.list-enter-from {
    opacity: 0;
    transform: translateX(50px) scale(0.9);
}
.list-leave-to {
    opacity: 0;
    transform: scale(0.5);
}
</style>
