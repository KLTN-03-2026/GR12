<template>
    <div class="space-y-3">
        <label
            class="text-xs font-bold text-gray-400 uppercase tracking-widest"
        >
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <div
            class="relative h-64 bg-gradient-to-br from-orange-50 to-white rounded-[2rem] border-2 border-dashed border-orange-200 overflow-hidden group cursor-pointer hover:border-orange-400 hover:from-orange-100 transition-all"
        >
            <template v-if="previewUrl">
                <img :src="previewUrl" class="w-full h-full object-cover" />
                <div
                    class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all flex items-center justify-center"
                >
                    <button
                        type="button"
                        @click="clearImage"
                        class="bg-red-500 text-white p-3 rounded-full shadow-lg hover:bg-red-600 transform scale-90 group-hover:scale-100 transition-all"
                        aria-label="Remove image"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </template>

            <label
                v-else
                class="flex flex-col items-center justify-center h-full hover:bg-orange-100/50 transition cursor-pointer group"
            >
                <svg
                    class="w-12 text-orange-300 mb-3 group-hover:scale-110 group-hover:text-orange-400 transition-all"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                        stroke-width="2"
                    />
                </svg>
                <span class="text-xs font-bold text-orange-500 uppercase"
                    >Kéo thả hoặc bấm để chọn</span
                >
                <input
                    type="file"
                    :accept="accept"
                    @change="(e) => $emit('change', e.target.files[0])"
                    @drop.prevent="
                        (e) => $emit('change', e.dataTransfer.files[0])
                    "
                    @dragover.prevent
                    class="hidden"
                />
            </label>
        </div>

        <p v-if="error" class="text-red-500 text-xs font-semibold">
            {{ error }}
        </p>
    </div>
</template>

<script setup>
defineProps({
    label: String,
    error: String,
    required: Boolean,
    accept: { type: String, default: "image/*" },
    previewUrl: String,
});

const emit = defineEmits(["change"]);

const clearImage = () => {
    emit("change", null);
};
</script>
