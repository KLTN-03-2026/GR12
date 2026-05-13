<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import ToastList from "@/Components/ToastList.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import { computed, ref, onMounted, onUnmounted } from "vue";
import Swal from "sweetalert2";

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
        name: "LỊCH SỬ ĐƠN",
        href: "restaurant.orders.history",
        icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>`,
        component: "Restaurant/Orders/History",
    },
    {
        name: "MÃ GIẢM GIÁ (KHUYẾN MÃI)",
        href: "restaurant.vouchers.index",
        icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
        </svg>`,
        component: "Restaurant/Vouchers",
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
    {
        name: "VÍ ĐIỆN TỬ",
        href: "restaurant.wallet",
        icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
        </svg>`,
        component: "Restaurant/Wallet",
    },
    {
        name: "THÔNG BÁO",
        href: "my.notifications",
        icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>`,
        component: "Notifications",
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

// Real-time Notification Sound using Web Audio API (FoodXpress Exclusive Melody)
const playNotificationSound = () => {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        
        // Helper to play a note with a bell-like envelope
        const playNote = (freq, type, startTime, duration, vol = 0.5) => {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = type;
            osc.frequency.setValueAtTime(freq, startTime);
            
            // Envelope (ADSR) for a plucky/bell-like sound
            gain.gain.setValueAtTime(0, startTime);
            gain.gain.linearRampToValueAtTime(vol, startTime + 0.02); // Fast attack
            gain.gain.exponentialRampToValueAtTime(vol * 0.3, startTime + 0.1); // Decay
            gain.gain.exponentialRampToValueAtTime(0.001, startTime + duration); // Release
            
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            
            osc.start(startTime);
            osc.stop(startTime + duration);
        };

        const now = audioCtx.currentTime;
        
        // Melody sequence: C5 -> F5 -> A5 -> C6 (Upbeat ascending arpeggio)
        const notes = [
            { f: 523.25, t: now },         // C5
            { f: 698.46, t: now + 0.15 },  // F5
            { f: 880.00, t: now + 0.30 },  // A5
            { f: 1046.50, t: now + 0.45 }  // C6
        ];

        // Play the arpeggio (combining triangle and sine for a richer bell tone)
        notes.forEach(note => {
            playNote(note.f, 'triangle', note.t, 0.5, 0.6);
            playNote(note.f * 2, 'sine', note.t, 0.5, 0.2); // subtle overtone
        });

        // The final "Chime" chord (F Major chord) played together at the end
        const finalTime = now + 0.65;
        playNote(698.46, 'triangle', finalTime, 1.5, 0.5);  // F5
        playNote(880.00, 'triangle', finalTime, 1.5, 0.4);  // A5
        playNote(1046.50, 'triangle', finalTime, 1.5, 0.3); // C6
        playNote(1396.91, 'sine', finalTime, 1.5, 0.2);     // F6 (sparkle)

    } catch (e) {
        console.warn("Could not play sound", e);
    }
};

let alarmInterval = null;

const startAlarm = () => {
    if (alarmInterval) clearInterval(alarmInterval);
    const trigger = () => {
        playNotificationSound();
        setTimeout(() => {
            const msg = new SpeechSynthesisUtterance("Bạn có đơn hàng mới từ Phút Ít Rét");
            msg.lang = 'vi-VN';
            window.speechSynthesis.speak(msg);
        }, 1000);
    };
    trigger();
    alarmInterval = setInterval(trigger, 6000);
};

const stopAlarm = () => {
    if (alarmInterval) clearInterval(alarmInterval);
    window.speechSynthesis.cancel();
};

onMounted(() => {
    if (window.Echo && page.props.auth?.user?.id) {
        window.Echo.private(`restaurant.${page.props.auth.user.id}`)
            .listen('NewOrderReceived', (e) => {
                console.log('New order received:', e);
                startAlarm();
                
                Swal.fire({
                    title: '🔔 ĐƠN HÀNG MỚI!',
                    text: `Bạn vừa nhận được đơn hàng ${e.order.order_code} trị giá ${new Intl.NumberFormat('vi-VN').format(e.order.total)}đ`,
                    icon: 'success',
                    showConfirmButton: true,
                    confirmButtonText: 'Xem ngay',
                    confirmButtonColor: '#f97316',
                    timer: 10000,
                    timerProgressBar: true,
                    customClass: { popup: "rounded-[2rem]" },
                    willClose: () => {
                        stopAlarm();
                    }
                }).then((result) => {
                    stopAlarm();
                    if (result.isConfirmed) {
                        window.location.href = route('restaurant.orders.index');
                    }
                });
                
                // Nếu đang ở trang danh sách đơn, có thể tải lại trang
                if (currentComponent.value === 'Restaurant/Orders/Index') {
                    // window.location.reload(); 
                }
            });
    }
});

onUnmounted(() => {
    stopAlarm();
    if (window.Echo && page.props.auth?.user?.id) {
        window.Echo.leave(`restaurant.${page.props.auth.user.id}`);
    }
});
</script>

<template>
    <div class="min-h-screen bg-[#f4f7f9] text-slate-800 flex overflow-hidden font-sans selection:bg-orange-200">
        <!-- Background Pattern -->
        <div class="absolute inset-0 z-0 pointer-events-none opacity-40 mix-blend-multiply" style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 32px 32px;"></div>
        
        <ToastList class="z-[100]" />

        <!-- Mobile Backdrop -->
        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden transition-opacity"
        ></div>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 w-[280px] bg-white shadow-[10px_0_40px_rgba(0,0,0,0.04)] transform transition-transform duration-400 ease-[cubic-bezier(0.16,1,0.3,1)] lg:translate-x-0 lg:relative flex-shrink-0 flex flex-col h-screen border-r border-slate-100',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <div class="flex items-center justify-between p-8">
                <Link
                    :href="route('restaurant.dashboard')"
                    class="flex flex-col relative group"
                >
                    <span class="absolute -inset-4 bg-orange-50 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity z-0"></span>
                    <div class="relative z-10">
                        <span class="text-3xl font-black text-slate-900 italic tracking-tighter">
                            Food<span class="text-orange-500">Xpress</span>
                        </span>
                        <span class="block mt-1 text-[9px] font-black text-slate-400 uppercase tracking-[0.25em]">
                            Restaurant Hub
                        </span>
                    </div>
                </Link>
                <button
                    @click="sidebarOpen = false"
                    class="lg:hidden p-2 text-slate-400 hover:text-orange-500 bg-slate-50 hover:bg-orange-50 rounded-xl transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" stroke-width="2"></path>
                    </svg>
                </button>
            </div>

            <nav class="flex-1 px-4 py-2 space-y-1 overflow-y-auto custom-scrollbar-sidebar">
                <p class="px-6 mb-4 text-[10px] font-black text-slate-300 uppercase tracking-widest">Menu</p>
                <Link
                    v-for="(item, index) in menuItems"
                    :key="item.name"
                    :href="route(item.href)"
                    :class="[
                        'flex items-center gap-4 px-6 py-4 rounded-2xl font-bold text-sm transition-all duration-300 group relative overflow-hidden',
                        currentComponent === item.component ||
                        (item.component !== 'Restaurant/Dashboard' &&
                            currentComponent?.startsWith(item.component))
                            ? 'text-orange-600 bg-orange-50/80 shadow-sm border border-orange-100/50'
                            : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900',
                    ]"
                >
                    <div 
                        v-if="currentComponent === item.component || (item.component !== 'Restaurant/Dashboard' && currentComponent?.startsWith(item.component))"
                        class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-orange-500 rounded-r-full shadow-[0_0_10px_rgba(249,115,22,0.4)]"
                    ></div>
                    <span 
                        class="text-[22px] transition-transform duration-300 group-hover:scale-110" 
                        :class="currentComponent === item.component || (item.component !== 'Restaurant/Dashboard' && currentComponent?.startsWith(item.component)) ? 'text-orange-500' : 'text-slate-400'"
                        v-html="item.icon"
                    ></span>
                    <span class="flex-1 tracking-wide">{{ item.name }}</span>
                    <span
                        v-if="item.badge"
                        class="bg-red-500 text-white text-[10px] px-2.5 py-0.5 rounded-full font-black shadow-lg shadow-red-500/30 animate-pulse"
                    >{{ item.badge }}</span>
                </Link>
            </nav>

            <div class="p-6">
                <div class="p-5 bg-slate-50 border border-slate-100 rounded-3xl relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-orange-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-5">
                            <UserAvatar
                                :user="$page.props.auth.user"
                                size="md"
                                rounded="xl"
                                class="ring-2 ring-white shadow-sm"
                            />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-black text-slate-900 truncate">
                                    {{ $page.props.auth.user.restaurant_name || "Chủ quán" }}
                                </p>
                                <div class="mt-1">
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Ví của quán</p>
                                    <p class="text-xs font-black text-emerald-500">
                                        {{ new Intl.NumberFormat('vi-VN').format($page.props.auth.user.wallet_balance || 0) }}<span class="text-[10px] text-emerald-400">đ</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3.5 bg-white border border-slate-200 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all duration-300 shadow-sm"
                        >
                            ĐĂNG XUẤT
                        </Link>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col h-screen overflow-hidden relative z-10">
            <!-- Mobile Header -->
            <header class="lg:hidden bg-white/80 backdrop-blur-md shadow-sm border-b border-slate-100 px-4 py-4 flex-shrink-0 sticky top-0 z-30">
                <div class="flex items-center justify-between">
                    <button
                        @click="sidebarOpen = true"
                        class="p-2.5 rounded-2xl bg-white border border-slate-100 text-slate-600 shadow-sm active:scale-95 transition-transform"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <h1 class="text-sm font-black text-slate-900 uppercase tracking-[0.15em]">
                        {{ getPageTitle() }}
                    </h1>
                    <div class="w-10">
                        <UserAvatar v-if="$page.props.auth.user" :user="$page.props.auth.user" size="sm" rounded="lg" />
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-8 lg:p-10 scroll-smooth">
                <div class="max-w-7xl mx-auto animate-fade-in-up">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,600;1,800&display=swap');

* {
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.custom-scrollbar-sidebar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar-sidebar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar-sidebar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}

.custom-scrollbar-sidebar:hover::-webkit-scrollbar-thumb {
    background: #cbd5e1;
}

/* Base animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

/* Glassmorphism utilities */
.glass {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}
</style>
