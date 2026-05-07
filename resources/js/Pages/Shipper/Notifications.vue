<template>
    <ShipperLayout>
        <template #default>
            <!-- Profile Section -->
            <section
                class="mb-6 rounded-[2rem] bg-gradient-to-br from-slate-900 to-slate-800 p-6 text-white shadow-[0_10px_30px_-10px_rgba(15,23,42,0.6)] relative overflow-hidden ring-1 ring-white/10"
            >
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
                
                <div class="flex items-center gap-5 relative z-10">
                    <div
                        class="h-20 w-20 shrink-0 overflow-hidden rounded-full ring-4 ring-white/10 bg-slate-800 shadow-xl"
                    >
                        <img
                            v-if="avatarUrl"
                            :src="avatarUrl"
                            alt="Avatar"
                            class="h-full w-full object-cover"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 text-2xl font-black text-white"
                        >
                            {{ initials }}
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-white line-clamp-1">
                            {{ fullName }}
                        </h1>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md bg-indigo-500/20 text-[10px] font-bold uppercase tracking-widest text-indigo-300 ring-1 ring-indigo-500/50">
                                Đối tác
                            </span>
                            <span class="text-xs text-slate-400">Shipper Pro</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Notifications Section -->
            <section class="space-y-6">
                <div class="flex items-center justify-between px-2">
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">
                        Thông báo hệ thống
                    </h2>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                        Mới nhất
                    </span>
                </div>

                <div class="space-y-4">
                    <div v-if="!notifications || notifications.length === 0" class="text-center py-8">
                        <p class="text-slate-400 font-medium text-sm">Chưa có thông báo nào.</p>
                    </div>

                    <div
                        v-for="notification in notifications" :key="notification.id"
                        class="rounded-[1.5rem] p-4 relative overflow-hidden border"
                        :class="{
                            'bg-blue-50 border-blue-100': notification.data.type === 'info',
                            'bg-emerald-50 border-emerald-100': notification.data.type === 'success',
                            'bg-amber-50 border-amber-100': notification.data.type === 'warning',
                            'bg-slate-50 border-slate-100': !notification.data.type
                        }"
                    >
                        <div class="absolute top-0 left-0 w-1.5 h-full"
                            :class="{
                                'bg-blue-500': notification.data.type === 'info',
                                'bg-emerald-500': notification.data.type === 'success',
                                'bg-amber-500': notification.data.type === 'warning',
                                'bg-slate-500': !notification.data.type
                            }"
                        ></div>
                        <div class="flex items-start gap-4 pl-2">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 text-xl"
                                :class="{
                                    'bg-blue-100 text-blue-600': notification.data.type === 'info',
                                    'bg-emerald-100 text-emerald-600': notification.data.type === 'success',
                                    'bg-amber-100 text-amber-600': notification.data.type === 'warning',
                                    'bg-slate-200 text-slate-600': !notification.data.type
                                }"
                            >
                                {{ notification.data.type === 'info' ? 'i' : (notification.data.type === 'success' ? '✓' : '!') }}
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <p class="font-bold text-sm mb-1"
                                        :class="{
                                            'text-blue-900': notification.data.type === 'info',
                                            'text-emerald-900': notification.data.type === 'success',
                                            'text-amber-900': notification.data.type === 'warning',
                                            'text-slate-900': !notification.data.type
                                        }"
                                    >
                                        {{ notification.data.title }}
                                    </p>
                                    <span class="text-[10px] font-bold text-slate-400 whitespace-nowrap ml-2">
                                        {{ dayjs(notification.created_at).fromNow() }}
                                    </span>
                                </div>
                                <p class="text-xs leading-relaxed"
                                    :class="{
                                        'text-blue-700/80': notification.data.type === 'info',
                                        'text-emerald-700/80': notification.data.type === 'success',
                                        'text-amber-700/80': notification.data.type === 'warning',
                                        'text-slate-600': !notification.data.type
                                    }"
                                >
                                    {{ notification.data.message }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-[1.5rem] bg-slate-50 border border-slate-100 p-5 mt-6"
                >
                    <p class="font-bold text-slate-800 text-sm mb-3 flex items-center gap-2">
                        <span class="text-rose-500">📌</span> Lưu ý quan trọng
                    </p>
                    <ul class="space-y-2.5 text-xs text-slate-600">
                        <li class="flex items-start gap-2">
                            <span class="text-slate-400 mt-0.5">•</span>
                            <span>Luôn bật <strong class="text-slate-800">Dịch vụ vị trí (GPS)</strong> để hệ thống gán đơn chính xác nhất.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-slate-400 mt-0.5">•</span>
                            <span>Nhớ <strong class="text-slate-800">Check-out (Nghỉ ngơi)</strong> khi bạn muốn kết thúc ca làm việc.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-slate-400 mt-0.5">•</span>
                            <span>Gọi Hotline <strong class="text-indigo-600">1900 1234</strong> ngay nếu gặp sự cố giao hàng.</span>
                        </li>
                    </ul>
                </div>
            </section>
        </template>
    </ShipperLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import ShipperLayout from "@/Layouts/ShipperLayout.vue";
import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";
import "dayjs/locale/vi";

dayjs.extend(relativeTime);
dayjs.locale('vi');

const props = defineProps({
    notifications: Array
});

const page = usePage();

const fullName = computed(() => page.props.auth?.user?.name || "Shipper");
const avatarUrl = computed(() => {
    const user = page.props.auth?.user;
    return user?.profile_photo_path
        ? `/storage/${user.profile_photo_path}`
        : "";
});

const initials = computed(() =>
    fullName.value
        .split(" ")
        .map((word) => word[0])
        .join("")
        .slice(0, 2)
        .toUpperCase(),
);

onMounted(() => {
    // Request location permission when component mounts
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            () => {
                console.log("Location permission granted");
            },
            (error) => {
                console.warn("Location permission denied:", error);
            },
            { timeout: 10000 },
        );
    }
});
</script>
