<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const menuOpen = ref(false);

const navItems = [
    { label: 'Dashboard',     href: '/dashboard',      icon: 'grid' },
    { label: 'My Orders',     href: '/orders',         icon: 'shopping-bag' },
    { label: 'Wishlist',      href: '/wishlist',       icon: 'heart' },
    { label: 'Addresses',     href: '/addresses',      icon: 'map-pin' },
    { label: 'Profile',       href: '/profile',        icon: 'user' },
    { label: 'Notifications', href: '/notifications',  icon: 'bell' },
];

function isActive(href) {
    return page.url === href || page.url.startsWith(href + '/');
}

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Mobile header -->
        <header class="bg-white border-b border-gray-100 lg:hidden">
            <div class="flex items-center justify-between px-4 h-14">
                <Link href="/" class="font-bold text-amber-700 text-lg">SNACKZAR</Link>
                <button @click="menuOpen = !menuOpen" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
            <!-- Mobile nav -->
            <div v-if="menuOpen" class="border-t border-gray-100 py-2">
                <Link v-for="item in navItems" :key="item.href" :href="item.href"
                    @click="menuOpen = false"
                    :class="isActive(item.href) ? 'bg-amber-50 text-amber-700' : 'text-gray-600'"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium">
                    {{ item.label }}
                </Link>
                <button @click="logout" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-red-500 w-full">
                    Sign Out
                </button>
            </div>
        </header>

        <div class="max-w-6xl mx-auto px-4 py-6 lg:py-8">
            <div class="grid lg:grid-cols-4 gap-6">
                <!-- Sidebar (desktop) -->
                <aside class="hidden lg:block">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <!-- User header -->
                        <div class="bg-gradient-to-br from-amber-500 to-amber-700 px-6 py-8 text-white">
                            <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center text-xl font-bold mb-3">
                                {{ user?.name?.charAt(0)?.toUpperCase() }}
                            </div>
                            <p class="font-semibold">{{ user?.name }}</p>
                            <p class="text-amber-200 text-sm">{{ user?.email }}</p>
                        </div>

                        <!-- Nav links -->
                        <nav class="py-3">
                            <Link v-for="item in navItems" :key="item.href" :href="item.href"
                                :class="isActive(item.href)
                                    ? 'bg-amber-50 text-amber-700 font-semibold'
                                    : 'text-gray-600 hover:bg-gray-50'"
                                class="flex items-center gap-3 px-5 py-3 text-sm transition-colors">
                                {{ item.label }}
                            </Link>
                            <button @click="logout" class="flex items-center gap-3 px-5 py-3 text-sm text-red-500 hover:bg-red-50 transition-colors w-full text-left">
                                Sign Out
                            </button>
                        </nav>
                    </div>
                </aside>

                <!-- Main content -->
                <div class="lg:col-span-3">
                    <div v-if="$page.props.flash?.success" class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl">
                        {{ $page.props.flash.success }}
                    </div>
                    <div v-if="$page.props.flash?.error" class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl">
                        {{ $page.props.flash.error }}
                    </div>
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
