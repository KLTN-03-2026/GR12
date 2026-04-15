<template>
    <ShipperLayout>
        <template #default>
            <section
                class="rounded-[28px] bg-white p-5 shadow-sm ring-1 ring-slate-200"
            >
                <div class="space-y-4 text-slate-700">
                    <p class="text-sm text-slate-500">{{ pageDescription }}</p>
                    <div v-if="item === 'destination'" class="space-y-3">
                        <div class="rounded-3xl bg-slate-50 p-4">
                            <p class="font-semibold text-slate-900">
                                Điểm đến tiếp theo
                            </p>
                            <p class="mt-2 text-sm text-slate-600">
                                Xem lộ trình và giờ đến dự kiến.
                            </p>
                        </div>
                        <div
                            class="rounded-3xl overflow-hidden bg-slate-900/5 p-4 text-sm text-slate-600"
                        >
                            Bản đồ lộ trình sẽ được cập nhật khi shipper nhận
                            đơn và tiến tới quán.
                        </div>
                    </div>
                    <div v-else-if="item === 'notifications'" class="space-y-3">
                        <div class="rounded-3xl bg-slate-50 p-4">
                            <p class="font-semibold text-slate-900">
                                Thông báo mới
                            </p>
                            <p class="mt-2 text-sm text-slate-600">
                                Mỗi khi có đơn, thay đổi trạng thái hoặc tin
                                nhắn mới.
                            </p>
                        </div>
                        <div
                            class="rounded-3xl bg-white border border-slate-200 p-4 text-sm text-slate-700"
                        >
                            <p class="font-semibold mb-2">
                                Hiện tại chưa có thông báo mới.
                            </p>
                            <p>
                                Thông báo sẽ xuất hiện khi đơn mới được gán hoặc
                                thay đổi.
                            </p>
                        </div>
                    </div>
                    <div v-else-if="item === 'income'" class="space-y-3">
                        <div class="rounded-3xl bg-slate-50 p-4">
                            <p class="font-semibold text-slate-900">Thu nhập</p>
                            <p class="mt-2 text-sm text-slate-600">
                                Tổng thu nhập shipper và phí giao hàng.
                            </p>
                        </div>
                        <div
                            class="rounded-3xl bg-white border border-slate-200 p-4 text-sm text-slate-700"
                        >
                            <p class="text-slate-800 font-black text-lg">
                                {{ formatCurrency(estimatedIncome) }}
                            </p>
                            <p class="mt-2 text-slate-500">
                                Thu nhập ước tính theo đơn hiện tại và lịch sử.
                            </p>
                        </div>
                    </div>
                    <div v-else-if="item === 'wallet'" class="space-y-3">
                        <div class="rounded-3xl bg-slate-50 p-4">
                            <p class="font-semibold text-slate-900">
                                Ví của bạn
                            </p>
                            <p class="mt-2 text-sm text-slate-600">
                                Quản lý số dư, rút tiền và lịch sử thanh toán.
                            </p>
                        </div>
                        <div
                            class="rounded-3xl bg-white border border-slate-200 p-4 text-sm text-slate-700"
                        >
                            <p class="text-slate-800 font-black text-lg">
                                {{ formatCurrency(walletBalance) }}
                            </p>
                            <p class="mt-2 text-slate-500">
                                Số dư hiện tại trong ví của shipper.
                            </p>
                        </div>
                    </div>
                    <div v-else-if="item === 'history'" class="space-y-3">
                        <div class="rounded-3xl bg-slate-50 p-4">
                            <p class="font-semibold text-slate-900">
                                Lịch sử đơn hàng
                            </p>
                            <p class="mt-2 text-sm text-slate-600">
                                Xem lại các đơn đã giao và trạng thái hoàn
                                thành.
                            </p>
                        </div>
                        <div
                            class="rounded-3xl bg-white border border-slate-200 p-4 text-sm text-slate-700"
                        >
                            <p class="font-semibold">
                                Tổng đơn đã giao: {{ completedOrders }}
                            </p>
                            <p class="mt-2">
                                Danh sách đầy đủ sẽ hiển thị tại trang lịch sử
                                công việc.
                            </p>
                        </div>
                    </div>
                    <div v-else-if="item === 'help'" class="space-y-3">
                        <div class="rounded-3xl bg-slate-50 p-4">
                            <p class="font-semibold text-slate-900">
                                Trung tâm trợ giúp
                            </p>
                            <p class="mt-2 text-sm text-slate-600">
                                Các hướng dẫn và liên hệ hỗ trợ shipper.
                            </p>
                        </div>
                        <div
                            class="rounded-3xl bg-white border border-slate-200 p-4 text-sm text-slate-700"
                        >
                            <p>Gọi ngay: <strong>1900 1234</strong></p>
                            <p class="mt-2">
                                Hoặc gửi email tới
                                <strong>support@foodxpress.vn</strong>.
                            </p>
                        </div>
                    </div>
                    <div v-else-if="item === 'settings'" class="space-y-3">
                        <div class="rounded-3xl bg-slate-50 p-4">
                            <p class="font-semibold text-slate-900">Cài đặt</p>
                            <p class="mt-2 text-sm text-slate-600">
                                Điều chỉnh trải nghiệm shipper và thông tin tài
                                khoản.
                            </p>
                        </div>
                        <div
                            class="rounded-3xl bg-white border border-slate-200 p-4 text-sm text-slate-700"
                        >
                            <p>
                                Hiện tại chưa có cài đặt nâng cao. Chúng ta có
                                thể thêm thông báo, khu vực nhận đơn, và cấu
                                hình ví.
                            </p>
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
