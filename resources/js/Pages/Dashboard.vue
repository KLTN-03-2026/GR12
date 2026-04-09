<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

// Nhận danh sách quán ăn từ web.php (mình đã thêm ở bước trước)
defineProps({
    restaurants: Array,
});
</script>

<template>
    <Head title="Bảng điều khiển" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Khám phá món ngon
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h1 class="text-2xl font-black text-gray-800">
                        Chào mừng trở lại, {{ $page.props.auth.user.name }}! 👋
                    </h1>
                    <p class="text-gray-500">Hôm nay bạn muốn ăn gì nào?</p>
                </div>

                <div
                    v-if="restaurants && restaurants.length > 0"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8"
                >
                    <div
                        v-for="shop in restaurants"
                        :key="shop.id"
                        class="group bg-white rounded-[2rem] overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300"
                    >
                        <div class="relative h-48 overflow-hidden">
                            <img
                                :src="
                                    'https://ui-avatars.com/api/?name=' +
                                    shop.name +
                                    '&background=ffedd5&color=f97316'
                                "
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                            />
                        </div>

                        <div class="p-6">
                            <h3
                                class="text-xl font-bold text-gray-800 mb-1 group-hover:text-orange-500 transition-colors"
                            >
                                {{ shop.name }}
                            </h3>
                            <div class="flex items-center justify-between mt-4">
                                <span class="text-xs font-bold text-gray-400"
                                    >🕒 20-30 phút</span
                                >
                                <Link
                                    :href="route('restaurant.menu', shop.id)"
                                    class="bg-orange-500 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-orange-600 transition-all"
                                >
                                    Đặt ngay
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-10 text-center text-gray-500"
                >
                    <div class="text-5xl mb-4">🍕</div>
                    <p>Hiện tại chưa có quán ăn nào hoạt động quanh đây.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
