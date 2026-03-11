<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const mobileMenuOpen = ref(false);

const navigation = [
    { name: 'Home', href: '/' },
    { name: 'Products', href: '/products' },
    { name: 'About', href: '/about' },
    { name: 'Contact', href: '/contact' },
];
</script>

<template>
    <nav class="bg-white shadow-sm sticky top-0 z-50 border-b border-amber-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <Link href="/" class="flex items-center gap-2">
                        <span class="text-2xl">🥜</span>
                        <span class="text-xl font-bold text-amber-900 tracking-tight">Snackzar</span>
                    </Link>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center gap-8">
                    <Link
                        v-for="item in navigation"
                        :key="item.name"
                        :href="item.href"
                        class="text-sm font-medium text-gray-700 hover:text-amber-600 transition-colors relative py-1"
                        :class="{ 'text-amber-600': $page.url === item.href || $page.url.startsWith(item.href + '?') || (item.href !== '/' && $page.url.startsWith(item.href)) }"
                    >
                        {{ item.name }}
                        <span
                            v-if="$page.url === item.href || (item.href !== '/' && $page.url.startsWith(item.href))"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-amber-500 rounded-full"
                        ></span>
                    </Link>
                </div>

                <!-- Right side: Search + Auth -->
                <div class="hidden md:flex items-center gap-4">
                    <Link
                        href="/products?search="
                        class="p-2 text-gray-500 hover:text-amber-600 transition-colors"
                        title="Search"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </Link>

                    <template v-if="user">
                        <Link
                            href="/cart"
                            class="p-2 text-gray-500 hover:text-amber-600 transition-colors relative"
                            title="Cart"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                            </svg>
                        </Link>
                        <div class="relative group">
                            <button class="flex items-center gap-2 p-2 text-gray-700 hover:text-amber-600 transition-colors">
                                <span class="w-8 h-8 bg-amber-100 text-amber-700 rounded-full flex items-center justify-center text-sm font-semibold">
                                    {{ user.name?.charAt(0)?.toUpperCase() }}
                                </span>
                            </button>
                            <div class="absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 py-2">
                                <Link href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50">My Profile</Link>
                                <Link href="/orders" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50">My Orders</Link>
                                <Link href="/wishlist" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50">Wishlist</Link>
                                <hr class="my-1 border-gray-100" />
                                <Link href="/logout" method="post" as="button" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Logout</Link>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <Link href="/login" class="text-sm font-medium text-gray-700 hover:text-amber-600 transition-colors">
                            Sign In
                        </Link>
                        <Link href="/register" class="bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-amber-700 transition-colors shadow-sm">
                            Get Started
                        </Link>
                    </template>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="p-2 text-gray-700 hover:text-amber-600"
                    >
                        <svg v-if="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div v-if="mobileMenuOpen" class="md:hidden border-t border-amber-100 bg-white">
            <div class="px-4 pt-2 pb-4 space-y-1">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:bg-amber-50 hover:text-amber-600"
                    @click="mobileMenuOpen = false"
                >
                    {{ item.name }}
                </Link>
                <hr class="my-2 border-gray-100" />
                <template v-if="user">
                    <Link href="/profile" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:bg-amber-50">Profile</Link>
                    <Link href="/orders" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:bg-amber-50">Orders</Link>
                    <Link href="/logout" method="post" as="button" class="block w-full text-left px-3 py-2 rounded-lg text-base font-medium text-red-600 hover:bg-red-50">Logout</Link>
                </template>
                <template v-else>
                    <Link href="/login" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:bg-amber-50">Sign In</Link>
                    <Link href="/register" class="block px-3 py-2 rounded-lg text-base font-medium text-white bg-amber-600 hover:bg-amber-700 text-center">Get Started</Link>
                </template>
            </div>
        </div>
    </nav>
</template>
