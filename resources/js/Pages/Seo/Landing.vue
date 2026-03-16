<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    mode: { type: String, default: 'location' },
    location: { type: Object, default: null },
    keyword: { type: Object, default: null },
    seo: { type: Object, default: () => ({}) },
});

const title = props.seo?.title || 'Snackzar SEO Landing';
</script>

<template>
    <AppLayout :title="title">
        <section class="bg-gradient-to-br from-amber-50 via-orange-50 to-yellow-50 border-b border-amber-100">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14">
                <p class="inline-flex items-center px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-semibold tracking-wide">
                    Programmatic SEO Landing
                </p>

                <h1 class="mt-4 text-3xl sm:text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight">
                    <template v-if="mode === 'keyword' && keyword">
                        {{ keyword.keyword }}
                    </template>
                    <template v-else-if="location">
                        Buy Premium Makhana in {{ location.name }}
                    </template>
                    <template v-else>
                        Premium Makhana Delivery
                    </template>
                </h1>

                <p class="mt-4 text-base sm:text-lg text-gray-700 max-w-3xl">
                    {{ seo.description || 'Snackzar delivers premium makhana and healthy snacks with trusted quality, strong local relevance, and fast delivery.' }}
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <Link href="/products" class="px-5 py-3 rounded-xl bg-amber-600 text-white font-semibold hover:bg-amber-700 transition">
                        Shop Products
                    </Link>
                    <Link href="/" class="px-5 py-3 rounded-xl bg-white border border-amber-200 text-amber-700 font-semibold hover:bg-amber-50 transition">
                        Go Home
                    </Link>
                </div>
            </div>
        </section>

        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid md:grid-cols-3 gap-4">
                <article class="rounded-2xl bg-white p-6 border border-gray-100 shadow-sm">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Coverage</h2>
                    <p class="mt-2 text-gray-900 font-bold text-lg">38 Bihar Districts + 400+ India + 500+ Global</p>
                </article>
                <article class="rounded-2xl bg-white p-6 border border-gray-100 shadow-sm">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Keyword Engine</h2>
                    <p class="mt-2 text-gray-900 font-bold text-lg">2.5 Lakh Long-Tail Combinations</p>
                </article>
                <article class="rounded-2xl bg-white p-6 border border-gray-100 shadow-sm">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Sitemaps</h2>
                    <p class="mt-2 text-gray-900 font-bold text-lg">Auto-sharded for 150k+ URLs</p>
                </article>
            </div>

            <div v-if="mode === 'location' && location" class="mt-8 rounded-2xl bg-white p-6 border border-gray-100 shadow-sm">
                <h2 class="text-2xl font-bold text-gray-900">Why choose Snackzar in {{ location.name }}?</h2>
                <p class="mt-3 text-gray-700 leading-relaxed">{{ location.content_summary }}</p>

                <ul class="mt-4 grid sm:grid-cols-2 gap-3 text-gray-700">
                    <li class="bg-amber-50 rounded-xl p-3">Products Available: {{ location.stats?.products_available || 0 }}</li>
                    <li class="bg-amber-50 rounded-xl p-3">Customers Served: {{ location.stats?.customers_served || 0 }}</li>
                    <li class="bg-amber-50 rounded-xl p-3">Average Rating: {{ location.stats?.avg_rating || 4.8 }}</li>
                    <li class="bg-amber-50 rounded-xl p-3">Reviews: {{ location.stats?.total_reviews || 0 }}</li>
                </ul>
            </div>

            <div v-if="mode === 'keyword' && keyword" class="mt-8 rounded-2xl bg-white p-6 border border-gray-100 shadow-sm">
                <h2 class="text-2xl font-bold text-gray-900">Keyword SEO Insights</h2>
                <p class="mt-3 text-gray-700 leading-relaxed">
                    This page is generated from your programmatic keyword model and optimized for intent matching, semantic relevance, and regional discoverability.
                </p>
                <p class="mt-3 text-gray-700">
                    Keyword ID: <span class="font-semibold">{{ keyword.id }}</span>
                </p>
            </div>
        </section>
    </AppLayout>
</template>
