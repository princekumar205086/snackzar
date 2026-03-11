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

const currentSlide = ref(0);
const slides = [
    {
        badge: 'Fresh from Bihar',
        title: 'Premium Makhana',
        highlight: '& Bihari Snacks',
        desc: 'Hand-picked Makhana, traditional namkeen, and artisanal snacks sourced directly from local farmers.',
    },
    {
        badge: 'Best Seller',
        title: 'Roasted & Flavoured',
        highlight: 'Makhana Range',
        desc: 'Try our signature Peri Peri, Cheese & Herb, and Classic Salt varieties — crispy, light and healthy.',
    },
    {
        badge: 'New Arrivals',
        title: 'Authentic Sattu &',
        highlight: 'Traditional Sweets',
        desc: 'Chana Sattu, Tilkut, Khaja and more — celebrating the rich culinary heritage of Bihar.',
    },
];

let slideTimer = null;
const nextSlide = () => { currentSlide.value = (currentSlide.value + 1) % slides.length; };
const prevSlide = () => { currentSlide.value = (currentSlide.value - 1 + slides.length) % slides.length; };
const goToSlide = (i) => { currentSlide.value = i; resetTimer(); };
function resetTimer() {
    clearInterval(slideTimer);
    slideTimer = setInterval(nextSlide, 5000);
}

const features = [
    { icon: '🚚', title: 'Free Delivery', description: 'On orders above ₹499' },
    { icon: '✅', title: '100% Authentic', description: 'Sourced from Bihar' },
    { icon: '🔄', title: 'Easy Returns', description: '7-day hassle-free' },
    { icon: '🔒', title: 'Secure Payment', description: 'Razorpay secured' },
];

const testimonials = [
    { name: 'Priya Sharma', location: 'Delhi', text: "The Makhana from Snackzar is the crunchiest I've ever had! Truly authentic Bihari taste.", rating: 5 },
    { name: 'Rahul Kumar', location: 'Mumbai', text: 'Fast delivery and excellent packaging. The snacks were fresh and delicious. My family loved them!', rating: 5 },
    { name: 'Anita Devi', location: 'Bangalore', text: 'Finally found a place that delivers real Bihari snacks. The Sattu and Makhana are outstanding quality.', rating: 4 },
];

const showcaseItems = [
    { emoji: '🥜', name: 'Makhana', price: '₹199' },
    { emoji: '🍯', name: 'Sattu', price: '₹149' },
    { emoji: '🍿', name: 'Namkeen', price: '₹89' },
    { emoji: '🧁', name: 'Tilkut', price: '₹129' },
];

onMounted(() => { slideTimer = setInterval(nextSlide, 5000); });
onUnmounted(() => clearInterval(slideTimer));
</script>

<template>
    <AppLayout title="Premium Bihari Snacks - Makhana & More">

        <!-- Hero Section -->
        <section class="relative bg-[#FFF8ED] overflow-hidden">
            <!-- Decorative blobs -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute -top-24 -right-20 w-96 h-96 bg-amber-300/25 rounded-full blur-3xl"></div>
                <div class="absolute top-1/2 -left-16 w-72 h-72 bg-orange-200/20 rounded-full blur-2xl"></div>
                <div class="absolute bottom-0 right-1/3 w-48 h-48 bg-yellow-200/30 rounded-full blur-2xl"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-6 lg:pt-16 lg:pb-10 relative z-10">
                <div class="grid lg:grid-cols-2 gap-8 lg:gap-16 items-center">

                    <!-- Left: Text (shown second on mobile for visual-first feel) -->
                    <div class="order-2 lg:order-1">
                        <Transition
                            mode="out-in"
                            enter-active-class="transition-all duration-500"
                            enter-from-class="opacity-0 translate-y-3"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition-all duration-250"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <div :key="currentSlide">
                                <div class="inline-flex items-center gap-2 bg-amber-100 text-amber-800 text-xs font-semibold px-3.5 py-1.5 rounded-full mb-5 border border-amber-200">
                                    <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                                    {{ slides[currentSlide].badge }}
                                </div>
                                <h1 class="text-4xl sm:text-5xl xl:text-6xl font-extrabold text-gray-900 leading-[1.1] mb-4 tracking-tight">
                                    {{ slides[currentSlide].title }}<br>
                                    <span class="text-amber-600">{{ slides[currentSlide].highlight }}</span>
                                </h1>
                                <p class="text-base sm:text-lg text-gray-600 leading-relaxed mb-7 max-w-lg">
                                    {{ slides[currentSlide].desc }}
                                </p>
                            </div>
                        </Transition>

                        <!-- CTAs -->
                        <div class="flex flex-wrap gap-3 mb-9">
                            <Link href="/products" class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white px-6 py-3.5 rounded-xl font-bold text-base transition-all shadow-lg shadow-amber-300/60">
                                Shop Now
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </Link>
                            <Link href="/products?featured=1" class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-6 py-3.5 rounded-xl font-semibold text-base hover:border-amber-300 hover:text-amber-700 transition-all">
                                View Offers
                            </Link>
                        </div>

                        <!-- Stats row -->
                        <div class="flex items-center gap-6 flex-wrap">
                            <div>
                                <p class="text-2xl font-extrabold text-gray-900 leading-none">{{ stats.products || '50' }}+</p>
                                <p class="text-xs text-gray-500 font-medium mt-0.5">Products</p>
                            </div>
                            <div class="w-px h-9 bg-gray-200"></div>
                            <div>
                                <p class="text-2xl font-extrabold text-gray-900 leading-none">{{ stats.happy_customers || '500' }}+</p>
                                <p class="text-xs text-gray-500 font-medium mt-0.5">Happy Customers</p>
                            </div>
                            <div class="w-px h-9 bg-gray-200"></div>
                            <div>
                                <p class="text-2xl font-extrabold text-gray-900 leading-none">4.8 <span class="text-amber-500 text-lg">★</span></p>
                                <p class="text-xs text-gray-500 font-medium mt-0.5">Avg Rating</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Product Showcase Visual -->
                    <div class="order-1 lg:order-2 flex items-center justify-center">
                        <div class="relative w-full max-w-xs sm:max-w-sm lg:max-w-md">
                            <!-- Gradient border card -->
                            <div class="relative rounded-3xl p-[2px] shadow-2xl shadow-amber-200/50">
                                <div class="absolute inset-0 rounded-3xl bg-gradient-to-br from-amber-400 via-orange-400 to-amber-500"></div>
                                <div class="relative bg-white rounded-[22px] overflow-hidden">
                                    <div class="bg-gradient-to-br from-amber-50 to-orange-50/80 p-6 sm:p-8">
                                        <!-- Product mini-grid -->
                                        <div class="grid grid-cols-2 gap-3">
                                            <div
                                                v-for="item in showcaseItems"
                                                :key="item.name"
                                                class="bg-white rounded-2xl shadow-sm p-4 flex flex-col items-center gap-1.5 border border-amber-50/80 hover:shadow-md transition-shadow"
                                            >
                                                <span class="text-3xl sm:text-4xl">{{ item.emoji }}</span>
                                                <p class="text-xs font-bold text-gray-700">{{ item.name }}</p>
                                                <p class="text-xs text-amber-600 font-semibold">{{ item.price }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Floating badge: Authentic -->
                            <div class="absolute -top-4 -left-4 bg-white rounded-2xl px-4 py-2.5 shadow-xl border border-gray-100 flex items-center gap-2.5">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-900 leading-none">100% Authentic</p>
                                    <p class="text-[10px] text-gray-500 mt-0.5">Direct from Bihar</p>
                                </div>
                            </div>

                            <!-- Floating badge: Delivery -->
                            <div class="absolute -bottom-4 -right-4 bg-white rounded-2xl px-4 py-2.5 shadow-xl border border-gray-100 flex items-center gap-2.5">
                                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-900 leading-none">Free Delivery</p>
                                    <p class="text-[10px] text-gray-500 mt-0.5">Orders above ₹499</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide dots -->
            <div class="flex justify-center gap-2 py-7">
                <button
                    v-for="(_, i) in slides"
                    :key="i"
                    @click="goToSlide(i)"
                    class="rounded-full transition-all duration-300"
                    :class="i === currentSlide ? 'w-6 h-2 bg-amber-600' : 'w-2 h-2 bg-gray-300 hover:bg-amber-400'"
                ></button>
            </div>
        </section>

        <!-- Trust Badges -->
        <section class="bg-white border-y border-gray-100 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex overflow-x-auto scrollbar-hide divide-x divide-gray-100 lg:grid lg:grid-cols-4">
                    <div v-for="feature in features" :key="feature.title" class="flex items-center gap-3 py-4 px-5 shrink-0 lg:shrink">
                        <span class="text-xl">{{ feature.icon }}</span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 whitespace-nowrap">{{ feature.title }}</p>
                            <p class="text-xs text-gray-500 whitespace-nowrap">{{ feature.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section v-if="categories.length > 0" class="py-10 lg:py-14 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between mb-6">
                    <div>
                        <p class="text-xs font-semibold text-amber-600 uppercase tracking-widest mb-1">Browse</p>
                        <h2 class="text-2xl lg:text-3xl font-bold text-gray-900">Shop by Category</h2>
                    </div>
                    <Link href="/products" class="flex items-center gap-1.5 text-amber-600 font-semibold text-sm hover:text-amber-700 transition-colors border border-amber-200 hover:border-amber-400 px-4 py-2 rounded-full whitespace-nowrap">
                        View All
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                </div>
                <div class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide lg:grid lg:grid-cols-5 xl:grid-cols-6 lg:gap-5">
                    <CategoryCard v-for="category in categories" :key="category.id" :category="category" />
                </div>
            </div>
        </section>

        <!-- Featured Products - horizontal scroll on mobile, grid on desktop -->
        <section v-if="featuredProducts.length > 0" class="py-10 lg:py-14 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between mb-6">
                    <div>
                        <p class="text-xs font-semibold text-amber-600 uppercase tracking-widest mb-1">Popular</p>
                        <h2 class="text-2xl lg:text-3xl font-bold text-gray-900">Best Deals</h2>
                    </div>
                    <Link href="/products?featured=1" class="flex items-center gap-1.5 text-amber-600 font-semibold text-sm hover:text-amber-700 transition-colors border border-amber-200 hover:border-amber-400 px-4 py-2 rounded-full whitespace-nowrap">
                        View All
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                </div>
                <div class="flex gap-3 overflow-x-auto pb-4 scrollbar-hide lg:grid lg:grid-cols-4 xl:grid-cols-5 lg:gap-5 lg:overflow-visible lg:pb-0">
                    <div v-for="product in featuredProducts" :key="product.id" class="shrink-0 w-44 sm:w-52 lg:w-auto">
                        <ProductCard :product="product" />
                    </div>
                </div>
            </div>
        </section>

        <!-- New Arrivals - horizontal scroll on mobile, grid on desktop -->
        <section v-if="newArrivals.length > 0" class="py-10 lg:py-14 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between mb-6">
                    <div>
                        <p class="text-xs font-semibold text-amber-600 uppercase tracking-widest mb-1">Just In</p>
                        <h2 class="text-2xl lg:text-3xl font-bold text-gray-900">New Arrivals</h2>
                    </div>
                    <Link href="/products?sort=latest" class="flex items-center gap-1.5 text-amber-600 font-semibold text-sm hover:text-amber-700 transition-colors border border-amber-200 hover:border-amber-400 px-4 py-2 rounded-full whitespace-nowrap">
                        View All
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                </div>
                <div class="flex gap-3 overflow-x-auto pb-4 scrollbar-hide lg:grid lg:grid-cols-4 xl:grid-cols-5 lg:gap-5 lg:overflow-visible lg:pb-0">
                    <div v-for="product in newArrivals" :key="product.id" class="shrink-0 w-44 sm:w-52 lg:w-auto">
                        <ProductCard :product="product" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Snackzar -->
        <section class="py-14 lg:py-20 bg-gradient-to-br from-amber-500 via-amber-600 to-orange-600 relative overflow-hidden">
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/10 rounded-full"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/4"></div>
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
                    <div>
                        <p class="text-amber-200 text-xs font-bold uppercase tracking-widest mb-3">Why Choose Us</p>
                        <h2 class="text-3xl lg:text-4xl font-bold text-white mb-8 leading-tight">Bihar's Finest Snacks,<br>Delivered to Your Door</h2>
                        <div class="space-y-5">
                            <div v-for="(item, i) in [
                                { title: 'Direct from Farmers', desc: 'We source all our Makhana and snacks directly from farmers across Bihar, ensuring the freshest quality and fair prices.' },
                                { title: 'Premium Quality', desc: 'Every batch is carefully inspected and quality-tested. Only the best makes it to your table.' },
                                { title: 'Pan-India Delivery', desc: 'We deliver to every corner of India. Free shipping on orders above ₹499 with Shiprocket integration.' },
                            ]" :key="i" class="flex gap-4 items-start">
                                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-white font-bold text-base mb-0.5">{{ item.title }}</h3>
                                    <p class="text-amber-100 text-sm leading-relaxed">{{ item.desc }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div v-for="(stat, i) in [
                            { value: stats.products || '50', label: 'Products', suffix: '+' },
                            { value: stats.categories || '10', label: 'Categories', suffix: '+' },
                            { value: stats.happy_customers || '500', label: 'Customers', suffix: '+' },
                            { value: '4.8', label: 'Avg Rating', suffix: ' ★' },
                        ]" :key="i" class="bg-white/10 backdrop-blur-sm rounded-2xl p-5 text-center border border-white/10">
                            <p class="text-3xl lg:text-4xl font-extrabold text-white">{{ stat.value }}<span class="text-amber-300 ml-0.5 text-xl">{{ stat.suffix }}</span></p>
                            <p class="text-amber-200 text-xs font-medium mt-1">{{ stat.label }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="py-14 lg:py-20 bg-[#FFF8ED]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10">
                    <p class="text-amber-600 font-semibold text-xs uppercase tracking-widest mb-2">Customer Love</p>
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900">What Our Customers Say</h2>
                </div>

                <!-- horizontal scroll on mobile, 3-col grid on desktop -->
                <template v-if="recentReviews.length > 0">
                    <div class="flex gap-4 overflow-x-auto pb-4 scrollbar-hide lg:grid lg:grid-cols-3 lg:gap-6 lg:overflow-visible lg:pb-0">
                        <div v-for="review in recentReviews.slice(0, 3)" :key="review.id" class="shrink-0 w-72 sm:w-80 lg:w-auto bg-white rounded-2xl p-6 shadow-sm border border-amber-50">
                            <div class="flex mb-3">
                                <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= review.rating ? 'text-amber-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            <p class="text-gray-700 text-sm leading-relaxed mb-4">"{{ review.comment }}"</p>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-amber-100 text-amber-700 rounded-full flex items-center justify-center text-sm font-bold">
                                    {{ review.user?.name?.charAt(0)?.toUpperCase() }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ review.user?.name }}</p>
                                    <p v-if="review.product" class="text-xs text-gray-500">on {{ review.product.name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div v-else class="flex gap-4 overflow-x-auto pb-4 scrollbar-hide lg:grid lg:grid-cols-3 lg:gap-6 lg:overflow-visible lg:pb-0">
                    <div v-for="testimonial in testimonials" :key="testimonial.name" class="shrink-0 w-72 sm:w-80 lg:w-auto bg-white rounded-2xl p-6 shadow-sm border border-amber-50">
                        <div class="flex mb-3">
                            <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= testimonial.rating ? 'text-amber-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <p class="text-gray-700 text-sm leading-relaxed mb-4">"{{ testimonial.text }}"</p>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-amber-100 text-amber-700 rounded-full flex items-center justify-center text-sm font-bold">
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

        <!-- CTA -->
        <section class="py-14 lg:py-20 bg-white">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-3xl p-8 md:p-12 border border-amber-100">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-900 mb-3 leading-tight">
                        Ready to Taste Bihar's Finest?
                    </h2>
                    <p class="text-gray-600 text-base mb-8 max-w-md mx-auto">
                        Join thousands of snack lovers who trust Snackzar for authentic, premium-quality Bihari snacks delivered fresh.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <Link href="/products" class="inline-flex items-center justify-center gap-2 bg-amber-600 text-white px-8 py-3.5 rounded-xl font-bold text-base hover:bg-amber-700 active:scale-95 transition-all shadow-lg shadow-amber-200">
                            Browse All Products
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </Link>
                        <Link href="/register" class="inline-flex items-center justify-center bg-white border-2 border-amber-200 text-amber-700 px-8 py-3.5 rounded-xl font-semibold text-base hover:border-amber-400 hover:bg-amber-50 transition-all">
                            Create Free Account
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
