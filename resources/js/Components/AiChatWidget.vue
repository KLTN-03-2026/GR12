<script setup>
import { ref, nextTick } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const isOpen = ref(false);
const messages = ref([
    { role: 'ai', content: 'Xin chào! Mình là FoodXpress AI. Bạn cần tìm món gì hay có thắc mắc gì không?' }
]);
const inputMessage = ref('');
const isTyping = ref(false);
const chatContainer = ref(null);

const toggleChat = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        scrollToBottom();
    }
};

const scrollToBottom = async () => {
    await nextTick();
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

const sendMessage = async () => {
    if (!inputMessage.value.trim() || isTyping.value) return;

    const userMsg = inputMessage.value.trim();
    messages.value.push({ role: 'user', content: userMsg });
    inputMessage.value = '';
    isTyping.value = true;
    scrollToBottom();

    try {
        // Chuẩn bị mảng history, bỏ qua tin nhắn chào mừng đầu tiên (tuỳ chọn)
        const history = messages.value.slice(1, -1).map(m => ({
            role: m.role,
            content: m.content
        }));

        const response = await axios.post('/api/chatbot', { 
            message: userMsg,
            history: history
        });

        if (response.data && response.data.success) {
            messages.value.push({ role: 'ai', content: response.data.reply });
            
            // Xử lý nếu AI đã thêm vào giỏ hàng
            if (response.data.cart_updated) {
                // Kích hoạt reload data từ Inertia để cập nhật số lượng giỏ hàng
                router.reload({ only: ['cartCount', 'auth'] });
            }
        } else {
            messages.value.push({ role: 'ai', content: response.data?.message || 'Xin lỗi, có lỗi xảy ra. Bạn thử lại nhé!' });
        }
    } catch (error) {
        console.error('Chat error:', error);
        const errorMsg = error.response?.data?.message || 'Không thể kết nối đến máy chủ AI. Vui lòng thử lại sau.';
        messages.value.push({ role: 'ai', content: errorMsg });
    } finally {
        isTyping.value = false;
        scrollToBottom();
    }
};

const formatMessage = (text) => {
    // Đơn giản hóa việc format: thay thế **text** thành <strong>text</strong> và \n thành <br>
    let formatted = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    formatted = formatted.replace(/\n/g, '<br/>');
    return formatted;
};
</script>

<template>
    <div class="fixed bottom-6 right-6 z-[100] font-sans">
        <!-- Floating Button -->
        <button 
            v-if="!isOpen"
            @click="toggleChat"
            class="w-14 h-14 bg-gradient-to-tr from-purple-600 to-indigo-500 rounded-full shadow-2xl flex items-center justify-center hover:scale-110 transition-transform duration-300 relative group animate-bounce"
        >
            <span class="text-2xl">🤖</span>
            <!-- Tooltip -->
            <div class="absolute right-full mr-4 bg-white text-indigo-900 text-xs font-black px-3 py-2 rounded-xl shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">
                Chat với FoodXpress AI
                <div class="absolute top-1/2 -right-1 w-2 h-2 bg-white transform rotate-45 -translate-y-1/2"></div>
            </div>
        </button>

        <!-- Chat Window -->
        <div 
            v-if="isOpen"
            class="w-[350px] max-w-[calc(100vw-2rem)] h-[500px] max-h-[80vh] bg-white rounded-3xl shadow-2xl flex flex-col overflow-hidden animate-slide-up border border-indigo-100"
        >
            <!-- Header -->
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-4 text-white flex justify-between items-center shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center text-xl">
                        🤖
                    </div>
                    <div>
                        <h3 class="font-black text-sm tracking-widest uppercase">FoodXpress AI</h3>
                        <p class="text-[10px] text-indigo-100 font-medium flex items-center gap-1">
                            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                            Luôn sẵn sàng
                        </p>
                    </div>
                </div>
                <button @click="toggleChat" class="w-8 h-8 flex items-center justify-center bg-white/10 hover:bg-white/20 rounded-full transition-colors">
                    ✕
                </button>
            </div>

            <!-- Messages Area -->
            <div 
                ref="chatContainer"
                class="flex-1 overflow-y-auto p-4 space-y-4 bg-gradient-to-b from-indigo-50/50 to-white"
            >
                <div 
                    v-for="(msg, index) in messages" 
                    :key="index"
                    class="flex flex-col max-w-[85%]"
                    :class="msg.role === 'user' ? 'ml-auto items-end' : 'mr-auto items-start'"
                >
                    <span v-if="msg.role === 'ai' && index === 0" class="text-[10px] text-gray-400 mb-1 ml-1 font-bold">FoodXpress AI</span>
                    <div 
                        class="p-3 rounded-2xl text-sm leading-relaxed"
                        :class="msg.role === 'user' 
                            ? 'bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-br-sm shadow-md' 
                            : 'bg-white border border-gray-100 text-gray-700 rounded-bl-sm shadow-sm'"
                    >
                        <div v-if="msg.role === 'user'" v-html="formatMessage(msg.content)"></div>
                        <div v-else class="markdown-body" v-html="formatMessage(msg.content)"></div>
                    </div>
                </div>

                <!-- Typing indicator -->
                <div v-if="isTyping" class="flex flex-col max-w-[85%] mr-auto items-start">
                    <div class="p-3 bg-white border border-gray-100 rounded-2xl rounded-bl-sm shadow-sm flex gap-1 items-center">
                        <div class="w-2 h-2 bg-indigo-300 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="p-3 bg-white border-t border-gray-100 shrink-0">
                <form @submit.prevent="sendMessage" class="flex items-end gap-2 relative">
                    <textarea 
                        v-model="inputMessage"
                        @keydown.enter.prevent="sendMessage"
                        rows="1"
                        placeholder="Hỏi AI bất kỳ điều gì..."
                        class="w-full bg-gray-100 border-none rounded-2xl py-3 pl-4 pr-12 focus:ring-2 focus:ring-indigo-400 resize-none text-sm min-h-[44px] max-h-[120px]"
                    ></textarea>
                    <button 
                        type="submit"
                        :disabled="!inputMessage.trim() || isTyping"
                        class="absolute right-1 bottom-1 w-9 h-9 flex items-center justify-center bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 disabled:opacity-50 transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform rotate-45 -ml-1 -mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.animate-slide-up {
    animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    transform-origin: bottom right;
}
.markdown-body strong {
    font-weight: 900;
    color: #4338ca;
}
textarea::-webkit-scrollbar {
    width: 4px;
}
textarea::-webkit-scrollbar-track {
    background: transparent;
}
textarea::-webkit-scrollbar-thumb {
    background: #c7d2fe;
    border-radius: 4px;
}
</style>
