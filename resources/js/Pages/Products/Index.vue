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
const showPriceFilter = ref(false);

const sortOptions = [
    { value: 'latest', label: 'Newest First' },
    { value: 'price_low', label: 'Price: Low → High' },
    { value: 'price_high', label: 'Price: High → Low' },
    { value: 'rating', label: 'Top Rated' },
    { value: 'popular', label: 'Most Popular' },
];

const applyFilters = () => {
    const params = {};
    if (search.value) params.search = search.value;
    if (selectedCategory.value) params.category = selectedCategory.value;
    if (sortBy.value !== 'latest') params.sort = sortBy.value;
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
    showPriceFilter.value = false;
    router.get('/products', {}, { preserveState: true });
};

const hasActiveFilters = () => search.value || selectedCategory.value || minPrice.value || maxPrice.value || sortBy.value !== 'latest';

let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 400);
});

watch(sortBy, () => applyFilters());
</script>

<template>
    <AppLayout title="Products">
        <div class="bg-gray-50 min-h-screen">

            <!-- Page Header -->
            <div class="bg-white border-b border-gray-100 sticky top-24 z-20 shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <!-- Title + search + sort row -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-4">
                        <div class="flex-1 min-w-0">
                            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 leading-tight">
                                {{ filters.featured ? '⭐ Featured Snacks' : (selectedCategory ? (categories.find(c => c.slug === selectedCategory)?.name || 'Products') : 'All Snacks') }}
                            </h1>
                            <p class="text-sm text-gray-400 mt-0.5 font-medium">{{ products.total }} products</p>
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <!-- Search -->
                            <div class="relative flex-1 sm:w-64">
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search snacks…"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:border-amber-500 focus:bg-white transition-colors shadow-sm"
                                />
                                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <!-- Sort -->
                            <select v-model="sortBy"
                                class="shrink-0 bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-700 focus:border-amber-500 focus:outline-none shadow-sm transition-colors">
                                <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Category filter chips + price toggle -->
                    <div class="flex items-center gap-2">
                        <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide flex-1 min-w-0">
                            <button
                                @click="selectedCategory = ''; applyFilters()"
                                class="shrink-0 px-4 py-1.5 rounded-full text-sm font-semibold transition-all border"
                                :class="!selectedCategory ? 'bg-amber-500 text-white border-amber-500 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:border-amber-300 hover:text-amber-700'">
                                All
                            </button>
                            <button
                                v-for="cat in categories" :key="cat.id"
                                @click="selectedCategory = cat.slug; applyFilters()"
                                class="shrink-0 px-4 py-1.5 rounded-full text-sm font-semibold transition-all border"
                                :class="selectedCategory === cat.slug ? 'bg-amber-500 text-white border-amber-500 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:border-amber-300 hover:text-amber-700'">
                                {{ cat.name }}
                                <span class="opacity-60 text-xs ml-0.5">({{ cat.products_count }})</span>
                            </button>
                        </div>
                        <!-- Price filter toggle -->
                        <button
                            @click="showPriceFilter = !showPriceFilter"
                            class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-sm text-gray-700 hover:border-amber-400 hover:text-amber-700 transition-all shadow-sm"
                            :class="showPriceFilter ? 'border-amber-400 text-amber-700 bg-amber-50' : ''">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Price
                            <span v-if="minPrice || maxPrice" class="w-2 h-2 bg-amber-500 rounded-full inline-block"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">

                <!-- Price filter panel -->
                <Transition
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2">
                    <div v-if="showPriceFilter" class="flex items-center gap-3 mb-4 bg-white border border-amber-100 rounded-2xl px-4 py-3 shadow-sm">
                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs text-gray-500 shrink-0 font-medium">Price range (₹)</span>
                        <input v-model="minPrice" type="number" min="0" placeholder="Min" @change="applyFilters"
                            class="w-20 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:border-amber-500 focus:outline-none bg-gray-50" />
                        <span class="text-gray-300 text-sm">—</span>
                        <input v-model="maxPrice" type="number" min="0" placeholder="Max" @change="applyFilters"
                            class="w-20 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:border-amber-500 focus:outline-none bg-gray-50" />
                        <button v-if="minPrice || maxPrice" @click="minPrice = ''; maxPrice = ''; applyFilters()"
                            class="text-xs text-red-400 hover:text-red-600 ml-1 font-medium">Clear</button>
                    </div>
                </Transition>

                <!-- Active filter chips -->
                <div v-if="hasActiveFilters()" class="flex items-center gap-2 mb-5 flex-wrap">
                    <span class="text-xs text-gray-400 font-medium">Active:</span>
                    <span v-if="search" class="inline-flex items-center gap-1 bg-amber-100 text-amber-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                        "{{ search }}"
                        <button @click="search = ''; applyFilters()" class="hover:text-amber-900 ml-0.5 text-base leading-none">✕</button>
                    </span>
                    <span v-if="selectedCategory" class="inline-flex items-center gap-1 bg-amber-100 text-amber-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                        {{ categories.find(c => c.slug === selectedCategory)?.name }}
                        <button @click="selectedCategory = ''; applyFilters()" class="hover:text-amber-900 ml-0.5 text-base leading-none">✕</button>
                    </span>
                    <span v-if="minPrice || maxPrice" class="inline-flex items-center gap-1 bg-amber-100 text-amber-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                        ₹{{ minPrice || 0 }} – {{ maxPrice || '∞' }}
                        <button @click="minPrice = ''; maxPrice = ''; applyFilters()" class="hover:text-amber-900 ml-0.5 text-base leading-none">✕</button>
                    </span>
                    <button @click="clearFilters" class="text-xs text-red-400 hover:text-red-600 font-semibold underline ml-auto">Clear all</button>
                </div>
                <div v-else class="mb-4"></div>

                <!-- Product Grid -->
                <div v-if="products.data.length > 0" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">
                    <ProductCard v-for="product in products.data" :key="product.id" :product="product" />
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-3xl border border-gray-100 text-center py-20 px-4 shadow-sm">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No snacks found</h3>
                    <p class="text-gray-400 text-sm mb-6 max-w-xs mx-auto">Try different keywords or clear your filters to discover more delicious snacks.</p>
                    <button @click="clearFilters" class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-8 py-3 rounded-xl text-sm transition-colors shadow-sm shadow-amber-500/30">
                        Clear Filters
                    </button>
                </div>

                <!-- Pagination -->
                <div v-if="products.last_page > 1" class="flex justify-center mt-10">
                    <nav class="flex flex-wrap gap-1.5 justify-center">
                        <Link
                            v-for="link in products.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            class="px-3.5 py-2 rounded-xl text-sm font-semibold transition-all"
                            :class="link.active ? 'bg-amber-600 text-white shadow-md shadow-amber-600/25' : (link.url ? 'bg-white text-gray-700 border border-gray-200 hover:border-amber-300 hover:text-amber-700' : 'text-gray-300 cursor-not-allowed pointer-events-none')"
                            v-html="link.label"
                            preserve-scroll
                        />
                    </nav>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
