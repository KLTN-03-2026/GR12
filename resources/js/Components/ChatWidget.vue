<template>
    <div 
        class="draggable-widget-container" 
        :class="widgetClasses"
        :style="widgetStyle"
    >
        <!-- Floating Action Button -->
        <button
            @mousedown="startDrag"
            @touchstart="startDrag"
            @click="handleToggleChat"
            class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 transition-transform hover:scale-110 touch-none cursor-move"
            :class="{ 'animate-bounce': unreadCount > 0 && !isOpen, 'active:scale-90': !isDragging }"
        >
            <span v-if="unreadCount > 0 && !isOpen" class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white shadow">
                {{ unreadCount }}
            </span>
            <svg v-if="!isOpen" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6 6 18M6 6l12 12"/>
            </svg>
        </button>

        <!-- Chat Window -->
        <transition name="slide-fade">
            <div
                v-if="isOpen"
                class="absolute bottom-16 left-0 flex h-[480px] max-h-[80vh] w-[90vw] sm:w-[380px] flex-col overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-200"
                style="transform-origin: bottom left;"
            >
                <!-- Header (also draggable) -->
                <div 
                    @mousedown="startDrag"
                    @touchstart="startDrag"
                    class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-3 cursor-move touch-none"
                >
                    <div class="flex items-center gap-2 pointer-events-none">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900">Kênh hỗ trợ đơn #{{ orderCode }}</h3>
                            <p class="text-[10px] font-bold text-emerald-500">Online</p>
                        </div>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-slate-50/50" ref="messagesContainer">
                    <div v-if="loading" class="flex justify-center p-4">
                        <div class="h-6 w-6 animate-spin rounded-full border-2 border-indigo-600 border-t-transparent"></div>
                    </div>
                    
                    <div v-else-if="messages.length === 0" class="flex h-full flex-col items-center justify-center text-slate-400">
                        <svg class="mb-2" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <p class="text-xs font-semibold">Chưa có tin nhắn nào.</p>
                        <p class="text-[10px]">Hãy gửi tin nhắn đầu tiên!</p>
                    </div>

                    <div
                        v-else
                        v-for="(msg, index) in messages"
                        :key="msg.id || index"
                        class="flex flex-col"
                        :class="msg.user_id === currentUser.id ? 'items-end' : 'items-start'"
                    >
                        <p v-if="shouldShowName(index)" class="text-[10px] font-bold text-slate-400 mb-1 px-1">
                            {{ getRoleLabel(msg.user?.role) }} {{ msg.user?.name }}
                        </p>
                        <div
                            class="max-w-[85%] rounded-2xl px-3 py-2 text-sm shadow-sm"
                            :class="msg.user_id === currentUser.id ? 'bg-indigo-600 text-white rounded-tr-sm' : 'bg-white border border-slate-100 text-slate-800 rounded-tl-sm'"
                        >
                            <p v-if="msg.message" class="whitespace-pre-wrap leading-relaxed">{{ msg.message }}</p>
                            <a v-if="msg.image_path" :href="msg.image_path" target="_blank" class="block mt-1">
                                <img :src="msg.image_path" alt="Image" class="max-h-40 rounded-xl object-cover" />
                            </a>
                        </div>
                        <p class="text-[9px] font-medium text-slate-400 mt-1 px-1">
                            {{ formatTime(msg.created_at) }}
                        </p>
                    </div>
                </div>

                <!-- Premium Quick Suggestions (Only for Customer) -->
                <div 
                    v-if="currentUser.role === 'customer'" 
                    ref="suggestionsContainer"
                    @mousedown="onSuggestionMouseDown"
                    @mouseleave="onSuggestionMouseLeave"
                    @mouseup="onSuggestionMouseUp"
                    @mousemove="onSuggestionMouseMove"
                    class="px-3 pt-3 pb-3 flex gap-2.5 overflow-x-auto bg-slate-50/80 backdrop-blur-md border-t border-slate-100 custom-scrollbar-hide cursor-grab active:cursor-grabbing select-none"
                >
                    <button 
                        v-for="suggestion in [
                            { text: 'Bác tài ở đâu vậy?', icon: '📍' },
                            { text: 'Giao đến gọi tôi nhé!', icon: '📞' },
                            { text: 'Cho mình thêm đũa nhé!', icon: '🥢' },
                            { text: 'Đơn của mình xong chưa?', icon: '👨‍🍳' }
                        ]" 
                        :key="suggestion.text"
                        @click="!isDraggingSuggestion && sendQuickMessage(suggestion.text)"
                        class="group flex shrink-0 items-center gap-1.5 rounded-[1rem] bg-white px-3.5 py-2 text-[11px] font-black text-slate-600 shadow-[0_2px_8px_rgb(0,0,0,0.04)] border border-slate-100 transition-all duration-300 hover:border-indigo-200 hover:bg-gradient-to-r hover:from-indigo-500 hover:to-indigo-600 hover:text-white hover:shadow-[0_8px_20px_rgba(79,70,229,0.25)] hover:-translate-y-0.5 active:scale-95 active:translate-y-0"
                    >
                        <span class="text-sm transform group-hover:scale-125 transition-transform duration-300 origin-center">{{ suggestion.icon }}</span>
                        <span class="tracking-wide">{{ suggestion.text }}</span>
                    </button>
                </div>

                <!-- Input Area -->
                <div class="border-t border-slate-100 bg-white p-3 pb-safe">
                    <form @submit.prevent="sendMessage" class="flex items-end gap-2 relative">
                        <!-- Image Upload Preview -->
                        <div v-if="imagePreview" class="absolute bottom-full left-0 mb-2 rounded-xl border border-slate-200 bg-white p-1 shadow-lg">
                            <div class="relative">
                                <img :src="imagePreview" alt="Preview" class="h-20 w-20 rounded-lg object-cover" />
                                <button type="button" @click="removeImage" class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-slate-900 text-white shadow">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Image Input Trigger -->
                        <button type="button" @click="$refs.fileInput.click()" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-indigo-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                        </button>
                        <input type="file" ref="fileInput" accept="image/*" class="hidden" @change="handleImageSelected" />

                        <textarea
                            v-model="newMessage"
                            placeholder="Nhập tin nhắn..."
                            rows="1"
                            class="max-h-24 w-full resize-none rounded-2xl border-0 bg-slate-100 px-4 py-2.5 text-sm ring-0 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-600/20"
                            @keydown.enter.prevent="sendMessage"
                        ></textarea>

                        <button
                            type="submit"
                            :disabled="isSending || (!newMessage.trim() && !imageFile)"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-white transition hover:bg-indigo-700 disabled:opacity-50"
                        >
                            <svg v-if="!isSending" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" x2="11" y1="2" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            <svg v-else class="h-4 w-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useDraggableWidget } from "@/Composables/useDraggableWidget";

const props = defineProps({
    orderId: {
        type: Number,
        required: true,
    },
    orderCode: {
        type: String,
        required: true,
    },
});

const page = usePage();
const currentUser = page.props.auth.user;
const csrfFetch = window.csrfFetch;

const {
    position,
    isDragging,
    hasMoved,
    widgetStyle,
    widgetClasses,
    startDrag
} = useDraggableWidget('bottom-6 left-6');

const isOpen = ref(false);
const messages = ref([]);
const newMessage = ref("");
const imageFile = ref(null);
const imagePreview = ref(null);
const fileInput = ref(null);
const messagesContainer = ref(null);
const suggestionsContainer = ref(null);
const loading = ref(true);
const isSending = ref(false);
const unreadCount = ref(0);

// Drag to scroll logic for suggestions
let isDown = false;
let startX;
let scrollLeft;
let isDraggingSuggestion = false;

const onSuggestionMouseDown = (e) => {
    isDown = true;
    isDraggingSuggestion = false;
    startX = e.pageX - suggestionsContainer.value.offsetLeft;
    scrollLeft = suggestionsContainer.value.scrollLeft;
};

const onSuggestionMouseLeave = () => {
    isDown = false;
};

const onSuggestionMouseUp = () => {
    isDown = false;
    // Small delay to prevent click event if it was a drag
    setTimeout(() => { isDraggingSuggestion = false; }, 50);
};

const onSuggestionMouseMove = (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - suggestionsContainer.value.offsetLeft;
    const walk = (x - startX) * 2;
    if (Math.abs(walk) > 5) isDraggingSuggestion = true;
    suggestionsContainer.value.scrollLeft = scrollLeft - walk;
};

const handleToggleChat = (e) => {
    if (hasMoved.value) {
        e.preventDefault();
        return;
    }
    toggleChat();
};

const toggleChat = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        unreadCount.value = 0;
        scrollToBottom();
    }
};

const fetchMessages = async () => {
    try {
        const res = await csrfFetch(`/api/orders/${props.orderId}/chat`, {
            headers: {
                Accept: "application/json",
            },
        });
        if (res.ok) {
            messages.value = await res.json();
            if (isOpen.value) scrollToBottom();
        }
    } catch (error) {
        console.error("Failed to load chat history:", error);
    } finally {
        loading.value = false;
    }
};

const handleImageSelected = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    if (file.size > 5 * 1024 * 1024) {
        alert("Hình ảnh không được vượt quá 5MB.");
        return;
    }

    imageFile.value = file;
    const reader = new FileReader();
    reader.onload = (e) => {
        imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const removeImage = () => {
    imageFile.value = null;
    imagePreview.value = null;
    if (fileInput.value) fileInput.value.value = "";
};

const sendQuickMessage = (text) => {
    newMessage.value = text;
    sendMessage();
};

const sendMessage = async () => {
    if (!newMessage.value.trim() && !imageFile.value) return;
    if (isSending.value) return;

    isSending.value = true;
    const formData = new FormData();
    if (newMessage.value.trim()) formData.append("message", newMessage.value.trim());
    if (imageFile.value) formData.append("image", imageFile.value);

    // Optimistic UI update
    const tempMessage = {
        id: Date.now(),
        user_id: currentUser.id,
        user: currentUser,
        message: newMessage.value.trim(),
        image_path: imagePreview.value,
        created_at: new Date().toISOString(),
    };
    messages.value.push(tempMessage);
    
    newMessage.value = "";
    removeImage();
    scrollToBottom();

    try {
        const res = await csrfFetch(`/api/orders/${props.orderId}/chat`, {
            method: "POST",
            body: formData,
            // FormData does not need Content-Type header (browser sets it with boundary)
            headers: {
                Accept: "application/json",
            },
        });

        if (res.ok) {
            const savedMsg = await res.json();
            // Replace temp message with actual saved message
            const index = messages.value.findIndex(m => m.id === tempMessage.id);
            if (index !== -1) {
                messages.value[index] = savedMsg;
            }
        } else {
            console.error("Failed to send message");
            // Remove temp message if failed
             messages.value = messages.value.filter(m => m.id !== tempMessage.id);
             alert("Lỗi khi gửi tin nhắn.");
        }
    } catch (error) {
        console.error("Error sending message:", error);
         messages.value = messages.value.filter(m => m.id !== tempMessage.id);
    } finally {
        isSending.value = false;
    }
};

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const formatTime = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit" });
};

const getRoleLabel = (role) => {
    if (role === 'admin') return 'Quản trị viên';
    if (role === 'restaurant') return 'Nhà hàng';
    if (role === 'shipper') return 'Shipper';
    return 'Khách hàng';
};

const shouldShowName = (index) => {
    if (index === 0) return true;
    return messages.value[index].user_id !== messages.value[index - 1].user_id;
};

const playNotificationSound = () => {
    try {
        const audio = new Audio('/sounds/notification.mp3');
        audio.play().catch(e => console.warn(e));
    } catch (e) {
        // Ignore
    }
};

onMounted(() => {
    fetchMessages();

    if (window.Echo) {
        window.Echo.private(`order.${props.orderId}`)
            .listen("ChatMessageSent", (e) => {
                if (e.chatMessage.user_id !== currentUser.id) {
                    messages.value.push(e.chatMessage);
                    if (!isOpen.value) {
                        unreadCount.value++;
                        playNotificationSound();
                    } else {
                        scrollToBottom();
                    }
                }
            });
    }
});

onUnmounted(() => {
    if (window.Echo) {
        // We shouldn't leave the channel entirely because OrderDetail/Tracking might be using it for status/location
        window.Echo.private(`order.${props.orderId}`).stopListening("ChatMessageSent");
    }
});
</script>

<style scoped>
.slide-fade-enter-active {
    transition: all 0.3s ease-out;
}
.slide-fade-leave-active {
    transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: translateY(20px) scale(0.95);
    opacity: 0;
}
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom, 12px);
}
.custom-scrollbar-hide::-webkit-scrollbar {
    display: none !important;
    width: 0 !important;
    height: 0 !important;
}
.custom-scrollbar-hide {
    -ms-overflow-style: none !important;
    scrollbar-width: none !important;
}
</style>
