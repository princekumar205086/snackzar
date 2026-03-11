<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const sidebarOpen = ref(false);

const navItems = [
    { label: 'Dashboard',         href: '/admin/dashboard',          icon: 'grid' },
    { label: 'Orders',            href: '/admin/orders',             icon: 'shopping-bag' },
    { label: 'Users',             href: '/admin/users',              icon: 'users' },
    { label: 'Sellers',           href: '/admin/sellers',            icon: 'briefcase' },
    { label: 'Delivery Partners', href: '/admin/delivery-partners',  icon: 'truck' },
    { label: 'Categories',        href: '/admin/categories',         icon: 'tag' },
    { label: 'Blog',              href: '/admin/blog',               icon: 'file-text' },
];

function isActive(href) {
    return page.url === href || page.url.startsWith(href + '/');
}

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="min-h-screen bg-gray-950 text-white flex">
        <!-- Sidebar Overlay Mobile -->
        <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/60 z-20 lg:hidden"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed lg:static inset-y-0 left-0 z-30 w-64 bg-gray-900 border-r border-gray-800 flex flex-col transition-transform duration-200">
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-gray-800 shrink-0">
                <Link href="/admin/dashboard" class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">S</span>
                    </div>
                    <div class="leading-tight">
                        <p class="font-bold text-white text-sm">SNACKZAR</p>
                        <p class="text-xs text-gray-500">Admin Panel</p>
                    </div>
                </Link>
            </div>

            <!-- Nav -->
            <nav class="flex-1 overflow-y-auto py-4 px-3">
                <Link v-for="item in navItems" :key="item.href" :href="item.href"
                    :class="isActive(item.href)
                        ? 'bg-blue-600 text-white'
                        : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium mb-1 transition-colors">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="item.icon === 'grid'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        <path v-else-if="item.icon === 'shopping-bag'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z M3 6h18 M16 10a4 4 0 01-8 0"/>
                        <path v-else-if="item.icon === 'users'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2 M23 21v-2a4 4 0 00-3-3.87 M16 3.13a4 4 0 010 7.75"/>
                        <path v-else-if="item.icon === 'briefcase'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                        <path v-else-if="item.icon === 'truck'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h11a2 2 0 012 2v3m3 7a2 2 0 100 4 2 2 0 000-4zm-8 0a2 2 0 100 4 2 2 0 000-4z M16 5h2l3 5v5h-2"/>
                        <path v-else-if="item.icon === 'tag'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z M7 7h.01"/>
                        <path v-else-if="item.icon === 'file-text'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z M14 2v6h6 M16 13H8 M16 17H8 M10 9H8"/>
                    </svg>
                    {{ item.label }}
                </Link>
            </nav>

            <!-- User info -->
            <div class="px-4 py-4 border-t border-gray-800 shrink-0">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 bg-blue-700 rounded-full flex items-center justify-center text-sm font-bold">
                        {{ user?.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ user?.name }}</p>
                        <p class="text-xs text-gray-500 truncate">Administrator</p>
                    </div>
                </div>
                <button @click="logout" class="w-full text-left text-xs text-gray-500 hover:text-red-400 transition-colors flex items-center gap-2 py-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Sign Out
                </button>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Header -->
            <header class="h-16 bg-gray-900 border-b border-gray-800 flex items-center px-4 lg:px-6 shrink-0">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden mr-4 p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="flex-1"></div>
                <div class="flex items-center gap-3">
                    <Link href="/" target="_blank" class="text-xs text-gray-500 hover:text-white transition-colors">
                        View Store →
                    </Link>
                    <span class="text-gray-700">|</span>
                    <span class="text-sm text-gray-400">{{ user?.name }}</span>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto bg-gray-950 p-4 lg:p-6">
                <!-- Flash messages -->
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-900/50 border border-green-700 text-green-300 text-sm px-4 py-3 rounded-lg">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4 bg-red-900/50 border border-red-700 text-red-300 text-sm px-4 py-3 rounded-lg">
                    {{ $page.props.flash.error }}
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
