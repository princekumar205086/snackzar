<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';
import CategoryCard from '@/Components/CategoryCard.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    featuredProducts: { type: Array, default: () => [] },
    newArrivals: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    topRated: { type: Array, default: () => [] },
    recentReviews: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price);
};

const features = [
    {
        icon: 'truck',
        title: 'Free Delivery',
        description: 'Free shipping on orders above ₹499',
    },
    {
        icon: 'shield',
        title: '100% Authentic',
        description: 'Sourced directly from Bihar',
    },
    {
        icon: 'refresh',
        title: 'Easy Returns',
        description: '7-day hassle-free returns',
    },
    {
        icon: 'lock',
        title: 'Secure Payment',
        description: 'Razorpay secured checkout',
    },
];

const testimonials = [
    {
        name: 'Priya Sharma',
        location: 'Delhi',
        text: 'The Makhana from Snackzar is the crunchiest I\'ve ever had! Truly authentic Bihari taste. Will order again.',
        rating: 5,
    },
    {
        name: 'Rahul Kumar',
        location: 'Mumbai',
        text: 'Fast delivery and excellent packaging. The snacks were fresh and delicious. My family loved them!',
        rating: 5,
    },
    {
        name: 'Anita Devi',
        location: 'Bangalore',
        text: 'Finally found a place that delivers real Bihari snacks. The Sattu and Makhana are outstanding quality.',
        rating: 4,
    },
];
</script>

<template>
    <AppLayout title="Premium Bihari Snacks - Makhana & More">
        <!-- Hero Section -->
        <section class="relative overflow-hidden bg-gradient-to-br from-amber-900 via-amber-800 to-amber-950">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>
            </div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <div class="inline-flex items-center gap-2 bg-amber-700/50 text-amber-200 text-sm font-medium px-4 py-1.5 rounded-full mb-6 backdrop-blur-sm">
                            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                            Fresh from Bihar
                        </div>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight mb-6">
                            Premium <span class="text-amber-300">Makhana</span> &amp; Authentic Bihari Snacks
                        </h1>
                        <p class="text-lg text-amber-200 leading-relaxed mb-8 max-w-lg">
                            Discover the finest selection of hand-picked Makhana, traditional Bihari namkeen, and artisanal snacks. Sourced directly from local farmers, delivered fresh to your doorstep.
                        </p>
                        <div class="flex flex-wrap gap-4">
                            <Link href="/products" class="bg-amber-500 text-amber-950 px-8 py-3.5 rounded-xl font-bold text-lg hover:bg-amber-400 transition-all shadow-lg shadow-amber-500/25 hover:shadow-amber-400/40">
                                Shop Now
                            </Link>
                            <Link href="/products?featured=1" class="border-2 border-amber-400/50 text-amber-100 px-8 py-3.5 rounded-xl font-semibold text-lg hover:bg-amber-800/50 transition-all backdrop-blur-sm">
                                Featured Snacks
                            </Link>
                        </div>

                        <!-- Stats -->
                        <div class="flex gap-8 mt-12 pt-8 border-t border-amber-700/50">
                            <div>
                                <p class="text-3xl font-bold text-white">{{ stats.products || '50' }}+</p>
                                <p class="text-amber-300 text-sm">Products</p>
                            </div>
                            <div>
                                <p class="text-3xl font-bold text-white">{{ stats.happy_customers || '500' }}+</p>
                                <p class="text-amber-300 text-sm">Happy Customers</p>
                            </div>
                            <div>
                                <p class="text-3xl font-bold text-white">4.8</p>
                                <p class="text-amber-300 text-sm">Avg Rating</p>
                            </div>
                        </div>
                    </div>

                    <!-- Hero Image / Visual -->
                    <div class="hidden lg:flex items-center justify-center">
                        <div class="relative">
                            <div class="w-96 h-96 bg-gradient-to-br from-amber-400/20 to-amber-600/20 rounded-full blur-3xl absolute -inset-10"></div>
                            <div class="relative w-80 h-80 bg-gradient-to-br from-amber-200 to-amber-400 rounded-full flex items-center justify-center shadow-2xl">
                                <span class="text-[120px]">🥜</span>
                            </div>
                            <div class="absolute -top-4 -right-4 bg-white rounded-2xl p-3 shadow-xl">
                                <div class="flex items-center gap-2">
                                    <span class="text-amber-500 font-bold">★ 4.9</span>
                                    <span class="text-gray-500 text-sm">Rating</span>
                                </div>
                            </div>
                            <div class="absolute -bottom-4 -left-4 bg-white rounded-2xl p-3 shadow-xl">
                                <div class="flex items-center gap-2">
                                    <span class="text-xl">🚚</span>
                                    <span class="text-gray-700 text-sm font-medium">Free Delivery</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Trust Badges -->
        <section class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div v-for="feature in features" :key="feature.title" class="flex items-center gap-3">
                        <div class="w-11 h-11 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center shrink-0">
                            <svg v-if="feature.icon === 'truck'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                            <svg v-if="feature.icon === 'shield'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <svg v-if="feature.icon === 'refresh'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <svg v-if="feature.icon === 'lock'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ feature.title }}</p>
                            <p class="text-xs text-gray-500">{{ feature.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section v-if="categories.length > 0" class="py-16 lg:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between mb-10">
                    <div>
                        <p class="text-amber-600 font-semibold text-sm uppercase tracking-wider mb-2">Browse by Category</p>
                        <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">Shop by Category</h2>
                    </div>
                    <Link href="/products" class="hidden sm:inline-flex items-center gap-1 text-amber-600 font-semibold hover:text-amber-700 transition-colors">
                        View All
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-4 lg:gap-6">
                    <CategoryCard v-for="category in categories" :key="category.id" :category="category" />
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section v-if="featuredProducts.length > 0" class="py-16 lg:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between mb-10">
                    <div>
                        <p class="text-amber-600 font-semibold text-sm uppercase tracking-wider mb-2">Handpicked for You</p>
                        <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">Featured Snacks</h2>
                    </div>
                    <Link href="/products?featured=1" class="hidden sm:inline-flex items-center gap-1 text-amber-600 font-semibold hover:text-amber-700 transition-colors">
                        View All
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                    <ProductCard v-for="product in featuredProducts" :key="product.id" :product="product" />
                </div>
            </div>
        </section>

        <!-- Why Snackzar Banner -->
        <section class="py-16 lg:py-20 bg-gradient-to-r from-amber-600 to-amber-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">Why Choose Snackzar?</h2>
                        <div class="space-y-5">
                            <div class="flex gap-4">
                                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-white font-semibold text-lg">Direct from Farmers</h3>
                                    <p class="text-amber-100 text-sm">We source all our Makhana and snacks directly from farmers across Bihar, ensuring the freshest quality and fair prices.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-white font-semibold text-lg">Premium Quality</h3>
                                    <p class="text-amber-100 text-sm">Every batch is carefully inspected and quality-tested. Only the best makes it to your table.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-white font-semibold text-lg">Pan-India Delivery</h3>
                                    <p class="text-amber-100 text-sm">We deliver to every corner of India. Free shipping on orders above ₹499 with Shiprocket integration.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-center">
                                <p class="text-4xl font-bold text-white">{{ stats.products || '50' }}+</p>
                                <p class="text-amber-200 text-sm mt-1">Products</p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-center">
                                <p class="text-4xl font-bold text-white">{{ stats.categories || '10' }}+</p>
                                <p class="text-amber-200 text-sm mt-1">Categories</p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-center">
                                <p class="text-4xl font-bold text-white">{{ stats.happy_customers || '500' }}+</p>
                                <p class="text-amber-200 text-sm mt-1">Customers</p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-center">
                                <p class="text-4xl font-bold text-white">4.8</p>
                                <p class="text-amber-200 text-sm mt-1">Avg Rating</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- New Arrivals -->
        <section v-if="newArrivals.length > 0" class="py-16 lg:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between mb-10">
                    <div>
                        <p class="text-amber-600 font-semibold text-sm uppercase tracking-wider mb-2">Just Landed</p>
                        <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">New Arrivals</h2>
                    </div>
                    <Link href="/products?sort=latest" class="hidden sm:inline-flex items-center gap-1 text-amber-600 font-semibold hover:text-amber-700 transition-colors">
                        View All
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                    <ProductCard v-for="product in newArrivals" :key="product.id" :product="product" />
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="py-16 lg:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <p class="text-amber-600 font-semibold text-sm uppercase tracking-wider mb-2">Customer Love</p>
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">What Our Customers Say</h2>
                </div>

                <!-- Use recentReviews if available, fallback to hardcoded testimonials -->
                <div v-if="recentReviews.length > 0" class="grid md:grid-cols-3 gap-6">
                    <div v-for="review in recentReviews.slice(0, 3)" :key="review.id" class="bg-amber-50 rounded-2xl p-6 border border-amber-100">
                        <div class="flex mb-3">
                            <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= review.rating ? 'text-amber-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <p class="text-gray-700 text-sm leading-relaxed mb-4">"{{ review.comment }}"</p>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-amber-200 text-amber-800 rounded-full flex items-center justify-center text-sm font-bold">
                                {{ review.user?.name?.charAt(0)?.toUpperCase() }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ review.user?.name }}</p>
                                <p v-if="review.product" class="text-xs text-gray-500">on {{ review.product.name }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="grid md:grid-cols-3 gap-6">
                    <div v-for="testimonial in testimonials" :key="testimonial.name" class="bg-amber-50 rounded-2xl p-6 border border-amber-100">
                        <div class="flex mb-3">
                            <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= testimonial.rating ? 'text-amber-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <p class="text-gray-700 text-sm leading-relaxed mb-4">"{{ testimonial.text }}"</p>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-amber-200 text-amber-800 rounded-full flex items-center justify-center text-sm font-bold">
                                {{ testimonial.name.charAt(0) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ testimonial.name }}</p>
                                <p class="text-xs text-gray-500">{{ testimonial.location }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA / Newsletter -->
        <section class="py-16 lg:py-20 bg-amber-50">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
                    Ready to Taste Bihar's Finest?
                </h2>
                <p class="text-gray-600 text-lg mb-8 max-w-xl mx-auto">
                    Join thousands of snack lovers who trust Snackzar for authentic, premium-quality Bihari snacks delivered fresh.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link href="/products" class="bg-amber-600 text-white px-8 py-3.5 rounded-xl font-bold text-lg hover:bg-amber-700 transition-all shadow-lg shadow-amber-600/25">
                        Browse All Products
                    </Link>
                    <Link href="/register" class="border-2 border-amber-600 text-amber-700 px-8 py-3.5 rounded-xl font-semibold text-lg hover:bg-amber-100 transition-all">
                        Create Account
                    </Link>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
