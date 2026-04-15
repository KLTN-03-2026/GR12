<template>
    <ShipperLayout>
        <template #default>
            <section
                class="rounded-[32px] bg-white p-6 shadow-sm ring-1 ring-slate-200"
            >
                <div class="flex flex-col items-center gap-6 text-center">
                    <div class="flex flex-col items-center gap-4 text-center">
                        <div
                            class="h-20 w-20 overflow-hidden rounded-full border-4 border-slate-100 bg-slate-100"
                        >
                            <img
                                v-if="avatarUrl"
                                :src="avatarUrl"
                                alt="Avatar"
                                class="h-full w-full object-cover"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center bg-slate-200 text-3xl font-black text-slate-600"
                            >
                                {{ initials }}
                            </div>
                        </div>
                        <div>
                            <h1 class="text-2xl font-black text-slate-900">
                                {{ fullName }}
                            </h1>
                            <p
                                class="text-sm uppercase tracking-[0.3em] text-slate-400"
                            >
                                Shipper
                            </p>
                        </div>
                    </div>

                    <div
                        class="rounded-[28px] bg-slate-900/95 p-6 text-white w-full"
                    >
                        <div class="text-center">
                            <div
                                class="mx-auto mb-4 h-16 w-16 rounded-full bg-white/10 flex items-center justify-center"
                            >
                                <span class="text-3xl">�</span>
                            </div>
                            <h2 class="text-xl font-bold mb-2">Thông báo</h2>
                            <p class="text-slate-300">
                                Bạn sẽ nhận được các thông báo về đơn hàng mới ở
                                đây.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4 w-full">
                        <div
                            class="rounded-3xl bg-blue-50 p-4 text-sm text-blue-700"
                        >
                            <div class="flex items-start gap-3">
                                <span class="text-xl">📍</span>
                                <div>
                                    <p class="font-semibold mb-1">
                                        Vị trí của bạn
                                    </p>
                                    <p>
                                        Hệ thống sẽ theo dõi vị trí để gán đơn
                                        hàng phù hợp trong bán kính 2km.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="rounded-3xl bg-green-50 p-4 text-sm text-green-700"
                        >
                            <div class="flex items-start gap-3">
                                <span class="text-xl">💰</span>
                                <div>
                                    <p class="font-semibold mb-1">
                                        Thu nhập hấp dẫn
                                    </p>
                                    <p>
                                        Nhận 12k/km + 3k/km cho quãng đường trên
                                        2km. Thu nhập trung bình 150k-300k/ngày.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="rounded-3xl bg-orange-50 p-4 text-sm text-orange-700"
                        >
                            <div class="flex items-start gap-3">
                                <span class="text-xl">⚡</span>
                                <div>
                                    <p class="font-semibold mb-1">
                                        Nhanh chóng & tiện lợi
                                    </p>
                                    <p>
                                        Giao diện tối ưu cho điện thoại, dễ dàng
                                        theo dõi đơn hàng và điều hướng.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-600 w-full"
                    >
                        <p class="font-semibold mb-2">Lưu ý quan trọng:</p>
                        <ul class="space-y-1 text-xs">
                            <li>
                                • Luôn bật GPS để hệ thống theo dõi vị trí chính
                                xác
                            </li>
                            <li>• Check-out khi kết thúc ca làm việc</li>
                            <li>• Giao hàng đúng giờ để nhận đánh giá tốt</li>
                            <li>• Liên hệ hotline 1900 1234 nếu cần hỗ trợ</li>
                        </ul>
                    </div>
                </div>
            </section>
        </template>
    </ShipperLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import ShipperLayout from "@/Layouts/ShipperLayout.vue";

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
