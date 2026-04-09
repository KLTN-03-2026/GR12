<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
const toast = useToast();

defineProps({
    users: Array,
});

const form = useForm({});

const approveUser = (id) => {
    if (confirm("Bạn có chắc chắn muốn DUYỆT tài khoản này?")) {
        form.post(route("admin.approve", id), {
            onSuccess: () => toast.success("🚀 Đã duyệt đối tác thành công!"),
            onError: () => toast.error("Có lỗi xảy ra, vui lòng thử lại."),
        });
    }
};

const rejectUser = (id) => {
    if (confirm("Bạn có chắc chắn muốn TỪ CHỐI tài khoản này?")) {
        form.post(route("admin.reject", id));
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Duyệt tài khoản đối tác" />

        <div class="mb-6">
            <h2 class="text-2xl font-black text-gray-800">
                Danh sách chờ phê duyệt
            </h2>
            <p class="text-gray-500">
                Hãy kiểm tra kỹ thông tin trước khi cho phép đối tác tham gia hệ
                thống.
            </p>
        </div>

        <div
            class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100"
        >
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="p-4 font-bold text-gray-700">Đối tác</th>
                        <th class="p-4 font-bold text-gray-700">Loại</th>
                        <th class="p-4 font-bold text-gray-700">
                            Thông tin chi tiết
                        </th>
                        <th class="p-4 font-bold text-gray-700 text-center">
                            Ngày đăng ký
                        </th>
                        <th class="p-4 font-bold text-gray-700 text-center">
                            Hành động
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr
                        v-for="user in users"
                        :key="user.id"
                        class="hover:bg-gray-50 transition-colors"
                    >
                        <td class="p-4">
                            <div class="font-bold text-gray-900 leading-tight">
                                {{ user.name }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ user.email }}
                            </div>
                            <div class="text-xs text-orange-600 font-semibold">
                                {{ user.phone }}
                            </div>
                        </td>

                        <td class="p-4">
                            <span
                                v-if="user.role === 'restaurant'"
                                class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-orange-100 text-orange-700 border border-orange-200"
                            >
                                Quán ăn
                            </span>
                            <span
                                v-else
                                class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-blue-100 text-blue-700 border border-blue-200"
                            >
                                Shipper
                            </span>
                        </td>

                        <td class="p-4 text-sm text-gray-600">
                            <div
                                v-if="user.role === 'restaurant'"
                                class="space-y-0.5"
                            >
                                <p class="font-bold text-gray-800 italic">
                                    "{{ user.restaurant_name }}"
                                </p>
                                <p
                                    class="text-xs text-gray-500 leading-relaxed"
                                >
                                    Đ/C: {{ user.address }}
                                </p>
                            </div>
                            <div
                                v-if="user.role === 'shipper'"
                                class="space-y-0.5"
                            >
                                <p
                                    class="text-xs text-gray-500 uppercase font-bold tracking-tighter"
                                >
                                    CCCD: {{ user.id_card_number }}
                                </p>
                                <p
                                    class="text-xs text-blue-600 font-mono font-bold"
                                >
                                    {{ user.license_plate }}
                                </p>
                            </div>
                        </td>

                        <td
                            class="p-4 text-center text-xs font-medium text-gray-500 uppercase"
                        >
                            {{
                                user.created_at
                                    ? new Date(
                                          user.created_at,
                                      ).toLocaleDateString("vi-VN")
                                    : "N/A"
                            }}
                        </td>

                        <td class="p-4">
                            <div class="flex justify-center items-center gap-3">
                                <button
                                    @click="approveUser(user.id)"
                                    class="px-5 py-2 rounded-lg text-xs font-black shadow-md transition hover:opacity-80 active:scale-95"
                                    style="
                                        background-color: #16a34a;
                                        color: white;
                                        min-width: 80px;
                                    "
                                >
                                    DUYỆT
                                </button>

                                <button
                                    @click="rejectUser(user.id)"
                                    class="px-5 py-2 rounded-lg text-xs font-black shadow-md transition hover:bg-gray-100 active:scale-95"
                                    style="
                                        background-color: white;
                                        color: #dc2626;
                                        border: 1px solid #fee2e2;
                                        min-width: 80px;
                                    "
                                >
                                    TỪ CHỐI
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div
                v-if="users.length === 0"
                class="p-20 text-center text-gray-400"
            >
                <p class="text-sm font-medium">
                    ✨ Không có đối tác nào đang chờ phê duyệt.
                </p>
            </div>
        </div>
    </AdminLayout>
</template>
