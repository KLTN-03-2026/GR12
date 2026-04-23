<template>
    <div class="max-w-6xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">
            Quản lý phê duyệt yêu cầu cập nhật
        </h1>

        <div v-if="requests.length === 0" class="text-center py-12">
            <p class="text-gray-500">
                Không có yêu cầu nào đang chờ phê duyệt.
            </p>
        </div>

        <div v-else class="space-y-6">
            <div
                v-for="request in requests"
                :key="request.id"
                class="bg-white p-6 rounded-lg shadow"
            >
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold">
                            {{ request.user.name }} ({{ request.user.email }})
                        </h3>
                        <p class="text-sm text-gray-600">
                            {{ getRequestDescription(request) }}
                        </p>
                        <p class="text-xs text-gray-500">
                            Ngày yêu cầu:
                            {{
                                new Date(
                                    request.created_at,
                                ).toLocaleDateString()
                            }}
                        </p>
                    </div>
                    <span
                        class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800"
                    >
                        {{ request.status }}
                    </span>
                </div>

                <!-- Show changes -->
                <div class="mb-4 p-4 bg-gray-50 rounded">
                    <h4 class="font-medium mb-2">Thay đổi yêu cầu:</h4>
                    <div v-if="request.type === 'password'">
                        <p>Mật khẩu mới sẽ được cập nhật</p>
                    </div>
                    <div v-else-if="request.type === 'avatar'">
                        <p>Avatar mới sẽ được cập nhật</p>
                    </div>
                    <div v-else-if="request.type === 'location'">
                        <div class="space-y-1">
                            <p v-if="request.new_value.restaurant_name">
                                <strong>Tên quán:</strong>
                                {{ request.new_value.restaurant_name }}
                            </p>
                            <p v-if="request.new_value.address">
                                <strong>Địa chỉ:</strong>
                                {{ request.new_value.address }}
                            </p>
                            <p v-if="request.new_value.address_detail">
                                <strong>Địa chỉ chi tiết:</strong>
                                {{ request.new_value.address_detail }}
                            </p>
                            <p
                                v-if="
                                    request.new_value.latitude &&
                                    request.new_value.longitude
                                "
                            >
                                <strong>Tọa độ:</strong>
                                {{ request.new_value.latitude }},
                                {{ request.new_value.longitude }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="flex space-x-4">
                    <button
                        @click="approveRequest(request)"
                        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600"
                    >
                        Phê duyệt
                    </button>
                    <button
                        @click="showRejectModal(request)"
                        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
                    >
                        Từ chối
                    </button>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div
            v-if="showReject"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white p-6 rounded-lg max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4">Lý do từ chối</h3>
                <textarea
                    v-model="rejectComment"
                    class="w-full p-2 border rounded mb-4"
                    rows="4"
                    placeholder="Nhập lý do từ chối..."
                ></textarea>
                <div class="flex justify-end space-x-2">
                    <button
                        @click="showReject = false"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800"
                    >
                        Hủy
                    </button>
                    <button
                        @click="rejectRequest"
                        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
                    >
                        Xác nhận từ chối
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    requests: Array,
});

const showReject = ref(false);
const rejectComment = ref("");
const selectedRequest = ref(null);

const getRequestDescription = (request) => {
    switch (request.type) {
        case "password":
            return "Yêu cầu cập nhật mật khẩu";
        case "avatar":
            return "Yêu cầu cập nhật avatar";
        case "location":
            return "Yêu cầu cập nhật vị trí quán";
        default:
            return "Yêu cầu cập nhật thông tin";
    }
};

const approveRequest = (request) => {
    if (confirm("Bạn có chắc chắn muốn phê duyệt yêu cầu này?")) {
        router.post(
            `/admin/approvals/${request.id}/approve`,
            {},
            {
                onSuccess: () => {
                    window.location.reload();
                },
            },
        );
    }
};

const showRejectModal = (request) => {
    selectedRequest.value = request;
    showReject.value = true;
    rejectComment.value = "";
};

const rejectRequest = () => {
    if (!rejectComment.value.trim()) {
        alert("Vui lòng nhập lý do từ chối.");
        return;
    }

    router.post(
        `/admin/approvals/${selectedRequest.value.id}/reject`,
        {
            comment: rejectComment.value,
        },
        {
            onSuccess: () => {
                showReject.value = false;
                selectedRequest.value = null;
                rejectComment.value = "";
                window.location.reload();
            },
        },
    );
};
</script>
