<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import SellerLayout from '@/Layouts/SellerLayout.vue';

const stats = ref(null);
const orders = ref([]);
const loading = ref(true);

async function load() {
    try {
        const res = await window.axios.get('/api/v1/seller/dashboard');
        const d = res.data.data ?? res.data;
        stats.value = d;
        orders.value = d.recent_orders ?? [];
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

function currency(v) {
    return '₹' + Number(v ?? 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
}
function formatDate(d) {
    return new Date(d).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
}

onMounted(load);
</script>

<template>
    <Head title="Seller Dashboard" />
    <SellerLayout>
        <div class="space-y-6">
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>

            <div v-if="loading" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-for="i in 4" :key="i" class="bg-white rounded-xl h-24 animate-pulse"></div>
            </div>

            <div v-else class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Total Orders</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats?.total_orders ?? 0 }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Revenue</p>
                    <p class="text-2xl font-bold text-amber-600 mt-2">{{ currency(stats?.total_revenue) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Products</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats?.total_products ?? 0 }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Pending Payouts</p>
                    <p class="text-2xl font-bold text-green-600 mt-2">{{ currency(stats?.pending_payout) }}</p>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <a href="/seller/products/create" class="bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-xl p-4 text-center transition-colors">
                    <p class="text-2xl mb-1">📦</p>
                    <p class="text-sm font-medium text-amber-800">Add Product</p>
                </a>
                <a href="/seller/orders" class="bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-xl p-4 text-center transition-colors">
                    <p class="text-2xl mb-1">🛒</p>
                    <p class="text-sm font-medium text-blue-800">View Orders</p>
                </a>
                <a href="/seller/payouts" class="bg-green-50 hover:bg-green-100 border border-green-200 rounded-xl p-4 text-center transition-colors">
                    <p class="text-2xl mb-1">💰</p>
                    <p class="text-sm font-medium text-green-800">Payouts</p>
                </a>
                <a href="/seller/profile" class="bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-xl p-4 text-center transition-colors">
                    <p class="text-2xl mb-1">👤</p>
                    <p class="text-sm font-medium text-purple-800">My Profile</p>
                </a>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900">Recent Orders</h2>
                    <a href="/seller/orders" class="text-sm text-amber-600 hover:text-amber-700">View all →</a>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Order #</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3 hidden md:table-cell">Customer</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Status</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Total</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3 hidden lg:table-cell">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in orders" :key="order.id" class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="px-5 py-3 text-amber-600 font-mono text-xs">#{{ order.id }}</td>
                            <td class="px-5 py-3 text-gray-700 hidden md:table-cell">{{ order.customer_name ?? order.user?.name }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">{{ order.status }}</span>
                            </td>
                            <td class="px-5 py-3 text-gray-900 font-medium">{{ currency(order.total) }}</td>
                            <td class="px-5 py-3 text-gray-400 text-xs hidden lg:table-cell">{{ formatDate(order.created_at) }}</td>
                        </tr>
                        <tr v-if="!orders.length">
                            <td colspan="5" class="text-center text-gray-400 py-8">No orders yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </SellerLayout>
</template>
