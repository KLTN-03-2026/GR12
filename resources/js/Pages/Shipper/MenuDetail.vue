<template>
    <ShipperLayout>
        <template #default>
            <section
                class="rounded-[28px] bg-white p-5 shadow-sm ring-1 ring-slate-200 min-h-[400px]"
            >
                <div class="space-y-5 text-slate-700">
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-3xl bg-slate-900 text-2xl text-white shadow-lg"
                        >
                            {{ pageIcon }}
                        </div>
                        <div class="space-y-2">
                            <p
                                class="text-sm uppercase tracking-[0.24em] text-slate-400"
                            >
                                Chi tiết mục
                            </p>
                            <h1 class="text-xl font-bold text-slate-900">
                                {{ pageTitle }}
                            </h1>
                            <p class="text-sm text-slate-500">
                                {{ pageDescription }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4">
                        <div
                            class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-600"
                        >
                            <p class="font-semibold text-slate-900 mb-2">
                                Tổng quan
                            </p>
                            <p>
                                Các nội dung trong mục này được thiết kế để đồng
                                bộ với giao diện Shipper khác: card tròn, khoảng
                                cách đều và nền tươi sáng.
                            </p>
                        </div>

                        <div v-if="item === 'destination'" class="grid gap-4">
                            <div
                                class="rounded-3xl bg-white border border-slate-200 p-4 shadow-sm"
                            >
                                <p class="font-semibold text-slate-900">
                                    Điểm đến tiếp theo
                                </p>
                                <p class="mt-2 text-sm text-slate-500">
                                    Lộ trình sẽ được cập nhật khi bạn nhận đơn.
                                    Giữ GPS bật để hệ thống định vị chính xác.
                                </p>
                            </div>
                            <div
                                class="rounded-3xl bg-slate-50 p-4 text-slate-600"
                            >
                                <p class="font-semibold text-slate-900">
                                    Tình trạng lộ trình
                                </p>
                                <p class="mt-2 text-sm">
                                    Chờ đơn hàng mới hoặc đơn đã sẵn sàng nhận.
                                </p>
                            </div>
                        </div>

                        <div
                            v-else-if="item === 'notifications'"
                            class="grid gap-4"
                        >
                            <div
                                class="rounded-3xl bg-white border border-slate-200 p-4 shadow-sm"
                            >
                                <p class="font-semibold text-slate-900">
                                    Thông báo mới
                                </p>
                                <p class="mt-2 text-sm text-slate-500">
                                    Những tin nhắn và cập nhật đơn hàng sẽ hiện
                                    ở đây ngay khi có thay đổi.
                                </p>
                            </div>
                            <div
                                class="rounded-3xl bg-slate-50 p-4 text-slate-600"
                            >
                                <p class="font-semibold text-slate-900">
                                    Gợi ý
                                </p>
                                <p class="mt-2 text-sm">
                                    Vuốt sang trái để xem chi tiết nếu có nhiều
                                    thông báo hơn.
                                </p>
                            </div>
                        </div>

                        <div v-else-if="item === 'income'" class="grid gap-4">
                            <div
                                class="rounded-3xl bg-white border border-slate-200 p-4 shadow-sm"
                            >
                                <p class="font-semibold text-slate-900">
                                    Thu nhập hiện tại
                                </p>
                                <p
                                    class="mt-2 text-3xl font-bold text-slate-900"
                                >
                                    {{ formatCurrency(estimatedIncome) }}
                                </p>
                                <p class="mt-2 text-sm text-slate-500">
                                    Thu nhập ước tính theo các đơn đã giao và
                                    đơn hiện tại.
                                </p>
                            </div>
                            <div
                                class="rounded-3xl bg-slate-50 p-4 text-slate-600"
                            >
                                <p class="font-semibold text-slate-900">
                                    Mẹo tăng thu nhập
                                </p>
                                <p class="mt-2 text-sm">
                                    Ưu tiên đơn gần, giao nhanh và nhận đánh giá
                                    tốt để tăng cơ hội nhận đơn cao hơn.
                                </p>
                            </div>
                        </div>

                        <div v-else-if="item === 'wallet'" class="grid gap-4">
                            <div
                                class="rounded-3xl bg-white border border-slate-200 p-4 shadow-sm"
                            >
                                <p class="font-semibold text-slate-900">
                                    Số dư ví
                                </p>
                                <p
                                    class="mt-2 text-3xl font-bold text-slate-900"
                                >
                                    {{ formatCurrency(walletBalance) }}
                                </p>
                                <p class="mt-2 text-sm text-slate-500">
                                    Quản lý rút tiền và số dư hiện tại của bạn.
                                </p>
                            </div>
                            <div
                                class="rounded-3xl bg-slate-50 p-4 text-slate-600"
                            >
                                <p class="font-semibold text-slate-900">
                                    Lưu ý
                                </p>
                                <p class="mt-2 text-sm">
                                    Số dư sẽ được cập nhật ngay khi đơn giao
                                    thành công và kết thúc ca.
                                </p>
                            </div>
                        </div>

                        <div v-else-if="item === 'history'" class="grid gap-4">
                            <div
                                class="rounded-3xl bg-white border border-slate-200 p-4 shadow-sm"
                            >
                                <div
                                    class="flex items-center justify-between gap-4"
                                >
                                    <div>
                                        <p class="font-semibold text-slate-900">
                                            Đơn hàng đã giao
                                        </p>
                                        <p class="mt-2 text-sm text-slate-500">
                                            Tổng đơn đã hoàn thành trong lịch sử
                                            của bạn.
                                        </p>
                                    </div>
                                    <span
                                        class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"
                                    >
                                        {{ completedOrders }} đơn
                                    </span>
                                </div>
                            </div>
                            <div
                                class="rounded-3xl bg-slate-50 p-4 text-slate-600"
                            >
                                <p class="font-semibold text-slate-900">
                                    Kết quả
                                </p>
                                <p class="mt-2 text-sm">
                                    Các đơn hàng đã hoàn tất sẽ được ghi nhận và
                                    tổng hợp rõ ràng tại đây.
                                </p>
                            </div>
                        </div>

                        <div v-else-if="item === 'help'" class="grid gap-4">
                            <div
                                class="rounded-3xl bg-white border border-slate-200 p-4 shadow-sm"
                            >
                                <p class="font-semibold text-slate-900">
                                    Trung tâm trợ giúp
                                </p>
                                <p class="mt-2 text-sm text-slate-500">
                                    Liên hệ nhanh khi cần hỗ trợ về đơn hàng
                                    hoặc tài khoản.
                                </p>
                            </div>
                            <div
                                class="rounded-3xl bg-slate-50 p-4 text-slate-600"
                            >
                                <p class="font-semibold text-slate-900">
                                    Liên hệ
                                </p>
                                <p class="mt-2 text-sm">
                                    Gọi ngay 1900 1234 hoặc gửi email tới
                                    support@foodxpress.vn.
                                </p>
                            </div>
                        </div>

                        <div v-else-if="item === 'settings'" class="grid gap-4">
                            <div
                                class="rounded-3xl bg-white border border-slate-200 p-4 shadow-sm"
                            >
                                <p class="font-semibold text-slate-900">
                                    Cài đặt
                                </p>
                                <p class="mt-2 text-sm text-slate-500">
                                    Điều chỉnh trải nghiệm shipper và thông tin
                                    tài khoản.
                                </p>
                            </div>
                            <div
                                class="rounded-3xl bg-slate-50 p-4 text-slate-600"
                            >
                                <p class="font-semibold text-slate-900">
                                    Gợi ý
                                </p>
                                <p class="mt-2 text-sm">
                                    Chúng ta có thể thêm cài đặt thông báo, khu
                                    vực nhận đơn và quản lý thanh toán.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </template>
    </ShipperLayout>
</template>

<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import ShipperLayout from "@/Layouts/ShipperLayout.vue";

const page = usePage();
const item = page.props.item;

const menuConfig = {
    destination: {
        description: "Xem lộ trình và điểm đến xác định cho đơn hàng hiện tại.",
    },
    notifications: {
        description: "Các cập nhật quan trọng sẽ xuất hiện tại đây.",
    },
    income: {
        description: "Xem tổng thu nhập shipper và phí giao hàng.",
    },
    wallet: {
        description:
            "Số dư hiện tại và lịch sử thanh toán được hiển thị ở đây.",
    },
    history: {
        description: "Xem lại đơn hàng đã nhận, đã giao và đã hoàn tất.",
    },
    help: {
        description: "Liên hệ và hướng dẫn khi cần trợ giúp.",
    },
    settings: {
        description: "Điều chỉnh trạng thái và cài đặt shipper.",
    },
};

const pageTitle = computed(() => {
    const titles = {
        destination: "Điểm đến của tôi",
        notifications: "Thông báo",
        income: "Thu nhập",
        wallet: "Ví",
        history: "Lịch sử đơn hàng",
        help: "Trợ giúp",
        settings: "Cài đặt",
    };
    return titles[item] || "Menu Shipper";
});

const pageIcon = computed(() => {
    const icons = {
        destination: "📍",
        notifications: "🔔",
        income: "💰",
        wallet: "👛",
        history: "📜",
        help: "🆘",
        settings: "⚙️",
    };
    return icons[item] || "📦";
});

const pageDescription = computed(
    () =>
        menuConfig[item]?.description ||
        "Chọn một mục để xem thông tin chi tiết.",
);

const estimatedIncome = 1520000;
const walletBalance = 480000;
const completedOrders = 28;

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(amount);
};
</script>
