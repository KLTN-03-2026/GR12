<template>
    <ShipperLayout>
        <template #default>
            <div class="pb-24">
                <!-- Header Card -->
                <section
                    class="rounded-[2rem] bg-gradient-to-br from-indigo-900 to-slate-800 p-6 text-white shadow-[0_10px_30px_-10px_rgba(15,23,42,0.6)] relative overflow-hidden ring-1 ring-white/10 mb-6"
                >
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="flex items-center gap-5 relative z-10">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-[1.25rem] bg-white/10 text-3xl shadow-xl ring-1 ring-white/20 shrink-0 backdrop-blur-sm"
                        >
                            {{ pageIcon }}
                        </div>
                        <div>
                            <p
                                class="text-[10px] uppercase font-bold tracking-[0.2em] text-indigo-200 mb-1"
                            >
                                Chi tiết mục
                            </p>
                            <h1 class="text-2xl font-black text-white tracking-tight">
                                {{ pageTitle }}
                            </h1>
                        </div>
                    </div>
                </section>

                <div class="space-y-4">
                    <div
                        class="rounded-[2rem] bg-white p-5 shadow-sm border border-slate-100"
                    >
                        <p class="font-bold text-slate-900 mb-1 text-sm flex items-center gap-2">
                            <span class="text-indigo-500">ℹ️</span> Tổng quan
                        </p>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            {{ pageDescription }}
                        </p>
                    </div>

                    <div v-if="item === 'destination'" class="space-y-4">
                        <div
                            class="rounded-[2rem] bg-indigo-50 border border-indigo-100 p-5 relative overflow-hidden"
                        >
                            <div class="absolute left-0 top-0 w-1.5 h-full bg-indigo-500"></div>
                            <p class="font-bold text-indigo-900 text-sm mb-1">
                                Điểm đến tiếp theo
                            </p>
                            <p class="text-xs text-indigo-700/80 leading-relaxed">
                                Lộ trình sẽ được cập nhật khi bạn nhận đơn.
                                Giữ GPS bật để hệ thống định vị chính xác.
                            </p>
                        </div>
                        <div
                            class="rounded-[2rem] bg-white border border-slate-100 p-5 shadow-sm"
                        >
                            <p class="font-bold text-slate-900 text-sm mb-1 flex items-center gap-2">
                                <span class="text-emerald-500">📍</span> Tình trạng lộ trình
                            </p>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Chờ đơn hàng mới hoặc đơn đã sẵn sàng nhận.
                            </p>
                        </div>
                    </div>

                    <div
                        v-else-if="item === 'notifications'"
                        class="space-y-4"
                    >
                        <div
                            class="rounded-[2rem] bg-orange-50 border border-orange-100 p-5 relative overflow-hidden"
                        >
                            <div class="absolute left-0 top-0 w-1.5 h-full bg-orange-500"></div>
                            <p class="font-bold text-orange-900 text-sm mb-1">
                                Thông báo mới
                            </p>
                            <p class="text-xs text-orange-700/80 leading-relaxed">
                                Những tin nhắn và cập nhật đơn hàng sẽ hiện
                                ở đây ngay khi có thay đổi.
                            </p>
                        </div>
                        <div
                            class="rounded-[2rem] bg-white border border-slate-100 p-5 shadow-sm"
                        >
                            <p class="font-bold text-slate-900 text-sm mb-1 flex items-center gap-2">
                                <span class="text-sky-500">💡</span> Gợi ý
                            </p>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Xem lại trang Thông báo từ Menu để quản lý chi tiết.
                            </p>
                        </div>
                    </div>

                    <div v-else-if="item === 'income'" class="space-y-4">
                        <div
                            class="rounded-[2rem] bg-emerald-50 border border-emerald-100 p-6 text-center shadow-sm relative overflow-hidden"
                        >
                            <div class="absolute -right-4 -top-4 text-7xl opacity-10 blur-sm pointer-events-none">💰</div>
                            <p class="font-bold text-emerald-900 text-sm uppercase tracking-wider mb-2">
                                Thu nhập hiện tại
                            </p>
                            <p
                                class="text-4xl font-black text-emerald-600 tracking-tight"
                            >
                                {{ formatCurrency(estimatedIncome) }}
                            </p>
                            <p class="mt-3 text-[10px] text-emerald-700 font-semibold bg-emerald-100 inline-block px-3 py-1 rounded-full">
                                Ước tính trong ngày
                            </p>
                        </div>
                        <div
                            class="rounded-[2rem] bg-white border border-slate-100 p-5 shadow-sm"
                        >
                            <p class="font-bold text-slate-900 text-sm mb-1 flex items-center gap-2">
                                <span class="text-amber-500">⭐</span> Mẹo tăng thu nhập
                            </p>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Ưu tiên đơn gần, giao nhanh và nhận đánh giá
                                tốt để tăng cơ hội nhận đơn cao hơn.
                            </p>
                        </div>
                    </div>

                    <div v-else-if="item === 'wallet'" class="space-y-4">
                        <div
                            class="rounded-[2rem] bg-blue-50 border border-blue-100 p-6 text-center shadow-sm relative overflow-hidden"
                        >
                            <div class="absolute -right-4 -top-4 text-7xl opacity-10 blur-sm pointer-events-none">💳</div>
                            <p class="font-bold text-blue-900 text-sm uppercase tracking-wider mb-2">
                                Số dư ví
                            </p>
                            <p
                                class="text-4xl font-black text-blue-600 tracking-tight"
                            >
                                {{ formatCurrency(walletBalance) }}
                            </p>
                            <p class="mt-3 text-[10px] text-blue-700 font-semibold bg-blue-100 inline-block px-3 py-1 rounded-full">
                                Khả dụng rút tiền
                            </p>
                        </div>
                        <div
                            class="rounded-[2rem] bg-white border border-slate-100 p-5 shadow-sm"
                        >
                            <p class="font-bold text-slate-900 text-sm mb-1 flex items-center gap-2">
                                <span class="text-rose-500">📌</span> Lưu ý
                            </p>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Số dư sẽ được cập nhật ngay khi đơn giao
                                thành công và kết thúc ca.
                            </p>
                        </div>
                    </div>

                    <div v-else-if="item === 'history'" class="space-y-4">
                        <div
                            class="rounded-[2rem] bg-purple-50 border border-purple-100 p-5 relative overflow-hidden"
                        >
                            <div class="absolute left-0 top-0 w-1.5 h-full bg-purple-500"></div>
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <div>
                                    <p class="font-bold text-purple-900 text-sm mb-1">
                                        Đơn hàng đã giao
                                    </p>
                                    <p class="text-xs text-purple-700/80 leading-relaxed">
                                        Tổng đơn đã hoàn thành trong lịch sử
                                        của bạn.
                                    </p>
                                </div>
                                <span
                                    class="rounded-full bg-purple-200 text-purple-800 px-3 py-1.5 text-sm font-black shadow-sm"
                                >
                                    {{ completedOrders }} đơn
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="item === 'help'" class="space-y-4">
                        <div
                            class="rounded-[2rem] bg-sky-50 border border-sky-100 p-5 relative overflow-hidden"
                        >
                            <div class="absolute left-0 top-0 w-1.5 h-full bg-sky-500"></div>
                            <p class="font-bold text-sky-900 text-sm mb-1">
                                Liên hệ hỗ trợ
                            </p>
                            <p class="text-xs text-sky-700/80 leading-relaxed mb-3">
                                Liên hệ nhanh khi cần hỗ trợ về đơn hàng
                                hoặc tài khoản.
                            </p>
                            <div class="flex gap-2">
                                <a href="tel:19001234" class="flex-1 text-center bg-white text-sky-600 rounded-xl py-2 font-bold text-xs shadow-sm ring-1 ring-sky-200 active:scale-95 transition-transform">
                                    📞 1900 1234
                                </a>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="item === 'settings'" class="space-y-4">
                        <div
                            class="rounded-[2rem] bg-white border border-slate-100 p-5 shadow-sm"
                        >
                            <p class="font-bold text-slate-900 text-sm mb-1 flex items-center gap-2">
                                <span class="text-slate-500">⚙️</span> Cài đặt
                            </p>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Các tùy chọn cài đặt chi tiết sẽ được tích hợp tại đây trong phiên bản tiếp theo.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
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
