<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
});

// Cycle through soft pastel backgrounds for variety
const bgColors = [
    'bg-blue-50',
    'bg-amber-50',
    'bg-green-50',
    'bg-pink-50',
    'bg-purple-50',
    'bg-orange-50',
    'bg-teal-50',
    'bg-yellow-50',
];

// Pick a consistent color based on the category id or name
const getBg = () => {
    const seed = props.category.id ? props.category.id : props.category.name.charCodeAt(0);
    return bgColors[seed % bgColors.length];
};
</script>

<template>
    <Link
        :href="`/products?category=${category.slug}`"
        class="group flex flex-col items-center gap-2 cursor-pointer shrink-0 w-28 lg:w-auto"
    >
        <!-- Image Box -->
        <div :class="getBg()" class="w-full aspect-square rounded-2xl overflow-hidden flex items-center justify-center border border-gray-100 group-hover:border-amber-300 group-hover:shadow-md transition-all duration-300">
            <img
                v-if="category.image"
                :src="category.image"
                :alt="category.name"
                class="w-4/5 h-4/5 object-contain group-hover:scale-110 transition-transform duration-500"
                loading="lazy"
            />
            <div v-else class="flex flex-col items-center gap-1">
                <svg class="w-10 h-10 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </div>
        <!-- Name -->
        <div class="text-center">
            <h3 class="text-xs sm:text-sm font-semibold text-gray-800 group-hover:text-amber-700 transition-colors leading-tight">{{ category.name }}</h3>
            <p v-if="category.products_count" class="text-xs text-gray-400 mt-0.5">{{ category.products_count }} items</p>
        </div>
    </Link>
</template>
