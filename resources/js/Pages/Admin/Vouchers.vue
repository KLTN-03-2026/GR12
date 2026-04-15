<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    vouchers: Array,
});

defineOptions({ layout: AdminLayout });

const form = useForm({
    code: "",
    discount_type: "fixed",
    discount_value: 0,
    expires_at: "",
});

const activeVouchers = computed(() =>
    props.vouchers.filter(
        (voucher) => new Date(voucher.expires_at) > new Date(),
    ),
);

const submitVoucher = () => {
    form.post(route("admin.vouchers.store"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset("code", "discount_type", "discount_value", "expires_at");
        },
    });
};
</script>

<template>
    <Head title="Quản lý Voucher" />

    <div class="space-y-8">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
            >
                <div>
                    <h1 class="text-3xl font-black text-gray-900">
                        Quản lý voucher khuyến mãi
                    </h1>
                    <p class="text-sm text-gray-500 mt-2">
                        Tạo mã khuyến mãi có thời hạn. Mã quá hạn sẽ tự động
                        không còn hiệu lực.
                    </p>
                </div>
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <div>
                        <label
                            class="block text-sm font-bold text-gray-700 mb-2"
                        >
                            Mã voucher
                        </label>
                        <input
                            v-model="form.code"
                            type="text"
                            placeholder="VD: FOODX25"
                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 p-4 text-sm font-semibold outline-none transition focus:border-orange-500"
                        />
                        <p
                            v-if="form.errors.code"
                            class="text-red-500 text-xs mt-2"
                        >
                            {{ form.errors.code }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-bold text-gray-700 mb-2"
                            >
                                Loại giảm giá
                            </label>
                            <select
                                v-model="form.discount_type"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 p-4 text-sm outline-none"
                            >
                                <option value="fixed">Giảm tiền</option>
                                <option value="percent">Giảm phần trăm</option>
                            </select>
                            <p
                                v-if="form.errors.discount_type"
                                class="text-red-500 text-xs mt-2"
                            >
                                {{ form.errors.discount_type }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-bold text-gray-700 mb-2"
                            >
                                Giá trị
                            </label>
                            <input
                                v-model="form.discount_value"
                                type="number"
                                min="1"
                                step="0.01"
                                placeholder="Số tiền hoặc %"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 p-4 text-sm font-semibold outline-none transition focus:border-orange-500"
                            />
                            <p
                                v-if="form.errors.discount_value"
                                class="text-red-500 text-xs mt-2"
                            >
                                {{ form.errors.discount_value }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-bold text-gray-700 mb-2"
                        >
                            Ngày hết hạn
                        </label>
                        <input
                            v-model="form.expires_at"
                            type="datetime-local"
                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 p-4 text-sm outline-none transition focus:border-orange-500"
                        />
                        <p
                            v-if="form.errors.expires_at"
                            class="text-red-500 text-xs mt-2"
                        >
                            {{ form.errors.expires_at }}
                        </p>
                    </div>

                    <button
                        @click.prevent="submitVoucher"
                        :disabled="form.processing"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-orange-500 px-6 py-4 text-sm font-black uppercase tracking-[0.2em] text-white transition hover:bg-orange-600 disabled:opacity-70"
                    >
                        {{ form.processing ? "Đang lưu..." : "Tạo voucher" }}
                    </button>
                </div>

                <div class="bg-slate-50 rounded-3xl border border-gray-200 p-6">
                    <h2 class="font-black text-gray-900 text-lg mb-4">
                        Voucher đang hoạt động
                    </h2>
                    <div v-if="activeVouchers.length" class="space-y-3">
                        <div
                            v-for="voucher in activeVouchers"
                            :key="voucher.id"
                            class="rounded-3xl bg-white p-4 border border-gray-200"
                        >
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <div>
                                    <div class="font-black text-gray-900">
                                        {{ voucher.code }}
                                    </div>
                                    <div class="text-xs text-slate-500 mt-1">
                                        {{
                                            voucher.discount_type === "percent"
                                                ? voucher.discount_value + "%"
                                                : new Intl.NumberFormat(
                                                      "vi-VN",
                                                      {
                                                          style: "currency",
                                                          currency: "VND",
                                                      },
                                                  ).format(
                                                      voucher.discount_value,
                                                  )
                                        }}
                                    </div>
                                </div>
                                <div class="text-right text-xs text-gray-500">
                                    Hết hạn<br />{{
                                        new Date(
                                            voucher.expires_at,
                                        ).toLocaleString("vi-VN")
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="rounded-3xl bg-white p-4 border border-gray-200 text-sm text-gray-500"
                    >
                        Chưa có voucher hoạt động.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
