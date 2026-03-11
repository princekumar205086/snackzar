<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const mobileMenuOpen = ref(false);
const searchOpen = ref(false);
const searchQuery = ref('');
const searchInput = ref(null);
const profileMenuOpen = ref(false);

const navigation = [
    { name: 'Home', href: '/' },
    { name: 'Products', href: '/products' },
    { name: 'Blog', href: '/blog' },
    { name: 'About', href: '/about' },
    { name: 'Contact', href: '/contact' },
];

const openSearch = () => {
    searchOpen.value = true;
    setTimeout(() => searchInput.value?.focus(), 100);
};

const closeSearch = () => {
    searchOpen.value = false;
    searchQuery.value = '';
};

const submitSearch = () => {
    if (searchQuery.value.trim()) {
        router.get('/products', { search: searchQuery.value.trim() });
        closeSearch();
        mobileMenuOpen.value = false;
    }
};

const handleKeydown = (e) => {
    if (e.key === 'Escape') closeSearch();
    if (e.key === '/' && !searchOpen.value && !['INPUT', 'TEXTAREA'].includes(e.target.tagName)) {
        e.preventDefault();
        openSearch();
    }
};

onMounted(() => document.addEventListener('keydown', handleKeydown));
onUnmounted(() => document.removeEventListener('keydown', handleKeydown));
</script>

<template>
    <nav class="bg-white/95 backdrop-blur-md shadow-sm sticky top-0 z-50 border-b border-amber-100/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-14 lg:h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <Link href="/" class="flex items-center gap-2">
                        <span class="text-xl lg:text-2xl">🥜</span>
                        <span class="text-lg lg:text-xl font-bold text-amber-900 tracking-tight">Snackzar</span>
                    </Link>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center gap-1">
                    <Link
                        v-for="item in navigation"
                        :key="item.name"
                        :href="item.href"
                        class="text-sm font-medium px-3 py-2 rounded-lg transition-colors relative"
                        :class="($page.url === item.href || (item.href !== '/' && $page.url.startsWith(item.href)))
                            ? 'text-amber-700 bg-amber-50'
                            : 'text-gray-600 hover:text-amber-600 hover:bg-amber-50/50'"
                    >
                        {{ item.name }}
                    </Link>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-1 lg:gap-2">
                    <!-- Search Trigger -->
                    <button
                        @click="openSearch"
                        class="p-2 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                        title="Search (Press /)"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>

                    <!-- Desktop Search Shortcut Badge -->
                    <button
                        @click="openSearch"
                        class="hidden lg:flex items-center gap-2 text-sm text-gray-400 border border-gray-200 rounded-lg px-3 py-1.5 hover:border-amber-300 hover:text-amber-600 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Search products...</span>
                        <kbd class="text-xs bg-gray-100 px-1.5 py-0.5 rounded font-mono">/</kbd>
                    </button>

                    <template v-if="user">
                        <!-- Cart -->
                        <Link
                            href="/cart"
                            class="p-2 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors relative hidden lg:flex"
                            title="Cart"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                            </svg>
                        </Link>

                        <!-- Profile Menu (Desktop) -->
                        <div class="relative hidden lg:block">
                            <button
                                @click="profileMenuOpen = !profileMenuOpen"
                                @blur="setTimeout(() => profileMenuOpen = false, 150)"
                                class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-amber-50 transition-colors"
                            >
                                <span class="w-8 h-8 bg-amber-100 text-amber-700 rounded-full flex items-center justify-center text-sm font-semibold">
                                    {{ user.name?.charAt(0)?.toUpperCase() }}
                                </span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <Transition
                                enter-active-class="transition ease-out duration-100"
                                enter-from-class="opacity-0 scale-95"
                                enter-to-class="opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="opacity-100 scale-100"
                                leave-to-class="opacity-0 scale-95"
                            >
                                <div v-if="profileMenuOpen" class="absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 z-50">
                                    <div class="px-4 py-2 border-b border-gray-50">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ user.name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ user.email }}</p>
                                    </div>
                                    <Link href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700">My Profile</Link>
                                    <Link href="/orders" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700">My Orders</Link>
                                    <Link href="/wishlist" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700">Wishlist</Link>
                                    <hr class="my-1 border-gray-100" />
                                    <Link href="/logout" method="post" as="button" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Logout</Link>
                                </div>
                            </Transition>
                        </div>
                    </template>
                    <template v-else>
                        <Link href="/login" class="hidden lg:inline-flex text-sm font-medium text-gray-600 hover:text-amber-600 px-3 py-2 rounded-lg transition-colors">
                            Sign In
                        </Link>
                        <Link href="/register" class="hidden lg:inline-flex bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-amber-700 transition-colors shadow-sm">
                            Get Started
                        </Link>
                    </template>

                    <!-- Mobile Hamburger -->
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="lg:hidden p-2 text-gray-700 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                    >
                        <svg v-if="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Search Overlay -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="searchOpen" class="fixed inset-0 z-[60] bg-black/40 backdrop-blur-sm" @click="closeSearch">
                <div class="max-w-2xl mx-auto mt-20 px-4" @click.stop>
                    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                        <form @submit.prevent="submitSearch" class="flex items-center">
                            <svg class="w-5 h-5 ml-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                ref="searchInput"
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search for Makhana, Namkeen, Sweets..."
                                class="flex-1 px-4 py-4 text-lg border-0 focus:ring-0 focus:outline-none placeholder-gray-400"
                            />
                            <button
                                v-if="searchQuery.trim()"
                                type="submit"
                                class="mr-3 bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-amber-700 transition-colors"
                            >
                                Search
                            </button>
                            <button
                                type="button"
                                @click="closeSearch"
                                class="mr-3 p-2 text-gray-400 hover:text-gray-600 transition-colors"
                            >
                                <kbd class="text-xs bg-gray-100 px-2 py-1 rounded font-mono">ESC</kbd>
                            </button>
                        </form>
                        <div class="border-t border-gray-100 px-5 py-3 bg-gray-50">
                            <p class="text-xs text-gray-400">Popular: <span class="text-amber-600 cursor-pointer hover:underline" @click="searchQuery = 'Makhana'; submitSearch()">Makhana</span> &middot; <span class="text-amber-600 cursor-pointer hover:underline" @click="searchQuery = 'Namkeen'; submitSearch()">Namkeen</span> &middot; <span class="text-amber-600 cursor-pointer hover:underline" @click="searchQuery = 'Sweets'; submitSearch()">Sweets</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Mobile Slide Menu -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div v-if="mobileMenuOpen" class="lg:hidden border-t border-amber-100 bg-white shadow-lg">
                <!-- Mobile Search -->
                <div class="px-4 pt-3">
                    <form @submit.prevent="submitSearch" class="flex items-center bg-gray-50 rounded-xl border border-gray-200">
                        <svg class="w-4 h-4 ml-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search products..."
                            class="flex-1 bg-transparent border-0 text-sm py-2.5 px-3 focus:ring-0 focus:outline-none placeholder-gray-400"
                        />
                    </form>
                </div>

                <div class="px-4 pt-2 pb-4 space-y-0.5">
                    <Link
                        v-for="item in navigation"
                        :key="item.name"
                        :href="item.href"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors"
                        :class="($page.url === item.href || (item.href !== '/' && $page.url.startsWith(item.href)))
                            ? 'text-amber-700 bg-amber-50'
                            : 'text-gray-700 hover:bg-gray-50'"
                        @click="mobileMenuOpen = false"
                    >
                        {{ item.name }}
                    </Link>
                    <hr class="my-2 border-gray-100" />
                    <template v-if="user">
                        <Link href="/profile" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50" @click="mobileMenuOpen = false">Profile</Link>
                        <Link href="/orders" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50" @click="mobileMenuOpen = false">Orders</Link>
                        <Link href="/wishlist" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50" @click="mobileMenuOpen = false">Wishlist</Link>
                        <Link href="/logout" method="post" as="button" class="flex items-center gap-3 w-full text-left px-3 py-2.5 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50" @click="mobileMenuOpen = false">Logout</Link>
                    </template>
                    <template v-else>
                        <Link href="/login" class="block text-center px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50" @click="mobileMenuOpen = false">Sign In</Link>
                        <Link href="/register" class="block text-center px-3 py-2.5 rounded-xl text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700" @click="mobileMenuOpen = false">Get Started</Link>
                    </template>
                </div>
            </div>
        </Transition>
    </nav>
</template>
