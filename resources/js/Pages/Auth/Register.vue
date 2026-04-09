<script setup>
import { ref } from "vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const showPassword = ref(false);

const form = useForm({
    name: "",
    email: "",
    phone: "",
    gender: "",
    birthday: "",
    occupation: "",
    role: "customer",
    restaurant_name: "",
    address: "",
    id_card_number: "",
    license_plate: "",
    password: "",
    password_confirmation: "",
});

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Đăng ký tài khoản FoodXpress" />

        <div class="max-w-3xl mx-auto">
            <div
                class="bg-white shadow-xl rounded-2xl p-6 border border-gray-100"
            >
                <h2 class="text-2xl font-bold text-gray-800 mb-1">
                    Tạo tài khoản
                </h2>
                <p class="text-sm text-gray-500 mb-6">
                    Tham gia FoodXpress ngay hôm nay 🚀
                </p>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Role -->
                    <div
                        class="p-4 bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl border"
                    >
                        <InputLabel
                            value="Bạn muốn đăng ký với vai trò"
                            class="text-orange-700 font-semibold mb-3"
                        />
                        <div class="grid grid-cols-3 gap-3">
                            <label
                                v-for="r in [
                                    ['customer', 'Khách Hàng'],
                                    ['restaurant', 'Quán Ăn'],
                                    ['shipper', 'Shipper'],
                                ]"
                                :key="r[0]"
                                class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer transition-all duration-200 bg-white hover:shadow-md"
                                :class="{
                                    'border-orange-500 ring-2 ring-orange-300 scale-105':
                                        form.role === r[0],
                                }"
                            >
                                <input
                                    type="radio"
                                    v-model="form.role"
                                    :value="r[0]"
                                    class="hidden"
                                />
                                <span class="text-sm font-semibold">
                                    {{ r[1] }}
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Name + Email -->
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Họ và tên" />
                            <TextInput
                                v-model="form.name"
                                class="mt-1 w-full rounded-xl focus:ring-orange-500"
                                placeholder="Nguyễn Văn A"
                                required
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div>
                            <InputLabel value="Email" />
                            <TextInput
                                type="email"
                                v-model="form.email"
                                class="mt-1 w-full rounded-xl focus:ring-orange-500"
                                placeholder="example@gmail.com"
                                required
                            />
                            <InputError :message="form.errors.email" />
                        </div>
                    </div>

                    <!-- Dynamic Sections -->
                    <transition name="fade">
                        <div
                            v-if="form.role === 'restaurant'"
                            class="p-4 bg-gray-50 rounded-xl border-l-4 border-orange-500 space-y-3"
                        >
                            <h3 class="font-semibold text-gray-700">
                                Thông tin quán
                            </h3>
                            <TextInput
                                v-model="form.restaurant_name"
                                placeholder="Tên quán"
                                required
                                class="rounded-xl"
                            />
                            <TextInput
                                v-model="form.address"
                                placeholder="Địa chỉ"
                                required
                                class="rounded-xl"
                            />
                        </div>
                    </transition>

                    <transition name="fade">
                        <div
                            v-if="form.role === 'shipper'"
                            class="p-4 bg-gray-50 rounded-xl border-l-4 border-blue-500 space-y-3"
                        >
                            <h3 class="font-semibold text-gray-700">
                                Thông tin shipper
                            </h3>
                            <TextInput
                                v-model="form.id_card_number"
                                placeholder="CCCD (12 số)"
                                required
                                class="rounded-xl"
                            />
                            <TextInput
                                v-model="form.license_plate"
                                placeholder="Biển số xe"
                                required
                                class="rounded-xl"
                            />
                        </div>
                    </transition>

                    <!-- Phone -->
                    <div>
                        <InputLabel value="Số điện thoại" />
                        <TextInput
                            v-model="form.phone"
                            class="mt-1 w-full rounded-xl"
                            placeholder="09xxxxxxxx"
                            required
                        />
                        <InputError :message="form.errors.phone" />
                    </div>

                    <!-- Gender + Birthday -->
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Giới tính" />
                            <select
                                v-model="form.gender"
                                class="mt-1 w-full rounded-xl border-gray-300 focus:ring-orange-500"
                                required
                            >
                                <option value="" disabled>Chọn...</option>
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Ngày sinh" />
                            <TextInput
                                type="date"
                                v-model="form.birthday"
                                class="mt-1 w-full rounded-xl"
                                required
                            />
                        </div>
                    </div>

                    <!-- Occupation -->
                    <div>
                        <InputLabel value="Nghề nghiệp" />
                        <select
                            v-model="form.occupation"
                            class="mt-1 w-full rounded-xl border-gray-300 focus:ring-orange-500"
                            required
                        >
                            <option value="" disabled>Chọn...</option>
                            <option>Sinh viên</option>
                            <option>Nhân viên văn phòng</option>
                            <option>Lao động tự do</option>
                            <option>Kinh doanh</option>
                            <option>Khác</option>
                        </select>
                    </div>

                    <!-- Password -->
                    <div class="relative">
                        <InputLabel value="Mật khẩu" />
                        <TextInput
                            :type="showPassword ? 'text' : 'password'"
                            v-model="form.password"
                            class="mt-1 w-full rounded-xl pr-10"
                            required
                        />
                        <button
                            type="button"
                            @click="togglePassword"
                            class="absolute right-3 top-9 text-gray-500 hover:text-orange-600"
                        >
                            👁️
                        </button>
                        <InputError :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel value="Xác nhận mật khẩu" />
                        <TextInput
                            :type="showPassword ? 'text' : 'password'"
                            v-model="form.password_confirmation"
                            class="mt-1 w-full rounded-xl"
                            required
                        />
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-between items-center pt-4">
                        <Link
                            :href="route('login')"
                            class="text-sm text-gray-500 hover:text-orange-600"
                        >
                            Đã có tài khoản?
                        </Link>
                        <PrimaryButton
                            class="bg-orange-600 hover:bg-orange-700 px-6 py-2 rounded-xl shadow-md"
                            :disabled="form.processing"
                        >
                            Đăng ký
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: all 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
