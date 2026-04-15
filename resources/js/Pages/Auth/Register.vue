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

const flashToast = computed(() => page.props.flash.toast);
watch(
    flashToast,
    (newToast) => {
        if (newToast && newToast.message) {
            toast[newToast.type || "success"](newToast.message);
        }
    },
    { immediate: true },
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
            toast.success("Đăng ký thành công! Vui lòng đăng nhập.");
        },
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Khởi tạo tài khoản FoodXpress" />

        <div class="min-h-screen flex bg-white font-sans overflow-hidden">
            <div
                class="hidden lg:flex lg:w-5/12 bg-orange-500 relative overflow-hidden items-center justify-center"
            >
                <img
                    src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1000&q=80"
                    class="absolute inset-0 w-full h-full object-cover mix-blend-multiply opacity-70"
                />
                <div class="relative z-10 p-12 text-white text-center">
                    <h1
                        class="text-6xl font-black italic tracking-tighter leading-none mb-4"
                    >
                        FOOD<br />XPRESS.
                    </h1>
                    <p
                        class="text-orange-100 font-bold uppercase tracking-[0.3em] text-xs"
                    >
                        Fast • Fresh • Reliable
                    </p>
                </div>
            </div>

            <div
                class="w-full lg:w-7/12 flex flex-col items-center justify-center p-6 md:p-12 bg-gray-50/50"
            >
                <div class="w-full max-w-xl">
                    <div
                        class="mb-12 flex items-center justify-between relative px-4"
                    >
                        <div
                            class="absolute left-4 right-4 top-1/2 -translate-y-1/2 h-1.5 bg-gray-200 rounded-full z-0"
                        ></div>
                        <div
                            class="absolute left-4 top-1/2 -translate-y-1/2 h-1.5 bg-orange-500 transition-all duration-700 rounded-full z-0"
                            :style="{
                                width: ((currentStep - 1) / 2) * 100 + '%',
                            }"
                        ></div>

                        <div
                            v-for="step in [1, 2, 3]"
                            :key="step"
                            class="relative z-10 w-12 h-12 rounded-2xl flex items-center justify-center font-black transition-all duration-500 shadow-sm"
                            :class="
                                currentStep >= step
                                    ? 'bg-orange-500 text-white rotate-0'
                                    : 'bg-white text-gray-300 border-2 border-gray-100 rotate-12'
                            "
                        >
                            {{ step }}
                        </div>
                    </div>

                    <div
                        class="bg-white shadow-[0_20px_60px_rgba(0,0,0,0.03)] rounded-[3rem] border border-gray-100 overflow-hidden"
                    >
                        <form @submit.prevent="submit" class="p-8 md:p-12">
                            <div
                                v-if="currentStep === 1"
                                class="animate-in fade-in slide-in-from-bottom-6 duration-700"
                            >
                                <div class="mb-10">
                                    <h2
                                        class="text-3xl font-black text-gray-900 tracking-tight italic uppercase"
                                    >
                                        Bạn là ai? 👋
                                    </h2>
                                    <p
                                        class="text-gray-400 font-bold text-[10px] uppercase tracking-widest mt-1"
                                    >
                                        Hãy chọn vai trò để chúng tôi phục vụ
                                        bạn tốt hơn
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 gap-4">
                                    <label
                                        v-for="r in [
                                            [
                                                'customer',
                                                '😋 Khách Hàng',
                                                'Khám phá vạn món ngon',
                                            ],
                                            [
                                                'restaurant',
                                                '🏪 Quán Ăn',
                                                'Mở rộng kinh doanh tại đây',
                                            ],
                                            [
                                                'shipper',
                                                '🛵 Shipper',
                                                'Giao hàng, tăng thu nhập',
                                            ],
                                        ]"
                                        :key="r[0]"
                                        class="group relative p-6 rounded-3xl border-2 transition-all duration-300 flex items-center gap-6 cursor-pointer"
                                        :class="
                                            form.role === r[0]
                                                ? 'border-orange-500 bg-orange-50/30'
                                                : 'border-gray-50 hover:border-orange-100 bg-white'
                                        "
                                    >
                                        <input
                                            type="radio"
                                            v-model="form.role"
                                            :value="r[0]"
                                            class="hidden"
                                        />
                                        <div
                                            class="text-4xl bg-gray-50 w-16 h-16 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform"
                                        >
                                            {{ r[1].split(" ")[0] }}
                                        </div>
                                        <div class="flex-1">
                                            <h4
                                                class="font-black text-gray-800 uppercase text-sm"
                                            >
                                                {{ r[1].split(" ")[1] }}
                                            </h4>
                                            <p
                                                class="text-[11px] text-gray-400 font-bold tracking-tight"
                                            >
                                                {{ r[2] }}
                                            </p>
                                        </div>
                                        <div
                                            v-if="form.role === r[0]"
                                            class="text-orange-500"
                                        >
                                            <svg
                                                class="w-6 h-6"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L8 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </div>
                                    </label>
                                </div>

                                <div class="mt-8 flex justify-end">
                                    <button
                                        type="button"
                                        @click="nextStep"
                                        class="bg-orange-500 text-white px-8 py-3 rounded-2xl font-black uppercase text-sm hover:bg-orange-600 transition-all"
                                    >
                                        Tiếp theo →
                                    </button>
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 2"
                                class="animate-in fade-in slide-in-from-right-6 duration-700 space-y-6"
                            >
                                <div class="mb-10">
                                    <h2
                                        class="text-3xl font-black text-gray-900 tracking-tight italic uppercase"
                                    >
                                        Hồ sơ cá nhân
                                    </h2>
                                    <p
                                        class="text-gray-400 font-bold text-[10px] uppercase tracking-widest mt-1"
                                    >
                                        Giới thiệu một chút về bản thân bạn
                                    </p>
                                </div>

                                <div class="grid md:grid-cols-2 gap-5">
                                    <div class="space-y-1">
                                        <InputLabel
                                            value="Họ và tên"
                                            class="ml-2 text-[10px] font-black uppercase text-gray-400"
                                        />
                                        <TextInput
                                            v-model="form.name"
                                            class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-4 focus:ring-orange-100"
                                            placeholder="Nguyễn Văn A"
                                            required
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <InputLabel
                                            value="Email liên hệ"
                                            class="ml-2 text-[10px] font-black uppercase text-gray-400"
                                        />
                                        <TextInput
                                            v-model="form.email"
                                            type="email"
                                            class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-4 focus:ring-orange-100"
                                            placeholder="email@example.com"
                                            required
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <InputLabel
                                            value="Số điện thoại"
                                            class="ml-2 text-[10px] font-black uppercase text-gray-400"
                                        />
                                        <TextInput
                                            v-model="form.phone"
                                            class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-4 focus:ring-orange-100"
                                            placeholder="090XXXXXXXX"
                                            required
                                        />
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="space-y-1">
                                            <InputLabel
                                                value="Giới tính"
                                                class="ml-2 text-[10px] font-black uppercase text-gray-400"
                                            />
                                            <select
                                                v-model="form.gender"
                                                class="w-full rounded-2xl border-none bg-gray-50 h-[56px] font-bold text-sm focus:ring-4 focus:ring-orange-100"
                                            >
                                                <option value="" disabled>
                                                    Chọn...
                                                </option>
                                                <option value="Nam">Nam</option>
                                                <option value="Nữ">Nữ</option>
                                            </select>
                                        </div>
                                        <div class="space-y-1">
                                            <InputLabel
                                                value="Ngày sinh"
                                                class="ml-2 text-[10px] font-black uppercase text-gray-400"
                                            />
                                            <TextInput
                                                v-model="form.birthday"
                                                type="date"
                                                class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-4 focus:ring-orange-100 text-xs"
                                                required
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <InputLabel
                                        value="Công việc"
                                        class="ml-2 text-[10px] font-black uppercase text-gray-400"
                                    />
                                    <select
                                        v-model="form.occupation"
                                        class="w-full rounded-2xl border-none bg-gray-50 h-[56px] font-bold text-sm focus:ring-4 focus:ring-orange-100 shadow-none"
                                    >
                                        <option
                                            v-for="occ in [
                                                'Sinh viên',
                                                'Nhân viên văn phòng',
                                                'Lao động tự do',
                                                'Kinh doanh',
                                                'Khác',
                                            ]"
                                            :key="occ"
                                            :value="occ"
                                        >
                                            {{ occ }}
                                        </option>
                                    </select>
                                </div>

                                <div class="flex justify-between mt-8">
                                    <button
                                        type="button"
                                        @click="prevStep"
                                        class="bg-gray-200 text-gray-700 px-8 py-3 rounded-2xl font-black uppercase text-sm hover:bg-gray-300 transition-all"
                                    >
                                        ← Quay lại
                                    </button>
                                    <button
                                        type="button"
                                        @click="nextStep"
                                        class="bg-orange-500 text-white px-8 py-3 rounded-2xl font-black uppercase text-sm hover:bg-orange-600 transition-all"
                                    >
                                        Tiếp theo →
                                    </button>
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 3"
                                class="animate-in fade-in slide-in-from-right-6 duration-700 space-y-8"
                            >
                                <div class="mb-10 text-center">
                                    <h2
                                        class="text-3xl font-black text-gray-900 tracking-tight italic uppercase"
                                    >
                                        Bảo mật tài khoản
                                    </h2>
                                    <p
                                        class="text-gray-400 font-bold text-[10px] uppercase tracking-widest mt-1"
                                    >
                                        Tạo mật khẩu mạnh để bảo vệ tài khoản
                                    </p>
                                </div>

                                <div class="space-y-6">
                                    <div class="space-y-1">
                                        <InputLabel
                                            value="Mật khẩu"
                                            class="ml-2 text-[10px] font-black uppercase text-gray-400"
                                        />
                                        <div class="relative">
                                            <TextInput
                                                v-model="form.password"
                                                :type="
                                                    showPassword
                                                        ? 'text'
                                                        : 'password'
                                                "
                                                class="w-full bg-gray-50 border-none rounded-2xl pr-12 p-4 focus:ring-4 focus:ring-orange-100"
                                                placeholder="••••••••"
                                                required
                                            />
                                            <button
                                                type="button"
                                                @click="
                                                    showPassword = !showPassword
                                                "
                                                class="absolute inset-y-0 right-4 flex items-center text-lg hover:scale-110 transition"
                                            >
                                                <span v-if="showPassword"
                                                    >🙈</span
                                                >
                                                <span v-else>👁️</span>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-1">
                                        <InputLabel
                                            value="Xác nhận mật khẩu"
                                            class="ml-2 text-[10px] font-black uppercase text-gray-400"
                                        />
                                        <TextInput
                                            v-model="form.password_confirmation"
                                            type="password"
                                            class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-4 focus:ring-orange-100"
                                            placeholder="••••••••"
                                            required
                                        />
                                    </div>

                                    <!-- Additional fields for restaurant -->
                                    <div
                                        v-if="form.role === 'restaurant'"
                                        class="space-y-6"
                                    >
                                        <div class="space-y-1">
                                            <InputLabel
                                                value="Tên quán ăn"
                                                class="ml-2 text-[10px] font-black uppercase text-gray-400"
                                            />
                                            <TextInput
                                                v-model="form.restaurant_name"
                                                class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-4 focus:ring-orange-100"
                                                placeholder="Nhà hàng ABC"
                                            />
                                        </div>

                                        <div class="space-y-1">
                                            <InputLabel
                                                value="Địa chỉ quán ăn"
                                                class="ml-2 text-[10px] font-black uppercase text-gray-400"
                                            />
                                            <div class="relative">
                                                <TextInput
                                                    v-model="form.address"
                                                    class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-4 focus:ring-orange-100"
                                                    placeholder="Nhập địa chỉ..."
                                                />
                                                <div
                                                    v-if="isSearching"
                                                    class="absolute right-4 top-1/2 -translate-y-1/2"
                                                >
                                                    <div
                                                        class="w-4 h-4 border-2 border-orange-500 border-t-transparent rounded-full animate-spin"
                                                    ></div>
                                                </div>
                                            </div>
                                            <div
                                                v-if="suggestions.length > 0"
                                                class="bg-white border border-gray-200 rounded-2xl mt-2 max-h-40 overflow-y-auto"
                                            >
                                                <div
                                                    v-for="suggestion in suggestions"
                                                    :key="
                                                        suggestion.properties
                                                            .osm_id
                                                    "
                                                    @click="
                                                        selectAddress(
                                                            suggestion,
                                                        )
                                                    "
                                                    class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                                >
                                                    <div
                                                        class="font-semibold text-sm"
                                                    >
                                                        {{
                                                            suggestion
                                                                .properties
                                                                .name ||
                                                            "Địa chỉ không xác định"
                                                        }}
                                                    </div>
                                                    <div
                                                        class="text-xs text-gray-500"
                                                    >
                                                        {{
                                                            [
                                                                suggestion
                                                                    .properties
                                                                    .street,
                                                                suggestion
                                                                    .properties
                                                                    .district,
                                                                suggestion
                                                                    .properties
                                                                    .city,
                                                            ]
                                                                .filter(Boolean)
                                                                .join(", ")
                                                        }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Additional fields for shipper -->
                                    <div
                                        v-if="form.role === 'shipper'"
                                        class="space-y-6"
                                    >
                                        <div class="space-y-1">
                                            <InputLabel
                                                value="Số CMND/CCCD"
                                                class="ml-2 text-[10px] font-black uppercase text-gray-400"
                                            />
                                            <TextInput
                                                v-model="form.id_card_number"
                                                class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-4 focus:ring-orange-100"
                                                placeholder="123456789"
                                            />
                                        </div>

                                        <div class="space-y-1">
                                            <InputLabel
                                                value="Biển số xe (tùy chọn)"
                                                class="ml-2 text-[10px] font-black uppercase text-gray-400"
                                            />
                                            <TextInput
                                                v-model="form.license_plate"
                                                class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-4 focus:ring-orange-100"
                                                placeholder="51A-12345"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-between mt-8">
                                    <button
                                        type="button"
                                        @click="prevStep"
                                        class="bg-gray-200 text-gray-700 px-8 py-3 rounded-2xl font-black uppercase text-sm hover:bg-gray-300 transition-all"
                                    >
                                        ← Quay lại
                                    </button>
                                    <PrimaryButton
                                        class="px-8 py-3 rounded-2xl bg-orange-500 hover:bg-gray-900 text-white font-black uppercase tracking-[0.2em] text-xs shadow-xl shadow-orange-100 transition-all active:scale-95 border-none"
                                        :disabled="form.processing"
                                    >
                                        {{
                                            form.processing
                                                ? "Đang tạo tài khoản..."
                                                : "Hoàn thành đăng ký 🚀"
                                        }}
                                    </PrimaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
.animate-in {
    animation-fill-mode: both;
}

.fade-in {
    animation-name: fadeIn;
}

.slide-in-from-bottom-6 {
    animation-name: slideInFromBottom;
}

.slide-in-from-right-6 {
    animation-name: slideInFromRight;
}

.duration-700 {
    animation-duration: 700ms;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideInFromBottom {
    from {
        opacity: 0;
        transform: translateY(2rem);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInFromRight {
    from {
        opacity: 0;
        transform: translateX(2rem);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>
