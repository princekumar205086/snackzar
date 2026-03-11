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

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'long',
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
        <section class="bg-gradient-to-r from-amber-600 to-orange-500 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                    Snackzar Blog
                </h1>
                <p class="text-xl text-amber-100 max-w-2xl mx-auto">
                    Discover recipes, health tips, and stories about Bihar's finest snacks
                </p>
            </div>
        </section>

        <!-- Filters -->
        <section class="bg-white border-b sticky top-0 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search articles..."
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                        />
                    </div>
                    <div class="sm:w-48">
                        <select
                            v-model="selectedCategory"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                        >
                            <option value="">All Categories</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">
                                {{ cat }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </section>

        <!-- Posts Grid -->
        <section class="py-12 bg-gray-50 min-h-[60vh]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-if="posts.data.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <article
                        v-for="post in posts.data"
                        :key="post.id"
                        class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow duration-300 flex flex-col"
                    >
                        <Link :href="`/blog/${post.slug}`" class="block">
                            <div class="aspect-video bg-gradient-to-br from-amber-100 to-orange-100 relative overflow-hidden">
                                <img
                                    v-if="post.featured_image"
                                    :src="post.featured_image"
                                    :alt="post.title"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <svg class="w-16 h-16 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                    </svg>
                                </div>
                            </div>
                        </Link>
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="inline-block bg-amber-100 text-amber-800 text-xs font-semibold px-2.5 py-1 rounded-full">
                                    {{ post.category }}
                                </span>
                                <span class="text-xs text-gray-400">
                                    {{ readingTime(post.content) }}
                                </span>
                            </div>
                            <Link :href="`/blog/${post.slug}`" class="block group">
                                <h2 class="text-lg font-bold text-gray-900 group-hover:text-amber-600 transition-colors mb-2 line-clamp-2">
                                    {{ post.title }}
                                </h2>
                            </Link>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3 flex-1">
                                {{ post.excerpt }}
                            </p>
                            <div class="flex items-center justify-between text-xs text-gray-400 mt-auto pt-4 border-t">
                                <span v-if="post.author">{{ post.author.name }}</span>
                                <span>{{ formatDate(post.published_at) }}</span>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-20">
                    <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-600 mb-1">No articles found</h3>
                    <p class="text-gray-400">Try adjusting your search or filter criteria.</p>
                </div>

                <!-- Pagination -->
                <nav v-if="posts.last_page > 1" class="mt-12 flex justify-center">
                    <div class="flex gap-2">
                        <template v-for="link in posts.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                                :class="link.active ? 'bg-amber-500 text-white' : 'bg-white text-gray-700 hover:bg-amber-50 border'"
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
