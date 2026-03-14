<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useCart } from '@/composables/useCart';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const menuOpen = ref(false);
const { cartCount, loadCart } = useCart();

const navItems = [
    { label: 'Dashboard', href: '/dashboard', icon: 'grid' },
    { label: 'My Orders', href: '/orders', icon: 'shopping-bag' },
    { label: 'Wishlist', href: '/wishlist', icon: 'heart' },
    { label: 'Addresses', href: '/addresses', icon: 'map-pin' },
    { label: 'Profile', href: '/profile', icon: 'user' },
    { label: 'Notifications', href: '/notifications', icon: 'bell' },
];

const activePageLabel = computed(() => navItems.find((item) => isActive(item.href))?.label ?? 'My Account');

function isActive(href) {
    return page.url === href || page.url.startsWith(href + '/');
}

function logout() {
    router.post('/logout');
}

onMounted(() => {
    if (user.value) {
        loadCart();
    }
});
</script>

<template>
    <div class="min-h-screen bg-slate-100">
        <header class="fixed inset-x-0 top-0 z-50 h-16 border-b border-amber-700 bg-gradient-to-r from-amber-500 to-amber-700 text-white shadow-lg">
            <div class="mx-auto flex h-full max-w-[1400px] items-center justify-between px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <button @click="menuOpen = true" class="md:hidden rounded-md p-2 hover:bg-white/15">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <Link href="/dashboard" class="text-2xl font-black tracking-tight">Snackzar Dashboard</Link>
                </div>

                <div class="flex items-center gap-2.5">
                    <Link href="/cart" class="relative rounded-lg bg-white/15 px-3 py-1.5 text-sm font-semibold hover:bg-white/25">
                        Cart
                        <span v-if="cartCount > 0" class="ml-1 rounded-full bg-white px-1.5 py-0.5 text-[10px] font-bold leading-none text-amber-700">{{ cartCount > 99 ? '99+' : cartCount }}</span>
                    </Link>
                    <Link href="/" class="hidden rounded-lg bg-white/15 px-3 py-1.5 text-sm font-semibold hover:bg-white/25 sm:inline-flex">Store</Link>
                </div>
            </div>
        </header>

        <div class="pt-16">
            <div v-if="menuOpen" class="fixed inset-0 z-40 bg-black/40 md:hidden" @click="menuOpen = false"></div>

            <aside
                class="fixed inset-y-16 left-0 z-50 flex w-64 flex-col border-r border-slate-900 bg-slate-950 text-slate-200 transition-transform md:translate-x-0"
                :class="menuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
            >
                <div class="border-b border-amber-500/50 bg-gradient-to-br from-amber-500 to-amber-700 p-4">
                    <div class="mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-amber-300 text-xl font-black text-amber-900">
                        {{ user?.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <p class="truncate text-base font-bold text-white">Welcome</p>
                    <p class="truncate text-sm text-amber-100">{{ user?.email }}</p>
                </div>

                <nav class="flex-1 space-y-1 p-3">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        @click="menuOpen = false"
                        :class="isActive(item.href) ? 'bg-amber-500 text-slate-950' : 'text-slate-200 hover:bg-slate-800'"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition-colors"
                    >
                        <svg v-if="item.icon === 'grid'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h4a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <svg v-else-if="item.icon === 'shopping-bag'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14l-1 11H6L5 8zM9 8V6a3 3 0 116 0v2"/></svg>
                        <svg v-else-if="item.icon === 'heart'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        <svg v-else-if="item.icon === 'map-pin'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <svg v-else-if="item.icon === 'user'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span>{{ item.label }}</span>
                    </Link>
                </nav>

                <div class="border-t border-slate-800 p-3">
                    <button @click="logout" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold text-red-300 hover:bg-red-500/10">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H9m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h6a3 3 0 013 3v1"/></svg>
                        <span>Sign Out</span>
                    </button>
                </div>
            </aside>

            <main class="md:ml-64 p-4 sm:p-6">
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:px-5 mb-4">
                    <p class="text-sm text-slate-500">Home <span class="mx-1">›</span> <span class="font-semibold text-slate-700">{{ activePageLabel }}</span></p>
                    <h1 class="mt-2 text-4xl font-black tracking-tight text-slate-900">{{ activePageLabel }}</h1>
                    <p class="mt-1 text-slate-500">Manage your account, orders, and saved preferences.</p>
                </div>

                <div v-if="$page.props.flash?.success" class="mb-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="mb-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ $page.props.flash.error }}
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-3 sm:p-4 lg:p-5 shadow-sm">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
