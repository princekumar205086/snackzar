<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    post: Object,
    relatedPosts: Array,
    canonicalUrl: String,
    schemas: Array,
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
    <Head>
        <title>{{ post.meta_title || `${post.title} | Snackzar` }}</title>
        <meta name="description" :content="post.meta_description || post.excerpt || ''" />
        <meta name="keywords" :content="post.meta_keywords || ''" />
        <link rel="canonical" :href="canonicalUrl" />
        <meta property="og:type" content="article" />
        <meta property="og:title" :content="post.meta_title || post.title" />
        <meta property="og:description" :content="post.meta_description || post.excerpt || ''" />
        <meta property="og:url" :content="canonicalUrl" />
        <meta property="og:image" :content="post.featured_image || ''" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="post.meta_title || post.title" />
        <meta name="twitter:description" :content="post.meta_description || post.excerpt || ''" />
        <meta name="twitter:image" :content="post.featured_image || ''" />
        <template v-for="(schema, index) in schemas || []" :key="`schema-${index}`">
            <component :is="'script'" type="application/ld+json" v-html="JSON.stringify(schema)" />
        </template>
    </Head>
    <AppLayout>
        <article>
            <!-- Hero Image -->
            <div class="bg-gradient-to-br from-amber-100 to-orange-100">
                <div class="max-w-4xl mx-auto">
                    <div v-if="post.featured_image" class="aspect-[21/9] overflow-hidden">
                        <img
                            :src="post.featured_image"
                            :alt="post.title"
                            class="w-full h-full object-cover"
                        />
                    </div>
                    <div v-else class="aspect-[21/9] flex items-center justify-center">
                        <svg class="w-24 h-24 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-sm text-gray-400 mb-8">
                    <Link href="/" class="hover:text-amber-600">Home</Link>
                    <span>/</span>
                    <Link href="/blog" class="hover:text-amber-600">Blog</Link>
                    <span>/</span>
                    <span class="text-gray-600 truncate">{{ post.title }}</span>
                </nav>

                <!-- Meta -->
                <div class="flex flex-wrap items-center gap-4 mb-6">
                    <span class="bg-amber-100 text-amber-800 text-sm font-semibold px-3 py-1 rounded-full">
                        {{ post.category }}
                    </span>
                    <span class="text-sm text-gray-500">{{ readingTime(post.content) }}</span>
                    <span class="text-sm text-gray-500">{{ formatDate(post.published_at) }}</span>
                    <span class="text-sm text-gray-500">{{ post.views_count }} views</span>
                </div>

                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6 leading-tight">
                    {{ post.title }}
                </h1>

                <!-- Author -->
                <div v-if="post.author" class="flex items-center gap-3 mb-10 pb-8 border-b">
                    <div class="w-10 h-10 rounded-full bg-amber-500 flex items-center justify-center text-white font-bold">
                        {{ post.author.name.charAt(0) }}
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ post.author.name }}</p>
                        <p class="text-sm text-gray-500">Author</p>
                    </div>
                </div>

                <!-- Tags -->
                <div v-if="post.tags && post.tags.length" class="flex flex-wrap gap-2 mb-8">
                    <span
                        v-for="tag in post.tags"
                        :key="tag"
                        class="bg-gray-100 text-gray-600 text-xs px-3 py-1 rounded-full"
                    >
                        #{{ tag }}
                    </span>
                </div>

                <!-- Body -->
                <div
                    class="prose prose-lg prose-amber max-w-none mb-12"
                    v-html="post.content"
                />

                <!-- Share -->
                <div class="border-t pt-8 mb-12">
                    <p class="text-sm font-medium text-gray-500 mb-3">Share this article</p>
                    <div class="flex gap-3">
                        <a
                            :href="`https://twitter.com/intent/tweet?text=${encodeURIComponent(post.title)}&url=${encodeURIComponent($page.props.ziggy?.url ? $page.props.ziggy.url + '/blog/' + post.slug : '')}`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-4 py-2 border rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors"
                        >
                            Twitter
                        </a>
                        <a
                            :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent($page.props.ziggy?.url ? $page.props.ziggy.url + '/blog/' + post.slug : '')}`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-4 py-2 border rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors"
                        >
                            Facebook
                        </a>
                    </div>
                </div>

                <!-- Back -->
                <div class="text-center">
                    <Link
                        href="/blog"
                        class="inline-flex items-center gap-2 text-amber-600 hover:text-amber-700 font-medium"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Blog
                    </Link>
                </div>
            </div>
        </article>

        <!-- Related Posts -->
        <section v-if="relatedPosts && relatedPosts.length" class="bg-gray-50 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-8">Related Articles</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <Link
                        v-for="related in relatedPosts"
                        :key="related.id"
                        :href="`/blog/${related.slug}`"
                        class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow group"
                    >
                        <div class="aspect-video bg-gradient-to-br from-amber-100 to-orange-100 overflow-hidden">
                            <img
                                v-if="related.featured_image"
                                :src="related.featured_image"
                                :alt="related.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                        </div>
                        <div class="p-5">
                            <span class="text-xs text-amber-600 font-semibold">{{ related.category }}</span>
                            <h3 class="font-bold text-gray-900 mt-1 group-hover:text-amber-600 transition-colors line-clamp-2">
                                {{ related.title }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ related.excerpt }}</p>
                        </div>
                    </Link>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
