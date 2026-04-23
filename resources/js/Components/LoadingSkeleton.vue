<template>
    <div
        :class="[
            'bg-gradient-to-r from-gray-100 to-gray-50 rounded-2xl',
            heightClass,
            'animate-pulse',
        ]"
    ></div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    height: { type: String, default: "h-12" },
});

const heightClass = computed(() => props.height);
</script>
