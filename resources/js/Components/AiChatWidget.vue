<script setup>
import { ref, watch, nextTick, onMounted } from 'vue';
import axios from 'axios';
import { router, usePage } from '@inertiajs/vue3';
import { useDraggableWidget } from '@/Composables/useDraggableWidget';

const isOpen = ref(false);
const messages = ref([]);
const userInput = ref('');
const isLoading = ref(false);
const chatContainer = ref(null);
const inputRef = ref(null); // Để tự động focus
const page = usePage();

const {
    position,
    isDragging,
    hasMoved,
    widgetStyle,
    widgetClasses,
    startDrag
} = useDraggableWidget('bottom-6 right-6');

const suggestedQuestions = [
    "📦 Đơn hàng của mình đang ở đâu?",
    "🎟️ Hôm nay có mã giảm giá nào không?",
    "🤤 Gợi ý cho mình món nào ngon ngon đi!",
    "🍜 Tìm giúp mình món bún bò",
    "🚚 Phí ship tính thế nào vậy?"
];

const useSuggestion = (text) => {
    userInput.value = text;
    sendMessage();
};

const handleToggleChat = async (e) => {
    if (hasMoved.value) {
        if (e) e.preventDefault();
        return;
    }
    await toggleChat();
};

const toggleChat = async () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value && messages.value.length === 0) {
        messages.value.push({ role: 'ai', content: `Xin chào ${page.props.auth.user?.name || 'bạn'}! Mình là FoodXpress AI. Bạn muốn ăn gì hôm nay, hay cần kiểm tra đơn hàng cứ nói mình nhé! 👋` });
    }
    scrollToBottom();
    
    // Tự động focus vào ô nhập liệu khi mở chat
    if (isOpen.value) {
        await nextTick();
        if (inputRef.value) inputRef.value.focus();
    }
};

const scrollToBottom = async () => {
    await nextTick();
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

const sendMessage = async () => {
    if (!userInput.value.trim() || isLoading.value) return;

    const messageText = userInput.value;
    messages.value.push({ role: 'user', content: messageText });
    userInput.value = '';
    
    // MÃ CHEAT (DEBUG) ĐỂ TEST GIAO DIỆN TRACKING KHÔNG CẦN GỌI AI
    if (messageText.trim() === 'DEBUG_TRACKING') {
        setTimeout(() => {
            messages.value.push({
                role: 'ai',
                content: 'Đây là bản Demo giao diện Theo dõi Đơn hàng (Live Tracking Widget) của bạn nhé:',
                widget: {
                    type: 'tracking',
                    order: {
                        id: 1,
                        order_code: 'ORD-FX999888',
                        status: 'đang giao',
                        total: 255000,
                        shipper_name: 'Trần Văn Fast'
                    }
                }
            });
            scrollToBottom();
        }, 600);
        return;
    }

    isLoading.value = true;
    scrollToBottom();

    // Lọc lịch sử (chỉ lấy 5 tin nhắn gần nhất để tránh payload lớn)
    const history = messages.value.slice(0, -1).slice(-5).map(m => ({
        role: m.role,
        content: m.content
    }));

    try {
        const response = await axios.post('/api/chatbot', {
            message: messageText,
            history: history,
            context: window.location.pathname // Gửi ngữ cảnh trang hiện tại
        });

        if (response.data.success) {
            messages.value.push({ 
                role: 'ai', 
                content: response.data.reply,
                widget: response.data.widget_data
            });
            
            // Nếu AI cập nhật giỏ hàng, reload page để component Navbar tự render lại
            if (response.data.cart_updated) {
                router.reload({ preserveScroll: true });
                showToast('Giỏ hàng đã được cập nhật! 🛒');
            }
        }
    } catch (error) {
        console.error("Chatbot Error:", error);
        messages.value.push({ role: 'ai', content: error.response?.data?.message || 'Xin lỗi, hệ thống đang bận. Vui lòng thử lại sau.' });
    } finally {
        isLoading.value = false;
        scrollToBottom();
    }
};

// Web Speech API cho tính năng Giong Nói
const isListening = ref(false);
const startListening = () => {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        showToast('Trình duyệt của bạn không hỗ trợ nhận diện giọng nói!');
        return;
    }

    const recognition = new SpeechRecognition();
    recognition.lang = 'vi-VN';
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    recognition.onstart = () => {
        isListening.value = true;
        showToast('Đang lắng nghe...');
    };

    recognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        userInput.value = transcript;
        sendMessage();
    };

    recognition.onerror = (event) => {
        console.error("Speech recognition error", event.error);
        isListening.value = false;
        showToast('Không nghe rõ, vui lòng thử lại.');
    };

    recognition.onend = () => {
        isListening.value = false;
    };

    recognition.start();
};

// Tính năng Đọc văn bản (Text-to-Speech)
const speakText = (text) => {
    if (!window.speechSynthesis) {
        showToast('Trình duyệt không hỗ trợ đọc giọng nói!');
        return;
    }
    
    window.speechSynthesis.cancel(); // Dừng nếu đang đọc câu khác
    
    // Xóa các thẻ markdown và HTML để đọc tự nhiên hơn
    let cleanText = text.replace(/!\[.*?\]\(.*?\)/g, ''); // Bỏ ảnh
    cleanText = cleanText.replace(/[*_#]/g, ''); // Bỏ in đậm, in nghiêng
    cleanText = cleanText.replace(/<[^>]*>?/gm, ''); // Bỏ thẻ HTML
    
    const utterance = new SpeechSynthesisUtterance(cleanText);
    utterance.lang = 'vi-VN';
    utterance.rate = 1.1; // Đọc nhanh hơn một chút cho tự nhiên
    
    window.speechSynthesis.speak(utterance);
};

// Simple Toast inside Chatbot
const toastMessage = ref('');
const showToast = (msg) => {
    toastMessage.value = msg;
    setTimeout(() => toastMessage.value = '', 3000);
};

// Parse Markdown/HTML simple for bold, images, and line breaks
const formatMessage = (text) => {
    if (!text) return '';
    let formatted = text;
    
    // Xử lý Markdown hình ảnh: ![alt](url)
    formatted = formatted.replace(/!\[([^\]]*)\]\((.*?)\)/g, '<img src="$2" alt="$1" class="w-full h-auto max-h-48 object-cover mt-2 mb-2 rounded-xl border border-slate-200 shadow-sm" />');
    
    // Xử lý in đậm: **text**
    formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    
    // Xử lý xuống dòng
    formatted = formatted.replace(/\n/g, '<br/>');
    
    return formatted;
};
</script>

<template>
    <div 
        class="draggable-widget-container"
        :class="[widgetClasses, 'font-sans']"
        :style="widgetStyle"
    >
        <!-- Toast Thông Báo -->
        <transition name="toast-fade">
            <div v-if="toastMessage" class="absolute bottom-full right-0 mb-4 bg-green-500 text-white px-4 py-2 rounded-xl shadow-lg whitespace-nowrap font-bold text-sm z-50">
                {{ toastMessage }}
            </div>
        </transition>

        <!-- Nút FAB -->
        <button 
            v-if="!isOpen" 
            @mousedown="startDrag"
            @touchstart="startDrag"
            @click="handleToggleChat" 
            class="relative group w-16 h-16 bg-gradient-to-tr from-orange-500 to-red-500 rounded-full shadow-2xl flex items-center justify-center transition-transform duration-300 z-50 cursor-move touch-none"
            :class="{ 'hover:scale-110': !isDragging }"
        >
            <div class="absolute inset-0 bg-orange-400 rounded-full animate-ping opacity-30"></div>
            <span class="text-3xl filter drop-shadow-md">🤖</span>
            <div class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white rounded-full"></div>
            <!-- Tooltip -->
            <div class="absolute -top-12 right-0 bg-slate-900 text-white text-xs font-bold px-3 py-1.5 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-xl pointer-events-none">
                Hỏi FoodXpress AI
                <div class="absolute -bottom-1 right-6 w-2 h-2 bg-slate-900 rotate-45"></div>
            </div>
        </button>

        <!-- Cửa Sổ Chat -->
        <transition name="chat-slide">
            <div v-if="isOpen" class="absolute bottom-0 right-0 w-[350px] md:w-[400px] h-[550px] max-h-[80vh] bg-white rounded-[2rem] shadow-[0_20px_50px_-12px_rgba(0,0,0,0.3)] border border-slate-100 flex flex-col overflow-hidden" style="transform-origin: bottom right;">
                <!-- Header -->
                <div 
                    @mousedown="startDrag"
                    @touchstart="startDrag"
                    class="bg-gradient-to-r from-orange-500 to-red-500 p-4 text-white flex justify-between items-center shadow-md z-10 cursor-move touch-none"
                >
                    <div class="flex items-center gap-3 pointer-events-none">
                        <div class="relative">
                            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center text-xl backdrop-blur-sm border border-white/30">🤖</div>
                            <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 border-2 border-orange-500 rounded-full"></div>
                        </div>
                        <div>
                            <h3 class="font-black text-lg tracking-tight leading-none">FoodXpress AI</h3>
                            <p class="text-[10px] uppercase font-bold text-orange-100 tracking-widest mt-1">Trợ lý ảo thông minh</p>
                        </div>
                    </div>
                    <button @click="handleToggleChat" class="w-8 h-8 flex items-center justify-center bg-white/20 hover:bg-white/30 rounded-full backdrop-blur-sm transition-colors text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Messages Area -->
                <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 bg-slate-50 space-y-4 scroll-smooth">
                    <div class="text-center mb-6">
                        <span class="inline-block bg-slate-200 text-slate-500 text-[10px] font-bold uppercase px-3 py-1 rounded-full">Bắt đầu trò chuyện</span>
                    </div>

                    <div v-for="(msg, idx) in messages" :key="idx" class="flex" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                        <div v-if="msg.role === 'ai'" class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-sm mr-2 shrink-0 self-end mb-1 border border-orange-200">🤖</div>
                        <div :class="[
                            'max-w-[85%] rounded-[1.5rem] p-3 shadow-sm text-sm relative group',
                            msg.role === 'user' 
                                ? 'bg-slate-900 text-white rounded-br-sm' 
                                : 'bg-white text-slate-800 rounded-bl-sm border border-slate-100'
                        ]">
                            <!-- Nút Đọc Giọng Nói (chỉ hiện cho tin nhắn của AI) -->
                            <button 
                                v-if="msg.role === 'ai'" 
                                @click="speakText(msg.content)" 
                                class="absolute -right-8 top-2 w-6 h-6 bg-slate-100 hover:bg-orange-100 text-slate-400 hover:text-orange-500 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all shadow-sm"
                                title="Đọc tin nhắn này"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5 10v4a2 2 0 002 2h2l4 4V4L9 8H7a2 2 0 00-2 2z"></path></svg>
                            </button>

                            <div class="prose prose-sm prose-slate leading-relaxed" v-html="formatMessage(msg.content)"></div>
                            
                            <!-- Mini Widget (Tracking) -->
                            <div v-if="msg.widget && msg.widget.type === 'tracking'" class="mt-3 p-3 bg-white border border-orange-200 rounded-xl shadow-[0_4px_10px_-4px_rgba(0,0,0,0.05)] w-[250px] max-w-full">
                                <div class="flex items-center gap-2 mb-2 pb-2 border-b border-orange-50">
                                    <span class="bg-gradient-to-tr from-orange-400 to-red-500 text-white p-1.5 rounded-lg shadow-sm text-sm">📦</span>
                                    <div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest leading-none">Mã Đơn Hàng</div>
                                        <div class="font-black text-sm text-slate-800 mt-0.5">{{ msg.widget.order.order_code }}</div>
                                    </div>
                                </div>
                                <div class="space-y-1.5 mb-3">
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-gray-500 font-medium">Trạng thái:</span>
                                        <span class="font-bold text-orange-600 uppercase bg-orange-50 px-2 py-0.5 rounded-md border border-orange-100">{{ msg.widget.order.status }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-gray-500 font-medium">Shipper:</span>
                                        <span class="font-bold text-slate-700 truncate max-w-[100px] text-right">{{ msg.widget.order.shipper_name }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-gray-500 font-medium">Tổng tiền:</span>
                                        <span class="font-bold text-red-500">{{ new Intl.NumberFormat('vi-VN').format(msg.widget.order.total) }}đ</span>
                                    </div>
                                </div>
                                <a :href="'/my-orders/' + msg.widget.order.id" class="flex items-center justify-center gap-1 w-full py-2 bg-slate-900 text-white font-bold text-xs text-center rounded-lg hover:bg-slate-800 transition-colors shadow-md">
                                    📍 Xem Bản Đồ Live
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Typing Indicator -->
                    <div v-if="isLoading" class="flex justify-start items-end">
                        <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-sm mr-2 shrink-0 border border-orange-200">🤖</div>
                        <div class="bg-white border border-slate-100 rounded-[1.5rem] rounded-bl-sm p-4 shadow-sm flex gap-1 items-center h-[42px]">
                            <div class="w-2 h-2 bg-slate-300 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                            <div class="w-2 h-2 bg-slate-300 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                            <div class="w-2 h-2 bg-slate-300 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                        </div>
                    </div>
                </div>

                <!-- Suggested Questions -->
                <div class="px-3 pb-3 pt-2 bg-white flex flex-wrap gap-2 border-t border-slate-50 max-h-[120px] overflow-y-auto custom-scrollbar">
                    <button 
                        v-for="(question, index) in suggestedQuestions" 
                        :key="index"
                        @click="useSuggestion(question)"
                        class="whitespace-nowrap px-3 py-1.5 bg-slate-50 hover:bg-orange-50 text-slate-600 hover:text-orange-600 rounded-full text-xs font-medium transition-colors border border-slate-200 shrink-0"
                    >
                        {{ question }}
                    </button>
                </div>

                <!-- Input Area -->
                <div class="p-3 bg-white border-t border-slate-100 flex items-end gap-2">
                    <div class="flex-1 bg-slate-100 rounded-3xl flex items-end border border-transparent focus-within:border-orange-300 focus-within:bg-white transition-colors overflow-hidden px-4 py-1">
                        <textarea 
                            ref="inputRef"
                            v-model="userInput" 
                            @keydown.enter.prevent="sendMessage"
                            rows="1"
                            class="w-full bg-transparent border-none focus:ring-0 resize-none max-h-24 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none"
                            placeholder="Nhập câu hỏi, gợi ý món..."
                        ></textarea>
                    </div>
                    
                    <!-- Nút Micro -->
                    <button 
                        @click="startListening"
                        :class="isListening ? 'bg-red-500 animate-pulse text-white' : 'bg-slate-200 text-slate-600 hover:bg-slate-300'"
                        class="w-12 h-12 rounded-full flex items-center justify-center transition-all shrink-0 shadow-sm"
                        title="Nói để chat"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path></svg>
                    </button>

                    <!-- Nút Gửi -->
                    <button 
                        @click="sendMessage"
                        :disabled="!userInput.trim() || isLoading"
                        class="w-12 h-12 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-full flex items-center justify-center shadow-lg hover:shadow-xl hover:scale-105 transition-all disabled:opacity-50 disabled:hover:scale-100 shrink-0"
                    >
                        <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.chat-slide-enter-active,
.chat-slide-leave-active {
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    transform-origin: bottom right;
}
.chat-slide-enter-from,
.chat-slide-leave-to {
    opacity: 0;
    transform: scale(0.5) translateY(20px);
}

.toast-fade-enter-active,
.toast-fade-leave-active {
    transition: all 0.3s ease;
}
.toast-fade-enter-from,
.toast-fade-leave-to {
    opacity: 0;
    transform: translateY(10px);
}

/* Tùy chỉnh scrollbar cho chat và phần gợi ý */
.overflow-y-auto::-webkit-scrollbar, .custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track, .custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb, .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
</style>
