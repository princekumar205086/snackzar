<script setup>
import { useToast } from '@/composables/useToast';

const { toasts, dismiss } = useToast();
</script>

<template>
    <Teleport to="body">
        <div class="fixed bottom-20 lg:bottom-6 left-1/2 -translate-x-1/2 z-[100] flex flex-col items-center gap-2 pointer-events-none w-full max-w-xs px-4">
            <TransitionGroup
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 translate-y-4 scale-95"
                enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0 scale-100"
                leave-to-class="opacity-0 translate-y-2 scale-95"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-2xl shadow-xl border text-sm font-medium w-full"
                    :class="{
                        'bg-white border-green-200 text-green-800': toast.type === 'success',
                        'bg-white border-red-200 text-red-700': toast.type === 'error',
                        'bg-white border-amber-200 text-amber-800': toast.type === 'warning',
                        'bg-white border-blue-200 text-blue-800': toast.type === 'info',
                    }"
                >
                    <span class="text-base shrink-0">
                        <span v-if="toast.type === 'success'">✅</span>
                        <span v-else-if="toast.type === 'error'">❌</span>
                        <span v-else-if="toast.type === 'warning'">⚠️</span>
                        <span v-else>ℹ️</span>
                    </span>
                    <span class="flex-1">{{ toast.message }}</span>
                    <button @click="dismiss(toast.id)" class="text-gray-400 hover:text-gray-600 ml-1 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
