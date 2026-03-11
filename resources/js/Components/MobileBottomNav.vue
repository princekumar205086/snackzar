<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const currentUrl = computed(() => page.url);
</script>

<template>
    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-200 safe-area-bottom">
        <div class="flex items-center justify-around h-14">
            <!-- Home -->
            <Link href="/" class="flex flex-col items-center justify-center gap-0.5 flex-1 py-1" :class="currentUrl === '/' ? 'text-amber-600' : 'text-gray-500'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="text-[10px] font-medium">Home</span>
            </Link>

            <!-- Products -->
            <Link href="/products" class="flex flex-col items-center justify-center gap-0.5 flex-1 py-1" :class="currentUrl.startsWith('/products') ? 'text-amber-600' : 'text-gray-500'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span class="text-[10px] font-medium">Shop</span>
            </Link>

            <!-- Cart / Login -->
            <template v-if="user">
                <Link href="/cart" class="flex flex-col items-center justify-center gap-0.5 flex-1 py-1 text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                    </svg>
                    <span class="text-[10px] font-medium">Cart</span>
                </Link>
            </template>
            <template v-else>
                <Link href="/login" class="flex flex-col items-center justify-center gap-0.5 flex-1 py-1 text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    <span class="text-[10px] font-medium">Login</span>
                </Link>
            </template>

            <!-- Wishlist / Blog -->
            <template v-if="user">
                <Link href="/wishlist" class="flex flex-col items-center justify-center gap-0.5 flex-1 py-1 text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <span class="text-[10px] font-medium">Wishlist</span>
                </Link>
            </template>
            <template v-else>
                <Link href="/blog" class="flex flex-col items-center justify-center gap-0.5 flex-1 py-1" :class="currentUrl.startsWith('/blog') ? 'text-amber-600' : 'text-gray-500'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                    <span class="text-[10px] font-medium">Blog</span>
                </Link>
            </template>

            <!-- Profile -->
            <template v-if="user">
                <Link href="/profile" class="flex flex-col items-center justify-center gap-0.5 flex-1 py-1 text-gray-500">
                    <span class="w-5 h-5 bg-amber-100 text-amber-700 rounded-full flex items-center justify-center text-[10px] font-bold">
                        {{ user.name?.charAt(0)?.toUpperCase() }}
                    </span>
                    <span class="text-[10px] font-medium">Account</span>
                </Link>
            </template>
            <template v-else>
                <Link href="/register" class="flex flex-col items-center justify-center gap-0.5 flex-1 py-1 text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    <span class="text-[10px] font-medium">Register</span>
                </Link>
            </template>
        </div>
    </div>
</template>
