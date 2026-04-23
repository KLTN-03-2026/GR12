<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import ToastList from "@/Components/ToastList.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import { computed, ref } from "vue";

const page = usePage();
const sidebarOpen = ref(false);
const currentMenuIndex = ref(-1);

const currentComponent = computed(() => page.component);

// Menu items with proper icons
const menuItems = [
    {
        name: "TỔNG QUAN",
        href: "restaurant.dashboard",
        icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
        </svg>`,
        component: "Restaurant/Dashboard",
    },
    {
        name: "MÓN ĂN CỦA TÔI",
        href: "restaurant.products.index",
        icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </svg>`,
        component: "Restaurant/Products",
    },
    {
        name: "ĐƠN HÀNG",
        href: "restaurant.orders.index",
        icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
        </svg>`,
        component: "Restaurant/Orders/Index",
        badge: computed(() =>
            page.props.newOrdersCount > 0 ? page.props.newOrdersCount : null,
        ),
    },
    {
        name: "ĐÁNH GIÁ",
        href: "restaurant.reviews.index",
        icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
        </svg>`,
        component: "Restaurant/Reviews/Index",
    },
    {
        name: "THÔNG TIN TÀI KHOẢN",
        href: "restaurant.profile.edit",
        icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>`,
        component: "Restaurant/Profile/Edit",
    },
];

// Keyboard navigation
const handleKeyboardNavigation = (e, index) => {
    if (e.key === "ArrowDown") {
        e.preventDefault();
        currentMenuIndex.value = Math.min(index + 1, menuItems.length - 1);
    } else if (e.key === "ArrowUp") {
        e.preventDefault();
        currentMenuIndex.value = Math.max(index - 1, 0);
    }
};

// Get page title for mobile header
const getPageTitle = () => {
    const current = menuItems.find(
        (item) =>
            currentComponent.value === item.component ||
            (item.component !== "Restaurant/Dashboard" &&
                currentComponent.value &&
                currentComponent.value.startsWith(item.component)),
    );
    return current ? current.name : "FoodXpress";
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex overflow-hidden">
        <ToastList />

        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden"
        ></div>

        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 w-72 bg-gradient-to-b from-orange-500 to-orange-600 shadow-2xl transform transition-all duration-300 ease-in-out lg:translate-x-0 lg:relative flex-shrink-0 flex flex-col h-screen',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <div
                class="flex items-center justify-between p-8 border-b border-white border-opacity-20"
            >
                <Link
                    :href="route('restaurant.dashboard')"
                    class="flex flex-col"
                >
                    <span
                        class="text-2xl font-black text-white italic tracking-tighter"
                        >FoodXpress</span
                    >
                    <span
                        class="text-[10px] font-bold text-white text-opacity-80 uppercase tracking-[0.2em]"
                        >Restaurant Hub</span
                    >
                </Link>
                <button
                    @click="sidebarOpen = false"
                    class="lg:hidden p-2 text-white"
                >
                    <svg
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path d="M6 18L18 6M6 6l12 12" stroke-width="2"></path>
                    </svg>
                </button>
            </div>

            <nav
                class="flex-1 px-4 py-6 space-y-2 overflow-y-auto custom-scrollbar-sidebar"
            >
                <Link
                    v-for="(item, index) in menuItems"
                    :key="item.name"
                    :href="route(item.href)"
                    :class="[
                        'flex items-center gap-4 px-6 py-4 rounded-2xl font-semibold text-sm transition-all duration-300 group shadow-sm',
                        currentComponent === item.component ||
                        (item.component !== 'Restaurant/Dashboard' &&
                            currentComponent?.startsWith(item.component))
                            ? 'bg-white text-orange-600 shadow-xl scale-105 translate-x-2'
                            : 'text-white hover:bg-white hover:bg-opacity-10',
                    ]"
                >
                    <span class="text-xl" v-html="item.icon"></span>
                    <span class="flex-1">{{ item.name }}</span>
                    <span
                        v-if="item.badge"
                        class="bg-red-500 text-white text-[10px] px-2 py-1 rounded-full animate-pulse"
                        >{{ item.badge }}</span
                    >
                </Link>
            </nav>

            <div
                class="p-4 border-t border-white border-opacity-20 bg-black bg-opacity-10"
            >
                <div
                    class="flex items-center gap-3 mb-4 p-3 bg-white rounded-2xl shadow-inner"
                >
                    <UserAvatar
                        :user="$page.props.auth.user"
                        size="sm"
                        rounded="lg"
                    />
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-black text-gray-900 truncate">
                            {{
                                $page.props.auth.user.restaurant_name ||
                                "Chủ quán"
                            }}
                        </p>
                        <p
                            class="text-[9px] text-gray-500 truncate font-bold uppercase tracking-tighter"
                        >
                            {{ $page.props.auth.user.email }}
                        </p>
                    </div>
                </div>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-500 text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-red-600 transition-all shadow-lg"
                    >ĐĂNG XUẤT</Link
                >
            </div>
        </aside>

        <div class="flex-1 flex flex-col h-screen overflow-hidden">
            <header
                class="lg:hidden bg-white shadow-sm border-b border-gray-100 px-6 py-4 flex-shrink-0"
            >
                <div class="flex items-center justify-between">
                    <button
                        @click="sidebarOpen = true"
                        class="p-2 rounded-xl bg-orange-50 text-orange-500"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M4 6h16M4 12h16M4 18h16"
                                stroke-width="2.5"
                                stroke-linecap="round"
                            ></path>
                        </svg>
                    </button>
                    <h1
                        class="text-sm font-black text-gray-900 uppercase tracking-widest"
                    >
                        {{ getPageTitle() }}
                    </h1>
                    <div class="w-10"></div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6 lg:p-10 bg-[#fdf2f0]/50">
                <div class="max-w-7xl mx-auto">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
<style scoped>
* {
    font-family: "Inter", sans-serif;
}

/* Enhanced scrollbar */
aside::-webkit-scrollbar {
    width: 6px;
}

aside::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}

aside::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 10px;
}

aside::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

/* Smooth transitions */
aside {
    transition:
        transform 0.3s ease-in-out,
        box-shadow 0.3s ease-in-out;
}

/* Mobile responsive adjustments */
@media (max-width: 1024px) {
    aside {
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.2);
    }

    /* Increase mobile header padding */
    header {
        padding: 1rem;
    }

    /* Better spacing for main content on mobile */
    main {
        padding: 1.5rem;
    }
}
</style>
