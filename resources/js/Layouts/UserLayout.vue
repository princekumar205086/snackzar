<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useCart } from '@/composables/useCart';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const menuOpen = ref(false);
const sidebarCollapsed = ref(false);
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

function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem('user_sidebar_collapsed', sidebarCollapsed.value ? '1' : '0');
}

function logout() {
    router.post('/logout');
}

onMounted(() => {
    sidebarCollapsed.value = localStorage.getItem('user_sidebar_collapsed') === '1';
    if (user.value) {
        loadCart();
    }
});
</script>

<template>
    <div class="relative min-h-screen overflow-hidden bg-slate-100">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-28 -right-12 h-72 w-72 rounded-full bg-amber-200/35 blur-3xl" />
            <div class="absolute top-28 -left-24 h-80 w-80 rounded-full bg-orange-200/30 blur-3xl" />
        </div>

        <header class="relative border-b border-slate-200/80 bg-white/95 backdrop-blur lg:hidden">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4">
                <Link href="/" class="font-black tracking-wide text-amber-700">SNACKZAR</Link>
                <div class="flex items-center gap-2">
                    <Link href="/cart" class="relative rounded-lg border border-slate-200 bg-white p-2 text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4"/>
                        </svg>
                        <span v-if="cartCount > 0" class="absolute -right-1.5 -top-1.5 min-w-[18px] rounded-full bg-red-500 px-1 py-0.5 text-center text-[10px] font-bold leading-none text-white">{{ cartCount > 99 ? '99+' : cartCount }}</span>
                    </Link>
                    <button @click="menuOpen = !menuOpen" class="rounded-lg border border-slate-200 bg-white p-2 text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div v-if="menuOpen" class="border-t border-slate-200 bg-white px-3 py-2">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    @click="menuOpen = false"
                    :class="isActive(item.href) ? 'bg-amber-50 text-amber-700' : 'text-slate-700 hover:bg-slate-50'"
                    class="mb-1 flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold"
                >
                    {{ item.label }}
                </Link>
                <button @click="logout" class="flex w-full items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">
                    Sign Out
                </button>
            </div>
        </header>

        <div class="relative mx-auto flex w-full max-w-7xl gap-5 px-4 py-5 sm:py-6 lg:py-7">
            <aside
                class="hidden overflow-hidden rounded-3xl border border-slate-200/80 bg-white/90 shadow-sm backdrop-blur transition-all duration-300 lg:flex lg:flex-col"
                :class="sidebarCollapsed ? 'w-24' : 'w-72'"
            >
                <div class="border-b border-slate-100 bg-gradient-to-br from-amber-500 via-amber-600 to-orange-500 p-5 text-white" :class="sidebarCollapsed ? 'text-center' : ''">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20 text-lg font-black">
                        {{ user?.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <p v-if="!sidebarCollapsed" class="truncate text-sm font-bold">{{ user?.name }}</p>
                    <p v-if="!sidebarCollapsed" class="truncate text-xs text-amber-100">{{ user?.email }}</p>
                </div>

                <nav class="flex-1 space-y-1 p-3">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        :title="sidebarCollapsed ? item.label : ''"
                        :class="isActive(item.href)
                            ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                        class="group flex items-center rounded-2xl px-3 py-2.5 text-sm font-semibold transition-all"
                    >
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200/70 bg-white text-slate-600 group-hover:border-slate-300" :class="isActive(item.href) ? 'border-amber-200 text-amber-700' : ''">
                            <svg v-if="item.icon === 'grid'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h4a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            <svg v-else-if="item.icon === 'shopping-bag'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14l-1 11H6L5 8zM9 8V6a3 3 0 116 0v2"/></svg>
                            <svg v-else-if="item.icon === 'heart'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            <svg v-else-if="item.icon === 'map-pin'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <svg v-else-if="item.icon === 'user'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </span>
                        <span v-if="!sidebarCollapsed" class="ml-3 truncate">{{ item.label }}</span>
                    </Link>
                </nav>

                <div class="border-t border-slate-100 p-3">
                    <button
                        @click="logout"
                        :title="sidebarCollapsed ? 'Sign Out' : ''"
                        class="flex w-full items-center rounded-2xl px-3 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50"
                        :class="sidebarCollapsed ? 'justify-center' : ''"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H9m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h6a3 3 0 013 3v1"/></svg>
                        <span v-if="!sidebarCollapsed" class="ml-3">Sign Out</span>
                    </button>
                </div>
            </aside>

            <main class="min-w-0 flex-1">
                <div class="mb-4 hidden items-center justify-between rounded-3xl border border-slate-200/80 bg-white/90 px-4 py-3 shadow-sm backdrop-blur lg:flex">
                    <div class="flex items-center gap-3">
                        <button @click="toggleSidebar" class="rounded-xl border border-slate-200 bg-white p-2 text-slate-600 hover:bg-slate-50">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="sidebarCollapsed" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5v14"/>
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7M19 5v14"/>
                            </svg>
                        </button>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">User Panel</p>
                            <h1 class="text-sm font-bold text-slate-900">{{ activePageLabel }}</h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <Link href="/" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Storefront</Link>
                        <Link href="/cart" class="relative rounded-xl bg-amber-600 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-700">
                            Cart
                            <span v-if="cartCount > 0" class="ml-1 rounded-full bg-white px-1.5 py-0.5 text-[10px] font-bold leading-none text-amber-700">{{ cartCount > 99 ? '99+' : cartCount }}</span>
                        </Link>
                    </div>
                </div>

                <div v-if="$page.props.flash?.success" class="mb-3 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="mb-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ $page.props.flash.error }}
                </div>

                <div class="rounded-3xl border border-slate-200/80 bg-white/75 p-3 shadow-sm backdrop-blur sm:p-4 lg:p-5">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
