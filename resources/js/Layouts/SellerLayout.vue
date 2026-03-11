<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const sidebarOpen = ref(false);

const navItems = [
    { label: 'Dashboard', href: '/seller/dashboard', icon: 'grid' },
    { label: 'Products',  href: '/seller/products',  icon: 'box' },
    { label: 'Orders',    href: '/seller/orders',    icon: 'shopping-bag' },
    { label: 'Payouts',   href: '/seller/payouts',   icon: 'credit-card' },
    { label: 'Profile',   href: '/seller/profile',   icon: 'user' },
];

function isActive(href) {
    return page.url === href || page.url.startsWith(href + '/');
}

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- Sidebar Overlay Mobile -->
        <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/40 z-20 lg:hidden"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed lg:static inset-y-0 left-0 z-30 w-64 bg-white border-r border-gray-100 flex flex-col shadow-sm transition-transform duration-200">
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-gray-100 shrink-0">
                <Link href="/seller/dashboard" class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">S</span>
                    </div>
                    <div class="leading-tight">
                        <p class="font-bold text-gray-900 text-sm">SNACKZAR</p>
                        <p class="text-xs text-gray-400">Seller Portal</p>
                    </div>
                </Link>
            </div>

            <!-- Nav -->
            <nav class="flex-1 overflow-y-auto py-4 px-3">
                <Link v-for="item in navItems" :key="item.href" :href="item.href"
                    :class="isActive(item.href)
                        ? 'bg-amber-50 text-amber-700 border-r-2 border-amber-500'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium mb-1 transition-colors">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="item.icon === 'grid'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        <path v-else-if="item.icon === 'box'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                        <path v-else-if="item.icon === 'shopping-bag'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                        <path v-else-if="item.icon === 'credit-card'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        <path v-else-if="item.icon === 'user'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2 M12 11a4 4 0 100-8 4 4 0 000 8z"/>
                    </svg>
                    {{ item.label }}
                </Link>
            </nav>

            <!-- User -->
            <div class="px-4 py-4 border-t border-gray-100 shrink-0">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 bg-amber-100 text-amber-700 rounded-full flex items-center justify-center text-sm font-bold">
                        {{ user?.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ user?.name }}</p>
                        <p class="text-xs text-gray-500">Seller</p>
                    </div>
                </div>
                <button @click="logout" class="text-xs text-gray-400 hover:text-red-500 transition-colors flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Sign Out
                </button>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-white border-b border-gray-100 flex items-center px-4 lg:px-6 shrink-0">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden mr-4 p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="flex-1"></div>
                <Link href="/" target="_blank" class="text-xs text-gray-400 hover:text-gray-700 transition-colors">
                    View Store →
                </Link>
            </header>

            <main class="flex-1 overflow-y-auto bg-gray-50 p-4 lg:p-6">
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
                    {{ $page.props.flash.error }}
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
