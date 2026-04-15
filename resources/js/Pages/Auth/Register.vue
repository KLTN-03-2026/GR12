<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import { useToast } from "vue-toastification";
import axios from "axios";

const toast = useToast();
const page = usePage();

// Quản lý các bước đăng ký
const currentStep = ref(1);
const showPassword = ref(false);
const suggestions = ref([]);
const isSearching = ref(false);

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
    house_number: "",
    latitude: null,
    longitude: null,
    id_card_number: "",
    license_plate: "",
    password: "",
    password_confirmation: "",
});

// --- LOGIC CHUYỂN BƯỚC ---
const nextStep = () => {
    if (currentStep.value < 3) currentStep.value++;
};
const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

// Validation cho từng bước
const canProceed = computed(() => {
    if (currentStep.value === 1) {
        return form.role !== '';
    }
    if (currentStep.value === 2) {
        return form.name && form.email && form.phone;
    }
    return true;
});

// --- HỆ THỐNG TOAST TỰ ĐỘNG ---
const errors = computed(() => page.props.errors);
watch(
    errors,
    (newErrors) => {
        const errorKeys = Object.keys(newErrors);
        if (errorKeys.length > 0) {
            toast.error(newErrors[errorKeys[0]]);
        }
    },
    { deep: true },
);

// --- TÌM KIẾM ĐỊA CHỈ (PHOTON API) ---
const searchAddress = async (query) => {
    if (!query || query.length < 3) {
        suggestions.value = [];
        return;
    }
    isSearching.value = true;
    try {
        const searchTerms = query.includes("Vietnam")
            ? query
            : `${query}, Vietnam`;
        const response = await axios.get(`https://photon.komoot.io/api/`, {
            params: {
                q: searchTerms,
                limit: 5,
                bbox: "102.14,8.18,109.46,23.39",
            },
        });
        suggestions.value = response.data.features;
    } catch (error) {
        console.error("Lỗi Photon API:", error);
    } finally {
        isSearching.value = false;
    }
};

const selectAddress = (feature) => {
    const { properties, geometry } = feature;
    if (properties.housenumber) form.house_number = properties.housenumber;
    const parts = [
        properties.street || properties.name,
        properties.district || properties.suburb,
        properties.city || properties.state,
    ];
    form.address = parts.filter(Boolean).join(", ");
    form.longitude = geometry.coordinates[0];
    form.latitude = geometry.coordinates[1];
    suggestions.value = [];
};

let debounceTimeout = null;
watch(
    () => form.address,
    (newVal) => {
        if (form.role === "restaurant" && newVal.length >= 3) {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => searchAddress(newVal), 500);
        }
    },
);

const submit = () => {
    form.post(route("register"), {
        onSuccess: () => {
            toast.success("Đăng ký thành công! Chào mừng bạn đến với FoodXpress!");
        },
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Gia nhập cộng đồng FoodXpress" />

        <div class="min-h-screen flex">
            <!-- LEFT IMAGE -->
            <div
                class="hidden md:flex w-1/2 bg-orange-500 items-center justify-center"
            >
                <img
                    src="https://images.unsplash.com/photo-1504674900247-0877df9cc836"
                    class="object-cover h-full w-full opacity-90"
                />
            </div>

            <!-- RIGHT FORM -->
            <div
                class="flex w-full md:w-1/2 items-center justify-center bg-gray-50 px-6"
            >
                <div class="w-full max-w-md bg-white p-8 rounded-3xl shadow-xl">
                    <!-- Progress Bar -->
                    <div class="flex gap-2 mb-6">
                        <div
                            v-for="s in 3"
                            :key="s"
                            class="h-1.5 flex-1 rounded-full transition-all duration-700 shadow-sm"
                            :class="
                                currentStep >= s
                                    ? 'bg-orange-500'
                                    : 'bg-gray-200'
                            "
                        ></div>
                    </div>

                    <div class="mb-6 text-center">
                        <h1 class="text-3xl font-black text-gray-800">
                            {{ currentStep === 1 ? 'Chào bạn mới! 👋' : currentStep === 2 ? 'Thông tin cá nhân' : 'Hoàn tất 🚀' }}
                        </h1>
                        <p class="text-gray-500 mt-2">
                            {{ currentStep === 1 ? 'Chọn vai trò để bắt đầu trải nghiệm' : currentStep === 2 ? 'Hãy để chúng tôi biết về bạn' : 'Bảo mật tài khoản & Địa chỉ' }}
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- STEP 1: ROLE SELECTION -->
                        <div v-if="currentStep === 1" class="space-y-4">
                            <div class="grid grid-cols-1 gap-3">
                                <label
                                    v-for="r in [
                                        ['customer', '😋', 'Khách hàng', 'Đặt món & Thưởng thức'],
                                        ['restaurant', '🏪', 'Chủ quán', 'Kinh doanh & Phát triển'],
                                        ['shipper', '🛵', 'Shipper', 'Giao hàng tăng thu nhập']
                                    ]"
                                    :key="r[0]"
                                    class="group relative flex items-center gap-4 p-4 rounded-xl border-2 transition-all duration-300 cursor-pointer"
                                    :class="
                                        form.role === r[0]
                                            ? 'border-orange-500 bg-orange-50'
                                            : 'border-gray-200 hover:border-orange-300 hover:bg-orange-50/50'
                                    "
                                >
                                    <input
                                        type="radio"
                                        v-model="form.role"
                                        :value="r[0]"
                                        class="hidden"
                                    />
                                    <div class="text-2xl">{{ r[1] }}</div>
                                    <div class="flex-1">
                                        <h4 class="font-bold text-gray-800 text-sm">{{ r[2] }}</h4>
                                        <p class="text-xs text-gray-500">{{ r[3] }}</p>
                                    </div>
                                    <div
                                        v-if="form.role === r[0]"
                                        class="w-5 h-5 bg-orange-500 rounded-full flex items-center justify-center text-white text-xs"
                                    >
                                        ✓
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- STEP 2: PERSONAL INFO -->
                        <div v-if="currentStep === 2" class="space-y-4">
                            <div>
                                <InputLabel for="name" value="Họ và tên" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500"
                                    v-model="form.name"
                                    required
                                    placeholder="Nguyễn Văn A"
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div>
                                <InputLabel for="email" value="Email" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="mt-1 block w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500"
                                    v-model="form.email"
                                    required
                                    placeholder="example@email.com"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div>
                                <InputLabel for="phone" value="Số điện thoại" />
                                <TextInput
                                    id="phone"
                                    type="tel"
                                    class="mt-1 block w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500"
                                    v-model="form.phone"
                                    required
                                    placeholder="033xxxxxxx"
                                />
                                <InputError class="mt-2" :message="form.errors.phone" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="birthday" value="Ngày sinh" />
                                    <TextInput
                                        id="birthday"
                                        type="date"
                                        class="mt-1 block w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500"
                                        v-model="form.birthday"
                                    />
                                    <InputError class="mt-2" :message="form.errors.birthday" />
                                </div>

                                <div>
                                    <InputLabel for="gender" value="Giới tính" />
                                    <select
                                        id="gender"
                                        v-model="form.gender"
                                        class="mt-1 block w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500 px-3 py-2"
                                    >
                                        <option value="">Chọn giới tính</option>
                                        <option value="Nam">Nam</option>
                                        <option value="Nữ">Nữ</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.gender" />
                                </div>
                            </div>

                            <div>
                                <InputLabel for="occupation" value="Nghề nghiệp" />
                                <select
                                    id="occupation"
                                    v-model="form.occupation"
                                    class="mt-1 block w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500 px-3 py-2"
                                >
                                    <option value="">Chọn nghề nghiệp</option>
                                    <option value="Sinh viên">Sinh viên</option>
                                    <option value="Nhân viên văn phòng">Nhân viên văn phòng</option>
                                    <option value="Lao động tự do">Lao động tự do</option>
                                    <option value="Kinh doanh">Kinh doanh</option>
                                    <option value="Khác">Khác</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.occupation" />
                            </div>
                        </div>

                        <!-- STEP 3: FINAL DETAILS -->
                        <div v-if="currentStep === 3" class="space-y-4">
                            <!-- Restaurant specific fields -->
                            <div v-if="form.role === 'restaurant'" class="space-y-4">
                                <div>
                                    <InputLabel for="restaurant_name" value="Tên quán ăn" />
                                    <TextInput
                                        id="restaurant_name"
                                        type="text"
                                        class="mt-1 block w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500"
                                        v-model="form.restaurant_name"
                                        required
                                        placeholder="Tên thương hiệu quán ăn"
                                    />
                                    <InputError class="mt-2" :message="form.errors.restaurant_name" />
                                </div>

                                <div class="relative">
                                    <InputLabel for="address" value="Địa chỉ quán" />
                                    <TextInput
                                        id="address"
                                        type="text"
                                        class="mt-1 block w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500"
                                        v-model="form.address"
                                        required
                                        placeholder="📍 Tìm đường, phường, quận..."
                                    />
                                    <InputError class="mt-2" :message="form.errors.address" />

                                    <!-- Address suggestions -->
                                    <ul
                                        v-if="suggestions.length > 0"
                                        class="absolute z-50 w-full bg-white mt-1 rounded-xl shadow-lg border py-2 max-h-48 overflow-y-auto"
                                    >
                                        <li
                                            v-for="(s, i) in suggestions"
                                            :key="i"
                                            @click="selectAddress(s)"
                                            class="px-4 py-2 hover:bg-gray-50 cursor-pointer text-sm"
                                        >
                                            {{ s.properties.name || s.properties.street }}, {{ s.properties.city }}
                                        </li>
                                    </ul>
                                </div>

                                <div>
                                    <InputLabel for="house_number" value="Số nhà/Tòa nhà" />
                                    <TextInput
                                        id="house_number"
                                        type="text"
                                        class="mt-1 block w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500"
                                        v-model="form.house_number"
                                        placeholder="Ví dụ: 123, Tòa A, Chung cư ABC..."
                                    />
                                    <InputError class="mt-2" :message="form.errors.house_number" />
                                </div>
                            </div>

                            <!-- Shipper specific fields -->
                            <div v-if="form.role === 'shipper'" class="space-y-4">
                                <div>
                                    <InputLabel for="id_card_number" value="CMND/CCCD" />
                                    <TextInput
                                        id="id_card_number"
                                        type="text"
                                        class="mt-1 block w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500"
                                        v-model="form.id_card_number"
                                        required
                                        placeholder="123456789"
                                    />
                                    <InputError class="mt-2" :message="form.errors.id_card_number" />
                                </div>

                                <div>
                                    <InputLabel for="license_plate" value="Biển số xe" />
                                    <TextInput
                                        id="license_plate"
                                        type="text"
                                        class="mt-1 block w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500"
                                        v-model="form.license_plate"
                                        required
                                        placeholder="43A-12345"
                                    />
                                    <InputError class="mt-2" :message="form.errors.license_plate" />
                                </div>
                            </div>

                            <!-- Password fields -->
                            <div>
                                <InputLabel for="password" value="Mật khẩu" />
                                <div class="relative mt-1">
                                    <TextInput
                                        id="password"
                                        :type="showPassword ? 'text' : 'password'"
                                        class="block w-full rounded-xl border-gray-200 pr-12 focus:border-orange-500 focus:ring-orange-500"
                                        v-model="form.password"
                                        required
                                        placeholder="••••••••"
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                    >
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path v-if="!showPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path v-if="!showPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            <path v-if="showPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                        </svg>
                                    </button>
                                </div>
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>

                            <div>
                                <InputLabel for="password_confirmation" value="Xác nhận mật khẩu" />
                                <TextInput
                                    id="password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500"
                                    v-model="form.password_confirmation"
                                    required
                                    placeholder="••••••••"
                                />
                                <InputError class="mt-2" :message="form.errors.password_confirmation" />
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex gap-3 pt-4">
                            <button
                                v-if="currentStep > 1"
                                type="button"
                                @click="prevStep"
                                class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-xl font-semibold hover:bg-gray-200 transition-colors"
                            >
                                Quay lại
                            </button>

                            <button
                                v-if="currentStep < 3"
                                type="button"
                                @click="nextStep"
                                :disabled="!canProceed"
                                class="flex-1 bg-orange-500 text-white py-3 px-4 rounded-xl font-semibold hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                Tiếp tục
                            </button>

                            <PrimaryButton
                                v-if="currentStep === 3"
                                :disabled="form.processing"
                                class="flex-1 bg-orange-500 hover:bg-orange-600 text-white py-3 px-4 rounded-xl font-semibold"
                            >
                                <span v-if="form.processing">Đang xử lý...</span>
                                <span v-else>Gia nhập ngay</span>
                            </PrimaryButton>
                        </div>
                    </form>

                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600">
                            Đã có tài khoản?
                            <Link href="/login" class="text-orange-500 hover:text-orange-600 font-semibold">
                                Đăng nhập
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
