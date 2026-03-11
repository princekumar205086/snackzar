<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    posts: Object,
    filters: Object,
    categories: Array,
})

const search = ref(props.filters?.search || '')
const selectedCategory = ref(props.filters?.category || '')

let debounceTimer = null

function applyFilters() {
    const params = {}
    if (search.value) params.search = search.value
    if (selectedCategory.value) params.category = selectedCategory.value
    router.get('/blog', params, { preserveState: true, replace: true })
}

watch(search, () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(applyFilters, 400)
})

watch(selectedCategory, () => {
    applyFilters()
})

function clearFilters() {
    search.value = ''
    selectedCategory.value = ''
    router.get('/blog', {}, { preserveState: true, replace: true })
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

function readingTime(content) {
    if (!content) return '1 min read'
    const words = content.replace(/<[^>]*>/g, '').split(/\s+/).length
    return Math.max(1, Math.ceil(words / 200)) + ' min read'
}
</script>

<template>
    <Head title="Blog - Snackzar" />
    <AppLayout>
        <!-- Hero -->
        <section class="bg-gradient-to-br from-amber-800 via-amber-700 to-orange-600 py-12 lg:py-16 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>
            </div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto">
                    <p class="text-amber-200 font-medium text-sm uppercase tracking-wider mb-3">The Snackzar Blog</p>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4">
                        Stories, Recipes & Health Tips
                    </h1>
                    <p class="text-amber-100 text-lg max-w-xl mx-auto">
                        Discover the world of Bihari snacks — from farm stories to delicious recipes and nutritional insights.
                    </p>
                </div>
            </div>
        </section>

        <!-- Blog Navigation / Categories -->
        <nav class="bg-white border-b sticky top-0 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-6 py-3 overflow-x-auto scrollbar-hide">
                    <button
                        @click="selectedCategory = ''"
                        class="whitespace-nowrap px-3 py-1.5 rounded-full text-sm font-medium transition-colors"
                        :class="!selectedCategory ? 'bg-amber-600 text-white' : 'text-gray-600 hover:text-amber-600 hover:bg-amber-50'"
                    >
                        All Posts
                    </button>
                    <button
                        v-for="cat in categories"
                        :key="cat"
                        @click="selectedCategory = cat"
                        class="whitespace-nowrap px-3 py-1.5 rounded-full text-sm font-medium transition-colors"
                        :class="selectedCategory === cat ? 'bg-amber-600 text-white' : 'text-gray-600 hover:text-amber-600 hover:bg-amber-50'"
                    >
                        {{ cat }}
                    </button>
                </div>
            </div>
        </nav>

        <!-- Search & Content -->
        <section class="py-8 lg:py-12 bg-gray-50 min-h-[60vh]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Search Bar -->
                <div class="max-w-xl mx-auto mb-10">
                    <div class="relative">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search articles, recipes, health tips..."
                            class="w-full pl-12 pr-10 py-3 rounded-xl border border-gray-200 bg-white shadow-sm focus:border-amber-500 focus:ring-amber-500 text-gray-900 placeholder-gray-400"
                        />
                        <button v-if="search" @click="search = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div v-if="search || selectedCategory" class="mt-3 flex items-center gap-2 text-sm text-gray-500">
                        <span>Showing results for</span>
                        <span v-if="search" class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full font-medium">"{{ search }}"</span>
                        <span v-if="search && selectedCategory">in</span>
                        <span v-if="selectedCategory" class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full font-medium">{{ selectedCategory }}</span>
                        <button @click="clearFilters" class="text-amber-600 hover:text-amber-700 font-medium ml-1">Clear</button>
                    </div>
                </div>

                <!-- Featured Post (first post when not filtering) -->
                <div v-if="posts.data.length > 0 && !search && !selectedCategory && posts.current_page === 1" class="mb-10">
                    <article class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <div class="grid md:grid-cols-2 gap-0">
                            <Link :href="`/blog/${posts.data[0].slug}`" class="block">
                                <div class="aspect-video md:aspect-auto md:h-full bg-gradient-to-br from-amber-100 to-orange-100 relative overflow-hidden">
                                    <img v-if="posts.data[0].featured_image" :src="posts.data[0].featured_image" :alt="posts.data[0].title" class="w-full h-full object-cover"/>
                                    <div v-else class="w-full h-full flex items-center justify-center min-h-[300px]">
                                        <span class="text-6xl">📰</span>
                                    </div>
                                </div>
                            </Link>
                            <div class="p-6 lg:p-8 flex flex-col justify-center">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="bg-amber-100 text-amber-800 text-xs font-semibold px-3 py-1 rounded-full">{{ posts.data[0].category }}</span>
                                    <span class="text-xs text-gray-400">{{ readingTime(posts.data[0].content) }}</span>
                                    <span class="text-xs text-gray-400">{{ formatDate(posts.data[0].published_at) }}</span>
                                </div>
                                <Link :href="`/blog/${posts.data[0].slug}`" class="group">
                                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 group-hover:text-amber-600 transition-colors mb-3">
                                        {{ posts.data[0].title }}
                                    </h2>
                                </Link>
                                <p class="text-gray-600 leading-relaxed mb-6 line-clamp-3">{{ posts.data[0].excerpt }}</p>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-amber-200 text-amber-800 rounded-full flex items-center justify-center text-sm font-bold">
                                        {{ posts.data[0].author?.name?.charAt(0)?.toUpperCase() || 'S' }}
                                    </div>
                                    <span class="text-sm text-gray-600 font-medium">{{ posts.data[0].author?.name || 'Snackzar Team' }}</span>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- Posts Grid -->
                <div v-if="posts.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                    <template v-for="(post, index) in posts.data" :key="post.id">
                        <!-- Skip first post on page 1 when not filtering (shown as featured above) -->
                        <article
                            v-if="!(index === 0 && !search && !selectedCategory && posts.current_page === 1)"
                            class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 flex flex-col group"
                        >
                            <Link :href="`/blog/${post.slug}`" class="block">
                                <div class="aspect-video bg-gradient-to-br from-amber-50 to-orange-50 relative overflow-hidden">
                                    <img
                                        v-if="post.featured_image"
                                        :src="post.featured_image"
                                        :alt="post.title"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <span class="text-5xl">📝</span>
                                    </div>
                                </div>
                            </Link>
                            <div class="p-5 lg:p-6 flex-1 flex flex-col">
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="inline-block bg-amber-100 text-amber-800 text-xs font-semibold px-2.5 py-1 rounded-full">
                                        {{ post.category }}
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        {{ readingTime(post.content) }}
                                    </span>
                                </div>
                                <Link :href="`/blog/${post.slug}`" class="block">
                                    <h2 class="text-lg font-bold text-gray-900 group-hover:text-amber-600 transition-colors mb-2 line-clamp-2">
                                        {{ post.title }}
                                    </h2>
                                </Link>
                                <p class="text-gray-500 text-sm mb-4 line-clamp-3 flex-1">
                                    {{ post.excerpt }}
                                </p>
                                <div class="flex items-center justify-between text-xs text-gray-400 mt-auto pt-4 border-t border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-amber-100 text-amber-700 rounded-full flex items-center justify-center text-xs font-bold">
                                            {{ post.author?.name?.charAt(0)?.toUpperCase() || 'S' }}
                                        </div>
                                        <span>{{ post.author?.name || 'Snackzar Team' }}</span>
                                    </div>
                                    <span>{{ formatDate(post.published_at) }}</span>
                                </div>
                            </div>
                        </article>
                    </template>
                </div>

                <!-- Empty State -->
                <div v-if="posts.data.length === 0" class="text-center py-20">
                    <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">No articles found</h3>
                    <p class="text-gray-400 mb-4">Try adjusting your search or filter criteria.</p>
                    <button @click="clearFilters" class="text-amber-600 hover:text-amber-700 font-medium text-sm">
                        Clear all filters
                    </button>
                </div>

                <!-- Pagination -->
                <nav v-if="posts.last_page > 1" class="mt-12 flex justify-center">
                    <div class="flex gap-2">
                        <template v-for="link in posts.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                                :class="link.active ? 'bg-amber-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-amber-50 border border-gray-200'"
                                v-html="link.label"
                                preserve-state
                            />
                            <span
                                v-else
                                class="px-4 py-2 rounded-lg text-sm text-gray-400 bg-gray-100"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </nav>
            </div>
        </section>
    </AppLayout>
</template>
