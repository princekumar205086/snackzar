<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const imageUrl = computed(() => {
    return props.product.primary_image?.url || props.product.primary_image?.image_url || '/images/placeholder-product.png';
});

const discountPercent = computed(() => {
    if (props.product.compare_price && parseFloat(props.product.compare_price) > parseFloat(props.product.price)) {
        return Math.round(((props.product.compare_price - props.product.price) / props.product.compare_price) * 100);
    }
    return 0;
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price);
};
</script>

<template>
    <Link :href="`/products/${product.slug}`" class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 flex flex-col">
        <!-- Image -->
        <div class="relative overflow-hidden aspect-square bg-amber-50">
            <img
                :src="imageUrl"
                :alt="product.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                loading="lazy"
            />
            <div v-if="discountPercent > 0" class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">
                -{{ discountPercent }}%
            </div>
            <div v-if="product.is_featured" class="absolute top-3 right-3 bg-amber-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">
                Featured
            </div>
        </div>

        <!-- Content -->
        <div class="p-4 flex flex-col flex-1">
            <p v-if="product.category" class="text-xs text-amber-600 font-medium mb-1">{{ product.category.name }}</p>
            <h3 class="text-sm font-semibold text-gray-900 group-hover:text-amber-700 transition-colors line-clamp-2 mb-2 flex-1">
                {{ product.name }}
            </h3>

            <!-- Rating -->
            <div v-if="product.avg_rating > 0" class="flex items-center gap-1 mb-2">
                <div class="flex">
                    <svg v-for="i in 5" :key="i" class="w-3.5 h-3.5" :class="i <= Math.round(product.avg_rating) ? 'text-amber-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
                <span class="text-xs text-gray-500">({{ product.total_reviews }})</span>
            </div>

            <!-- Price -->
            <div class="flex items-baseline gap-2">
                <span class="text-lg font-bold text-gray-900">{{ formatPrice(product.price) }}</span>
                <span v-if="discountPercent > 0" class="text-sm text-gray-400 line-through">{{ formatPrice(product.compare_price) }}</span>
            </div>
        </div>
    </Link>
</template>
