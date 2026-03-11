<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';
import DeliveryCheck from '@/Components/DeliveryCheck.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    product: { type: Object, required: true },
    reviews: { type: Object, default: () => ({}) },
    relatedProducts: { type: Array, default: () => [] },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const selectedImage = ref(0);
const selectedVariant = ref(null);
const quantity = ref(1);
const activeTab = ref('description');

const images = computed(() => {
    return props.product.images?.length ? props.product.images : [{ url: '/images/placeholder-product.png', alt: props.product.name }];
});

const currentPrice = computed(() => {
    if (selectedVariant.value) {
        return selectedVariant.value.price;
    }
    return props.product.price;
});

const comparePrice = computed(() => {
    if (selectedVariant.value) {
        return selectedVariant.value.compare_price;
    }
    return props.product.compare_price;
});

const discountPercent = computed(() => {
    if (comparePrice.value && parseFloat(comparePrice.value) > parseFloat(currentPrice.value)) {
        return Math.round(((comparePrice.value - currentPrice.value) / comparePrice.value) * 100);
    }
    return 0;
});

const inStock = computed(() => {
    if (selectedVariant.value) {
        return selectedVariant.value.stock > 0;
    }
    return props.product.stock > 0;
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price);
};

const addToCart = async () => {
    if (!user.value) {
        window.location.href = '/login';
        return;
    }

    try {
        await window.axios.post('/api/v1/user/cart', {
            product_id: props.product.id,
            product_variant_id: selectedVariant.value?.id,
            quantity: quantity.value,
        });
        alert('Added to cart!');
    } catch (e) {
        alert(e.response?.data?.message || 'Failed to add to cart');
    }
};

const addToWishlist = async () => {
    if (!user.value) {
        window.location.href = '/login';
        return;
    }

    try {
        await window.axios.post('/api/v1/user/wishlist', {
            product_id: props.product.id,
        });
        alert('Added to wishlist!');
    } catch (e) {
        alert(e.response?.data?.message || 'Failed to add to wishlist');
    }
};
</script>

<template>
    <AppLayout :title="product.name">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 text-sm text-gray-500 mb-8">
                <Link href="/" class="hover:text-amber-600">Home</Link>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <Link href="/products" class="hover:text-amber-600">Products</Link>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <span v-if="product.category">
                    <Link :href="`/products?category=${product.category.slug}`" class="hover:text-amber-600">{{ product.category.name }}</Link>
                    <svg class="w-4 h-4 inline mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </span>
                <span class="text-gray-900 font-medium truncate max-w-[200px]">{{ product.name }}</span>
            </nav>

            <!-- Main Product Section -->
            <div class="grid lg:grid-cols-2 gap-10 lg:gap-14">
                <!-- Image Gallery -->
                <div>
                    <!-- Main Image -->
                    <div class="aspect-square bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm mb-4">
                        <img
                            :src="images[selectedImage]?.url || images[selectedImage]?.image_url || '/images/placeholder-product.png'"
                            :alt="product.name"
                            class="w-full h-full object-cover"
                        />
                    </div>
                    <!-- Thumbnails -->
                    <div v-if="images.length > 1" class="flex gap-3 overflow-x-auto">
                        <button
                            v-for="(img, idx) in images"
                            :key="idx"
                            @click="selectedImage = idx"
                            class="w-20 h-20 rounded-xl overflow-hidden border-2 transition-colors shrink-0"
                            :class="selectedImage === idx ? 'border-amber-500' : 'border-gray-100 hover:border-gray-300'"
                        >
                            <img :src="img.url || img.image_url" :alt="`${product.name} - Image ${idx + 1}`" class="w-full h-full object-cover" />
                        </button>
                    </div>
                </div>

                <!-- Product Info -->
                <div>
                    <div v-if="product.category" class="mb-2">
                        <Link :href="`/products?category=${product.category.slug}`" class="text-amber-600 text-sm font-medium hover:text-amber-700">
                            {{ product.category.name }}
                        </Link>
                    </div>

                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3">{{ product.name }}</h1>

                    <!-- Rating -->
                    <div v-if="product.avg_rating > 0" class="flex items-center gap-2 mb-4">
                        <div class="flex">
                            <svg v-for="i in 5" :key="i" class="w-5 h-5" :class="i <= Math.round(product.avg_rating) ? 'text-amber-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <span class="text-sm text-gray-600">{{ product.avg_rating }} ({{ product.total_reviews }} reviews)</span>
                    </div>

                    <!-- Price -->
                    <div class="flex items-baseline gap-3 mb-6">
                        <span class="text-3xl font-bold text-gray-900">{{ formatPrice(currentPrice) }}</span>
                        <span v-if="discountPercent > 0" class="text-lg text-gray-400 line-through">{{ formatPrice(comparePrice) }}</span>
                        <span v-if="discountPercent > 0" class="bg-red-100 text-red-600 text-sm font-bold px-2.5 py-0.5 rounded-full">
                            {{ discountPercent }}% OFF
                        </span>
                    </div>

                    <!-- Short description -->
                    <p v-if="product.short_description" class="text-gray-600 leading-relaxed mb-6">{{ product.short_description }}</p>

                    <!-- Variants -->
                    <div v-if="product.variants?.length > 0" class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Select Variant</h3>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="variant in product.variants"
                                :key="variant.id"
                                @click="selectedVariant = variant"
                                class="px-4 py-2 rounded-lg border-2 text-sm font-medium transition-all"
                                :class="selectedVariant?.id === variant.id ? 'border-amber-500 bg-amber-50 text-amber-700' : 'border-gray-200 text-gray-700 hover:border-gray-300'"
                            >
                                {{ variant.name }} - {{ formatPrice(variant.price) }}
                            </button>
                        </div>
                    </div>

                    <!-- Quantity & Actions -->
                    <div class="flex flex-wrap items-center gap-4 mb-8">
                        <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden">
                            <button @click="quantity = Math.max(1, quantity - 1)" class="w-10 h-10 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition-colors">
                                <svg class scoped="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                            </button>
                            <span class="w-12 text-center font-semibold text-gray-900">{{ quantity }}</span>
                            <button @click="quantity = Math.min(10, quantity + 1)" class="w-10 h-10 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            </button>
                        </div>

                        <button
                            @click="addToCart"
                            :disabled="!inStock"
                            class="flex-1 sm:flex-initial bg-amber-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-amber-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-amber-600/25"
                        >
                            {{ inStock ? 'Add to Cart' : 'Out of Stock' }}
                        </button>

                        <button
                            @click="addToWishlist"
                            class="w-12 h-12 border-2 border-gray-200 rounded-xl flex items-center justify-center text-gray-400 hover:text-red-500 hover:border-red-200 transition-all"
                            title="Add to Wishlist"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Stock & SKU -->
                    <div class="flex items-center gap-4 text-sm mb-6">
                        <span :class="inStock ? 'text-green-600' : 'text-red-600'" class="font-medium">
                            {{ inStock ? 'In Stock' : 'Out of Stock' }}
                        </span>
                        <span v-if="product.sku" class="text-gray-400">SKU: {{ product.sku }}</span>
                        <span v-if="product.weight" class="text-gray-400">{{ product.weight }}{{ product.unit || 'g' }}</span>
                    </div>

                    <!-- Features -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex items-center gap-2 bg-gray-50 rounded-lg p-3">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                            <span class="text-xs text-gray-600">Free delivery ₹499+</span>
                        </div>
                        <div class="flex items-center gap-2 bg-gray-50 rounded-lg p-3">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            <span class="text-xs text-gray-600">100% authentic</span>
                        </div>
                        <div class="flex items-center gap-2 bg-gray-50 rounded-lg p-3">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            <span class="text-xs text-gray-600">7-day returns</span>
                        </div>
                        <div class="flex items-center gap-2 bg-gray-50 rounded-lg p-3">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            <span class="text-xs text-gray-600">Secure payment</span>
                        </div>
                    </div>

                    <!-- Delivery Availability Check -->
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <DeliveryCheck />
                    </div>
                </div>
            </div>

            <!-- Tabs: Description / Reviews -->
            <div class="mt-16">
                <div class="flex border-b border-gray-200">
                    <button
                        @click="activeTab = 'description'"
                        class="px-6 py-3 text-sm font-semibold border-b-2 transition-colors"
                        :class="activeTab === 'description' ? 'border-amber-500 text-amber-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    >
                        Description
                    </button>
                    <button
                        @click="activeTab = 'reviews'"
                        class="px-6 py-3 text-sm font-semibold border-b-2 transition-colors"
                        :class="activeTab === 'reviews' ? 'border-amber-500 text-amber-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    >
                        Reviews ({{ product.total_reviews || 0 }})
                    </button>
                </div>

                <!-- Description Tab -->
                <div v-if="activeTab === 'description'" class="py-8">
                    <div class="prose prose-amber max-w-none" v-html="product.description || '<p>No description available.</p>'"></div>

                    <!-- Attributes -->
                    <div v-if="product.attributes && Object.keys(product.attributes).length > 0" class="mt-8">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Specifications</h3>
                        <table class="w-full">
                            <tr v-for="(val, key) in product.attributes" :key="key" class="border-b border-gray-100">
                                <td class="py-3 pr-4 text-sm font-medium text-gray-600 w-1/3 capitalize">{{ key.replace('_', ' ') }}</td>
                                <td class="py-3 text-sm text-gray-900">{{ val }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Reviews Tab -->
                <div v-if="activeTab === 'reviews'" class="py-8">
                    <div v-if="reviews.data?.length > 0" class="space-y-6">
                        <div v-for="review in reviews.data" :key="review.id" class="border-b border-gray-100 pb-6">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-amber-100 text-amber-700 rounded-full flex items-center justify-center text-sm font-bold shrink-0">
                                    {{ review.user?.name?.charAt(0)?.toUpperCase() }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-1">
                                        <span class="font-semibold text-gray-900 text-sm">{{ review.user?.name }}</span>
                                        <div class="flex">
                                            <svg v-for="i in 5" :key="i" class="w-3.5 h-3.5" :class="i <= review.rating ? 'text-amber-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="text-gray-600 text-sm">{{ review.comment }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12">
                        <p class="text-gray-500">No reviews yet for this product.</p>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <section v-if="relatedProducts.length > 0" class="mt-16">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">You Might Also Like</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                    <ProductCard v-for="product in relatedProducts" :key="product.id" :product="product" />
                </div>
            </section>
        </div>
    </AppLayout>
</template>
