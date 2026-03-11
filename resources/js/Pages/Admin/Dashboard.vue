<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const stats = ref(null);
const loading = ref(true);

onMounted(async () => {
    try {
        const res = await window.axios.get('/api/v1/admin/dashboard');
        stats.value = res.data.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
});

const statCards = [
    { key: 'total_users',    label: 'Total Users',     color: 'bg-blue-600',   icon: 'users' },
    { key: 'total_orders',   label: 'Total Orders',    color: 'bg-purple-600', icon: 'shopping-bag' },
    { key: 'total_products', label: 'Total Products',  color: 'bg-amber-500',  icon: 'box' },
    { key: 'total_sellers',  label: 'Active Sellers',  color: 'bg-green-600',  icon: 'briefcase' },
];
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout>
        <div>
            <h1 class="text-2xl font-bold text-white mb-6">Dashboard</h1>

            <!-- Stat Cards -->
            <div v-if="loading" class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div v-for="i in 4" :key="i" class="bg-gray-800 rounded-xl p-5 animate-pulse h-28"></div>
            </div>
            <div v-else-if="stats" class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div v-for="card in statCards" :key="card.key" class="bg-gray-800 rounded-xl p-5">
                    <div :class="card.color" class="w-10 h-10 rounded-lg flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="card.icon === 'users'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2 M23 21v-2a4 4 0 00-3-3.87 M16 3.13a4 4 0 010 7.75"/>
                            <path v-else-if="card.icon === 'shopping-bag'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z M3 6h18"/>
                            <path v-else-if="card.icon === 'box'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                            <path v-else-if="card.icon === 'briefcase'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                        </svg>
                    </div>
                    <p class="text-2xl font-bold text-white">{{ stats[card.key] ?? '—' }}</p>
                    <p class="text-sm text-gray-400 mt-1">{{ card.label }}</p>
                </div>
            </div>

            <!-- Revenue and recent stats -->
            <div v-if="stats" class="grid lg:grid-cols-3 gap-4 mb-8">
                <div class="bg-gray-800 rounded-xl p-5">
                    <p class="text-sm text-gray-400 mb-1">Today's Revenue</p>
                    <p class="text-2xl font-bold text-green-400">₹{{ stats.today_revenue ?? '0' }}</p>
                    <p class="text-xs text-gray-500 mt-2">+{{ stats.orders_today ?? 0 }} orders today</p>
                </div>
                <div class="bg-gray-800 rounded-xl p-5">
                    <p class="text-sm text-gray-400 mb-1">Total Revenue</p>
                    <p class="text-2xl font-bold text-white">₹{{ stats.total_revenue ?? '0' }}</p>
                    <p class="text-xs text-gray-500 mt-2">All time earnings</p>
                </div>
                <div class="bg-gray-800 rounded-xl p-5">
                    <p class="text-sm text-gray-400 mb-1">Pending Approvals</p>
                    <p class="text-2xl font-bold text-amber-400">{{ (stats.pending_sellers ?? 0) + (stats.pending_delivery ?? 0) }}</p>
                    <p class="text-xs text-gray-500 mt-2">Sellers + Delivery partners</p>
                </div>
            </div>

            <!-- Quick links -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <a href="/admin/users" class="bg-gray-800 hover:bg-gray-700 rounded-xl p-4 text-center transition-colors">
                    <p class="text-sm font-medium text-gray-300">Manage Users</p>
                </a>
                <a href="/admin/orders" class="bg-gray-800 hover:bg-gray-700 rounded-xl p-4 text-center transition-colors">
                    <p class="text-sm font-medium text-gray-300">All Orders</p>
                </a>
                <a href="/admin/sellers" class="bg-gray-800 hover:bg-gray-700 rounded-xl p-4 text-center transition-colors">
                    <p class="text-sm font-medium text-gray-300">Approve Sellers</p>
                </a>
                <a href="/admin/categories" class="bg-gray-800 hover:bg-gray-700 rounded-xl p-4 text-center transition-colors">
                    <p class="text-sm font-medium text-gray-300">Categories</p>
                </a>
            </div>
        </div>
    </AdminLayout>
</template>
