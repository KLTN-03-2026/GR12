<script setup>
import { Head, usePage, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import RestaurantLayout from '@/Layouts/RestaurantLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";
import "dayjs/locale/vi";

dayjs.extend(relativeTime);
dayjs.locale('vi');

const page = usePage();
const userRole = page.props.auth?.user?.role || 'customer';

// Determine the layout dynamically
const layout = userRole === 'restaurant' ? RestaurantLayout : (userRole === 'customer' ? GuestLayout : AuthenticatedLayout);

defineProps({
    notifications: Array
});
</script>

<template>
    <Head title="Thông báo của bạn" />

    <component :is="layout">
        <template #header>
            <h2 class="font-black text-xl text-gray-800 leading-tight">
                Thông báo của bạn
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-slate-100 p-6 md:p-8">
                    
                    <div class="space-y-4">
                        <div v-if="!notifications || notifications.length === 0" class="text-center py-12">
                            <div class="text-6xl mb-4 opacity-50">📭</div>
                            <h3 class="text-lg font-bold text-slate-700">Chưa có thông báo nào</h3>
                            <p class="text-slate-500 font-medium mt-1">Khi có cập nhật mới, thông báo sẽ hiển thị tại đây.</p>
                        </div>

                        <component
                            :is="notification.data.link ? Link : 'div'"
                            :href="notification.data.link"
                            v-for="notification in notifications" :key="notification.id"
                            class="rounded-2xl p-5 relative overflow-hidden border transition-all duration-300 hover:shadow-md block"
                            :class="{
                                'bg-blue-50 border-blue-100 cursor-pointer': notification.data.type === 'info',
                                'bg-emerald-50 border-emerald-100 cursor-pointer': notification.data.type === 'success',
                                'bg-amber-50 border-amber-100 cursor-pointer': notification.data.type === 'warning',
                                'bg-slate-50 border-slate-100 cursor-pointer': !notification.data.type
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
                            <div class="flex items-start gap-4 pl-3">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 text-2xl shadow-sm"
                                    :class="{
                                        'bg-blue-100 text-blue-600': notification.data.type === 'info',
                                        'bg-emerald-100 text-emerald-600': notification.data.type === 'success',
                                        'bg-amber-100 text-amber-600': notification.data.type === 'warning',
                                        'bg-slate-200 text-slate-600': !notification.data.type
                                    }"
                                >
                                    {{ notification.data.type === 'info' ? 'ℹ️' : (notification.data.type === 'success' ? '🎉' : '⚠️') }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start flex-wrap gap-2">
                                        <h4 class="font-black text-lg"
                                            :class="{
                                                'text-blue-900': notification.data.type === 'info',
                                                'text-emerald-900': notification.data.type === 'success',
                                                'text-amber-900': notification.data.type === 'warning',
                                                'text-slate-900': !notification.data.type
                                            }"
                                        >
                                            {{ notification.data.title }}
                                        </h4>
                                        <span class="text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap"
                                            :class="{
                                                'bg-blue-100 text-blue-700': notification.data.type === 'info',
                                                'bg-emerald-100 text-emerald-700': notification.data.type === 'success',
                                                'bg-amber-100 text-amber-700': notification.data.type === 'warning',
                                                'bg-slate-200 text-slate-700': !notification.data.type
                                            }"
                                        >
                                            {{ dayjs(notification.created_at).fromNow() }}
                                        </span>
                                    </div>
                                    <p class="text-sm mt-2 font-medium leading-relaxed"
                                        :class="{
                                            'text-blue-800': notification.data.type === 'info',
                                            'text-emerald-800': notification.data.type === 'success',
                                            'text-amber-800': notification.data.type === 'warning',
                                            'text-slate-700': !notification.data.type
                                        }"
                                    >
                                        {{ notification.data.message }}
                                    </p>
                                </div>
                            </div>
                        </component>
                    </div>

                </div>
            </div>
        </div>
    </component>
</template>
