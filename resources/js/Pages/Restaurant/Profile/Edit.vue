<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import RestaurantLayout from '@/Layouts/RestaurantLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useToast } from 'vue-toastification';

const props = defineProps({
    user: Object,
    pendingRequests: Array,
});

const toast = useToast();

const hasPendingRequest = (type) => {
    return props.pendingRequests.some(req => req.type === type);
};

// --- Banking Info Form ---
const bankingForm = useForm({
    type: 'banking',
    bank_name: props.user.bank_name || '',
    bank_account: props.user.bank_account || '',
    bank_account_name: props.user.bank_account_name || '',
});

const submitBanking = () => {
    bankingForm.post(route('restaurant.profile.update'), {
        preserveScroll: true,
        onSuccess: () => toast.success('Đã cập nhật thông tin ngân hàng thành công!'),
    });
};

// --- Password Form ---
const passwordForm = useForm({
    type: 'password',
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

const submitPassword = () => {
    passwordForm.post(route('restaurant.profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Mật khẩu đã được đổi thành công!');
            passwordForm.reset();
        },
    });
};

// --- Location Form ---
const locationForm = useForm({
    type: 'location',
    restaurant_name: props.user.restaurant_name || '',
    address: props.user.address || '',
    address_detail: props.user.address_detail || '',
    latitude: props.user.latitude || '',
    longitude: props.user.longitude || '',
});

const submitLocation = () => {
    locationForm.post(route('restaurant.profile.update'), {
        preserveScroll: true,
        onSuccess: () => toast.success('Yêu cầu cập nhật thông tin cơ bản đã được gửi đến Admin!'),
    });
};

// --- Avatar Form ---
const avatarForm = useForm({
    type: 'avatar',
    avatar: null,
});
const avatarPreview = ref(null);

const handleAvatarChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        avatarForm.avatar = file;
        const reader = new FileReader();
        reader.onload = (e) => avatarPreview.value = e.target.result;
        reader.readAsDataURL(file);
    }
};

const submitAvatar = () => {
    avatarForm.post(route('restaurant.profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Yêu cầu cập nhật ảnh đại diện đã được gửi đến Admin!');
            avatarForm.reset('avatar');
        },
    });
};

</script>

<template>
    <RestaurantLayout>
        <Head title="Hồ sơ nhà hàng" />

        <div class="max-w-4xl mx-auto py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Hồ sơ nhà hàng</h1>
                <p class="text-slate-500 mt-2">Quản lý thông tin hiển thị, tài khoản ngân hàng và bảo mật.</p>
            </div>

            <div v-if="pendingRequests.length > 0" class="mb-8 p-5 bg-amber-50 border border-amber-200 rounded-2xl flex gap-4 items-start shadow-sm">
                <span class="text-amber-500 text-2xl mt-0.5">⏳</span>
                <div>
                    <h3 class="font-bold text-amber-800 text-lg">Bạn đang có yêu cầu chờ phê duyệt</h3>
                    <p class="text-amber-700 text-sm mt-1">
                        Một số thông tin hiển thị (như tên quán, địa chỉ, ảnh đại diện) cần được Admin duyệt trước khi hiển thị công khai.
                        Bạn đã gửi yêu cầu thay đổi cho các mục: 
                        <span class="font-bold">{{ pendingRequests.map(r => r.type === 'location' ? 'Thông tin cơ bản' : (r.type === 'avatar' ? 'Ảnh đại diện' : r.type)).join(', ') }}</span>.
                    </p>
                </div>
            </div>

            <div class="space-y-8">
                
                <!-- Banking Information -->
                <section class="bg-white rounded-[2rem] p-8 shadow-sm ring-1 ring-slate-200/50">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center text-2xl shadow-inner">
                            💳
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-800">Thông tin Ngân hàng</h2>
                            <p class="text-sm text-slate-500">Thông tin để nhận tiền hoàn doanh thu từ hệ thống.</p>
                        </div>
                    </div>

                    <form @submit.prevent="submitBanking" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="bank_name" value="Tên Ngân Hàng (VD: Vietcombank)" />
                                <TextInput id="bank_name" type="text" class="mt-2 block w-full rounded-xl" v-model="bankingForm.bank_name" required />
                                <InputError class="mt-2" :message="bankingForm.errors.bank_name" />
                            </div>
                            <div>
                                <InputLabel for="bank_account" value="Số Tài Khoản" />
                                <TextInput id="bank_account" type="text" class="mt-2 block w-full rounded-xl" v-model="bankingForm.bank_account" required />
                                <InputError class="mt-2" :message="bankingForm.errors.bank_account" />
                            </div>
                            <div class="md:col-span-2">
                                <InputLabel for="bank_account_name" value="Tên Chủ Tài Khoản" />
                                <TextInput id="bank_account_name" type="text" class="mt-2 block w-full rounded-xl" v-model="bankingForm.bank_account_name" required />
                                <InputError class="mt-2" :message="bankingForm.errors.bank_account_name" />
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100">
                            <PrimaryButton :disabled="bankingForm.processing" class="bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/20 px-8 py-3 rounded-xl">
                                Cập nhật ngay
                            </PrimaryButton>
                        </div>
                    </form>
                </section>

                <!-- Security / Password -->
                <section class="bg-white rounded-[2rem] p-8 shadow-sm ring-1 ring-slate-200/50">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center text-2xl shadow-inner">
                            🔒
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-800">Bảo mật tài khoản</h2>
                            <p class="text-sm text-slate-500">Đổi mật khẩu để giữ tài khoản của bạn luôn an toàn.</p>
                        </div>
                    </div>

                    <form @submit.prevent="submitPassword" class="space-y-6">
                        <div>
                            <InputLabel for="current_password" value="Mật khẩu hiện tại" />
                            <TextInput id="current_password" type="password" class="mt-2 block w-full md:w-1/2 rounded-xl" v-model="passwordForm.current_password" required />
                            <InputError class="mt-2" :message="passwordForm.errors.current_password" />
                        </div>
                        <div>
                            <InputLabel for="new_password" value="Mật khẩu mới" />
                            <TextInput id="new_password" type="password" class="mt-2 block w-full md:w-1/2 rounded-xl" v-model="passwordForm.new_password" required />
                            <InputError class="mt-2" :message="passwordForm.errors.new_password" />
                        </div>
                        <div>
                            <InputLabel for="new_password_confirmation" value="Xác nhận mật khẩu mới" />
                            <TextInput id="new_password_confirmation" type="password" class="mt-2 block w-full md:w-1/2 rounded-xl" v-model="passwordForm.new_password_confirmation" required />
                            <InputError class="mt-2" :message="passwordForm.errors.new_password_confirmation" />
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100">
                            <PrimaryButton :disabled="passwordForm.processing" class="bg-indigo-600 hover:bg-indigo-700 shadow-indigo-500/20 px-8 py-3 rounded-xl">
                                Đổi mật khẩu
                            </PrimaryButton>
                        </div>
                    </form>
                </section>

                <!-- Avatar Upload (Requires Approval) -->
                <section class="bg-white rounded-[2rem] p-8 shadow-sm ring-1 ring-slate-200/50">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center text-2xl shadow-inner">
                                🖼️
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-800">Ảnh đại diện quán</h2>
                                <p class="text-sm text-slate-500">Gửi yêu cầu đổi ảnh đại diện (cần Admin duyệt).</p>
                            </div>
                        </div>
                        <span v-if="hasPendingRequest('avatar')" class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold uppercase">Đang chờ duyệt</span>
                    </div>

                    <form @submit.prevent="submitAvatar" class="space-y-6">
                        <div class="flex items-center gap-6">
                            <div class="w-24 h-24 rounded-2xl bg-slate-100 overflow-hidden shrink-0 border border-slate-200 shadow-sm relative">
                                <img v-if="avatarPreview || user.avatar" :src="avatarPreview || (user.avatar.startsWith('http') ? user.avatar : '/storage/' + user.avatar)" class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-400 text-3xl">🏪</div>
                            </div>
                            <div class="flex-1">
                                <input type="file" @change="handleAvatarChange" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100 transition-colors cursor-pointer" />
                                <InputError class="mt-2" :message="avatarForm.errors.avatar" />
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100">
                            <PrimaryButton :disabled="avatarForm.processing || !avatarForm.avatar || hasPendingRequest('avatar')" class="bg-rose-600 hover:bg-rose-700 shadow-rose-500/20 px-8 py-3 rounded-xl disabled:opacity-50">
                                Gửi yêu cầu đổi ảnh
                            </PrimaryButton>
                        </div>
                    </form>
                </section>

                <!-- General Location (Requires Approval) -->
                <section class="bg-white rounded-[2rem] p-8 shadow-sm ring-1 ring-slate-200/50">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-sky-100 text-sky-600 rounded-xl flex items-center justify-center text-2xl shadow-inner">
                                📍
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-800">Thông tin cơ bản</h2>
                                <p class="text-sm text-slate-500">Tên quán, địa chỉ và tọa độ (cần Admin duyệt).</p>
                            </div>
                        </div>
                        <span v-if="hasPendingRequest('location')" class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold uppercase">Đang chờ duyệt</span>
                    </div>

                    <form @submit.prevent="submitLocation" class="space-y-6">
                        <div>
                            <InputLabel for="restaurant_name" value="Tên quán ăn" />
                            <TextInput id="restaurant_name" type="text" class="mt-2 block w-full rounded-xl" v-model="locationForm.restaurant_name" required />
                            <InputError class="mt-2" :message="locationForm.errors.restaurant_name" />
                        </div>
                        
                        <div>
                            <InputLabel for="address" value="Địa chỉ chính" />
                            <TextInput id="address" type="text" class="mt-2 block w-full rounded-xl" v-model="locationForm.address" required />
                            <InputError class="mt-2" :message="locationForm.errors.address" />
                        </div>

                        <div>
                            <InputLabel for="address_detail" value="Chi tiết địa chỉ (Ghi chú)" />
                            <TextInput id="address_detail" type="text" class="mt-2 block w-full rounded-xl" v-model="locationForm.address_detail" />
                            <InputError class="mt-2" :message="locationForm.errors.address_detail" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="latitude" value="Vĩ độ (Latitude)" />
                                <TextInput id="latitude" type="text" class="mt-2 block w-full rounded-xl" v-model="locationForm.latitude" required />
                                <InputError class="mt-2" :message="locationForm.errors.latitude" />
                            </div>
                            <div>
                                <InputLabel for="longitude" value="Kinh độ (Longitude)" />
                                <TextInput id="longitude" type="text" class="mt-2 block w-full rounded-xl" v-model="locationForm.longitude" required />
                                <InputError class="mt-2" :message="locationForm.errors.longitude" />
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100">
                            <PrimaryButton :disabled="locationForm.processing || hasPendingRequest('location')" class="bg-sky-600 hover:bg-sky-700 shadow-sky-500/20 px-8 py-3 rounded-xl disabled:opacity-50">
                                Gửi yêu cầu thay đổi
                            </PrimaryButton>
                        </div>
                    </form>
                </section>

            </div>
        </div>
    </RestaurantLayout>
</template>
