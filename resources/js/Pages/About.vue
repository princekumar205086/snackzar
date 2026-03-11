<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const team = [
    { name: 'Snackzar Team', role: 'Founders', bio: 'A team of passionate food lovers from Bihar, dedicated to bringing authentic Bihari snacks to the world.' },
];

const values = [
    { icon: 'heart', title: 'Authenticity', description: 'We source directly from local farmers in Bihar, ensuring every product carries the authentic taste of our homeland.' },
    { icon: 'leaf', title: 'Quality First', description: 'Rigorous quality checks at every stage — from sourcing raw materials to packaging and delivery.' },
    { icon: 'users', title: 'Farmer Empowerment', description: 'We work directly with farmers, ensuring fair prices and sustainable livelihoods for the communities that grow our ingredients.' },
    { icon: 'globe', title: 'Pan-India Reach', description: 'From Bihar to every corner of India, we deliver the taste of home to millions of food lovers.' },
];
</script>

<template>
    <AppLayout title="About Us">
        <!-- Hero -->
        <section class="bg-gradient-to-br from-amber-800 to-amber-950 py-20 lg:py-28">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl lg:text-5xl font-bold text-white mb-6">Our Story</h1>
                <p class="text-lg text-amber-200 leading-relaxed max-w-2xl mx-auto">
                    Snackzar was born from a simple desire — to share the incredible flavors of Bihar with the rest of India. What started as a dream to bring authentic Makhana to every household has grown into a thriving marketplace for premium Bihari snacks.
                </p>
            </div>
        </section>

        <!-- Mission -->
        <section class="py-16 lg:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <p class="text-amber-600 font-semibold text-sm uppercase tracking-wider mb-2">Our Mission</p>
                        <h2 class="text-3xl font-bold text-gray-900 mb-6">Bringing Bihar's Best to Your Doorstep</h2>
                        <p class="text-gray-600 leading-relaxed mb-4">
                            Bihar is home to some of India's finest snacks — from the world-renowned Makhana (fox nuts) of Mithila to traditional namkeens, sattu-based snacks, and artisanal sweets. Yet, these incredible flavors have remain largely underrepresented in the mainstream market.
                        </p>
                        <p class="text-gray-600 leading-relaxed mb-4">
                            Snackzar bridges this gap. We connect local farmers and artisanal producers directly with consumers across India, ensuring fresh quality, fair pricing, and authentic taste in every bite.
                        </p>
                        <p class="text-gray-600 leading-relaxed">
                            Our marketplace empowers small sellers and local businesses from Bihar to reach a nationwide audience, while customers enjoy a curated selection of premium, verified snacks delivered right to their homes.
                        </p>
                    </div>
                    <div class="bg-gradient-to-br from-amber-100 to-amber-200 rounded-3xl p-12 flex items-center justify-center">
                        <div class="text-center">
                            <span class="text-8xl block mb-4">🥜</span>
                            <p class="text-amber-800 font-bold text-xl">Premium Quality</p>
                            <p class="text-amber-700 text-sm">Straight from Bihar</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Values -->
        <section class="py-16 lg:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <p class="text-amber-600 font-semibold text-sm uppercase tracking-wider mb-2">What Drives Us</p>
                    <h2 class="text-3xl font-bold text-gray-900">Our Core Values</h2>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="value in values" :key="value.title" class="bg-amber-50 rounded-2xl p-6 border border-amber-100 text-center">
                        <div class="w-14 h-14 bg-amber-200 text-amber-700 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <svg v-if="value.icon === 'heart'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                            <svg v-if="value.icon === 'leaf'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            <svg v-if="value.icon === 'users'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            <svg v-if="value.icon === 'globe'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ value.title }}</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ value.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Numbers -->
        <section class="py-16 lg:py-20 bg-gradient-to-r from-amber-600 to-amber-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                    <div>
                        <p class="text-4xl lg:text-5xl font-bold text-white">50+</p>
                        <p class="text-amber-200 text-sm mt-1">Products</p>
                    </div>
                    <div>
                        <p class="text-4xl lg:text-5xl font-bold text-white">500+</p>
                        <p class="text-amber-200 text-sm mt-1">Happy Customers</p>
                    </div>
                    <div>
                        <p class="text-4xl lg:text-5xl font-bold text-white">10+</p>
                        <p class="text-amber-200 text-sm mt-1">Sellers</p>
                    </div>
                    <div>
                        <p class="text-4xl lg:text-5xl font-bold text-white">28+</p>
                        <p class="text-amber-200 text-sm mt-1">States Delivered</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-16 lg:py-20">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Join the Snackzar Family</h2>
                <p class="text-gray-600 text-lg mb-8">Whether you're a snack lover or a seller from Bihar, there's a place for you at Snackzar.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link href="/products" class="bg-amber-600 text-white px-8 py-3.5 rounded-xl font-bold text-lg hover:bg-amber-700 transition-all">
                        Shop Now
                    </Link>
                    <Link href="/contact" class="border-2 border-amber-600 text-amber-700 px-8 py-3.5 rounded-xl font-semibold text-lg hover:bg-amber-50 transition-all">
                        Contact Us
                    </Link>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
