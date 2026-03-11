<template>
    <div class="flex items-center justify-center py-12">
        <div class="flex flex-col items-center gap-3">
            <div class="relative w-10 h-10">
                <div class="absolute inset-0 rounded-full border-2 border-amber-200"></div>
                <div class="absolute inset-0 rounded-full border-2 border-amber-600 border-t-transparent animate-spin"></div>
            </div>
            <p v-if="text" class="text-sm text-gray-500">{{ text }}</p>
        </div>
    </div>
</template>

<script setup>
defineProps({
    text: { type: String, default: '' },
});
</script>
