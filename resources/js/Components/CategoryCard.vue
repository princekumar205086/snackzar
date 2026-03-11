<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    category: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Link :href="`/products?category=${category.slug}`" class="group relative overflow-hidden rounded-2xl aspect-[4/3] bg-amber-100">
        <img
            v-if="category.image"
            :src="category.image"
            :alt="category.name"
            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
            loading="lazy"
        />
        <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-amber-100 to-amber-200">
            <svg class="w-12 h-12 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
        <div class="absolute bottom-0 left-0 right-0 p-4">
            <h3 class="text-white font-bold text-lg group-hover:text-amber-200 transition-colors">{{ category.name }}</h3>
            <p v-if="category.products_count" class="text-amber-200 text-sm mt-0.5">{{ category.products_count }} Products</p>
        </div>
    </Link>
</template>
