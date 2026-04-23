<template>
    <!-- FormInput Component -->
    <div class="space-y-2">
        <label
            class="text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center gap-1"
        >
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <input
            :type="type"
            :class="[
                'w-full border-none bg-gray-50 rounded-2xl p-4 transition-all duration-200',
                'focus:ring-2 focus:ring-orange-500 focus:bg-white focus:shadow-md',
                error ? 'ring-2 ring-red-500 bg-red-50' : '',
            ]"
            v-bind="$attrs"
        />
        <p
            v-if="error"
            class="text-red-500 text-xs font-semibold flex items-center gap-1"
        >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path
                    fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                    clip-rule="evenodd"
                />
            </svg>
            {{ error }}
        </p>
        <p v-if="hint" class="text-xs text-gray-400 italic">{{ hint }}</p>
    </div>
</template>

<script setup>
defineProps({
    label: String,
    error: String,
    required: Boolean,
    hint: String,
    type: { type: String, default: "text" },
});
</script>
