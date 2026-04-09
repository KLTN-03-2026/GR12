<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
</script>

<template>
    <GuestLayout>
        <Head title="Chờ phê duyệt" />

        <div class="text-center p-6">
            <div class="mb-4 flex justify-center">
                <div class="bg-orange-100 p-4 rounded-full animate-pulse">
                    <svg
                        class="w-12 h-12 text-orange-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                </div>
            </div>

            <h2 class="text-2xl font-bold text-gray-800 mb-2">
                Đăng ký thành công!
            </h2>
            <p class="text-gray-600 mb-8 max-w-sm mx-auto">
                Tài khoản của bạn đang được Admin xem xét. Chúng tôi sẽ gửi
                thông báo qua email ngay sau khi hồ sơ được duyệt (thường trong
                vòng 24h).
            </p>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="inline-block bg-orange-500 text-white px-8 py-3 rounded-2xl font-black hover:bg-orange-600 hover:shadow-lg hover:shadow-orange-200 transition-all active:scale-95 text-sm"
            >
                Quay lại Đăng nhập
            </Link>

            <div class="mt-6">
                <Link
                    :href="route('home')"
                    class="text-sm font-bold text-gray-400 hover:text-orange-500 transition-colors"
                >
                    Về trang chủ
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>
