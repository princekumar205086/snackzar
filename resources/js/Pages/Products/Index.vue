<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    products: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');
const sortBy = ref(props.filters.sort || 'latest');
const minPrice = ref(props.filters.min_price || '');
const maxPrice = ref(props.filters.max_price || '');
const showFilters = ref(false);

const sortOptions = [
    { value: 'latest', label: 'Newest First' },
    { value: 'price_low', label: 'Price: Low to High' },
    { value: 'price_high', label: 'Price: High to Low' },
    { value: 'rating', label: 'Highest Rated' },
    { value: 'popular', label: 'Most Popular' },
];

const applyFilters = () => {
    const params = {};
    if (search.value) params.search = search.value;
    if (selectedCategory.value) params.category = selectedCategory.value;
    if (sortBy.value && sortBy.value !== 'latest') params.sort = sortBy.value;
    if (minPrice.value) params.min_price = minPrice.value;
    if (maxPrice.value) params.max_price = maxPrice.value;
    if (props.filters.featured) params.featured = 1;

    router.get('/products', params, { preserveState: true, preserveScroll: false });
};

const clearFilters = () => {
    search.value = '';
    selectedCategory.value = '';
    sortBy.value = 'latest';
    minPrice.value = '';
    maxPrice.value = '';
    router.get('/products', {}, { preserveState: true });
};

let searchTimeout;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 400);
});

watch(sortBy, () => applyFilters());
</script>

<template>
    <AppLayout title="Products">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
                <Link href="/" class="hover:text-amber-600">Home</Link>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <span class="text-gray-900 font-medium">Products</span>
            </nav>

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Sidebar Filters (Desktop) -->
                <aside class="hidden lg:block w-64 shrink-0">
                    <div class="sticky top-24">
                        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                            <h3 class="font-semibold text-gray-900 mb-4">Filters</h3>

                            <!-- Search -->
                            <div class="mb-5">
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Search</label>
                                <div class="relative">
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="Search snacks..."
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500 pr-8"
                                    />
                                    <svg class="w-4 h-4 text-gray-400 absolute right-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Categories -->
                            <div class="mb-5">
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Category</label>
                                <div class="space-y-1">
                                    <button
                                        @click="selectedCategory = ''; applyFilters()"
                                        class="block w-full text-left px-3 py-1.5 rounded-lg text-sm transition-colors"
                                        :class="!selectedCategory ? 'bg-amber-100 text-amber-700 font-medium' : 'text-gray-700 hover:bg-gray-50'"
                                    >
                                        All Categories
                                    </button>
                                    <button
                                        v-for="cat in categories"
                                        :key="cat.id"
                                        @click="selectedCategory = cat.slug; applyFilters()"
                                        class="block w-full text-left px-3 py-1.5 rounded-lg text-sm transition-colors"
                                        :class="selectedCategory === cat.slug ? 'bg-amber-100 text-amber-700 font-medium' : 'text-gray-700 hover:bg-gray-50'"
                                    >
                                        {{ cat.name }}
                                        <span class="text-gray-400 text-xs">({{ cat.products_count }})</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Price Range -->
                            <div class="mb-5">
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Price Range (₹)</label>
                                <div class="flex gap-2 items-center">
                                    <input
                                        v-model="minPrice"
                                        type="number"
                                        min="0"
                                        placeholder="Min"
                                        class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:border-amber-500 focus:ring-amber-500"
                                        @change="applyFilters"
                                    />
                                    <span class="text-gray-400">-</span>
                                    <input
                                        v-model="maxPrice"
                                        type="number"
                                        min="0"
                                        placeholder="Max"
                                        class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:border-amber-500 focus:ring-amber-500"
                                        @change="applyFilters"
                                    />
                                </div>
                            </div>

                            <button
                                @click="clearFilters"
                                class="w-full text-sm text-amber-600 hover:text-amber-700 font-medium py-2 border border-amber-200 rounded-lg hover:bg-amber-50 transition-colors"
                            >
                                Clear All Filters
                            </button>
                        </div>
                    </div>
                </aside>

                <!-- Main Content -->
                <div class="flex-1">
                    <!-- Header bar -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">
                                {{ filters.featured ? 'Featured Snacks' : (selectedCategory ? categories.find(c => c.slug === selectedCategory)?.name || 'Products' : 'All Products') }}
                            </h1>
                            <p class="text-sm text-gray-500 mt-0.5">{{ products.total }} products found</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <!-- Mobile filter toggle -->
                            <button
                                @click="showFilters = !showFilters"
                                class="lg:hidden inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                Filters
                            </button>

                            <!-- Sort -->
                            <select
                                v-model="sortBy"
                                class="bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 focus:border-amber-500 focus:ring-amber-500"
                            >
                                <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Mobile Filters Panel -->
                    <div v-if="showFilters" class="lg:hidden bg-white rounded-2xl border border-gray-100 p-5 shadow-sm mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900">Filters</h3>
                            <button @click="showFilters = false" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div class="mb-4">
                            <input v-model="search" type="text" placeholder="Search snacks..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500" />
                        </div>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <button
                                @click="selectedCategory = ''; applyFilters()"
                                class="px-3 py-1 rounded-full text-sm"
                                :class="!selectedCategory ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-700'"
                            >All</button>
                            <button
                                v-for="cat in categories"
                                :key="cat.id"
                                @click="selectedCategory = cat.slug; applyFilters()"
                                class="px-3 py-1 rounded-full text-sm"
                                :class="selectedCategory === cat.slug ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-700'"
                            >{{ cat.name }}</button>
                        </div>
                        <button @click="clearFilters" class="text-sm text-amber-600 font-medium">Clear All</button>
                    </div>

                    <!-- Product Grid -->
                    <div v-if="products.data.length > 0" class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
                        <ProductCard v-for="product in products.data" :key="product.id" :product="product" />
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-20">
                        <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No products found</h3>
                        <p class="text-gray-500 text-sm mb-4">Try adjusting your filters or search terms.</p>
                        <button @click="clearFilters" class="text-amber-600 font-semibold hover:text-amber-700">Clear all filters</button>
                    </div>

                    <!-- Pagination -->
                    <div v-if="products.last_page > 1" class="flex justify-center mt-10">
                        <nav class="flex gap-1">
                            <Link
                                v-for="link in products.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors"
                                :class="link.active ? 'bg-amber-600 text-white' : (link.url ? 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' : 'text-gray-300 cursor-not-allowed')"
                                v-html="link.label"
                                preserve-scroll
                            />
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
