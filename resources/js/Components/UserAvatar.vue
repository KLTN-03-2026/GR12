<script setup>
defineProps({
    user: {
        type: Object,
        default: () => ({ name: "User" }),
    },
    size: {
        type: String,
        default: "md", // xs, sm, md, lg, xl
    },
    rounded: {
        type: String,
        default: "lg", // sm, md, lg, xl, full
    },
    showBorder: {
        type: Boolean,
        default: false,
    },
    clickable: {
        type: Boolean,
        default: false,
    },
});

const getSizeClass = (size) => {
    const sizes = {
        xs: "h-6 w-6 text-xs",
        sm: "h-8 w-8 text-sm",
        md: "h-12 w-12 text-base",
        lg: "h-14 w-14 text-lg",
        xl: "h-16 w-16 text-xl",
    };
    return sizes[size] || sizes.md;
};

const getRoundedClass = (rounded) => {
    const sizes = {
        sm: "rounded-md",
        md: "rounded-lg",
        lg: "rounded-2xl",
        xl: "rounded-3xl",
        full: "rounded-full",
    };
    return sizes[rounded] || sizes.lg;
};
</script>

<template>
    <div
        :class="[
            getSizeClass(size),
            getRoundedClass(rounded),
            'flex items-center justify-center font-black uppercase flex-shrink-0 transition-all duration-300',
            showBorder ? 'border-2 border-white shadow-lg' : '',
            clickable ? 'cursor-pointer hover:shadow-xl' : '',
        ]"
        :style="{
            backgroundColor: generateColor(user.name),
        }"
    >
        <!-- Hiển thị ảnh nếu có -->
        <img
            v-if="user?.avatar && user.avatar !== ''"
            :src="
                user.avatar.startsWith('/')
                    ? '/storage/' + user.avatar
                    : user.avatar
            "
            :alt="user?.name || 'User'"
            class="w-full h-full object-cover"
            :class="getRoundedClass(rounded)"
        />
        <!-- Fallback: ký tự đầu tiên -->
        <span
            v-else
            class="text-white tracking-tight"
            :style="{ fontSize: 'inherit', lineHeight: 'inherit' }"
        >
            {{ (user?.name || "User").charAt(0).toUpperCase() }}
        </span>
    </div>
</template>

<script>
function generateColor(name = "User") {
    const colors = [
        "#FF6B6B", // Đỏ
        "#4ECDC4", // Cyan
        "#45B7D1", // Xanh dương
        "#FFA07A", // Cam nhạt
        "#98D8C8", // Xanh lá nhạt
        "#F7DC6F", // Vàng
        "#BB8FCE", // Tím
        "#85C1E2", // Xanh biển
        "#F8B195", // Cam
        "#FF6B9D", // Hồng
    ];

    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return colors[Math.abs(hash) % colors.length];
}
</script>

<style scoped>
/* Animation cho hover effect */
.hover\:shadow-xl {
    transition: box-shadow 0.3s ease;
}
</style>
