<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';
import CategoryCard from '@/Components/CategoryCard.vue';
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

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

// Carousel
const currentSlide = ref(0);
const slides = [
    {
        tag: 'Fresh from Bihar',
        title: 'Premium Makhana',
        highlight: '& Bihari Snacks',
        desc: 'Hand-picked Makhana, traditional namkeen, and artisanal snacks sourced directly from local farmers.',
        bg: 'from-amber-50 to-orange-100',
        accent: 'bg-amber-600',
        emoji: '🥜',
    },
    {
        tag: 'Best Seller',
        title: 'Roasted & Flavoured',
        highlight: 'Makhana Range',
        desc: 'Try our signature Peri Peri, Cheese & Herb, and Classic Salt varieties — crispy, light and healthy.',
        bg: 'from-orange-50 to-amber-100',
        accent: 'bg-orange-600',
        emoji: '🍿',
    },
    {
        tag: 'New Arrivals',
        title: 'Authentic Sattu &',
        highlight: 'Traditional Sweets',
        desc: 'Chana Sattu, Tilkut, Khaja and more — celebrating the rich culinary heritage of Bihar.',
        bg: 'from-yellow-50 to-amber-100',
        accent: 'bg-yellow-600',
        emoji: '🍯',
    },
];

let slideTimer = null;

const nextSlide = () => { currentSlide.value = (currentSlide.value + 1) % slides.length; };
const prevSlide = () => { currentSlide.value = (currentSlide.value - 1 + slides.length) % slides.length; };
const goToSlide = (i) => { currentSlide.value = i; resetTimer(); };

function resetTimer() {
    clearInterval(slideTimer);
    slideTimer = setInterval(nextSlide, 4000);
}

const features = [
    { icon: '🚚', title: 'Free Delivery', description: 'On orders above ₹499' },
    { icon: '✅', title: '100% Authentic', description: 'Sourced directly from Bihar' },
    { icon: '🔄', title: 'Easy Returns', description: '7-day hassle-free returns' },
    { icon: '🔒', title: 'Secure Payment', description: 'Razorpay secured checkout' },
];

const testimonials = [
    { name: 'Priya Sharma', location: 'Delhi', text: "The Makhana from Snackzar is the crunchiest I've ever had! Truly authentic Bihari taste.", rating: 5 },
    { name: 'Rahul Kumar', location: 'Mumbai', text: 'Fast delivery and excellent packaging. The snacks were fresh and delicious. My family loved them!', rating: 5 },
    { name: 'Anita Devi', location: 'Bangalore', text: 'Finally found a place that delivers real Bihari snacks. The Sattu and Makhana are outstanding quality.', rating: 4 },
];

onMounted(() => { slideTimer = setInterval(nextSlide, 4000); });
onUnmounted(() => clearInterval(slideTimer));
</script>

<template>
    <AppLayout title="Premium Bihari Snacks - Makhana & More">

        <!-- Hero Carousel -->
        <section class="relative overflow-hidden bg-white">
            <div class="relative">
                <!-- Slides -->
                <div class="overflow-hidden">
                    <Transition
                        enter-active-class="transition-all duration-500"
                        enter-from-class="opacity-0 translate-x-8"
                        enter-to-class="opacity-100 translate-x-0"
                        leave-active-class="transition-all duration-300 absolute inset-0"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                        mode="out-in"
                    >
                        <div :key="currentSlide" :class="`bg-gradient-to-br ${slides[currentSlide].bg}`" class="w-full">
                            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-16">
                                <div class="grid lg:grid-cols-2 gap-8 lg:gap-16 items-center">
                                    <!-- Text -->
                                    <div>
                                        <span :class="slides[currentSlide].accent" class="inline-flex items-center gap-1.5 text-white text-xs font-semibold px-3 py-1.5 rounded-full mb-5">
                                            <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                                            {{ slides[currentSlide].tag }}
                                        </span>
                                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-4">
                                            {{ slides[currentSlide].title }}<br>
                                            <span class="text-amber-600">{{ slides[currentSlide].highlight }}</span>
                                        </h1>
                                        <p class="text-base lg:text-lg text-gray-600 leading-relaxed mb-8 max-w-lg">
                                            {{ slides[currentSlide].desc }}
                                        </p>
                                        <div class="flex flex-wrap gap-3">
                                            <Link href="/products" class="bg-amber-600 hover:bg-amber-700 text-white px-7 py-3.5 rounded-full font-bold text-base transition-all shadow-lg shadow-amber-200 hover:shadow-amber-300">
                                                Shop Now
                                            </Link>
                                            <Link href="/products?featured=1" class="bg-white border-2 border-amber-200 text-amber-700 px-7 py-3.5 rounded-full font-semibold text-base hover:border-amber-400 hover:bg-amber-50 transition-all">
                                                View Featured
                                            </Link>
                                        </div>
                                    </div>

                                    <!-- Visual -->
                                    <div class="flex items-center justify-center">
                                        <div class="relative">
                                            <div class="w-72 h-72 lg:w-80 lg:h-80 bg-white/60 backdrop-blur-sm rounded-3xl flex items-center justify-center shadow-2xl border border-white/80">
                                                <span class="text-[110px] drop-shadow-lg select-none">{{ slides[currentSlide].emoji }}</span>
                                            </div>
                                            <!-- Floating badge -->
                                            <div class="absolute -top-3 -right-3 bg-white rounded-2xl px-4 py-2.5 shadow-xl border border-gray-100">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-amber-500 font-bold text-sm">★ 4.9</span>
                                                    <span class="text-gray-500 text-xs">Rating</span>
                                                </div>
                                            </div>
                                            <div class="absolute -bottom-3 -left-3 bg-white rounded-2xl px-4 py-2.5 shadow-xl border border-gray-100">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-lg">🚚</span>
                                                    <span class="text-gray-700 text-xs font-semibold">Free Delivery</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>

                <!-- Controls -->
                <button @click="prevSlide" class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 hover:bg-white rounded-full shadow-lg flex items-center justify-center text-gray-700 hover:text-amber-700 transition-all border border-gray-100 z-10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button @click="nextSlide" class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 hover:bg-white rounded-full shadow-lg flex items-center justify-center text-gray-700 hover:text-amber-700 transition-all border border-gray-100 z-10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>

                <!-- Dots -->
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-2 z-10">
                    <button
                        v-for="(_, i) in slides"
                        :key="i"
                        @click="goToSlide(i)"
                        class="rounded-full transition-all duration-300"
                        :class="i === currentSlide ? 'w-6 h-2.5 bg-amber-600' : 'w-2.5 h-2.5 bg-gray-300 hover:bg-amber-400'"
                    ></button>
                </div>
            </div>
        </section>

        <!-- Trust Badges -->
        <section class="bg-white border-y border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div v-for="feature in features" :key="feature.title" class="flex items-center gap-3 py-1">
                        <span class="text-2xl">{{ feature.icon }}</span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ feature.title }}</p>
                            <p class="text-xs text-gray-500">{{ feature.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section v-if="categories.length > 0" class="py-10 lg:py-14 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900">Shop by Category</h2>
                    <Link href="/products" class="hidden sm:flex items-center gap-1 text-amber-600 font-semibold text-sm hover:text-amber-700 transition-colors">
                        View All
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                </div>
                <!-- Scrollable category row -->
                <div class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide lg:grid lg:grid-cols-5 xl:grid-cols-6 lg:gap-5">
                    <CategoryCard v-for="category in categories" :key="category.id" :category="category" />
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section v-if="featuredProducts.length > 0" class="py-10 lg:py-14 bg-white border-t border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900">Best Deals</h2>
                    <Link href="/products?featured=1" class="hidden sm:flex items-center gap-1 text-amber-600 font-semibold text-sm hover:text-amber-700 transition-colors">
                        View All →
                    </Link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 lg:gap-5">
                    <ProductCard v-for="product in featuredProducts" :key="product.id" :product="product" />
                </div>
            </div>
        </section>

        <!-- New Arrivals -->
        <section v-if="newArrivals.length > 0" class="py-10 lg:py-14 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900">New Arrivals</h2>
                    <Link href="/products?sort=latest" class="hidden sm:flex items-center gap-1 text-amber-600 font-semibold text-sm hover:text-amber-700 transition-colors">
                        View All →
                    </Link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 lg:gap-5">
                    <ProductCard v-for="product in newArrivals" :key="product.id" :product="product" />
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
